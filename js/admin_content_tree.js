//<![CDATA[

$(function() {

	// Unfocus proper elements
	$("body").click(function() {
		// tree menu listings
		$(".dropdown:visible").fadeOut("fast", "easeOutSine", function () {
			$(this).parents(".tree_listing_menu").first().fadeOut("fast", "easeOutSine");
			$(this).parents(".tree_listing_menu").next(".tree_listing_text").toggleClass("tree_listing_hover", false);
		});
		// Discard labels being edited
		$("form.label").find("input.edit[type='text']").each(function() {
			$(this).removeClass("edit");
		});
	});
	
	/*
	 * Show existing contents listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();
		/*
		 * Request type
		 */
		if ( $(this).hasClass("category") ) {
			var request = "category";
		}
		else if ( $(this).hasClass("content") ) {
			var request = "content";
		}
		var id = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/admin/content/xhr_render_tree_listing", { request : request, id : id }, function(data){
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
	 * Show/hide menu button on mouse over
	 */
	$(".tree_listing_row").live("mouseenter", function() {
		$(this).children(".tree_listing_menu").show();
		$(this).children(".tree_listing_text").toggleClass("tree_listing_hover", true);
	});
	$(".tree_listing_row").live("mouseleave", function() {
		if ( $(this).find(".tree_menu_dropdown").is(":hidden") ) {
			$(this).children(".tree_listing_menu").hide();
			$(this).children(".tree_listing_text").toggleClass("tree_listing_hover", false);
		}
	});
	
	/*
	 * Show/hide category/content/element menu
	 */
	$(".tree_menu_dropdown_button").live('click', function(event) {
		event.preventDefault();
		$(this).next(".tree_menu").children(".tree_menu_dropdown:visible").fadeOut("fast", "easeInSine");
		$(this).next(".tree_menu").children(".tree_menu_dropdown:hidden").fadeIn("fast", "easeInSine");
	});

	/*
	 * Activate rename category/content/element
	 */
	$("form.label").find("input[type='text']").live("click", function(event) {
		event.preventDefault();		
		if ( ! $(this).hasClass("edit") ) {
			$(this).addClass("edit");
			//$(this).select();
		}
	});
	/*
	 * Rename form
	 */
	$("form.label").live("submit", function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");
		
		var form = $(this);

		/*
		 * Request type
		 */
		if ( $(form).hasClass("category") ) {
			var action = "/admin/content/xhr_rename_category";
		}
		else if ( $(form).hasClass("content") ) {
			var action = "/admin/content/xhr_rename_content";
		}
		else if ( $(form).hasClass("element") ) {
			var action = "/admin/content/xhr_rename_element";
		}
		else if ( $(form).hasClass("menu") ) {
			var action = "/admin/content/xhr_rename_menu";
		}
		else {
			return false;
		}

		$.post(action, $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					$(form).find("input.edit[type='text']").each(function() {
						$(this).blur();
						$(this).removeClass("edit");
					});	
				}
				else {
					showClientWarning(data.error);
					$(form).find("input.edit[type='text']").each(function() {
						$(this).blur();
						$(this).removeClass("edit");
						$(this).val(data.name);
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
	 * Mostrar formulário de criação de conteúdo
	 */
	$("a.new.content").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_content_new", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_window").html(data.html).show();
					// appropriate tinymce
					$("#content_editor_window").find("textarea").each(function() {
						make_editor($(this));
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
	 * Mostrar formulário de criação de elemento em categoria
	 */
	$("a.new.category_element").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');
		var parent = "category";

		$.post("/admin/content/xhr_render_element_new", { parent : parent, id : id }, function(data){
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
	 * Mostrar formulário de criação de elemento em conteúdo
	 */
	$("a.new.content_element").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');
		var parent = "content";

		$.post("/admin/content/xhr_render_element_new", { parent : parent, id : id }, function(data){
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
	 * Mostrar formulário de edição dos meta fields
	 */
	$("a.meta").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		/*
		 * Item type
		 */
		if ( $(this).hasClass("category") ) {
			var request = "category";
		}
		else if ( $(this).hasClass("content") ) {
			var request = "content";
		}

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_meta_form", { request : request, id : id }, function(data){
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
	 * Mostrar formulário de atualização de conteúdo
	 */
	$("a.edit.content").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_content_form", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_window").html(data.form).show();
					// appropriate tinymce
					$("#content_editor_window").find("textarea").each(function() {
						make_editor($(this));
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
	 * Mostrar formulário de atualização de elemento
	 */
	$("a.edit.element").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_element_form", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_editor_window").html(data.form).show();
					// appropriate tinymce
					$("#content_editor_window").find("textarea").each(function() {
						make_editor($(this));
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
	 * Criação de categoria
	 */
	$("a.new.category").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_category_form", { parent_id : id }, function(data){
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
	 * Item removal
	 */
	$("a.remove").live('click', function(event) {
		event.preventDefault();
		/*
		 * Item type
		 */
		if ( $(this).hasClass("category") ) {
			var action = "/admin/content/xhr_erase_category";
		}
		else if ( $(this).hasClass("content") ) {
			var action = "/admin/content/xhr_erase_content";
		}
		else if ( $(this).hasClass("element") ) {
			var action = "/admin/content/xhr_erase_element";
		}
		else if ( $(this).hasClass("menu") ) {
			var action = "/admin/content/xhr_erase_menu";
		}

		// Bloqueio
		$("#sections_blocker").fadeIn("fast");
		
		if (confirm($(this).attr("title") + "?")) { 

			var id = $(this).attr('href');
			var erase = $(this).parents(".tree_parent").first();
			var parent_listing = $(erase).parents(".tree_listing").first();
			var parent = $(erase).parents(".tree_parent").first();
			$.post(action, { id : id }, function(data){
				try {
					if ( data.done == true ) {
						showClientWarning("Removido");
						$(erase).slideUp("fast", "easeOutSine", function() {
							$(this).remove();
							if ( $(parent_listing).children().length == 0 ) {
								$(parent_listing).hide();
								$(parent).find(".tree_listing_bullet").first().html("<span class=\"bullet_placeholder\">&nbsp;</span>");
							}
						});
					}
				}
				catch (err) {
					showClientWarning("Erro de comunicação com o servidor");
				}
	
				// Bloqueio
				$("#sections_blocker").fadeOut("fast");
			}, "json");
		}
		else {
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}
	});

})

//]]>
