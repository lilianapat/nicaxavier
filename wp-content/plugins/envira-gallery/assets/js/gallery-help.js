/**
 * Handles:
 * - Inline Video Help
 *
 * @since 1.5.0
 */

// Setup selectors.
const envira_video_link = "p.envira-intro a.envira-video",
  envira_close_video_link = "a.envira-video-close";

jQuery(document).ready(function ($) {
  /**
   * Display Video Inline on Video Link Click.
   */
  $(document).on("click", envira_video_link, function (e) {
    // Prevent default action.
    e.preventDefault();

    // Get the video URL.
    const envira_video_url = $(this).attr("href");

    // Destroy any other instances of Envira Video iframes.
    $("div.envira-video-help").remove();

    // Get the intro paragraph.
    const envira_video_paragraph = $(this).closest("p.envira-intro");

    // Load the video below the intro paragraph on the current tab.
    $(envira_video_paragraph).append(
      '<div class="envira-video-help"><iframe src="' +
        envira_video_url +
        '" /><a href="#" class="envira-video-close dashicons dashicons-no"></a></div>'
    );
  });

  /**
   * Destroy Video when closed.
   */
  $(document).on("click", envira_close_video_link, function (e) {
    e.preventDefault();

    $(this).closest(".envira-video-help").remove();
  });

  /**
   * Display envira ai upsell modal.
   */
  const ai_btn_upsell = $(".envira-tab .button-envira-ai-tab.upsell");
  const ai_upsell_modal = $("#envira-ai-upsell-modal");
  const ai_close_button = $("#close-envira-ai-upsell");

  ai_btn_upsell.on("click", function () {
    ai_upsell_modal.show();
  });

  ai_close_button.on("click", function () {
    ai_upsell_modal.hide();
  });

  /**
   * Display envira ai create images modal.
   */
  const ai_btn_create_images = $(
    ".envira-tab .button-envira-ai-tab.create-images"
  );
  const ai_create_images_modal = $("#envira-ai-create-images-modal");
  const ai_close_btn_create_images = $("#close-envira-ai-create-images");

  ai_btn_create_images.on("click", function () {
    ai_create_images_modal.show();
  });

  ai_close_btn_create_images.on("click", function () {
    ai_create_images_modal.hide();
  });

  /**
   * Attach change event to all checkboxes.
   */
  let ai_selected_images_indices = [];
  let ai_selected_images_array = [];
  $(document).on("change", ".ai-image-checkbox", function () {
    const image_index = $(this).data("index");
    const add_selected_img_btn_sec = $(".ai-add-selected-images");
    const add_selected_img_btn = $(".add-selected-images-btn");

    if ($(this).is(":checked")) {
      // Add the selected image index to the array (if not already in it).
      if (!ai_selected_images_indices.includes(image_index)) {
        ai_selected_images_indices.push(image_index);
      }
    } else {
      // Remove the unselected image index from the array.
      ai_selected_images_indices = ai_selected_images_indices.filter(function (
        index
      ) {
        return index !== image_index;
      });
    }

    ai_selected_images_array = ai_selected_images_indices.map(function (index) {
      return $('.ai-image-checkbox[data-index="' + index + '"]').data("url");
    });

    // Check if the ai_selected_images_array array is not empty.
    if (ai_selected_images_array.length > 0) {
      const add_images_text = add_selected_img_btn.attr("data-add-images");
      add_selected_img_btn_sec.show();
      add_selected_img_btn.text(add_images_text);
      $(".form-group.ai-form-field").hide();
    } else {
      add_selected_img_btn_sec.hide();
      $(".form-group.ai-form-field").show();
      $(".envira-ai-response-error-msg").html("").hide();
    }
  });

  /**
   * Add selected images to wp media.
   */
  $(document).on("click", ".add-selected-images-btn", function (e) {
    e.preventDefault();
    $(".envira-ai-images-spinner").css("display", "flex");
    $(".envira-ai-response-error-msg").html("").hide();

    const add_img_btn = $(this);
    const add_btn_text = add_img_btn.attr("data-add-images");
    const adding_text = add_img_btn.attr("data-add-loading");

    // Add images data.
    const imagesData = {
      action: "envira_gallery_ai_add_selected_images",
      ai_add_image_nonce: envira_gallery_metabox.ai_add_selected_image_nonce,
      selected_images: ai_selected_images_array,
      post_id: envira_gallery_metabox.id,
      nonce: envira_gallery_metabox.insert_nonce,
    };

    $.ajax({
      url: envira_gallery_metabox.ajax,
      type: "POST",
      dataType: "json",
      data: imagesData,
      beforeSend: function () {
        // Disable button and show spinner.
        add_img_btn
          .prop("disabled", true)
          .text(adding_text)
          .addClass("disabled");
        $(".envira-ai-images-spinner").css("display", "flex");
      },
      success: function (response) {
        if (response && response.success) {
          // Set the image grid to the HTML we received
          $("#envira-gallery-output").html(response.success);

          $(document).trigger("enviraInsert");

          // Repopulate the Envira Gallery Image Collection.
          EnviraGalleryImagesUpdate(false);

          $(".ajax-add-success.envira-ai-response-error-msg")
            .html(envira_gallery_metabox.ai_added_success_message)
            .show();
        } else {
          $(".ajax-add-error.envira-ai-response-error-msg")
            .html(response.data.error_message)
            .show();
        }
      },
      error: function (xhr, status, error) {
        $(".ajax-add-error.envira-ai-response-error-msg").html(error).show();
      },
      complete: function () {
        // Re-enable button and hide spinner.
        add_img_btn.text(add_btn_text);
        add_img_btn.prop("disabled", false).removeClass("disabled");
        $(".envira-ai-images-spinner").css("display", "none");
      },
    });
  });

  /**
   * Submit the envira ai image creation form via REST API.
   */
  $(".create-images-submit-btn").on("click", function (e) {
    e.preventDefault();
    let isValid = true;

    // Clear previous error states.
    $(".form-group").removeClass("envira-ai-error");
    $(".ai-error-message").hide();
    $(".envira-ai-response-error-msg").html("").hide();
    $(".generated-ai-images-section").remove();
    $(".button-envira-ai-write-new").hide();
    $(".generate-image-text").show();

    // Validate the prompt textarea.
    const prompt = $("#envira-ai-image-prompt").val().trim();
    if (!prompt) {
      $("#ai-prompt-error").show();
      $("#envira-ai-image-prompt")
        .closest(".form-group")
        .addClass("envira-ai-error");
      isValid = false;
    }

    // Validate the image type select.
    const image_type = $("#envira-ai-image-type").val();
    if (!image_type) {
      $("#ai-type-error").show();
      $("#envira-ai-image-type")
        .closest(".form-group")
        .addClass("envira-ai-error");
      isValid = false;
    }

    // Validate the aspect ratio select.
    const aspect_ratio = $("#envira-ai-image-aspect-ratio").val();
    if (!aspect_ratio) {
      $("#ai-aspect-error").show();
      $("#envira-ai-image-aspect-ratio")
        .closest(".form-group")
        .addClass("envira-ai-error");
      isValid = false;
    }

    // If form is valid, trigger REST API request.
    if (isValid) {
      // Prepare form data.
      const formData = new FormData();
      formData.append("prompt", prompt);
      formData.append("image_type", image_type);
      formData.append("aspect_ratio", aspect_ratio);

      // Update button text and show spinner.
      const create_btn = $(this);
      const creating_text = create_btn.attr("data-loading");
      const create_new_text = create_btn.attr("data-create");
      const button_text = create_btn.attr("data-button");

      create_btn
        .prop("disabled", true)
        .text(creating_text)
        .addClass("disabled");
      $(".envira-ai-images-spinner").css("display", "flex");

      // Make the fetch request to the REST API.
      fetch(envira_gallery_metabox.ai_image_crate_rest_url, {
        method: "POST",
        body: formData,
        headers: {
          "X-WP-Nonce": envira_gallery_metabox.ai_image_rest_nonce, // REST API nonce for authorization.
        },
      })
        .then((response) => response.json())
        .then((response) => {
          if (response.html) {
            // Handle success: Update UI.
            create_btn.text(create_new_text);
            $(".generate-image-text").hide();
            $(".button-envira-ai-write-new").show();
            $(".ajax-create-success.envira-ai-response-error-msg")
              .html('')
              .hide(); // Hide message - no need to display success message.

            $(".create-images-two-columns .right-column").append(response.html);
          } else {
            // Handle errors: Reset button text and show errors.
            create_btn.text(button_text);
            $(".generate-image-text").show();
            if (response.rest_invalid_prompt) {
              $(".prompt-ajax-error.envira-ai-response-error-msg")
                .html(response.rest_invalid_prompt)
                .show();
            }
            if (response.rest_api_error) {
              $(".ajax-create-error.envira-ai-response-error-msg")
                .html(response.rest_api_error)
                .show();
            }
          }
        })
        .catch((error) => {
          // Handle fetch error.
          create_btn.text(button_text);
          $(".ajax-create-error.envira-ai-response-error-msg")
            .html(error)
            .show();
        })
        .finally(() => {
          // Re-enable button and hide spinner after request completes.
          create_btn.prop("disabled", false).removeClass("disabled");
          $(".envira-ai-images-spinner").css("display", "none");
        });
    }
  });

  /**
   * Clear the form and response messages.
   */
  $(".button-envira-ai-write-new").on("click", function (e) {
    e.preventDefault();

    ai_selected_images_indices = [];
    ai_selected_images_array = [];

    // Clear the prompt textarea.
    $("#envira-ai-image-prompt").val("");

    // Reset the image type and aspect ratio selects to the default option.
    $("#envira-ai-image-type").val("");
    $("#envira-ai-image-aspect-ratio").val("");

    // Remove error states from form groups.
    $(".form-group").removeClass("envira-ai-error");

    // Hide error messages.
    $(".ai-error-message").hide();
    $(".envira-ai-response-error-msg").html("").hide();

    // Clear generated images if any.
    $(".generated-ai-images-section").remove();

    // Show the default button text.
    const button_text = $(".create-images-submit-btn").attr("data-button");
    $(".create-images-submit-btn")
      .text(button_text)
      .removeClass("disabled")
      .prop("disabled", false);

    // Show default text and hide write new button.
    $(".generate-image-text").show();
    $(".button-envira-ai-write-new").hide();

    // Hide select image button and show form fields.
    $(".ai-add-selected-images").hide();
    $(".form-group.ai-form-field").show();
  });

  /**
   * Download image from third party URL on click via REST API using fetch.
   */
  $(document).on(
    "click",
    ".ai-download-image-link.download-image",
    function (e) {
      e.preventDefault();
      $(".envira-ai-response-error-msg").html("").hide();

      const aiImageUrl = $(this).data("url");

      // Prepare the data for the fetch request.
      const aiImageData = {
        image_url: aiImageUrl,
      };

      // Show spinner.
      $(".envira-ai-images-spinner").css("display", "flex");

      fetch(envira_gallery_metabox.ai_download_image_rest_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": envira_gallery_metabox.ai_image_rest_nonce, // REST API nonce for authorization.
        },
        body: JSON.stringify(aiImageData),
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.success) {
            const blob = b64toBlob(data.image, "image/png");
            const blobUrl = URL.createObjectURL(blob);
            const imageName = "download-" + Date.now() + ".png";

            // IE Compatibility.
            if (window.navigator.msSaveOrOpenBlob) {
              window.navigator.msSaveOrOpenBlob(blob, imageName);
            } else {
              const a = document.createElement("a");
              a.href = blobUrl;
              a.download = imageName;
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
            }
          } else {
            alert(data.message);
          }
        })
        .catch(function (error) {
          alert("Error: " + error.message);
        })
        .finally(function () {
          // Hide spinner.
          $(".envira-ai-images-spinner").css("display", "none");
        });
    }
  );

  // Convert base64 string to a Blob.
  function b64toBlob(b64Data, contentType) {
    const sliceSize = 4096; // Larger chunk for performance.
    const byteCharacters = atob(b64Data);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
      const slice = byteCharacters.slice(offset, offset + sliceSize);
      const byteNumbers = new Array(slice.length);

      for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }

      const byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    }

    return new Blob(byteArrays, { type: contentType });
  }
});
