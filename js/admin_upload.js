//<![CDATA[

$(function() {

/*
 * Envio automático ao escolher imagem
 */
$("input.upload_file").live("change", function() {
	$(this).parents("form.upload_image").first().submit();
});

/*
 * Image upload
 */
$(".upload_image").live("submit", function(event) {
	/*
	 * This upload form
	 */
	var form = $(this);
	
	/*
	 * Don't send if no file was selected
	 */
	if ( $(form).find("input.upload_file").val() == "" ) {
		return false;
	}
	
	/*
	 * Get the upload session id for this image field
	 */
	var upload_session_id = $(form).find("input[name='upload_session_id']").val();

	/*
	 * Thumbnail
	 */
	var image_thumbnail = $(form).parents('.image_item').first().find(".image_item_thumbnail");

	/*
	 * Loading animation
	 */
	var upload_loading = $(image_thumbnail).find(".loading");
	$(upload_loading).fadeIn("fast");
	
	/*
	 * Cancel uploading action
	 */
	var image_cancel_item = $(form).parents('.menu_item_menu').first().find('.image_cancel_item');
	$(image_cancel_item).show('fast');

	/*
	 * Check upload progress
	 */
	$(form).everyTime("3s", 'upload_session_status', function() {
		$.post("/admin/upload/xhr_read_upload_status", { upload_session_id : upload_session_id }, function(data){
			try {
				if ( data.done == true ) {
					/*
					 * Stop timer
					 */
					$(form).stopTime('upload_session_status');
					/*
					 * Put image id in the field
					 */
					var field_sname = $(form).find("input[name='field_sname']").first().val();
					var image_field = $(form).parents('.image_field').first().find("input[name='"+field_sname+"']");
					$(image_field).val(data.image_id);
					/*
					 * Update thumbnail and hide loading animation
					 */
					$(image_thumbnail).css("backgroundImage", 'url(' + data.thumb_uri + ')');
					$(upload_loading).fadeOut("slow");
					$(image_cancel_item).hide('slow');
				}
			}
			catch (err) {
				$(form).stopTime('upload_session_status');
				/*
				 * Hide loading animation
				 */
				$(upload_loading).fadeOut("slow");
				$(image_cancel_item).hide('slow');
			}
		}, "json");
	});
});

$(".image_cancel").live("click", function(event) {
	event.preventDefault();
	var container = $(this).parents(".image_item").first();
	/*
	 * Stop timer
	 */
	$(container).find("form.upload_image").stopTime('upload_session_status');
	/*
	 * Stop upload
	 */
	$(container).find("iframe").attr('src', '/admin/upload/empty_iframe');
	/*
	 * Clear file input field
	 */
	$(container).find("input.upload_file").val("");
	/*
	 * Hide cancel upload and loading animation
	 */
	$(container).find(".image_cancel_item").hide("slow");
	$(container).find(".loading").fadeOut("slow");
});

$(".image_erase").live("click", function(event) {
	event.preventDefault();
	var container = $(this).parents(".image_item").first();
	/*
	 * Stop timer
	 */
	$(container).find("form.upload_image").stopTime('upload_session_status');
	/*
	 * Stop upload
	 */
	$(container).find("iframe").attr('src', '/admin/upload/empty_iframe');
	/*
	 * Clear file input field
	 */
	$(container).find("input.upload_file").val("");
	/*
	 * Update thumbnail and hide loading animation
	 */
	var image_thumbnail = $(container).find(".image_item_thumbnail");
	$(image_thumbnail).css("backgroundImage", 'none');
	/*
	 * Hide cancel upload and loading animation
	 */
	$(container).find(".image_cancel_item").hide("slow");
	$(container).find(".loading").fadeOut("slow");
	/*
	 * Empty the image id field
	 */
	var field_sname = $(container).find("form.upload_image").find("input[name='field_sname']").first().val();
	var image_field = $(container).parents('.image_field').first().find("input[name='"+field_sname+"']");
	$(image_field).val('');
	/*
	 * Empty the description field
	 */
	var image_description = $(container).find("input[name='"+field_sname+"_description']");
	$(image_description).val('');
});

});

//]]>

