<?php
/**
 * Title: Single Movie Trailer
 * Slug: tenup-theme/single-movie-trailer
 * Description: A trailer embed with a fallback image.
 * Inserter: false
 *
 * @todo: This gets overridden everytime we make template edits and paste back that into the html file.
 * Ensure this gets added to the template before final launch.
 *
 * @package FueledMoviesTheme
 */

$placeholder_image = FUELED_MOVIES_THEME_TEMPLATE_URL . '/patterns/images/placeholder.png';
$trailer_id        = get_post_meta( get_the_ID(), 'tenup_movie_trailer_id', true );
$url               = 'https://www.imdb.com/video/embed/' . $trailer_id . '/';

if ( empty( $trailer_id ) || is_admin() ) :

	echo '<!-- wp:image {"sizeSlug":"large"} -->
		<figure class="wp-block-image size-large"><img src="' . esc_url( $placeholder_image ) . '" alt=""/></figure>
	<!-- /wp:image -->';

else :

	echo '<!-- wp:html -->
		<iframe
			src="' . esc_url( $url ) . '"
			allowfullscreen
			loading="lazy"
			style="width:100%;aspect-ratio:16/9;height:auto;border:0;"
		></iframe>
	<!-- /wp:html -->';

endif;
