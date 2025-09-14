import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_biography = '' } = meta || {};

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return (
			<>
				<dt>{__('Born', 'tenup')}</dt>
				<dd>{__('January 1, 1950', 'tenup')}</dd>
			</>
		);
	}

	if (tenup_person_biography !== '') {
		return (
			<>
				<dt>{__('Biography', 'tenup')}</dt>
				<dd>{tenup_person_biography}</dd>
			</>
		);
	}

	return (
		<div className="components-notice is-error">
			{__('Person biography post meta not found.', 'tenup')}
		</div>
	);
};
