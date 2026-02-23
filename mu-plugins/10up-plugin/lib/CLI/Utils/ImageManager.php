<?php
/**
 * Featured image download and attachment creation.
 *
 * @package TenUpPlugin\CLI\Utils
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * ImageManager class.
 */
class ImageManager {

	/**
	 * Download an image and attach it to a post as the featured image.
	 *
	 * @param string $image_url The URL of the image to download.
	 * @param int    $post_id   The post ID to attach the image to.
	 * @param string $title     The title used for the filename.
	 * @return int|false The attachment ID on success, false on failure.
	 */
	public function download_and_attach( string $image_url, int $post_id, string $title ): int|false {
		$tmp_file = download_url( $image_url, 30 );

		if ( is_wp_error( $tmp_file ) ) {
			\WP_CLI::warning( "  Failed to download image for '{$title}': " . $tmp_file->get_error_message() );
			return false;
		}

		$extension = $this->get_extension( $image_url );
		$filename  = sanitize_file_name( sanitize_title( $title ) ) . '.' . $extension;

		$file_array = [
			'name'     => $filename,
			'tmp_name' => $tmp_file,
		];

		$attachment_id = media_handle_sideload( $file_array, $post_id, $title );

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp_file );
			\WP_CLI::warning( "  Failed to create attachment for '{$title}': " . $attachment_id->get_error_message() );
			return false;
		}

		set_post_thumbnail( $post_id, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Extract the file extension from an image URL.
	 *
	 * @param string $url The image URL.
	 * @return string The file extension, defaulting to 'jpg'.
	 */
	private function get_extension( string $url ): string {
		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( $path ) {
			$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

			if ( in_array( $ext, [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ], true ) ) {
				return $ext;
			}
		}

		return 'jpg';
	}
}
