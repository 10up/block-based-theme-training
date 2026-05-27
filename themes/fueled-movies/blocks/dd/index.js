import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

import { BlockEdit } from './edit';
import { example } from '../dl/example';
import { Icon } from './icon';
import metadata from './block.json';

registerBlockType(metadata, {
	example,
	icon: Icon,
	edit: BlockEdit,
	save: () => <InnerBlocks.Content />,
});
