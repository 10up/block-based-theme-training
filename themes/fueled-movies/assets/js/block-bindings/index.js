import { registerBlockBindingsSource } from '@wordpress/blocks';

registerBlockBindingsSource({
	name: 'tenup/block-bindings',
	label: 'Fueled Movies Theme',
	usesContext: ['postId', 'postType'],
	getValues({ bindings }) {
		if (bindings.text?.args?.key === 'archiveLinkText') {
			return {
				text: '← Back',
			};
		}

		if (bindings.url?.args?.key === 'archiveLinkUrl') {
			return {
				url: '#',
			};
		}

		if (bindings.content?.args?.key === 'movieStars') {
			return {
				content: 'Placeholder Stars',
			};
		}

		if (bindings.content?.args?.key === 'personBorn') {
			return {
				content: 'January 1, 1970',
			};
		}

		if (bindings.content?.args?.key === 'personDied') {
			return {
				content: 'January 1, 2000',
			};
		}

		if (bindings.content?.args?.key === 'personMovies') {
			return {
				content: 'Placeholder Movies',
			};
		}

		if (bindings.text?.args?.key === 'viewerRatingLabelText') {
			return {
				text: '★ 0/10 (0)',
			};
		}

		if (bindings.url?.args?.key === 'viewerRatingLabelUrl') {
			return {
				url: '#',
			};
		}

		return {};
	},
	getFieldsList() {
		return [
			{
				label: 'Archive Link Text',
				type: 'string',
				args: {
					key: 'archiveLinkText',
				},
			},
			{
				label: 'Archive Link URL',
				type: 'string',
				args: {
					key: 'archiveLinkUrl',
				},
			},
			{
				label: 'Movie Stars',
				type: 'string',
				args: {
					key: 'movieStars',
				},
			},
			{
				label: 'Person Born',
				type: 'string',
				args: {
					key: 'personBorn',
				},
			},
			{
				label: 'Person Died',
				type: 'string',
				args: {
					key: 'personDied',
				},
			},
			{
				label: 'Person Movies',
				type: 'string',
				args: {
					key: 'personMovies',
				},
			},
			{
				label: 'Viewer Rating Label Text',
				type: 'string',
				args: {
					key: 'viewerRatingLabelText',
				},
			},
			{
				label: 'Viewer Rating Label URL',
				type: 'string',
				args: {
					key: 'viewerRatingLabelUrl',
				},
			},
		];
	},
});
