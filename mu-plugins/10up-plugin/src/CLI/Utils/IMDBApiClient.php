<?php
/**
 * IMDB API Client
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Utils;

use WP_Error;

/**
 * IMDB API Client for fetching movie and person data.
 */
class IMDBApiClient {

	/**
	 * Base API URL for movies.
	 *
	 * @var string
	 */
	private const MOVIE_API_URL = 'https://api.imdbapi.dev/titles/';

	/**
	 * Base API URL for people.
	 *
	 * @var string
	 */
	private const PERSON_API_URL = 'https://api.imdbapi.dev/names/';

	/**
	 * Base API URL for movie certificates.
	 *
	 * @var string
	 */
	private const CERTIFICATES_API_URL = 'https://api.imdbapi.dev/titles/';

	/**
	 * Rate limiting delay between requests (in seconds).
	 *
	 * @var float
	 */
	private const RATE_LIMIT_DELAY = 0.5;

	/**
	 * Maximum retry attempts for failed requests.
	 *
	 * @var int
	 */
	private const MAX_RETRIES = 3;

	/**
	 * Cache duration for API responses (in seconds).
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 3600; // 1 hour

	/**
	 * Get movie data from IMDB API.
	 *
	 * @param string $imdb_id IMDB movie ID.
	 * @return array|WP_Error Movie data or error.
	 */
	public function get_movie_data( $imdb_id ) {
		$cache_key   = 'imdb_movie_' . $imdb_id;
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$url      = self::MOVIE_API_URL . $imdb_id;
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( $response, true );

		if ( ! $data || isset( $data['error'] ) ) {
			return new WP_Error( 'api_error', 'Failed to fetch movie data: ' . ( $data['error'] ?? 'Unknown error' ) );
		}

		// Cache the successful response.
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return $data;
	}

	/**
	 * Get person data from IMDB API.
	 *
	 * @param string $imdb_id IMDB person ID.
	 * @return array|WP_Error Person data or error.
	 */
	public function get_person_data( $imdb_id ) {
		$cache_key   = 'imdb_person_' . $imdb_id;
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$url      = self::PERSON_API_URL . $imdb_id;
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( $response, true );

		if ( ! $data || isset( $data['error'] ) ) {
			return new WP_Error( 'api_error', 'Failed to fetch person data: ' . ( $data['error'] ?? 'Unknown error' ) );
		}

		// Cache the successful response.
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return $data;
	}

	/**
	 * Get movie certificates data from IMDB API.
	 *
	 * @param string $imdb_id IMDB movie ID.
	 * @return array|WP_Error Certificates data or error.
	 */
	public function get_movie_certificates( $imdb_id ) {
		$cache_key   = 'imdb_certificates_' . $imdb_id;
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$url      = self::CERTIFICATES_API_URL . $imdb_id . '/certificates';
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( $response, true );

		if ( ! $data || isset( $data['error'] ) ) {
			return new WP_Error( 'api_error', 'Failed to fetch certificates data: ' . ( $data['error'] ?? 'Unknown error' ) );
		}

		// Cache the successful response.
		set_transient( $cache_key, $data, self::CACHE_DURATION );

		return $data;
	}

	/**
	 * Extract US MPA rating from certificates data.
	 *
	 * @param array $certificates_data Certificates API response data.
	 * @return string|null MPA rating or null if not found.
	 */
	public function extract_us_mpa_rating( $certificates_data ) {
		if ( ! isset( $certificates_data['certificates'] ) || ! is_array( $certificates_data['certificates'] ) ) {
			return null;
		}

		foreach ( $certificates_data['certificates'] as $certificate ) {
			// Check if this is a US certificate with "certificate #" or "certificate#" attribute.
			if ( isset( $certificate['country']['code'] ) && 'US' === $certificate['country']['code'] ) {
				if ( isset( $certificate['attributes'] ) && is_array( $certificate['attributes'] ) ) {
					foreach ( $certificate['attributes'] as $attribute ) {
						if ( is_string( $attribute ) && ( strpos( $attribute, 'certificate #' ) !== false || strpos( $attribute, 'certificate#' ) !== false ) ) {
							return $certificate['rating'] ?? null;
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * Make HTTP request with rate limiting and retry logic.
	 *
	 * @param string $url Request URL.
	 * @return string|WP_Error Response body or error.
	 */
	private function make_request( $url ) {
		$args = [
			'timeout'    => 30,
			'user-agent' => 'WordPress IMDB Import CLI/1.0',
			'headers'    => [
				'Accept' => 'application/json',
			],
		];

		$retry_count = 0;

		while ( $retry_count < self::MAX_RETRIES ) {
			// Rate limiting delay.
			if ( $retry_count > 0 ) {
				sleep( 1 );
			} else {
				usleep( self::RATE_LIMIT_DELAY * 1000000 ); // Convert to microseconds.
			}

			$response = wp_remote_get( $url, $args );

			if ( is_wp_error( $response ) ) {
				++$retry_count;
				if ( $retry_count >= self::MAX_RETRIES ) {
					return new WP_Error( 'http_error', 'HTTP request failed: ' . $response->get_error_message() );
				}
				continue;
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 === $response_code ) {
				return wp_remote_retrieve_body( $response );
			}

			if ( 429 === $response_code ) {
				// Rate limited, wait longer before retry.
				++$retry_count;
				if ( $retry_count < self::MAX_RETRIES ) {
					sleep( 5 );
				}
				continue;
			}

			++$retry_count;
			if ( $retry_count >= self::MAX_RETRIES ) {
				return new WP_Error( 'http_error', 'HTTP request failed with status: ' . $response_code );
			}
		}

		return new WP_Error( 'http_error', 'Max retries exceeded' );
	}

	/**
	 * Batch fetch movie data.
	 *
	 * @param array $imdb_ids Array of IMDB movie IDs.
	 * @return array Results array with 'success' and 'errors' keys.
	 */
	public function batch_get_movies( $imdb_ids ) {
		$results = [
			'success' => [],
			'errors'  => [],
		];

		foreach ( $imdb_ids as $imdb_id ) {
			$data = $this->get_movie_data( $imdb_id );

			if ( is_wp_error( $data ) ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $data->get_error_message(),
				];
			} else {
				$results['success'][ $imdb_id ] = $data;
			}
		}

		return $results;
	}

	/**
	 * Batch fetch person data.
	 *
	 * @param array $imdb_ids Array of IMDB person IDs.
	 * @return array Results array with 'success' and 'errors' keys.
	 */
	public function batch_get_people( $imdb_ids ) {
		$results = [
			'success' => [],
			'errors'  => [],
		];

		foreach ( $imdb_ids as $imdb_id ) {
			$data = $this->get_person_data( $imdb_id );

			if ( is_wp_error( $data ) ) {
				$results['errors'][] = [
					'id'      => $imdb_id,
					'message' => $data->get_error_message(),
				];
			} else {
				$results['success'][ $imdb_id ] = $data;
			}
		}

		return $results;
	}
}
