import { useBlockProps } from '@wordpress/block-editor';
import { usePostMetaValue } from '@10up/block-components';

export const BlockEdit = () => {
	const [trailerId] = usePostMetaValue('tenup_movie_trailer_id');
	const blockProps = useBlockProps();

	if (!trailerId) {
		const placeholderUrl = window.tenupMovieTrailer?.placeholderUrl ?? '';
		return (
			<figure {...blockProps}>
				<img src={placeholderUrl} alt="" />
			</figure>
		);
	}

	return (
		<div {...blockProps}>
			<iframe
				src={`https://www.imdb.com/video/embed/${trailerId}/`}
				allowFullScreen
				loading="lazy"
				title="Movie trailer"
				style={{ width: '100%', aspectRatio: '16 / 9', height: 'auto', border: 0 }}
			/>
		</div>
	);
};
