<?php
/**
 * Movie post importer.
 *
 * @package TenUpPlugin\CLI\Importers
 */

namespace TenUpPlugin\CLI\Importers;

use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\ImageManager;
use TenUpPlugin\CLI\Utils\RelationshipManager;
use TenUpPlugin\CLI\Utils\Validator;
use TenUpPlugin\CLI\Utils\DateFormatter;
use TenUpPlugin\PostTypes\Movie;
use TenUpPlugin\PostMeta\MovieIMDBID;
use TenUpPlugin\PostMeta\MovieReleaseYear;
use TenUpPlugin\PostMeta\MovieRuntime;
use TenUpPlugin\PostMeta\MoviePlot;
use TenUpPlugin\PostMeta\MovieViewerRating;
use TenUpPlugin\PostMeta\MovieViewerRatingCount;
use TenUpPlugin\PostMeta\MovieMPARating;
use TenUpPlugin\PostMeta\MovieTrailerID;
use TenUpPlugin\Taxonomies\Genre;
use WP_CLI;
use WP_Query;

/**
 * MovieImporter class.
 */
class MovieImporter {

	/**
	 * Valid MPA ratings that match the MovieMPARating enum.
	 */
	const VALID_MPA_RATINGS = [ 'G', 'PG', 'PG-13', 'R', 'NC-17' ];

	/**
	 * The IMDB API client.
	 *
	 * @var IMDBApiClient
	 */
	private IMDBApiClient $api_client;

	/**
	 * The featured image manager.
	 *
	 * @var ImageManager
	 */
	private ImageManager $image_manager;

	/**
	 * The Content Connect relationship manager.
	 *
	 * @var RelationshipManager
	 */
	private RelationshipManager $relationship_manager;

	/**
	 * The IMDB ID and response validator.
	 *
	 * @var Validator
	 */
	private Validator $validator;

	/**
	 * The date and runtime formatter.
	 *
	 * @var DateFormatter
	 */
	private DateFormatter $date_formatter;

	/**
	 * Maximum number of stars to import per movie.
	 *
	 * @var int
	 */
	private int $star_limit;

	/**
	 * Whether this is a dry run (no database writes).
	 *
	 * @var bool
	 */
	private bool $dry_run;

	/**
	 * The person importer instance.
	 *
	 * @var PersonImporter
	 */
	private PersonImporter $person_importer;

	/**
	 * Constructor.
	 *
	 * @param IMDBApiClient       $api_client           The API client.
	 * @param ImageManager        $image_manager        The image manager.
	 * @param RelationshipManager $relationship_manager The relationship manager.
	 * @param Validator           $validator            The validator.
	 * @param DateFormatter       $date_formatter       The date formatter.
	 * @param int                 $star_limit           Maximum stars per movie.
	 * @param bool                $dry_run              Whether this is a dry run.
	 */
	public function __construct(
		IMDBApiClient $api_client,
		ImageManager $image_manager,
		RelationshipManager $relationship_manager,
		Validator $validator,
		DateFormatter $date_formatter,
		int $star_limit,
		bool $dry_run
	) {
		$this->api_client           = $api_client;
		$this->image_manager        = $image_manager;
		$this->relationship_manager = $relationship_manager;
		$this->validator            = $validator;
		$this->date_formatter       = $date_formatter;
		$this->star_limit           = $star_limit;
		$this->dry_run              = $dry_run;

		$this->person_importer = new PersonImporter(
			$this->api_client,
			$this->image_manager,
			$this->validator,
			$this->date_formatter,
			$this->dry_run
		);
	}

