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
				<dt>{__('Plot', 'tenup')}</dt>
				<dd>
					Lorem ipsum dolor sit amet consectetur adipiscing elit. Nulla facilisi euismod.
				</dd>
			</>
		);
	}

	if (tenup_movie_plot !== '') {
		return (
			<>
				<dt>{__('Plot', 'tenup')}</dt>
				<dd>{tenup_movie_plot}</dd>
			</>
		);
	}

	return (
		<div className="components-notice is-error">
			{__('Movie plot post meta not found.', 'tenup')}
		</div>
	);
};
