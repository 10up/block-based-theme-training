<?php
/**
 * Gutenberg Blocks setup
 *
 * @package FueledMoviesTheme
 */

namespace FueledMoviesTheme;

use TenupFramework\Assets\GetAssetInfo;
use TenupFramework\Module;
use TenupFramework\ModuleInterface;
use TenUpPlugin\PostTypes\Movie;
use TenUpPlugin\PostTypes\Person;
use WP_HTML_Tag_Processor;

/**
 * Blocks module.
 *
 * @package FueledMoviesTheme
 */
class Blocks implements ModuleInterface {

	use Module;
	use GetAssetInfo;

	/**
	 * Can this module be registered?
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Register any hooks and filters.
	 *
	 * @return void
	 */
	public function register() {
		$this->setup_asset_vars(
			dist_path: FUELED_MOVIES_THEME_DIST_PATH,
			fallback_version: FUELED_MOVIES_THEME_VERSION
		);
		add_action( 'init', [ $this, 'register_theme_blocks' ], 10, 0 );
		add_action( 'init', [ $this, 'enqueue_theme_block_styles' ], 10, 0 );
		add_filter( 'block_type_metadata', [ $this, 'inject_shared_component_dependency' ], 10, 1 );
		add_action( 'init', [ $this, 'register_block_bindings' ], 11, 0 );
		add_filter( 'render_block_core/post-featured-image', [ $this, 'filter_featured_image_block' ], 10, 3 );
		add_filter( 'render_block', [ $this, 'maybe_add_flex_shrink' ], 10, 3 );

		// Prevents third-party blocks from being suggested in the block inserter.
		remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
	}

	/**
	 * Automatically registers all blocks that are located within the includes/blocks directory
	 *
	 * @return void
	 */
	public function register_theme_blocks() {
		// Register all the blocks in the theme.
		if ( file_exists( FUELED_MOVIES_THEME_BLOCK_DIST_DIR ) ) {
			$block_json_files = glob( FUELED_MOVIES_THEME_BLOCK_DIST_DIR . '*/block.json' );
			$block_names      = [];

			if ( empty( $block_json_files ) ) {
				return;
			}

			foreach ( $block_json_files as $filename ) {
				$block_folder = dirname( $filename );
				$block        = register_block_type_from_metadata( $block_folder );

				if ( ! $block ) {
					continue;
				}

				$block_names[] = $block->name;

				// Add shared-components as a dependency for this block's editor script.
				// This ensures @10up/block-components (via webpack externals) is available.
				if ( ! empty( $block->editor_script_handles ) ) {
					foreach ( $block->editor_script_handles as $handle ) {
						$script = wp_scripts()->query( $handle );
						if ( $script && ! in_array( 'tenup-shared-components', $script->deps, true ) ) {
							$script->deps[] = 'tenup-shared-components';
						}
					}
				}
			}

			add_filter(
				'allowed_block_types_all',
				function ( array|bool $allowed_blocks ) use ( $block_names ): array|bool {
					if ( ! is_array( $allowed_blocks ) ) {
						return $allowed_blocks;
					}
					return array_merge( $allowed_blocks, $block_names );
				}
			);
		}
	}

	/**
	 * Enqueue block specific styles.
	 *
	 * @return void
	 */
	public function enqueue_theme_block_styles() {
		$stylesheets = glob( FUELED_MOVIES_THEME_DIST_PATH . '/blocks/autoenqueue/**/*.css' );

		if ( empty( $stylesheets ) ) {
			return;
		}

		foreach ( $stylesheets as $stylesheet_path ) {
			$block_type = str_replace( FUELED_MOVIES_THEME_DIST_PATH . '/blocks/autoenqueue/', '', $stylesheet_path );
			$block_type = str_replace( '.css', '', $block_type );

			wp_register_style(
				"tenup-theme-{$block_type}",
				FUELED_MOVIES_THEME_DIST_URL . 'blocks/autoenqueue/' . $block_type . '.css',
				$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'dependencies' ),
				$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'version' ),
			);

			wp_enqueue_block_style(
				$block_type,
				[
					'handle' => "tenup-theme-{$block_type}",
					'path'   => $stylesheet_path,
				]
			);

