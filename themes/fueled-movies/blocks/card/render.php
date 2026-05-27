<?php
/**
 * Card block markup.
 *
 * @package TenupBlockTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$post_id = $block->context['postId'] ?? null;
$variant = $attributes['variant'] ?? 'default';

if ( ! $post_id ) {
	return;
}

$allowed_variants = [ 'default', 'movie', 'person' ];

if ( ! in_array( $variant, $allowed_variants, true ) ) {
	$variant = 'default';
}

$pattern_slug = sprintf( 'tenup-theme/card-inner-%s', $variant );

?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'is-clickable-card' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo do_blocks( sprintf( '<!-- wp:pattern {"slug":"%s"} /-->', esc_attr( $pattern_slug ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