	/**
	 * Import a single movie by IMDB ID.
	 *
	 * @param string $imdb_id The IMDB title ID.
	 * @return string 'imported', 'skipped', or 'failed'.
	 */
	public function import( string $imdb_id ): string {
		if ( ! $this->validator->is_valid_title_id( $imdb_id ) ) {
			WP_CLI::warning( "Invalid IMDB ID format: {$imdb_id}. Skipping." );
			return 'failed';
		}

		$existing_id = $this->find_existing_movie( $imdb_id );
		if ( $existing_id ) {
			WP_CLI::warning( "Movie {$imdb_id} already exists (post #{$existing_id}). Skipping." );
			return 'skipped';
		}

		$movie_data = $this->api_client->get_title( $imdb_id );

		if ( is_wp_error( $movie_data ) ) {
			WP_CLI::warning( "Failed to fetch movie data for {$imdb_id}: " . $movie_data->get_error_message() );
			return 'failed';
		}

		if ( ! $this->validator->has_required_movie_fields( $movie_data ) ) {
			WP_CLI::warning( "Movie {$imdb_id} missing required fields (primaryTitle, id, or primaryImage). Skipping." );
			return 'failed';
		}

		$title = $movie_data['primaryTitle'];

		WP_CLI::log( "Importing: {$title}..." );

		$cert_data  = $this->api_client->get_certificates( $imdb_id );
		$mpa_rating = $this->extract_mpa_rating( $cert_data );

		$video_data = $this->api_client->get_videos( $imdb_id );
		$trailer_id = $this->extract_trailer_id( $video_data );

		if ( $this->dry_run ) {
			WP_CLI::log( "  [DRY RUN] Would create movie: {$title}" );
			WP_CLI::log( "  MPA: {$mpa_rating} | Trailer: " . ( $trailer_id ? $trailer_id : 'none' ) );

			$stars = array_slice( $movie_data['stars'] ?? [], 0, $this->star_limit );
			foreach ( $stars as $star ) {
				$this->person_importer->import( $star );
			}

			return 'imported';
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => Movie::POST_TYPE,
				'post_title'  => $title,
				'post_status' => 'publish',
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( "Failed to create post for {$title}: " . $post_id->get_error_message() );
			return 'failed';
		}

		$this->image_manager->download_and_attach(
			$movie_data['primaryImage']['url'],
			$post_id,
			$title
		);

		update_post_meta( $post_id, MovieIMDBID::META_KEY, $imdb_id );
		update_post_meta( $post_id, MovieReleaseYear::META_KEY, (string) ( $movie_data['startYear'] ?? '' ) );
		update_post_meta( $post_id, MovieRuntime::META_KEY, $this->date_formatter->runtime_seconds_to_object( $movie_data['runtimeSeconds'] ?? 0 ) );
		update_post_meta( $post_id, MoviePlot::META_KEY, $movie_data['plot'] ?? '' );
		update_post_meta( $post_id, MovieViewerRating::META_KEY, (string) ( $movie_data['rating']['aggregateRating'] ?? '0.0' ) );
		update_post_meta( $post_id, MovieViewerRatingCount::META_KEY, (string) ( $movie_data['rating']['voteCount'] ?? '0' ) );
		update_post_meta( $post_id, MovieMPARating::META_KEY, $mpa_rating );
		update_post_meta( $post_id, MovieTrailerID::META_KEY, $trailer_id );

		$this->assign_genres( $post_id, $movie_data['genres'] ?? [] );

		$stars      = array_slice( $movie_data['stars'] ?? [], 0, $this->star_limit );
		$star_count = 0;

		foreach ( $stars as $index => $star ) {
			$person_post_id = $this->person_importer->import( $star );

			if ( $person_post_id ) {
				$this->relationship_manager->connect_movie_person( $post_id, $person_post_id, $index );
				++$star_count;
			}
		}

		WP_CLI::success( "Created: {$title} ({$star_count} stars)" );

		return 'imported';
	}

	/**
	 * Find an existing movie post by IMDB ID.
	 *
	 * @param string $imdb_id The IMDB title ID.
	 * @return int|false The post ID if found, false otherwise.
	 */
	private function find_existing_movie( string $imdb_id ): int|false {
		$query = new WP_Query(
			[
				'post_type'      => Movie::POST_TYPE,
				'post_status'    => 'any',
				'meta_key'       => MovieIMDBID::META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $imdb_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			]
		);

		if ( ! empty( $query->posts ) ) {
			return $query->posts[0];
		}

		return false;
	}

	/**
	 * Extract the US MPA rating from certificate data.
	 *
	 * @param array|\WP_Error $cert_data The certificates API response.
	 * @return string The MPA rating, or 'Unrated' as fallback.
	 */
	private function extract_mpa_rating( $cert_data ): string {
		if ( is_wp_error( $cert_data ) || empty( $cert_data['certificates'] ) ) {
			return 'Unrated';
		}

		foreach ( $cert_data['certificates'] as $cert ) {
			if ( ! isset( $cert['country']['code'] ) || 'US' !== $cert['country']['code'] ) {
				continue;
			}

			$has_certificate_attr = false;

			foreach ( $cert['attributes'] ?? [] as $attr ) {
				if ( stripos( $attr, 'certificate #' ) !== false || stripos( $attr, 'certificate#' ) !== false ) {
					$has_certificate_attr = true;
					break;
				}
			}

			if ( $has_certificate_attr && isset( $cert['rating'] ) && in_array( $cert['rating'], self::VALID_MPA_RATINGS, true ) ) {
				return $cert['rating'];
			}
		}

		return 'Unrated';
	}

	/**
	 * Extract the first trailer video ID from video data.
	 *
	 * @param array|\WP_Error $video_data The videos API response.
	 * @return string The trailer video ID, or empty string if none found.
	 */
	private function extract_trailer_id( $video_data ): string {
		if ( is_wp_error( $video_data ) || empty( $video_data['videos'] ) ) {
			return '';
		}

		foreach ( $video_data['videos'] as $video ) {
			if ( isset( $video['type'] ) && 'trailer' === $video['type'] && ! empty( $video['id'] ) ) {
				return $video['id'];
			}
		}

		return '';
	}

	/**
	 * Assign genre taxonomy terms to a movie post.
	 *
	 * @param int   $post_id The movie post ID.
	 * @param array $genres  Array of genre name strings from the API.
	 */
	private function assign_genres( int $post_id, array $genres ): void {
		if ( empty( $genres ) ) {
			return;
		}

		$term_ids = [];

		foreach ( $genres as $genre_name ) {
			$term = get_term_by( 'name', $genre_name, Genre::TAXONOMY_NAME );

			if ( ! $term ) {
				$result = wp_insert_term( $genre_name, Genre::TAXONOMY_NAME );

				if ( ! is_wp_error( $result ) ) {
					$term_ids[] = $result['term_id'];
				}
			} else {
				$term_ids[] = $term->term_id;
			}
		}

		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $post_id, $term_ids, Genre::TAXONOMY_NAME );
		}
	}
}
