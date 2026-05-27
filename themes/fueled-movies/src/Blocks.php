<?php
/**
 * Gutenberg Blocks setup
 *
 * @package TenupBlockTheme
 */

namespace TenupBlockTheme;

use TenupFramework\Assets\GetAssetInfo;
use TenupFramework\Module;
use TenupFramework\ModuleInterface;
use WP_HTML_Tag_Processor;

/**
 * Blocks module.
 *
 * @package TenupBlockTheme
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
			dist_path: TENUP_BLOCK_THEME_DIST_PATH,
			fallback_version: TENUP_BLOCK_THEME_VERSION
		);
		add_action( 'init', [ $this, 'register_theme_blocks' ], 10, 0 );
		add_action( 'init', [ $this, 'enqueue_theme_block_styles' ], 10, 0 );
		add_action( 'enqueue_block_editor_assets', [ $this, 'localize_block_editor_data' ] );
		add_filter( 'render_block_core/post-featured-image', [ $this, 'filter_featured_image_block' ], 10, 3 );
		add_filter( 'render_block_core/button', [ $this, 'neutralize_empty_button_link' ], 10, 1 );
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
		if ( file_exists( TENUP_BLOCK_THEME_BLOCK_DIST_DIR ) ) {
			$block_json_files = glob( TENUP_BLOCK_THEME_BLOCK_DIST_DIR . '*/block.json' );
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
		$stylesheets = glob( TENUP_BLOCK_THEME_DIST_PATH . '/blocks/autoenqueue/**/*.css' );

		if ( empty( $stylesheets ) ) {
			return;
		}

		foreach ( $stylesheets as $stylesheet_path ) {
			$block_type = str_replace( TENUP_BLOCK_THEME_DIST_PATH . '/blocks/autoenqueue/', '', $stylesheet_path );
			$block_type = str_replace( '.css', '', $block_type );

			wp_register_style(
				"tenup-theme-{$block_type}",
				TENUP_BLOCK_THEME_DIST_URL . 'blocks/autoenqueue/' . $block_type . '.css',
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

			if ( file_exists( TENUP_BLOCK_THEME_DIST_PATH . 'blocks/autoenqueue/' . $block_type . '.js' ) ) {
				wp_enqueue_script(
					$block_type,
					TENUP_BLOCK_THEME_DIST_URL . 'blocks/autoenqueue/' . $block_type . '.js',
					$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'dependencies' ),
					$this->get_asset_info( 'blocks/autoenqueue/' . $block_type, 'version' ),
					true
				);
			}
		}
	}

	/**
	 * Inject per-block data into the editor so JS previews can use server-derived URLs.
	 *
	 * Runs before each block's editorScript loads, giving `edit.js` access to
	 * a small namespaced global for assets webpack can't emit (e.g. static
	 * images kept next to the block source).
	 *
	 * @return void
	 */
	public function localize_block_editor_data() {
		wp_add_inline_script(
			'tenup-movie-trailer-editor-script',
			sprintf(
				'window.tenupMovieTrailer = %s;',
				wp_json_encode(
					[
						'placeholderUrl' => get_theme_file_uri( 'blocks/movie-trailer/placeholder.png' ),
					]
				)
			),
			'before'
		);
	}

	/**
	 * Filter the post-featured-image block to add a view transition class based on the featured image ID.
	 *
	 * @param string    $block_content The block content.
	 * @param array     $block         The parsed block array.
	 * @param \WP_Block $instance      The block instance.
	 * @return string
	 */
	public function filter_featured_image_block( $block_content, $block, $instance ) {

		if ( empty( $instance->context['postId'] ) ) {
			return $block_content;
		}

		$featured_image_id = get_post_thumbnail_id( $instance->context['postId'] );

		$p = new WP_HTML_Tag_Processor( $block_content );

		if ( ! $p->next_tag() ) {
			return $block_content;
		}

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
	 * Replace href-less core/button anchors with spans so they don't intercept card-level clicks.
	 *
	 * Card patterns include decorative buttons (e.g. "Trailer", "View More") rendered without
	 * an href because the surrounding card is wrapped in a primary link. Leaving the anchor
	 * in place lets it swallow the click and prevents the card-level navigation from firing.
	 *
	 * @param string $block_content The block content.
	 * @return string
	 */
	public function neutralize_empty_button_link( $block_content ) {
		if ( ! str_contains( $block_content, '<a' ) ) {
			return $block_content;
		}

		$block_content = preg_replace_callback(
			'#<a\b([^>]*)>(.*?)</a>#is',
			function ( $matches ) {
				if ( preg_match( '/\bhref\s*=/i', $matches[1] ) ) {
					return $matches[0];
				}
				return '<span' . $matches[1] . '>' . $matches[2] . '</span>';
			},
			$block_content
		);

		return $block_content;
	}

	/**
	 * Maybe add flex-shrink-0 class.
	 *
	 * Opinionated solution for Fit option in layout controls not behaving as expected.
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/53766
	 *
	 * @param string    $block_content The block content.
	 * @param array     $block         The parsed block array.
	 * @param \WP_Block $instance      The block instance.
	 * @return string
	 */
	public function maybe_add_flex_shrink( $block_content, $block, $instance ) {

		if ( isset( $block['attrs']['style']['layout']['selfStretch'] ) && 'fixed' === $block['attrs']['style']['layout']['selfStretch'] ) {
			$tags = new WP_HTML_Tag_Processor( $block_content );

			if ( ! $tags->next_tag() ) {
				return $block_content;
			}

			$tags->add_class( 'flex-shrink-0' );
			$block_content = $tags->get_updated_html();
		}

		return $block_content;
	}
}
