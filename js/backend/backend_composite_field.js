//<![CDATA[

$(function() {

	/******************************************
	 * Serialized composite fields assembling *
	 ******************************************/
	$.extend({
		prepareCompositeFields: function(){
			/*
			 * Update file field json data
			 */
			$("input.file").each(function() {
				var file = $.parseJSON($(this).val());
				if ( file != null ) {
					/*
					 * Push description text to array
					 */
					file.title = $('input#' + $(this).attr('id') + '_description').val();
				}
				$(this).val($.toJSON(file));
			});
	
			/*
			 * Update file gallery json data
			 */
			$(".file_gallery_field").each(function() {
				$(this).prepareFileGalleryField();
			});
	
			/*
			 * Update menu field json data
			 */
			$(".menu_field").each(function() {
				$(this).prepareMenuField();
			});
	
			/*
			 * Update youtube gallery json data
			 */
			$(".youtube_gallery_field").each(function() {
				$(this).prepareYoutubeGalleryField();
			});
		}
	});

	/******************************
	 * Method to activate tinymce *
	 * in specific textareas      *
	 ******************************/
	$.fn.extend({
		wysiwyg: function () {
			if ( $(this).hasClass('p') ) {
				var config = {
					// Location of TinyMCE script
					script_url : '/js/backend/tiny_mce/tiny_mce.js',
				
					// General options
					theme : "advanced",
					plugins : "",
				
					// Theme options
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,cut,copy,paste,pastetext,|,undo,redo,|,link,unlink,|,code",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					width : "100%",
				
					force_br_newlines : true,
					force_p_newlines : false,
					forced_root_block : '',
				
					theme_advanced_source_editor_width : 800,
					theme_advanced_source_editor_height : 480,
					relative_urls : false
				};
				$(this).tinymce(config);
			}
			else if ( $(this).hasClass('hypertext') ) {
				var config = {
					// Location of TinyMCE script
					script_url : '/js/backend/tiny_mce/tiny_mce.js',
				
					// General options
					theme : "advanced",
					plugins : "filemanager",
				
					// Theme options
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,undo,redo,|,link,unlink,filemanager,|,code",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					width : "100%",
					
					theme_advanced_source_editor_width : 800,
					theme_advanced_source_editor_height : 480,
					relative_urls : false
				};
				$(this).tinymce(config);
			}
		}
	});

	/**********************
	 * File gallery field *
	 **********************/
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

	/*
	 * File gallery assembling
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

	/***************
	 * Index field *
	 ***************/
	/*
	 * Dropdown contents items
	 */	
	$("input.index_field[type='text']").live('click', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$("input.index_field[type='text']").live('blur', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});

	$(".dropdown_items_listing_contents > li > a").live('click', function(event) {
		event.preventDefault();
		var input = $(this).parents(".dropdown_items_listing_position").first().prev("input");
		$(input).val($(this).attr("href"));
	});

	/*
	 * Index field assembling
	 */
	$.fn.extend({
		prepareIndexField: function(){
		}
	});

	/**************
	 * Menu field *
	 **************/
	/*
	 * Novo menu topo
	 */
	$("a.menu_add").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".menu_field").first().children(".menu_parent:visible");
		if ( $(parent).length > 0 ) {
			var NewMenu = $(".menu_item_template").first().clone();
			// Redefinir
			$(NewMenu).removeClass("menu_item_template");
			$(NewMenu).css("display", "none");
			// Inserir
			$(parent).prepend(NewMenu);
			$(NewMenu).show("fast", "easeInSine");
		}
		else
		{
			var NewParent = $(".menu_parent_template").first().clone();
			var NewMenu = $(NewParent).find(".menu_item_template");
			// Redefinir
			$(NewParent).removeClass("menu_parent_template");
			$(NewParent).css("display", "none");
			$(NewMenu).removeClass("menu_item_template");
			// Inserir
			$(this).parent(".menu_parent_add").after(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Novo menu acima
	 */
	$("a.menu_add_up").live('click', function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Redefinir
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Inserir
		$(this).parents('div.menu_item').first().before(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * Novo menu abaixo
	 */
	$("a.menu_add_down").live('click', function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Redefinir
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Inserir
		$(this).parents('div.menu_item').first().after(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * Novo submenu
	 */
	$("a.menu_add_submenu").live('click', function(event) {
		event.preventDefault();

		var NewParent = $(".menu_parent_template").first().clone();
		var NewMenu = $(NewParent).find(".menu_item_template");
		
		// Redefinir
		$(NewParent).removeClass("menu_parent_template");
		$(NewMenu).removeClass("menu_item_template");
		
		// Inserir
		var Menu = $(this).parents('div.menu_item').first();
		if ( $(Menu).find(".menu_parent").length > 0 )
		{
			$(NewMenu).css("display", "none");
			$(Menu).find(".menu_parent").first().prepend(NewMenu);
			$(NewMenu).show("fast", "easeInSine");
		}
		else
		{
			$(NewParent).css("display", "none");
			// Inserir menu parent
			$(this).parents('div.menu_item').first().append(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Remove menu
	 */
	$("a.menu_delete").live('click', function(event) {
		event.preventDefault();

		var menu_item = $(this).parents("div.menu_item").first();
		var parent = $(this).parents("div.menu_parent").first();
		if ( $(parent).find("div.menu_item").length == 1 && $(this).parents(".menu_field").first().find("div.menu_item:visible").length > 1)
		{
			$(parent).hide("slow", function() {
				$(this).remove();
			});
		}
		else if ( $(this).parents(".menu_field").first().find("div.menu_item:visible").length > 1 )
		{
			$(menu_item).hide("slow", function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Dropdown menu items
	 */	
	$(".menu_item_target > input[type='text']").live('click', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$(".menu_item_target > input[type='text']").live('blur', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});
	
	$(".dropdown_items_listing_targets > li > a").live('click', function(event) {
		event.preventDefault();
		var input = $(this).parents(".dropdown_items_listing_position").first().prev("input");
		$(input).val($(this).attr("href"));
	});

	/*
	 * Subir item de menu
	 */
	$("a.menu_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.menu_item").first();
		var swap = $(item).prev("div.menu_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Descer item de menu
	 */
	$("a.menu_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.menu_item").first();
		var swap = $(item).next("div.menu_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

	/*
	 * Menu assembling
	 */
	$.fn.extend({
		prepareMenuField: function(){
			var menus = new Array();
			$(this).children('.menu_parent:not(.menu_parent_template)').each(function() {
				$(this).children('.menu_item:not(.menu_item_template)').each(function() {
					var name = $(this).find('input[name="name"]').val();
					var target = $(this).find('input[name="target"]').val();
					if ( $(this).children('.menu_parent:not(.menu_parent_template)').length > 0 ) {
						var submenu = $(this).prepareMenuField();
					}
					else {
						var submenu = null;
					}
					menus.push( { name : name, target : target, menu : submenu } );
				});
			});
			$(this).find('input.menu_actual_field').val($.toJSON(menus));
		}
	});


	/*************************
	 * Youtube video gallery *
	 *************************/
	/*
	 * Novo vídeo
	 */
	$("a.youtube_add").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".youtube_gallery_field").first().children(".youtube_parent:visible");
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(parent).prepend(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Novo vídeo acima
	 */
	$("a.youtube_add_up").live('click', function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(this).parents('div.youtube_item').first().before(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Novo vídeo abaixo
	 */
	$("a.youtube_add_down").live('click', function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(this).parents('div.youtube_item').first().after(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Remove vídeo
	 */
	$("a.youtube_delete").live('click', function(event) {
		event.preventDefault();

		var youtube_item = $(this).parents("div.youtube_item").first();
		var parent = $(this).parents("div.youtube_parent").first();
		if ( $(parent).find("div.youtube_item").length == 1 && $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1)
		{
			$(parent).hide("slow", function() {
				$(this).remove();
			});
		}
		else if ( $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1 )
		{
			$(youtube_item).hide("slow", function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Subir vídeo
	 */
	$("a.youtube_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.youtube_item").first();
		var swap = $(item).prev("div.youtube_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Descer vídeo
	 */
	$("a.youtube_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.youtube_item").first();
		var swap = $(item).next("div.youtube_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

	/*
	 * Youtube gallery assembling
	 */
	$.fn.extend({
		prepareYoutubeGalleryField: function(){
			var videos = new Array();
			$(this).children('.youtube_parent:not(.youtube_parent_template)').each(function() {
				$(this).children('.youtube_item:not(.youtube_item_template)').each(function() {
					var url = $(this).find('input[name="url"]').val();
					var description = $(this).find('input[name="description"]').val();
					if ( url != '' ) {
						videos.push( { url : url, description : description } );
					}
				});
			});
			$(this).find('input.youtube_gallery_actual_field').val($.toJSON(videos));
		}
	});

});

//]]>
