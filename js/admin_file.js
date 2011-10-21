//<![CDATA[
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
				$(listing).children(".loading").fadeIn("fast");
	
				$.post('/admin/file/xhr_render_contents', { path : path }, function(data) {
					try {
						if ( data.done == true ) {
							/*
							 * File listing
							 */
							$(listing).html(data.html);
							/*
							 * Update directories tree?
							 */
							if ( update_tree ) {
								$.post('/admin/file/xhr_render_tree_unfold', { path : path }, function(data) {
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
							$('#current_folder_title').html(title);
							if ( path == '/' )
							{
								$('#current_folder_rename').parent('li').hide('fast');
								$('#current_folder_erase').parent('li').hide('fast');
							}
							else
							{
								$('#current_folder_rename').parent('li').show('fast');
								$('#current_folder_erase').parent('li').show('fast');
							}
							$('#current_folder_rename').attr('href', path);
							$('#current_folder_erase').attr('href', path);
							$('#current_folder_upload').attr('href', path);
							/*
							 * Hide previous file details
							 */
							$('#current_file_details').hide('fast', function(){
								$(this).html('');
							});
						}
					}
					catch (err) {
						showClientWarning("Erro de comunicação com o servidor");
					}
					// File listing loading animation
					$(listing).children(".loading").stop().fadeOut("fast");
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
		var contents = $(this).next('.action_insert').html();
		FileManagerDialog.insert(contents);
	});

	/*
	 * Show existing contents listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();

		var path = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/admin/file/xhr_render_tree", { path : path }, function(data){
			try {
				if ( data.done == true ) {
					$(listing).html(data.html);
					$(listing).slideDown("fast", "easeInSine");
					$(bullet).addClass("unfold");
					$(bullet).removeClass("fold");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
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
		$.post("/admin/file/xhr_render_upload_form", { path : path }, function(data){
			try {
				if ( data.done == true ) {
					$('#current_folder_details').after(data.html);
					$('#upload_form_container_' + data.upload_session_id).show('fast');
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
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
			$.post("/admin/file/xhr_read_upload_status", { upload_session_id : upload_session_id }, function(data){
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
		$(container).find("iframe").attr('src', '/admin/file/cancel_upload');
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
		 * Show folder contents
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
		
		var path = $(this).attr('href');
		$.post("/admin/file/xhr_erase_item", { path : path }, function(data){
			try {
				if ( data.done == true ) {
					console.log('Apagado');
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
		}, 'json');

	});

});
//]]>
