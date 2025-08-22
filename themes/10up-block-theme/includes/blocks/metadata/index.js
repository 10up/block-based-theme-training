import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import { formatListBullets as icon } from '@wordpress/icons';

import { BlockEdit } from './edit';
import metadata from './block.json';

registerBlockType(metadata, {
	icon,
	edit: BlockEdit,
	save: () => <InnerBlocks.Content />,
});
