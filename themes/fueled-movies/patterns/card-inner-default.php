<?php
/**
 * Title: Card Inner - Default
 * Slug: tenup-theme/card-inner-default
 * Description: Inner content for the default variant of the tenup/card block.
 * Inserter: false
 *
 * @package TenupBlockTheme
 */

?>

<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|12","padding":{"top":"var:preset|spacing|12","bottom":"var:preset|spacing|12","left":"var:preset|spacing|12","right":"var:preset|spacing|12"}},"border":{"radius":"10px"}},"backgroundColor":"background-transparent-5","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch","flexWrap":"nowrap","verticalAlignment":"space-between"},"lock":{"move":true,"remove":true}} -->
<div class="wp-block-group alignwide has-background-transparent-5-background-color has-background" style="border-radius:10px;padding-top:var(--wp--preset--spacing--12);padding-right:var(--wp--preset--spacing--12);padding-bottom:var(--wp--preset--spacing--12);padding-left:var(--wp--preset--spacing--12)">

	<!-- wp:group {"align":"wide","className":"is-style-default","style":{"spacing":{"blockGap":"var:preset|spacing|12"},"layout":{"selfStretch":"fill","flexSize":null},"border":{"width":"0px","style":"none","radius":{"topLeft":"0px","topRight":"0px","bottomLeft":"8px","bottomRight":"8px"}}},"fontSize":"small","layout":{"type":"flex","orientation":"vertical","verticalAlignment":"space-between","flexWrap":"nowrap","justifyContent":"stretch"},"lock":{"move":true,"remove":true}} -->
	<div class="wp-block-group alignwide is-style-default has-small-font-size" style="border-style:none;border-width:0px;border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-left-radius:8px;border-bottom-right-radius:8px">

		<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|4"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"},"lock":{"move":true,"remove":true}} -->
		<div class="wp-block-group alignwide">

			<!-- wp:post-title {"isLink":true,"align":"wide","style":{"spacing":{"margin":{"top":"0","right":"0","bottom":"0","left":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|text-primary"},":hover":{"color":{"text":"var:preset|color|text-primary"}}}}},"fontSize":"heading-4","lock":{"move":true,"remove":true}} /-->

			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|8"}},"layout":{"type":"flex","orientation":"vertical"}} -->
			<div class="wp-block-group">

				<!-- wp:post-date /-->
				<!-- wp:post-terms {"term":"category"} /-->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

	<!-- wp:post-featured-image {"aspectRatio":"2/3","width":"","height":"","style":{"border":{"radius":"5px"}},"lock":{"move":true,"remove":true}} /-->

</div>
<!-- /wp:group -->
