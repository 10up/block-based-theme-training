/**
 * Webpack configuration extending 10up-toolkit.
 *
 * Externalizes @10up/block-components to use the shared bundle,
 * reducing overall bundle size by ~280 KiB per block.
 *
 * @package FueledMoviesTheme
 */

const path = require('path');
const defaultConfigs = require('10up-toolkit/config/webpack.config');

// Absolute path to shared-components source file.
const sharedComponentsPath = path.resolve(__dirname, 'assets/js/shared-components.js');

// Custom external function to externalize @10up/block-components except for shared-components entry.
const customExternalFn = ({ context, request, contextInfo }, callback) => {
	// Get the issuer (file that's doing the import).
	const issuer = contextInfo?.issuer || '';

	// Don't externalize if the issuer is shared-components.js.
	// Check both the full path and just the filename.
	if (issuer === sharedComponentsPath || issuer.endsWith('shared-components.js')) {
		return callback();
	}

	// Also check if we're resolving from within @10up/block-components itself.
	// These internal imports should not be externalized.
	if (context && context.includes('@10up/block-components')) {
		return callback();
	}

	if (request === '@10up/block-components') {
		return callback(null, 'tenupSharedComponents');
	}

	if (request === 'clsx') {
		return callback(null, ['tenupSharedComponents', 'clsx']);
	}

	return callback();
};

// 10up-toolkit returns an array of webpack configs.
// Modify each config to add our custom externals.
module.exports = defaultConfigs.map((config) => {
	const existingExternals = config.externals || [];
	const externalsArray = Array.isArray(existingExternals)
		? existingExternals
		: [existingExternals].filter(Boolean);

	return {
		...config,
		externals: [...externalsArray, customExternalFn],
	};
});
