<?php
/**
 * Movie Metadata: Stars markup
 *
 * @todo Make this dynamic.
 *
 * @package tenup\Blocks\MovieMetadataStars
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$stars = [
	[
		'name' => 'Marlon Brando',
		'link' => '#',
	],
	[
		'name' => 'Al Pacino',
		'link' => '#',
	],
];

if ( ! is_array( $stars ) || empty( $stars ) ) {
	return;
}

$stars = array_map(
	function ( $star ) {
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $star['link'] ),
			esc_html( $star['name'] )
		);
	},
	$stars
);
$stars = implode( ', ', $stars );

?>

<dt><?php esc_html_e( 'Stars', 'tenup' ); ?></dt>
<dd><?php echo wp_kses_post( $stars ); ?></dd>
