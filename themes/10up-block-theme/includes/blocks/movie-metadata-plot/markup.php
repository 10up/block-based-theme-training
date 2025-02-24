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

$plot = get_post_meta( get_the_ID(), 'tenup_movie_plot', true ) ?? '';

if ( '' === $plot ) {
	return;
}

?>

<dt><?php esc_html_e( 'Plot', 'tenup' ); ?></dt>
<dd><?php esc_html_e( $plot, 'tenup' ); ?></dd>
