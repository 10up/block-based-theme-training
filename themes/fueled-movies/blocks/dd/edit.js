import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

// Get PHP-filtered config or use defaults.
const getConfig = () => {
	const phpConfig = window.tenupDescriptionListConfig?.dd || {};
	return {
		allowedBlocks: phpConfig.allowedBlocks ?? null,
		template: phpConfig.template ?? [
			['core/paragraph', { placeholder: 'Enter description...' }],
		],
	};
};

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const config = getConfig();

	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: config.allowedBlocks,
		template: config.template,
		templateLock: false,
	});

	return <dd {...innerBlocksProps} />;
};
