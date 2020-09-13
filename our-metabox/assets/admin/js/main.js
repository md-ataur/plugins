var frame, gframe;
; (function ($) {
	$(document).ready(function () {
		/**
		 * Datepicker 
		 * ============================================
		 */
		$(".datepicker").datepicker();


		/**
		 * Image upload mechanism 
		 * ============================================
		 */

		/* Image show from the database */
		var img_url = $("#omb_image_url").val();
		if (img_url) {
			$("#image_container").html(`<img src='${img_url}' />`);
		}

		/* Media frame open */ 
		$("#image_upload").on("click", function () {
			/* If the media frame already exists, reopen it */
			if (frame) {
				frame.open();
				return false;
			}
			
			/* Create a new media frame */			
			frame = wp.media({ 	
				// wp.media is a global object
				title: "Upload Image",
				button: {
					text: "Select Image"
				},
				multiple: false
			});

			/* Event listener add */
			frame.on("select", function () {

				// Get media attachment details from the frame state
				var attachment = frame.state().get("selection").first().toJSON();
				//console.log(attachment);

				// Send the attachment id to our hidden input field
				$("#omb_image_id").val(attachment.id);

				// Send the attachment url to our hidden input field
				$("#omb_image_url").val(attachment.sizes.thumbnail.url);

				// Selection Image show
				$("#image_container").html(`<img src='${attachment.sizes.thumbnail.url}' />`);

			});

			frame.open();

			return false;
		});



		/**
		 * Gallery upload mechanism 
		 * ============================================
		 */
		
		$("#gallery_upload").on("click", function () {			
			/* If the media frame already exists, reopen it */
			if (gframe) {
				gframe.open();
				return false;
			}

			// Create a new media frame
			gframe = wp.media({
				// wp.media is a global object
				title: "Galley Upload",
				button: {
					text: "Select Galley"
				},
				multiple: true
			});

			/* Event listener add */
			gframe.on("select", function () {				
				var image_ids = [];
				var image_urls = [];
				
				// Get media attachment details from the frame state
				var attachments = gframe.state().get("selection").toJSON();
				
				$("#gallery_container").html('');				
				for (i in attachments) {
					var attachment = attachments[i];
					image_ids.push(attachment.id);
					image_urls.push(attachment.sizes.thumbnail.url);
					// Selection Image show
					$("#gallery_container").append(`<img src="${attachment.sizes.thumbnail.url}">`);
				}

				// Convert array to string and Separated by ;
				$("#omb_gl_id").val(image_ids.join(";"));
				$("#omb_gl_url").val(image_urls.join(";"));

				//console.log(image_ids);
			});

			gframe.open();

			return false;
		});


		/* Galley show from the database */
		var galllery_url = $("#omb_gl_url").val();
		
		// split by ; and convert to an array		
		galllery_url = galllery_url ? galllery_url.split(";") : [];
		//console.log(galllery_url);
		for (i in galllery_url) {
			var single_image = galllery_url[i];
			$("#gallery_container").append(`<img src='${single_image}' />`);
		}

	});

})(jQuery);

