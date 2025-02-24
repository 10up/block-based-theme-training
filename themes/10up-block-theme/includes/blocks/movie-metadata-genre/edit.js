/* eslint-disable jsx-a11y/anchor-is-valid */
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { usePost, useAllTerms } from '@10up/block-components';

export const BlockEdit = () => {
	const { postType } = usePost();
	const [genre, hasResolvedCategories] = useAllTerms('tenup-genre');

	if (!hasResolvedCategories) {
		return <Spinner />;
	}

	let RenderedUI = (
		<div className="components-notice is-error">{__('No Genre found.', 'tenup')}</div>
	);

	if (genre.length > 0) {
		RenderedUI = (
			<>
				<dt>{__('Genre', 'tenup')}</dt>
				<dd>
					<div className="taxonomy-tenup-genre wp-block-post-terms">
						{genre.map((term) => (
							<a key={term.id} rel="tag">
								{term.name}
							</a>
						))}
					</div>
				</dd>
			</>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		RenderedUI = (
			<>
				<dt>{__('Genre', 'tenup')}</dt>
				<dd>
					<div className="taxonomy-tenup-genre wp-block-post-terms">
						<a rel="tag">{__('Action', 'tenup')}</a>
						<a rel="tag">{__('Comedy', 'tenup')}</a>
					</div>
				</dd>
			</>
		);
	}

	return RenderedUI;
};
