/**
 * Clickable Cards
 *
 * Adds the ability to click anywhere on an element to navigate to the
 * first link found within the chosen selector.
 *
 * For full hover styles, use the data attributes in your CSS rather than the class
 * name so styles do not get applied should anything fail or a link not be found.
 *
 * This implements the recommended approach outlined the Cards section of the Inclusive Components
 * book by Heydon Pickering. See the "The redundant click event" section for more details:
 *
 * @see https://inclusive-components.design/cards/#theredundantclickevent
 */
const IsClickableCard = {
	SELECTORS: '.is-clickable-card',
	PRIMARY_LINK_SELECTOR: ':where(h1, h2, h3, h4, h5, h6, .wp-block-button) a',
	DATA_ATTRIBUTE: 'data-is-clickable-card',
	DATA_ATTRIBUTE_PRIMARY_LINK: 'data-is-clickable-card-primary',
	DATA_ATTRIBUTE_SECONDARY_LINK: 'data-is-clickable-card-secondary',
	MAX_ALLOWED_SCROLL_DISTANCE: 5,
	MAX_ALLOWED_TIME_BETWEEN_EVENTS: 200,

	/**
	 * Toggles cursor classes on the card element.
	 *
	 * @param {HTMLElement} card The card element.
	 * @param {string}      to   The cursor mode ('pointer' or 'text').
	 */
	toggleCursor(card, to) {
		card.classList.toggle('is-cursor-pointer', to === 'pointer');
		card.classList.toggle('is-cursor-text', to === 'text');
	},

	/**
	 * Retrieves metadata about an event.
	 *
	 * @param {HTMLElement} card The element that was clicked.
	 * @returns {object} An object containing the timestamp, vertical scroll position, and horizontal scroll position.
	 */
	getEventMetadata(card) {
		return {
			timestamp: Date.now(),
			verticalScrollPosition: window.scrollY,
			horizontalScrollPosition: card.scrollLeft,
		};
	},

	/**
	 * Sets up the given card to be clickable.
	 *
	 * @param {HTMLElement} card The card element.
	 */
	setupIsClickableCard(card) {
		const primaryLinkElement = card.querySelector(this.PRIMARY_LINK_SELECTOR);

		if (!primaryLinkElement) {
			return;
		}

		card.setAttribute(this.DATA_ATTRIBUTE, '');
		primaryLinkElement.setAttribute(this.DATA_ATTRIBUTE_PRIMARY_LINK, '');

		// Add data attribute to other clickable elements in card to aid when styling full card hovers.
		const clickableElements = card.querySelectorAll(':where(button, a, [role="button"])');
		clickableElements.forEach((el) => {
			if (el === primaryLinkElement) {
				return;
			}
			el.setAttribute(this.DATA_ATTRIBUTE_SECONDARY_LINK, '');
		});

		this.toggleCursor(card, 'pointer');

		let up;
		let down;
		let timeoutId;

		const handleClickStartEvent = () => {
			down = this.getEventMetadata(card);

			if (timeoutId !== undefined) {
				clearTimeout(timeoutId);
				timeoutId = undefined;
			}

			// Switch to text cursor after threshold — signals that text selection is active.
			timeoutId = window.setTimeout(() => {
				if (!up) {
					this.toggleCursor(card, 'text');
				}
			}, this.MAX_ALLOWED_TIME_BETWEEN_EVENTS + 1);
		};

		const handleClickStopEvent = (event) => {
			this.toggleCursor(card, 'pointer');

			up = this.getEventMetadata(card);

			if (timeoutId !== undefined) {
				clearTimeout(timeoutId);
				timeoutId = undefined;
			}

			try {
				const hasClickedOnButton = event.target.closest('button');
				const hasClickedOnLink = event.target.closest('a');

				// If the user clicks on any other link or button inside the card that takes precedence.
				if (hasClickedOnButton || hasClickedOnLink) {
					return;
				}

				/**
				 * If the time between mouse down and mouse up is greater than our set max
				 * then the user is probably trying to select text, so we don't want to
				 * trigger the link.
				 */
				const hasElapsedTimeout =
					up.timestamp - down.timestamp > this.MAX_ALLOWED_TIME_BETWEEN_EVENTS;

				if (hasElapsedTimeout) {
					return;
				}

				/**
				 * If the user scrolled more than our set max while clicking, then they
				 * are probably trying to scroll the page and not click the link.
				 */
				const absoluteVerticalScrollDistance = Math.abs(
					up.verticalScrollPosition - down.verticalScrollPosition,
				);
				const hasScrolledVertically =
					absoluteVerticalScrollDistance > this.MAX_ALLOWED_SCROLL_DISTANCE;

				if (hasScrolledVertically) {
					return;
				}

				/**
				 * If the user scrolled more than our set max horizontally while clicking, then they
				 * are probably trying to scroll within a scroll container and not click the link.
				 */
				const absoluteHorizontalScrollDistance = Math.abs(
					up.horizontalScrollPosition - down.horizontalScrollPosition,
				);
				const hasScrolledHorizontally =
					absoluteHorizontalScrollDistance > this.MAX_ALLOWED_SCROLL_DISTANCE;

				if (hasScrolledHorizontally) {
					return;
				}

				/**
				 * Instead of creating a new event, we can reuse the event that was
				 * passed to this function and dispatch it on the primary link element.
				 *
				 * This will trigger the link as if the user clicked on it and
				 * include any other data that was included in the original event
				 * such as keyboard modifiers (shift, ctrl, etc).
				 */
				const clickEvent = new MouseEvent('click', event);
				primaryLinkElement.dispatchEvent(clickEvent);
			} finally {
				up = undefined;

				if (timeoutId !== undefined) {
					clearTimeout(timeoutId);
					timeoutId = undefined;
				}

				this.toggleCursor(card, 'pointer');
			}
		};

		card.addEventListener('mousedown', handleClickStartEvent, { passive: true });
		card.addEventListener('mouseup', handleClickStopEvent);
		card.addEventListener('auxclick', handleClickStopEvent);
		card.addEventListener('touchstart', handleClickStartEvent, { passive: true });
		card.addEventListener('touchend', handleClickStopEvent, { passive: true });
	},

	/**
	 * Init.
	 */
	init() {
		const cards = document.querySelectorAll(`${this.SELECTORS}`);

		if (!cards.length) {
			return;
		}

		cards.forEach((card) => {
			this.setupIsClickableCard(card);
		});
	},
};

export default IsClickableCard;

IsClickableCard.init();
