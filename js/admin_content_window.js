//<![CDATA[

$(function() {

	// Unfocus proper elements
	$("body").click(function() {
		// dropdown widget
		var widget = $(".dropdown_items_listing").parents(".dropdown_items_listing_inline").first();
		$(".dropdown_items_listing:visible").fadeOut("fast");
		$(widget).find("a.down").addClass("up").removeClass("down");
		// Discard labels being edited
	});
	
	// Dropdown widget
	$(".dropdown_items_listing_inline > a").live('click', function(event) {
		event.preventDefault();
		var listing = $(this).parent().find(".dropdown_items_listing").first();
		if ( ! $(listing).is(":visible") ) {
			$(listing).fadeIn("fast");
			$(this).addClass("down");
			$(this).removeClass("up");
		}
		else {
			$(listing).fadeOut("fast");
			$(this).addClass("up");
			$(this).removeClass("down");
		}
	});

	// Salvar meta fields
	$("#button_meta_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_meta", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Campos armazenados com sucesso");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#blocker").fadeOut("fast");
		}, "json");
	});

	// Selecao do tipo do elemento
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

		$.post("/admin/content/xhr_render_element_form", { parent_id : parent_id, type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					// Close type editor (if visible)
					$("#type_define_new_container:visible").fadeOut("slow");
					$("#content_editors_container").replaceWith(data.html).show(function(){
						// CKEditor activation
						ckeditor();
					});
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#blocker").fadeOut("fast");
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
		 * Update CKEditor contents
		 */
		$.each(CKEDITOR.instances, function() {
			this.updateElement();
		});

		/*
		 * Update menu field json data
		 */
		$(".menu_field").each(function() {
			var menu = prepare_menu_field(this);
			$(this).find('input.menu_actual_field').val($.toJSON(menu));
			//console.log($.toJSON(menu));
		});

		$.post("/admin/content/xhr_write_element", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Elemento salvo com sucesso");
					/*
					 * Reload Tree
					 */
					$.post("/admin/content/xhr_render_tree_unfold", { request : 'element', id : data.element_id }, function(data) {
						$("#tree_listing_1").html(data.html);
					}, "json");
					/*
					 * Reload editor window
					 */
					$.post("/admin/content/xhr_render_element_form", { id : data.element_id }, function(data){
						try {
							if ( data.done == true ) {
								$("#content_window").html(data.html).show(function() {
									// CKEditor activation
									ckeditor();

									// Bloqueio
									$("#blocker").fadeOut("fast");
								});
							}
							else {
								// Bloqueio
								$("#blocker").fadeOut("fast");
								showClientWarning("Erro ao salvar");
							}
						}
						catch (err) {
							// Bloqueio
							$("#blocker").fadeOut("fast");
							showClientWarning("Erro de comunicação com o servidor");
						}
					}, "json");
				}
				else {
					// Bloqueio
					$("#blocker").fadeOut("fast");
					showClientWarning(data.error);
				}
			}
			catch (err) {
				// Bloqueio
				$("#blocker").fadeOut("fast");
				showClientWarning("Erro de comunicação com o servidor");
			}
		}, "json");
	});

	// Selecao do tipo do conteúdo
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

		$.post("/admin/content/xhr_render_content_form", { parent_id : parent_id, type_id : type_id, editor : 'content' }, function(data){
			try {
				if ( data.done == true ) {
					// Close type editor (if visible)
					$("#type_define_new_container:visible").fadeOut("slow");
					$("#content_editors_container").replaceWith(data.html).show(function(){
						// CKEditor activation
						ckeditor();
					});
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#blocker").fadeOut("fast");
		}, "json");
	});

	// Descartar formulário novo conteúdo
	$("#button_content_discard").live('click', function(event) {
		event.preventDefault();
		$("#content_editor_form").hide('slow', function() {
			$("#content_editor_form").html("");
		});
	});
	
	// Salvar novo conteúdo
	$("#button_content_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		/*
		 * Update CKEditor contents
		 */
		$.each(CKEDITOR.instances, function() {
			this.updateElement();
		});

		/*
		 * Update menu field json data
		 */
		$(".menu_field").each(function() {
			var menu = prepare_menu_field(this);
			$(this).find('input.menu_actual_field').val($.toJSON(menu));
			console.log($.toJSON(menu));
		});

		$.post("/admin/content/xhr_write_content", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Conteúdo salvo com sucesso");
					/*
					 * Reload Tree & editor window if not home
					 */
					if ( data.content_id != 1 ) {
						$.post("/admin/content/xhr_render_tree_unfold", { request : 'content', id : data.content_id }, function(data) {
							$("#tree_listing_1").html(data.html);
						}, "json");

						$.post("/admin/content/xhr_render_content_form", { id : data.content_id, editor : 'content' }, function(data){
							try {
								if ( data.done == true ) {
									$("#content_window").html(data.html).show(function() {
										// CKEditor activation
										ckeditor();
										// Bloqueio
										$("#blocker").fadeOut("fast");
									});
								}
								else {
									// Bloqueio
									$("#blocker").fadeOut("fast");
									showClientWarning("Erro ao salvar");
								}
							}
							catch (err) {
								// Bloqueio
								$("#blocker").fadeOut("fast");
								showClientWarning("Erro de comunicação com o servidor");
							}
						}, "json");
					}
				}
				else {
					// Bloqueio
					$("#blocker").fadeOut("fast");
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
				// Bloqueio
				$("#blocker").fadeOut("fast");
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

		$.post("/admin/content/xhr_render_content_type_form", function(data){
			try {
				if ( data.done == true ) {
					$("#type_define_new_container").html(data.html).show("slow");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#blocker").fadeOut("fast");
		}, "json");
	});

	/* 
	 * Carregar formulário new element_type
	 */
	$("a#element_type_create").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		$.post("/admin/content/xhr_render_element_type_form", function(data){
			try {
				if ( data.done == true ) {
					$("#type_define_new_container").html(data.html).show("slow");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#blocker").fadeOut("fast");
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

		$.post("/admin/content/xhr_write_content_type", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Tipo salvo com sucesso");
					$("#type_define_new_container").hide('slow', function() {

						// Reload types dropdown widget with new value
						var id = $("#choose_content_type_for_parent_id").attr('href');
				
						$.post("/admin/content/xhr_render_content_new", { id : id, type_id : data.type_id }, function(data){
							try {
								if ( data.done == true ) {
									$("#content_window").html(data.html).show();
								}
							}
							catch (err) {
								showClientWarning("Erro de comunicação com o servidor");
							}
				
							// Bloqueio
							$("#blocker").fadeOut("fast");
						}, "json");

					});
				}
				else {
					showClientWarning(data.error);
					// Bloqueio
					$("#blocker").fadeOut("fast");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
				// Bloqueio
				$("#blocker").fadeOut("fast");
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
		$.post("/admin/content/xhr_write_element_type", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Tipo salvo com sucesso");
					$("#type_define_new_container").hide('slow', function() {
						$("#type_define_new_container").html("");
					});
					
					// Reload types dropdown widget with new value
					var id = $("#choose_element_type_for_parent_id").attr("href");
			
					$.post("/admin/content/xhr_render_element_new", { id : id, type_id : data.type_id }, function(data){
						try {
							if ( data.done == true ) {
								$("#content_window").html(data.html).show();
							}
						}
						catch (err) {
							showClientWarning("Erro de comunicação com o servidor");
						}
						// Bloqueio
						$("#blocker").fadeOut("fast");
					}, "json");

				}
				else {
					showClientWarning(data.error);
					// Bloqueio
					$("#blocker").fadeOut("fast");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
				// Bloqueio
				$("#blocker").fadeOut("fast");
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
	$("a.content_editors_menu_item").live('click', function(event) {
		event.preventDefault();
		var target = $(this).attr('href');
		
		$("a.content_editors_menu_item[href!='"+target+"']").removeClass("current");
		$(this).addClass("current");
		
		$("div.editor_form[id!='"+target+"']").hide();
		$("div.editor_form[id='"+target+"']").show();
	});
	
	/*
	 * Template save
	 */
	$("form.template_form").live('submit', function(event) {
		event.preventDefault();
		// Bloqueio
		$("#blocker").fadeIn("fast");
		
		/*
		 * Requires confirmation if default
		 * template is about to be overwritten
		 */
		var overwrite = true;
		var sole = $(this).find('input[name="template_sole"]').first();
		if ( ! $(sole).attr('checked') && $(sole).length > 0 ) {
			overwrite = confirm("Sobrescrever template padrão?");
		}
		
		/*
		 * Template textarea
		 */
		var template_textarea = $(this).find('.template_textarea');

		$.post("/admin/content/xhr_write_template", $(this).serialize() + '&overwrite=' + overwrite, function(data){
			try {
				if ( data.done == true ) {
					$(template_textarea).val(data.template);
					showClientWarning("Template salvo com sucesso");
				}
				else {
					showClientWarning(data.error);
					// Bloqueio
					$("#blocker").fadeOut("fast");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
				// Bloqueio
				$("#blocker").fadeOut("fast");
			}
			// Bloqueio
			$("#blocker").fadeOut("fast");
		}, "json");
	});

});


//]]>
