<?php
/**
 * Person Metadata: Birthplace markup
 *
 * @package tenup\Blocks\PersonMetadataBirthplace
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

$birthplace = get_post_meta( $post_id, 'tenup_person_birthplace', true ) ?? '';

if ( '' === $birthplace ) {
	return;
}

?>

<dt><?php esc_html_e( 'Birthplace', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $birthplace ); ?></dd>
