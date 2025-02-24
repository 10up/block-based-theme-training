<?php
/**
 * MovieRuntime post meta
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostMeta;

use TenUpPlugin\PostTypes\Movie;

/**
 * MovieRuntime meta field.
 */
class MovieRuntime extends AbstractPostMeta {

	/**
	 * The meta_key name.
	 *
	 * @var string
	 */
	const META_KEY = 'tenup_movie_runtime';

	/**
	 * Get the field description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Movie Runtime', 'tenup' );
	}

	/**
	 * Value type.
	 * Allowed options: 'string', 'boolean', 'integer', 'number', 'array', 'object'
	 *
	 * @var string
	 */
	protected $type = 'object';

	/**
	 * Default value.
	 *
	 * @var array|string|bool|int|null
	 */
	protected $default_value = [
		'hours'   => '0',
		'minutes' => '0',
	];

	/**
	 * Get the schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		$schema = parent::get_schema();

		$schema['schema']['properties'] = [
			'hours'   => [
				'type'        => 'string',
				'description' => __( 'Hours', 'tenup' ),
			],
			'minutes' => [
				'type'        => 'string',
				'description' => __( 'Minutes', 'tenup' ),
			],
		];

		return $schema;
	}

	/**
	 * Get the post types.
	 *
	 * @return array
	 */
	public function get_post_types(): array {
		return [
			Movie::get_name(),
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
}
