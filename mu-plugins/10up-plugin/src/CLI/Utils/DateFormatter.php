<?php
/**
 * Date formatting utilities for IMDB import.
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI\Utils;

/**
 * Date formatting utilities.
 */
class DateFormatter {

	/**
	 * Format birth date from API response.
	 *
	 * @param array $birth_date Birth date array from API.
	 * @return string|null Formatted date or null if invalid.
	 */
	public static function format_birth_date( $birth_date ) {
		if ( ! is_array( $birth_date ) || empty( $birth_date['year'] ) ) {
			return null;
		}

		$year  = (int) $birth_date['year'];
		$month = isset( $birth_date['month'] ) ? (int) $birth_date['month'] : 1;
		$day   = isset( $birth_date['day'] ) ? (int) $birth_date['day'] : 1;

		// Validate date components.
		if ( $year < 1800 || $year > gmdate( 'Y' ) ) {
			return null;
		}

		if ( $month < 1 || $month > 12 ) {
			$month = 1;
		}

		if ( $day < 1 || $day > 31 ) {
			$day = 1;
		}

		// Check if the date is valid.
		if ( ! checkdate( $month, $day, $year ) ) {
			$day = 1; // Fallback to first day of month.
		}

		return sprintf( '%04d-%02d-%02d', $year, $month, $day );
	}

	/**
	 * Format death date from API response.
	 *
	 * @param array $death_date Death date array from API.
	 * @return string|null Formatted date or null if invalid.
	 */
	public static function format_death_date( $death_date ) {
		if ( ! is_array( $death_date ) || empty( $death_date['year'] ) ) {
			return null;
		}

		$year  = (int) $death_date['year'];
		$month = isset( $death_date['month'] ) ? (int) $death_date['month'] : 1;
		$day   = isset( $death_date['day'] ) ? (int) $death_date['day'] : 1;

		// Validate date components.
		if ( $year < 1800 || $year > gmdate( 'Y' ) ) {
			return null;
		}

		if ( $month < 1 || $month > 12 ) {
			$month = 1;
		}

		if ( $day < 1 || $day > 31 ) {
			$day = 1;
		}

		// Check if the date is valid.
		if ( ! checkdate( $month, $day, $year ) ) {
			$day = 1; // Fallback to first day of month.
		}

		return sprintf( '%04d-%02d-%02d', $year, $month, $day );
	}

	/**
	 * Format runtime from seconds to hours/minutes object.
	 *
	 * @param int $runtime_seconds Runtime in seconds.
	 * @return array Runtime object with hours and minutes.
	 */
	public static function format_runtime( $runtime_seconds ) {
		$hours   = floor( $runtime_seconds / 3600 );
		$minutes = floor( ( $runtime_seconds % 3600 ) / 60 );

		return [
			'hours'   => (string) $hours,
			'minutes' => (string) $minutes,
		];
	}
}
