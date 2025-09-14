/* eslint-disable jsx-a11y/anchor-is-valid */

/**
 * @todo Match to FE once we can make dynamic.
 */
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-person', 'meta');

	const { tenup_person_movies = '' } = meta || {};

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return (
			<>
				<dt>{__('Movies', 'tenup')}</dt>
				<dd>
					<a href="#">{__('The Godfather', 'tenup')}</a>
				</dd>
			</>
		);
	}

	if (tenup_person_movies !== '') {
		return (
			<>
				<dt>{__('Movies', 'tenup')}</dt>
				<dd>{tenup_person_movies}</dd>
			</>
		);
	}

	return (
		<div className="components-notice is-error">{__('Person movies not found.', 'tenup')}</div>
	);
};
