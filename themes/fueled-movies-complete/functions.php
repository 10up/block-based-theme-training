<?php
/**
 * Theme constants and setup functions
 *
 * @package FueledMoviesTheme
 */

// Useful global constants.
define( 'FUELED_MOVIES_THEME_VERSION', '1.0.0' );
define( 'FUELED_MOVIES_THEME_TEMPLATE_URL', get_template_directory_uri() );
define( 'FUELED_MOVIES_THEME_PATH', get_template_directory() . '/' );
define( 'FUELED_MOVIES_THEME_DIST_PATH', FUELED_MOVIES_THEME_PATH . 'dist/' );
define( 'FUELED_MOVIES_THEME_DIST_URL', FUELED_MOVIES_THEME_TEMPLATE_URL . '/dist/' );
define( 'FUELED_MOVIES_THEME_INC', FUELED_MOVIES_THEME_PATH . 'src/' );
define( 'FUELED_MOVIES_THEME_BLOCK_DIST_DIR', FUELED_MOVIES_THEME_DIST_PATH . '/blocks/' );

// Require Composer autoloader if it exists.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	throw new Exception( 'Please run `composer install` in your theme directory.' );
}

$is_local_env = in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
$is_local_url = strpos( home_url(), '.test' ) || strpos( home_url(), '.local' );
$is_local     = $is_local_env || $is_local_url;

if ( $is_local && file_exists( __DIR__ . '/dist/fast-refresh.php' ) ) {
	require_once __DIR__ . '/dist/fast-refresh.php';

	if ( function_exists( 'TenUpToolkit\set_dist_url_path' ) ) {
		TenUpToolkit\set_dist_url_path( basename( __DIR__ ), FUELED_MOVIES_THEME_DIST_URL, FUELED_MOVIES_THEME_DIST_PATH );
	}
}

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/template-tags.php';

$theme_core = new \FueledMoviesTheme\ThemeCore();
$theme_core->setup();
