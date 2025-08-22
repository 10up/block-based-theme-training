<?php
/**
 * Page Post Type
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostTypes;

use TenupFramework\PostTypes\AbstractCorePostType;

/**
 * Page Post Type
 *
 * This class is a placeholder for the core Page post type.
 * It's here to allow engineers to extend the core Page post type in the same way as custom post types.
 */
class Page extends AbstractCorePostType {

	/**
	 * Post type name constant.
	 */
	const POST_TYPE = 'page';

	/**
	 * Singular label constant.
	 */
	const SINGULAR_LABEL = 'Page';

	/**
	 * Plural label constant.
	 */
	const PLURAL_LABEL = 'Pages';

	/**
	 * Load order priority.
	 *
	 * @return int
	 */
	public function load_order(): int {
		return 10;
	}

	/**
	 * Get the post type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return self::POST_TYPE;
	}

	/**
	 * Returns the default supported taxonomies. The subclass should declare the
	 * Taxonomies that it supports here if required.
	 *
	 * @return array<string>
	 */
	public function get_supported_taxonomies() {
		return [];
	}

	/**
	 * Run any code after the post type has been registered.
	 *
	 * @return void
	 */
	public function after_register() {
		// Do nothing.
	}
}
