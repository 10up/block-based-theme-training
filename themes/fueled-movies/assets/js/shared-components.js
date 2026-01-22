/**
 * Shared Components Entry Point
 *
 * Bundles @10up/block-components once and exposes it globally.
 * Other scripts reference this via webpack externals.
 *
 * @package FueledMoviesTheme
 */

import * as TenupBlockComponents from '@10up/block-components';
import clsx from 'clsx';

// Expose as global for other scripts.
window.tenupSharedComponents = {
	...TenupBlockComponents,
	clsx,
};
