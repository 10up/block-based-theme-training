<?php
/**
 * Movie Metadata: Director markup
 *
 * @todo Make this dynamic.
 *
 * @package tenup\Blocks\MovieMetadataDirector
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$directors = [
	[
		'name' => 'Steven Spielberg',
		'link' => '#',
	],
];

if ( ! is_array( $directors ) || empty( $directors ) ) {
	return;
}

$label     = _n( 'Director', 'Directors', $directors, 'tenup' );
$directors = array_map(
	function ( $director ) {
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $director['link'] ),
			esc_html( $director['name'] )
		);
	},
	$directors
);
$directors = implode( ', ', $directors );

?>

<dt><?php esc_html_e( $label, 'tenup' ); ?></dt>
<dd><?php echo wp_kses_post( $directors ); ?></dd>
