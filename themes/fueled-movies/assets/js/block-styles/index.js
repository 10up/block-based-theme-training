import { unregisterBlockStyle, registerBlockStyle } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';

/**
 * Remove Core Block Style Variations
 */
function removeCoreBlockStyleVariations() {
	/* ---- Core Button Block ---- */
	unregisterBlockStyle('core/button', 'fill');
	unregisterBlockStyle('core/button', 'outline');

	/* ---- Core Quote Block ---- */
	unregisterBlockStyle('core/quote', 'default');
	unregisterBlockStyle('core/quote', 'plain');
	unregisterBlockStyle('core/quote', 'large');

	/* ---- Core Table Block ---- */
	unregisterBlockStyle('core/table', 'regular');
	unregisterBlockStyle('core/table', 'stripes');

	/* ---- Core Image Block ---- */
	unregisterBlockStyle('core/image', 'default');
	unregisterBlockStyle('core/image', 'rounded');

	/* ---- Core Separator Block ---- */
	unregisterBlockStyle('core/separator', 'default');
	unregisterBlockStyle('core/separator', 'wide');
	unregisterBlockStyle('core/separator', 'dots');

	/* ---- Core Site Logo Block ---- */
	unregisterBlockStyle('core/site-logo', 'default');
	unregisterBlockStyle('core/site-logo', 'rounded');
}

/**
 * Register TenUp Block Styles
 */
function registerTenUpBlockStyles() {
	/* ---- Core Button Block ---- */
	registerBlockStyle('core/button', {
		name: 'primary',
		label: 'Primary',
		isDefault: true,
	});
	registerBlockStyle('core/button', {
		name: 'secondary',
		label: 'Secondary',
	});
}

domReady(() => {
	removeCoreBlockStyleVariations();
	registerTenUpBlockStyles();
});
