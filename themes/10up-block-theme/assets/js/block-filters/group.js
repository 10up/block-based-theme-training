/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { ToggleControl, PanelBody } from '@wordpress/components';

/**
 * External dependencies.
 */
import { registerBlockExtension } from '@10up/block-components';

/**
 * Registers an extension for the Group block.
 */
registerBlockExtension('core/group', {
	extensionName: 'group-has-separator',
	attributes: {
		hasSeparator: {
			type: 'boolean',
			default: false,
		},
	},
	/**
	 * Adds a class name to the block wrapper.
	 *
	 * @param {object} attributes Block attributes.
	 * @returns {string} The class name to add.
	 */
	classNameGenerator: (attributes) => {
		const { hasSeparator, layout } = attributes;

		if (hasSeparator && layout?.type === 'flex' && layout?.orientation === 'horizontal') {
			return 'has-separator';
		}

		return '';
	},
	/**
	 * Adds a separator control to the block settings.
	 *
	 * @param {object} props Block props.
	 * @returns {Function} The component.
	 */
	Edit: (props) => {
		const { attributes, setAttributes } = props;
		const { hasSeparator, layout } = attributes;

		if (layout?.type !== 'flex' || layout?.orientation !== 'horizontal') {
			return null;
		}

		return (
			<InspectorControls group="settings">
				<PanelBody title={__('Separator', 'tenup')}>
					<ToggleControl
						label={__('Add Separator', 'tenup')}
						help={__('Creates a middot between each innerblock.', 'tenup')}
						checked={hasSeparator}
						onChange={(value) => setAttributes({ hasSeparator: value })}
					/>
				</PanelBody>
			</InspectorControls>
		);
	},
});
