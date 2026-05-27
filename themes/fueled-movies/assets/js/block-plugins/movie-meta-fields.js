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
import MovieIMDBID from '../block-components/PostMeta/MovieIMDBID';
import MoviePlot from '../block-components/PostMeta/MoviePlot';
import MovieMPARating from '../block-components/PostMeta/MovieMPARating';
import MovieReleaseYear from '../block-components/PostMeta/MovieReleaseYear';
import MovieRuntime from '../block-components/PostMeta/MovieRuntime';
import MovieViewerRating from '../block-components/PostMeta/MovieViewerRating';
import MovieViewerRatingCount from '../block-components/PostMeta/MovieViewerRatingCount';
import MovieTrailerID from '../block-components/PostMeta/MovieTrailerID';

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
			title={__('Movie Information', 'tenup-block-theme')}
		>
			<Flex direction="column">
				<MovieIMDBID />
				<MovieTrailerID />
				<MovieReleaseYear />
				<MovieMPARating />
				<MovieRuntime />
				<MovieViewerRating />
				<MovieViewerRatingCount />
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
