<?php
/**
 * Movie Importer
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Importers;

use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\Validator;
use TenUpPlugin\CLI\Utils\DateFormatter;
use TenUpPlugin\CLI\Utils\ImageManager;
use TenUpPlugin\CLI\Utils\RelationshipManager;
use TenUpPlugin\CLI\Importers\PersonImporter;
use TenUpPlugin\PostTypes\Movie;
use WP_CLI;
use WP_Error;

/**
 * Movie importer for IMDB data.
 */
class MovieImporter {

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
	 * Relationship manager.
	 *
	 * @var RelationshipManager
	 */
	private $relationship_manager;

	/**
	 * Person importer.
	 *
	 * @var PersonImporter
	 */
	private $person_importer;

	/**
	 * Constructor.
	 *
	 * @param IMDBApiClient $api_client API client instance.
	 */
	public function __construct( IMDBApiClient $api_client ) {
		$this->api_client           = $api_client;
		$this->image_manager        = new ImageManager();
		$this->relationship_manager = new RelationshipManager();
		$this->person_importer      = new PersonImporter( $api_client );
	}

	/**
	 * Import movies from IMDB IDs.
	 *
	 * @param array $imdb_ids Array of IMDB movie IDs.
	 * @param array $options  Import options.
	 * @return array Results array with 'success' and 'errors' keys.
	 */
	public function import_movies( $imdb_ids, $options ) {
		$results = [
			'success' => [],
			'errors'  => [],
		];

		// Fetch data from API.
		$api_results = $this->api_client->batch_get_movies( $imdb_ids );

		// Process successful API responses.
		foreach ( $api_results['success'] as $imdb_id => $data ) {
			$validation = Validator::validate_movie_data( $data );
			if ( true !== $validation ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $validation,
				];
				continue;
			}

			$result = $this->import_single_movie( $data, $options );
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $result->get_error_message(),
				];
			} else {
				$results['success'][] = [
					'id'      => $imdb_id,
					'post_id' => $result,
					'title'   => $data['primaryTitle'],
				];
			}
		}

		// Add API errors.
		$results['errors'] = array_merge( $results['errors'], $api_results['errors'] );

		return $results;
	}

	/**
	 * Import a single movie.
	 *
	 * @param array $data    Movie data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	private function import_single_movie( $data, $options ) {
		$imdb_id = $data['id'];

		// Check if movie already exists.
		$existing_post = $this->find_existing_movie( $imdb_id );

		if ( $existing_post ) {
			if ( $options['skip_existing'] ) {
				return new WP_Error( 'exists', 'Movie already exists and skip_existing is enabled' );
			}

			if ( ! $options['update'] ) {
				return new WP_Error( 'exists', 'Movie already exists and update is not enabled' );
			}

			// Update existing movie.
			return $this->update_movie( $existing_post, $data, $options );
		}

		// Create new movie.
		return $this->create_movie( $data, $options );
	}

	/**
	 * Find existing movie by IMDB ID.
	 *
	 * @param string $imdb_id IMDB ID.
	 * @return int|null Post ID or null if not found.
	 */
	private function find_existing_movie( $imdb_id ) {
		$posts = get_posts(
			[
				'post_type'      => Movie::POST_TYPE,
				'meta_key'       => 'tenup_movie_imdb_id',
				'meta_value'     => $imdb_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Create new movie post.
	 *
	 * @param array $data    Movie data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	private function create_movie( $data, $options ) {
		if ( $options['dry_run'] ) {
			WP_CLI::log( sprintf( 'Would create movie: %s (%s)', $data['primaryTitle'], $data['id'] ) );
			return 0; // Return 0 for dry run.
		}

		$post_data = [
			'post_title'   => $data['primaryTitle'],
			'post_type'    => Movie::POST_TYPE,
			'post_status'  => $options['post_status'],
			'post_content' => '', // Empty content, data is stored in meta.
		];

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set meta fields.
		$this->set_movie_meta( $post_id, $data );

		// Fetch and set MPA rating from certificates API.
		$this->set_mpa_rating( $post_id, $data['id'] );

		// Set featured image.
		$this->set_featured_image( $post_id, $data );

		// Set genres.
		$this->set_genres( $post_id, $data );

		// Import star cast if enabled.
		if ( $options['import_stars'] ) {
			$this->import_star_cast( $post_id, $data, $options );
		}

		return $post_id;
	}

	/**
	 * Update existing movie post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Movie data from API.
	 * @param array $options Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	private function update_movie( $post_id, $data, $options ) {
		if ( $options['dry_run'] ) {
			WP_CLI::log( sprintf( 'Would update movie: %s (%s)', $data['primaryTitle'], $data['id'] ) );
			return $post_id;
		}

		$post_data = [
			'ID'          => $post_id,
			'post_title'  => $data['primaryTitle'],
			'post_status' => $options['post_status'],
		];

		$result = wp_update_post( $post_data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Update meta fields.
		$this->set_movie_meta( $post_id, $data );

		// Fetch and update MPA rating from certificates API.
		$this->set_mpa_rating( $post_id, $data['id'] );

		// Update featured image.
		$this->set_featured_image( $post_id, $data );

		// Update genres.
		$this->set_genres( $post_id, $data );

		// Import star cast if enabled.
		if ( $options['import_stars'] ) {
			$this->import_star_cast( $post_id, $data, $options );
		}

		return $post_id;
	}

	/**
	 * Set movie meta fields.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Movie data from API.
	 */
	private function set_movie_meta( $post_id, $data ) {
		$meta_fields = [
			'tenup_movie_imdb_id' => $data['id'],
		];

		// Release year.
		if ( ! empty( $data['startYear'] ) ) {
			$meta_fields['tenup_movie_release_year'] = (string) $data['startYear'];
		}

		// Runtime.
		if ( ! empty( $data['runtimeSeconds'] ) ) {
			$runtime                            = DateFormatter::format_runtime( (int) $data['runtimeSeconds'] );
			$meta_fields['tenup_movie_runtime'] = $runtime;
		}

		// Plot.
		if ( ! empty( $data['plot'] ) ) {
			$meta_fields['tenup_movie_plot'] = $data['plot'];
		}

		// Viewer rating.
		if ( ! empty( $data['rating']['aggregateRating'] ) ) {
			$meta_fields['tenup_movie_viewer_rating'] = (string) $data['rating']['aggregateRating'];
		}

		// Vote count.
		if ( ! empty( $data['rating']['voteCount'] ) ) {
			$meta_fields['tenup_movie_viewer_rating_count'] = (string) $data['rating']['voteCount'];
		}

		// Set all meta fields.
		foreach ( $meta_fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Set MPA rating from certificates API.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $imdb_id IMDB movie ID.
	 */
	private function set_mpa_rating( $post_id, $imdb_id ) {
		// Fetch certificates data.
		$certificates_data = $this->api_client->get_movie_certificates( $imdb_id );

		if ( is_wp_error( $certificates_data ) ) {
			WP_CLI::warning( sprintf( 'Failed to fetch certificates for %s: %s', $imdb_id, $certificates_data->get_error_message() ) );
			return;
		}

		// Extract US MPA rating.
		$mpa_rating = $this->api_client->extract_us_mpa_rating( $certificates_data );

		if ( $mpa_rating ) {
			update_post_meta( $post_id, 'tenup_movie_mpa_rating', $mpa_rating );
			WP_CLI::log( sprintf( 'Set MPA rating for %s: %s', $imdb_id, $mpa_rating ) );
		} else {
			update_post_meta( $post_id, 'tenup_movie_mpa_rating', 'Not Rated' );
			WP_CLI::log( sprintf( 'No US MPA rating found for %s, set to: Not Rated', $imdb_id ) );
		}
	}

	/**
	 * Set featured image from API data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Movie data from API.
	 */
	private function set_featured_image( $post_id, $data ) {
		if ( empty( $data['primaryImage']['url'] ) ) {
			return;
		}

		$image_url    = $data['primaryImage']['url'];
		$filename     = sanitize_file_name( $data['primaryTitle'] );
		$is_different = $this->image_manager->is_image_different( $post_id, $image_url );

		if ( ! $is_different ) {
			return; // Image is the same, no need to update.
		}

		// Replace featured image.
		$attachment_id = $this->image_manager->replace_featured_image( $post_id, $image_url, $filename );
		if ( is_wp_error( $attachment_id ) ) {
			WP_CLI::warning( sprintf( 'Failed to set featured image for %s: %s', $data['primaryTitle'], $attachment_id->get_error_message() ) );
		}
	}

	/**
	 * Import star cast for a movie.
	 *
	 * @param int   $post_id Movie post ID.
	 * @param array $data    Movie data from API.
	 * @param array $options Import options.
	 */
	private function import_star_cast( $post_id, $data, $options ) {
		if ( empty( $data['stars'] ) || ! is_array( $data['stars'] ) ) {
			return;
		}

		// Limit number of stars.
		$stars      = array_slice( $data['stars'], 0, $options['stars_limit'] );
		$person_ids = [];

		foreach ( $stars as $order => $star ) {
			if ( empty( $star['id'] ) || empty( $star['displayName'] ) ) {
				continue;
			}

			// Check if person already exists.
			$existing_person = $this->find_existing_person( $star['id'] );
			if ( $existing_person ) {
				// Update existing person with latest data.
				$person_data = $this->api_client->get_person_data( $star['id'] );
				if ( ! is_wp_error( $person_data ) ) {
					$this->person_importer->update_person( $existing_person, $person_data, $options );
				}
				$person_ids[] = $existing_person;
			} else {
				// Create new person.
				$person_data = $this->api_client->get_person_data( $star['id'] );
				if ( is_wp_error( $person_data ) ) {
					WP_CLI::warning( sprintf( 'Failed to get person data for %s: %s', $star['displayName'], $person_data->get_error_message() ) );
					continue;
				}

				$person_id = $this->person_importer->create_person( $person_data, $options );
				if ( ! is_wp_error( $person_id ) ) {
					$person_ids[] = $person_id;
				}
			}
		}

		// Update relationships.
		$this->relationship_manager->update_movie_star_cast( $post_id, $person_ids );
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
				'post_type'      => 'tenup-person',
				'meta_key'       => 'tenup_person_imdb_id',
				'meta_value'     => $imdb_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Set genres from API data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Movie data from API.
	 */
	private function set_genres( $post_id, $data ) {
		if ( empty( $data['genres'] ) || ! is_array( $data['genres'] ) ) {
			return;
		}

		$genre_terms = [];
		foreach ( $data['genres'] as $genre ) {
			$term = get_term_by( 'name', $genre, 'tenup-genre' );
			if ( ! $term ) {
				$term = wp_insert_term( $genre, 'tenup-genre' );
				if ( ! is_wp_error( $term ) ) {
					$genre_terms[] = $term['term_id'];
				}
			} else {
				$genre_terms[] = $term->term_id;
			}
		}

		if ( ! empty( $genre_terms ) ) {
			wp_set_post_terms( $post_id, $genre_terms, 'tenup-genre' );
		}
	}
}
