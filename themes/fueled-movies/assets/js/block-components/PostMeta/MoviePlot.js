/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { TextareaControl } from '@wordpress/components';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MoviePlot component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MoviePlot = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_plot" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextareaControl
					label={__('Plot', 'tenup')}
					onChange={(value) => setMeta(value)}
					value={meta}
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default MoviePlot;
