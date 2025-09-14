<?php
/**
 * Person Metadata: Born markup
 *
 * @package tenup\Blocks\PersonMetadataBorn
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$born = get_post_meta( get_the_ID(), 'tenup_person_born', true ) ?? '';

if ( '' === $born ) {
	return;
}

$born = gmdate( 'F j, Y', strtotime( $born ) );

?>

<dt><?php esc_html_e( 'Born', 'tenup' ); ?></dt>
<dd><?php echo esc_html( $born ); ?></dd>
