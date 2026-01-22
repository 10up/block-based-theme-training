<?php
/**
 * Movie Runtime markup
 *
 * @package tenup\Blocks\MovieRuntime
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
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
	'<span aria-label="%s">%s</span>',
	esc_html( $hours ) . __( ' hours', 'tenup' ),
	esc_html( $hours ) . 'h'
);

$minutes_tag = sprintf(
	'<span aria-label="%s">%s</span>',
	esc_html( $minutes ) . __( ' minutes', 'tenup' ),
	esc_html( $minutes ) . 'm'
);

?>

<p <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	printf(
		'<time datetime="%s">%s%s</time>',
		esc_attr( 'PT' . $hours . 'H' . $minutes . 'M' ),
		'0' === $hours ? '' : wp_kses_post( $hours_tag ) . ' ',
		wp_kses_post( $minutes_tag )
	);
	?>

</p>
