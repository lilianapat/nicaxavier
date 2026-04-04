<?php
/**
 * Envira Licensing Tabs Modal.
 *
 * @package Envira Gallery.
 */

/**
 * This file is used to display the licensing settings modal.
 * The strings are not escaped as these are used in the background for the modal.
 */

?>
<div class="envira-admin-content">
	<?php
	if ( 'pro' === $data['level'] ) {
		if ( 'inactive' === $data['message'] ) {
			?>
	<div class="envira-admin-modal">
		<div class="envira-admin-modal-content">
			<h2>
				<?php esc_html_e( 'Image Licensing Addon is not active.', 'envira-gallery' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Activate the addon, to customize and display licensing information on your galleries.', 'envira-gallery' ); ?>
			</p>
			<?php
			$redirect_url = admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-licensing' );
			$url          = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=envira-image-licensing/envira-image-licensing.php&_wp_http_referer=' . rawurlencode( $redirect_url ) ), 'activate-plugin_envira-image-licensing/envira-image-licensing.php' );
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="button envira-button envira-primary-button">
				<?php esc_html_e( 'Activate Image Licensing Addon Now', 'envira-gallery' ); ?>
			</a>
		</div>
	</div>
			<?php
		}
		if ( 'not-installed' === $data['message'] ) {
			?>
		<div class="envira-admin-modal">
			<div class="envira-admin-modal-content" id="envira-addons">
				<h2>
					<?php esc_html_e( 'Image Licensing Addon is not installed.', 'envira-gallery' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'Install the addon, to customize and display licensing information on your galleries.', 'envira-gallery' ); ?>
				</p>
				<?php
				$redirect_url = admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-licensing' );
				?>
				<a class="button envira-button envira-primary-button envira-addon-action-button envira-install-addon" href="#" rel="<?php echo esc_url( $data['link'] ); ?>" >
					<?php esc_html_e( 'Install and Activate the Image Licensing Addon Now', 'envira-gallery' ); ?>
				</a>
			</div>
		</div>
			<?php
		}
	} else {
		?>
		<div class="envira-admin-modal">
			<div class="envira-admin-modal-content">
			<h2>
				<?php esc_html_e( 'Image Licensing is only available with Pro Features', 'envira-gallery' ); ?>
			</h2>
			<p>
				<?php esc_html_e( 'Once you upgrade to Envira Pro, licensing will be available to customize and show licenses on your galleries in seconds.', 'envira-gallery' ); ?>
			</p>
			<div class="envira-admin-model-lists envira-clear">
				<ul class="left">
					<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="28px" height="28px" fill="#7cc048"><path d="M256 48a208 208 0 110 416 208 208 0 110-416zm0 464a256 256 0 100-512 256 256 0 100 512zm113-303c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"></path></svg></i>
						<?php esc_html_e( 'Enable/Disable Licensing', 'envira-gallery' ); ?></li>
					<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="28px" height="28px" fill="#7cc048"><path d="M256 48a208 208 0 110 416 208 208 0 110-416zm0 464a256 256 0 100-512 256 256 0 100 512zm113-303c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"></path></svg></i>
						<?php esc_html_e( 'Choose License Type', 'envira-gallery' ); ?></li>
				</ul>
				<ul class="right">
					<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="28px" height="28px" fill="#7cc048"><path d="M256 48a208 208 0 110 416 208 208 0 110-416zm0 464a256 256 0 100-512 256 256 0 100 512zm113-303c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"></path></svg></i>
						<?php esc_html_e( 'Set Author/Creator Name', 'envira-gallery' ); ?></li>
					<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="28px" height="28px" fill="#7cc048"><path d="M256 48a208 208 0 110 416 208 208 0 110-416zm0 464a256 256 0 100-512 256 256 0 100 512zm113-303c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"></path></svg></i>
						<?php esc_html_e( 'Display License On The Galleries', 'envira-gallery' ); ?></li>
				</ul>
			</div>
		</div>
		<div class="envira-admin-modal-bonus">
			<svg class="check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="28px" height="28px" fill="#7cc048"><path d="M256 48a208 208 0 110 416 208 208 0 110-416zm0 464a256 256 0 100-512 256 256 0 100 512zm113-303c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"></path></svg>
			<p>
				<?php
				printf(
				// Translators: %1$s - Opening strong tag, do not translate. %2$s - Closing strong tag, do not translate. %3$s - Opening span tag, do not translate. %4$s - Closing strong tag, do not translate.
					esc_html__( '%1$sBonus%2$s: Envira Gallery Basic users get %3$s 50%% off%4$s regular price, automatically applied at checkout', 'envira-gallery' ),
					'<strong>',
					'</strong>',
					'<span class="envira-green"><strong>',
					'</strong></span>'
				);
				?>
			</p>
		</div>
		<div class="envira-admin-modal-button">
			<?php
			$upgrade_link = function_exists( 'envira_get_upgrade_link' ) ? envira_get_upgrade_link( 'https://enviragallery.com/pricing/', 'licensingPage', 'upgrade' ) : 'https://enviragallery.com/pricing/';
			?>
			<a href="<?php echo esc_url( $upgrade_link ); ?>" class="button envira-button envira-primary-button" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to Envira Gallery Pro Now', 'envira-gallery' ); ?>
			</a>
		</div>
		<a class="envira-admin-modal-text-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=envira&page=envira-gallery-settings' ) ); ?>"><?php esc_html_e( 'Already Purchased?', 'envira-gallery' ); ?></a>
	</div>
	<?php } ?>
	<div class="wrap">
		<div id="envira-settings-licensing">
			<div class="envira-intro">
				<p ><?php esc_html_e( 'Specify how your assets can be shared, used, or modified from inside your galleries by displaying Creative Commons licensing information. ', 'envira-gallery' ); ?></p>
				<p><?php esc_html_e( 'Once enabled, Envira Gallery will add your license to the bottom of each embedded gallery.', 'envira-gallery' ); ?></p>
				<?php // translators: %s is the URL to the Creative Commons website. ?>
				<p><?php printf( esc_html__( 'Need help  %s choosing a proper license', 'envira-gallery' ), '<a href="https://chooser-beta.creativecommons.org/" target="_blank">choosing a proper license</a>' ); ?></p>
			</div>
			<form action="#" method="post">
				<table class="form-table">
					<tbody>
					<tr id="envira-config-enable-licensing">
						<th scope="row">
							<label for="licensing_enabled"><?php esc_html_e( 'Enable Image Licensing', 'envira-gallery' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="licensing_enabled" id="licensing_enabled" value="1" checked="checked">
							<p class="description"><?php esc_html_e( 'If enabled, Envira Gallery will add your license to the bottom of each embedded gallery.', 'envira-gallery' ); ?></p>
					</tr>
					<tr>
						<th><label for="licensing_author"><?php esc_html_e( 'Author/Creator Name', 'envira-gallery' ); ?></label></th>
						<td><input type="text" name="licensing_author" id="licensing_author" value="<?php echo esc_attr_e( 'Author Name', 'envira-gallery' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="licensing_type"><?php esc_html_e( 'License Type', 'envira-gallery' ); ?></label></th>
						<td class="license-label-td" style="display: flex;flex-wrap: wrap;align-content: flex-start;max-width: 70%;">
							<label for="licensing_type_cc-by" style="background-color: rgba(124, 192, 72, 0.34); border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by" value="<?php esc_attr_e( 'cc-by', 'envira-gallery' ); ?>" checked="checked">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by/4.0/' ); ?>" target="_blank"><?php esc_html_e( '4.0 International Deed', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by.svg" alt="<?php esc_attr_e( '(CC BY 4.0)', 'envira-gallery' ); ?>" ></p>
							</label>
							<label for="licensing_type_cc-by-nc" style=" border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by-nc" value="<?php esc_attr_e( 'cc-by-nc', 'envira-gallery' ); ?>">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by-nc/4.0/' ); ?>" target="_blank"><?php esc_html_e( 'NonCommercial 4.0 International', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY-NC 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by-nc.svg" alt="<?php esc_attr_e( '(CC BY-NC 4.0)', 'envira-gallery' ); ?>" ></p>
							</label>
							<label for="licensing_type_cc-by-nc-nd" style=" border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by-nc-nd" value="<?php esc_attr_e( 'cc-by-nc-nd', 'envira-gallery' ); ?>">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by-nc-nd/4.0/' ); ?>" target="_blank"><?php esc_html_e( 'NonCommercial 4.0 No Derivatives', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY-NC-SA 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by-nc-nd.svg" alt="<?php esc_attr_e( '(CC BY-NC-SA 4.0)', 'envira-gallery' ); ?>"></p>
							</label>
							<label for="licensing_type_cc-by-nc-sa" style=" border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by-nc-sa" value="<?php esc_attr_e( 'cc-by-nc-sa', 'envira-gallery' ); ?>">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by-nc-sa/4.0/' ); ?>" target="_blank"><?php esc_html_e( 'NonCommercial 4.0 ShareALike', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY-SA 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by-nc-sa.svg" alt="<?php esc_attr_e( '(CC BY-SA 4.0)', 'envira-gallery' ); ?>" ></p>
							</label>
							<label for="licensing_type_cc-by-nd" style=" border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by-nd" value="<?php esc_attr_e( 'cc-by-nd', 'envira-gallery' ); ?>">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by-nd/4.0/' ); ?>" target="_blank"><?php esc_html_e( 'No Derivatives', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY-ND 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by-nd.svg" alt="<?php esc_attr_e( '(CC BY-ND 4.0)', 'envira-gallery' ); ?>" ></p>
							</label>
							<label for="licensing_type_cc-by-sa" style=" border-color: rgb(124, 192, 72); flex-basis:40%;margin:10px 5px;padding:10px;border-radius:5px;">
								<input type="radio" required="" name="licensing_type" id="licensing_type_cc-by-sa" value="<?php esc_attr_e( 'cc-by-sa', 'envira-gallery' ); ?>">
								<a href="<?php echo esc_url( 'https://creativecommons.org/licenses/by-sa/4.0/' ); ?>" target="_blank"><?php esc_html_e( 'ShareALike', 'envira-gallery' ); ?></a>
								<?php esc_html_e( '(CC BY-SA 4.0)', 'envira-gallery' ); ?><p><img src="/wp-content/plugins/envira-gallery/assets/images/icons/licensing/cc-by-sa.svg" alt="<?php esc_attr_e( '(CC BY-SA 4.0)', 'envira-gallery' ); ?>" ></p>
							</label>
						</td>

					</tr><tr>
						<th scope="row"><input type="submit" name="envira-licensing-save" id="envira-gallery-licensing-save" class="button button-primary" value="<?php esc_attr_e( 'Save', 'envira-gallery' ); ?>"></th>
						<td>&nbsp;</td>
					</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
</div>
