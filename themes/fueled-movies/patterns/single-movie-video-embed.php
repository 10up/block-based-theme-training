<?php
/**
 * Title: Single Tenup Video Embed
 * Slug: tenup-theme/single-movie-video-embed
 * Description: A video embed with a fallback image.
 * Inserter: false
 *
 * @package FueledMoviesTheme
 */

$youtube_id = get_post_meta( get_the_ID(), 'tenup_movie_youtube_id', true );

if ( empty( $youtube_id ) || is_admin() ) :

	echo '<!-- wp:image {"sizeSlug":"large"} -->
		<figure class="wp-block-image size-large"><img src="https://placehold.co/1600x900/2a2721/898989.jpg?text=placeholder" alt=""/></figure>
	<!-- /wp:image -->';

else :

	$url = 'https://www.youtube.com/watch?v=' . $youtube_id;

	echo '<!-- wp:embed {"url":"' . esc_url( $url ) . '","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
		<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
			<div class="wp-block-embed__wrapper">
				' . esc_url( $url ) . '
			</div>
		</figure>
	<!-- /wp:embed -->';

endif;
