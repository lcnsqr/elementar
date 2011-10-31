//<![CDATA[

$(function() {

	/*
	 * New image item
	 */
	$("a.image_add").live('click', function(event) {
		event.preventDefault();
		/*
		 * Gallery real field (json encoded)
		 */
		var image_gallery_actual_field = $(this).parents(".image_gallery_field").first().find(".image_gallery_actual_field");
		/*
		 * Gallery items container
		 */
		var parent = $(this).parents(".image_gallery_field").first().children(".image_parent:not(.image_parent_template)");
		
		/*
		 * Clone image template
		 */
		var NewImage = $(".image_item_template").first().clone();
		// Redefinir
		$(NewImage).removeClass("image_item_template");
		$(NewImage).css("display", "none");
		/*
		 * Temporary image input name (unique) to receive file manager values
		 */
		var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="image_item_field_' + image_id + '"]').length > 0 )
		{
			var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewImage).find('.image_item_field').attr('name', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_field').attr('id', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_description_field').attr('name', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_description_field').attr('id', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_thumbnail').attr('id', 'image_item_thumbnail_image_item_field_' + image_id);
		$(NewImage).find('a.image_erase').attr('href', 'image_item_field_' + image_id);
		$(NewImage).find('a.browse_file').attr('href', 'image_item_field_' + image_id);
		// Inserir
		$(parent).prepend(NewImage);
		$(NewImage).show("fast", "easeInSine");
	});

	/*
	 * New image item above
	 */
	$("a.image_add_up").live('click', function(event) {
		event.preventDefault();
		var current_item = $(this).parents('div.image_item').first();

		/*
		 * Clone image template
		 */
		var NewImage = $(".image_item_template").first().clone();
		// Redefinir
		$(NewImage).removeClass("image_item_template");
		$(NewImage).css("display", "none");
		/*
		 * Temporary image input name (unique) to receive file manager values
		 */
		var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="image_item_field_' + image_id + '"]').length > 0 )
		{
			var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewImage).find('.image_item_field').attr('name', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_field').attr('id', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_description_field').attr('name', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_description_field').attr('id', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_thumbnail').attr('id', 'image_item_thumbnail_image_item_field_' + image_id);
		$(NewImage).find('a.image_erase').attr('href', 'image_item_field_' + image_id);
		$(NewImage).find('a.browse_file').attr('href', 'image_item_field_' + image_id);
		// Insert
		$(current_item).before(NewImage);
		$(NewImage).show("fast", "easeInSine");

	});

	/*
	 * New image below
	 */
	$("a.image_add_down").live('click', function(event) {
		event.preventDefault();

		var current_item = $(this).parents('div.image_item').first();
		/*
		 * Clone image template
		 */
		var NewImage = $(".image_item_template").first().clone();
		// Redefinir
		$(NewImage).removeClass("image_item_template");
		$(NewImage).css("display", "none");
		/*
		 * Temporary image input name (unique) to receive file manager values
		 */
		var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="image_item_field_' + image_id + '"]').length > 0 )
		{
			var image_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewImage).find('.image_item_field').attr('name', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_field').attr('id', 'image_item_field_' + image_id);
		$(NewImage).find('.image_item_description_field').attr('name', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_description_field').attr('id', 'image_item_field_' + image_id + '_description');
		$(NewImage).find('.image_item_thumbnail').attr('id', 'image_item_thumbnail_image_item_field_' + image_id);
		$(NewImage).find('a.image_erase').attr('href', 'image_item_field_' + image_id);
		$(NewImage).find('a.browse_file').attr('href', 'image_item_field_' + image_id);
		// Insert
		$(current_item).after(NewImage);
		$(NewImage).show("fast", "easeInSine");
	});

	/*
	 * Remove image item
	 */
	$("a.image_delete").live('click', function(event) {
		event.preventDefault();

		var image_item = $(this).parents("div.image_item").first();
		$(image_item).hide("slow", function() {
			$(this).remove();
		});
	});

	/*
	 * Move image item up
	 */
	$("a.image_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.image_item").first();
		var swap = $(item).prev("div.image_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Move image item down
	 */
	$("a.image_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.image_item").first();
		var swap = $(item).next("div.image_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

});

/*
 * Serialize image gallery for writing
 */
function prepare_image_gallery_field(list) {
	var gallery = new Array();
	$(list).find(".image_item").each(function() {
		var image_id = $(this).find("input.image_id").val();
		var image_description = $(this).find("input.image_description").val();
		if ( image_id != '' ) {
			gallery.push( { image_id : image_id, image_description : image_description } );
		}
	});
	return gallery;
}

/*
 * Serialize images gallery for saving
 */
$.fn.extend({
	prepareImageGalleryField: function(){
		var gallery = new Array();
		$(this).find(".image_item").each(function() {
			/*
			 * Push each image image description into its array
			 */
			var image_description = $(this).find("input.image_item_description_field").val();
			var image_item = $(this).find("input.image_item_field").val();
			var img = $.parseJSON(image_item);
			if ( img != null ) {
				/*
				 * Push description text to array
				 */
				img.title = image_description;
				/*
				 * Push image to gallery
				 */
				gallery.push( $.toJSON(img) );
			}
		});
		/*
		 * Update gallery field
		 */
		$(this).find('input.image_gallery_actual_field').val($.toJSON(gallery));
	}
});

//]]>
