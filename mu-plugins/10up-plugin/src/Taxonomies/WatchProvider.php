<?php
/**
 * Watch Provider Taxonomy
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Taxonomies;

use TenupFramework\Taxonomies\AbstractTaxonomy;

/**
 * Watch Provider Taxonomy.
 */
class WatchProvider extends AbstractTaxonomy {

	/**
	 * Taxonomy name constant.
	 */
	const TAXONOMY_NAME = 'tenup-watch-provider';

	/**
	 * Singular label constant.
	 */
	const SINGULAR_LABEL = 'Watch Provider';

	/**
	 * Plural label constant.
	 */
	const PLURAL_LABEL = 'Watch Providers';

	/**
	 * Load order priority.
	 *
	 * @return int
	 */
	public function load_order(): int {
		return 10;
	}

	/**
	 * Get the taxonomy name.
	 *
	 * @return string
	 */
	public function get_name() {
		return self::TAXONOMY_NAME;
	}

	/**
	 * Get the singular taxonomy label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return self::SINGULAR_LABEL;
	}

	/**
	 * Get the plural taxonomy label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return self::PLURAL_LABEL;
	}

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
	}
}
