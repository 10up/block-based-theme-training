import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-movie', 'meta');

	const { tenup_movie_mpa_rating = '' } = meta || {};

	let RenderedUI = (
		<div className="components-notice is-error">
			{__('Movie MPA Rating post meta not found.', 'tenup')}
		</div>
	);

	if (tenup_movie_mpa_rating !== '') {
		RenderedUI = <p {...blockProps}>{tenup_movie_mpa_rating}</p>;
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = <p {...blockProps}>{__('PG-13', 'tenup')}</p>;
	}

	return RenderedUI;
};
