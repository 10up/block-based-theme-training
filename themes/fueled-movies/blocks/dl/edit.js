import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: ['tenup/dl-item'],
		template: [['tenup/dl-item']],
		templateLock: false,
	});

	return <dl {...innerBlocksProps} />;
};
