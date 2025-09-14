<?php
/**
 * MovieYouTubeID post meta
 *
 * @package TenUpPlugin
 */

namespace TenUpPlugin\PostMeta;

use TenUpPlugin\PostTypes\Movie;

/**
 * MovieYouTubeID meta field.
 */
class MovieYouTubeID extends AbstractPostMeta {

	/**
	 * The meta_key name.
	 *
	 * @var string
	 */
	const META_KEY = 'tenup_movie_youtube_id';

	/**
	 * Get the field description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Movie YouTube ID', 'tenup' );
	}

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
}
