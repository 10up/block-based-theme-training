<?php
/**
 * Person Metadata: Deathplace markup
 *
 * @package tenup\Blocks\PersonMetadataDeathplace
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

$deathplace = get_post_meta( $post_id, 'tenup_person_deathplace', true ) ?? '';

if ( '' === $deathplace ) {
	return;
}

?>

<dt><?php esc_html_e( 'Deathplace', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $deathplace ); ?></dd>
