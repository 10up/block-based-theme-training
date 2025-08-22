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

$birthplace = get_post_meta( get_the_ID(), 'tenup_person_birthplace', true ) ?? '';

if ( '' === $birthplace ) {
	return;
}

?>

<dt><?php esc_html_e( 'Birthplace', 'tenup' ); ?></dt>
<dd><?php esc_html_e( $birthplace, 'tenup' ); ?></dd>
