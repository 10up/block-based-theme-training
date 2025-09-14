<?php
/**
 * MovieMPARating post meta
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostMeta;

use TenUpPlugin\PostTypes\Movie;

/**
 * MovieMPARating meta field.
 */
class MovieMPARating extends AbstractPostMeta {

	/**
	 * The meta_key name.
	 *
	 * @var string
	 */
	const META_KEY = 'tenup_movie_mpa_rating';

	/**
	 * Get the field description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Movie MPA Rating', 'tenup' );
	}

	/**
	 * Default value.
	 *
	 * @var array|string|bool|int|null
	 */
	protected $default_value = 'Unrated';

	/**
	 * Whether the field has key value options.
	 *
	 * @var bool
	 */
	protected $has_key_value_options = true;

	/**
	 * Get the post types.
	 *
	 * @return array
	 */
	public function get_post_types(): array {
		return [
			Movie::POST_TYPE,
		];
	}

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	public function can_register(): bool {
		return true;
	}

	/**
	 * Add localized script.
	 *
	 * @return void
	 */
	public function add_localized_script() {

		wp_localize_script(
			'tenup_plugin_admin',
			'TenupMovieMPARating',
			array(
				'options' => [
					'Not Rated' => 'Not Rated',
					'Approved'  => 'Approved',
					'G'         => 'G',
					'PG'        => 'PG',
					'PG-13'     => 'PG-13',
					'R'         => 'R',
					'NC-17'     => 'NC-17',
				],
			)
		);
	}

	/**
	 * Run any code after the post meta has been registered.
	 *
	 * @return void
	 */
	public function after_register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'add_localized_script' ], 11 );
	}
}
