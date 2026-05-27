<?php
/**
 * Content Connect relationship wiring.
 *
 * @package TenUpPlugin\CLI\Utils
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * RelationshipManager class.
 */
class RelationshipManager {

	const RELATIONSHIP_NAME = 'movie_person';

	/**
	 * Create a movie-to-person relationship via Content Connect.
	 *
	 * @param int $movie_post_id  The movie post ID.
	 * @param int $person_post_id The person post ID.
	 * @param int $order          The star order index (0-based).
	 * @return bool True on success or if already exists, false on failure.
	 */
	public function connect_movie_person( int $movie_post_id, int $person_post_id, int $order ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'post_to_post';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE id1 = %d AND id2 = %d AND name = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$movie_post_id,
				$person_post_id,
				self::RELATIONSHIP_NAME
			)
		);

		if ( $exists ) {
			return true;
		}

		// Content Connect stores both directions for bidirectional queries.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$forward = $wpdb->insert(
			$table,
			[
				'id1'   => $movie_post_id,
				'id2'   => $person_post_id,
				'name'  => self::RELATIONSHIP_NAME,
				'order' => $order,
			],
			[ '%d', '%d', '%s', '%d' ]
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inverse = $wpdb->insert(
			$table,
			[
				'id1'   => $person_post_id,
				'id2'   => $movie_post_id,
				'name'  => self::RELATIONSHIP_NAME,
				'order' => 0,
			],
			[ '%d', '%d', '%s', '%d' ]
		);

		return false !== $forward && false !== $inverse;
	}
}
