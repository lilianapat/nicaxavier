<?php
/**
 * Handles all rest endpoints and functions related to envira ai image creation feature.
 *
 * @since 1.9.18
 *
 * @package Envira Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST API route for Envira AI Image creation.
 *
 * @since 1.9.18
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'envira-ai/v1',
			'/create-ai-images',
			[
				'methods'             => 'POST',
				'callback'            => 'envira_gallery_ai_rest_create_images',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * REST API Callback to create images using Envira AI.
 *
 * @since 1.9.18
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error
 */
function envira_gallery_ai_rest_create_images( $request ) {
	// Get the nonce from the request header.
	$nonce = $request->get_header( 'X-WP-Nonce' );

	// Verify the nonce.
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( [ 'rest_api_error' => __( 'Security check failed. You are not authorized. Please refresh and try again.', 'envira-gallery' ) ], 403 );
	}

	// Get form data from the request.
	$prompt       = sanitize_text_field( $request->get_param( 'prompt' ) );
	$image_type   = sanitize_text_field( $request->get_param( 'image_type' ) );
	$aspect_ratio = sanitize_text_field( $request->get_param( 'aspect_ratio' ) );

	// Prompt should not be empty.
	if ( empty( $prompt ) ) {
		return new WP_REST_Response( [ 'rest_invalid_prompt' => __( 'Please provide a valid description.', 'envira-gallery' ) ], 400 );
	}

	// Regular expression to check if the prompt contains only special characters.
	if ( preg_match( '/^[^a-zA-Z0-9]+$/', $prompt ) ) {
		return new WP_REST_Response( [ 'rest_invalid_prompt' => __( 'Please enter a valid description. It should not contain only special characters.', 'envira-gallery' ) ], 400 );
	}

	// Check for maximum character limit of prompt.
	if ( strlen( $prompt ) > 1000 ) {
		return new WP_REST_Response( [ 'rest_invalid_prompt' => __( 'Description cannot exceed 1000 characters.', 'envira-gallery' ) ], 400 );
	}

	// Call your API to generate images.
	$api_response = envira_openai_image_generator_api_request( $prompt, $image_type, $aspect_ratio );

	if ( is_array( $api_response ) && $api_response['success'] ) {
			ob_start();
		?>
		<div class="generated-ai-images-section">
			<div class="generated-ai-images-headline">
				<p><?php esc_html_e( 'Click on an image to add it to your gallery.', 'envira-gallery' ); ?></p>
			</div>
			<div class="generated-ai-images-result">
			<?php
			// Process and display the generated images.
			foreach ( $api_response['images'] as $img_key => $image_url ) {
				?>
				<div class="ai-image-item">
					<input type="checkbox" class="ai-image-checkbox" id="ai-image-checkbox-<?php echo esc_attr( $img_key ); ?>" data-index="<?php echo esc_attr( $img_key ); ?>" data-url="<?php echo esc_url( $image_url ); ?>" />
					<img class="ai-download-image-link download-image" src="<?php echo esc_url( trailingslashit( ENVIRA_URL ) . 'assets/css/images/icons/download-img.svg' ); ?>" alt="<?php echo esc_attr__( 'AI Generated Image', 'envira-gallery' ); ?>" data-url="<?php echo esc_url( $image_url ); ?>"/>
					<img class="ai-image" src="<?php echo esc_url( $image_url ); ?>" />
				</div>
				<?php
			}
			?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		$message = isset( $api_response['message'] ) ? $api_response['message'] : '';

		// Send back the images and the message.
		return rest_ensure_response(
			[
				'html'    => $html,
				'message' => $message,
			]
		);
	} else {
		return new WP_REST_Response( [ 'rest_api_error' => $api_response ], 400 );
	}
}

/**
 * Function to make the REST API call to Envira OpenAI Image Generator.
 *
 * @param string $prompt The image generation prompt.
 * @param string $image_type The type of image.
 * @param string $aspect_ratio The aspect ratio of the image.
 */
