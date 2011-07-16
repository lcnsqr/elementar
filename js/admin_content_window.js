//<![CDATA[

$(function() {

	// Salvar meta fields
	$("#button_meta_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

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
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});

	// Salvar nova categoria
	$("#button_category_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_category", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Categoria salva com sucesso");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});

	// Criar formulário de novo elemento em categoria
	$("a#choose_category_element_type").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var parent = "category";
		var parent_id = $(this).attr("href");
		var type_id = $("#element_type").val();
		
		$.post("/admin/content/xhr_render_element_form", { parent : parent, parent_id : parent_id, type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					$("#element_editor_form").html(data.form).show();
					// appropriate tinymce
					$("#element_editor_form").find("textarea").each(function() {
						make_editor($(this));
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
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});

	// Criar formulário de novo elemento em conteúdo
	$("a#choose_content_element_type").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var parent = "content";
		var parent_id = $(this).attr("href");
		var type_id = $("#element_type").val();
		
		$.post("/admin/content/xhr_render_element_form", { parent : parent, parent_id : parent_id, type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					$("#element_editor_form").html(data.form).show();
					// appropriate tinymce
					$("#element_editor_form").find("textarea").each(function() {
						make_editor($(this));
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
			$("#sections_blocker").fadeOut("fast");
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
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_element", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Elemento salvo com sucesso");
					/*
					 * Reload Tree
					 */
					$.post("/admin/content/xhr_render_tree_unfold", { request : 'element', id : data.element_id }, function(data) {
						$("#tree_listing_0").html(data.html);
					}, "json");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});

	// Criar conteúdo
	$("a#choose_content_type").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var category_id = $(this).attr("href");
		var type_id = $("#content_type").val();
		
		$.post("/admin/content/xhr_render_content_form", { category_id : category_id, type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_form").html(data.form).show();
					// appropriate tinymce
					$("#content_editor_form").find("textarea").each(function() {
						make_editor($(this));
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
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});

	// Descartar formulário novo conteúdo
	$("#button_cont_discard").live('click', function(event) {
		event.preventDefault();
		$("#content_editor_form").hide('slow', function() {
			$("#content_editor_form").html("");
		});
	});
	
	// Salvar novo conteúdo
	$("#button_cont_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_content", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Conteúdo salvo com sucesso");
					/*
					 * Reload Tree
					 */
					$.post("/admin/content/xhr_render_tree_unfold", { request : 'content', id : data.content_id }, function(data) {
						$("#tree_listing_0").html(data.html);
					}, "json");
				}
				else {
					showClientWarning(data.error);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");
	});


	// Remover conteúdo
	$("a.cont_excluir").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// content id
		cont_id = $(this).attr("href");
		
		// remover
		$.post("/admin/content/xhr_erase_content", { id : cont_id }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Item removido");
					// Recarregar seção
					$.post("/admin/content/xhr_render_section", { section : "edicao" }, function(reload){
						try {
							if ( reload.done == true ) {
								// esconder, atualizar e exibir seção
								divid = "div_edicao";
								$('div#'+divid).fadeOut("fast", function() {
									$('div#'+divid).html(reload.html);
									$('div#'+divid).fadeIn("slow");
								});
								$("#elapsed_time").html(reload.elapsed_time);
							}
						}
						catch (err) {
							showClientWarning("Erro de comunicação com o servidor");
						}
					}, "json");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

		// Bloqueio
		$("#sections_blocker").fadeOut("fast");

		}, "json");

	});
	
	/*
	 * Exibir tags do tipo
	 */
	$("#cont_type").live('change', function(event) {
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		type_id = $(this).val();
		$.post("/admin/content/xhr_render_content_type_tags", { type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					$('div#type_define').fadeOut("fast", function() {
						$('div#type_define').html(data.html);
						$('div#type_define').fadeIn("slow");
					});
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");
		
	});

	/* 
	 * Carregar formulário new content_type
	 */
	$("a#type_create").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		name = $("#new_type_field").val();
		$.post("/admin/content/xhr_render_content_type_form", { name : name }, function(data){
			try {
				if ( data.done == true ) {
					$("#type_define_new_container").html(data.html).show("slow");
				}
				else {
					showClientWarning(data.error);
					$("#new_type_field").focus();
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
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
	$("#type_define_new_form").live('submit', function(event) {
		event.preventDefault();
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");
		$.post("/admin/content/xhr_write_content_type", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Tipo salvo com sucesso");
					$("#type_define_new_container").hide('slow', function() {
						$("#type_define_new_container").html("");
					});
					
					// Recarregar seção
					$.post("/admin/content/xhr_render_section", { section : "tipo" }, function(reload){
						try {
							// esconder, atualizar e exibir seção
							divid = "div_tipos";
							$('div#'+divid).fadeOut("fast", function() {
								$('div#'+divid).html(reload.html);
								$('div#'+divid).fadeIn("slow");
							});
							$("#elapsed_time").html(reload.elapsed_time);
						}
						catch (err) {
							showClientWarning("Erro de comunicação com o servidor");
						}
					}, "json");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
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


});

function make_editor(textarea) {
	// .p	
	if ( $(textarea).hasClass("p") ) {
		$(textarea).tinymce({
			// Location of TinyMCE script
			script_url : '/js/tinymce/jscripts/tiny_mce/tiny_mce.js',
			// General options
			relative_urls : false,
			width : "100%",
			height : "80",
			language : "pt",
			theme : "advanced",
			theme_advanced_toolbar_location : "top",
			plugins : "xhtmlxtras",
			theme_advanced_buttons1 : "link,unlink,|,bold,italic,cite,ins,del,abbr,acronym,|,sub,sup,|,charmap,|,undo,redo,code,cleanup",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			// formatting
			force_br_newlines : true,
			force_p_newlines : false,
			forced_root_block : ''
		});
	}

	// .hypertext
	if ( $(textarea).hasClass("hypertext") ) {
		$(textarea).tinymce({
			// Location of TinyMCE script
			script_url : '/js/tinymce/jscripts/tiny_mce/tiny_mce.js',
			// General options
			relative_urls : false,
			width : "100%",
			height : "200",
			language : "pt",
			theme : "advanced",
			theme_advanced_toolbar_location : "top",
			theme_advanced_blockformats : "h1,h2,h3",
			plugins : "xhtmlxtras",
			theme_advanced_buttons1 : "formatselect,|,image,|,link,unlink,|,bold,italic,cite,ins,del,abbr,acronym,|,bullist,numlist,|,sub,sup,|,charmap,|,undo,redo,code,cleanup",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			// formatting
			force_br_newlines : false,
			force_p_newlines : true,
			forced_root_block : '',
			external_image_list_url : "/js/admin_conteudo_imagelist.js"
		});
	}
}

//]]>
