import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

export const BlockEdit = (props) => {
	const { attributes, name } = props;
	const blockProps = useBlockProps();
	return (
		<p {...blockProps}>
			<ServerSideRender block={name} attributes={{ ...attributes, isEditor: true }} />
		</p>
	);
};
