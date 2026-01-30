/**
 * Shared example for Description List blocks.
 */

// Single dl-item example used by child blocks.
export const dlItemExample = {
	name: 'tenup/dl-item',
	attributes: {
		layout: {
			type: 'flex',
			orientation: 'vertical',
		},
	},
	innerBlocks: [
		{
			name: 'tenup/dt',
			attributes: {
				content: 'Monday - Friday',
			},
		},
		{
			name: 'tenup/dd',
			innerBlocks: [
				{
					name: 'core/paragraph',
					attributes: {
						content: '9:00 AM - 5:00 PM',
					},
				},
			],
		},
	],
};

// Full example for child blocks showing parent context.
export const example = {
	attributes: {
		layout: {
			type: 'flex',
			orientation: 'vertical',
		},
	},
	innerBlocks: [dlItemExample],
};

// Extended example for dl block with multiple items.
export const dlExample = {
	attributes: {
		layout: {
			type: 'flex',
			orientation: 'vertical',
		},
	},
	innerBlocks: [
		dlItemExample,
		{
			...dlItemExample,
			innerBlocks: [
				{
					name: 'tenup/dt',
					attributes: {
						content: 'Saturday',
					},
				},
				{
					name: 'tenup/dd',
					innerBlocks: [
						{
							name: 'core/paragraph',
							attributes: {
								content: '10:00 AM - 2:00 PM',
							},
						},
					],
				},
			],
		},
		{
			...dlItemExample,
			innerBlocks: [
				{
					name: 'tenup/dt',
					attributes: {
						content: 'Sunday',
					},
				},
				{
					name: 'tenup/dd',
					innerBlocks: [
						{
							name: 'core/paragraph',
							attributes: {
								content: 'Closed',
							},
						},
					],
				},
			],
		},
	],
};