			if ( file_exists( FUELED_MOVIES_THEME_DIST_PATH . 'blocks/autoenqueue/' . $block_type . '.js' ) ) {
				wp_enqueue_script(
					$block_type,
					FUELED_MOVIES_THEME_DIST_URL . 'blocks/autoenqueue/' . $block_type . '.js',
					$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'dependencies' ),
					$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'version' ),
					true
				);
			}
		}
	}

	/**
	 * Inject shared-components script as a dependency for theme blocks.
	 *
	 * This ensures @10up/block-components is loaded before blocks that use it.
	 * The shared-components script provides window.tenupSharedComponents which
	 * blocks reference via webpack externals.
	 *
	 * @param array $metadata Block metadata.
	 * @return array Modified metadata.
	 */
	public function inject_shared_component_dependency( array $metadata ): array {
		// Only modify our theme blocks.
		if ( empty( $metadata['name'] ) || ! str_starts_with( $metadata['name'], 'tenup/' ) ) {
			return $metadata;
		}

		// Store marker for post-registration processing.
		$metadata['_requires_shared_components'] = true;

		return $metadata;
	}

	/**
	 * Register the block bindings for the theme.
	 *
	 * @return void
	 */
	public function register_block_bindings() {
		register_block_bindings_source(
			'tenup/block-bindings',
			array(
				'label'              => __( 'Fueled Movies Theme', 'tenup' ),
				'get_value_callback' => [ $this, 'block_bindings_callback' ],
			)
		);
	}

	/**
	 * Main callback for the 'tenup/block-bindings' source.
	 * Routes to appropriate helper functions based on the key.
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The binding value or null.
	 */
	public function block_bindings_callback( $source_args ) {
		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		switch ( $source_args['key'] ) {
			case 'archiveLinkText':
				return $this->get_archive_link( 'text' );
			case 'archiveLinkUrl':
				return $this->get_archive_link( 'url' );
			case 'movieStars':
				return $this->get_movie_stars();
			case 'personBorn':
				return $this->get_person_born();
			case 'personDied':
				return $this->get_person_died();
			case 'personMovies':
				return $this->get_person_movies();
			case 'viewerRatingLabelText':
				return $this->get_viewer_rating_label( 'text' );
			case 'viewerRatingLabelUrl':
				return $this->get_viewer_rating_label( 'url' );
			default:
				return null;
		}
	}

	/**
	 * Get the archive link text or URL.
	 *
	 * @param string $type The type of value to return ('text' or 'url').
	 * @return string|null The text or URL.
	 */
	private function get_archive_link( $type ) {
		$url         = home_url();
		$referer     = wp_get_referer();
		$archive_url = get_post_type_archive_link( get_post_type() );

		if ( $referer && $archive_url && strpos( $referer, $archive_url ) === 0 ) {
			$url = $referer;
		} elseif ( $archive_url ) {
			$url = $archive_url;
		}

		$value = $url;

		if ( 'text' === $type ) {
			$value = __( '← Back', 'tenup' );
		}

		return $value;
	}

	/**
	 * Get the movie stars as comma-separated linked names.
	 *
	 * @return string|null The linked names HTML or null.
	 */
	private function get_movie_stars() {
		$value   = null;
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		if ( ! function_exists( '\TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
			return $value;
		}

		$star_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

		if ( empty( $star_ids ) ) {
			return $value;
		}

		$stars_query = new \WP_Query(
			[
				'post_type'      => Person::POST_TYPE,
				'post__in'       => $star_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 99,
			]
		);

		if ( ! $stars_query->have_posts() ) {
			return $value;
		}

		$star_links = array_map(
			function ( $star ) {
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_permalink( $star->ID ) ),
					esc_html( $star->post_title )
				);
			},
			$stars_query->posts
		);

		$value = implode( ', ', $star_links );

		return $value;
	}

	/**
	 * Get the person's birth date formatted.
	 *
	 * @return string|null The formatted date or null.
	 */
	private function get_person_born() {
		$value   = null;
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		$born = get_post_meta( $post_id, 'tenup_person_born', true ) ?? '';

		if ( '' === $born ) {
			return $value;
		}

		$value = gmdate( 'F j, Y', strtotime( $born ) );

		return $value;
	}

	/**
	 * Get the person's death date formatted.
	 *
	 * @return string|null The formatted date or null.
	 */
	private function get_person_died() {
		$value   = null;
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		$died = get_post_meta( $post_id, 'tenup_person_died', true ) ?? '';

		if ( '' === $died ) {
			return $value;
		}

		$value = gmdate( 'F j, Y', strtotime( $died ) );

		return $value;
	}

	/**
	 * Get the person's movies as comma-separated linked titles.
	 *
	 * @return string|null The linked titles HTML or null.
	 */
	private function get_person_movies() {
		$value   = null;
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		if ( ! function_exists( '\TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
			return $value;
		}

		$movie_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

		if ( empty( $movie_ids ) ) {
			return $value;
		}

		$movies_query = new \WP_Query(
			[
				'post_type'      => Movie::POST_TYPE,
				'post__in'       => $movie_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 99,
			]
		);

		if ( ! $movies_query->have_posts() ) {
			return $value;
		}

		$movie_links = array_map(
			function ( $movie ) {
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_permalink( $movie->ID ) ),
					esc_html( $movie->post_title )
				);
			},
			$movies_query->posts
		);

		$value = implode( ', ', $movie_links );

		return $value;
	}

	/**
	 * Get the viewer rating label text or URL.
	 *
	 * @param string $type The type of value to return ('text' or 'url').
	 * @return string The text or URL.
	 */
	private function get_viewer_rating_label( $type ) {
		$value = '#';

		if ( 'text' === $type ) {
			$text   = '0/10 (0)';
			$rating = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating', true ) ?? false;
			$count  = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating_count', true ) ?? false;

			if ( false !== $rating && false !== $count ) {
				$count_display = $count;

				if ( $count >= 1000 && $count < 10000 ) {
					$count_display = number_format( round( $count, -2 ) / 1000, 1, '.', '' ) . 'K';
				} elseif ( $count >= 10000 ) {
					$count_display = number_format( round( $count, -3 ) / 1000, 0, '.', '' ) . 'K';
				}

				$text = $rating . '/10 (' . $count_display . ')';
			}

			$star         = '<mark style="background-color: transparent;color:#f5c518" class="has-inline-color">★</mark>';
			$allowed_tags = [
				'mark' => [
					'style' => [],
					'class' => [],
				],
			];

			$value = wp_kses( $star . $text, $allowed_tags );
		}

		return $value;
	}

	/**
	 * Filter the post-featured-image block to add a view transition class based on the featured image ID.
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block The block.
	 * @param WP_Block $parsed_block The parsed block.
	 * @return string
	 */
	public function filter_featured_image_block( $block_content, $block, $parsed_block ) {

		$featured_image_id = get_post_thumbnail_id( $parsed_block->context['postId'] );

		$p = new WP_HTML_Tag_Processor( $block_content );
		$p->next_tag();

		if ( $p->has_class( 'is-style-single-movie-backdrop' ) ) {
			return $block_content;
		}

		$style_attribute       = $p->get_attribute( 'style' );
		$view_transition_style = "view-transition-name: post-featured-image-id-{$featured_image_id};";

		if ( false === strpos( $style_attribute, $view_transition_style ) ) {
			$style_attribute = $view_transition_style . $style_attribute;
		}

		$p->set_attribute( 'style', $style_attribute );

		return $p->get_updated_html();
	}

	/**
	 * Maybe add flex-shrink-0 class.
	 *
	 * Opinionated solution for Fit option in layout controls not behaving as expected.
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/53766
	 *
	 * @param string   $block_content The block content.
	 * @param array    $block The block.
	 * @param WP_Block $parsed_block The parsed block.
	 * @return string
	 */
	public function maybe_add_flex_shrink( $block_content, $block, $parsed_block ) {

		if ( isset( $block['attrs']['style']['layout']['selfStretch'] ) && 'fixed' === $block['attrs']['style']['layout']['selfStretch'] ) {
			$tags = new WP_HTML_Tag_Processor( $block_content );
			$tags->next_tag();
			$tags->add_class( 'flex-shrink-0' );
			$block_content = $tags->get_updated_html();
		}

		return $block_content;
	}
}
