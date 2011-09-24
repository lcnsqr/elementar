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
		var parent = $(this).parents(".image_gallery_field").first().children(".image_parent");
		
		// Bloqueio
		$("#blocker").fadeIn("fast");
		// Generate new image item form
		$.post('/admin/content/xhr_render_image_gallery_item_form', { field_sname : $(image_gallery_actual_field).attr('name') }, function(data) {
			if ( data.done == true )
			{
				// Inserir
				$(parent).prepend(data.html);
				// Bloqueio
				$("#blocker").fadeOut("fast");
			}
		}, 'json');
	});

	/*
	 * New image item above
	 */
	$("a.image_add_up").live('click', function(event) {
		event.preventDefault();
		/*
		 * Gallery real field (json encoded)
		 */
		var image_gallery_actual_field = $(this).parents(".image_gallery_field").first().find(".image_gallery_actual_field");
		var current_item = $(this).parents('div.image_item').first();
		// Bloqueio
		$("#blocker").fadeIn("fast");
		// Generate new image item form
		$.post('/admin/content/xhr_render_image_gallery_item_form', { field_sname : $(image_gallery_actual_field).attr('name') }, function(data) {
			if ( data.done == true )
			{
				// Inserir
				$(current_item).before(data.html);
				// Bloqueio
				$("#blocker").fadeOut("fast");
			}
		}, 'json');
	});

	/*
	 * New image below
	 */
	$("a.image_add_down").live('click', function(event) {
		event.preventDefault();
		/*
		 * Gallery real field (json encoded)
		 */
		var image_gallery_actual_field = $(this).parents(".image_gallery_field").first().find(".image_gallery_actual_field");
		var current_item = $(this).parents('div.image_item').first();
		// Bloqueio
		$("#blocker").fadeIn("fast");
		// Generate new image item form
		$.post('/admin/content/xhr_render_image_gallery_item_form', { field_sname : $(image_gallery_actual_field).attr('name') }, function(data) {
			if ( data.done == true )
			{
				// Inserir
				$(current_item).after(data.html);
				// Bloqueio
				$("#blocker").fadeOut("fast");
			}
		}, 'json');
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

//]]>
