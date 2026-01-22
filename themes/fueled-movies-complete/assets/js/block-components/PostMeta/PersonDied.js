/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * Internal dependencies.
 */
import DateTimePopover from '../DateTimePopover';

/**
 * PersonDied component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const PersonDied = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_person_died" {...postMetaProps}>
			{(meta, setMeta) => (
				<DateTimePopover
					label={__('Died', 'tenup')}
					date={meta}
					setDate={(value) => setMeta(value)}
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default PersonDied;
