<?php
/**
 * Description List Item markup
 *
 * @package tenup\Blocks\DescriptionListItem
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 * @var array    $context    Block context.
 */

// Don't render empty item.
if ( empty( trim( $content ) ) ) {
	return;
}

$block_wrapper_attributes = get_block_wrapper_attributes();

?>

<div <?php echo $block_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
