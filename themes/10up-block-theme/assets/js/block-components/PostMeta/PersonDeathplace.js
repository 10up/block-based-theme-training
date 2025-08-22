/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * PersonDeathplace component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const PersonDeathplace = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_person_deathplace" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextControl
					label={__('Deathplace', 'tenup')}
					help={__('City, State, Country', 'tenup')}
					onChange={(value) => setMeta(value)}
					value={meta}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default PersonDeathplace;
