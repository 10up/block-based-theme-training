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
	add_action( 'init', 'TenupBlockTheme\Blocks\register_block_bindings', 11, 0 );

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

/**
 * Register the block bindings for the theme.
 *
 * @return void
 */
function register_block_bindings() {

	register_block_bindings_source(
		'tenup/archive-link',
		array(
			'label'              => __( 'Archive Link', 'tenup' ),
			'get_value_callback' => 'TenupBlockTheme\Blocks\block_binding_archive_link',
		)
	);

	register_block_bindings_source(
		'tenup/movie-viewer-rating-label',
		array(
			'label'              => __( 'Movie Viewer Rating Label', 'tenup' ),
			'get_value_callback' => 'TenupBlockTheme\Blocks\block_binding_movie_viewer_rating_label',
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
function block_binding_archive_link( $source_args ) {

	if ( ! isset( $source_args['key'] ) ) {
		return null;
	}

	$text = __( '← Back', 'tenup' );
	$url  = get_post_type_archive_link( get_post_type() );

	if ( ! $url ) {
		return null;
	}

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
 * Callback function for the 'tenup/movie-viewer-rating-label' block binding.
 * Displays the movie viewer rating label with a star rating and count. e.g. ★7.5/10 (175K)
 *
 * @param array $source_args The args found in the block metadata to create the binding.
 * @return string|null The text or URL based on the key argument set in the block attributes.
 */
function block_binding_movie_viewer_rating_label( $source_args ) {

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
