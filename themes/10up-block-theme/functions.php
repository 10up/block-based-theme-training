<?php
/**
 * Theme constants and setup functions
 *
 * @package TenupBlockTheme
 */

// Useful global constants.
define( 'TENUP_BLOCK_THEME_VERSION', '1.0.0' );
define( 'TENUP_BLOCK_THEME_TEMPLATE_URL', get_template_directory_uri() );
define( 'TENUP_BLOCK_THEME_PATH', get_template_directory() . '/' );
define( 'TENUP_BLOCK_THEME_DIST_PATH', TENUP_BLOCK_THEME_PATH . 'dist/' );
define( 'TENUP_BLOCK_THEME_DIST_URL', TENUP_BLOCK_THEME_TEMPLATE_URL . '/dist/' );
define( 'TENUP_BLOCK_THEME_INC', TENUP_BLOCK_THEME_PATH . 'includes/' );
define( 'TENUP_BLOCK_THEME_BLOCK_DIST_DIR', TENUP_BLOCK_THEME_DIST_PATH . '/blocks/' );

// Require Composer autoloader if it exists.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

$is_local_env = in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
$is_local_url = strpos( home_url(), '.test' ) || strpos( home_url(), '.local' );
$is_local     = $is_local_env || $is_local_url;

if ( $is_local && file_exists( __DIR__ . '/dist/fast-refresh.php' ) ) {
	require_once __DIR__ . '/dist/fast-refresh.php';
	TenUpToolkit\set_dist_url_path( basename( __DIR__ ), TENUP_BLOCK_THEME_DIST_URL, TENUP_BLOCK_THEME_DIST_PATH );
}

require_once TENUP_BLOCK_THEME_INC . 'core.php';
require_once TENUP_BLOCK_THEME_INC . 'blocks.php';
require_once TENUP_BLOCK_THEME_INC . 'overrides.php';
require_once TENUP_BLOCK_THEME_INC . 'template-tags.php';
require_once TENUP_BLOCK_THEME_INC . 'utility.php';

// Run the setup functions.
TenupBlockTheme\Core\setup();
TenupBlockTheme\Blocks\setup();
TenupBlockTheme\TemplateTags\setup();
