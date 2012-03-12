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
 * Backend File Javascript
 * 
 * Client side code for handling the file manager actions
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	// Unfocus proper elements
	$("body").click(function() {
		// Discard labels being edited
		$("form.label").find("input.edit[type='text']").each(function() {
			$(this).removeClass("edit");
		});
	});

	/*
	 * Update file listing contents
	 */
	(function( $ ) {
		$.fn.extend({
			update_listing: function (path, title, update_tree) {

				var listing = this;
				
				// File listing loading animation
				$(listing).next(".loading").fadeIn("fast");
	
				$.post('/backend/file/xhr_render_contents', { path : path, parent : $.getUrlVar('parent') }, function(data) {
					try {
						if ( data.done == true ) {
							/*
							 * File listing
							 */
							$(listing).html(data.html);
							/*
							 * Locate selected item in listing
							 */
							if ( $(listing).find("a.item.block.current").length > 0 )
							{
								var selected = $(listing).find("a.item.block.current").first();
								var details = $(selected).next('.item_details').html();
								$('#current_file_details').html(details).show('fast');
							}
							else
							{
								/*
								 * Hide previous file details
								 */
								$('#current_file_details').hide('fast', function(){
									$(this).html('');
								});
							}

							/*
							 * Update directories tree?
							 */
							if ( update_tree ) {
								$.post('/backend/file/xhr_render_tree_unfold', { path : path }, function(data) {
									if ( $(".label.folder > a.current").length > 0 ) {
										/*
										 * Remove current item class
										 */
										$(".label.folder > a.current").removeClass('current');
									}
									$("#tree_listing_content_root").html(data.html);
									if ( $(".label.folder > a.current").length == 0 ) {
										/*
										 * No item selected, it's the root!
										 */
										$(".label.folder > a").first().addClass('current');
									}
								}, 'json');
							}
							else {
								$(".label.folder > a.current").removeClass('current');
								$('.label.folder > a[href="'+path+'"]').addClass('current');
							}
							/*
							 * Update action links
							 */
							$('#current_folder_title').html(data.title);
							$('#current_folder_mkdir').attr('href', data.path);
							$('#current_folder_upload').attr('href', data.path);
						}
					}
					catch (err) {
						showClientWarning("Communication error");
					}
					// File listing loading animation
					$(listing).next(".loading").stop().fadeOut("fast");
				}, 'json');
	
			}
		});
	})( jQuery );

	/*
	 * Select folder in tree
	 */
	$(".label.folder > a").live("click", function(event) {
		event.preventDefault();

		/*
		 * Show folder contents
		 */
		var title = $(this).attr('title');
		var path = $(this).attr('href');
		$("#file_manager_listing").update_listing(path, title, false);
	});
	
	/*
	 * Select file listing item
	 */
	$('a.item.block').live('click', function(event){
		event.preventDefault();
		$("a.item.block.current").removeClass('current');
		$(this).addClass('current');
		var details = $(this).next('.item_details').html();
		$('#current_file_details').html(details).show('fast');
	});

	/*
	 * Enter directory item upon double click
	 */
	$('a.item.block.directory').live('dblclick', function(event){
		event.preventDefault();
		/*
		 * Show folder contents
		 */
		var title = $(this).attr('title');
		var path = $(this).attr('href');
		$("#file_manager_listing").update_listing(path, title, true);
	});

	
	/*
	 * Insert selected file URI
	 */
	$('a.insert').live('click', function(event){
		event.preventDefault();

		/* 
		 * File details
		 */
		var uri = $(this).next('.action_insert').html();
		var title = $(this).attr('title');
		var details = $(this).parents('#current_file_details');
		var mime = $(details).find('span.mime').first().html();
		var size = $(details).find('span.size').first().html();
		var width = $(details).find('span.width').first().html();
		var height = $(details).find('span.height').first().html();
		var thumbnail = $(details).find('span.icon').first().html();
		
		/*
		 * Identifies file manager caller
		 * and responds correctly
		 */
		if ( $.getUrlVar('parent') == 'tinymce' ) { 
			FileManagerDialog.insert(uri);
		}
		else if ( $.getUrlVar('parent') == 'direct' ) {
			var identifier = $.getUrlVar('identifier');
			var field = window.opener.$('input[name="' + identifier + '"]');
			var field_description = window.opener.$('input#' + identifier + '_description');
			var field_thumbnail = window.opener.$('div#file_item_thumbnail_' + identifier);
			var field_details = window.opener.$('ul#file_details_' + identifier);
			var field_details_uri = window.opener.$(field_details).find('span.uri');
			var field_details_mime = window.opener.$(field_details).find('span.mime');
			var field_details_size = window.opener.$(field_details).find('span.size');
			if ($(field) != null) {
				/*
				 * Convert variables to json before returning to parent field
				 */
				var contents = { uri : uri, title : title, mime : mime, size : size, width : width, height : height, thumbnail : thumbnail };
				$(field).val($.toJSON(contents));
				/*
				 * Check for previous description and update
				 */
				if ( $(field_description).val() == '' ) {
					$(field_description).val(title);
				}
				/*
				 * Update Thumbnail
				 */
				$(field_thumbnail).css('background-image', 'url("' + thumbnail + '")');
				/*
				 * File details
				 */
				$(field_details_uri).html(uri);
				$(field_details_mime).html(mime);
				$(field_details_size).html(size);
				$(field_details).show();
			}
			/*
			 * Close File manager
			 */
			window.close();
		}
	});

	/*
	 * Show existing contents listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();

		// Loading icon
		$("#tree_loading").fadeIn("fast");

		var path = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/backend/file/xhr_render_tree", { path : path }, function(data){
			try {
				if ( data.done == true ) {
					$(listing).html(data.html);
					$(listing).slideDown("fast", "easeInSine");
					$(bullet).addClass("unfold");
					$(bullet).removeClass("fold");
				}
			}
			catch (err) {
				showClientWarning("Communication error");
			}
			// Loading icon
			$("#tree_loading").fadeOut("fast");

		}, "json");
	});
	/*
	 * Hide contents listing and rotate bullet arrow
	 */
	$("a.unfold.folder_switch").live("click", function(event) {
		event.preventDefault();
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		$(listing).slideUp("fast", "easeOutSine");
		$(this).addClass("fold");
		$(this).removeClass("unfold");
	});

	/*
	 * Show upload file form
	 */
	$('#current_folder_upload').live('click', function(event){
		event.preventDefault();
		var path = $(this).attr('href');
		$.post("/backend/file/xhr_render_upload_form", { path : path }, function(data){
			try {
				if ( data.done == true ) {
					$('#current_folder_details').after(data.html);
					$('#upload_form_container_' + data.upload_session_id).show('fast');
				}
			}
			catch (err) {
				showClientWarning("Communication error");
			}
		}, 'json');
	});
	
	/*
	 * Deactivate fake upload link
	 */
	$('.fake_upload_link').live('click', function(event){
		event.preventDefault();
	});
	
	/*
	 * Trigger fake upload link hover
	 */
	$("input.upload_file").live("mouseenter", function() {
		var fake_link = $(this).parents('.upload_form_container').first().find('.fake_upload_link');
		$(this).css('cursor', 'pointer');
		$(fake_link).css('text-decoration', 'underline');
	});
	$("input.upload_file").live("mouseleave", function() {
		var fake_link = $(this).parents('.upload_form_container').first().find('.fake_upload_link');
		$(this).css('cursor', 'default');
		$(fake_link).css('text-decoration', 'none');
	});

	/*
	 * Automatic sending after file choosen
	 */
	$("input.upload_file").live("change", function() {
		$(this).parents("form.upload_form").first().submit();
	});
	
	/*
	 * File upload
	 */
	$(".upload_form").live("submit", function(event) {
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
		 * Apply cancel to close button
		 */
		$(this).parents('.upload_form_container').first().find('.close_upload').removeClass('close_upload').addClass('cancel_upload');

		/*
		 * Loading animation
		 */
		var upload_loading = $(this).parents('.upload_form_container').first().find('.loading');
		$(upload_loading).fadeIn('fast');

		/*
		 * Get the upload session id for this upload
		 */
		var upload_session_id = $(form).find("input[name='upload_session_id']").val();

		/*
		 * Check upload progress
		 */
		$(form).everyTime("3s", 'upload_session_status', function() {
			$.post("/backend/file/xhr_read_upload_status", { upload_session_id : upload_session_id }, function(data){
				try {
					if ( data.done == true ) {
						/*
						 * Stop timer
						 */
						$(form).stopTime('upload_session_status');
						/*
						 * Hide loading animation
						 */
						$(upload_loading).fadeOut("slow", function(){
							$(this).remove();
						});
						/*
						 * Remove upload elements and show uploaded file info
						 */
						var container = $(form).parents('.upload_form_container').first();
						/*
						 * Recover close button
						 */
						$(container).find('.cancel_upload').removeClass('cancel_upload').addClass('close_upload');
						/*
						 * Remove fake upload link
						 */
						$(container).find('.fake_upload_link_container').fadeOut('slow', function(){
							$(this).remove();
						});
						/*
						 * Remove upload form
						 */
						$(container).find('.upload_form').fadeOut('slow', function(){
							$(this).remove();
						});
						/*
						 * Show uploaded file
						 */
						$(container).find('.close_upload').before(data.html);
						$(container).find('.uploaded_file_container').fadeIn('fast');
					}
				}
				catch (err) {
					$(form).stopTime('upload_session_status');
					/*
					 * Hide loading animation
					 */
					$(upload_loading).fadeOut("slow");
					//$(image_cancel_item).hide('slow');
				}
			}, "json");
		});

	});
	

	/*
	 * Close/Cancel hover
	 */
	$('.close_upload').live('mouseenter', function(event){
		$(this).addClass('close_upload_hover');
	});
	$('.close_upload').live('mouseleave', function(event){
		$(this).removeClass('close_upload_hover');
	});
	$('.cancel_upload').live('mouseenter', function(event){
		$(this).addClass('cancel_upload_hover');
	});
	$('.cancel_upload').live('mouseleave', function(event){
		$(this).removeClass('cancel_upload_hover');
	});

	/*
	 * Cancel upload
	 */
	$(".cancel_upload").live("click", function(event) {
		event.preventDefault();
		var container = $(this).parents('.upload_form_container').first();
		/*
		 * Stop timer
		 */
		$(container).find("form.upload_form").stopTime('upload_session_status');
		/*
		 * Stop upload
		 */
		$(container).find("iframe").attr('src', '/backend/file/cancel_upload');
		/*
		 * Hide cancel upload and loading animation
		 */
		$(container).find(".loading").fadeOut("slow");
		/*
		 * Recover close button
		 */
		$(this).removeClass('cancel_upload').addClass('close_upload');
	});
	
	/*
	 * Close upload form
	 */
	$('.close_upload').live('click', function(event){
		event.preventDefault();
		$(this).parents('.upload_form_container').first().hide('slow', function(){
			$(this).remove();
		});
	});
	
	/*
	 * Select uploaded file
	 */
	$('a.uploaded_file').live('click', function(event){
		event.preventDefault();
		/*
		 * Show file in listing
		 */
		var title = $(this).attr('title');
		var path = $(this).attr('href');
		$("#file_manager_listing").update_listing(path, title, true);
		
	});

	/*
	 * Erase file/folder
	 */
	$('.current_item_erase').live('click', function(event){
		event.preventDefault();
		
		var details = $(this).parents('#current_file_details');
		var title = $(details).find('p.current_file_title').first().html();
		var mime = $(details).find('span.mime').first().html();
		
		if ( mime == null )
		{
			/*
			 * Directory
			 */
			var question = ' e todo seu conteúdo?';
		}
		else
		{
			/*
			 * File
			 */
			var question = '?';
		}

		if ( confirm("Excluir “" + title + '”' + question) == true )
		{
			var path = $(this).attr('href');
			$.post("/backend/file/xhr_rm", { path : path }, function(data){
				try {
					if ( data.done == true ) {
						/*
						 * Update listing and tree
						 */
						$("#file_manager_listing").update_listing(data.path, data.title, true);
					}
				}
				catch (err) {
					showClientWarning("Communication error");
				}
			}, 'json');
		}
	});

	/*
	 * Rename file/folder
	 */
	$('.current_item_rename').live('click', function(event){
		event.preventDefault();
		
		var details = $(this).parents('#current_file_details');
		var title = $(details).find('p.current_file_title').first().html();

		var path = $(this).attr('href');
		
		var name = prompt('Renomear “' + title + '”', title);
		
		if ( name != '' && name != null )
		{
			$.post("/backend/file/xhr_rename", { path : path, name : name }, function(data){
				try {
					if ( data.done == true ) {
						/*
						 * Update listing and tree
						 */
						$("#file_manager_listing").update_listing(data.path, data.title, true);
					}
				}
				catch (err) {
					showClientWarning("Communication error");
				}
			}, 'json');
		}
	});

	/*
	 * Create a folder
	 */
	$('#current_folder_mkdir').live('click', function(event)
	{
		event.preventDefault();
		
		var path = $(this).attr('href');
		
		var newdir = prompt('Nova Pasta', 'Nova Pasta');
		
		if ( newdir != '' && newdir != null )
		{
			$.post("/backend/file/xhr_mkdir", { path : path, newdir : newdir }, function(data)
			{
				try 
				{
					if ( data.done == true ) 
					{
						/*
						 * Update listing and tree
						 */
						$("#file_manager_listing").update_listing(path + '/' + newdir, newdir, true);
					}
				}
				catch (err) 
				{
					showClientWarning("Communication error");
				}
			}, 'json');
		}

	});

});

/*
 * Read a page's GET URL variables 
 * Code from http://jquery-howto.blogspot.com/2009/09/get-url-parameters-values-with-jquery.html
 */
$.extend({
	getUrlVars: function(){
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	},
	getUrlVar: function(name){
		return $.getUrlVars()[name];
	}
});
