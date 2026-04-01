<?php
/**
 * Title: Base Card
 * Slug: tenup-theme/base-card
 * Description: A card pattern with a featured image, title, and contextual metadata. Adapts layout based on post type.
 * Inserter: false
 *
 * @package TenupBlockTheme
 */

$post_type = get_post_type();
$is_movie  = 'tenup-movie' === $post_type;
$is_person = 'tenup-person' === $post_type;

?>

<!-- wp:group {"align":"wide","className":"is-clickable-card","style":{"spacing":{"blockGap":"var:preset|spacing|12","padding":{"top":"var:preset|spacing|12","bottom":"var:preset|spacing|12","left":"var:preset|spacing|12","right":"var:preset|spacing|12"}},"border":{"radius":"10px"}},"backgroundColor":"background-transparent-5","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch","flexWrap":"nowrap","verticalAlignment":"space-between"}} -->
<div class="wp-block-group alignwide is-clickable-card has-background-transparent-5-background-color has-background" style="border-radius:10px;padding-top:var(--wp--preset--spacing--12);padding-right:var(--wp--preset--spacing--12);padding-bottom:var(--wp--preset--spacing--12);padding-left:var(--wp--preset--spacing--12)">

	<!-- wp:post-featured-image {"aspectRatio":"2/3","width":"","height":"","style":{"border":{"radius":"5px"}}} /-->

	<!-- wp:group {"align":"wide","className":"is-style-default","style":{"spacing":{"blockGap":"var:preset|spacing|12"},"layout":{"selfStretch":"fill","flexSize":null},"border":{"width":"0px","style":"none","radius":{"topLeft":"0px","topRight":"0px","bottomLeft":"8px","bottomRight":"8px"}}},"fontSize":"small","layout":{"type":"flex","orientation":"vertical","verticalAlignment":"space-between","flexWrap":"nowrap","justifyContent":"stretch"}} -->
	<div class="wp-block-group alignwide is-style-default has-small-font-size" style="border-style:none;border-width:0px;border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-left-radius:8px;border-bottom-right-radius:8px">

		<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|4"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
		<div class="wp-block-group alignwide">

			<!-- wp:post-title {"isLink":true,"align":"wide","style":{"spacing":{"margin":{"top":"0","right":"0","bottom":"0","left":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|text-primary"},":hover":{"color":{"text":"var:preset|color|text-primary"}}}}},"fontSize":"heading-4"} /-->

			<?php if ( $is_movie ) : ?>

				<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
				<div class="wp-block-group">

					<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"tenup/block-bindings","args":{"key":"viewerRatingLabelTextNumberOnly"}}}}} -->
					<p><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-yellow-primary-color">★</mark> 0.0</p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph -->
					<p>☆ Rate</p>
					<!-- /wp:paragraph -->

				</div>
				<!-- /wp:group -->

			<?php elseif ( ! $is_movie && ! $is_person ) : ?>

				<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|8"}},"layout":{"type":"flex","orientation":"vertical"}} -->
				<div class="wp-block-group">

					<!-- wp:post-date {"fontSize":"minus-1"} /-->
					<!-- wp:post-terms {"term":"category","fontSize":"minus-1"} /-->

				</div>
				<!-- /wp:group -->

			<?php endif; ?>

		</div>
		<!-- /wp:group -->

		<?php if ( $is_movie || $is_person ) : ?>

			<!-- wp:buttons -->
			<div class="wp-block-buttons">

				<!-- wp:button {"width":100,"className":"is-style-secondary"} -->
				<div class="wp-block-button has-custom-width wp-block-button__width-100 is-style-secondary"><a class="wp-block-button__link wp-element-button"><?php echo $is_movie ? '▶ Trailer' : 'View More'; ?></a></div>
				<!-- /wp:button -->

			</div>
			<!-- /wp:buttons -->

		<?php endif; ?>

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
