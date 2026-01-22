import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: [
			'tenup/movie-metadata-director',
			'tenup/movie-metadata-genre',
			'tenup/movie-metadata-plot',
			'tenup/movie-metadata-stars',
			'tenup/movie-metadata-writer',
		],
		template: [
			['tenup/movie-metadata-director'],
			['tenup/movie-metadata-genre'],
			['tenup/movie-metadata-plot'],
			['tenup/movie-metadata-stars'],
			['tenup/movie-metadata-writer'],
		],
	});

	return <dl {...innerBlocksProps} />;
};
