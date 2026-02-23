<?php
/**
 * Date and runtime conversion helpers.
 *
 * @package TenUpPlugin\CLI\Utils
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * DateFormatter class.
 */
class DateFormatter {

	/**
	 * Convert an API date object to YYYY-MM-DD string.
	 *
	 * @param array|null $date_obj The API date object with year, month, day keys.
	 * @return string The formatted date string, or empty string if invalid/null.
	 */
	public function api_date_to_string( ?array $date_obj ): string {
		if ( null === $date_obj || empty( $date_obj['year'] ) ) {
			return '';
		}

		$year  = (int) $date_obj['year'];
		$month = ! empty( $date_obj['month'] ) ? (int) $date_obj['month'] : 1;
		$day   = ! empty( $date_obj['day'] ) ? (int) $date_obj['day'] : 1;

		$current_year = (int) gmdate( 'Y' );

		if ( $year < 1800 || $year > $current_year ) {
			return '';
		}

		if ( ! checkdate( $month, $day, $year ) ) {
			return '';
		}

		return sprintf( '%04d-%02d-%02d', $year, $month, $day );
	}

	/**
	 * Convert runtime in seconds to the object format expected by tenup_movie_runtime.
	 *
	 * @param int $seconds The runtime in seconds.
	 * @return array Associative array with 'hours' and 'minutes' as string values.
	 */
	public function runtime_seconds_to_object( int $seconds ): array {
		if ( $seconds <= 0 ) {
			return [
				'hours'   => '0',
				'minutes' => '0',
			];
		}

		$hours   = (string) floor( $seconds / 3600 );
		$minutes = (string) floor( ( $seconds % 3600 ) / 60 );

		return [
			'hours'   => $hours,
			'minutes' => $minutes,
		];
	}
}
