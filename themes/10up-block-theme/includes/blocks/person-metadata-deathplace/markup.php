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

$deathplace = get_post_meta( get_the_ID(), 'tenup_person_deathplace', true ) ?? '';

if ( '' === $deathplace ) {
	return;
}

?>

<dt><?php esc_html_e( 'Deathplace', 'tenup' ); ?></dt>
<dd><?php esc_html_e( $deathplace, 'tenup' ); ?></dd>
