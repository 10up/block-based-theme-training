/* global TenupMovieRating */

/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MovieRating component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieRating = ({ postMetaProps, ...restProps }) => {
	const options = Object.entries(TenupMovieRating.options).map(([key, value]) => ({
		label: value,
		value: key,
	}));

	return (
		<PostMeta metaKey="tenup_movie_rating" {...postMetaProps}>
			{(meta, setMeta) => (
				<SelectControl
					label={__('Rating', 'tenup')}
					value={meta}
					options={options}
					onChange={(value) => setMeta(value)}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default MovieRating;
