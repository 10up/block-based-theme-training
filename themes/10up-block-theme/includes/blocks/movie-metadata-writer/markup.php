<?php
/**
 * Movie Metadata: Writers markup
 *
 * @todo Make this dynamic.
 *
 * @package tenup\Blocks\MovieMetadataWriters
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$writers = [
	[
		'name' => 'Jane Doe',
		'link' => '#',
	],
	[
		'name' => 'John Doe',
		'link' => '#',
	],
];

if ( ! is_array( $writers ) || empty( $writers ) ) {
	return;
}

$label     = _n( 'Writer', 'Writers', $writers, 'tenup' );
$writers = array_map(
	function ( $writer ) {
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $writer['link'] ),
			esc_html( $writer['name'] )
		);
	},
	$writers
);
$writers = implode( ', ', $writers );

?>

<dt><?php esc_html_e( $label, 'tenup' ); ?></dt>
<dd><?php echo wp_kses_post( $writers ); ?></dd>
