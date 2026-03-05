<?php
/**
 * Template Tags
 *
 * @package TenUpBlockTheme
 *
 * This file contains **only** pure functions that relate to templating.
 *
 * Rules:
 * - Functions in this file **must be pure** (i.e., they must not cause side effects).
 * - No hooks, filters, or global state modifications should be added here.
 * - If a function has side effects (e.g., enqueuing scripts, modifying post data, adding filters),
 *   it should be encapsulated within a class in the `src/` directory.
 *
 * A pure function:
 * - Given the same input, it always returns the same output.
 * - Does not modify external state (no global variables, no database writes, etc.).
 * - Does not rely on WordPress hooks or filters.
 *
 * Example of an allowed function:
 * ```php
 * function get_custom_excerpt( string $content, int $length = 50 ): string {
 *     return wp_trim_words( $content, $length );
 * }
 * ```
 *
 * Example of a function **that does not belong here**:
 * ```php
 * function modify_post_title( string $title ): string {
 *     return 'My Great ' . $title;
 * }
 * add_filter( 'the_title', 'modify_post_title' );
 * ```
 *
 * Keeping this file limited to pure functions ensures maintainability and a clear separation of concerns.
 */

namespace TenupBlockTheme;
