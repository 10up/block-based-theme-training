import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_born = '' } = meta || {};

	let RenderedUI = (
		<div className="components-notice is-error">
			{__('Person born post meta not found.', 'tenup')}
		</div>
	);

	if (tenup_person_born !== '') {
		RenderedUI = (
			<>
				<dt>{__('Born', 'tenup')}</dt>
				<dd>{tenup_person_born}</dd>
			</>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = (
			<>
				<dt>{__('Born', 'tenup')}</dt>
				<dd>{__('January 1, 1950', 'tenup')}</dd>
			</>
		);
	}

	return RenderedUI;
};
