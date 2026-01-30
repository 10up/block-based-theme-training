import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

import { BlockEdit } from './edit';
import { dlExample } from './example';
import { Icon } from './icon';
import metadata from './block.json';
import { transforms } from './transforms';

registerBlockType(metadata, {
	example: dlExample,
	icon: Icon,
	edit: BlockEdit,
	save: () => <InnerBlocks.Content />,
	transforms,
});
