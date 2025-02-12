/**
 * WordPress dependencies.
 */
import { Flex } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

/**
 * External dependencies.
 */
import { usePost } from '@10up/block-components';

/**
 * Internal dependencies.
 */
import MoviePlot from '../block-components/PostMeta/MoviePlot';
import MovieRating from '../block-components/PostMeta/MovieRating';
import MovieReleaseYear from '../block-components/PostMeta/MovieReleaseYear';
import MovieRuntime from '../block-components/PostMeta/MovieRuntime';

/**
 * Adds a Movie meta field panel to the editor.
 *
 * @returns {Function} The meta field panel.
 */
const MovieFields = () => {
	const { postType } = usePost();

	if (postType !== 'tenup-movie') {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="tenup-movie-fields"
			title={__('Movie Information', 'tenup')}
		>
			<Flex direction="column">
				<MovieReleaseYear />
				<MovieRating />
				<MovieRuntime />
				<MoviePlot />
			</Flex>
		</PluginDocumentSettingPanel>
	);
};

/**
 * Register plugin.
 *
 * See https://developer.wordpress.org/block-editor/reference-guides/slotfills/plugin-document-setting-panel/
 */
registerPlugin('tenup-movie-fields', {
	render: MovieFields,
});
