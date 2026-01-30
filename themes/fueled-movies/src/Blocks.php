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
