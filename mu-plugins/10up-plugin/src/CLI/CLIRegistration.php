<?php
/**
 * CLI command registration module.
 *
 * @package TenUpPlugin\CLI
 */

namespace TenUpPlugin\CLI;

use TenupFramework\Module;
use TenupFramework\ModuleInterface;

/**
 * CLIRegistration class.
 *
 * Auto-discovered by the 10up framework. Registers the WP-CLI command
 * only when WP-CLI is available.
 */
class CLIRegistration implements ModuleInterface {

	use Module;

	/**
	 * Load order — run after post types, taxonomies, meta, and relationships.
	 *
	 * @return int
	 */
	public function load_order(): int {
		return 99;
	}

	/**
	 * Only register when running under WP-CLI.
	 *
	 * @return bool
	 */
	public function can_register(): bool {
		return defined( 'WP_CLI' ) && \WP_CLI;
	}

	/**
	 * Register the fueled-movies import command.
	 */
	public function register(): void {
		\WP_CLI::add_command( 'fueled-movies import', FueledMoviesImport::class );
	}
}
