<?php
/**
 * Person Importer
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Importers;

use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\Validator;
use TenUpPlugin\CLI\Utils\DateFormatter;
use TenUpPlugin\CLI\Utils\ImageManager;
use TenUpPlugin\PostTypes\Person;
use WP_CLI;
use WP_Error;

/**
 * Person importer for IMDB data.
 */
class PersonImporter {

	/**
	 * IMDB API client.
	 *
	 * @var IMDBApiClient
	 */
	private $api_client;

	/**
	 * Image manager.
	 *
	 * @var ImageManager
	 */
	private $image_manager;

	/**
	 * Constructor.
	 *
	 * @param IMDBApiClient $api_client API client instance.
	 */
	public function __construct( IMDBApiClient $api_client ) {
		$this->api_client    = $api_client;
		$this->image_manager = new ImageManager();
	}

	/**
	 * Import people from IMDB IDs.
	 *
	 * @param array $imdb_ids Array of IMDB person IDs.
	 * @param array $options  Import options.
	 * @return array Results array with 'success' and 'errors' keys.
	 */
	public function import_people( $imdb_ids, $options ) {
		$results = [
			'success' => [],
			'errors'  => [],
		];

		// Fetch data from API.
		$api_results = $this->api_client->batch_get_people( $imdb_ids );

		// Process successful API responses.
		foreach ( $api_results['success'] as $imdb_id => $data ) {
			$validation = Validator::validate_person_data( $data );
			if ( true !== $validation ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $validation,
				];
				continue;
			}

			$result = $this->import_single_person( $data, $options );
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $result->get_error_message(),
				];
			} else {
				$results['success'][] = [
					'id'      => $imdb_id,
					'post_id' => $result,
					'name'    => $data['displayName'],
				];
			}
		}

		// Add API errors.
		$results['errors'] = array_merge( $results['errors'], $api_results['errors'] );

		return $results;
	}

	/**
	 * Import a single person.
	 *
	 * @param array $data    Person data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	private function import_single_person( $data, $options ) {
		$imdb_id = $data['id'];

		// Check if person already exists.
		$existing_post = $this->find_existing_person( $imdb_id );

		if ( $existing_post ) {
			if ( $options['skip_existing'] ) {
				return new WP_Error( 'exists', 'Person already exists and skip_existing is enabled' );
			}

			if ( ! $options['update'] ) {
				return new WP_Error( 'exists', 'Person already exists and update is not enabled' );
			}

			// Update existing person.
			return $this->update_person( $existing_post, $data, $options );
		}

		// Create new person.
		return $this->create_person( $data, $options );
	}

	/**
	 * Find existing person by IMDB ID.
	 *
	 * @param string $imdb_id IMDB ID.
	 * @return int|null Post ID or null if not found.
	 */
	private function find_existing_person( $imdb_id ) {
		$posts = get_posts(
			[
				'post_type'      => Person::POST_TYPE,
				'meta_key'       => 'tenup_person_imdb_id',
				'meta_value'     => $imdb_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Create new person post.
	 *
	 * @param array $data    Person data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	public function create_person( $data, $options ) {
		if ( $options['dry_run'] ) {
			WP_CLI::log( sprintf( 'Would create person: %s (%s)', $data['displayName'], $data['id'] ) );
			return 0; // Return 0 for dry run.
		}

		$post_data = [
			'post_title'   => $data['displayName'],
			'post_type'    => Person::POST_TYPE,
			'post_status'  => $options['post_status'],
			'post_content' => '', // Empty content, data is stored in meta.
		];

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set meta fields.
		$this->set_person_meta( $post_id, $data );

		// Set featured image.
		$this->set_featured_image( $post_id, $data );

		return $post_id;
	}

	/**
	 * Update existing person post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Person data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	public function update_person( $post_id, $data, $options ) {
		if ( $options['dry_run'] ) {
			WP_CLI::log( sprintf( 'Would update person: %s (%s)', $data['displayName'], $data['id'] ) );
			return $post_id;
		}

		$post_data = [
			'ID'          => $post_id,
			'post_title'  => $data['displayName'],
			'post_status' => $options['post_status'],
		];

		$result = wp_update_post( $post_data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Update meta fields.
		$this->set_person_meta( $post_id, $data );

		// Update featured image.
		$this->set_featured_image( $post_id, $data );

		return $post_id;
	}

	/**
	 * Set person meta fields.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Person data from API.
	 */
	private function set_person_meta( $post_id, $data ) {
		$meta_fields = [
			'tenup_person_imdb_id' => $data['id'],
		];

		// Birth date.
		if ( ! empty( $data['birthDate'] ) ) {
			$birth_date = DateFormatter::format_birth_date( $data['birthDate'] );
			if ( $birth_date ) {
				$meta_fields['tenup_person_born'] = $birth_date;
			}
		}

		// Birthplace.
		if ( ! empty( $data['birthLocation'] ) ) {
			$meta_fields['tenup_person_birthplace'] = $data['birthLocation'];
		}

		// Death date.
		if ( ! empty( $data['deathDate'] ) ) {
			$death_date = DateFormatter::format_death_date( $data['deathDate'] );
			if ( $death_date ) {
				$meta_fields['tenup_person_died'] = $death_date;
			}
		}

		// Death place.
		if ( ! empty( $data['deathLocation'] ) ) {
			$meta_fields['tenup_person_deathplace'] = $data['deathLocation'];
		}

		// Biography.
		if ( ! empty( $data['biography'] ) ) {
			$meta_fields['tenup_person_biography'] = $data['biography'];
		}

		// Set all meta fields.
		foreach ( $meta_fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Set featured image from API data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Person data from API.
	 */
	private function set_featured_image( $post_id, $data ) {
		if ( empty( $data['primaryImage']['url'] ) ) {
			return;
		}

		$image_url    = $data['primaryImage']['url'];
		$filename     = sanitize_file_name( $data['displayName'] );
		$is_different = $this->image_manager->is_image_different( $post_id, $image_url );

		if ( ! $is_different ) {
			return; // Image is the same, no need to update.
		}

		// Replace featured image.
		$attachment_id = $this->image_manager->replace_featured_image( $post_id, $image_url, $filename );
		if ( is_wp_error( $attachment_id ) ) {
			WP_CLI::warning( sprintf( 'Failed to set featured image for %s: %s', $data['displayName'], $attachment_id->get_error_message() ) );
		}
	}
}
