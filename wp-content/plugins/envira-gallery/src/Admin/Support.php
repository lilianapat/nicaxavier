<?php
/**
 * Support class.
 *
 * @since 1.8.1
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Support class.
 *
 * @since 1.8.1
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */
class Support {

	/**
	 * Holds the submenu pagehook.
	 *
	 * @since 1.7.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds gallery transient status.
	 *
	 * @since 1.7.0
	 *
	 * @var string
	 */
	public $gallery_transient_status;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the nonce name.
	 *
	 * @var string Nonce action for the support page.
	 */
	public $nonce_action = 'envira-support';

	/**
	 * Holds the nonce name.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.1
	 */
	public function __construct() {
		// Register the admin page.
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 15 );

		// Do actions on each tab.
		add_action( 'load-envira_page_envira-gallery-support-general', [ $this, 'load' ] );
		add_action( 'load-envira_page_envira-gallery-support-api', [ $this, 'load' ] );
		add_action( 'load-envira_page_envira-gallery-support-tools', [ $this, 'load' ] );
		add_action( 'load-envira_page_envira-gallery-support-logs', [ $this, 'load' ] );
		add_action( 'load-envira_page_envira-gallery-support-settings', [ $this, 'load' ] );

		// Load Admin Bar.
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_support_button' ], 9999 );

