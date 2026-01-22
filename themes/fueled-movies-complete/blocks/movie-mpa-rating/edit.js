import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { usePost } from '@10up/block-components';

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const { postType } = usePost();
	const [meta] = useEntityProp('postType', 'tenup-movie', 'meta');

	const { tenup_movie_mpa_rating = '' } = meta || {};

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return <p {...blockProps}>{__('PG-13', 'tenup')}</p>;
	}

	if (tenup_movie_mpa_rating !== '') {
		return <p {...blockProps}>{tenup_movie_mpa_rating}</p>;
	}

	return (
		<div className="components-notice is-error">
			{__('Movie MPA Rating post meta not found.', 'tenup')}
		</div>
	);
};
