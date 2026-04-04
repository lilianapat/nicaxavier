// Fetch gallery choices and populate the choices instance

function fetchAndPopulateChoices(ajaxurl, action, choicesInstance, previousSelection) {
	fetch(ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
		},
		body: 'action=' + action /* add &_wpnonce=123 */,
		credentials: 'same-origin',
	})
		.then(function (response) {
			return response.json();
		})
		.then(function (data) {
			// Assuming data.galleries is an array of objects with gallery_id and gallery_title
			var options = data.galleries.map(function (gallery) {
				return { value: gallery.gallery_id, label: gallery.gallery_title };
			});
			choicesInstance.setChoices(options, 'value', 'label', true);

			if (previousSelection !== undefined) {
				choicesInstance.setChoiceByValue(previousSelection);
			}
		})
		.catch(function (error) {
			console.log(error);
		});
}


/**
 * Handles:
 * - Copy to Clipboard functionality
 * - Sort/Direction Dropdowns On Gallery Edit Screens
 *
 * @since 1.5.0
 */
jQuery(document).ready(function($) {
	$('.importfile').each(function() {
		var $input = $(this),
			$label = $input.next('label'),
			labelVal = $label.html();

		$input.on('change', function(e) {
			var fileName = '';

			if(this.files && this.files.length > 1){
				fileName = (
					this.getAttribute('data-multiple-caption') || ''
				).replace('{count}', this.files.length);
			} else if(e.target.value){
				fileName = e.target.value.split('\\').pop();
			}

			if(fileName){
				$label.find('span').html(fileName);
			} else {
				$label.html(labelVal);
			}
		});

		// Firefox bug fix
		$input
			.on('focus', function() {
				$input.addClass('has-focus');
			})
			.on('blur', function() {
				$input.removeClass('has-focus');
			});
	});

	$('#screen-meta-links').prependTo('#envira-header-temp');
	$('#screen-meta').prependTo('#envira-header-temp');
	$('#screen-meta-links').css('display', 'block');

	/**
	 * Copy to Clipboard
	 */
	if(typeof Clipboard !== 'undefined'){
		$(document).on('click', '.envira-clipboard', function(e) {
			var envira_clipboard = new Clipboard('.envira-clipboard');
			e.preventDefault();
		});
	}

	/**
	 * Permissions modal -shows when setting user roles.
	 * use A11yDialog library.
	 */

	let perms_container = document.getElementById('envira-permissions-dialog-id');
	if(perms_container){

		let perms_dialog = null;
		perms_dialog = new A11yDialog(perms_container);


		/**
		 * Permissions tab - Select User Roles.
		 * - Uses choices JS
		 */

		// all the select fields that have this class .envira-permissions-select-field are the permissions fields.

		var permissions_select = document.getElementsByClassName('envira-permissions-select-field');

		// store the permissions select elements as a constant.
		const enviraPermissionsChoices = [];

		for(var i = 0; i < permissions_select.length; i ++){
			var current_select = permissions_select[i];
			if(current_select.length > 0){
				let choices_permissions = current_select.getAttribute('id') + '_select';
				enviraPermissionsChoices[choices_permissions] = new Choices(
					current_select, {
						allowHTML: true,
						searchChoices: false,
						searchEnabled: false,
						removeItemButton: true,
						itemSelectText: '',
						addItemText: '',
						shouldSort: false,
						shouldSortItems: false,
						classNames: {
							containerInner: 'choices__inner roles_inner',
							containerOuter: 'choices roles_inner',
						},
					},
				);

				current_select.addEventListener(
					'addItem',
					function(event) {
						check_envira_permissions($(this).attr('id'), event.detail.value, event.detail.label, 'add');
						disable_administrator();
					},
					false,
				);

				current_select.addEventListener(
					'removeItem',
					function(event) {
						check_envira_permissions($(this).attr('id'), event.detail.value, event.detail.label, 'remove');
						disable_administrator();
					},
					false,
				);
			}
		}


		// find all choices that has 'administrator' as a value and disable it.

		function disable_administrator() {
			$('.choices__item').each(function() {
				if($(this).attr('data-value') === "administrator"){
					$(this).removeClass('choices__item--selectable');
					$(this).removeAttr('data-deletable');
					$(this).removeAttr('data-item');
					$(this).addClass('demos');
					$(this).off('click');
				}
			});

			$('.choices__button').each(function() {
				if($(this).attr('aria-label') === "Remove item: '\administrator\'"){
					$(this).hide();
				}
			});
		}


		disable_administrator();



		function check_envira_permissions(element, value, label, type) {
			if(!enviraPermissions || !enviraPermissions.hasOwnProperty(element)){
				return;
			}
			// get other elements value and see if it is already selected.
			var permissionLabel = enviraPermissionsLabels[element];
			var other_elements = enviraPermissions[element];
			let choices_permissions = element + '_select';
			let reqPermissionLabel = get_envira_permissions(other_elements, type, value);
			reqPermissionLabel = reqPermissionLabel.filter(function(item) {
				return item !== permissionLabel;
			});
			let message = '';
			let permission_text = reqPermissionLabel.length > 1 ? 'permissions' : 'permission'

			if(type === 'add'){
				message = `<p>In order to give <strong>${permissionLabel}</strong> permission,
				<strong>${reqPermissionLabel}</strong> ${permission_text} ${reqPermissionLabel.length > 1 ? 'are' : 'is'} also required.</p>`;
				message += `<p>Would you like to also grant <strong>${reqPermissionLabel}</strong> ${permission_text} to ${label}?</p>`;
			} else {
				message = `<p>In order to remove <strong>${permissionLabel}</strong> permission,
				<strong>${reqPermissionLabel}</strong> ${permission_text} will also be removed.</p>`;
				message += `<p>Would you like to also remove <strong>${reqPermissionLabel}</strong> ${permission_text} to <strong>${label}</strong>?</p>`;
			}

			if(reqPermissionLabel.length > 0){
				$('#envira-permissions-alert').html(message);
				perms_dialog.show();
				$('.envira-permissions-yes').on('click', function() {
					update_envira_permissions(other_elements, value, type);
					perms_dialog.hide();
				});
				$('.envira-permissions-cancel').on('click', function() {
					if(type === 'add'){
						enviraPermissionsChoices[choices_permissions].removeActiveItemsByValue(value);
					}
					if(type === 'remove'){
						enviraPermissionsChoices[choices_permissions].setChoiceByValue(value);
					}
					perms_dialog.hide();
				});
			} else {
				update_envira_permissions(other_elements, value, type);
			}
			// Hide the choices after the dialog is closed.
			setTimeout(() => enviraPermissionsChoices[choices_permissions].hideDropdown(), 0);
		}


		function update_envira_permissions(elements, value, type) {

			for(var i = 0; i < elements.length; i ++){
				let element = elements[i];

				// Select the dropdown element
				let selectElement = document.querySelector('#' + element);

				// Get all selected options
				let selectedOptions = selectElement.selectedOptions;

				// Initialize an array to store the selected values
				let selectedValues = [];

				var choices_permissions = element + '_select';
				let reqPermissionLabel = enviraPermissionsLabels[element];
				// Loop through all selected options and get their values
				for(let option of selectedOptions){
					selectedValues.push(option.value);
				}

				let key = Object.keys(enviraPermissionsLabels).find(key => enviraPermissionsLabels[key] === reqPermissionLabel);
				let currentPermission = enviraPermissionsChoices[key + '_select'];
				// check if the value is in the selected values array.
				if(type === 'add'){
					if(selectedValues.indexOf(value) === - 1){
						selectedValues.push(value);
						currentPermission.setChoiceByValue(value);
					}
				} else {
					if(selectedValues.indexOf(value) !== - 1){
						selectedValues = selectedValues.filter(function(item) {
							return item !== value;
						});
						currentPermission.removeActiveItemsByValue(value);
					}
				}
			}
		}

		function get_envira_permissions(elements, type, value) {

			var reqPermissionLabel = [];

			for(var i = 0; i < elements.length; i ++){

				let element = elements[i];

				// Select the dropdown element
				let selectElement = document.querySelector('#' + element);

				// Get all selected options
				let selectedOptions = selectElement.selectedOptions;

				// Initialize an array to store the selected values
				let selectedValues = [];

				// Loop through all selected options and get their values
				for(let option of selectedOptions){
					selectedValues.push(option.value);
				}

				if(type === 'add'){
					if(selectedValues.indexOf(value) === - 1){
						selectedValues.push(value);
						reqPermissionLabel.push(enviraPermissionsLabels[element]);
					}
				} else {
					if(selectedValues.indexOf(value) !== - 1){
						selectedValues = selectedValues.filter(function(item) {
							return item !== value;
						});
						reqPermissionLabel = reqPermissionLabel.filter(function(item) {
							return item !== enviraPermissionsLabels[element];
						});
					}
				}
			}

			return reqPermissionLabel;

		}
	}
	/**
	 * Sort/Direction Dropdowns On Gallery Edit Screens
	 * - Uses choices JS
	 */

	if($('#envira-config-image-sort').length > 0){
		var imageSort = document.getElementById('envira-config-image-sort');
		var envira_image_sort_choice = new Choices(
			imageSort, {
				allowHTML: true,
				searchChoices: false,
				searchEnabled: false,
				itemSelectText: '',
				addItemText: '',
				shouldSort: false,
				shouldSortItems: false,
				classNames: {
					containerInner: 'choices__inner sort_inner',
					containerOuter: 'choices sort_inner',
				},
			},
		);
	}

	if($('#envira-config-image-sort-dir').length > 0){
		var imageSortDir = document.getElementById('envira-config-image-sort-dir');
		var envira_image_sort_dir_choice = new Choices(
			imageSortDir, {
				allowHTML: true,
				searchChoices: false,
				searchEnabled: false,
				itemSelectText: '',
				addItemText: '',
				classNames: {
					containerInner: 'choices__inner sort_dir',
					containerOuter: 'choices sort_dir',
				},
			},
		);
	}

	/**
	 * Widget Dropdowns
	 * - Uses choices JS
	 */

	$('.widgets-sortables').on('click', 'div.widget-top', function(
		event,
		element,
	) {
		var the_id = $(this)
			.parent()
			.attr('id');

		if(the_id.indexOf('envira-album') !== - 1){
			var action = 'envira_widget_get_albums';
			var keyword = 'album';
		} else {
			var action = 'envira_widget_get_galleries';
			var keyword = 'gallery';
		}

		var previous_selection = $(
				'#' + the_id + ' select.form-control',
			).val(),
			previous_selection_text = $(
				'#' + the_id + ' select.form-control option:selected',
			).text();

		/* clear to prevent duplicates */

		$('#' + the_id + ' select.form-control')
			.find('option')
			.remove();

		if($('#' + the_id + ' select.form-control').length > 0){
			var singleFetch = new Choices(
				'#' + the_id + ' select.form-control', {
					searchPlaceholderValue: 'Search for an ' + keyword,
					loadingText: '',
					itemSelectText: '',
				},
			);

			fetchAndPopulateChoices(ajaxurl, action, singleFetch, previous_selection);
		}
	});

	$(document).on('widget-updated', function(event, widget) {
		var widget_id = $(widget).attr('id');

		if(widget_id.indexOf('album') !== - 1){
			var action = 'envira_widget_get_albums';
			var keyword = 'album';
		} else {
			var action = 'envira_widget_get_galleries';
			var keyword = 'gallery';
		}

		var previous_selection = $(
				'#' + widget_id + ' select.form-control',
			).val(),
			previous_selection_text = $(
				'#' + widget_id + ' select.form-control option:selected',
			).text();

		/* clear to prevent duplicates */

		$('#' + widget_id + ' select.form-control')
			.find('option')
			.remove();

		if($('#' + widget_id + ' select.form-control').length > 0){
			var singleFetch = new Choices(
				'#' + widget_id + ' select.form-control', {
					searchPlaceholderValue: 'Search for an ' + keyword,
					loadingText: '',
					itemSelectText: '',
				},
			);

			fetchAndPopulateChoices(ajaxurl, action, singleFetch, previous_selection);
		}

		// any code that needs to be run when a widget gets updated goes here
		// widget_id holds the ID of the actual widget that got updated
		// be sure to only run the code if one of your widgets got updated
		// otherwise the code will be run when any widget is updated
	});

	$(document).on('widget-added', function(event, widget) {
		var widget_id = $(widget).attr('id');
		// any code that needs to be run when a new widget gets added goes here
		// widget_id holds the ID of the actual widget that got added
		// be sure to only run the code if one of your widgets got added
		// otherwise the code will be run when any widget is added
	});

	$('#envira-top-notification .notice-dismiss').on('click', function () {
		$('#envira-top-notification').remove();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'envira_dismiss_upsell_notification',
			}
		});
	});
});

