<?php
/**
 * Footer Template
 *
 * @since 1.9.0
 *
 * @package Envira Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

use Envira\Admin\Envira_Notifications;
$envira_notifications = new Envira_Notifications();
?>

<div class="envira-notifications-drawer" id="envira-notifications-drawer">
			<div class="envira-notifications-header">
				<h3 id="envira-active-title">
					<?php
					printf(
						wp_kses_post(
						// Translators: Placeholder for the number of active notifications.
							__( 'New Notifications (%s)', 'envira-gallery' )
						),
						'<span id="envira-notifications-count">' . absint( $envira_notifications->get_count() ) . '</span>'
					);
					?>
				</h3>
				<h3 id="envira-dismissed-title">
					<?php
					printf(
						wp_kses_post(
						// Translators: Placeholder for the number of dismissed notifications.
							__( 'Notifications (%s)', 'envira-gallery' )
						),
						'<span id="envira-notifications-dismissed-count">' . absint( $envira_notifications->get_dismissed_count() ) . '</span>'
					);
					?>
				</h3>
				<a href="#" class="envira-button-text" id="envira-notifications-show-dismissed">
					<?php esc_html_e( 'Dismissed Notifications', 'envira-gallery' ); ?>
				</a>
				<a href="#" class="envira-button-text" id="envira-notifications-show-active">
					<?php esc_html_e( 'Active Notifications', 'envira-gallery' ); ?>
				</a>
				<a class="envira-just-icon-button envira-notifications-close">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
							<path d="M18.984 6.422L13.406 12l5.578 5.578-1.406 1.406L12 13.406l-5.578 5.578-1.406-1.406L10.594 12 5.016 6.422l1.406-1.406L12 10.594l5.578-5.578z"></path>
					</svg>
				</a>
			</div>
			<div class="envira-notifications-list">
				<ul class="envira-notifications-active">
					<?php
					$active_notifications = $envira_notifications->get_active_notifications();
					foreach ( $active_notifications as $active ) {
						$envira_notifications->get_notification_markup( $active );
					}
					?>
				</ul>
				<ul class="envira-notifications-dismissed">
					<?php
					$dismissed_notifications = $envira_notifications->get_dismissed_notifications();
					foreach ( $dismissed_notifications as $dismissed ) {
						$envira_notifications->get_notification_markup( $dismissed );
					}
					?>
				</ul>
			</div>
			<div class="envira-notifications-footer">
				<a href="#" class="envira-button-text envira-notification-dismiss" id="envira-dismiss-all" data-id="all"><?php esc_html_e( 'Dismiss all', 'envira-gallery' ); ?></a>
			</div>
		</div>
