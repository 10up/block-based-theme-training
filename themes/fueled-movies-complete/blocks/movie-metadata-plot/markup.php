<?php
/**
 * Movie Metadata: Plot markup
 *
 * @package tenup\Blocks\MovieMetadataPlot
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

$plot = get_post_meta( $post_id, 'tenup_movie_plot', true ) ?? '';

if ( '' === $plot ) {
	return;
}

?>

<dt><?php esc_html_e( 'Plot', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $plot ); ?></dd>
