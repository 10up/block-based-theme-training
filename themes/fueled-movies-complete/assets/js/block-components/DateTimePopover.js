/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies.
 */
import {
	Button,
	DateTimePicker,
	Dropdown,
	__experimentalHeading as Heading,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { closeSmall } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Formats a date string into the desired display format.
 *
 * @param {string} date The date string to be formatted.
 * @returns {string}    The formatted date string.
 */
const formatDate = (date) => {
	const options = {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
	};

	let formattedDate = new Date(date);
	formattedDate = formattedDate.toLocaleDateString('en-US', options);

	return formattedDate;
};

/**
 * Renders a label and button opening a DateTime Popover to be used in a sidebar settings panel.
 *
 * @param {object} props           Component props.
 * @param {string} props.date      The date to be displayed.
 * @param {Function} props.setDate The function to be called when the date is changed.
 * @param {string} props.label     The label for the DateTimePopover.
 * @returns {Function}             The DateTimePopover component.
 */
const DateTimePopover = ({ date, setDate, label }) => {
	return (
		<Dropdown
			style={{ width: '100%' }}
			popoverProps={{ offset: 36, placement: 'left-end' }}
			renderToggle={({ isOpen, onToggle }) => (
				<HStack justify="flex-start" alignment="top">
					<div className="editor-post-panel__row-label">{__(label, 'tenup')}</div>
					<Button
						aria-expanded={isOpen}
						onClick={onToggle}
						size="compact"
						variant="tertiary"
					>
						{date ? formatDate(date) : __('Choose a date', 'tenup')}
					</Button>
				</HStack>
			)}
			renderContent={({ onClose }) => (
				<div style={{ padding: '16px' }}>
					<HStack
						justify="space-between"
						className="block-editor-inspector-popover-header"
					>
						<Heading level={2} size={13}>
							{__(label, 'tenup')}
						</Heading>
						<HStack
							justify="flex-end"
							align="center"
							style={{ 'max-width': 'fit-content' }}
						>
							<Button
								onClick={() => setDate('')}
								size="small"
								variant="link"
								isDestructive
							>
								{__('Clear', 'tenup')}
							</Button>
							<Button
								size="small"
								className="block-editor-inspector-popover-header__action"
								label={__('Close', 'tenup')}
								icon={closeSmall}
								onClick={onClose}
							/>
						</HStack>
					</HStack>
					<DateTimePicker
						label={__(label, 'tenup')}
						onChange={setDate}
						currentDate={date}
						is12Hour
					/>
				</div>
			)}
		/>
	);
};

export default DateTimePopover;
