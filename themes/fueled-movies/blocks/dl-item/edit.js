import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export const BlockEdit = () => {
	const blockProps = useBlockProps();

	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: ['tenup/dt', 'tenup/dd'],
		template: [['tenup/dt'], ['tenup/dd']],
		templateLock: false,
	});

	return <div {...innerBlocksProps} />;
};
