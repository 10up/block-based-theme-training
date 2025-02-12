<?php
/**
 * AbstractPostMeta
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostMeta;

use TenUpPlugin\Module;

/**
 * Abstract class for post meta.
 *
 *  Usage:
 *
 *  class FooPostMeta extends AbstractPostMeta {
 *
 *      public $load_order = 5;
 *
 *      const META_KEY = 'foo_post_meta';
 *
 *      public function get_description(): string {
 *          return __( 'Foo Post Meta', 'tenup' );
 *      }
 *
 *      public function get_post_types() {
 *          return TenUpPlugin\PostTypes\Post::get_name();
 *      }
 *
 *      public function can_register() {
 *          return true;
 *      }
 *  }
 */
abstract class AbstractPostMeta extends Module {

	/**
	 * Used to alter the order in which clases are initialized.
	 *
	 * Lower number will be initialized first.
	 *
	 * @note This has no correlation to the `init` priority. It's just a way to allow certain classes to be initialized before others.
	 *
	 * @var int The priority of the module.
	 */
	public $load_order = 10;

	/**
	 * Whether the field has key value options.
	 *
	 * @var bool
	 */
	protected $has_key_value_options = false;

	/**
	 * The meta_key name.
	 *
	 * @var string|self::META_KEY
	 */
	const META_KEY = self::META_KEY;

	/**
	 * Default value.
	 *
	 * @var array|string|bool|int|null
	 */
	protected $default_value = null;

	/**
	 * Value type.
	 * Allowed options: 'string', 'boolean', 'integer', 'number', 'array', 'object'
	 *
	 * @var string
	 */
	protected $type = 'string';

	/**
	 * Save only single or multiple values.
	 *
	 * @var bool
	 */
	protected $single_value_mode = true;

	/**
	 * Disallow empty values.
	 *
	 * @var bool
	 */
	protected $is_required = false;

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	abstract public function can_register(): bool;

	/**
	 * Get the meta_key name.
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return static::META_KEY;
	}

	/**
	 * Get the field description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Get allowed values for field schema.
	 */
	public function allowed_values(): array {
		return [];
	}

	/**
	 * Post types to register the post meta.
	 *
	 * @return array
	 */
	abstract public function get_post_types(): array;

	/**
	 * Get the options.
	 *
	 * @return array
	 */
	public function get_options() {
		$options = [
			'show_in_rest' => $this->get_schema(),
			'single'       => $this->single_value_mode,
			'type'         => $this->type,
		];
		if ( null !== $this->default_value ) {
			$options['default'] = $this->default_value;
		}
		return $options;
	}

	/**
	 * Get the schema.
	 *
	 * @return array
	 */
	public function get_schema(): array {
		// Handle allowed values if data is provided in key/value pairs.
		if ( $this->has_key_value_options ) {
			$values = $this->allowed_values();
			$values = array_keys( $values );
			$enum   = $values;
		} else {
			$enum = $this->allowed_values();
		}

		return [
			'schema' => [
				'type'        => $this->type,
				'description' => $this->get_description(),
				'required'    => $this->is_required,
				'enum'        => $enum,
			],
		];
	}

	/**
	 * Register all post meta fields.
	 *
	 * @return void
	 */
	public function register_all(): void {
		foreach ( $this->get_post_types() as $post_type ) {
			\register_post_meta(
				$post_type,
				self::get_name(),
				$this->get_options()
			);
		}
	}

	/**
	 * Register hooks and actions.
	 *
	 * @uses $this->get_post_types() to get the post types.
	 * @uses self::get_name() to get the key name.
	 * @return bool
	 */
	public function register() {

		add_action( 'init', [ $this, 'register_all' ], 20 );
		$this->after_register();

		return true;
	}

	/**
	 * Run any code after the post meta has been registered.
	 *
	 * @return void
	 */
	public function after_register() {
		// Register any hooks/filters you need.
	}
}
