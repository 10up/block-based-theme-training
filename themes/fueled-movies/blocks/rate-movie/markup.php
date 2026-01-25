<?php
/**
 * Rate Movie block markup
 *
 * @package FueledMoviesTheme
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

$block_wrapper_attributes = get_block_wrapper_attributes(
	[
		'class'               => 'wp-block-button is-style-secondary',
		'data-wp-context'     => wp_json_encode(
			[
				'rating' => null,
			],
			JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		),
		'data-wp-interactive' => 'tenup/rate-movie',
	]
);

?>
<div <?php echo $block_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<button
		aria-controls="rate-movie-popover"
		aria-haspopup="true"
		class="wp-element-button wp-block-tenup-rate-movie__trigger"
		data-wp-bind--aria-expanded="state.isPopoverOpen"
		data-wp-text="state.buttonText"
		popovertarget="rate-movie-popover"
		type="button"
	>
		<?php echo esc_html__( 'Rate', 'tenup' ); ?>
	</button>

	<div
		aria-labelledby="rate-movie-popover-label"
		aria-modal="true"
		class="wp-block-tenup-rate-movie__popover"
		data-wp-class--is-open="state.isPopoverOpen"
		data-wp-init="callbacks.initPopover"
		id="rate-movie-popover"
		popover
		role="dialog"
	>
		<div class="wp-block-tenup-rate-movie__popover-content">
			<label
				class="wp-block-tenup-rate-movie__label"
				for="rate-movie-slider"
				id="rate-movie-popover-label"
			>
				<?php echo esc_html__( 'Rate this movie', 'tenup' ); ?>
			</label>
			<input
				aria-label="<?php echo esc_attr__( 'Movie rating from 1 to 10', 'tenup' ); ?>"
				class="wp-block-tenup-rate-movie__slider"
				data-wp-bind--value="state.sliderValue"
				data-wp-on--input="actions.selectRating"
				id="rate-movie-slider"
				max="10"
				min="1"
				step="1"
				type="range"
			/>
			<span data-wp-text="state.popupRatingText"></span>
			<button
				class="wp-element-button wp-block-tenup-rate-movie__clear"
				data-wp-on--click="actions.clearRating"
				type="button"
			>
				<?php echo esc_html__( 'Clear', 'tenup' ); ?>
			</button>

		</div>
	</div>
</div>
