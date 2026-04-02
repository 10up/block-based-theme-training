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
 * MovieReleaseYear component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieReleaseYear = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_release_year" {...postMetaProps}>
			{(meta, setMeta) => (
				<NumberControl
					label={__('Release Year', 'tenup-block-theme')}
					min={1900}
					max={2100}
					onChange={(value) => setMeta(value)}
					value={meta}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default MovieReleaseYear;
