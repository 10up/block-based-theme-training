/**
 * WordPress dependencies.
 */

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';
import { Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { external } from '@wordpress/icons';

/**
 * MovieIMDBID component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const MovieIMDBID = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_movie_imdb_id" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextControl
					label={__('IMDB ID', 'tenup-block-theme')}
					help={
						<>
							{__('Enter the IMDB ID of the movie.', 'tenup-block-theme')}
							{meta && (
								<>
									<br />
									<Button
										href={`https://www.imdb.com/title/${meta}`}
										icon={external}
										target="_blank"
										variant="link"
									>
										{__('IMDB', 'tenup-block-theme')}
									</Button>
									<Button
										href={`https://api.imdbapi.dev/titles/${meta}`}
										icon={external}
										target="_blank"
										variant="link"
									>
										{__('JSON', 'tenup-block-theme')}
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

export default MovieIMDBID;
