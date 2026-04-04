const bnbOverlay = {};

bnbOverlay.init = (mainElement = document) => {
	// Click on images.
	mainElement.querySelectorAll('a.envira-gallery-bnb-link').forEach(element => {
		element.addEventListener('click', event => {
			event.preventDefault();
			event.stopPropagation();
			const galleryElement = element.closest('.envira-layout-bnb--container');
			bnbOverlay.createOverlay(galleryElement);
		});
	});

	// Click on more link.
	mainElement.querySelectorAll('.envira-layout-bnb--more-link').forEach(element => {
		element.addEventListener('click', event => {
			event.preventDefault();
			event.stopPropagation();
			let target = event.target;
			if('BUTTON' !== target.tagName){
				target = event.target.parentElement;
			}
			const galleryElement = target.previousSibling;
			bnbOverlay.createOverlay(galleryElement);
		});
	});
};

bnbOverlay.createOverlay = galleryElement => {
	if(document.querySelector('#envira-gallery-bnb-overlay')){
		return;
	}

	const uniqueId = galleryElement.dataset.uniqueId;
	const overlayContentId = `envira-gallery-bnb-overlay-${uniqueId}`;
	const overlayContentElm = document.querySelector(`#${overlayContentId}`);

	const overlay = document.createElement('div');
	const overlayContainer = document.createElement('div');
	const overlayClose = document.createElement('div');

	overlay.id = 'envira-gallery-bnb-overlay';

	overlayContainer.id = 'envira-gallery-bnb-overlay--container';
	overlayContainer.innerHTML = '<div class="envira-loader"><div></div><div></div><div></div><div></div></div>';

	overlayClose.id = 'envira-gallery-bnb-overlay-close-button';
	overlayClose.style.opacity = 0;
	overlayClose.innerHTML =
		'<button class="envira-close-button" aria-label="Close Overlay">' +
		'<svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 768 768">' +
		'<path d="M607.5 205.5L429 384l178.5 178.5-45 45L384 429 205.5 607.5l-45-45L339 384 160.5 205.5l45-45L384 339l178.5-178.5z"></path>' +
		'</svg>' +
		'</button>'


	overlay.appendChild(overlayContainer);

	document.body.insertBefore(overlay, document.body.firstChild);

	document.body.scrollIntoView();

	setTimeout(() => {
		overlay.style.marginTop = 0;
		overlay.style.minHeight = document.body.scrollHeight + 'px';

		overlayContainer.innerHTML = overlayContentElm.innerHTML;
		overlayContentElm.innerHTML = '';

		overlayContainer.prepend(overlayClose)

		jQuery(document).trigger('envira_load');

		bnbOverlay.destroyOverlay(overlay, overlayContentElm);
	});

	// Show overlayClose 333ms later, after the overlay animation.
	setTimeout(() => {
		overlayClose.style.opacity = null;
	}, 333);
};

bnbOverlay.destroyOverlay = (overlay, overlayContentElm) => {
	const removeOverlay = () => {
		if(0 === document.querySelectorAll('.envirabox-wrap').length){
			document.removeEventListener('keydown', escKeyListener);
			overlay.style.marginTop = '100vh';
			overlayContentElm.innerHTML = overlay.querySelector('.envira-gallery-wrap').outerHTML;

			// Remove close button first.
			overlay.querySelector('#envira-gallery-bnb-overlay-close-button').remove();

			setTimeout(() => {
				overlay.remove();
			}, 333);
		}
	};

	// Close using esc key.
	const escKeyListener = event => {
		if('Escape' === event.key){
			removeOverlay();
		}
	};

	document.addEventListener('keydown', escKeyListener);

	// Close using x button.
	document.querySelectorAll('#envira-gallery-bnb-overlay-close-button button').forEach(element => {
		element.addEventListener('click', event => {
			event.preventDefault();
			event.stopPropagation();
			removeOverlay();
		});
	});
};

export default bnbOverlay;
