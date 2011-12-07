//<![CDATA[

$(function() {

	// Unfocus proper elements
	$("body").click(function() {
		// dropdown widget
		var widget = $(".dropdown_items_listing").parents(".dropdown_items_listing_inline").first();
		$(".dropdown_items_listing:visible").fadeOut("fast");
	});
	
	// Dropdown widget
	$(".dropdown_items_listing_inline > a").live('click', function(event) {
		event.preventDefault();
		var listing = $(this).parent().find(".dropdown_items_listing").first();
		if ( ! $(listing).is(":visible") ) {
			$(listing).fadeIn("fast");
		}
		else {
			$(listing).fadeOut("fast");
		}
	});

	// Salvar meta fields
	$("#button_meta_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/backend/content/xhr_write_meta", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
			}
			else {
				showClientWarning(data.message);
			}

			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Seleção do tipo do elemento
	
	$(".dropdown_items_listing_element_type_target").live('click', function(event) {
		event.preventDefault();
		
		var type_id = $(this).attr("href");
		var type_name = $(this).html();
		
		if ( type_id != "0" ) {
			$(this).parents(".dropdown_items_listing_inline").children("a:first").attr("href", type_id);
			$(this).parents(".dropdown_items_listing_inline").children("a:first").html(type_name);
		}
	});

	// Criar formulário de novo elemento em conteúdo
	$("a#choose_element_type_for_parent_id").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var parent_id = $(this).attr("href");
		var type_id = $(this).parents("div:first").find(".dropdown_items_listing_inline").find("a:first").attr("href");

		if ( type_id == "0" ) {
			// No element type, create a new one
			$("a#element_type_create").trigger('click');
			return null;
		}

		$.post("/backend/content/xhr_render_element_form", { parent_id : parent_id, type_id : type_id }, function(data){
			if ( data.done == true ) {
				// Close type editor (if visible)
				$("#type_define_new_container:visible").fadeOut("slow");
				$("#editors_container").replaceWith(data.html).show(function(){
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').wysiwyg();
				});
			}
			else {
				showClientWarning(data.message);
			}
			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Descartar formulário novo elemento
	$("#button_element_discard").live('click', function(event) {
		event.preventDefault();
		$("#element_editor_form").hide('slow', function() {
			$("#element_editor_form").html("");
		});
	});

	// Salvar novo elemento
	$("#button_element_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/content/xhr_write_element", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/content/xhr_render_tree_unfold", { request : 'element', id : data.element_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/content/xhr_render_element_form", { id : data.element_id }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#content_editor_form').find('textarea').wysiwyg();

							showClientWarning(message);

							// Bloqueio
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});

	// Seleção do tipo do conteúdo
	$(".dropdown_items_listing_content_type_target").live('click', function(event) {
		event.preventDefault();
		
		var type_id = $(this).attr("href");
		var type_name = $(this).html();
		
		if ( type_id != "0" ) {
			$(this).parents(".dropdown_items_listing_inline").children("a:first").attr("href", type_id);
			$(this).parents(".dropdown_items_listing_inline").children("a:first").html(type_name);
		}
	});

	// Criar conteúdo
	$("a#choose_content_type_for_parent_id").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var parent_id = $(this).attr("href");
		var type_id = $(this).parents("div:first").find(".dropdown_items_listing_inline").find("a:first").attr("href");
		
		if ( type_id == "0" ) {
			// No element type, create a new one
			$("a#content_type_create").trigger('click');
			return null;
		}

		$.post("/backend/content/xhr_render_content_form", { parent_id : parent_id, type_id : type_id, editor : 'content' }, function(data){
			if ( data.done == true ) {
				// Close type editor (if visible)
				$("#type_define_new_container:visible").fadeOut("slow");
				$("#editors_container").replaceWith(data.html).show(function(){
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').wysiwyg();
				});
			}
			else {
				showClientWarning(data.message);
			}
			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Descartar formulário novo conteúdo
	$("#button_content_discard").live('click', function(event) {
		event.preventDefault();
		$("#content_editor_form").hide('slow', function() {
			$("#content_editor_form").html("");
		});
	});

	/*
	 * Discard file in a file field	
	 */
	$(".file_erase").live("click", function(event) {
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

	// Salvar conteúdo
	$("#button_content_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();

		$.post("/backend/content/xhr_write_content", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree & editor window if not home
				 */
				if ( data.content_id != 1 ) {
					$.post("/backend/content/xhr_render_tree_unfold", { request : 'content', id : data.content_id }, function(data) {
						$("#tree_listing_1").html(data.html);
					}, "json");
				}
				$.post("/backend/content/xhr_render_content_form", { id : data.content_id, editor : 'content' }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#content_editor_form').find('textarea').wysiwyg();

							showClientWarning(message);

							// Bloqueio
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});
	
	/*
	 * Show content's available pseudo variables
	 */
	$('.pseudo_variables_menu_switcher').live('click', function(event){
		event.preventDefault();
		var accordion = $(this).parents('.pseudo_variables_menu').first().find('.pseudo_variables_accordion').first();
		if ( $(this).hasClass('collapsed') ) {
			$(accordion).slideDown('fast', 'easeOutSine');
			$(this).removeClass('collapsed');
			$(this).addClass('expanded');
		}
		else {
			$(accordion).slideUp('fast', 'easeInSine');
			$(this).removeClass('expanded');
			$(this).addClass('collapsed');
		}
	});
	
	/*
	 * Add pseudo variable to template
	 */
	$('.add_variable_single').live('click', function(event) {
		event.preventDefault();
		var variable = unescape($(this).attr('href'));
		var textarea = $(this).parents('.form_window_column_input').first().find('.template_textarea');
		$(textarea).insertAtCursor(variable);
	});
	
	/*
	 * Add pseudo variable pair (loop) to template
	 */
	$('.add_variable_pair').live('click', function(event) {
		event.preventDefault();
		var variable = unescape($(this).attr('href'));
		var textarea = $(this).parents('.form_window_column_input').first().find('.template_textarea');
		$(textarea).insertAtCursor(variable);
	});
	
	/*
	 * insertAtCursor: jQuery extended function to 
	 * insert text at cursor on input
	 */
	$.fn.extend({
		insertAtCursor: function (value) {
			/*
			 * Based on code found in
			 * http://alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript
			 */
			// IE support
			if (document.selection) {
				$(this)[0].focus();
				sel = document.selection.createRange();
				sel.text = value;
			}
			// Other browsers
			else if ($(this)[0].selectionStart || $(this)[0].selectionStart == '0') {
				var startPos = $(this)[0].selectionStart;
				var endPos = $(this)[0].selectionEnd;
				$(this)[0].value = $(this)[0].value.substring(0, startPos)
				+ value
				+ $(this)[0].value.substring(endPos, $(this)[0].value.length);
			} 
			else {
				$(this)[0].value += value;
			}

			var CaretPos = $(this)[0].value.substring(0, startPos).length + value.length;	

			if($(this)[0].setSelectionRange)
			{
				$(this)[0].focus();
				$(this)[0].setSelectionRange(CaretPos,CaretPos);
			}
			else if ($(this)[0].createTextRange) {
				var range = $(this)[0].createTextRange();
				range.collapse(true);
				range.moveEnd('character', CaretPos);
				range.moveStart('character', CaretPos);
				range.select();
			}
		}
	});
	
	/* 
	 * Carregar formulário new content_type
	 */
	$("a#content_type_create").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/backend/content/xhr_render_content_type_form", function(data){
			if ( data.done == true ) {
				$("#type_define_new_container").html(data.html).show("slow");
			}
			else {
				showClientWarning(data.message);
			}
			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/* 
	 * Carregar formulário new element_type
	 */
	$("a#element_type_create").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/backend/content/xhr_render_element_type_form", function(data){
			if ( data.done == true ) {
				$("#type_define_new_container").html(data.html).show("slow");
			}
			else {
				showClientWarning(data.message);
			}
			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Descartar formulário novo tipo de conteúdo
	$(".type_define_new_form").live('reset', function(event) {
		event.preventDefault();
		$("#type_define_new_container").hide('slow', function() {
			$("#type_define_new_container").html("");
		});
	});
	
	/*
	 * Criar content_type
	 */
	$("#content_type_define_new_form").live('submit', function(event) {
		event.preventDefault();
		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/backend/content/xhr_write_content_type", $(this).serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
				$("#type_define_new_container").hide('slow', function() {

					// Reload types dropdown widget with new value
					var id = $("#choose_content_type_for_parent_id").attr('href');
			
					$.post("/backend/content/xhr_render_content_new", { id : id, type_id : data.type_id }, function(data){
						if ( data.done == true ) {
							$("#content_window").html(data.html).show();
						}
			
						// Bloqueio
						$("#blocker").stop().fadeOut("fast");
					}, "json");

				});
			}
			else {
				showClientWarning(data.message);
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
			}
		}, "json");
	});

	/*
	 * Criar element_type
	 */
	$("#element_type_define_new_form").live('submit', function(event) {
		event.preventDefault();
		// Bloqueio
		$("#blocker").fadeIn("fast");
		$.post("/backend/content/xhr_write_element_type", $(this).serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
				$("#type_define_new_container").hide('slow', function() {
					$("#type_define_new_container").html("");
				});
				
				// Reload types dropdown widget with new value
				var id = $("#choose_element_type_for_parent_id").attr("href");
		
				$.post("/backend/content/xhr_render_element_new", { id : id, type_id : data.type_id }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show();
					}
					// Bloqueio
					$("#blocker").stop().fadeOut("fast");
				}, "json");

			}
			else {
				showClientWarning(data.message);
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
			}
		}, "json");
	});

	/*
	 * Clonar e Redefinir nomes dos campos clonados (new content type field)
	 */
	$("a#add_type_field").live('click', function(event) {
		event.preventDefault();
		var NewField = $("#type_define_new_field_0").clone();
		var field_label = $(NewField).find("label[for='field_0']");
		var field = $(NewField).find("#field_0");
		var field_type_label = $(NewField).find("label[for='field_type_0']");
		var field_type = $(NewField).find("#field_type_0");
		
		// Redefinir
		var id = $("#type_define_new_container").find(".type_define_new_field").length;
		$(NewField).attr("id", "type_define_new_field_"+id);
		$(field_label).attr("for", "field_"+id);
		$(field).attr("id", "field_"+id);
		$(field).attr("name", "field_"+id);
		$(field).attr("value", "");
		$(field_type_label).attr("for", "field_type_"+id);
		$(field_type).attr("id", "field_type_"+id);
		$(field_type).attr("name", "field_type_"+id);
		$(NewField).css("display", "none");
		
		// Inserir
		$(this).before(NewField);
		$(NewField).show("slow");

		// field count
		var count = $("#type_define_new_container").find(".type_define_new_field").length;
		$("#type_define_new_container").find("input[name='field_count']").val(count);
		
	});
	
	/*
	 * Content/template editor tabs
	 */
	$("a.editors_menu_item").live('click', function(event) {
		event.preventDefault();
		var target = $(this).attr('href');
		
		$("a.editors_menu_item[href!='"+target+"']").removeClass("current");
		$(this).addClass("current");
		
		$("div.editor_form[id!='"+target+"']").hide();
		$("div.editor_form[id='"+target+"']").show();
	});
	
	/*
	 * Template save
	 */
	$("form.template_form").live('submit', function(event) {
		event.preventDefault();
		
		/*
		 * Requires confirmation if default
		 * template is about to be overwritten
		 */
		var overwrite = true;
		var sole = $(this).find('input[name="template_sole"]').first();
		if ( ! $(sole).attr('checked') && $(sole).length > 0 ) {
			overwrite = confirm($('label.template_confirm_overwrite').html());
		}

		if ( overwrite == true ) {
			// Bloqueio
			$("#blocker").fadeIn("fast");

			/*
			 * Template textarea
			 */
			var template_textarea = $(this).find('.template_textarea');
	
			$.post("/backend/content/xhr_write_template", $(this).serialize() + '&overwrite=' + overwrite, function(data){
				if ( data.done == true ) {
					//$(template_textarea).val(data.template);
					showClientWarning(data.message);
				}
				else {
					showClientWarning(data.message);
				}
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
			}, "json");
		}
	});
	
	/*
	 * Alternate betwin language inputs
	 */
	$('.input_lang_tab_link').live('click', function(event){
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
	
	/*
	 * Open file manager
	 */
	$('.browse_file').live('click', function(event){
		event.preventDefault();
		/*
		 * Identifies receptor input
		 */
		var identifier = $(this).attr('href');
		/*
		 * Pass caller data to file manager 
		 */
		window.open('/backend/file/manager?parent=direct&identifier=' + identifier, '_blank', 'height=480, width=880');
	});
	
});

/*
 * Serialize composite fields for saving
 */
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


//]]>
