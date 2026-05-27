/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { BaseControl, TimePicker } from '@wordpress/components';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MovieRuntime component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieRuntime = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_runtime" {...postMetaProps}>
			{(meta, setMeta) => (
				<BaseControl
					id="tenup-movie-runtime"
					label={__('Runtime', 'tenup-block-theme')}
					help={__('In hours & minutes', 'tenup-block-theme')}
				>
					<TimePicker.TimeInput
						onChange={(value) => {
							setMeta({
								hours: String(value.hours),
								minutes: String(value.minutes),
							});
						}}
						value={meta}
						{...restProps}
					/>
				</BaseControl>
			)}
		</PostMeta>
	);
};

export default MovieRuntime;
