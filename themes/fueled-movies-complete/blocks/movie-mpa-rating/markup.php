<?php
/**
 * Movie MPA Rating markup
 *
 * @package tenup\Blocks\MovieMPARating
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$rating = get_post_meta( get_the_ID(), 'tenup_movie_mpa_rating', true ) ?? '';

if ( '' === $rating ) {
	return;
}

?>

<p <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo esc_html( $rating ); ?>
</p>
