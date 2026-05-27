import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export const BlockEdit = (props) => {
	const { attributes, setAttributes } = props;
	const { content } = attributes;

	const blockProps = useBlockProps();

	return (
		<RichText
			{...blockProps}
			tagName="dt"
			value={content}
			onChange={(value) => setAttributes({ content: value })}
			placeholder={__('Enter term…', 'tenup-block-theme')}
		/>
	);
};