function envira_openai_image_generator_api_request( $prompt, $image_type, $aspect_ratio ) {
	$url = 'https://enviragallery.com/wp-json/envira-ai/v1/generate-image';

	// Get license key.
	$license_key = envira_get_license_key();

	$body = [
		'prompt'       => sanitize_text_field( $prompt ),
		'image_type'   => sanitize_text_field( $image_type ),
		'aspect_ratio' => sanitize_text_field( $aspect_ratio ),
		'license_key'  => $license_key,
		'site_url'     => site_url(),
	];

	$response = wp_remote_post(
		$url,
		[
			'method'  => 'POST',
			'body'    => wp_json_encode( $body ),
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'timeout' => 120,
		]
	);

	// Check if there's a connection error.
	if ( is_wp_error( $response ) ) {
		return 'Connection error: ' . $response->get_error_message();
	}

	// Get response body and decode it.
	$response_body = wp_remote_retrieve_body( $response );
	$result        = json_decode( $response_body, true );

	// Retrieve HTTP status code.
	$status_code = wp_remote_retrieve_response_code( $response );

	// Handle different HTTP status codes.
	if ( $status_code >= 400 ) {
		// Define the default messages for each status code.
		$default_messages = [
			400 => __( 'Bad Request: The API request was invalid. Please check your prompt and parameters.', 'envira-gallery' ),
			401 => __( 'Unauthorized: Invalid license key or missing authorization.', 'envira-gallery' ),
			403 => __( 'Forbidden: You do not have access to the Envira AI API endpoint. Please check your permissions.', 'envira-gallery' ),
			404 => __( 'Not Found: The requested resource could not be found on the server.', 'envira-gallery' ),
			429 => __( 'Too Many Requests: You have exceeded the API request limit. Try again later.', 'envira-gallery' ),
			500 => __( 'Server Error: There is a problem on the server. Please try again later.', 'envira-gallery' ),
		];

		// Get the message from the result if available, otherwise use the default message.
		$message = isset( $result['message'] ) ? $result['message'] : ( isset( $default_messages[ $status_code ] ) ? $default_messages[ $status_code ] : __( 'Unknown error occurred.', 'envira-gallery' ) );

		return $message;
	}

	// If we have a successful response, handle it accordingly.
	if ( isset( $result['images'] ) && ! empty( $result['images'] ) ) {
		$message     = isset( $result['message'] ) ? $result['message'] : '';
		$image_count = count( $result['images'] );

		// Save the total generated images count and weekly generated images count.
		envira_ai_save_count_generated_images( $image_count );

		return [
			'success' => true,
			'images'  => $result['images'],
			'message' => $message,
		];
	} else {
		return __( 'Unexpected error: No images found in the response. Please try again with different image description.', 'envira-gallery' );
	}
}

/**
 * Helper method to save the total generated images count and weekly generated images count.
 *
 * @param  int $image_count image count.
 * @return void
 */
function envira_ai_save_count_generated_images( $image_count ) {
	// Get the current total generated images count.
	$total_generated_images     = get_option( 'envira_ai_total_generated_images_count', 0 );
	$new_total_generated_images = $total_generated_images + $image_count;

	// Get the current week and year using wp_date() to ensure proper timezone handling.
	$current_week = wp_date( 'W' ); // ISO-8601 week number of year (weeks starting on Monday).
	$current_year = wp_date( 'Y' ); // Current year.

	// Get the stored weekly count and the week/year when it was last updated.
	$weekly_data = get_option(
		'envira_ai_weekly_generated_images_data',
		[
			'week'  => 0,
			'year'  => 0,
			'count' => 0,
		]
	);

	// Check if the stored data is from the current week.
	if ( $weekly_data['week'] === $current_week && $weekly_data['year'] === $current_year ) {
		// Add the new images to the existing count for this week.
		$weekly_data['count'] += $image_count;
	} else {
		// It's a new week, so reset the count and set the current week and year.
		$weekly_data = [
			'week'  => $current_week,
			'year'  => $current_year,
			'count' => $image_count,
		];
	}

	// Update the total count and the weekly data in the options table.
	update_option( 'envira_ai_total_generated_images_count', $new_total_generated_images );
	update_option( 'envira_ai_weekly_generated_images_data', $weekly_data );
}

/**
 * Add selected images into gallery.
 *
 * @since 1.9.18
 */
