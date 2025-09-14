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

$biography = get_post_meta( get_the_ID(), 'tenup_person_biography', true ) ?? '';

if ( '' === $biography ) {
	return;
}

?>

<dt><?php esc_html_e( 'Biography', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $biography ); ?></dd>
