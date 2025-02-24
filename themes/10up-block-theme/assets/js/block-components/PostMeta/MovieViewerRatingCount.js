/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MovieViewerRatingCount component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieViewerRatingCount = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_viewer_rating_count" {...postMetaProps}>
			{(meta, setMeta) => (
				<NumberControl
					label={__('Viewer Rating Count', 'tenup')}
					onChange={(value) => setMeta(value)}
					value={meta}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default MovieViewerRatingCount;
