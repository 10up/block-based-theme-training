<?php
/**
 * Gutenberg Blocks setup
 *
 * @package FueledMoviesTheme
 */

namespace FueledMoviesTheme;

use WP_HTML_Tag_Processor;
use TenupFramework\Assets\GetAssetInfo;
use TenupFramework\Module;
use TenupFramework\ModuleInterface;

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
		add_action( 'init', [ $this, 'register_block_bindings' ], 11, 0 );
		add_filter( 'render_block_core/post-featured-image', [ $this, 'filter_featured_image_block' ], 10, 3 );
		add_filter( 'block_type_metadata', [ $this, 'inject_shared_component_dependency' ], 10, 1 );

		// Prevents third-party blocks from being suggested in the block inserter.
		remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
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
	 * Register the block bindings for the theme.
	 *
	 * @return void
	 */
	public function register_block_bindings() {

		register_block_bindings_source(
			'tenup/archive-link',
			array(
				'label'              => __( 'Archive Link', 'tenup' ),
				'get_value_callback' => [ $this, 'block_binding_archive_link' ],
			)
		);

		register_block_bindings_source(
			'tenup/movie-viewer-rating-label',
			array(
				'label'              => __( 'Movie Viewer Rating Label', 'tenup' ),
				'get_value_callback' => [ $this, 'block_binding_movie_viewer_rating_label' ],
			)
		);

		register_block_bindings_source(
			'tenup/movie-genre',
			array(
				'label'              => __( 'Movie Genre', 'tenup' ),
				'get_value_callback' => [ $this, 'block_binding_movie_genre' ],
			)
		);

		register_block_bindings_source(
			'tenup/movie-stars',
			array(
				'label'              => __( 'Movie Stars', 'tenup' ),
				'get_value_callback' => [ $this, 'block_binding_movie_stars' ],
			)
		);
	}

	/**
	 * Callback function for the 'tenup/archive-link' block binding.
	 * Displays the archive link for the post type.
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The text or URL based on the key argument set in the block attributes.
	 */
	public function block_binding_archive_link( $source_args ) {

		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		// Set home as the fallback URL, but check for a referer or archive URL first.
		$url         = home_url();
		$referer     = wp_get_referer();
		$archive_url = get_post_type_archive_link( get_post_type() );

		// Use referer only if it's a paged version of the archive URL
		if ( $referer && $archive_url && strpos( $referer, $archive_url ) === 0 ) {
			$url = $referer;
		} elseif ( $archive_url ) {
			$url = $archive_url;
		}

		switch ( $source_args['key'] ) {
			case 'text':
				return __( '← Back', 'tenup' );
			case 'url':
				return $url;
			default:
				return null;
		}
	}

	/**
	 * Callback function for the 'tenup/movie-viewer-rating-label' block binding.
	 * Displays the movie viewer rating label with a star rating and count. e.g. ★7.5/10 (175K)
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The text or URL based on the key argument set in the block attributes.
	 */
	public function block_binding_movie_viewer_rating_label( $source_args ) {

		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		// Set default binding values.
		$text = '0/10 (0)';
		$url  = '#';

		// Get post meta.
		$rating = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating', true ) ?? false;
		$count  = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating_count', true ) ?? false;

		if ( false !== $rating && false !== $count ) {

			$count_display = $count;

			switch ( true ) {

				// 1000 - 9999, round to the nearest hundred and format. e.g. 1156 = 1.2K
				case ( $count >= 1000 && $count < 10000 ):
					$count_display = number_format( round( $count, -2 ) / 1000, 1, '.', '' ) . 'K';
					break;

				// 10000+, round to the nearest thousand and format. e.g. 11560 = 12K
				case ( $count >= 10000 ):
					$count_display = number_format( round( $count, -3 ) / 1000, 0, '.', '' ) . 'K';
					break;

				default:
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
		$text         = wp_kses( $star . $text, $allowed_tags );

		switch ( $source_args['key'] ) {
			case 'text':
				return $text;
			case 'url':
				return $url;
			default:
				return null;
		}
	}

	/**
	 * Callback function for the 'tenup/movie-genre' block binding.
	 * Displays the movie genre for the post as comma-separated linked terms.
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The linked terms HTML or null.
	 */
	public function block_binding_movie_genre( $source_args ) {
		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		$terms = get_the_terms( $post_id, 'tenup-genre' );

		if ( false === $terms || is_wp_error( $terms ) ) {
			return null;
		}

		$term_links = array_map(
			function ( $term ) {
				return sprintf(
					'<a href="%s" rel="tag">%s</a>',
					esc_url( get_term_link( $term ) ),
					esc_html( $term->name )
				);
			},
			$terms
		);

		switch ( $source_args['key'] ) {
			case 'content':
				return implode( ', ', $term_links );
			default:
				return null;
		}
	}

	/**
	 * Callback function for the 'tenup/movie-stars' block binding.
	 * Displays the movie stars for the post as comma-separated linked names.
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The linked names HTML or null.
	 */
	public function block_binding_movie_stars( $source_args ) {
		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		if ( ! function_exists( '\TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
			return null;
		}

		$star_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

		if ( empty( $star_ids ) ) {
			return null;
		}

		$stars_query = new \WP_Query(
			[
				'post_type'      => 'tenup-person',
				'post__in'       => $star_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 99,
			]
		);

		if ( ! $stars_query->have_posts() ) {
			return null;
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

		switch ( $source_args['key'] ) {
			case 'content':
				return implode( ', ', $star_links );
			default:
				return null;
		}
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
}
