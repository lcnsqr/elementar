/**
 *      Copyright 2012 Luciano Siqueira <lcnsqr@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

/** 
 * Backend Composite Fields Javascript
 * 
 * Client side code to manipulate the composite fields in backend
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	/*
	 * Alternate betwin language inputs
	 */
	$(document).on('click', '.input_lang_tab_link', function(event){
		event.preventDefault();
		var lang_code = $(this).attr('href');
		var form_window_column_input = $(this).parents('.form_window_column_input').first();
		// Change tab link colors
		$(form_window_column_input).find("a.input_lang_tab_link.current").removeClass("current");
		$(this).addClass("current");
		// Hide all other language inputs
		$(form_window_column_input).children('.input_lang_field:visible').hide();
		// Show requested language input
		$(form_window_column_input).children('.input_lang_field_' + lang_code).show();
	});
	
	/******************************************
	 * Serialized composite fields assembling *
	 ******************************************/
	$.extend({
		prepareCompositeFields: function(){
			/*
			 * Update file field json data
			 */
			$("input.file").each(function() {
				var file = null;
				if ( $(this).val() != "" ){
					file = $.parseJSON($(this).val());
				}
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
				var menus = $(this).prepareMenuField();
				$(this).find('input.menu_actual_field').val($.toJSON(menus));
			});
	
			/*
			 * Update youtube gallery json data
			 */
			$(".youtube_gallery_field").each(function() {
				$(this).prepareYoutubeGalleryField();
			});
			
			/*
			 * Update index field
			 */
			$(".index_field").each(function() {
				$(this).prepareIndexField();
			});

			/*
			 * Update list field
			 */
			$(".list_field").each(function() {
				$(this).prepareListField();
			});

			/*
			 * Update hypertext field
			 */
			$(".hypertext_field").each(function() {
				$(this).prepareHypertextField();
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
					script_url : '/js/backend/tinymce/tinymce.min.js',
					plugins: [
						"advlist autolink lists link image charmap print preview anchor",
						"searchreplace visualblocks code fullscreen",
						"insertdatetime media contextmenu paste"
					],
					toolbar: "undo redo | styleselect | bold italic | link image",
					file_browser_callback: function(fieldname, url, type, win) {
						win.open("/backend/file/manager?parent=tinymce&fieldname="+fieldname, "_blank", "height=480, width=880");
					},
					//language: 'pt_BR',
					width : "100%",
					height : "24em",
					force_br_newlines : true,
					force_p_newlines : false,
					forced_root_block : '',
					relative_urls : false
				};
				$(this).tinymce(config);
			}
			else if ( $(this).hasClass('hypertext') ) {
				var config = {
					// Location of TinyMCE script
					script_url : '/js/backend/tinymce/tinymce.min.js',
					plugins: [
						"advlist autolink lists link image charmap print preview anchor",
						"searchreplace visualblocks code fullscreen",
						"insertdatetime media table contextmenu paste"
					],
					toolbar: "undo redo | styleselect | bold italic | bullist numlist | link image",
					file_browser_callback: function(fieldname, url, type, win) {
						win.open("/backend/file/manager?parent=tinymce&fieldname="+fieldname, "_blank", "height=480, width=880");
					},
					//language: 'pt_BR',
					width : "100%",
					height : "24em",
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
	$(document).on('click', "a.file_add", function(event) {
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
		// Reset
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
		$(parent).prepend(NewFile);
		$(NewFile).show("fast", "easeInSine");
	});

	/*
	 * Open file manager
	 */
	$(document).on('click', '.browse_file', function(event){
		event.preventDefault();
		/*
		 * Identifies receptor input
		 */
		var identifier = $(this).attr('href');
		/*
		 * Pass caller data to file manager 
		 */
		window.open('/backend/file/manager?parent=file_field&identifier=' + identifier, '_blank', 'height=480, width=880');
	});

	/*
	 * New file item above
	 */
	$(document).on('click', "a.file_add_up", function(event) {
		event.preventDefault();
		var current_item = $(this).parents('div.file_item').first();

		/*
		 * Clone file item template
		 */
		var NewFile = $(".file_item_template").first().clone();
		// Reset
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
	$(document).on('click', "a.file_add_down", function(event) {
		event.preventDefault();

		var current_item = $(this).parents('div.file_item').first();
		/*
		 * Clone image template
		 */
		var NewFile = $(".file_item_template").first().clone();
		// Reset
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
	 * Discard file in file field	
	 */
	$(document).on("click", ".file_erase", function(event) {
		event.preventDefault();
		var container = $(this).parents(".file_item").first();
		/*
		 * Clear file input field
		 */
		$(container).find("input.upload_file").val("");
		/*
		 * Update thumbnail and hide loading animation
		 */
		var file_thumbnail = $(container).find(".file_item_thumbnail");
		$(file_thumbnail).addClass('file_item_thumbnail_missing');
		$(file_thumbnail).removeAttr("style");
		/*
		 * Empty the file id field
		 */
		var field_sname = $(this).attr('href');
		var file_field = $(container).parents('.file_field').first().find("input[name='"+field_sname+"']");
		$(file_field).val('');
		/*
		 * Empty the description field
		 */
		var file_description = $(container).find("input[name='"+field_sname+"_description']");
		$(file_description).val('');
		/*
		 * Hide details
		 */
		var file_details = $(container).find("ul.file_details");
		$(file_details).hide();
		$(file_details).find('span').html('');
	});

	/*
	 * Remove file item
	 */
	$(document).on('click', "a.file_delete", function(event) {
		event.preventDefault();

		var file_item = $(this).parents("div.file_item").first();
		$(file_item).hide("fast", 'easeOutSine', function() {
			$(this).remove();
		});
	});

	/*
	 * Move file item up
	 */
	$(document).on("click", "a.file_up", function(event) {
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
	$(document).on("click", "a.file_down", function(event) {
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

				var file = null;
				if ( file_item != "" ){
					file = $.parseJSON(file_item);
				}

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

	/*******************
	 * Hypertext field *
	 *******************/
	/*
	 * New hypertext page
	 */
	$(document).on('click', "a.add_hypertext_page", function(event) {
		event.preventDefault();
		var hypertext = $(this).parents('.hypertext_field').first();
		var NewPage = $(hypertext).find(".hypertext_page_template").first().clone();
		// Reset
		$(NewPage).removeClass("hypertext_page_template");
		$(NewPage).addClass("hypertext_page");
		$(NewPage).css("display", "none");
		$(NewPage).find("textarea.page").addClass('hypertext');
		// Insert
		$(this).parents('.hypertext_link_container').first().before(NewPage);
		$(NewPage).show("fast", "easeInSine");
		// WYSIWYG textarea activation
		$(NewPage).find('textarea').each(function(){ $(this).wysiwyg(); });
	});

	/*
	 * Remove hypertext page
	 */
	$(document).on('click', "a.remove_hypertext_page", function(event) {
		event.preventDefault();

		var page = $(this).parents("div.hypertext_page").first();
		$(page).hide("fast", 'easeOutSine', function() {
			$(this).remove();
		});
	});

	/*
	 * Hypertext pages assembling
	 */
	$.fn.extend({
		prepareHypertextField: function(){
			var pages = new Array();
			$(this).find(".hypertext_page").each(function() {
				/*
				 * Push page into pages
				 */
				var page = $(this).find("textarea.page").val();
				pages.push(page);
			});
			/*
			 * Update hypertext field
			 */
			$(this).find('input.hypertext_actual_field').val($.toJSON(pages));
		}
	});

	/***************
	 * Index field *
	 ***************/
	/*
	 * Dropdown contents items
	 */	
	$(document).on('click', "input.index_field[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$(document).on('blur', "input.index_field[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});

	/*
	 * Choose parent content for the field
	 */
	$(document).on('click', ".index_root_content", function(event) {
		event.preventDefault();
		var content_id = $(this).attr('href');
		var content_name = $(this).html();
		
		var root_selector = $(this).parents('.dropdown_items_listing_inline').first().children('a');
		
		/*
		 * Load index field filters
		 */
		$.post('/backend/editor/xhr_render_index_filter', { content_id : content_id, field_sname : $(root_selector).attr('href') }, function(data)
		{
			$(root_selector).html(content_name);
			$( 'div#' + $(root_selector).attr('href') + '_filter_forms' ).html(data.html);
		}, 'json');

	});

	/*
	 * Index field assembling
	 */
	$.fn.extend({
		prepareIndexField: function()
		{
			var content_id = $(this).find('form').find('input[name="content_id"]').val();
			var order_by = $(this).find('form').find('input[name="order_by"]:checked').val();
			var direction = $(this).find('form').find('input[name="direction"]:checked').val();
			var limit = $(this).find('form').find('input[name="limit"]').val();
			var depth = $(this).find('form').find('input[name="depth"]').val();
			var filter = { content_id : content_id, order_by : order_by, direction : direction, limit : limit, depth : depth };
			$(this).find('input.noform').val($.toJSON(filter));
		}
	});

	/***************
	 * List field *
	 ***************/
	/*
	 * Dropdown contents items
	 */	
	$(document).on('click', "input.list_field[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$(document).on('blur', "input.list_field[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});

	/*
	 * Choose parent content for the field
	 */
	$(document).on('click', ".list_root_content", function(event) {
		event.preventDefault();
		var content_id = $(this).attr('href');
		var content_name = $(this).html();
		
		var root_selector = $(this).parents('.dropdown_items_listing_inline').first().children('a');
		
		/*
		 * Load list field filters
		 */
		$.post('/backend/editor/xhr_render_list_filter', { content_id : content_id, field_sname : $(root_selector).attr('href') }, function(data)
		{
			$(root_selector).html(content_name);
			$( 'div#' + $(root_selector).attr('href') + '_filter_forms' ).html(data.html);
		}, 'json');

	});

	/*
	 * List field assembling
	 */
	$.fn.extend({
		prepareListField: function()
		{
			var content_id = $(this).find('form').find('input[name="content_id"]').val();
			var content_types = [];
			$(this).find('form').find('input[name="list_content_type[]"]:checked').each(function(){
				content_types.push($(this).val());
			});
			var order_by = $(this).find('form').find('input[name="order_by"]:checked').val();
			var direction = $(this).find('form').find('input[name="direction"]:checked').val();
			var limit = $(this).find('form').find('input[name="limit"]').val();
			var filter = { content_id : content_id, order_by : order_by, direction : direction, limit : limit, content_types : content_types };
			$(this).find('input.noform').val($.toJSON(filter));
		}
	});

	/**************
	 * Menu field *
	 **************/
	/*
	 * New top menu
	 */
	$(document).on('click', "a.menu_add", function(event) {
		event.preventDefault();
		var parent = $(this).parents(".menu_field").first().children(".menu_parent:visible");
		if ( $(parent).length > 0 ) {
			var NewMenu = $(".menu_item_template").first().clone();
			// Reset
			$(NewMenu).removeClass("menu_item_template");
			$(NewMenu).css("display", "none");
			// Insert
			$(parent).prepend(NewMenu);
			$(NewMenu).show("fast", "easeInSine");
		}
		else
		{
			var NewParent = $(".menu_parent_template").first().clone();
			var NewMenu = $(NewParent).find(".menu_item_template");
			// Reset
			$(NewParent).removeClass("menu_parent_template");
			$(NewParent).css("display", "none");
			$(NewMenu).removeClass("menu_item_template");
			// Insert
			$(this).parent(".menu_parent_add").after(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * New menu above
	 */
	$(document).on('click', "a.menu_add_up", function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Reset
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Insert
		$(this).parents('div.menu_item').first().before(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * New menu below
	 */
	$(document).on('click', "a.menu_add_down", function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Reset
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Insert
		$(this).parents('div.menu_item').first().after(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * New submenu
	 */
	$(document).on('click', "a.menu_add_submenu", function(event) {
		event.preventDefault();

		var NewParent = $(".menu_parent_template").first().clone();
		var NewMenu = $(NewParent).find(".menu_item_template");
		
		// Reset
		$(NewParent).removeClass("menu_parent_template");
		$(NewMenu).removeClass("menu_item_template");
		
		// Insert
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
			// Insert menu parent
			$(this).parents('div.menu_item').first().append(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Remove menu
	 */
	$(document).on('click', "a.menu_delete", function(event) {
		event.preventDefault();

		var menu_item = $(this).parents("div.menu_item").first();
		var parent = $(this).parents("div.menu_parent").first();
		if ( $(parent).find("div.menu_item:visible").length == 1 )
		{
			$(parent).hide("fast", 'easeOutSine', function() {
				$(this).remove();
			});
		}
		else if ( $(parent).find("div.menu_item:visible").length > 1 )
		{
			$(menu_item).hide("fast", 'easeOutSine', function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Dropdown menu items
	 */	
	$(document).on('click', ".menu_item_target > input[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$(document).on('blur', ".menu_item_target > input[type='text']", function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});
	
	$(document).on('click', ".dropdown_items_listing_targets > li > a", function(event) {
		event.preventDefault();
		var input = $(this).parents(".dropdown_items_listing_position").first().prev("input");
		$(input).val($(this).attr("href"));
	});

	/*
	 * Move item menu up
	 */
	$(document).on("click", "a.menu_up", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.menu_item").first();
		var swap = $(item).prev("div.menu_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Move item menu down
	 */
	$(document).on("click", "a.menu_down", function(event) {
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
			return menus;
		}
	});


	/*************************
	 * Youtube video gallery *
	 *************************/
	/*
	 * New video
	 */
	$(document).on('click', "a.youtube_add", function(event) {
		event.preventDefault();
		var parent = $(this).parents(".youtube_gallery_field").first().children(".youtube_parent:visible");
		var NewVideo = $(".youtube_item_template").first().clone();
		// Reset
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Insert
		$(parent).prepend(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * New video up
	 */
	$(document).on('click', "a.youtube_add_up", function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// empty field
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// insert
		$(this).parents('div.youtube_item').first().before(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * New video below
	 */
	$(document).on('click', "a.youtube_add_down", function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// Empty field
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Insert
		$(this).parents('div.youtube_item').first().after(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Remove video
	 */
	$(document).on('click', "a.youtube_delete", function(event) {
		event.preventDefault();

		var youtube_item = $(this).parents("div.youtube_item").first();
		var parent = $(this).parents("div.youtube_parent").first();
		if ( $(parent).find("div.youtube_item").length == 1 && $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1)
		{
			$(parent).hide("fast", 'easeOutSine', function() {
				$(this).remove();
			});
		}
		else if ( $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1 )
		{
			$(youtube_item).hide("fast", 'easeOutSine', function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Move video up
	 */
	$(document).on("click", "a.youtube_up", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.youtube_item").first();
		var swap = $(item).prev("div.youtube_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Move video down
	 */
	$(document).on("click", "a.youtube_down", function(event) {
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
