/**
 * WordPress dependencies.
 */

/**
 * External dependencies.
 */
import { usePost } from '@10up/block-components';
import { Flex } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies.
 */
import PersonBiography from '../block-components/PostMeta/PersonBiography';
import PersonBirthplace from '../block-components/PostMeta/PersonBirthplace';
import PersonBorn from '../block-components/PostMeta/PersonBorn';
import PersonDeathplace from '../block-components/PostMeta/PersonDeathplace';
import PersonDied from '../block-components/PostMeta/PersonDied';
import PersonIMDBID from '../block-components/PostMeta/PersonIMDBID';

/**
 * Adds a Person meta field panel to the editor.
 *
 * @returns {Function} The meta field panel.
 */
const PersonFields = () => {
	const { postType } = usePost();

	if (postType !== 'tenup-person') {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="tenup-person-fields"
			title={__('Person Information', 'tenup')}
		>
			<Flex direction="column">
				<PersonIMDBID />
				<PersonBiography />
				<PersonBorn />
				<PersonDied />
				<PersonBirthplace />
				<PersonDeathplace />
			</Flex>
		</PluginDocumentSettingPanel>
	);
};

/**
 * Register plugin.
 *
 * See https://developer.wordpress.org/block-editor/reference-guides/slotfills/plugin-document-setting-panel/
 */
registerPlugin('tenup-person-fields', {
	render: PersonFields,
});
