<?php
/**
 * Movie Metadata: Genre markup
 *
 * @package tenup\Blocks\MovieMetadataGenre
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

use TenUpPlugin\Taxonomies\Genre;

$context = $block->context;
$post_id = $context['postId'] ?? null;

if ( ! $post_id ) {
	return;
}

$terms = get_the_terms( $post_id, Genre::TAXONOMY_NAME );

if ( false === $terms || is_wp_error( $terms ) ) {
	return;
}

?>

<dt><?php echo esc_html( Genre::PLURAL_LABEL ); ?></dt>
<dd><?php echo do_blocks( '<!-- wp:post-terms {"term":"' . Genre::TAXONOMY_NAME . '"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
