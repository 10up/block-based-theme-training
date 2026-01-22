<?php
/**
 * Person Metadata: Biography markup
 *
 * @package tenup\Blocks\PersonMetadataBiography
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

$biography = get_post_meta( $post_id, 'tenup_person_biography', true ) ?? '';

if ( '' === $biography ) {
	return;
}

?>

<dt><?php esc_html_e( 'Biography', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $biography ); ?></dd>
