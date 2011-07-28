//<![CDATA[

$(function() {

	/*
	 * Administração de menus
	 */

	// Tab nav
	$("a.tab_anchor").live("click", function(event) {
		event.preventDefault();
		var anchor = $(this);
		var id = $(this).attr("href");
		if ( ! $(anchor).hasClass("current") ) {
			$(anchor).addClass("current");
			$("a.tab_anchor[href!='" + id + "']").removeClass("current");
			// Show menu items
			$(".menu_window_tree[id!='menu_window_" + id + "']:visible").fadeOut("fast", function() {
				$("#menu_window_" + id).fadeIn("fast");
			});
		}
	});
	// New menu
	$("a.tab_anchor_add").live("click", function(event) {
		event.preventDefault();

	});

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

	/*
	 * Mostrar formulário de criacao de menu
	 */
	$("a.new.menu").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_menu_form", { parent_id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_window").html(data.html).show();
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
	 * Mostrar formulário de edicão de menu
	 */
	$("a.edit.menu").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_menu_form", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_window").html(data.html).show();
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
	 * Dropdown menu items
	 */	
	$("#menu_target").live('click', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		
		$(listing).fadeIn("fast");
	});
	$("#menu_target").live('blur', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});
	
	$(".dropdown_items_listing_targets > li > a").live('click', function(event) {
		event.preventDefault();
		$("#menu_target").val($(this).attr("href"))
	});

	// Salvar menu
	$("#button_menu_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		$.post("/admin/content/xhr_write_menu", $(".noform").serialize(), function(data){
			try {
				if ( data.done == true ) {
					showClientWarning("Menu salvo com sucesso");
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

	/*
	 * Subir item de menu
	 */
	$("map.menu_move > .up").live("click", function(event) {
		event.preventDefault();
		
		var item = $(this).parents(".tree_parent").first();
		var swap = $(item).prev(".tree_parent");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
			update_menu_order(move);
		}
	});

	/*
	 * Descer item de menu
	 */
	$("map.menu_move > .down").live("click", function(event) {
		event.preventDefault();
		
		var item = $(this).parents(".tree_parent").first();
		var swap = $(item).next(".tree_parent");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
			update_menu_order(move);
		}
	});

});

/*
 * Atualizar ordem dos menus
 */
function update_menu_order(move) {
	var parent_id = $(move).parents(".tree_parent").first().find("input[name='id']").first().val();
	var menus = new Array();

	$(move).parents(".tree_listing").first().children(".tree_parent").each(function(index) {
		$(this).children(".tree_listing_row").each(function() {
			var id = $(this).find("input[name='id']").first().val();
			menus[index] = id;
		});
	});

	$.post("/admin/content/xhr_write_menu_tree", { parent_id : parent_id, menus : menus }, function(data) {
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


//]]>