		$this->gallery_transient_status = get_option( 'eg_t_gallery_status' );
	}

	/**
	 * Load all actions.
	 *
	 * @return void
	 */
	public function load() {
		$valid_request = isset( $_REQUEST['action'], $_REQUEST['envira_nonce'] );
		$valid_request = $valid_request && wp_verify_nonce( sanitize_key( $_REQUEST['envira_nonce'] ), $this->nonce_action );

		if ( $valid_request ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			switch ( $action ) {
				case 'delete-options': // General Tab.
					$this->clear_all_envira_options();
					break;
				case 'toggle-debug': // General Tab.
					$this->toggle_debug();
					break;
				case 'test-api':  // API Tab.
					$this->test_api();
					break;
				case 'show_admin_bar': // Settings Tab.
					$this->show_admin_bar();
					break;
				case 'edit-gallery-settings':
					$this->envira_edit_gallery_settings();
					break;
				case 'tools-fix-image-links':
					add_action( 'envira_support_notices', [ $this, 'tools_fix_image_links_gallery' ] );
					break;
				case 'tools-repair-gallery':
					add_action( 'envira_support_notices', [ $this, 'tools_repair_gallery' ] );
					break;
			}
		}

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], [ 'view-gallery-settings', 'edit-gallery-settings' ], true ) ) {
			add_action( 'envira_view_gallery_settings', [ $this, 'envira_display_gallery_settings' ] );
		}
	}

	/**
	 * Register and enqueue addons page specific CSS.
	 *
	 * @since 1.8.1
	 */
	public function admin_styles() {

		wp_register_style( ENVIRA_SLUG . '-support-style', plugins_url( 'assets/css/support.css', ENVIRA_FILE ), [], time() );
		wp_enqueue_style( ENVIRA_SLUG . '-support-style' );

		do_action( 'envira_support_styles' );
	}

	/**
	 * Ability to view a particular gallery setting(s).
	 *
	 * @since 1.8.4.1
	 */
	public function envira_display_gallery_settings() {
		// phpcs:ignore WordPress.Security -- Nonce verification handled upstream.
		if ( 'view-gallery-settings' !== $_REQUEST['action'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security -- Nonce verification handled upstream.
		if ( ! isset( $_REQUEST['gallery_id'] ) || 0 === intval( $_REQUEST['gallery_id'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security -- Nonce verification handled upstream.
		$gallery_id = intval( $_REQUEST['gallery_id'] );

		$settings = json_decode( envira_get_gallery_config( $gallery_id ) );

		$html = '<form action="" method="post">';

		$html .= '<table style="width: 50%; margin-top: 20px; border: 1px solid #ccc;">';

		$html .= '<thead style="background-color: #ccc;"><th>Setting Name</th><th>Setting Value</th></thead><tbody>';

		foreach ( $settings as $setting_name => $setting_value ) {
			if ( is_array( $setting_name ) || is_array( $setting_value ) ) {
				continue;
			} else {
				$html .= '<tr>
						<td width="50%">' . $setting_name . '</td>
						<td><input style="width: 100%; font-size: 14px;" type="text" name="value-' . $setting_name . '" value="' . $setting_value . '" /></td>
					</tr>';
			}
		}

		$html .= '</tbody></table>';

		$html .= $this->get_nonce_field( true );

		$html .= '     <br/>
					<input type="hidden" name="action" value="edit-gallery-settings" />
					<input type="hidden" name="gallery_id" value="' . $gallery_id . '" />
				     <input type="submit" class="button button-primary" value="Save Edited Settings" /></form>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentionally outputting HTML.
		echo $html;
	}

	/**
	 * Ability to edit a particular gallery config setting(s).
	 *
	 * @since 1.8.4.1
	 */
	public function envira_edit_gallery_settings() {
		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification handled upstream.
		if ( ! isset( $_POST['gallery_id'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification handled upstream.
		$gallery_id = intval( $_POST['gallery_id'] );

		$gallery_data = get_post_meta( $gallery_id, '_eg_gallery_data', true );
		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification handled upstream.
		$updated_settings  = $_POST;
		$something_changed = false;

		if ( empty( $gallery_data ) || empty( $updated_settings ) ) {
			return;
		}

		foreach ( $gallery_data['config'] as $setting => $value ) {
			// locate the setting in the updated, and if it exists use the new value.
			if ( in_array( 'value-' . $setting, array_keys( $updated_settings ), true ) && $updated_settings[ 'value-' . $setting ] !== $value ) {
				$something_changed                  = true;
				$gallery_data['config'][ $setting ] = $updated_settings[ 'value-' . $setting ];
			}
		}

		if ( $something_changed ) {
			// update settings for that gallery.
			update_post_meta( $gallery_id, '_eg_gallery_data', $gallery_data );
			envira_flush_all_cache();
			add_action( 'envira_support_notices', [ $this, 'updated_gallery_message' ], 10 );
		}
	}

	/**
	 * Display updated notice message
	 *
	 * @since 1.8.4.1
	 */
	public function updated_gallery_message() {
		?>
		<div class="notice notice-warning"><p>Updated gallery settings.</p></div>
		<?php
	}

	/**
	 * Ability to 'fix' image links in a gallery
	 *
	 * @since 1.9.3.6
	 */
	public function tools_repair_gallery() {
		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification handled upstream.
		$valid_request = isset( $_POST['gallery_id'] );

		if ( ! $valid_request ) {
			return;
		}

		$all_galleries = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled upstream.
		if ( 'all' === $_POST['gallery_id'] ) {
			$all_galleries = _envira_get_galleries( true, '', -1 );
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification handled upstream.
			$gallery_id   = intval( $_POST['gallery_id'] );
			$gallery_data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

			$all_galleries[ "$gallery_id" ] = $gallery_data;
		}
		?>

		<div class="notice notice-warning">
			<?php
			foreach ( $all_galleries as $gallery_id => $gallery_data ) {
				$gallery_id    = intval( $gallery_id );
				$in_gallery    = get_post_meta( $gallery_id, '_eg_in_gallery', true );
				$gallery_data  = get_post_meta( $gallery_id, '_eg_gallery_data', true );
				$removed       = 0;
				$post          = get_post( $gallery_id );
				$gallery_title = $post->post_name;

				foreach ( $in_gallery as $index => $in_gallery_image_id ) {
					if ( ! array_key_exists( $in_gallery_image_id, $gallery_data['gallery'] ) ) {
						// the image doesn't exist in the gallery data, so let's remove it from the in_gallery meta.
						unset( $in_gallery[ $index ] );
						++$removed;
					}
				}

				if ( $removed > 0 ) {
					update_post_meta( $gallery_id, '_eg_in_gallery', $in_gallery );
				}

				echo '<p>Removed a total of <strong>' . esc_html( $removed ) . '</strong> items for the <strong>' . esc_html( $gallery_title ) . '</strong> gallery.</p>';

			}

			?>

		</div>

		<?php
	}

	/**
	 * Ability to 'fix' image links in a gallery
	 *
	 * @since 1.8.1
	 */
	public function tools_fix_image_links_gallery() {
		// phpcs:disable WordPress.Security.NonceVerification -- Nonce verification is handled upstream.
		$valid_request = isset( $_POST['gallery_id'] );

		if ( ! $valid_request ) {
			return;
		}

		$all_galleries = [];

		if ( 'all' === $_POST['gallery_id'] ) {
			$all_galleries = _envira_get_galleries( true, '', -1 );
		} else {
			$gallery_id   = intval( $_POST['gallery_id'] );
			$gallery_data = get_post_meta( $gallery_id, '_eg_gallery_data', true );
			// Add title.
			if ( isset( $gallery_data['config'] ) && is_array( $gallery_data['config'] ) ) {
				$gallery_data['config']['title'] = get_the_title( $gallery_id );
			}
			$all_galleries[ "$gallery_id" ] = $gallery_data;
		}
		ksort( $all_galleries );

		?>
		<div class="notice notice-warning">
			<?php
			foreach ( $all_galleries as $gallery_id => $data ) {
				$updated       = 0;
				$gallery_title = envira_get_config( 'title', $data );
				foreach ( $data['gallery'] as $item_id => $item ) {
					if ( empty( $data['gallery'][ $item_id ]['src'] ) || empty( $data['gallery'][ $item_id ]['link'] ) ) {
						++$updated;
					}

					if ( empty( $data['gallery'][ $item_id ]['src'] ) ) {
						$src = wp_get_attachment_url( $item_id );
						if ( false === $src ) {
							unset( $data['gallery'][ $item_id ] );
							continue;
						}
						$data['gallery'][ $item_id ]['src'] = $src;
						echo '<p>Updated src of image #' . esc_html( $item_id ) . ' to ' . esc_url( $data['gallery'][ $item_id ]['src'] ) . '</p>';
					}

					$valid_link = isset( $data['gallery'][ $item_id ]['link'] ) && wp_http_validate_url( $data['gallery'][ $item_id ]['link'] );

					if ( ! $valid_link ) {
						$data['gallery'][ $item_id ]['link'] = $data['gallery'][ $item_id ]['src'];
						echo '<p>Updated link of image #' . esc_html( $item_id ) . ' to ' . esc_url( $data['gallery'][ $item_id ]['src'] ) . '</p>';
					}
				}

				if ( $updated > 0 ) {
					update_post_meta( $gallery_id, '_eg_gallery_data', $data );
				}
				echo '<p>Updated a total of <strong>' . esc_html( $updated ) . '</strong> items for the <strong>' . esc_html( $gallery_title ) . '</strong> gallery.</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Total transients
	 *
	 * @since 1.8.1
	 */
	public function total_transients() {

		global $wpdb;

		// get current PHP time, offset by a minute to avoid clashes with other tasks.
		$threshold = time() - MINUTE_IN_SECONDS;

		// count transient expiration records, total and expired.
		$sql = "
			select count(*) as `total`, count(case when option_value < '$threshold' then 1 end) as `expired`
			from " . $wpdb->options . "
			where (option_name like '%_transient_timeout_%' or option_name like '%_site_transient_timeout_%')
		";

		// phpcs:ignore WordPress.DB -- Static db query.
		$counts = $wpdb->get_row( $sql );

		// count never-expire transients.
		$sql = "
			select count(*)
			from $wpdb->options
			where (option_name like '\_transient\_%' or option_name like '\_site\_transient\_%')
			and option_name not like '%\_timeout\_%'
			and autoload = 'yes'
		";
		// phpcs:ignore WordPress.DB -- Static db query.
		$counts->never_expire = $wpdb->get_var( $sql );

		return $counts;
	}

	/**
	 * Total Envira transients
	 *
	 * @since 1.8.1
	 */
	public function total_envira_transients() {

		global $wpdb;
		// phpcs:ignore WordPress.DB -- Static db query.
		$results = $wpdb->get_results(
			"SELECT option_name, option_value FROM $wpdb->options WHERE
			option_name LIKE ('%_transient__eg_%')
			OR option_name LIKE ('%_transient_timeout__eg_%')
			OR option_name LIKE ('%_transient__ea_%');"
		);

		return count( $results );
	}

	/**
	 * Total Options
	 *
	 * @since 1.8.1
	 */
	public function total_options() {
		global $wpdb;

		// phpcs:ignore WordPress.DB -- Static db query.
		$results = $wpdb->get_results(
			"SELECT option_name, option_value FROM $wpdb->options WHERE
			option_name LIKE ('eg_%');"
		);

		return $results;
	}


	/**
	 * Clear All Options
	 *
	 * @since 1.8.1
	 */
	public function clear_all_envira_options() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL,WordPress.DB.DirectDatabaseQuery -- we know what we are doing, I think.
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE ('eg_%');" );

		delete_option( 'envira_display_welcome' );
	}

	/**
	 * Toggle Debug.
	 */
	public function toggle_debug() {
		if ( get_option( 'envira_debug' ) ) {
			// Remove option instead of setting it as false.
			delete_option( 'envira_debug' );
		} else {
			add_option( 'envira_debug', true );
		}
	}

	/**
	 * Test API
	 *
	 * @since 1.8.1
	 */
	public function test_api() {
		$license = new \Envira\Admin\License();

		$key     = envira_get_license_key();
		$action  = 'verify-key';
		$headers = [];

		// Build the body of the request.
		$body = wp_parse_args(
			[ 'tgm-updater-key' => $key ],
			[
				'tgm-updater-action'     => $action,
				'tgm-updater-key'        => $key,
				'tgm-updater-wp-version' => get_bloginfo( 'version' ),
				'tgm-updater-referer'    => site_url(),
			]
		);
		$body = http_build_query( $body, '', '&' );

		// Build the headers of the request.
		$headers = wp_parse_args(
			$headers,
			[
				'Content-Type'   => 'application/x-www-form-urlencoded',
				'Content-Length' => strlen( $body ),
			]
		);

		// Setup variable for wp_remote_post.
		$post = [
			'headers' => $headers,
			'body'    => $body,
		];

		// Perform the query and retrieve the response.
		$response      = wp_remote_post( 'https://enviragallery.com', $post );
		$response_code = wp_remote_retrieve_response_code( $response ); /* log this for API issues */
		$response_body = wp_remote_retrieve_body( $response );

		update_option( 'envira_tb_api_response_code', $response_code );
		update_option( 'envira_tb_api_response_body', $response_body );
		update_option( 'envira_tb_api_response', $response );
		update_option( 'envira_tb_api_time_tested', time() );
	}

	/**
	 * Settings
	 *
	 * @since 1.8.1
	 */
	public function show_admin_bar() {
		// phpcs:ignore WordPress.Security.NonceVerification -- Nonce verification done upstream.
		if ( isset( $_POST['support_show_admin_bar'] ) && intval( $_POST['support_show_admin_bar'] ) === 1 ) {
			update_option( 'eg_admin_bar', 1 );
		} else {
			delete_option( 'eg_admin_bar' );
		}
		do_action( 'envira_support_save_settings' );
	}

	/**
	 * Get API Info
	 *
	 * @since 1.8.1
	 */
	public function get_api_info() {

		return ( [
			'response'      => get_option( 'envira_tb_api_response' ),
			'response_body' => get_option( 'envira_tb_api_response_body' ),
			'response_code' => get_option( 'envira_tb_api_response_code' ),
			'time_tested'   => get_option( 'envira_tb_api_time_tested' ),
		] );
	}

	/**
	 * Get Total Galleries
	 *
	 * @since 1.8.1
	 */
	public function total_galleries() {

		return ( wp_count_posts( 'envira' )->publish );
	}

	/**
	 * Get Total Albums
	 *
	 * @since 1.8.1
	 */
	public function total_albums() {

		if ( class_exists( 'Envira_Albums' ) ) {
			return ( wp_count_posts( 'envira_album' )->publish );
		} else {
			return 0;
		}
	}

	/**
	 * Get Problem Galleries
	 *
	 * @since 1.8.1
	 *
	 * @param integer $image_limit Image limit.
	 */
	public function get_problem_galleries( $image_limit = 100 ) {

		$galleries         = _envira_get_galleries( true, null, 100 );
		$problem_galleries = [];
		if ( empty( $galleries ) ) {
			return false;
		}
		foreach ( $galleries as $gallery ) {
			$gallery_id = intval( $gallery['id'] );
			/* check image count */
			$image_count = envira_get_gallery_image_count( $gallery_id );
			if ( $image_count >= $image_limit ) {
				$problem_galleries[] = [
					'link'        => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'       => ( 'Gallery #' . $gallery['id'] ),
					'image_count' => $image_count,
				];
			}
			/* check orphaned settings */
			$settings = json_decode( envira_get_gallery_config( $gallery_id ) );
			if ( property_exists( $settings, 'proofing_lightbox' ) && ! class_exists( 'Envira_Proofing' ) ) {
				$problem_galleries[] = [
					'link'       => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'      => ( 'Gallery #' . $gallery['id'] ),
					'issue_text' => 'has \'proofing_lightbox\' setting but the <strong>Proofing Addon</strong> is not installed or activated.',
				];
			}
			if ( property_exists( $settings, 'custom_css' ) && ! defined( 'ENVIRA_CUSTOM_CSS_PLUGIN_NAME' ) ) {
				$problem_galleries[] = [
					'link'       => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'      => ( 'Gallery #' . $gallery['id'] ),
					'issue_text' => 'has a \'custom_css\' setting but the <strong>CSS Addon</strong> is not installed or activated.',
				];
			}
			if ( ( property_exists( $settings, 'exif' ) || property_exists( $settings, 'exif_lightbox' ) ) && ! class_exists( 'Envira_Exif' ) ) {
				$problem_galleries[] = [
					'link'       => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'      => ( 'Gallery #' . $gallery['id'] ),
					'issue_text' => 'has a \'exif\' setting but the <strong>EXIF Addon</strong> is not installed or activated.',
				];
			}
			if ( ( property_exists( $settings, 'pagination_images_per_page' ) && 1 === $settings->pagination && ! class_exists( 'Envira_Pagination' ) ) ) {
				$problem_galleries[] = [
					'link'       => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'      => ( 'Gallery #' . $gallery['id'] ),
					'issue_text' => 'has pagination settings but the <strong>Pagination Addon</strong> is not installed or activated.',
				];
			}
			/* check settings set to 0 */
			if ( property_exists( $settings, 'pagination_images_per_page' ) && 1 === $settings->pagination && 0 === $settings->pagination_images_per_page && class_exists( 'Envira_Pagination' ) ) {
				$problem_galleries[] = [
					'severity'   => 'warning',
					'link'       => admin_url( 'post.php?post=' . $gallery['id'] . '&action=edit' ),
					'title'      => ( 'Gallery #' . $gallery['id'] ),
					'issue_text' => 'has \'pagination\' activated but the <strong>Images Per Page</strong> setting is set to " ' . $settings['pagination_images_per_page'] . '"',
				];
			}
		}

		return $problem_galleries;
	}

	/**
	 * Get Problem Albums
	 *
	 * @since 1.8.1
	 *
	 * @param integer $gallery_limit Image limit.
	 */
	public function get_problem_albums( $gallery_limit = 100 ) {

		$albums         = _envira_get_albums( true, null );
		$problem_albums = [];
		if ( empty( $albums ) ) {
			return false;
		}
		foreach ( $albums as $album ) {
			$album_id = intval( $album['id'] );
			/* check gallery count */
			$gallery_count = count( $album['galleries'] );
			if ( $gallery_count >= $gallery_limit ) {
				$problem_albums[] = [
					'link'        => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'       => ( 'Album #' . $album['id'] ),
					'image_count' => $gallery_limit,
				];
			}
			/* check orphaned settings */
			$settings = $album['config'];
			if ( in_array( 'proofing_lightbox', $settings, true ) && ! class_exists( 'Envira_Proofing' ) ) {
				$problem_albums[] = [
					'link'       => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'      => ( 'Album #' . $album['id'] ),
					'issue_text' => 'has \'proofing_lightbox\' setting but the <strong>Proofing Addon</strong> is not installed or activated.',
				];
			}
			if ( in_array( 'custom_css', $settings, true ) && ! defined( 'ENVIRA_CUSTOM_CSS_PLUGIN_NAME' ) ) {
				$problem_albums[] = [
					'link'       => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'      => ( 'Album #' . $album['id'] ),
					'issue_text' => 'has a \'custom_css\' setting but the <strong>CSS Addon</strong> is not installed or activated.',
				];
			}
			if ( ( in_array( 'exif', $settings, true ) || in_array( 'exif_lightbox', $settings, true ) ) && ! class_exists( 'Envira_Exif' ) ) {
				$problem_albums[] = [
					'link'       => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'      => ( 'Album #' . $album['id'] ),
					'issue_text' => 'has a \'exif\' setting but the <strong>EXIF Addon</strong> is not installed or activated.',
				];
			}
			if ( ( in_array( 'pagination_images_per_page', $settings, true ) && 1 === $settings['pagination'] && ! class_exists( 'Envira_Pagination' ) ) ) {
				$problem_albums[] = [
					'link'       => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'      => ( 'Album #' . $album['id'] ),
					'issue_text' => 'has pagination settings but the <strong>Pagination Addon</strong> is not installed or activated.',
				];
			}
			/* check settings set to 0 */
			if ( in_array( 'pagination_images_per_page', $settings, true ) && 1 === $settings['pagination'] && 0 === $settings['pagination_images_per_page'] && class_exists( 'Envira_Pagination' ) ) {
				$problem_albums[] = [
					'severity'   => 'warning',
					'link'       => admin_url( 'post.php?post=' . $album['id'] . '&action=edit' ),
					'title'      => ( 'Album #' . $album['id'] ),
					'issue_text' => 'has \'pagination\' activated but the <strong>Galleries Per Page</strong> setting is set to " ' . $settings['pagination_images_per_page'] . '"',
				];
			}
		}

		return $problem_albums;
	}

	/**
	 * Is Gallery On/Off
	 *
	 * @since 1.8.1
	 * return string
	 */
	public function is_gallery_on_or_off() {

		if ( get_option( 'eg_t_gallery_status' ) && get_option( 'eg_t_gallery_status' ) === 'disabled' ) {
			return 'Off';
		} else {
			return 'On';
		}
	}

	/**
	 * Is Album On/Off
	 *
	 * @since 1.8.1
	 * return string
	 */
	public function is_album_on_or_off() {

		if ( get_option( 'eg_t_album_status' ) && get_option( 'eg_t_album_status' ) === 'disabled' ) {
			return 'Off';
		} else {
			return 'On';
		}
	}

	/**
	 * Output tab navigation
	 *
	 * @since 2.2.0
	 *
	 * @param string $tab Tab to highlight as active.
	 */
	public static function tab_navigation( $tab = 'general' ) {
		?>

		<h3 class="nav-tab-wrapper">
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-support-general' === $_GET['page'] ) :
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							[
								'post_type' => 'envira',
								'page'      => 'envira-gallery-support-general',
							],
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'General', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-support-api' === $_GET['page'] ) :
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							[
								'post_type' => 'envira',
								'page'      => 'envira-gallery-support-api',
							],
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'API', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-support-tools' === $_GET['page'] ) :
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							[
								'post_type' => 'envira',
								'page'      => 'envira-gallery-support-tools',
							],
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Tools', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-support-logs' === $_GET['page'] ) :
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							[
								'post_type' => 'envira',
								'page'      => 'envira-gallery-support-logs',
							],
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Logs', 'envira-gallery' ); ?>
			</a>
			<a class="nav-tab
			<?php
			if ( isset( $_GET['page'] ) && 'envira-gallery-support-settings' === $_GET['page'] ) :
				?>
				nav-tab-active<?php endif; ?>" href="
				<?php
				echo esc_url(
					admin_url(
						add_query_arg(
							[
								'post_type' => 'envira',
								'page'      => 'envira-gallery-support-settings',
							],
							'edit.php'
						)
					)
				);
				?>
														">
				<?php esc_html_e( 'Settings', 'envira-gallery' ); ?>
			</a>
		</h3>

		<?php
	}


	/**
	 * Register the Support submenu item for Envira.
	 *
	 * @since 1.8.1
	 */
	public function admin_menu() {
		$whitelabel = apply_filters( 'envira_whitelabel', false ) ? '' : 'Envira Gallery ';

		// Register the submenu.
		add_submenu_page(
			'',
			$whitelabel . __( 'Support: General', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-general',
			[ $this, 'general_page' ]
		);

		add_submenu_page(
			'',
			$whitelabel . __( 'Support: API', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: API', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-api',
			[ $this, 'api_page' ]
		);

		add_submenu_page(
			'',
			$whitelabel . __( 'Support: Tools', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Tools', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-tools',
			[ $this, 'tools_page' ]
		);

		add_submenu_page(
			'',
			$whitelabel . __( 'Support: Logs', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Logs', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-logs',
			[ $this, 'logs_page' ]
		);

		add_submenu_page(
			'',
			$whitelabel . __( 'Support: Settings', 'envira-gallery' ),
			'<span style="color:#FFA500"> ' . __( 'Support: Settings', 'envira-gallery' ) . '</span>',
			apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
			ENVIRA_SLUG . '-support-settings',
			[ $this, 'settings_page' ]
		);
	}

	/**
	 * Output the general screen.
	 *
	 * @since 1.8.1
	 */
	public function general_page() {
		?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php $this->tab_navigation(); ?>

					<?php do_action( 'envira_support_notices' ); ?>

					<div class="card">
						<h2 class="title">Information</h2>
						<p><strong>Number of Galleries:</strong>&nbsp;&nbsp;<?php echo esc_html( $this->total_galleries() ); ?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<strong>Number of Albums:</strong>&nbsp;&nbsp;<?php echo esc_html( $this->total_albums() ); ?></p>
						<blockquote style="line-height: 13px;">
							<ul>
								<?php

									$galleries = $this->get_problem_galleries();

								if ( ! empty( $galleries ) ) {

									foreach ( $galleries as $gallery ) {

										$color = isset( $gallery['severity'] ) && 'note' !== $gallery['severity'] ? 'red' : 'orange';
										$label = isset( $gallery['severity'] ) && 'note' !== $gallery['severity'] ? 'Warning' : 'Note';

										?>
										<?php if ( ! empty( $gallery['image_count'] ) ) { ?>
									<li><span class="alert" style="font-weight: 600; color:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $label ); ?>:</span> <a href="<?php echo esc_url( $gallery['link'] ); ?>"><?php echo esc_html( $gallery['title'] ); ?></a> has <?php echo esc_html( $gallery['image_count'] ); ?> images.</li>
									<?php } else { ?>
										<li><span class="alert" style="font-weight: 600; color:orange;">Note:</span> <a href="<?php echo esc_url( $gallery['link'] ); ?>"><?php echo esc_html( $gallery['title'] ); ?></a> <?php echo esc_html( $gallery['issue_text'] ); ?></li>
									<?php } ?>
										<?php
									}
								}
								?>

								<?php

								if ( $this->total_albums() > 0 ) {

									$albums = $this->get_problem_albums();

									if ( ! empty( $albums ) ) {

										foreach ( $albums as $album ) {

											$color = isset( $album['severity'] ) && 'note' !== $album['severity'] ? 'red' : 'orange';
											$label = isset( $album['severity'] ) && 'note' !== $album['severity'] ? 'Warning' : 'Note';

											?>
											<?php if ( ! empty( $album['gallery_count'] ) ) { ?>
									<li><span class="alert" style="font-weight: 600; color:<?php echo esc_html( $color ); ?>;"><?php echo esc_html( $label ); ?>:</span> <a href="<?php echo esc_url( $album['link'] ); ?>"><?php echo esc_html( $album['title'] ); ?></a> has <?php echo intval( $album['gallery_count'] ); ?> galleries.</li>
									<?php } else { ?>
										<li><span class="alert" style="font-weight: 600; color:<?php echo esc_html( $color ); ?>;"><?php echo esc_html( $label ); ?>:</span> <a href="<?php echo esc_url( $album['link'] ); ?>"><?php echo esc_html( $album['title'] ); ?></a> <?php echo esc_html( $album['issue_text'] ); ?></li>
									<?php } ?>
											<?php
										}
									}
								}
								?>

								<?php

									$time = ini_get( 'max_execution_time' );
								if ( $time <= 60 ) {

									?>
								<li><span class="alert" style="font-weight: 600; color:orange;">Note:</span> Maximum Limit For Runtime Set To <strong><?php echo esc_html( $time ); ?> Seconds</strong></li>
								<?php } ?>
								<?php

									$php_version = phpversion();
								if ( version_compare( phpversion(), '5.6', '<' ) ) {

									?>
								<li><span class="alert" style="font-weight: 800; color:red;">Warning:</span> Site is running on an unsupported version of PHP (PHP <?php echo esc_html( $php_version ); ?>)</li>
								<?php } ?>
							</ul>
						</blockquote>
						<div style="display: inline-block;">
							<a href="<?php echo esc_url( admin_url( '/edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-debug' ) ); ?>"  class="button button-warning"><?php esc_html_e( 'View Support Information', 'envira-gallery' ); ?></a>
						</div>
					</div>

					<div class="card">
						<?php

						$envira_options = $this->total_options();

						?>
						<h2 class="title">Envira Options</h2>
						<p>Current number of options: <?php echo count( $envira_options ); ?></p>
						<blockquote>
							<?php if ( ! empty( $envira_options ) ) { ?>
							<ul>
								<?php foreach ( $envira_options as $option ) { ?>
									<li><?php echo esc_html( $option->option_name ); ?></li>
								<?php } ?>
							</ul>
							<?php } ?>
						</blockquote>
						<form action="" method="post">
							<input type="hidden" name="action" value="delete-options" />
							<?php self::get_nonce_field(); ?>
							<input type="submit" class="button button-primary" value="<?php esc_html_e( 'Clear Envira Specific Options', 'envira-gallery' ); ?>" />
						</form>
						<p>ENVIRA_DEBUG: <span class=""><?php echo defined( 'ENVIRA_DEBUG' ) && 'true' === ENVIRA_DEBUG ? 'ON' : 'OFF'; ?></span></p>

						<p>envira_debug option: <span class=""><?php echo get_option( 'envira_debug' ) ? 'ON' : 'OFF'; ?></span></p>
						<form action="" method="post">
							<input type="hidden" name="action" value="toggle-debug" />
							<?php self::get_nonce_field(); ?>
							<input type="submit" class="button button-primary" value="<?php esc_html_e( 'Toggle debug option', 'envira-gallery' ); ?>" />
						</form>
					</div>

					<div class="card">
						<?php

							$envira_version         = get_option( 'envira_version' );
							$envira_version_test    = defined( 'ENVIRA_DEBUG_VERSION' ) ? ENVIRA_DEBUG_VERSION : 'NOT DEFINED';
							$envira_display_welcome = empty( get_option( 'envira_display_welcome' ) ) ? 'N/A' : get_option( 'envira_display_welcome' );

						?>
						<h2 class="title">Versions</h2>
						<ul>
							<li>Current Version In Options: <?php echo esc_html( $envira_version ); ?></li>
							<li>Display Welcome: <?php echo esc_html( $envira_display_welcome ); ?></li>
							<li>Version Test: <?php echo esc_html( $envira_version_test ); ?></li>
						</ul>

					</div>



				</div>


		</div> <!-- wrap -->






		<?php
	}

	/**
	 * Output the api screen.
	 *
	 * @since 1.8.1
	 */
	public function api_page() {
		?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php $this->tab_navigation(); ?>

					<?php do_action( 'envira_support_notices' ); ?>

					<div class="card">
						<?php

						$api_results = $this->get_api_info();

						?>
						<h2 class="title">API</h2>
						<p><small>This uses the API call for confirming license keys.</small></p>
						<form action="" method="post">
							<input type="hidden" name="action" value="test-api" />
							<?php self::get_nonce_field(); ?>
							<input type="submit" class="button button-primary" value="<?php esc_html_e( 'Test API', 'envira-gallery' ); ?>" />
						</form>

						<br/>

						<hr/>

						<h4>Last Attempt</h4>



						<ul>
							<?php

							if ( empty( $api_results['time_tested'] ) ) {

								?>
							<li>No Test Attempt Has Been Logged</li>
							<?php } else { ?>
							<li><?php echo esc_html( wp_date( DATE_RFC2822, $api_results['time_tested'] ) ); ?><br/>
								<strong>RESPONSE CODE: </strong> <strong style="color: green; font-weight: 800;"><?php echo esc_html( $api_results['response_code'] ); ?></strong></li>
							<?php } ?>
							<li><strong>Response Code Response Body:</strong> <?php print_r( $api_results['response_body'] ); ?></li>
							<li><strong>Response Code Response:</strong>
								<?php
								// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
								echo highlight_string( "<?php\n\$data =\n" . var_export( $api_results['response'], true ) . ";\n?>" );
								?>
							</li>
						</ul>

					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}

	/**
	 * Output the tools screen.
	 *
	 * @since 1.8.1
	 */
	public function tools_page() {
		$galleries = _envira_get_galleries();

		?>
		<div class="envira-welcome-wrap envira-welcome">
				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php $this->tab_navigation(); ?>

					<?php do_action( 'envira_support_notices' ); ?>

					<div class="card">
						<h2 class="title">Repair Gallery</h2>
						<p class="subtitle"><small>Compares gallery data to meta data and attemps to remove bad/ghost images, etc. (see Github Ticket #4255).</small></p>
						<form action="" method="post">

						<p><label for="fix-image-links-gallery">Select Gallery:</label>
							<?php if ( ! empty( $galleries ) ) { ?>
								<select name="gallery_id" id="fix-image-links-gallery">
								<option value="all">ALL</option>
									<?php
									foreach ( $galleries as $gallery ) {
										$post = get_post( $gallery['id'] );
										?>
										<option value="<?php echo esc_attr( $gallery['id'] ); ?>">
										<?php echo esc_html( $post->post_title . ' -- ' . count( $gallery['gallery'] ) . ' Images (ID: ' . $gallery['id'] . ')' ); ?>
										</option>
									<?php } ?>
								</select>
							<?php } ?>

						</p>
								<?php $this->get_nonce_field(); ?>
								<input type="hidden" name="action" value="tools-repair-gallery" />
								<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Repair Gallery', 'envira-gallery' ); ?>" />
							</form>
					</div>

					<div class="card">
						<h2 class="title">Fix Image Links</h2>
						<p class="subtitle"><small>Assigns the image URL to all images (see Github Ticket #1904).</small></p>
						<form action="" method="post">

						<p><label>Select Gallery:</label>
							<?php if ( ! empty( $galleries ) ) { ?>
								<select name="gallery_id" id="fix-image-links-gallery">
									<option value="all">ALL</option>
									<?php
									foreach ( $galleries as $gallery ) {
										$post = get_post( $gallery['id'] );
										?>
										<option value="<?php echo esc_attr( $gallery['id'] ); ?>">
										<?php echo esc_html( $post->post_title . ' -- ' . count( $gallery['gallery'] ) . ' Images (ID: ' . $gallery['id'] . ')' ); ?>
										</option>
									<?php } ?>
								</select>
							<?php } ?>

						</p>
								<?php $this->get_nonce_field(); ?>
								<input type="hidden" name="action" value="tools-fix-image-links" />
								<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Fix Gallery Links', 'envira-gallery' ); ?>" />
							</form>
					</div>

					<div class="card">
						<h2 class="title">Edit Gallery Settings</h2>
						<p class="subtitle"><small>Examine and edit settings for a gallery stored in the database, including settings that might be associated with addons no longer activated or installed.</small></p>
						<form action="" method="get">
						<input type="hidden" name="post_type" value="envira" />
						<input type="hidden" name="page" value="envira-gallery-support-tools" />
						<?php $this->get_nonce_field(); ?>
						<p><label for="view-gallery-settings">Select Gallery:</label>
							<?php if ( ! empty( $galleries ) ) { ?>
								<select name="gallery_id" id="view-gallery-settings">
									<?php
									foreach ( $galleries as $gallery ) {
										$post = get_post( $gallery['id'] );
										?>
										<option value="<?php echo esc_attr( $gallery['id'] ); ?>">
										<?php echo esc_html( $post->post_title . ' -- ' . count( $gallery['gallery'] ) . ' Images (ID: ' . $gallery['id'] . ')' ); ?>
										</option>
									<?php } ?>
								</select>
							<?php } ?>

						</p>
								<input type="hidden" name="action" value="view-gallery-settings" />
								<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Edit Gallery Settings', 'envira-gallery' ); ?>" />
							</form>


						<?php do_action( 'envira_view_gallery_settings' ); ?>

					</div>
				</div>
		</div> <!-- wrap -->
		<?php
	}

	/**
	 * Output the logs screen.
	 *
	 * @since 1.8.1
	 */
	public function logs_page() {
		?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php $this->tab_navigation(); ?>

					<?php do_action( 'envira_support_notices' ); ?>

					<div class="card">

						<?php $this->envira_debug_log(); ?>

					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}


	/**
	 * Show debug.log file to support in tab
	 *
	 * @since 1.8.1
	 */
	public function envira_debug_log() {

		$toobig = apply_filters( 'debug_log_too_big', 5 ); // how many MB throws a warning?
		$latest = apply_filters( 'debug_log_latest_count', 15 ); // sets the number of latest error lines.

		if ( ! WP_DEBUG_LOG ) {
			?>
	<div class="notice notice-warning">
		<p>Debug Log is not enabled.  <a href="https://codex.wordpress.org/Debugging_in_WordPress#WP_DEBUG_LOG" target="_blank" rel="noopener">See the codex.</a>  Essentially, open wp-config.php and replace <code>define( 'WP_DEBUG', false );</code> with the code below.</p>
	</div>
	<pre>
	define( 'WP_DEBUG', true );// just toggle this line to false to turn off
	if ( WP_DEBUG ) {
		define( 'WP_DEBUG_DISPLAY', false );
		define( 'WP_DEBUG_LOG', true );
		@ini_set( 'display_errors', 0 );
		define( 'SCRIPT_DEBUG', true );
	}
	</pre>
			<?php
			return;
		}

		$path = WP_CONTENT_DIR . '/debug.log';

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! file_exists( $path ) ) {
			echo "<div class=\"notice notice-success\"><p>No log found at $path. Hopefully this means you have no errors.</p></div>";
			return;
		}

		$nonce = isset( $_REQUEST['delete-log'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete-log'] ) ) : null;
		if ( wp_verify_nonce( $nonce, 'delete-log' ) ) {

			if ( unlink( $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				echo '<div class="notice notice-success"><p>Deleted Log</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>Error deleting ' . $path . '</p></div>';
			}
			return;
		}

		$link = admin_url( 'edit.php?post_type=envira&page=envira-gallery-support-logs' );

		if ( ! isset( $_GET['loadanyhow'] ) ) {

			$size = round( filesize( $path ) / pow( 1024, 2 ), 2 ); // Can use MB_IN_BYTES but it would only work for 4.4 and up.
			if ( $size > $toobig ) {
				echo '<div class="notice notice-warning"><p>Log is ' . esc_html( $size ) . 'MB... Do you really want to load it here?</p><p><a href="' . esc_url( $link ) . '&loadanyhow">Yes, load it anyhow.</a></p></div>';
				$toobig = false;
			}
		}

		$nonce = wp_create_nonce( 'delete-log' );
		?>
		<div class="wrap">
			<form action="<?php echo esc_url( $link ); ?>" method="post" style="position:fixed;">
				<input type="hidden" name="delete-log" value="<?php echo esc_html( $nonce ); ?>">
				<input type="submit" class="button button-primary" value="Delete Log">
			</form>
		<?php

		echo '<div style="padding-top:28px;">';

		if ( $toobig ) {// $too big is the safety switch.  Is set to false by clicking through the warning or by filtering the initial value

			$log = file( $path, FILE_IGNORE_NEW_LINES );

			if ( $latest ) {
				$lines = count( $log );
				if ( $lines > 25 ) {// Avoid scrolling.
					echo '<h2>Latest Errors</h2>';
					echo '<div style="font-family:monospace;word-wrap:break-word;">';
					for ( $l = $lines - $latest; $l < $lines; ) {
						$i = $l++;
						echo "<p><span>{$l}</span> {$log[ $i ]}</p>";
					}
					echo '</div>';
					echo '<h2>Archives</h2>';
				}
			}

			echo '<div style="font-family:monospace;word-wrap:break-word;">';
			foreach ( $log as $no => $line ) {
				echo '<p><span>';
				echo $no + 1;
				echo "</span> {$line}</p>";
			}
			echo '</div></div>';
		}
		echo '</div>';
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Output the api screen.
	 *
	 * @since 1.8.1
	 */
	public function settings_page() {
		?>

		<div class="envira-welcome-wrap envira-welcome">

				<div class="wrap">

					<h1>Troubleshooting</h1>

					<?php $this->tab_navigation(); ?>

					<?php do_action( 'envira_support_notices' ); ?>

					<div class="card">
						<?php

						$admin_bar = filter_var( get_option( 'eg_admin_bar' ), FILTER_VALIDATE_BOOLEAN );

						?>
						<h2 class="title">Settings</h2>
						<form action="" method="post">
							<input type="hidden" name="action" value="show_admin_bar" />
							<?php $this->get_nonce_field(); ?>

							<table class="form-table">
							<tbody>
								<!-- Admin Bar -->
								<tr id="envira-media-position-box">
									<th scope="row">
										<label for="support_show_admin_bar">Show Link In Admin Bar?</label>
									</th>
									<td>
										<select name="support_show_admin_bar" id="support_show_admin_bar">
											<option value="0"
											<?php
											if ( ! $admin_bar ) {
												?>
												selected="selected" <?php } ?>>No</option>
											<option value="1"
											<?php
											if ( $admin_bar ) {
												?>
												selected="selected" <?php } ?>>Yes</option>
										</select>
										<p>Only logged in admin users will see this link.</p>
										<p><small><span style="color:red">Warning:</span> Be careful if you do this on a public or customer based site.</small></p>
									</td>
								</tr>

								<?php do_action( 'envira_support_display_settings' ); ?>

							</tbody>
							</table>


							<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Update Settings', 'envira-gallery' ); ?>" />
						</form>

					</div>

				</div>


		</div> <!-- wrap -->


		<?php
	}


	/**
	 * Add toolbar node
	 *
	 * @access  public
	 * @since   1.6
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Is the adminbar showing.
	 *
	 * @return  void
	 */
	public function admin_bar_support_button( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$admin_bar = filter_var( get_option( 'eg_admin_bar' ), FILTER_VALIDATE_BOOLEAN );

		if ( ! $admin_bar ) {
			return;
		}

		$label = '<span style="color:#FFA500">Envira Support</span>';

		$args = [
			'id'     => 'envira-support',
			'title'  => $label,
			'parent' => 'top-secondary',
			'href'   => admin_url(
				add_query_arg(
					[
						'post_type' => 'envira',
						'page'      => 'envira-gallery-support-general',
					],
					'edit.php'
				)
			),
		];
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Output the nonce field.
	 *
	 * @param boolean $result Whether to return the field or echo it.
	 */
	public function get_nonce_field( $result = false ) {
		$nonce = esc_attr( wp_create_nonce( $this->nonce_action ) );
		$field = "<input type=\"hidden\" name=\"envira_nonce\" value=\"$nonce\" />";

		if ( $result ) {
			return $field;
		}

		// phpcs:ignore
		echo $field;
	}
}
