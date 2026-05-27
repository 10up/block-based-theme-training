import { registerBlockType } from '@wordpress/blocks';

import { BlockEdit } from './edit';
import { Icon } from './icon';
import metadata from './block.json';

registerBlockType(metadata, {
	icon: Icon,
	edit: BlockEdit,
	save: () => null,
});
