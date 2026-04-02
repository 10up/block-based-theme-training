<?php
/**
 * Description List Term markup.
 *
 * @package TenupBlockTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$term_content = $attributes['content'] ?? '';

// Don't render empty term.
if ( empty( $term_content ) ) {
	return;
}

$block_wrapper_attributes = get_block_wrapper_attributes();

?>

<dt <?php echo $block_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo wp_kses_post( $term_content ); ?>
</dt>
