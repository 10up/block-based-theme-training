<?php
/**
 * HTTP client for the IMDB API.
 *
 * @package TenUpPlugin\CLI\Utils
 */

namespace TenUpPlugin\CLI\Utils;

use WP_Error;

/**
 * IMDBApiClient class.
 */
class IMDBApiClient {

	const BASE_URL        = 'https://api.imdbapi.dev';
	const REQUEST_TIMEOUT = 30;
	const MAX_RETRIES     = 2;
	const RETRY_BACKOFF   = 1;
	const RATE_LIMIT_WAIT = 5;

	/**
	 * Timestamp of the last request for rate limiting.
	 *
	 * @var float
	 */
	private float $last_request_time = 0.0;

	/**
	 * Fetch movie data.
	 *
	 * @param string $imdb_id The IMDB title ID.
	 * @return array|WP_Error
	 */
	public function get_title( string $imdb_id ): array|WP_Error {
		return $this->request( "/titles/{$imdb_id}" );
	}

	/**
	 * Fetch certificate data.
	 *
	 * @param string $imdb_id The IMDB title ID.
	 * @return array|WP_Error
	 */
	public function get_certificates( string $imdb_id ): array|WP_Error {
		return $this->request( "/titles/{$imdb_id}/certificates" );
	}

	/**
	 * Fetch video data.
	 *
	 * @param string $imdb_id The IMDB title ID.
	 * @return array|WP_Error
	 */
	public function get_videos( string $imdb_id ): array|WP_Error {
		return $this->request( "/titles/{$imdb_id}/videos" );
	}

	/**
	 * Fetch person data.
	 *
	 * @param string $imdb_id The IMDB name ID.
	 * @return array|WP_Error
	 */
	public function get_name( string $imdb_id ): array|WP_Error {
		return $this->request( "/names/{$imdb_id}" );
	}

	/**
	 * Make an HTTP request to the IMDB API with rate limiting and retries.
	 *
	 * @param string $endpoint The API endpoint path.
	 * @return array|WP_Error The decoded JSON response or WP_Error on failure.
	 */
	private function request( string $endpoint ): array|WP_Error {
		$url     = self::BASE_URL . $endpoint;
		$retries = 0;

		while ( $retries <= self::MAX_RETRIES ) {
			$this->rate_limit();

			$response = wp_remote_get(
				$url,
				[
					'timeout'    => self::REQUEST_TIMEOUT,
					'user-agent' => 'FueledMovies-WP-CLI/1.0',
				]
			);

			$this->last_request_time = microtime( true );

			if ( is_wp_error( $response ) ) {
				++$retries;
				if ( $retries > self::MAX_RETRIES ) {
					return $response;
				}
				sleep( self::RETRY_BACKOFF );
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			if ( 429 === $status_code ) {
				\WP_CLI::warning( 'Rate limited (429). Waiting ' . self::RATE_LIMIT_WAIT . 's...' );
				sleep( self::RATE_LIMIT_WAIT );
				++$retries;
				continue;
			}

			if ( $status_code < 200 || $status_code >= 300 ) {
				++$retries;
				if ( $retries > self::MAX_RETRIES ) {
					return new WP_Error(
						'http_error',
						"HTTP {$status_code} for {$url}"
					);
				}
				sleep( self::RETRY_BACKOFF );
				continue;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( null === $data ) {
				return new WP_Error( 'json_error', "Failed to decode JSON from {$url}" );
			}

			return $data;
		}

		return new WP_Error( 'max_retries', "Exhausted retries for {$url}" );
	}

	/**
	 * Enforce rate limiting between API requests.
	 */
	private function rate_limit(): void {
		if ( $this->last_request_time > 0 ) {
			$elapsed = microtime( true ) - $this->last_request_time;
			$delay   = 0.5 - $elapsed;

			if ( $delay > 0 ) {
				usleep( (int) ( $delay * 1000000 ) );
			}
		}
	}
}
