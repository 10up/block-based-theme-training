import { store, getContext, getElement } from '@wordpress/interactivity';

const { state } = store('tenup/rate-movie', {
	state: {
		isPopoverOpen: false,
		get buttonText() {
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
			const popover = ref.querySelector('#rate-movie-popover');
			const button = ref.querySelector('.wp-block-tenup-rate-movie__trigger');
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
