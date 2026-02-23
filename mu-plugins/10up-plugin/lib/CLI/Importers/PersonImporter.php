<?php
/**
 * Person post importer.
 *
 * @package TenUpPlugin\CLI\Importers
 */

namespace TenUpPlugin\CLI\Importers;

use TenUpPlugin\CLI\Utils\IMDBApiClient;
use TenUpPlugin\CLI\Utils\ImageManager;
use TenUpPlugin\CLI\Utils\Validator;
use TenUpPlugin\CLI\Utils\DateFormatter;
use TenUpPlugin\PostTypes\Person;
use TenUpPlugin\PostMeta\PersonIMDBID;
use TenUpPlugin\PostMeta\PersonBorn;
use TenUpPlugin\PostMeta\PersonBirthplace;
use TenUpPlugin\PostMeta\PersonDied;
use TenUpPlugin\PostMeta\PersonDeathplace;
use TenUpPlugin\PostMeta\PersonBiography;
use WP_CLI;
use WP_Query;

/**
 * PersonImporter class.
 */
class PersonImporter {

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
	 * Whether this is a dry run.
	 *
	 * @var bool
	 */
	private bool $dry_run;

	/**
	 * Constructor.
	 *
	 * @param IMDBApiClient $api_client     The API client.
	 * @param ImageManager  $image_manager  The image manager.
	 * @param Validator     $validator      The validator.
	 * @param DateFormatter $date_formatter The date formatter.
	 * @param bool          $dry_run        Whether this is a dry run.
	 */
	public function __construct(
		IMDBApiClient $api_client,
		ImageManager $image_manager,
		Validator $validator,
		DateFormatter $date_formatter,
		bool $dry_run
	) {
		$this->api_client     = $api_client;
		$this->image_manager  = $image_manager;
		$this->validator      = $validator;
		$this->date_formatter = $date_formatter;
		$this->dry_run        = $dry_run;
	}

	/**
	 * Import a single person from star data.
	 *
	 * @param array $star_data A single entry from the movie's stars array.
	 * @return int|false The person post ID (new or existing) or false on failure.
	 */
	public function import( array $star_data ): int|false {
		$person_imdb_id = $star_data['id'] ?? '';

		if ( ! $this->validator->is_valid_name_id( $person_imdb_id ) ) {
			WP_CLI::warning( "  Invalid person IMDB ID: {$person_imdb_id}. Skipping." );
			return false;
		}

		$existing_id = $this->find_existing_person( $person_imdb_id );
		if ( $existing_id ) {
			return $existing_id;
		}

		if ( empty( $star_data['primaryImage']['url'] ) ) {
			$name = $star_data['displayName'] ?? $person_imdb_id;
			WP_CLI::warning( "  Person {$name} ({$person_imdb_id}) has no image. Skipping." );
			return false;
		}

		$person_data = $this->api_client->get_name( $person_imdb_id );

		if ( is_wp_error( $person_data ) ) {
			WP_CLI::warning( "  Failed to fetch person data for {$person_imdb_id}: " . $person_data->get_error_message() );
			return false;
		}

		if ( ! $this->validator->has_required_person_fields( $person_data ) ) {
			WP_CLI::warning( "  Person {$person_imdb_id} missing required fields. Skipping." );
			return false;
		}

		$display_name = $person_data['displayName'];

		if ( $this->dry_run ) {
			WP_CLI::log( "  [DRY RUN] Would create person: {$display_name}" );
			return false;
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => Person::POST_TYPE,
				'post_title'  => $display_name,
				'post_status' => 'publish',
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( "  Failed to create person post for {$display_name}" );
			return false;
		}

		$image_url = $person_data['primaryImage']['url'] ?? $star_data['primaryImage']['url'];
		$this->image_manager->download_and_attach( $image_url, $post_id, $display_name );

		update_post_meta( $post_id, PersonIMDBID::META_KEY, $person_imdb_id );
		update_post_meta( $post_id, PersonBorn::META_KEY, $this->date_formatter->api_date_to_string( $person_data['birthDate'] ?? null ) );
		update_post_meta( $post_id, PersonBirthplace::META_KEY, $person_data['birthLocation'] ?? '' );
		update_post_meta( $post_id, PersonDied::META_KEY, $this->date_formatter->api_date_to_string( $person_data['deathDate'] ?? null ) );
		update_post_meta( $post_id, PersonDeathplace::META_KEY, $person_data['deathLocation'] ?? '' );
		update_post_meta( $post_id, PersonBiography::META_KEY, $person_data['biography'] ?? '' );

		WP_CLI::log( "  Created person: {$display_name}" );

		return $post_id;
	}

	/**
	 * Find an existing person post by IMDB ID.
	 *
	 * @param string $imdb_id The IMDB name ID.
	 * @return int|false The post ID if found, false otherwise.
	 */
	private function find_existing_person( string $imdb_id ): int|false {
		$query = new WP_Query(
			[
				'post_type'      => Person::POST_TYPE,
				'post_status'    => 'any',
				'meta_key'       => PersonIMDBID::META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
}
