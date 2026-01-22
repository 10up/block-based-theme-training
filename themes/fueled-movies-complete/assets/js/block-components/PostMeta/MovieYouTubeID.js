/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { externalLink } from '@wordpress/icons';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MovieYouTubeID component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieYouTubeID = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_youtube_id" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextControl
					label={__('YouTube ID', 'tenup')}
					help={
						<>
							{__('Enter the YouTube ID of the trailer.', 'tenup')}
							{meta && (
								<>
									<br />
									<Button
										href={`https://www.youtube.com/watch?v=${meta}`}
										icon={externalLink}
										target="_blank"
										variant="link"
									>
										{__('YouTube', 'tenup')}
									</Button>
								</>
							)}
						</>
					}
					onChange={(value) => setMeta(value)}
					value={meta}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default MovieYouTubeID;
