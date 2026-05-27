import { useBlockProps } from '@wordpress/block-editor';

export const BlockEdit = () => {
	const blockProps = useBlockProps({
		className: 'wp-block-button is-style-secondary',
	});

	return (
		<div {...blockProps}>
			<button type="button" className="wp-element-button">
				Rate
			</button>
		</div>
	);
};
