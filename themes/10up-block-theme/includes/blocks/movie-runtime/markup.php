<?php
/**
 * Movie Runtime markup
 *
 * @package tenup\Blocks\MovieRuntime
 *
 * @var array    $attributes         Block attributes.
 * @var string   $content            Block content.
 * @var WP_Block $block              Block instance.
 */

$is_editor = $attributes['isEditor'] ?? false;
$runtime   = get_post_meta( get_the_ID(), 'tenup_movie_runtime', true ) ?? '';

?>

<?php if ( ! $is_editor ) : ?>
<p <?php echo get_block_wrapper_attributes( [ 'class' => 'wp-block-tenup-movie-runtime' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php endif; ?>


	<?php
	printf(
		'<time datetime="%s">%sh %sm</time>',
		esc_attr( 'PT' . $runtime['hours'] . 'H' . $runtime['minutes'] . 'M' ),
		esc_html( $runtime['hours'] ),
		esc_html( $runtime['minutes'] )
	);
	?>

<?php if ( ! $is_editor ) : ?>
</p>
<?php endif; ?>
