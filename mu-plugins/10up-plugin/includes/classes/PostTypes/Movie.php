<?php
/**
 * Movie Post Type
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostTypes;

/**
 * Movie post type.
 */
class Movie extends AbstractPostType {

	/**
	 * Get the post type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'tenup-movie';
	}

	/**
	 * Get the singular post type label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return esc_html__( 'Movie', 'tenup-plugin' );
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
					'slug' => 'movies',
				],
			]
		);
	}

	/**
	 * Get the plural post type label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return esc_html__( 'Movies', 'tenup-plugin' );
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
		return 'dashicons-video-alt';
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
		return [
			'tenup-genre',
			'tenup-keyword',
			'tenup-watch-provider',
		];
	}

	/**
	 * Run any code after the post type has been registered.
	 *
	 * @return void
	 */
	public function after_register() {
		// Register any hooks/filters you need.

		register_post_meta(
			$this->get_name(),
			'tenup-movie-release-date',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-pg-rating',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-runtime',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-plot',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-summary',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-synopsis',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-tagline',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);

		register_post_meta(
			$this->get_name(),
			'tenup-movie-trailer',
			[
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			]
		);
	}
}
