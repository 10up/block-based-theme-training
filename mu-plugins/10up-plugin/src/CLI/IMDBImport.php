<?php
/**
 * IMDB Import CLI Command
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI;

use WP_CLI;
use WP_CLI_Command;
use TenUpPlugin\CLI\Importers\MovieImporter;
use TenUpPlugin\CLI\Importers\PersonImporter;
use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\Validator;

/**
 * IMDB Import CLI Command.
 */
class IMDBImport extends WP_CLI_Command {

	/**
	 * IMDB API client.
	 *
	 * @var IMDBApiClient
	 */
	private $api_client;

	/**
	 * Movie importer.
	 *
	 * @var MovieImporter
	 */
	private $movie_importer;

	/**
	 * Person importer.
	 *
	 * @var PersonImporter
	 */
	private $person_importer;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->api_client      = new IMDBApiClient();
		$this->movie_importer  = new MovieImporter( $this->api_client );
		$this->person_importer = new PersonImporter( $this->api_client );
	}

	/**
	 * Import movies from IMDB IDs.
	 *
	 * ## OPTIONS
	 *
	 * <imdb_ids>...
	 * : One or more IMDB movie IDs (e.g., tt0111161 tt0068646)
	 *
	 * [--dry-run]
	 * : Preview what would be imported without creating posts
	 *
	 * [--update]
	 * : Update existing posts if they already exist (default behavior)
	 *
	 * [--skip-existing]
	 * : Skip posts that already exist
	 *
	 * [--force-update]
	 * : Force update even if data appears unchanged
	 *
	 * [--skip-image-update]
	 * : Skip featured image replacement during updates
	 *
	 * [--import-stars]
	 * : Import star cast when importing movies (default: true)
	 *
	 * [--skip-stars]
	 * : Skip star cast import when importing movies
	 *
	 * [--stars-limit=<limit>]
	 * : Maximum number of stars to import per movie
	 * ---
	 * default: 3
	 * ---
	 *
	 * [--post-status=<status>]
	 * : Set post status (default: publish)
	 * ---
	 * default: publish
	 * options:
	 *   - publish
	 *   - draft
	 *   - private
	 * ---
	 *
	 * [--file=<file>]
	 * : Read IMDB IDs from a file (one per line)
	 *
	 * ## EXAMPLES
	 *
	 *     wp imdb-import movies tt0111161 tt0068646
	 *     wp imdb-import movies --file=movie_ids.txt
	 *     wp imdb-import movies tt0111161 --dry-run
	 *     wp imdb-import movies tt0111161 --update
	 *     wp imdb-import movies tt0111161 --skip-stars
	 *     wp imdb-import movies tt0111161 --stars-limit=5
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function movies( $args, $assoc_args ) {
		$imdb_ids = $this->get_imdb_ids( $args, $assoc_args );
		$imdb_ids = $this->filter_movie_ids( $imdb_ids );

		if ( empty( $imdb_ids ) ) {
			WP_CLI::error( 'No valid movie IMDB IDs provided.' );
		}

		$options = $this->parse_options( $assoc_args );

		WP_CLI::log( sprintf( 'Importing %d movies...', count( $imdb_ids ) ) );

		$results = $this->movie_importer->import_movies( $imdb_ids, $options );

		$this->display_results( $results, 'movies' );
	}

	/**
	 * Import people from IMDB IDs.
	 *
	 * ## OPTIONS
	 *
	 * <imdb_ids>...
	 * : One or more IMDB person IDs (e.g., nm0000008 nm0123785)
	 *
	 * [--dry-run]
	 * : Preview what would be imported without creating posts
	 *
	 * [--update]
	 * : Update existing posts if they already exist (default behavior)
	 *
	 * [--skip-existing]
	 * : Skip posts that already exist
	 *
	 * [--force-update]
	 * : Force update even if data appears unchanged
	 *
	 * [--skip-image-update]
	 * : Skip featured image replacement during updates
	 *
	 * [--post-status=<status>]
	 * : Set post status (default: publish)
	 * ---
	 * default: publish
	 * options:
	 *   - publish
	 *   - draft
	 *   - private
	 * ---
	 *
	 * [--file=<file>]
	 * : Read IMDB IDs from a file (one per line)
	 *
	 * ## EXAMPLES
	 *
	 *     wp imdb-import people nm0000008 nm0123785
	 *     wp imdb-import people --file=person_ids.txt
	 *     wp imdb-import people nm0000008 --dry-run
	 *     wp imdb-import people nm0000008 --update
	 *     wp imdb-import people nm0000008 --skip-image-update
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function people( $args, $assoc_args ) {
		$imdb_ids = $this->get_imdb_ids( $args, $assoc_args );
		$imdb_ids = $this->filter_person_ids( $imdb_ids );

		if ( empty( $imdb_ids ) ) {
			WP_CLI::error( 'No valid person IMDB IDs provided.' );
		}

		$options = $this->parse_options( $assoc_args );

		WP_CLI::log( sprintf( 'Importing %d people...', count( $imdb_ids ) ) );

		$results = $this->person_importer->import_people( $imdb_ids, $options );

		$this->display_results( $results, 'people' );
	}

	/**
	 * Import both movies and people from IMDB IDs.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Read IMDB IDs from a file (one per line)
	 *
	 * [--dry-run]
	 * : Preview what would be imported without creating posts
	 *
	 * [--update]
	 * : Update existing posts if they already exist
	 *
	 * [--skip-existing]
	 * : Skip posts that already exist
	 *
	 * [--post-status=<status>]
	 * : Set post status (default: publish)
	 * ---
	 * default: publish
	 * options:
	 *   - publish
	 *   - draft
	 *   - private
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp imdb-import both --file=imdb_ids.txt
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function both( $args, $assoc_args ) {
		if ( ! isset( $assoc_args['file'] ) ) {
			WP_CLI::error( 'File option is required for both command.' );
		}

		$imdb_ids   = $this->get_imdb_ids_from_file( $assoc_args['file'] );
		$movie_ids  = $this->filter_movie_ids( $imdb_ids );
		$person_ids = $this->filter_person_ids( $imdb_ids );

		$options = $this->parse_options( $assoc_args );

		WP_CLI::log( sprintf( 'Importing %d movies and %d people...', count( $movie_ids ), count( $person_ids ) ) );

		$movie_results  = [];
		$person_results = [];

		if ( ! empty( $movie_ids ) ) {
			$movie_results = $this->movie_importer->import_movies( $movie_ids, $options );
		}

		if ( ! empty( $person_ids ) ) {
			$person_results = $this->person_importer->import_people( $person_ids, $options );
		}

		$this->display_combined_results( $movie_results, $person_results );
	}

	/**
	 * Get IMDB IDs from arguments or file.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return array
	 */
	private function get_imdb_ids( $args, $assoc_args ) {
		if ( isset( $assoc_args['file'] ) ) {
			return $this->get_imdb_ids_from_file( $assoc_args['file'] );
		}

		return $args;
	}

	/**
	 * Get IMDB IDs from file.
	 *
	 * @param string $file_path File path.
	 * @return array
	 */
	private function get_imdb_ids_from_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			WP_CLI::error( sprintf( 'File not found: %s', $file_path ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file reading.
		$content = file_get_contents( $file_path );
		$lines   = array_filter( array_map( 'trim', explode( "\n", $content ) ) );

		return $lines;
	}

	/**
	 * Filter movie IMDB IDs.
	 *
	 * @param array $imdb_ids IMDB IDs.
	 * @return array
	 */
	private function filter_movie_ids( $imdb_ids ) {
		return array_filter( $imdb_ids, [ Validator::class, 'is_movie_id' ] );
	}

	/**
	 * Filter person IMDB IDs.
	 *
	 * @param array $imdb_ids IMDB IDs.
	 * @return array
	 */
	private function filter_person_ids( $imdb_ids ) {
		return array_filter( $imdb_ids, [ Validator::class, 'is_person_id' ] );
	}

	/**
	 * Parse command options.
	 *
	 * @param array $assoc_args Associative arguments.
	 * @return array
	 */
	private function parse_options( $assoc_args ) {
		return [
			'dry_run'           => isset( $assoc_args['dry-run'] ),
			'update'            => ! isset( $assoc_args['skip-existing'] ), // Default to update unless skip-existing is set
			'skip_existing'     => isset( $assoc_args['skip-existing'] ),
			'force_update'      => isset( $assoc_args['force-update'] ),
			'skip_image_update' => isset( $assoc_args['skip-image-update'] ),
			'import_stars'      => ! isset( $assoc_args['skip-stars'] ), // Default to import stars unless skip-stars is set
			'skip_stars'        => isset( $assoc_args['skip-stars'] ),
			'stars_limit'       => isset( $assoc_args['stars-limit'] ) ? (int) $assoc_args['stars-limit'] : 3,
			'post_status'       => $assoc_args['post-status'] ?? 'publish',
		];
	}

	/**
	 * Display import results.
	 *
	 * @param array  $results Import results.
	 * @param string $type    Type of import (movies/people).
	 */
	private function display_results( $results, $type ) {
		$success_count = count( $results['success'] );
		$error_count   = count( $results['errors'] );

		WP_CLI::success( sprintf( 'Successfully imported %d %s', $success_count, $type ) );

		if ( $error_count > 0 ) {
			WP_CLI::warning( sprintf( '%d %s failed to import:', $error_count, $type ) );
			foreach ( $results['errors'] as $error ) {
				WP_CLI::log( sprintf( '  %s: %s', $error['id'], $error['message'] ) );
			}
		}
	}

	/**
	 * Display combined import results.
	 *
	 * @param array $movie_results Movie import results.
	 * @param array $person_results Person import results.
	 */
	private function display_combined_results( $movie_results, $person_results ) {
		$total_success = count( $movie_results['success'] ) + count( $person_results['success'] );
		$total_errors  = count( $movie_results['errors'] ) + count( $person_results['errors'] );

		WP_CLI::success( sprintf( 'Successfully imported %d items total', $total_success ) );

		if ( $total_errors > 0 ) {
			WP_CLI::warning( sprintf( '%d items failed to import:', $total_errors ) );
			foreach ( $movie_results['errors'] as $error ) {
				WP_CLI::log( sprintf( '  Movie %s: %s', $error['id'], $error['message'] ) );
			}
			foreach ( $person_results['errors'] as $error ) {
				WP_CLI::log( sprintf( '  Person %s: %s', $error['id'], $error['message'] ) );
			}
		}
	}
}
