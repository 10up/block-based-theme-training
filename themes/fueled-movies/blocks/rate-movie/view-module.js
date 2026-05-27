/**
 * Interactivity API store for the Rate Movie block.
 *
 * `store()` creates a reactive store scoped to the 'tenup/rate-movie' namespace.
 * State is reactive: when values change, any directives referencing them re-render.
 * Context (`getContext()`) is per-block-instance data set via `data-wp-context` in the markup.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

const { state } = store('tenup/rate-movie', {
	// Reactive state shared across all instances of this block.
	state: {
		// Tracks whether the popover is currently open.
		isPopoverOpen: false,

		// Derived state: true when the user has set a rating.
		get hasRating() {
			const context = getContext();
			return context.rating !== null && context.rating > 0;
		},

		// Derived state: shows "Rate" or "7/10" depending on rating and popover state.
		get buttonText() {
			if (state.isPopoverOpen) {
				return 'Rate';
			}
			const context = getContext();
			return context.rating !== null && context.rating > 0 ? `${context.rating}/10` : 'Rate';
		},

		// Derived state: the text inside the popover showing the current selection.
		get popupRatingText() {
			const context = getContext();
			return context.rating !== null && context.rating > 0 ? `${context.rating}/10` : '';
		},

		// Derived state: the range slider's current value (defaults to 1 if no rating).
		get sliderValue() {
			const context = getContext();
			return context.rating !== null ? context.rating : 1;
		},
	},

	// Actions are event handlers triggered by `data-wp-on--{event}` directives.
	actions: {
		// Resets the rating to null (triggered by the "Clear" button).
		clearRating() {
			const context = getContext();
			context.rating = null;
		},

		// Sets the rating from the range slider input event.
		selectRating(event) {
			const context = getContext();
			const value = parseInt(event.target.value, 10);
			context.rating = value >= 1 && value <= 10 ? value : null;
		},
	},

	// Callbacks run in response to lifecycle events like `data-wp-init`.
	callbacks: {
		// Syncs the popover's open/close state with the reactive store.
		// Uses the native Popover API's `toggle` event to detect state changes.
		initPopover() {
			const { ref } = getElement();
			if (!ref) {
				return;
			}

			// `data-wp-init` is on the popover element, so we walk up to find
			// the block wrapper and then locate the trigger button sibling.
			const root = ref.closest('.wp-block-tenup-rate-movie') ?? ref.parentElement;
			const popover = ref;
			const button = root?.querySelector('.wp-block-tenup-rate-movie__trigger');

			if (!popover || !button) {
				return;
			}

			// Listen for the native popover toggle event and sync to reactive state.
			const updateState = () => {
				const isOpen = popover.matches(':popover-open');
				state.isPopoverOpen = isOpen;
				button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			};

			popover.addEventListener('toggle', updateState);
			updateState();
		},
	},
});
