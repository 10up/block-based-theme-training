<?php
/**
 * Movie Metadata: Stars markup
 *
 * @package tenup\Blocks\MovieMetadataStars
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

// Get the current post ID (movie post).
$post_id = get_the_ID();

if ( ! $post_id ) {
	return;
}

// Use WP Content Connect helper function to get related posts.
if ( ! function_exists( 'TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
	return;
}

$star_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

if ( empty( $star_ids ) ) {
	return;
}

$stars_query = new WP_Query(
	[
		'post_type'      => 'tenup-person',
		'post__in'       => $star_ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 99,
	]
);

if ( ! $stars_query->have_posts() ) {
	return;
}

$stars = $stars_query->posts;

// Format the stars with links to their person pages.
$stars_html = array_map(
	function ( $star ) {
		$person_url = get_permalink( $star->ID );
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $person_url ),
			esc_html( $star->post_title )
		);
	},
	$stars
);

$stars_html = implode( ', ', $stars_html );

?>

<dt><?php esc_html_e( 'Stars', 'tenup' ); ?></dt>
<dd><?php echo wp_kses_post( $stars_html ); ?></dd>
