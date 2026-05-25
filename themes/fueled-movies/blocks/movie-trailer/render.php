<?php
/**
 * Movie Trailer block markup.
 *
 * @package TenupBlockTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$post_id = $block->context['postId'] ?? null;

if ( ! $post_id ) {
	return;
}

$trailer_id         = get_post_meta( $post_id, 'tenup_movie_trailer_id', true );
$embed_url          = 'https://www.imdb.com/video/embed/' . $trailer_id . '/';
$placeholder_url    = get_theme_file_uri( 'blocks/movie-trailer/placeholder.png' );
$wrapper_attributes = get_block_wrapper_attributes();

if ( empty( $trailer_id ) ) : ?>
	<figure <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<img src="<?php echo esc_url( $placeholder_url ); ?>" alt="<?php esc_attr_e( 'Trailer not available', 'tenup-block-theme' ); ?>" />
	</figure>
	<?php
else :
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<iframe
			src="<?php echo esc_url( $embed_url ); ?>"
			allowfullscreen
			loading="lazy"
			title="<?php echo esc_attr__( 'Movie trailer', 'tenup-block-theme' ); ?>"
			style="width:100%;aspect-ratio:16/9;height:auto;border:0;"
		></iframe>
	</div>
	<?php
endif;
