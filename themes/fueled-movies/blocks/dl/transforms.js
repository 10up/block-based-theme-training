import { createBlock } from '@wordpress/blocks';

export const transforms = {
	from: [
		{
			type: 'block',
			blocks: ['core/details'],
			transform: (attributes, innerBlocks) => {
				const term = attributes?.summary || '';
				const dtBlock = createBlock('tenup/dt', { content: term });
				const ddBlock = createBlock('tenup/dd', {}, innerBlocks);
				const item = createBlock('tenup/dl-item', {}, [dtBlock, ddBlock]);
				return createBlock('tenup/dl', {}, [item]);
			},
		},
		{
			type: 'block',
			blocks: ['core/list'],
			transform: (attributes, innerBlocks) => {
				const items = innerBlocks.map((listItem) => {
					const content = listItem.attributes?.content || '';
					const dtBlock = createBlock('tenup/dt', { content });
					const ddBlock = createBlock('tenup/dd', {}, [
						createBlock('core/paragraph', { content: '' }),
					]);
					return createBlock('tenup/dl-item', {}, [dtBlock, ddBlock]);
				});
				return createBlock('tenup/dl', {}, items);
			},
		},
		{
			type: 'block',
			blocks: ['core/paragraph'],
			isMultiBlock: true,
			transform: (paragraphs) => {
				const items = paragraphs.map((paragraph) => {
					const content = paragraph.attributes?.content || '';
					const dtBlock = createBlock('tenup/dt', { content });
					const ddBlock = createBlock('tenup/dd', {}, [
						createBlock('core/paragraph', { content: '' }),
					]);
					return createBlock('tenup/dl-item', {}, [dtBlock, ddBlock]);
				});
				return createBlock('tenup/dl', {}, items);
			},
		},
		{
			type: 'block',
			blocks: ['core/table'],
			transform: (attributes) => {
				const items = [];
				const rows = attributes?.body || [];

				rows.forEach((row) => {
					const cells = row?.cells || [];
					if (cells.length >= 1) {
						const term = cells[0]?.content || '';
						const dtBlock = createBlock('tenup/dt', { content: term });
						// Combine all remaining columns into description paragraphs.
						const descriptionBlocks = cells.slice(1).map((cell) =>
							createBlock('core/paragraph', {
								content: cell?.content || '',
							}),
						);
						// Ensure at least one paragraph if no description columns.
						const innerBlocks = descriptionBlocks.length
							? descriptionBlocks
							: [createBlock('core/paragraph', { content: '' })];
						const ddBlock = createBlock('tenup/dd', {}, innerBlocks);
						items.push(createBlock('tenup/dl-item', {}, [dtBlock, ddBlock]));
					}
				});

				return createBlock('tenup/dl', {}, items);
			},
		},
	],
	to: [
		{
			type: 'block',
			blocks: ['core/details'],
			transform: (attributes, innerBlocks) => {
				// Each item becomes its own details block.
				return innerBlocks.map((item) => {
					// Find the dt block to get the term.
					const dtBlock = item.innerBlocks?.find((block) => block.name === 'tenup/dt');
					const summary = dtBlock?.attributes?.content || '';
					// Find the dd block(s) to get the content.
					const ddBlocks = item.innerBlocks?.filter((block) => block.name === 'tenup/dd');
					const content = ddBlocks?.flatMap((dd) => dd.innerBlocks) || [];
					return createBlock(
						'core/details',
						{ summary },
						content.length ? content : [createBlock('core/paragraph')],
					);
				});
			},
		},
		{
			type: 'block',
			blocks: ['core/list'],
			transform: (attributes, innerBlocks) => {
				const listItems = innerBlocks.map((item) => {
					// Find the dt block to get the term.
					const dtBlock = item.innerBlocks?.find((block) => block.name === 'tenup/dt');
					const term = dtBlock?.attributes?.content || '';
					// Find the dd block(s) to get description content.
					const ddBlocks = item.innerBlocks?.filter((block) => block.name === 'tenup/dd');
					const descriptionParts = ddBlocks
						?.flatMap((dd) => dd.innerBlocks || [])
						.map((block) => block.attributes?.content || '')
						.filter(Boolean);
					const description = descriptionParts?.join(' ') || '';
					// Combine term and description.
					const content = description ? `<strong>${term}</strong>: ${description}` : term;
					return createBlock('core/list-item', { content });
				});
				return createBlock('core/list', {}, listItems);
			},
		},
		{
			type: 'block',
			blocks: ['core/paragraph'],
			transform: (attributes, innerBlocks) => {
				return innerBlocks.map((item) => {
					// Find the dt block to get the term.
					const dtBlock = item.innerBlocks?.find((block) => block.name === 'tenup/dt');
					const term = dtBlock?.attributes?.content || '';
					// Find the dd block(s) to get description content.
					const ddBlocks = item.innerBlocks?.filter((block) => block.name === 'tenup/dd');
					const descriptionParts = ddBlocks
						?.flatMap((dd) => dd.innerBlocks || [])
						.map((block) => block.attributes?.content || '')
						.filter(Boolean);
					const description = descriptionParts?.join(' ') || '';
					// Combine term and description.
					const content = description ? `<strong>${term}</strong>: ${description}` : term;
					return createBlock('core/paragraph', { content });
				});
			},
		},
		{
			type: 'block',
			blocks: ['core/table'],
			transform: (attributes, innerBlocks) => {
				const body = innerBlocks.map((item) => {
					// Find the dt block to get the term.
					const dtBlock = item.innerBlocks?.find((block) => block.name === 'tenup/dt');
					const term = dtBlock?.attributes?.content || '';
					// First cell is the term.
					const cells = [{ content: term, tag: 'td' }];
					// Find the dd block(s) to get description content.
					const ddBlocks = item.innerBlocks?.filter((block) => block.name === 'tenup/dd');
					ddBlocks?.forEach((dd) => {
						dd.innerBlocks?.forEach((block) => {
							const content = block.attributes?.content || '';
							cells.push({ content, tag: 'td' });
						});
					});
					return { cells };
				});
				return createBlock('core/table', {
					hasFixedLayout: false,
					head: [],
					body,
					foot: [],
				});
			},
		},
	],
};
