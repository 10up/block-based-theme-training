<?php
/**
 * Person Post Type
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostTypes;

use TenupFramework\PostTypes\AbstractPostType;

/**
 * Person post type.
 */
class Person extends AbstractPostType {

	/**
	 * Post type name constant.
	 */
	const POST_TYPE = 'tenup-person';

	/**
	 * Singular label constant.
	 */
	const SINGULAR_LABEL = 'Person';

	/**
	 * Plural label constant.
	 */
	const PLURAL_LABEL = 'People';

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
	 * Get the singular post type label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return self::SINGULAR_LABEL;
	}

	/**
	 * Get the plural post type label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return self::PLURAL_LABEL;
	}

	/**
	 * Get the menu icon for the post type.
	 *
	 * This can be a base64 encoded SVG, a dashicons class or 'none' to leave it empty so it can be filled with CSS.
	 *
	 * @see https://developer.wordpress.org/resource/dashicons/
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return 'dashicons-groups';
	}

	/**
	 * Can the class be registered?
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
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
	 * Get the options for the post type.
	 *
	 * @return array{
	 *      labels?: array<string, string>,
	 *      description?: string,
	 *      public?: bool,
	 *      hierarchical?: bool,
	 *      exclude_from_search?: bool,
	 *      publicly_queryable?: bool,
	 *      show_ui?: bool,
	 *      show_in_menu?: bool,
	 *      show_in_nav_menus?: bool,
	 *      show_in_admin_bar?: bool,
	 *      menu_position?: int,
	 *      menu_icon?: string,
	 *      capability_type?: string|array<int, string>,
	 *      capabilities?: array<string, string>,
	 *      map_meta_cap?: bool,
	 *      supports?: array<string>|false,
	 *      register_meta_box_cb?: callable,
	 *      taxonomies?: array<string>,
	 *      has_archive?: bool|string,
	 *      rewrite?: bool|array{
	 *          slug?: string,
	 *          with_front?: bool,
	 *          feeds?: bool,
	 *          pages?: bool,
	 *          ep_mask?: int,
	 *      },
	 *      query_var?: bool|string,
	 *      can_export?: bool,
	 *      delete_with_user?: bool,
	 *      show_in_rest?: bool,
	 *      rest_base?: string,
	 *      rest_namespace?: string,
	 *      rest_controller_class?: string,
	 *      _builtin?: bool,
	 *      template?: array<array<string, mixed>>,
	 *      template_lock?: string|false,
	 *  }
	 */
	public function get_options() {
		$options = parent::get_options();

		return array_merge(
			$options,
			[
				'rewrite' => [
					'slug' => 'people',
				],
			]
		);
	}

	/**
	 * Set default archive ordering to title A-Z.
	 *
	 * @return void
	 */
	public function after_register() {
		add_action( 'pre_get_posts', [ $this, 'order_archive_by_title' ] );
	}

	/**
	 * Order person archive queries by title ascending.
	 *
	 * @param \WP_Query $query The query object.
	 * @return void
	 */
	public function order_archive_by_title( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( self::POST_TYPE ) ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}
	}

	/**
	 * Default post type supported feature names.
	 *
	 * @return array
	 */
	public function get_editor_supports() {
		$options  = parent::get_editor_supports();
		$supports = array_merge( $options, [ 'custom-fields' ] );

		return $supports;
	}
}
