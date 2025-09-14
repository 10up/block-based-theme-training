<?php
/**
 * CLI Registration
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\CLI;

use TenUpPlugin\CLI\IMDBImport;

/**
 * CLI Registration class.
 */
class CLIRegistration {

	/**
	 * Register CLI commands.
	 *
	 * @return void
	 */
	public static function register_commands() {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command( 'imdb-import', IMDBImport::class );
	}
}
