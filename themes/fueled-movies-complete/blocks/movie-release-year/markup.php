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

$context = $block->context;
$post_id = $context['postId'] ?? null;

if ( ! $post_id ) {
	return;
}

$year = get_post_meta( $post_id, 'tenup_movie_release_year', true ) ?? '';

if ( '' === $year ) {
	return;
}

?>
<p <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo esc_html( $year ); ?>
</p>
