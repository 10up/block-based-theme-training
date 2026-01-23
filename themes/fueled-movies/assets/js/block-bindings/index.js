import { registerBlockBindingsSource } from '@wordpress/blocks';

registerBlockBindingsSource({
	name: 'tenup/movie-genre',
	label: 'Movie Genre',
	useContext: ['postId', 'postType'],
	getValues: ({ bindings }) => {
		// this getValues assumes you're on a paragraph
		if (bindings.content?.args?.key === 'content') {
			return {
				content: 'hello world',
			};
		}
		return {
			content: bindings.content,
		};
	},

	getFieldsList() {
		return [
			{
				label: 'Movie Genre',
				type: 'string',
				args: {
					key: 'content',
				},
			},
		];
	},
});

registerBlockBindingsSource({
	name: 'tenup/movie-stars',
	label: 'Movie Stars',
	useContext: ['postId', 'postType'],
	getValues: ({ bindings }) => {
		if (bindings.content?.args?.key === 'content') {
			return {
				content: 'hello world',
			};
		}
		return {
			content: bindings.content,
		};
	},
	getFieldsList() {
		return [
			{
				label: 'Movie Stars',
				type: 'string',
				args: {
					key: 'content',
				},
			},
		];
	},
});
