<?php
/**
 * Image Manager for handling featured images
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Utils;

use WP_Error;

/**
 * ImageManager utility class.
 */
class ImageManager {

	/**
	 * Download and create attachment from URL.
	 *
	 * @param string $image_url Image URL.
	 * @param string $filename Desired filename (without extension).
	 * @param int    $post_id Post ID to attach to.
	 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	public function download_and_create_attachment( $image_url, $filename, $post_id = 0 ) {
		if ( empty( $image_url ) ) {
			return new WP_Error( 'no_url', 'No image URL provided.' );
		}

		// Download image.
		$response = wp_remote_get( $image_url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			return new WP_Error( 'no_data', 'No image data received.' );
		}

		// Get file extension from URL.
		$file_extension = pathinfo( $image_url, PATHINFO_EXTENSION );
		if ( empty( $file_extension ) ) {
			$file_extension = 'jpg'; // Default to jpg.
		}

		// Sanitize filename.
		$sanitized_filename = sanitize_file_name( $filename . '.' . $file_extension );

		// Upload file.
		$upload = wp_upload_bits( $sanitized_filename, null, $image_data );
		if ( $upload['error'] ) {
			return new WP_Error( 'upload_error', $upload['error'] );
		}

		// Create attachment.
		$attachment = [
			'post_mime_type' => wp_check_filetype( $sanitized_filename )['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate attachment metadata.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		return $attachment_id;
	}

	/**
	 * Set featured image for a post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $attachment_id Attachment ID.
	 * @return bool True on success, false on failure.
	 */
	public function set_featured_image( $post_id, $attachment_id ) {
		return set_post_thumbnail( $post_id, $attachment_id );
	}

	/**
	 * Delete old featured image and set new one.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $image_url New image URL.
	 * @param string $filename Desired filename.
	 * @return int|WP_Error New attachment ID on success, WP_Error on failure.
	 */
	public function replace_featured_image( $post_id, $image_url, $filename ) {
		// Get current featured image.
		$current_attachment_id = get_post_thumbnail_id( $post_id );

		// Download and create new attachment.
		$new_attachment_id = $this->download_and_create_attachment( $image_url, $filename, $post_id );
		if ( is_wp_error( $new_attachment_id ) ) {
			return $new_attachment_id;
		}

		// Set new featured image.
		$result = $this->set_featured_image( $post_id, $new_attachment_id );
		if ( ! $result ) {
			wp_delete_attachment( $new_attachment_id, true );
			return new WP_Error( 'set_featured_failed', 'Failed to set featured image.' );
		}

		// Delete old featured image if it exists.
		if ( $current_attachment_id ) {
			wp_delete_attachment( $current_attachment_id, true );
		}

		return $new_attachment_id;
	}

	/**
	 * Check if image URL is different from current featured image.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $new_image_url New image URL.
	 * @return bool True if different, false if same.
	 */
	public function is_image_different( $post_id, $new_image_url ) {
		$current_attachment_id = get_post_thumbnail_id( $post_id );
		if ( ! $current_attachment_id ) {
			return true; // No current image, so it's different.
		}

		$current_image_url = wp_get_attachment_url( $current_attachment_id );
		return $current_image_url !== $new_image_url;
	}

	/**
	 * Find existing attachment by filename.
	 *
	 * @param string $filename Filename to search for.
	 * @return int|false Attachment ID if found, false otherwise.
	 */
	public function find_existing_attachment( $filename ) {
		$attachments = get_posts(
			[
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'numberposts'    => 1,
				'meta_query'     => [
					[
						'key'     => '_wp_attached_file',
						'value'   => $filename,
						'compare' => 'LIKE',
					],
				],
			]
		);

		return ! empty( $attachments ) ? $attachments[0]->ID : false;
	}
}
