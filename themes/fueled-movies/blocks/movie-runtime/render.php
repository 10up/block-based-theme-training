<?php
/**
 * Movie Runtime markup.
 *
 * @package TenupBlockTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$context = $block->context;
$post_id = $context['postId'] ?? null;

if ( ! $post_id ) {
	return;
}

$runtime = get_post_meta( $post_id, 'tenup_movie_runtime', true ) ?? '';

if ( '' === $runtime ) {
	return;
}

$hours   = $runtime['hours'] ?? '0';
$minutes = $runtime['minutes'] ?? '0';

if ( '0' === $hours && '0' === $minutes ) {
	return;
}

$hours_tag = sprintf(
	'<span aria-label="%s">%s</span> ',
	esc_html( $hours ) . __( ' hours', 'tenup-block-theme' ),
	esc_html( $hours ) . 'h'
);

$minutes_tag = sprintf(
	'<span aria-label="%s">%s</span>',
	esc_html( $minutes ) . __( ' minutes', 'tenup-block-theme' ),
	esc_html( $minutes ) . 'm'
);

$output_parts = [];

if ( '0' !== $hours ) {
	$output_parts[] = $hours_tag;
}

$output_parts[] = $minutes_tag;
$output         = implode( '&nbsp;', $output_parts );

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'datetime' => esc_attr( 'PT' . $hours . 'H' . $minutes . 'M' ),
	]
);

?>

<time <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></time>
