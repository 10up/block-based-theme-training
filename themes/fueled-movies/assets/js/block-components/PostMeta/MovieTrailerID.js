/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { external } from '@wordpress/icons';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * MovieTrailerID component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieTrailerID = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_trailer_id" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextControl
					label={__('IMDB Trailer ID', 'tenup')}
					help={
						<>
							{__('Enter the IMDB video ID of the trailer.', 'tenup')}
							{meta && (
								<>
									<br />
									<Button
										href={`https://www.imdb.com/video/${meta}`}
										icon={external}
										target="_blank"
										variant="link"
									>
										{__('IMDB', 'tenup')}
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

export default MovieTrailerID;
