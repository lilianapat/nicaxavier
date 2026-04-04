<?php
/**
 * Outputs the Gallery Type Tab Selector and Panels
 *
 * @since   1.5.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

$gallery_data = envira_get_gallery( $data['post']->ID );
$type         = envira_get_config( 'type', $gallery_data ) !== '' ? envira_get_config( 'type', $gallery_data ) : 'default';

?>
<h2 id="envira-types-nav" class="nav-tab-wrapper envira-tabs-nav" data-container="#envira-types" data-update-hashbang="0">
	<label class="nav-tab nav-tab-native-envira-gallery<?php echo ( ( 'default' === $type ) ? ' envira-active' : '' ); ?>" for="envira-gallery-type-default" data-tab="#envira-gallery-native">
		<input id="envira-gallery-type-default" type="radio" name="_envira_gallery[type]" value="default" <?php checked( $type, 'default' ); ?> />
		<?php if ( apply_filters( 'envira_whitelabel', false ) ) { ?>
			<span><?php esc_html_e( 'Native Gallery', 'envira-gallery' ); ?></span>
		<?php } else { ?>
			<span><?php esc_html_e( 'Native Envira Gallery', 'envira-gallery' ); ?></span>
		<?php } ?>

	</label>

	<a href="#envira-gallery-external" title="<?php esc_attr_e( 'Galleries from Other Sources', 'envira-gallery' ); ?>" class="nav-tab nav-tab-external-gallery<?php echo 'default' !== $type ? ' envira-active' : ''; ?>">
		<span><?php esc_html_e( 'Galleries from Other Sources', 'envira-gallery' ); ?></span>
	</a>

	<a href="#envira-gallery-envira-ai" title="<?php esc_attr_e( 'Create with Envira AI', 'envira-gallery' ); ?>" class="nav-tab nav-tab-envira-ai">
		<span><?php esc_html_e( 'Create with Envira AI', 'envira-gallery' ); ?></span>
	</a>
</h2>

<!-- Types -->
<div id="envira-types" data-navigation="#envira-types-nav">
	<!-- Native Envira Gallery - Drag and Drop Uploader -->
	<div id="envira-gallery-native" class="envira-tab envira-clear<?php echo 'default' === $type ? ' envira-active' : ''; ?>">
		<!-- Errors -->
		<div id="envira-gallery-upload-error"></div>

		<!-- WP Media Upload Form -->
			<?php
			media_upload_form();
			?>
		<script type="text/javascript">
			var post_id = <?php echo esc_attr( $data['post']->ID ); ?>, shortform = 3;
		</script>
		<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr( $data['post']->ID ); ?>" />
	</div>

	<!-- External Gallery -->
	<div id="envira-gallery-external" class="envira-tab envira-clear<?php echo 'default' !== $type ? ' envira-active' : ''; ?>">
		<?php
		// If one or more External Gallery Types are registered, display them now.
		if ( count( $data['types'] ) > 1 ) {
			?>
			<p class="envira-intro"><?php esc_html_e( 'Select Your Source', 'envira-gallery' ); ?></p>
			<ul id="envira-gallery-types-nav">
				<?php
				foreach ( $data['types'] as $id => $title ) {
					// Don't output the default type as an option here.
					if ( 'default' === $id ) {
						continue;
					}

					// Output the type as a radio option.
					?>
					<li id="envira-gallery-type-<?php echo sanitize_html_class( $id ); ?>"<?php echo 'default' !== $type ? ' envira-active' : ''; ?>>
						<label for="envira-gallery-type-<?php echo esc_attr( $id ); ?>">
							<input id="envira-gallery-type-<?php echo sanitize_html_class( $id ); ?>" type="radio" name="_envira_gallery[type]" value="<?php echo esc_attr( $id ); ?>" <?php checked( $type, $id ); ?> />
							<div class="icon"></div>
							<div class="title"><?php echo esc_html( $title ); ?></div>
						</label>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		} else {
			// No External Gallery Types are registered.
			// If we're on the Lite version, show a notice.
			// Get License.
			$license_type = envira_get_license_level();

			if ( ! empty( $license_type ) ) {

				$installed_plugins = array_keys( get_plugins() );
				$activated_plugins = get_option( 'active_plugins' );
				$needs_update      = false;
				$is_installed      = [];

				if ( in_array( 'envira-instagram/envira-instagram.php', $installed_plugins, true ) && ! in_array( 'envira-instagram/envira-instagram.php', $activated_plugins, true ) ) {
					$is_installed['instagram'] = true;
				}
				if ( in_array( 'envira-featured-content/envira-featured-content.php', $installed_plugins, true ) && ! in_array( 'envira-featured-content/envira-featured-content.php', $activated_plugins, true ) ) {
					$is_installed['featured-content'] = true;
				}

				switch ( $license_type ) {
					case 'pro':
					case 'plus':
						break;
					case 'basic':
					case 'unlicensed':
					case '':
					default:
						$needs_update = true;
						break;
				}

				$message_line1 = esc_html__( 'It looks like the addons to import images from external sources are not installed or activated', 'envira-gallery' );
				$message_line2 = esc_html__( 'Envira Pro allows you to build galleries from Instagram photos, images from your posts, and more.', 'envira-gallery' );
				$plugin_link   = admin_url( 'edit.php?post_type=envira&page=envira-gallery-addons' );
				if ( ! $needs_update && empty( $is_installed ) ) {
					?>
					<p class="envira-intro"><?php echo esc_html( $message_line1 ); ?></p>
					<p><?php echo esc_html( $message_line2 ); ?></p>
					<p>
						<a href="<?php echo esc_url( $plugin_link ); ?>" class="button button-primary button-x-large" title="<?php esc_attr_e( 'Click Here to Install Addons', 'envira-gallery' ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Click Here to Install Addons', 'envira-gallery' ); ?>
						</a>
					</p>
					<?php
				}

				if ( $needs_update || ! empty( $is_installed ) ) {
					$upgrade_link = $needs_update ? envira_get_upgrade_link() : $plugin_link;
					?>

					<?php if ( $needs_update ) { ?>

					<p class="envira-intro"><?php esc_html_e( 'If you upgrade your basic Pro account, you can create dynamic galleries with Envira addons.', 'envira-gallery' ); ?></p>

					<?php } elseif ( ! empty( $is_installed ) ) { ?>

					<p class="envira-intro"><?php echo esc_html( $message_line1 ); ?></p>

					<?php } ?>

					<ul id="envira-gallery-types-nav">
						<?php if ( $needs_update || in_array( 'instagram', $is_installed, true ) ) { ?>
						<li id="envira-gallery-type-instagram">
							<a href="<?php echo esc_url( $upgrade_link ); ?>" title="<?php esc_attr_e( 'Build Galleries from Instagram images.', 'envira-gallery' ); ?>" target="_blank" rel="noopener">
								<div class="icon"></div>
								<div class="title"><?php esc_html_e( 'Instagram', 'envira-gallery' ); ?></div>
							</a>
						</li>
						<?php } ?>
						<?php if ( $needs_update || in_array( 'featured-content', $is_installed, true ) ) { ?>
						<li id="envira-gallery-type-fc">
							<a href="<?php echo esc_url( $upgrade_link ); ?>" title="<?php esc_attr_e( 'Build Galleries from Featured Content.', 'envira-gallery' ); ?>" target="_blank" rel="noopener">
								<div class="icon"></div>
								<div class="title"><?php esc_html_e( 'Featured Content', 'envira-gallery' ); ?></div>
							</a>
						</li>
						<?php } ?>
					</ul>
					<p><?php echo esc_html( $message_line2 ); ?></p>

					<?php if ( $needs_update ) { ?>

					<p>
						<a href="<?php echo esc_url( $upgrade_link ); ?>" class="button button-primary button-x-large" title="<?php esc_attr_e( 'Click Here to Upgrade', 'envira-gallery' ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Click Here to Upgrade', 'envira-gallery' ); ?>
						</a>
					</p>

					<?php } elseif ( ! empty( $is_installed ) ) { ?>

						<p>
							<a href="<?php echo esc_url( $plugin_link ); ?>" class="button button-primary button-x-large" title="<?php esc_attr_e( 'Click Here to Activate Addons', 'envira-gallery' ); ?>" target="_blank">
								<?php esc_html_e( 'Click Here to Activate Addons', 'envira-gallery' ); ?>
							</a>
						</p>

					<?php } ?>

					<?php
				}
			} else {

				?>

					<p><?php esc_html_e( 'It doesn\'t look like you have any Addons activated which import images from external sources.', 'envira-gallery' ); ?></p>

				<?php

			}
		}
		?>
	</div>

	<!-- Envira AI -->
	<div id="envira-gallery-envira-ai" class="envira-tab envira-clear">

		<p><?php esc_html_e( 'Add images to your gallery using the power of AI', 'envira-gallery' ); ?></p>
		<p>
			<?php
			$modal_class = 'upsell';
			$type        = envira_get_license_level();
			if ( ! empty( $type ) && ( 'pro' === $type ) ) {
				$modal_class = 'create-images';
			}
			?>
			<a href="javascript:void(0);" class="button button-primary button-x-large button-envira-ai-tab <?php echo esc_attr( $modal_class ); ?>" title="<?php esc_attr_e( 'Create Images with AI', 'envira-gallery' ); ?>">
				<?php esc_html_e( 'Create Images with AI', 'envira-gallery' ); ?>
			</a>
		</p>

	</div>

</div>

<!-- Envira AI Upsell Modal -->
<div id="envira-ai-upsell-modal" class="envira-ai-modal">
	<div class="envira-ai-modal-overlay"></div>
	<div class="envira-ai-upsell-modal-content">
		<a href="javascript:void(0);" id="close-envira-ai-upsell" class="close-envira-ai-upsell">&times;</a>
		<div class="upsell-content-container">
			<img src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/locked-with-key.svg' ); ?>" alt="<?php esc_attr_e( 'Unlock feature', 'envira-gallery' ); ?>" class="lock-icon" />
			<h3><?php esc_html_e( 'Upgrade to Envira Gallery and Create images with AI!', 'envira-gallery' ); ?></h3>
			<p><?php esc_html_e( 'Create unique and stunning galleries with Envira AI. No matter the type of site or design style, Envira Gallery makes it easy to create assets without a designer.', 'envira-gallery' ); ?></p>
			<div class="upsell-two-column">
				<div class="upsell-left-column">
					<div class="top-content">
						<h4><?php esc_html_e( 'Here’s what you get with Envira AI', 'envira-gallery' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Specify your asset needs.', 'envira-gallery' ); ?></li>
							<li><?php esc_html_e( 'Create an unlimited number of images.', 'envira-gallery' ); ?></li>
							<li><?php esc_html_e( 'Choose your design style.', 'envira-gallery' ); ?></li>
							<li><?php esc_html_e( 'Customize your image sizes.', 'envira-gallery' ); ?></li>
						</ul>
					</div>
					<div class="bottom-content">
						<a href="https://enviragallery.com/lite/?utm_source=liteplugin&amp;utm_medium=adminpageunlockai&amp;utm_campaign=upgradetopro" class="button button-primary" target="_blank"><?php esc_html_e( 'Unlock AI Features', 'envira-gallery' ); ?></a>
						<p class="gift-text">
							<?php
							$image_url = esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/wrapped-gift.svg' );
							$image_alt = esc_attr__( 'Unlock feature', 'envira-gallery' );

							// Create the full HTML string.
							$html = sprintf(
								'<img src="%1$s" alt="%2$s" class="gift-icon" /> %3$s',
								$image_url,
								$image_alt,
								__( 'Plus <span class="offer-text">Save 50%</span> by Upgrading to Pro today', 'envira-gallery' )
							);

							echo wp_kses_post( $html );
							?>
						</p>
					</div>
				</div>
				<div class="upsell-right-column">
					<img src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/images/upsell-ai-group.png' ); ?>" alt="<?php esc_attr_e( 'Demo Images', 'envira-gallery' ); ?>" />
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Envira AI Create Images Modal -->
<div id="envira-ai-create-images-modal" class="envira-ai-modal">
	<div class="envira-ai-modal-overlay"></div>
	<div class="envira-ai-create-images-modal-content">
		<a href="javascript:void(0);" id="close-envira-ai-create-images" class="close-envira-ai-create-images">
			<img src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/cross-ai-modal.svg' ); ?>" alt="<?php esc_attr_e( 'Close', 'envira-gallery' ); ?>"/>
		</a>
		<div class="create-images-content-container">
			<div class="main-title">
				<h3><?php esc_html_e( 'Create Images with Envira AI', 'envira-gallery' ); ?></h3>
			</div>
			<div class="create-images-two-columns">
				<div class="left-column">
					<div class="form-container">
						<div class="form-group ai-form-field">
							<label for="envira-ai-image-prompt"><?php esc_html_e( 'Describe what you\'d like inside your image', 'envira-gallery' ); ?></label>
							<textarea id="envira-ai-image-prompt" name="envira_ai_image_prompt" rows="4" maxlength="1000" placeholder="<?php esc_attr_e( 'A picture of a happy dog running through a field', 'envira-gallery' ); ?>"></textarea>
							<span class="characters-limit">Maximum 1000 characters.</span>
							<span class="ai-error-message" id="ai-prompt-error"><?php esc_html_e( 'Please provide a description.', 'envira-gallery' ); ?></span>
							<span class="prompt-ajax-error envira-ai-response-error-msg"></span>
						</div>
						<div class="form-group ai-form-field">
							<label for="envira-ai-image-type"><?php esc_html_e( 'Image Type', 'envira-gallery' ); ?></label>
							<select id="envira-ai-image-type" name="envira_ai_image_type">
								<option value=""><?php esc_html_e( 'Select an option', 'envira-gallery' ); ?></option>
								<option value="none"><?php esc_html_e( 'None', 'envira-gallery' ); ?></option>
								<option value="photographic"><?php esc_html_e( 'Photographic', 'envira-gallery' ); ?></option>
								<option value="background"><?php esc_html_e( 'Background', 'envira-gallery' ); ?></option>
								<option value="handmade"><?php esc_html_e( 'Handmade', 'envira-gallery' ); ?></option>
								<option value="digital-art"><?php esc_html_e( 'Digital Art', 'envira-gallery' ); ?></option>
								<option value="3d"><?php esc_html_e( '3D', 'envira-gallery' ); ?></option>
							</select>
							<span class="ai-error-message" id="ai-type-error"><?php esc_html_e( 'Please select an image type.', 'envira-gallery' ); ?></span>
						</div>
						<div class="form-group ai-form-field">
							<label for="envira-ai-image-aspect-ratio"><?php esc_html_e( 'Image Size', 'envira-gallery' ); ?></label>
							<select id="envira-ai-image-aspect-ratio" name="envira_ai_image_aspect_ratio">
								<option value=""><?php esc_html_e( 'Select an option', 'envira-gallery' ); ?></option>
								<option value="256x256"><?php esc_html_e( '256 x 256', 'envira-gallery' ); ?></option>
								<option value="512x512"><?php esc_html_e( '512 x 512', 'envira-gallery' ); ?></option>
								<option value="1024x1024"><?php esc_html_e( '1024 x 1024', 'envira-gallery' ); ?></option>
							</select>
							<span class="ai-error-message" id="ai-aspect-error"><?php esc_html_e( 'Please select an image size.', 'envira-gallery' ); ?></span>
						</div>
						<div class="form-group ai-form-field item-center">
							<?php
								$button_text       = esc_html__( 'Create images', 'envira-gallery' );
								$button_create_new = esc_html__( 'Create new images', 'envira-gallery' );
								$button_write_new  = esc_html__( 'Write New Prompt', 'envira-gallery' );
								$creating_text     = esc_html__( 'Creating...', 'envira-gallery' );
							?>
							<button class="create-images-submit-btn"
									data-button="<?php echo esc_attr( $button_text ); ?>"
									data-create="<?php echo esc_attr( $button_create_new ); ?>"
									data-loading="<?php echo esc_attr( $creating_text ); ?>">
								<?php echo esc_html( $button_text ); ?>
							</button>
							<span class="ajax-create-error envira-ai-response-error-msg"></span>
							<span class="ajax-create-success envira-ai-response-error-msg"></span>
						</div>
						<div class="form-group ai-add-selected-images item-center">
							<?php
								$add_images_text    = esc_html__( 'Add selected images', 'envira-gallery' );
								$add_images_loading = esc_html__( 'Adding...', 'envira-gallery' );
							?>
							<button class="add-selected-images-btn"
									data-add-images="<?php echo esc_attr( $add_images_text ); ?>"
									data-add-loading="<?php echo esc_attr( $add_images_loading ); ?>">
								<?php echo esc_html( $add_images_text ); ?>
							</button>
							<span class="ajax-add-error envira-ai-response-error-msg"></span>
							<span class="ajax-add-success envira-ai-response-error-msg"></span>
						</div>
						<div class="form-group ai-write-new-btn">
							<a href="javascript:void(0);" class="button-envira-ai-write-new" title="<?php echo esc_attr( $button_write_new ); ?>">
								<?php echo esc_html( $button_write_new ); ?>
							</a>
						</div>
					</div>
				</div>
				<div class="right-column">
					<div class="generate-image-text">
						<img src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/envira-ai-img.svg' ); ?>" alt="<?php esc_attr_e( 'Unlock feature', 'envira-gallery' ); ?>" class="lock-icon" />
						<p><?php esc_html_e( 'Generate images by describing your ideas and selecting the desired type and style.', 'envira-gallery' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="envira-ai-images-spinner">
			<img src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/3-dots-spinner.svg' ); ?>" alt="<?php esc_attr_e( 'Please wait...', 'envira-gallery' ); ?>" class="ai-spinner" />
		</div>
	</div>
</div>
