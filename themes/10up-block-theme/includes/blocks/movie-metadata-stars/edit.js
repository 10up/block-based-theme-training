/* eslint-disable jsx-a11y/anchor-is-valid */

/**
 * @todo Match to FE once we can make dynamic.
 */
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-movie', 'meta');

	const { tenup_movie_plot = '' } = meta || {};

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return (
			<>
				<dt>{__('Stars', 'tenup')}</dt>
				<dd>
					<a href="#">{__('Steven Spielberg', 'tenup')}</a>
				</dd>
			</>
		);
	}

	if (tenup_movie_plot !== '') {
		return (
			<>
				<dt>{__('Stars', 'tenup')}</dt>
				<dd>{tenup_movie_plot}</dd>
			</>
		);
	}

	return (
		<div className="components-notice is-error">{__('Movie stars not found.', 'tenup')}</div>
	);
};