add_action( 'wp_ajax_envira_gallery_ai_add_selected_images', 'envira_gallery_ai_ajax_add_selected_images' );

/**
 * Helper Method to add selected images into gallery.
 *
 * @access public
 * @return void
 */
function envira_gallery_ai_ajax_add_selected_images() {
	// Check nonce.
	check_admin_referer( 'envira-gallery-ai-add-selected-images-nonce', 'ai_add_image_nonce' );

	$selected_images = isset( $_POST['selected_images'] ) ? wp_unslash( $_POST['selected_images'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in below loop.

	// Get the Envira Gallery ID.
	$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;

	// Sanitize each item in the array.
	if ( is_array( $selected_images ) ) {
		$sanitized_images = array_map( 'esc_url_raw', $selected_images );
	} else {
		$sanitized_images = [];
	}

	if ( empty( $sanitized_images ) ) {
		wp_send_json_error(
			[
				'error_message' => __( 'Please select images to add into gallery.', 'envira-gallery' ),
			]
		);
	}

	// Sideload images and get attachment IDs.
	$attachments_data = envira_sideload_images( $sanitized_images, $post_id );

	// Check if there was an error during sideloading.
	if ( is_wp_error( $attachments_data ) ) {
		// Get the error code and message.
		$error_code    = $attachments_data->get_error_code();
		$error_message = $attachments_data->get_error_message();

		// Set user-friendly messages based on error code.
		switch ( $error_code ) {
			case 'http_403': // Forbidden access.
			case 'http_404': // Image not found.
				$error_message = __( 'The images are only valid for 60 minutes after they have been generated.', 'envira-gallery' );
				break;
			case 'http_500': // Internal server error.
				$error_message = __( 'There was a problem fetching the image. Please try again later.', 'envira-gallery' );
				break;
			default: // Generic error message.
				$error_message = __( 'Oops! We couldn’t process your image. Try again later.', 'envira-gallery' );
				break;
		}

		wp_send_json_error(
			[
				'error_message' => $error_message,
			]
		);
	}

	// Set $_POST data to mimic the structure expected by existing ajax insert images function.
	$_POST['images'] = wp_json_encode( $attachments_data );

	// Call the existing function.
	envira_gallery_ajax_insert_images();
	die;
}

/**
 * Helper function to sideload images from URLs and return attachment IDs.
 *
 * @param array $image_urls Array of image URLs.
 * @param int   $post_id The post ID where images will be attached.
 *
 * @return array Array of attachment IDs or WP_Error on failure.
 */
function envira_sideload_images( $image_urls, $post_id ) {
	$attachments_data = [];

	foreach ( $image_urls as $image_url ) {
		// Sideload the image and get the attachment ID.
		$attachment_id = media_sideload_image( $image_url, $post_id, null, 'id' );

		// Check if there was an error sideloading the image.
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id; // Return the error object.
		}

		// Get the file path of the attachment.
		$file = get_attached_file( $attachment_id );

		// Generate and update attachment metadata.
		$attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $file );
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

		// Get attachment post data (title, description, caption, etc.).
		$attachment_post = get_post( $attachment_id );

		// Get the image alt text.
		$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		// Get the image MIME type and other details.
		$mime_type           = get_post_mime_type( $attachment_id );
		$original_image_url  = wp_get_attachment_url( $attachment_id );
		$original_image_name = basename( get_attached_file( $attachment_id ) );

		// Get the upload URL.
		$upload_url = wp_get_attachment_url( $attachment_id );

		// Get the file size in bytes and human-readable format.
		$filesize                = filesize( get_attached_file( $attachment_id ) );
		$filesize_human_readable = size_format( $filesize, 2 );

		// Get the date the file was uploaded.
		$date_uploaded = get_the_date( 'Y-m-d\TH:i:s.000\Z', $attachment_id );
		$modified_date = get_the_modified_date( 'Y-m-d\TH:i:s.000\Z', $attachment_id );

		// Get the author information.
		$author_id   = $attachment_post->post_author;
		$author_name = get_the_author_meta( 'display_name', $author_id );
		$author_link = get_edit_profile_url( $author_id );

		// Generate the nonces for media actions.
		$nonces = [
			'delete' => wp_create_nonce( "delete-attachment_{$attachment_id}" ),
			'edit'   => wp_create_nonce( "edit-attachment_{$attachment_id}" ),
			'update' => wp_create_nonce( "update-attachment_{$attachment_id}" ),
		];

		// Generate the sizes and orientation.
		$sizes       = isset( $attachment_metadata['sizes'] ) ? $attachment_metadata['sizes'] : [];
		$orientation = ( isset( $attachment_metadata['height'] ) && $attachment_metadata['height'] > $attachment_metadata['width'] ) ? 'portrait' : 'landscape';

		// Prepare attachment data arary to return.
		$attachment_data = [
			'id'                    => $attachment_id,
			'title'                 => $attachment_post->post_title,
			'filename'              => basename( $file ),
			'url'                   => $upload_url,
			'link'                  => $upload_url,
			'alt'                   => $alt_text,
			'author'                => $author_id,
			'description'           => $attachment_post->post_content,
			'caption'               => $attachment_post->post_excerpt,
			'name'                  => $attachment_post->post_title,
			'status'                => $attachment_post->post_status,
			'uploadedTo'            => $post_id,
			'date'                  => $date_uploaded,
			'modified'              => $modified_date,
			'menuOrder'             => 0,
			'mime'                  => $mime_type,
			'type'                  => 'image',
			'subtype'               => strtolower( pathinfo( $upload_url, PATHINFO_EXTENSION ) ),
			'icon'                  => wp_mime_type_icon( $attachment_id ),
			'dateFormatted'         => get_the_date( 'F j, Y', $attachment_id ),
			'nonces'                => $nonces,
			'editLink'              => get_edit_post_link( $attachment_id ),
			'meta'                  => false,
			'authorName'            => $author_name,
			'authorLink'            => $author_link,
			'filesizeInBytes'       => $filesize,
			'filesizeHumanReadable' => $filesize_human_readable,
			'context'               => '',
			'originalImageURL'      => $original_image_url,
			'originalImageName'     => $original_image_name,
			'height'                => isset( $attachment_metadata['height'] ) ? $attachment_metadata['height'] : '',
			'width'                 => isset( $attachment_metadata['width'] ) ? $attachment_metadata['width'] : '',
			'orientation'           => $orientation,
			'sizes'                 => $sizes,
			'compat'                => [
				'item' => '',
				'meta' => '',
			],
		];

		// Add the attachment data to the array.
		$attachments_data[] = $attachment_data;
	}

	return $attachments_data;
}

