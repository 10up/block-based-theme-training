import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_birthplace = '' } = meta || {};

	let RenderedUI = (
		<div className="components-notice is-error">
			{__('Person birthplace post meta not found.', 'tenup')}
		</div>
	);

	if (tenup_person_birthplace !== '') {
		RenderedUI = (
			<>
				<dt>{__('Birthplace', 'tenup')}</dt>
				<dd>{tenup_person_birthplace}</dd>
			</>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = (
			<>
				<dt>{__('Birthplace', 'tenup')}</dt>
				<dd>{__('New York City, New York, USA', 'tenup')}</dd>
			</>
		);
	}

	return RenderedUI;
};
