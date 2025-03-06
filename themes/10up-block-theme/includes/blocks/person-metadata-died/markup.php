<?php
/**
 * Person Metadata: Died markup
 *
 * @package tenup\Blocks\PersonMetadataDied
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$died = get_post_meta( get_the_ID(), 'tenup_person_died', true ) ?? '';

if ( '' === $died ) {
	return;
}

$died = date( 'F j, Y', strtotime( $died ) );

?>

<dt><?php esc_html_e( 'Died', 'tenup' ); ?></dt>
<dd><?php esc_html_e( $died, 'tenup' ); ?></dd>