/**
 * Envira notifications drawer.
 */
(function($, window, envira_gallery_admin_notifications ) {

	let envira_notifications;
	window.envira_notifications = envira_notifications = {
		init() {
			var app = this;
			app.$drawer = $( '#envira-notifications-drawer' );
			app.find_elements();
			app.init_open();
			app.init_close();
			app.init_dismiss();
			app.init_view_switch();
			app.update_count( app.active_count );
		},

		should_init() {
			var app = this;
			return app.$drawer.length > 0;
		},
		find_elements() {
			var app = this;
			app.$open_button      = $( '#envira-notifications-button' );
			app.$count            = app.$drawer.find( '#envira-notifications-count' );
			app.$dismissed_count  = app.$drawer.find( '#envira-notifications-dismissed-count' );
			app.active_count      = app.$open_button.data( 'count' ) ? app.$open_button.data( 'count' ) : 0;
			app.dismissed_count   = app.$open_button.data( 'dismissed' );
			app.$body             = $( 'body' );
			app.$dismissed_button = $( '#envira-notifications-show-dismissed' );
			app.$active_button    = $( '#envira-notifications-show-active' );
			app.$active_list      = $( '.envira-notifications-list .envira-notifications-active' );
			app.$dismissed_list   = $( '.envira-notifications-list .envira-notifications-dismissed' );
			app.$dismiss_all      = $( '#envira-dismiss-all' );
		},
		update_count( count ) {
			var app = this;
			app.$open_button.data( 'count', count ).attr( 'data-count', count );
			if ( 0 === count ) {
				app.$open_button.removeAttr( 'data-count' );
			}
			app.$count.text( count );
			app.dismissed_count += Math.abs( count - app.active_count );
			app.active_count     = count;

			app.$dismissed_count.text( app.dismissed_count );

			if ( 0 === app.active_count ) {
				app.$dismiss_all.hide();
			}
		},
		init_open() {
			var app = this;
			app.$open_button.on(
				'click',
				function ( e ) {
					e.preventDefault();
					app.$body.addClass( 'envira-notifications-open' );
				}
			);
		},
		init_close() {

			var app = this;
			app.$body.on(
				'click',
				'.envira-notifications-close, .envira-notifications-overlay',
				function ( e ) {
					e.preventDefault();
					app.$body.removeClass( 'envira-notifications-open' );
				}
			);
		},
		init_dismiss() {
			var app = this;
			app.$drawer.on(
				'click',
				'.envira-notification-dismiss',
				function ( e ) {
					e.preventDefault();
					const id = $( this ).data( 'id' );
					app.dismiss_notification( id );
					if ( 'all' === id ) {
						app.move_to_dismissed( app.$active_list.find( 'li' ) );
						app.update_count( 0 );
						return;
					}
					app.move_to_dismissed( $( this ).closest( 'li' ) );
					app.update_count( app.active_count - 1 );
				}
			);
		},
		move_to_dismissed( element ) {
			var app = this;
			element.slideUp(
				function () {
					$( this ).prependTo( app.$dismissed_list ).show();
				}
			);
		},
		dismiss_notification( id ) {
			var app = this;
			return $.post(
				ajaxurl,
				{
					action: 'envira_notification_dismiss',
					nonce: envira_gallery_admin_notifications.dismiss_notification_nonce,
					id: id,
				}
			);
		},
		init_view_switch() {
			var app = this;
			app.$dismissed_button.on(
				'click',
				function ( e ) {
					e.preventDefault();
					app.$drawer.addClass( 'show-dismissed' );
				}
			);
			app.$active_button.on(
				'click',
				function ( e ) {
					e.preventDefault();
					app.$drawer.removeClass( 'show-dismissed' );
				}
			);
		}
	};

	// DOM ready.
	$(function() {
		envira_notifications.init();
	});

})(jQuery, window, envira_gallery_admin_notifications );
