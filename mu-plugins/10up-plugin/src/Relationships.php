<?php
/**
 * Set up post to post relationships with WP Content Connect.
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin;

use TenupFramework\ModuleInterface;
use TenUpPlugin\PostTypes\{Movie, Person};

/**
 * Relationships Class.
 */
class Relationships implements ModuleInterface {


	/**
	 * Load order priority.
	 *
	 * @return int
	 */
	public function load_order(): int {
		return 10;
	}

	/**
	 * The posts to post relationships.
	 * Data structure:
	 * 'relationship_name' => [
	 *     'from' => [
	 *         'cpt'       => post type 1
	 *         'enable_ui' => (optional bool),
	 *         'sortable'  => (optional bool),
	 *         'name'      => (optional string),
	 *     ],
	 *     'to' => [
	 *          'cpt'       => post type 2
	 *          'enable_ui' => (optional bool),
	 *          'sortable'  => (optional bool),
	 *          'name'      => (optional string),
	 *     ],
	 * ]
	 *
	 * @var array
	 */
	public static $relationships;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		self::$relationships = [
			'movie_person' => [
				'from' => [
					'cpt'  => Movie::POST_TYPE,
					'name' => __( 'Related People', 'tenup' ),
				],
				'to'   => [
					'cpt'  => Person::POST_TYPE,
					'name' => __( 'Related Movies', 'tenup' ),
				],
			],
		];
	}

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Connects the Module with WordPress using Hooks and/or Filters.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'tenup-content-connect-init', [ get_called_class(), 'define_relationships' ] );
	}

	/**
	 * Create the post to post relationships.
	 *
	 * @param \TenUp\ContentConnect\Registry $registry The Content Connect Registry.
	 * @return void
	 */
	public static function define_relationships( $registry ) {
		foreach ( self::$relationships as $relationship_name => $values ) {

			$registry->define_post_to_post(
				$values['from']['cpt'],
				$values['to']['cpt'],
				$relationship_name,
				[
					'from' => [
						'enable_ui' => $values['from']['enable_ui'] ?? true,
						'sortable'  => $values['from']['sortable'] ?? true,
						'labels'    => [
							'name' => $values['from']['name'] ?? __( 'Related ', 'tenup' ) . ucwords( str_replace( '_', ' ', $values['to']['cpt'] ) ),
						],
					],
					'to'   => [
						'enable_ui' => $values['to']['enable_ui'] ?? true,
						'sortable'  => $values['to']['sortable'] ?? true,
						'labels'    => [
							'name' => $values['to']['name'] ?? __( 'Related ', 'tenup' ) . ucwords( str_replace( '_', ' ', $values['from']['cpt'] ) ),
						],
					],
				],
			);
		}
	}
}
