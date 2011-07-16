//<![CDATA[

$(function() {

	$("a.cat_criar_sub").live('click', function(event) {
		event.preventDefault();
	});

	$("a.cat_excluir").live('click', function(event) {
		event.preventDefault();
	});

	// field name focus
	$("input[class^='field_name']").live('focus', function() {
		$(this).attr('class', 'field_name_focus');
	});
	$("input[class^='field_name']").live('blur', function() {
		$(this).attr('class', 'field_name_blur');
	});
	
	/*
	 * Conteúdo Homepage
	 */
	// Alterar áreas homepage
	$("#homepage_update_form").live('submit', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_homepage", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Definições de Homepage salvas");
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
	$("a#cont_criar").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		name = $("#new_cont_field").val();
		type_id = $("#new_cont_type").val();
		$.post("/admin/content/xhr_render_content_form", { name : name, type_id : type_id }, function(data){
			try {
				if ( data.done == true ) {
					$("#cont_new").html(data.form).show();
					// appropriate tinymce
					$("#cont_new").find("textarea").each(function() {
						make_editor($(this));
					});
				}
				else {
					showClientWarning(data.error);
					$("#new_cont_field").focus();
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
		$("#cont_new").hide('slow', function() {
			$("#cont_new").html("");
		});
		// se form de atualização
		$("#content_editor_window").hide('slow', function() {
			$("#content_editor_window").html("");
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
					 * Esconder campos apos salvar
					 */
					$("#cont_new").hide('slow', function() {
						$("#new_cont_field").val("");
						$("#cont_new").html("");
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
	
	// Descartar formulário atualização conteúdo
	$(".form_update_cont").live('reset', function(event) {
		event.preventDefault();
		$("#content_editor_window").fadeOut('fast', function() {
			$("#content_editor_window").html("");
		});
	});

	// Atualizar conteúdo
	$("#button_cont_update").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_update_content", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Conteúdo atualizado com sucesso");
					$("#content_editor_window").hide('slow', function() {
						$("#content_editor_window").html("");
					});
					
					if ( data.reload_section == true ) {
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


	// Alterar nome da categoria
	$(".form_category_name").live('submit', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// Categoria 
		parent = $(this).parents(".category").first();

		// cat id
		cat_id = $(parent).attr("id").slice($(parent).attr("id").lastIndexOf("_") + 1);

		$.post("/admin/content/xhr_rename_category", { cat_id : cat_id, name : $("input[name='category_name_"+cat_id+"']").val() }, function(data){
			try {
				if ( data.done == true ) {
					$("input[name='category_name_"+cat_id+"']").blur();
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");

	});

	// Criar categoria
	$("a[href='#cat_criar']").live('click', function(event) {
		event.preventDefault();
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");
		$.post("/admin/content/xhr_write_category", { name : $("input[name='category_name']").val() }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Categoria criada com sucesso");
					// Apagar form field
					$("input[name='category_name']").val('');
					
					// Clonar categoria modelo
					var NewCat = $("#cat_modelo").clone();
					$(NewCat).attr("id", "cat_"+data.id);
					
					// exclusão
					$(NewCat).find("a.cat_excluir").attr("href", data.id);
					
					// next input name
					$(NewCat).find("input.sub_category_name").attr("id", "sub_cat_create_field_"+data.id);
					
					// criar subcat href 
					$(NewCat).find("a.cat_criar_sub").attr("href", data.id);

					cat_name_field = $(NewCat).find("input[name='category_name_modelo']");
					$(cat_name_field).val(data.name);
					$(cat_name_field).attr("name", "category_name_"+data.id);
					
					// Inserir 
					$("#cat_editor").append(NewCat);
					$(NewCat).show("slow");
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
	 * Criar subcategoria
	 */
	$("a.cat_criar_sub").live('click', function(event) {

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// parent id
		parent_id = $(this).attr("href");
		
		// subcat name
		subcat = $("#sub_cat_create_field_"+parent_id).val();
		
		// Criar subcategoria
		$.post("/admin/content/xhr_write_category", { name : subcat, parent_id : parent_id }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Subcategoria criada com sucesso");
					// Apagar campo na página
					$("#sub_cat_create_field_"+parent_id).val("");
					
					// Clonar categoria modelo
					var NewCat = $("#cat_modelo").clone();
					$(NewCat).attr("id", "cat_"+data.id);
					
					// exclusão
					$(NewCat).find("a.cat_excluir").attr("href", data.id);
					
					// next input name
					$(NewCat).find("input.sub_category_name").attr("id", "sub_cat_create_field_"+data.id);
					
					// criar subcat href 
					$(NewCat).find("a.cat_criar_sub").attr("href", data.id);
					
					// margem esquerda (cat_controls) correspondente ao nível
					var margin = parseInt(data.level) * 32;
					$(NewCat).children(".cat_controls").css("marginLeft", String(margin)+"px");
					
					cat_name_field = $(NewCat).find("input[name='category_name_modelo']");
					$(cat_name_field).val(data.name);
					$(cat_name_field).attr("name", "category_name_"+data.id);
					
					// Inserir após parent
					$("#cat_"+parent_id).after(NewCat);
					$(NewCat).show("slow");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");

	});

	// Remover categoria
	$("a.cat_excluir").live('click', function(event) {

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// cat id
		cat_id = $(this).attr("href");
		
		// Categoria 
		parent = $("#cat_"+cat_id);
		
		// remover
		$.post("/admin/content/xhr_erase_category", { cat_id : cat_id }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Categoria removida com sucesso");
					
					// remover subcategorias
					$(data.children).each(function(index, value) {
						$("#cat_"+value).hide("slow", function() {
							$("#cat_"+value).remove();
						});
					});

					// Remover categoria
					$("#cat_"+cat_id).hide("slow", function() {
						$("#cat_"+cat_id).remove();
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
	 * Administração de menus
	 */

	// Alterar nome do menu
	$(".form_menu_name").live('submit', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// Menu 
		parent = $(this).parents(".menu").first();

		// menu id
		menu_id = $(parent).attr("id").slice($(parent).attr("id").lastIndexOf("_") + 1);

		$.post("/admin/content/xhr_rename_menu", { menu_id : menu_id, name : $("input[name='menu_name_"+menu_id+"']").val() }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Menu renomeado com sucesso");
					$("input[name='menu_name_"+menu_id+"']").blur();
				}
				else {
					showClientWarning("Não foi possível renomear");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");

	});

	// Criar menu
	$("a.menu_criar").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_menu", { name : $("input[name='menu_name']").val() }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Menu criado com sucesso");
					// Apagar form field
					$("input[name='menu_name']").val('');
					
					// Clonar menu modelo
					var NewMenu = $("#menu_modelo").clone();
					$(NewMenu).attr("id", "menu_"+data.id);
					
					// exclusão
					$(NewMenu).find("a.menu_excluir").attr("href", data.id);
					
					// submenu input name label
					$(NewMenu).find(".sub_menu_label").attr("for", "sub_menu_create_field_"+data.id);
					// submenu input name
					$(NewMenu).find("input.sub_menu_name").attr("id", "sub_menu_create_field_"+data.id);
					// criar submenu href 
					$(NewMenu).find("a.menu_criar_sub").attr("href", data.id);

					// target input name label
					$(NewMenu).find(".target_menu_label").attr("for", "target_menu_create_field_"+data.id);
					// submenu input name
					$(NewMenu).find("input.target_menu_name").attr("id", "target_menu_create_field_"+data.id);
					// criar submenu href 
					$(NewMenu).find("a.menu_criar_target").attr("href", data.id);

					menu_name_field = $(NewMenu).find("input[name='menu_name_modelo']");
					$(menu_name_field).val(data.name);
					$(menu_name_field).attr("name", "menu_name_"+data.id);
					
					// Inserir 
					$("#menu_editor").append(NewMenu);
					$(NewMenu).show("slow");
					
					$(NewMenu).after("<div class=\"menu_drop\"></div>");
					
					init_menu_dragdrop();
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
	 * Criar submenu
	 */
	$("a.menu_criar_sub").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// parent id
		parent_id = $(this).attr("href");
		
		// submenu name
		submenu = $("#sub_menu_create_field_"+parent_id).val();
		
		// Criar submenu
		$.post("/admin/content/xhr_write_menu", { name : submenu, parent_id : parent_id }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Menu criado com sucesso");
					
					// Apagar campo na página
					$("#sub_menu_create_field_"+parent_id).val("");
					
					// Clonar menu modelo
					var NewMenu = $("#menu_modelo").clone();
					$(NewMenu).attr("id", "menu_"+data.id);
					
					// exclusão
					$(NewMenu).find("a.menu_excluir").attr("href", data.id);
					
					// next input name
					$(NewMenu).find("input.sub_menu_name").attr("id", "sub_menu_create_field_"+data.id);
					
					// criar submenu href 
					$(NewMenu).find("a.menu_criar_sub").attr("href", data.id);
					
					// menu target  
					$(NewMenu).find("a.menu_criar_target").attr("href", data.id);
					$(NewMenu).find("select#target_menu_create_field_modelo").attr("id", "target_menu_create_field_"+data.id);

					// margem esquerda (menu_controls) correspondente ao nível
					var margin = parseInt(data.level) * 32;
					$(NewMenu).children(".menu_controls").css("marginLeft", String(margin)+"px");
					
					menu_name_field = $(NewMenu).find("input[name='menu_name_modelo']");
					$(menu_name_field).val(data.name);
					$(menu_name_field).attr("name", "menu_name_"+data.id);
					
					// Inserir após parent
					$("#menu_"+parent_id).next(".menu_drop").after(NewMenu);
					$(NewMenu).show("slow");
					$(NewMenu).after("<div class=\"menu_drop\"></div>");
					
					init_menu_dragdrop();
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");

	});

	// Remover menu
	$("a.menu_excluir").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// menu id
		menu_id = $(this).attr("href");
		
		// Menu 
		parent = $("#menu_"+menu_id);
		
		// remover
		$.post("/admin/content/xhr_erase_menu", { menu_id : menu_id }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Menu removido");
					
					// remover subcategorias
					$(data.children).each(function(index, value) {
						$("#menu_"+value).next(".menu_drop").remove();
						$("#menu_"+value).hide("slow", function() {
							$("#menu_"+value).remove();
						});
					});

					// Remover categoria
					$("#menu_"+menu_id).next(".menu_drop").remove();
					$("#menu_"+menu_id).hide("slow", function() {
						$("#menu_"+menu_id).remove();
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
	 * Alternar entre submenu e alvo
	 */
	$(".rotate_submenu_controls_form").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".submenu_controls").first().find(".submenu_controls_form").first();
		$(parent).children("div:visible").fadeOut("fast", "easeInSine", function() {
			$(parent).children("div:hidden").fadeIn("fast", "easeInSine", function() {
			});
			$(this).css("display", "none");
		});
	});

	/*
	 * Definir target
	 */
	$("a.menu_criar_target").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		// menu id
		menu_id = $(this).attr("href");
		
		// target
		target = $("#target_menu_create_field_"+menu_id).val();
		
		// Criar submenu
		$.post("/admin/content/xhr_write_menu_target", { menu_id : menu_id, target : target }, function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Destino do menu definido com sucesso");
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");

	});

});


/*
 * Configurar drag'n'drop menus
 */
function init_menu_dragdrop() {
	$("div.drag_handle").mouseup(function() {
		$(this).css('cursor', 'url("/img/openhand.cur"), auto');
	}).mousedown(function() {
		$(this).css('cursor', 'move');
	});
	$("div.menu_drop").droppable({
		over: function(event, ui) { 
			$(this).addClass("menu_drop_highlight") 
		},
		out: function(event, ui) { 
			$(this).removeClass("menu_drop_highlight") 
		},
		drop: function(event, ui) {
			draggable_menu_drop = $(ui.draggable).next("div.menu_drop");
			$(ui.draggable).detach();
			$(this).after($(ui.draggable));
			$(draggable_menu_drop).detach();
			$(ui.draggable).after($(draggable_menu_drop));
			$(ui.draggable).css("top", "0px");
			//$(ui.draggable).animate({ top: "0px" }, "slow", "easeOutSine" );	
			//$(ui.draggable).css("left", "0px");
			$(ui.draggable).animate({ left: "0px" }, "slow", "easeOutSine" );
			$(this).removeClass("menu_drop_highlight");
			
			init_menu_dragdrop();
		}
	});
	$("div.menu").draggable({ 
		revert: 'invalid' ,
		handle: '.drag_handle',
		stack: "div.menu",
		zIndex: 2000, 
		opacity: 0.5,
		scrollSpeed: 4,
		start: function(event, ui) { 
			$(this).find("div.drag_handle").css('cursor', 'move');
		},
		stop : function (event, ui) {
			update_menu_order();
		}
	});
}

/*
 * atualizar ordem dos menus
 */
function update_menu_order() {
	var menus = new Array();

	// Bloqueio
	$("#sections_blocker").fadeIn("fast");
	
	$(".menu:visible").each(function(index) {
		// menu id
		menu_id = $(this).attr("id").slice($(this).attr("id").lastIndexOf("_") + 1);
		menus[index] = menu_id ;
	});
	$.post("/admin/content/xhr_write_menu_order", { menus : menus }, function(data) {
		try {
			if ( data.done == true ) {
				showClientWarning("Menus atualizados");
			}
		}
		catch (err) {
			showClientWarning("Erro de comunicação com o servidor");
		}
		// Bloqueio
		$("#sections_blocker").fadeOut("fast");
	}, "json");
}


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
