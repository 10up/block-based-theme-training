import { registerBlockType } from '@wordpress/blocks';
import { video } from '@wordpress/icons';

import { BlockEdit } from './edit';
import metadata from './block.json';

registerBlockType(metadata, {
	icon: video,
	edit: BlockEdit,
	save: () => null,
});
