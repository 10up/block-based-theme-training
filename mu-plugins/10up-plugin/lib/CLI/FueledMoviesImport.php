<?php
/**
 * WP-CLI command for importing movies and people from the IMDB API.
 *
 * @package TenUpPlugin\CLI
 */

namespace TenUpPlugin\CLI;

use TenUpPlugin\CLI\Importers\MovieImporter;
use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\ImageManager;
use TenUpPlugin\CLI\Utils\RelationshipManager;
use TenUpPlugin\CLI\Utils\Validator;
use TenUpPlugin\CLI\Utils\DateFormatter;
use WP_CLI;

/**
 * FueledMoviesImport command class.
 */
class FueledMoviesImport extends \WP_CLI_Command {

	const DEFAULT_STAR_LIMIT = 3;

	const DEFAULT_MOVIE_IDS = [
		'tt0053779', // La Dolce Vita
		'tt0087332', // Ghostbusters
		'tt0062622', // 2001: A Space Odyssey
		'tt0076759', // Star Wars: Episode IV - A New Hope
		'tt0060196', // The Good, the Bad and the Ugly
		'tt0047478', // Seven Samurai
		'tt0114709', // Toy Story
		'tt0050083', // 12 Angry Men
		'tt15239678', // Dune: Part Two
		'tt0043014', // Sunset Boulevard
		'tt0120737', // The Lord of the Rings: The Fellowship of the Ring
		'tt0167261', // The Lord of the Rings: The Two Towers
		'tt0167260', // The Lord of the Rings: The Return of the King
		'tt0910970', // WALL-E
		'tt6751668', // Parasite
		'tt0482571', // The Prestige
		'tt0075148', // Rocky
		'tt0021814', // City Lights
		'tt0245429', // Spirited Away
		'tt0089218', // The Goonies
		'tt0109830', // Forrest Gump
		'tt0095016', // Die Hard
		'tt0046912', // Dial M for Murder
		'tt0099685', // Goodfellas
		'tt0045152', // Singin' in the Rain
		'tt0133093', // The Matrix
		'tt0068646', // The Godfather
		'tt26733325', // Homebound
		'tt4154796', // Avengers: Endgame
		'tt0052357', // Vertigo
	];

	/**
	 * Imports movies and people from the IMDB API.
	 *
	 * ## OPTIONS
	 *
	 * [--ids=<ids>]
	 * : Comma-separated list of IMDB title IDs (e.g., tt0910970,tt0068646).
	 *   If omitted, imports the 30 default movies.
	 *
	 * [--dry-run]
	 * : Preview what would be imported without writing to the database.
	 *
	 * [--star-limit=<number>]
	 * : Maximum number of stars to import per movie.
	 * ---
	 * default: 3
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp fueled-movies import
	 *     wp fueled-movies import --ids=tt0910970,tt0068646
	 *     wp fueled-movies import --dry-run
	 *     wp fueled-movies import --star-limit=5
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$dry_run    = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
		$star_limit = (int) \WP_CLI\Utils\get_flag_value( $assoc_args, 'star-limit', self::DEFAULT_STAR_LIMIT );
		$ids_flag   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'ids', '' );

		$movie_ids = $this->resolve_movie_ids( $ids_flag, $star_limit );

		if ( empty( $movie_ids ) ) {
			WP_CLI::error( 'No valid movie IDs to import.' );
		}

		if ( ! $dry_run ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$api_client           = new IMDBApiClient();
		$image_manager        = new ImageManager();
		$relationship_manager = new RelationshipManager();
		$validator            = new Validator();
		$date_formatter       = new DateFormatter();

		$movie_importer = new MovieImporter(
			$api_client,
			$image_manager,
			$relationship_manager,
			$validator,
			$date_formatter,
			$star_limit,
			$dry_run
		);

		$movie_count = count( $movie_ids );

		WP_CLI::log( '' );
		WP_CLI::log(
			sprintf(
				'Importing %d movies with up to %d stars each. This will take a few minutes -- sit tight.',
				$movie_count,
				$star_limit
			)
		);

		if ( $dry_run ) {
			WP_CLI::log( '(Dry run mode -- no changes will be made.)' );
		}

		WP_CLI::log( '' );

		$progress = \WP_CLI\Utils\make_progress_bar( 'Importing movies', $movie_count );
		$imported = 0;
		$skipped  = 0;
		$failed   = 0;

		foreach ( $movie_ids as $imdb_id ) {
			$result = $movie_importer->import( $imdb_id );

			switch ( $result ) {
			case 'imported':
				++$imported;
				break;
			case 'skipped':
				++$skipped;
				break;
			case 'failed':
				++$failed;
				break;
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::log( '' );

		if ( $dry_run ) {
			WP_CLI::success(
				sprintf(
					'Dry run complete. Would import %d movies, %d skipped, %d failed.',
					$imported,
					$skipped,
					$failed
				)
			);
		} else {
			WP_CLI::success(
				sprintf(
					'Done! %d movies imported, %d skipped, %d failed.',
					$imported,
					$skipped,
					$failed
				)
			);
		}
	}

	/**
	 * Resolve which movie IDs to import based on the --ids flag.
	 *
	 * @param string $ids_flag   The raw --ids flag value.
	 * @param int    $star_limit The star limit for the confirmation message.
	 * @return array The list of movie IDs to import.
	 */
	private function resolve_movie_ids( string $ids_flag, int $star_limit ): array {
		$validator = new Validator();

		if ( ! empty( $ids_flag ) ) {
			$raw_ids   = array_map( 'trim', explode( ',', $ids_flag ) );
			$movie_ids = [];

			foreach ( $raw_ids as $id ) {
				if ( empty( $id ) ) {
					continue;
				}

				if ( ! $validator->is_valid_title_id( $id ) ) {
					WP_CLI::warning( "Invalid IMDB ID format: {$id}. Removing from import list." );
					continue;
				}

				$movie_ids[] = $id;
			}

			return $movie_ids;
		}

		WP_CLI::confirm(
			sprintf(
				'You are about to import %d default movies with up to %d stars each. Continue?',
				count( self::DEFAULT_MOVIE_IDS ),
				$star_limit
			)
		);

		return self::DEFAULT_MOVIE_IDS;
	}
}
