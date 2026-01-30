import { store, getContext, getElement } from '@wordpress/interactivity';

const { state } = store('tenup/rate-movie', {
	state: {
		isPopoverOpen: false,
		get hasRating() {
			const context = getContext();
			return context.rating !== null && context.rating > 0;
		},
		get buttonText() {
			if (state.isPopoverOpen) {
				return 'Rate';
			}
			const context = getContext();
			return context.rating !== null && context.rating > 0 ? `${context.rating}/10` : 'Rate';
		},
		get popupRatingText() {
			const context = getContext();
			return context.rating !== null && context.rating > 0 ? `${context.rating}/10` : '';
		},
		get sliderValue() {
			const context = getContext();
			return context.rating !== null ? context.rating : 1;
		},
	},
	actions: {
		clearRating() {
			const context = getContext();
			context.rating = null;
		},
		selectRating(event) {
			const context = getContext();
			const value = parseInt(event.target.value, 10);
			context.rating = value >= 1 && value <= 10 ? value : null;
		},
	},
	callbacks: {
		initPopover() {
			const { ref } = getElement();
			if (!ref) {
				return;
			}
			// `data-wp-init` is on the popover element, so we need to locate the trigger button
			// from the block wrapper (the trigger is a sibling of the popover, not a child).
			const root = ref.closest('.wp-block-tenup-rate-movie') ?? ref.parentElement;
			const popover = ref;
			const button = root?.querySelector('.wp-block-tenup-rate-movie__trigger');
			if (!popover || !button) {
				return;
			}
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
