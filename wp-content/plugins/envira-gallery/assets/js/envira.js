import './lib/pollyfills.js';
import './lib/mousewheel.js';
import './lib/jquery.justifiedGallery.js';
import './lib/enviraJustifiedGallery-extensions.js';
import './lib/imagesloaded.js';
import './lib/isotope.js';
import './lib/envirabox.js';
import './lib/envirabox-fullscreen.js';
import './lib/envirabox-media.js';
import './lib/envirabox-wheel.js';
import './lib/envirabox-guestures.js';
import './lib/envirabox-thumbs.js';
import './lib/envirabox-slideshow.js';
// import './lib/envirabox-downloads.js';

import Envira from './gallery-init.js';
import Envira_Link from './gallery-link.js';

var envira_galleries = window.envira_galleries || {},
	envira_links = window.envira_links || {};

(function(
	$,
	window,
	document,
	Envira,
	Envira_Link,
	envira_gallery,
	envira_galleries,
) {
	$(function() {
		window.envira_galleries = envira_galleries;
		window.envira_links = envira_links;

		$(document).on('envira_load', function(e) {
			e.stopPropagation();
			envira_galleries = {};
			envira_links = {};
			var envira_galleries_total = [];

			applyOffsetsToImages();

			$('.envira-gallery-public').each(function() {
				let $this = $(this),
					$id = $this.data('envira-id'),
					$envira_galleries = $this.data('gallery-config'),
					$envira_images = $this.data('gallery-images'),
					$envira_lightbox = $this.data('lightbox-theme'),
					$suffix_id = '';

				// check to see if multiple duplicate ids exist, and if so add a suffix to make each unique (and therefore get init)
				if (
					envira_galleries_total[
						$envira_galleries['gallery_id']
					] !== undefined
				) {
					$suffix_id =
						'_' +
						(envira_galleries_total[
							$envira_galleries['gallery_id']
						]
							? parseInt(
									envira_galleries_total[
										$envira_galleries[
											'gallery_id'
										]
									],
							  ) + 1
							: 2);
					envira_galleries_total[
						$envira_galleries['gallery_id']
					] =
						parseInt(
							envira_galleries_total[
								$envira_galleries['gallery_id']
							],
						) + 1;
				} else {
					envira_galleries_total[
						$envira_galleries['gallery_id']
					] = 1;
				}
				envira_galleries[
					$envira_galleries['gallery_id']
				] = new Envira(
					$envira_galleries['gallery_id'] + $suffix_id,
					$envira_galleries,
					$envira_images,
					$envira_lightbox,
				);

				// the loader wont disappear if lazy loading is disabled (that is the point of the loader so we will do this here if the loading is disabled).
				if ( $("#envira-gallery-wrap-" + $envira_galleries['gallery_id'] + $suffix_id + ".envira-lazy-loading-disabled").length ) {
					$("#envira-gallery-wrap-" + $envira_galleries['gallery_id'] + $suffix_id + ".envira-lazy-loading-disabled")
					.find(".envira-loader")
					.remove();
				} else {
					$(".envira-gallery-wrap")
					.find(".envira-loader")
					.remove();
				}
				const galleryWrapper = $('#envira-gallery-wrap-' + $envira_galleries['gallery_id'] + $suffix_id);
				if(!galleryWrapper.hasClass('envira-animations-load')){
					galleryWrapper
						.find('.envira-layout-bnb--more-link')
						.css('opacity', '1');
				}
			});

			$('.envira-gallery-links').each(function() {
				let $this = $(this),
					$envira_galleries = $this.data('gallery-config'),
					$envira_images = $this.data('gallery-images'),
					$envira_lightbox = $this.data('lightbox-theme');

				if (
					envira_links[$envira_galleries['gallery_id']] ===
					undefined
				) {
					envira_links[
						$envira_galleries['gallery_id']
					] = new Envira_Link(
						$envira_galleries,
						$envira_images,
						$envira_lightbox,
					);
				}
			});

			$(document).trigger('envira_loaded', [
				envira_galleries,
				envira_links,
			]);
		});

		$(document).trigger('envira_load');

		if (envira_gallery.debug !== undefined && envira_gallery.debug) {
			console.log(envira_links);
			console.log(envira_galleries);
		}

		$('body').on(
			'click',
			'div.envirabox-title a[href*="#"]:not([href="#"])',
			function(e) {
				if (
					location.pathname.replace(/^\//, '') ==
						this.pathname.replace(/^\//, '') &&
					location.hostname == this.hostname
				) {
					$.envirabox.close();
					return false;
				}
			},
		);

		/**
		 * Increase div height if content is taller than the div.
		 */
		function adjustGalleryMargin() {
			const galleryDiv = document.getElementsByClassName('envira-album-public')[0];
			if (galleryDiv) {
				if (galleryDiv.scrollHeight > galleryDiv.clientHeight) {
					const heightDiff = galleryDiv.scrollHeight - galleryDiv.clientHeight;
					galleryDiv.style.marginBottom = heightDiff + 'px'; // Adjust as needed
				} else {
					galleryDiv.style.marginBottom = '0';
				}
			}
		}

		function adjustGalleryLoaded(container, callback) {
			const images = container.getElementsByTagName('img');
			const totalImages = images.length;
			let loadedImages = 0;

			if (totalImages === 0) {
				callback();
				return;
			}

			for (let i = 0; i < totalImages; i++) {
				const img = images[i];
				if (img.complete) {
					adjustGalleryIncrementCounter();
				} else {
					img.onload = img.onerror = adjustGalleryIncrementCounter;
				}
			}

			function adjustGalleryIncrementCounter() {
				loadedImages++;
				if (loadedImages === totalImages) {
					callback();
				}
			}
		}

		function adjustGalleryDebounce(func, wait) {
			let timeout;
			return function(...args) {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, args), wait);
			};
		}

		window.onload = function() {
			if (document.getElementsByClassName('envira-album-public').length > 0) {
				adjustGalleryLoaded(document, () => {
					setTimeout(adjustGalleryMargin, 1000); // Delay by 1 second
				});
			}
		};

		window.onresize = adjustGalleryDebounce(function() {
			if (document.getElementsByClassName('envira-album-public').length > 0) {
				setTimeout(adjustGalleryMargin, 1000); // Delay by 1 second
			}
		}, 100); // Adjust adjustGalleryDebounce time as needed

		/* setup lazy load event */
		$(document).on('envira_image_lazy_load_complete', function(event) {
			let envira_container = '';

			if (
				event !== undefined &&
				((event.image_id !== undefined &&
					event.image_id !== null) ||
					(event.video_id !== undefined &&
						event.video_id !== null))
			) {
				const galleryWrapper = $('#envira-gallery-wrap-' + event.gallery_id);
				if (
					galleryWrapper.find(
						'#' + event.video_id + ' iframe',
					).length > 0
				) {
					envira_container = $(
						'#envira-gallery-wrap-' + event.gallery_id,
					).find('#' + event.video_id + ' iframe');
				} else if (
					galleryWrapper.find(
						'#' + event.video_id + ' video',
					).length > 0
				) {
					envira_container = $(
						'#envira-gallery-wrap-' + event.gallery_id,
					).find('#' + event.video_id + ' video');
				} else {
					envira_container = $(
						'#envira-gallery-wrap-' + event.gallery_id,
					).find('img#' + event.image_id);
				}

				if (
					envira_container === undefined ||
					envira_container === ''
				) {
					return;
				}

				if (
					galleryWrapper
						.find('div.envira-gallery-public')
						.hasClass('envira-gallery-0-columns')
				) {
					/* this is an automatic gallery */
					$(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('div.envira-gallery-position-overlay')
						.delay(100)
						.show();
				} else {
					/* this is a legacy gallery */
					$(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('div.envira-gallery-position-overlay')
						.delay(100)
						.show();

					/* re-do the padding bottom */
					/* $padding_bottom = ( $output_height / $output_width ) * 100; */

					var envira_lazy_width = $(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('.envira-lazy')
						.width();
					var ratio1 = event.naturalHeight / event.naturalWidth;
					var ratio2 = event.naturalHeight / envira_lazy_width;

					if (ratio2 < ratio1) {
						var ratio = ratio2;
					} else {
						var ratio = ratio1;
					}

					var padding_bottom = ratio * 100;
					if (
						envira_container
							.closest('div.envira-gallery-public')
							.parent()
							.hasClass('envira-gallery-theme-sleek')
					) {
						// add additional padding for this theme
						padding_bottom = padding_bottom + 2;
					}

					var div_envira_lazy = $(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('div.envira-lazy');
					var caption_height = div_envira_lazy
						.closest('div.envira-gallery-item-inner')
						.find('.envira-gallery-captioned-data')
						.height();
					if (
						$(envira_container)
							.closest('div.envira-gallery-item')
							.hasClass('enviratope-item')
					) {
						div_envira_lazy
							.css('padding-bottom', padding_bottom + '%')
							.attr('data-envira-changed', 'true');
						var div_overlay = $(envira_container)
							.closest('div.envira-gallery-item-inner')
							.find(
								'.envira-gallery-position-overlay.envira-gallery-bottom-right',
							);
						div_overlay.css('bottom', caption_height);
						div_overlay = $(envira_container)
							.closest('div.envira-gallery-item-inner')
							.find(
								'.envira-gallery-position-overlay.envira-gallery-bottom-left',
							);
						div_overlay.css('bottom', caption_height);
					} else {
						div_envira_lazy
							.css('padding-bottom', 'unset')
							.attr('data-envira-changed', 'true');

						var gallery_classes = div_envira_lazy.closest('div.envira-gallery-wrap')[0]?.classList ?? [];

						if(gallery_classes.length > 0
							&& !gallery_classes.contains('envira-layout-bnb')
							&& !gallery_classes.contains('envira-layout-bnb--overlay')
						){
							div_envira_lazy.css('height', 'auto');
						}

						var div_overlay = $(envira_container)
							.closest('div.envira-gallery-item-inner')
							.find(
								'.envira-gallery-position-overlay.envira-gallery-bottom-right',
							);
						div_overlay.css('bottom', caption_height + 10);
						div_overlay = $(envira_container)
							.closest('div.envira-gallery-item-inner')
							.find(
								'.envira-gallery-position-overlay.envira-gallery-bottom-left',
							);
						div_overlay.css('bottom', caption_height + 10);
					}

					$(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('span.envira-title')
						.delay(1000)
						.css('visibility', 'visible');
					$(envira_container)
						.closest('div.envira-gallery-item-inner')
						.find('span.envira-caption')
						.delay(1000)
						.css('visibility', 'visible');

					if (
						window['envira_container_' + event.gallery_id] !==
						undefined
					) {
						if (
							$(
								'#envira-gallery-' + event.gallery_id,
							).hasClass('enviratope')
						) {
							window[
								'envira_container_' + event.gallery_id
							].on('layoutComplete', function(
								event,
								laidOutItems,
							) {
								$(envira_container)
									.closest(
										'div.envira-gallery-item-inner',
									)
									.find('span.envira-title')
									.delay(1000)
									.css('visibility', 'visible');
								$(envira_container)
									.closest(
										'div.envira-gallery-item-inner',
									)
									.find('span.envira-caption')
									.delay(1000)
									.css('visibility', 'visible');
							});
						} else {
						}
					}
				}
			}
		});

		$('.envira-proofing-select-image').on('click', function () {
			const $parent = $(this).parent();
			$parent.css('padding-bottom', '60px');
		});

		$('.envira-layout-polaroid .envira-gallery-item-inner').each(function () {
			let $galleryItemInner = $('.envira-gallery-item-inner');
			var $enviraTitle = $(this).find('.envira-title');
			var $enviraCaption = $(this).find('.envira-caption');

			if ($enviraTitle.is(':visible') && $enviraCaption.is(':visible')) {
				$galleryItemInner.css('min-height', '264px');
			}
			else if ($enviraTitle.is(':visible')) {
				$enviraTitle.css('min-height', '40px');
			}
			else if ($enviraCaption.is(':visible')) {
				$enviraCaption.css('min-height', '44px');
				$enviraCaption.css('margin-top', '20px');
			}
			else {
				$(this).addClass('no-caption');
			}
		});

		/* Creative layout - set row offsets for each set of images. */
		function applyOffsetsToImages() {
			const creativeItems = $('.envira-layout-creative .envira-gallery-item');
			if (creativeItems.length > 0) {
				const creativeItemsPerSet = 9;
				const creativeRowSpan = 15; // Total rows occupied by one set of images.
				const numberOfSets = Math.ceil(creativeItems.length / creativeItemsPerSet);

				// Use a single batch update for better performance.
				const updates = [];

				for (let i = 0; i < numberOfSets; i++) {
					const offset = i * creativeRowSpan;
					const startIndex = i * creativeItemsPerSet;
					const endIndex = startIndex + creativeItemsPerSet;
					creativeItems.slice(startIndex, endIndex).each(function() {
						updates.push({ item: this, offset: offset });
					});
				}

				// Apply CSS updates in batch.
				updates.forEach(update => {
					$(update.item).css('--row-start-offset', update.offset);
				});
			}
		}

		// Call custom function to apply offsets to images.
		applyOffsetsToImages();

		$(document).on('envira_pagination_lazy_load_completed', applyOffsetsToImages);
		$(document).on('envira_pagination_lazy_load_completed_inal', applyOffsetsToImages);

	});
})(
	jQuery,
	window,
	document,
	Envira,
	Envira_Link,
	envira_gallery,
	envira_galleries,
);
