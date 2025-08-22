import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_deathplace = '' } = meta || {};

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return (
			<>
				<dt>{__('Deathplace', 'tenup')}</dt>
				<dd>{__('Hollywood, California, USA', 'tenup')}</dd>
			</>
		);
	}

	if (tenup_person_deathplace !== '') {
		return (
			<>
				<dt>{__('Deathplace', 'tenup')}</dt>
				<dd>{tenup_person_deathplace}</dd>
			</>
		);
	}

	return (
		<div className="components-notice is-error">
			{__('Person birthplace post meta not found.', 'tenup')}
		</div>
	);
};
