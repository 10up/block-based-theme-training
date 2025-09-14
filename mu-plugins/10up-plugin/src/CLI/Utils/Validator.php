<?php
/**
 * Validation utilities for IMDB import.
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * Validation utilities.
 */
class Validator {

	/**
	 * Check if IMDB ID is a valid movie ID.
	 *
	 * @param string $imdb_id IMDB ID to validate.
	 * @return bool
	 */
	public static function is_movie_id( $imdb_id ) {
		return preg_match( '/^tt\d{7,8}$/', $imdb_id );
	}

	/**
	 * Check if IMDB ID is a valid person ID.
	 *
	 * @param string $imdb_id IMDB ID to validate.
	 * @return bool
	 */
	public static function is_person_id( $imdb_id ) {
		return preg_match( '/^nm\d{7,8}$/', $imdb_id );
	}

	/**
	 * Check if IMDB ID is valid (movie or person).
	 *
	 * @param string $imdb_id IMDB ID to validate.
	 * @return bool
	 */
	public static function is_valid_imdb_id( $imdb_id ) {
		return self::is_movie_id( $imdb_id ) || self::is_person_id( $imdb_id );
	}

	/**
	 * Validate movie data from API response.
	 *
	 * @param array $data Movie data from API.
	 * @return bool|string True if valid, error message if invalid.
	 */
	public static function validate_movie_data( $data ) {
		if ( ! is_array( $data ) ) {
			return 'Invalid data format';
		}

		if ( empty( $data['primaryTitle'] ) ) {
			return 'Missing primary title';
		}

		if ( empty( $data['id'] ) ) {
			return 'Missing IMDB ID';
		}

		return true;
	}

	/**
	 * Validate person data from API response.
	 *
	 * @param array $data Person data from API.
	 * @return bool|string True if valid, error message if invalid.
	 */
	public static function validate_person_data( $data ) {
		if ( ! is_array( $data ) ) {
			return 'Invalid data format';
		}

		if ( empty( $data['displayName'] ) ) {
			return 'Missing display name';
		}

		if ( empty( $data['id'] ) ) {
			return 'Missing IMDB ID';
		}

		return true;
	}
}
