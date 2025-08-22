<?php
/**
 * PersonBirthplace post meta
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostMeta;

use TenUpPlugin\PostTypes\Person;

/**
 * PersonBirthplace meta field.
 */
class PersonBirthplace extends AbstractPostMeta {

	/**
	 * The meta_key name.
	 *
	 * @var string
	 */
	const META_KEY = 'tenup_person_birthplace';

	/**
	 * Get the field description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Person Birthplace', 'tenup' );
	}

	/**
	 * Get the post types.
	 *
	 * @return array
	 */
	public function get_post_types(): array {
		return [
			Person::POST_TYPE,
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
