<?php
/**
 * Custom template tags for this theme.
 *
 * This file is for custom template tags only and it should not contain
 * functions that will be used for filtering or adding an action.
 *
 * All functions should be prefixed with TenupBlockTheme in order to prevent
 * pollution of the global namespace and potential conflicts with functions
 * from plugins.
 * Example: `TENUP_BLOCK_THEME_function()`
 *
 * @package TenupBlockTheme\TemplateTags
 */

namespace TenupBlockTheme\TemplateTags;

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @return void
 */
function setup() {
	add_action( 'wp_head', 'TenupBlockTheme\TemplateTags\add_viewport_meta_tag', 10, 0 );
}

/**
 * Add viewport meta tag to head.
 *
 * @return void
 */
function add_viewport_meta_tag() {
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<?php
}
