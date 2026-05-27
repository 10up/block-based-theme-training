<?php
/**
 * IMDB ID and API response validation.
 *
 * @package TenUpPlugin\CLI\Utils
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * Validator class.
 */
class Validator {

	const TITLE_ID_PATTERN = '/^tt\d{7,8}$/';
	const NAME_ID_PATTERN  = '/^nm\d{7,8}$/';

	/**
	 * Validate an IMDB title ID (tt + 7-8 digits).
	 *
	 * @param string $id The IMDB ID to validate.
	 * @return bool
	 */
	public function is_valid_title_id( string $id ): bool {
		return 1 === preg_match( self::TITLE_ID_PATTERN, $id );
	}

	/**
	 * Validate an IMDB name ID (nm + 7-8 digits).
	 *
	 * @param string $id The IMDB ID to validate.
	 * @return bool
	 */
	public function is_valid_name_id( string $id ): bool {
		return 1 === preg_match( self::NAME_ID_PATTERN, $id );
	}

	/**
	 * Check that a movie API response has all required fields.
	 *
	 * @param array $data The API response data.
	 * @return bool
	 */
	public function has_required_movie_fields( array $data ): bool {
		if ( empty( $data['primaryTitle'] ) ) {
			return false;
		}

		if ( empty( $data['id'] ) ) {
			return false;
		}

		if ( empty( $data['primaryImage']['url'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check that a person API response has all required fields.
	 *
	 * @param array $data The API response data.
	 * @return bool
	 */
	public function has_required_person_fields( array $data ): bool {
		if ( empty( $data['displayName'] ) ) {
			return false;
		}

		if ( empty( $data['id'] ) ) {
			return false;
		}

		if ( empty( $data['primaryImage']['url'] ) ) {
			return false;
		}

		return true;
	}
}
