import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { parse } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Convert parsed blocks into the `template` array shape that
 * useInnerBlocksProps expects: [name, attributes, innerBlocks].
 *
 * @param {Array} blocks Parsed block objects.
 * @returns {Array} Template array.
 */
const blocksToTemplate = (blocks) =>
	blocks.map((block) => [
		block.name,
		block.attributes,
		blocksToTemplate(block.innerBlocks ?? []),
	]);

export const BlockEdit = (props) => {
	const { attributes } = props;
	const { variant } = attributes;

	// Pull the matching variant pattern from WP's block pattern registry.
	const patternContent = useSelect(
		(select) =>
			select('core')
				.getBlockPatterns?.()
				?.find((pattern) => pattern.name === `tenup-theme/card-inner-${variant}`)
				?.content ?? '',
		[variant],
	);

	// Parse once and convert to InnerBlocks template format.
	const template = useMemo(() => {
		if (!patternContent) {
			return null;
		}
		return blocksToTemplate(parse(patternContent));
	}, [patternContent]);

	const blockProps = useBlockProps({ className: 'is-clickable-card' });
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		template: template ?? [],
		templateLock: 'all',
		renderAppender: false,
	});

	// Defer rendering inner blocks until the pattern resolves, preventing
	// a flash where children mount with an empty template and WP re-runs
	// block-support inline CSS once the real template arrives.
	if (template === null) {
		return <div {...blockProps} />;
	}

	return <div {...innerBlocksProps} />;
};
