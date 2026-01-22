<?php
/**
 * Person Metadata: Movies markup
 *
 * @package tenup\Blocks\PersonMetadataMovies
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

// Use WP Content Connect helper function to get related posts.
if ( ! function_exists( 'TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
	return;
}

$movies_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

if ( empty( $movies_ids ) ) {
	return;
}

$movies_query = new WP_Query(
	[
		'post_type'      => 'tenup-movie',
		'post__in'       => $movies_ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 99,
	]
);

if ( ! $movies_query->have_posts() ) {
	return;
}

$movies = $movies_query->posts;

// Format the movies with links to their movie pages.
$movies_html = array_map(
	function ( $movie ) {
		$movie_url = get_permalink( $movie->ID );
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $movie_url ),
			esc_html( $movie->post_title )
		);
	},
	$movies
);

$movies_html = implode( ', ', $movies_html );

?>

<dt><?php esc_html_e( 'Movies', 'tenup' ); ?></dt>
<dd><?php echo wp_kses_post( $movies_html ); ?></dd>
