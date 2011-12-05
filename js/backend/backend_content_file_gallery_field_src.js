//<![CDATA[

$(function() {

	/*
	 * New file item
	 */
	$("a.file_add").live('click', function(event) {
		event.preventDefault();
		/*
		 * Gallery real field (json encoded)
		 */
		var file_gallery_actual_field = $(this).parents(".file_gallery_field").first().find(".file_gallery_actual_field");
		/*
		 * Gallery items container
		 */
		var parent = $(this).parents(".file_gallery_field").first().children(".file_parent:not(.file_parent_template)");
		
		/*
		 * Clone fiele item template
		 */
		var NewFile = $(".file_item_template").first().clone();
		// Redefinir
		$(NewFile).removeClass("file_item_template");
		$(NewFile).css("display", "none");
		/*
		 * Temporary file input name (unique) to receive file manager values
		 */
		var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="file_item_field_' + file_id + '"]').length > 0 )
		{
			var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewFile).find('.file_item_field').attr('name', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_field').attr('id', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_description_field').attr('name', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_description_field').attr('id', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_thumbnail').attr('id', 'file_item_thumbnail_file_item_field_' + file_id);
		$(NewFile).find('.file_details').attr('id', 'file_details_file_item_field_' + file_id);
		$(NewFile).find('a.file_erase').attr('href', 'file_item_field_' + file_id);
		$(NewFile).find('a.browse_file').attr('href', 'file_item_field_' + file_id);
		// Inserir
		$(parent).prepend(NewFile);
		$(NewFile).show("fast", "easeInSine");
	});

	/*
	 * New file item above
	 */
	$("a.file_add_up").live('click', function(event) {
		event.preventDefault();
		var current_item = $(this).parents('div.file_item').first();

		/*
		 * Clone file item template
		 */
		var NewFile = $(".file_item_template").first().clone();
		// Redefinir
		$(NewFile).removeClass("file_item_template");
		$(NewFile).css("display", "none");
		/*
		 * Temporary file input name (unique) to receive file manager values
		 */
		var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="file_item_field_' + file_id + '"]').length > 0 )
		{
			var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewFile).find('.file_item_field').attr('name', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_field').attr('id', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_description_field').attr('name', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_description_field').attr('id', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_thumbnail').attr('id', 'file_item_thumbnail_file_item_field_' + file_id);
		$(NewFile).find('.file_details').attr('id', 'file_details_file_item_field_' + file_id);
		$(NewFile).find('a.file_erase').attr('href', 'file_item_field_' + file_id);
		$(NewFile).find('a.browse_file').attr('href', 'file_item_field_' + file_id);
		// Insert
		$(current_item).before(NewFile);
		$(NewFile).show("fast", "easeInSine");

	});

	/*
	 * New file item below
	 */
	$("a.file_add_down").live('click', function(event) {
		event.preventDefault();

		var current_item = $(this).parents('div.file_item').first();
		/*
		 * Clone image template
		 */
		var NewFile = $(".file_item_template").first().clone();
		// Redefinir
		$(NewFile).removeClass("file_item_template");
		$(NewFile).css("display", "none");
		/*
		 * Temporary image input name (unique) to receive file manager values
		 */
		var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		while ( $('input[name="file_item_field_' + file_id + '"]').length > 0 )
		{
			var file_id = String((new Date()).getTime()).replace(/\D/gi,'');
		}
		$(NewFile).find('.file_item_field').attr('name', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_field').attr('id', 'file_item_field_' + file_id);
		$(NewFile).find('.file_item_description_field').attr('name', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_description_field').attr('id', 'file_item_field_' + file_id + '_description');
		$(NewFile).find('.file_item_thumbnail').attr('id', 'file_item_thumbnail_file_item_field_' + file_id);
		$(NewFile).find('.file_details').attr('id', 'file_details_file_item_field_' + file_id);
		$(NewFile).find('a.file_erase').attr('href', 'file_item_field_' + file_id);
		$(NewFile).find('a.browse_file').attr('href', 'file_item_field_' + file_id);
		// Insert
		$(current_item).after(NewFile);
		$(NewFile).show("fast", "easeInSine");
	});

	/*
	 * Remove file item
	 */
	$("a.file_delete").live('click', function(event) {
		event.preventDefault();

		var file_item = $(this).parents("div.file_item").first();
		$(file_item).hide("slow", function() {
			$(this).remove();
		});
	});

	/*
	 * Move file item up
	 */
	$("a.file_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.file_item").first();
		var swap = $(item).prev("div.file_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Move file item down
	 */
	$("a.file_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.file_item").first();
		var swap = $(item).next("div.file_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

});

/*
 * Serialize file gallery for saving
 */
$.fn.extend({
	prepareFileGalleryField: function(){
		var gallery = new Array();
		$(this).find(".file_item").each(function() {
			/*
			 * Push each file description into its array
			 */
			var file_description = $(this).find("input.file_item_description_field").val();
			var file_item = $(this).find("input.file_item_field").val();
			var file = $.parseJSON(file_item);
			if ( file != null ) {
				/*
				 * Push description text to array
				 */
				file.title = file_description;
				/*
				 * Push image to gallery
				 */
				gallery.push( $.toJSON(file) );
			}
		});
		/*
		 * Update gallery field
		 */
		$(this).find('input.file_gallery_actual_field').val($.toJSON(gallery));
	}
});

//]]>
