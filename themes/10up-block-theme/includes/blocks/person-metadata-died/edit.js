import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_died = '' } = meta || {};

	let RenderedUI = (
		<div className="components-notice is-error">
			{__('Person died post meta not found.', 'tenup')}
		</div>
	);

	if (tenup_person_died !== '') {
		RenderedUI = (
			<>
				<dt>{__('Died', 'tenup')}</dt>
				<dd>{tenup_person_died}</dd>
			</>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = (
			<>
				<dt>{__('Died', 'tenup')}</dt>
				<dd>{__('January 1, 2025', 'tenup')}</dd>
			</>
		);
	}

	return RenderedUI;
};
