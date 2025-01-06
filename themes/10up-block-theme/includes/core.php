<?php
/**
 * Core setup, site hooks and filters.
 *
 * @package TenupBlockTheme
 */

namespace TenupBlockTheme\Core;

use function TenupBlockTheme\Utility\get_asset_info;

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @return void
 */
function setup() {
	add_action( 'init', 'TenupBlockTheme\Core\scripts' );
	add_action( 'init', 'TenupBlockTheme\Core\register_all_icons', 10 );
	add_action( 'after_setup_theme', 'TenupBlockTheme\Core\i18n' );
	add_action( 'after_setup_theme', 'TenupBlockTheme\Core\theme_setup' );
	add_action( 'wp_head', 'TenupBlockTheme\Core\js_detection', 0 );
	add_action( 'wp_head', 'TenupBlockTheme\Core\scrollbar_detection', 0 );
	add_action( 'wp_enqueue_scripts', 'TenupBlockTheme\Core\styles' );
	add_action( 'enqueue_block_editor_assets', 'TenupBlockTheme\Core\editor_style_overrides' );
}

/**
 * Makes Theme available for translation.
 *
 * Translations can be added to the /languages directory.
 * If you're building a theme based on "tenup-theme", change the
 * filename of '/languages/TenupBlockTheme.pot' to the name of your project.
 *
 * @return void
 */
function i18n() {
	load_theme_textdomain( 'tenup-theme', TENUP_BLOCK_THEME_PATH . '/languages' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function theme_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( '/dist/css/frontend.css' );
	remove_theme_support( 'core-block-patterns' );
}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */
function scripts() {

	wp_enqueue_script(
		'frontend',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/js/frontend.js',
		get_asset_info( 'frontend', 'dependencies' ),
		get_asset_info( 'frontend', 'version' ),
		[
			'strategy' => 'defer',
		]
	);
}


/**
 * Enqueue styles for front-end.
 *
 * @return void
 */
function styles() {
	wp_enqueue_style(
		'tenup-theme-styles',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/css/frontend.css',
		[],
		get_asset_info( 'frontend', 'version' )
	);
}

/**
 * Enqueue styles for editor only.
 *
 * @return void
 */
function editor_style_overrides() {
	wp_enqueue_style(
		'tenup-theme-editor-style-overrides',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/css/editor-style-overrides.css',
		[],
		TENUP_BLOCK_THEME_VERSION
	);

	wp_enqueue_script(
		'tenup-theme-block-extensions',
		TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/js/block-extensions.js',
		get_asset_info( 'block-extensions', 'dependencies' ),
		get_asset_info( 'block-extensions', 'version' ),
		true
	);
}

/**
 * register all icons located in the dist/svg folder
 */
function register_all_icons() {
	if ( ! function_exists( '\UIKitCore\Helpers\register_icons' ) ) {
		return;
	}

	$icon_paths = glob( TENUP_BLOCK_THEME_DIST_PATH . 'svg/*.svg' );
	$icons      = array_map(
		function ( $icon_path ) {
			$icon_name = preg_replace( '#\..*$#', '', basename( $icon_path ) );

			return new \UIKitCore\Icon(
				$icon_name,
				ucwords( str_replace( '-', ' ', $icon_name ) ),
				$icon_path
			);
		},
		$icon_paths
	);

	\UIKitCore\Helpers\register_icons(
		[
			'name'  => 'tenup',
			'label' => 'Theme Icons',
			'icons' => $icons,
		]
	);
}

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @return void
 */
function js_detection() {

	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}

/**
 * Handles scrollbar width detection.
 *
 * Adds a JavaScript event listener to the DOMContentLoaded event. When the DOM is fully loaded,
 * it calculates the width of the scrollbar and sets a CSS variable `--wp--custom--scrollbar-width` with the width.
 * It also adds an event listener to the window resize event to update the scrollbar width when the window is resized.
 *
 * @return void
 */
function scrollbar_detection() {
	echo '<script>window.addEventListener("DOMContentLoaded",()=>{const t=()=>window.innerWidth-document.body.clientWidth;const e=()=>{document.documentElement.style.setProperty("--wp--custom--scrollbar-width",`${t()}px`)};e();});</script>' . "\n";
}
