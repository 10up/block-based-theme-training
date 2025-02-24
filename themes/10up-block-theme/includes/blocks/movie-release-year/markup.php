<?php
/**
 * Movie Release Year markup
 *
 * @package tenup\Blocks\MovieReleaseYear
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$year = get_post_meta( get_the_ID(), 'tenup_movie_release_year', true ) ?? '';

if ( '' === $year ) {
	return;
}

?>
<p <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo esc_html( $year ); ?>
</p>
