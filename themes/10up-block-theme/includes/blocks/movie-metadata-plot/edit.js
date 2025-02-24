import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-movie', 'meta');

	const { tenup_movie_plot = '' } = meta || {};

	let RenderedUI = (
		<div className="components-notice is-error">
			{__('Movie plot post meta not found.', 'tenup')}
		</div>
	);

	if (tenup_movie_plot !== '') {
		RenderedUI = (
			<>
				<dt>{__('Plot', 'tenup')}</dt>
				<dd>{tenup_movie_plot}</dd>
			</>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = (
			<>
				<dt>{__('Plot', 'tenup')}</dt>
				<dd>
					Lorem ipsum dolor sit amet consectetur adipiscing elit. Nulla facilisi euismod.
				</dd>
			</>
		);
	}

	return RenderedUI;
};
