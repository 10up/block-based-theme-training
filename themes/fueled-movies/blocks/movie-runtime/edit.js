import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { usePost, usePostMetaValue } from '@10up/block-components';

export const BlockEdit = () => {
	const blockProps = useBlockProps();
	const { postType } = usePost();
	const [tenup_movie_runtime = { hours: '0', minutes: '0' }] =
		usePostMetaValue('tenup_movie_runtime');

	let HoursTag = null;
	let MinutesTag = null;

	if (tenup_movie_runtime?.hours) {
		HoursTag = (
			<span aria-label={`${tenup_movie_runtime?.hours} hours`}>
				{`${tenup_movie_runtime?.hours}h`}
			</span>
		);
	}

	if (tenup_movie_runtime?.minutes) {
		MinutesTag = (
			<span aria-label={`${tenup_movie_runtime?.minutes} minutes`}>
				{`${tenup_movie_runtime?.minutes}m`}
			</span>
		);
	}

	// Fallback for template preview.
	if (postType === 'wp_template') {
		return <p {...blockProps}>2h 30m</p>;
	}

	if (tenup_movie_runtime?.hours && tenup_movie_runtime?.minutes) {
		return (
			<p {...blockProps}>
				<time dateTime={`PT${tenup_movie_runtime?.hours}H${tenup_movie_runtime?.minutes}M`}>
					{tenup_movie_runtime?.hours !== '0' && <>{HoursTag} </>}
					{MinutesTag}
				</time>
			</p>
		);
	}

	return (
		<div className="components-notice is-error">
			{__('Movie Runtime post meta not found.', 'tenup-block-theme')}
		</div>
	);
};
