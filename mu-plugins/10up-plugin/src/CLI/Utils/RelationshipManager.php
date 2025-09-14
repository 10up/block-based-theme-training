<?php
/**
 * Relationship Manager for WP Content Connect
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Utils;

use wpdb;

/**
 * RelationshipManager utility class.
 */
class RelationshipManager {

	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Create a movie_person relationship.
	 *
	 * @param int $movie_id Movie post ID.
	 * @param int $person_id Person post ID.
	 * @param int $order Order of the relationship.
	 * @return bool True if successful, false otherwise.
	 */
	public function create_movie_person_relationship( $movie_id, $person_id, $order = 0 ) {
		// Check if relationship already exists.
		if ( $this->relationship_exists( $movie_id, $person_id ) ) {
			return true;
		}

		// Create bidirectional relationships.
		$result1 = $this->wpdb->insert(
			$this->wpdb->prefix . 'post_to_post',
			[
				'id1'   => $movie_id,
				'id2'   => $person_id,
				'name'  => 'movie_person',
				'order' => $order,
			],
			[ '%d', '%d', '%s', '%d' ]
		);

		$result2 = $this->wpdb->insert(
			$this->wpdb->prefix . 'post_to_post',
			[
				'id1'   => $person_id,
				'id2'   => $movie_id,
				'name'  => 'movie_person',
				'order' => $order,
			],
			[ '%d', '%d', '%s', '%d' ]
		);

		return $result1 && $result2;
	}

	/**
	 * Check if a relationship exists.
	 *
	 * @param int $movie_id Movie post ID.
	 * @param int $person_id Person post ID.
	 * @return bool True if relationship exists, false otherwise.
	 */
	public function relationship_exists( $movie_id, $person_id ) {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->wpdb->prefix}post_to_post
				WHERE ((id1 = %d AND id2 = %d) OR (id1 = %d AND id2 = %d))
				AND name = 'movie_person'",
				$movie_id,
				$person_id,
				$person_id,
				$movie_id
			)
		);

		return 0 < $count;
	}

	/**
	 * Delete a movie_person relationship.
	 *
	 * @param int $movie_id Movie post ID.
	 * @param int $person_id Person post ID.
	 * @return bool True if successful, false otherwise.
	 */
	public function delete_movie_person_relationship( $movie_id, $person_id ) {
		$result1 = $this->wpdb->delete(
			$this->wpdb->prefix . 'post_to_post',
			[
				'id1'  => $movie_id,
				'id2'  => $person_id,
				'name' => 'movie_person',
			],
			[ '%d', '%d', '%s' ]
		);

		$result2 = $this->wpdb->delete(
			$this->wpdb->prefix . 'post_to_post',
			[
				'id1'  => $person_id,
				'id2'  => $movie_id,
				'name' => 'movie_person',
			],
			[ '%d', '%d', '%s' ]
		);

		return $result1 !== false && $result2 !== false;
	}

	/**
	 * Get all people related to a movie.
	 *
	 * @param int $movie_id Movie post ID.
	 * @return array Array of person post IDs.
	 */
	public function get_movie_people( $movie_id ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT id2 as person_id, `order` FROM {$this->wpdb->prefix}post_to_post
				WHERE id1 = %d AND name = 'movie_person'
				ORDER BY `order` ASC",
				$movie_id
			)
		);

		return array_map( fn( $row ) => (int) $row->person_id, $results );
	}

	/**
	 * Get all movies related to a person.
	 *
	 * @param int $person_id Person post ID.
	 * @return array Array of movie post IDs.
	 */
	public function get_person_movies( $person_id ) {
		$results = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT id2 as movie_id, `order` FROM {$this->wpdb->prefix}post_to_post
				WHERE id1 = %d AND name = 'movie_person'
				ORDER BY `order` ASC",
				$person_id
			)
		);

		return array_map( fn( $row ) => (int) $row->movie_id, $results );
	}

	/**
	 * Update star cast relationships for a movie.
	 *
	 * @param int   $movie_id Movie post ID.
	 * @param array $person_ids Array of person post IDs in order.
	 * @return bool True if successful, false otherwise.
	 */
	public function update_movie_star_cast( $movie_id, $person_ids ) {
		// Get current relationships.
		$current_people = $this->get_movie_people( $movie_id );

		// Remove relationships that are no longer in the new list.
		foreach ( $current_people as $person_id ) {
			if ( ! in_array( $person_id, $person_ids, true ) ) {
				$this->delete_movie_person_relationship( $movie_id, $person_id );
			}
		}

		// Add new relationships.
		foreach ( $person_ids as $order => $person_id ) {
			$this->create_movie_person_relationship( $movie_id, $person_id, $order );
		}

		return true;
	}
}
