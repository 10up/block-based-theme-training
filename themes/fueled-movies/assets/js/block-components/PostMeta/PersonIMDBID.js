/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { external } from '@wordpress/icons';

/**
 * External dependencies.
 */
import { PostMeta } from '@10up/block-components';

/**
 * PersonIMDBID component.
 *
 * @param {object} props               Component props.
 * @param {object} props.postMetaProps Props to use on the 10up PostMeta component.
 * @param {object} props.restProps     Rest of the props to pass to the control component.
 * @returns {Function}                 The rendered component.
 */
const PersonIMDBID = ({ postMetaProps, ...restProps }) => {
	return (
		<PostMeta metaKey="tenup_person_imdb_id" {...postMetaProps}>
			{(meta, setMeta) => (
				<TextControl
					label={__('IMDB ID', 'tenup-block-theme')}
					help={
						<>
							{__('Enter the IMDB ID of the person.', 'tenup-block-theme')}
							{meta && (
								<>
									<br />
									<Button
										href={`https://www.imdb.com/name/${meta}`}
										icon={external}
										target="_blank"
										variant="link"
									>
										{__('IMDB', 'tenup-block-theme')}
									</Button>
									{' | '}
									<Button
										href={`https://api.imdbapi.dev/names/${meta}`}
										icon={external}
										target="_blank"
										variant="link"
									>
										{__('JSON', 'tenup-block-theme')}
									</Button>
								</>
							)}
						</>
					}
					onChange={(value) => setMeta(value)}
					value={meta}
					__next40pxDefaultSize
					{...restProps}
				/>
			)}
		</PostMeta>
	);
};

export default PersonIMDBID;