/**
 * Register REST API route for download image.
 *
 * @since 1.9.18
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'envira-ai/v1',
			'/download-image',
			[
				'methods'             => 'POST',
				'callback'            => 'envira_gallery_ai_download_image',
				'permission_callback' => '__return_true',
			]
		);
	}
);

/**
 * Callback method to download an image via REST API.
 *
 * @access public
 * @param  WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error
 */
function envira_gallery_ai_download_image( $request ) {
	// Get the nonce from the request header.
	$nonce = $request->get_header( 'X-WP-Nonce' );

	// Verify the nonce.
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( [ 'rest_api_error' => __( 'Security check failed. You are not authorized. Please refresh and try again.', 'envira-gallery' ) ], 403 );
	}

	// Get the image URL from the request.
	$image_url = $request->get_param( 'image_url' );
	if ( ! $image_url ) {
		return new WP_REST_Response( [ 'no_image_url' => __( 'No image URL provided.', 'envira-gallery' ) ], 400 );
	}

	// Sanitize the image URL.
	$image_url = esc_url_raw( wp_unslash( $image_url ) );

	// Fetch the image data from the URL.
	$image_data = file_get_contents( $image_url );
	if ( false === $image_data ) {
		return new WP_REST_Response( [ 'image_fetch_failed' => __( 'Failed to fetch image.', 'envira-gallery' ) ], 500 );
	}

	// Return the image data as a base64 string in a success response.
	return new WP_REST_Response(
		[
			'success' => true,
			'image'   => base64_encode( $image_data ),
		],
		200
	);
}
