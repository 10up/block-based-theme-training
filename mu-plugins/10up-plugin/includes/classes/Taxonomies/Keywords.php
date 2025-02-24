<?php
/**
 * Keyword Taxonomy
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\Taxonomies;

/**
 * Keyword Taxonomy.
 */
class Keyword extends AbstractTaxonomy {

	/**
	 * Get the taxonomy name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'tenup-keyword';
	}

	/**
	 * Get the singular taxonomy label.
	 *
	 * @return string
	 */
	public static function get_singular_label() {
		return esc_html__( 'Keyword', 'tenup-plugin' );
	}

	/**
	 * Get the plural taxonomy label.
	 *
	 * @return string
	 */
	public static function get_plural_label() {
		return esc_html__( 'Keywords', 'tenup-plugin' );
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
