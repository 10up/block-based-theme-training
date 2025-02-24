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

$terms = get_the_terms( get_the_ID(), Genre::get_name() );

if ( false === $terms || is_wp_error( $terms ) ) {
	return;
}

?>

<dt><?php esc_html_e( Genre::get_plural_label(), 'tenup' ); ?></dt>
<dd><?php echo do_blocks( '<!-- wp:post-terms {"term":"'. Genre::get_name() .'"} /-->'); ?></dd>
