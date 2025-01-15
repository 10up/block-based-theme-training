<?php
/**
 * Blocks setup, site hooks and filters.
 *
 * @package TenupBlockTheme
 */

namespace TenupBlockTheme\Blocks;

use WP_HTML_Tag_Processor;

use function TenupBlockTheme\Utility\get_asset_info;

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @return void
 */
function setup() {
	add_action( 'init', 'TenupBlockTheme\Blocks\register_theme_blocks', 10, 0 );
	add_action( 'init', 'TenupBlockTheme\Blocks\enqueue_theme_block_styles', 10, 0 );

	add_filter( 'render_block_core/post-featured-image', 'TenupBlockTheme\Blocks\filter_featured_image_block', 10, 3 );
}

/**
 * Automatically registers all blocks that are located within the includes/blocks directory
 *
 * @return void
 */
function register_theme_blocks() {
	// Register all the blocks in the theme.
	if ( file_exists( TENUP_BLOCK_THEME_BLOCK_DIST_DIR ) ) {
		$block_json_files = glob( TENUP_BLOCK_THEME_BLOCK_DIST_DIR . '*/block.json' );
		$block_names      = [];

		foreach ( $block_json_files as $filename ) {
			$block_folder  = dirname( $filename );
			$block         = register_block_type_from_metadata( $block_folder );
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
 */
function enqueue_theme_block_styles() {
	$stylesheets = glob( TENUP_BLOCK_THEME_DIST_PATH . '/blocks/autoenqueue/**/*.css' );
	foreach ( $stylesheets as $stylesheet_path ) {
		$block_type = str_replace( TENUP_BLOCK_THEME_DIST_PATH . '/blocks/autoenqueue/', '', $stylesheet_path );
		$block_type = str_replace( '.css', '', $block_type );

		wp_register_style(
			"tenup-theme-{$block_type}",
			TENUP_BLOCK_THEME_DIST_URL . 'blocks/autoenqueue/' . $block_type . '.css',
			get_asset_info( 'blocks/autoenqueue/' . $block_type, 'dependencies' ),
			get_asset_info( 'blocks/autoenqueue/' . $block_type, 'version' ),
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
				get_asset_info( 'blocks/autoenqueue/' . $block_type, 'dependencies' ),
				get_asset_info( 'blocks/autoenqueue/' . $block_type, 'version' ),
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
function filter_featured_image_block( $block_content, $block, $parsed_block ) {

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
