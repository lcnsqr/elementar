//<![CDATA[

$(function() {

	// Hide floating menus
	$("body").click(function() {
		$('body > .tree_listing_menu').fadeOut('fast', function() {
			$(this).remove();
		});
		$(".label > a.current").removeClass('current');
	});
	
	/*
	 * Item menu
	 */
	$('.label > a').live('click', function(event){
		event.preventDefault();
		/*
		 * Hide visible menus first
		 */
		$('body > .tree_listing_menu').fadeOut('fast', function() {
			$(this).remove();
		});
		/*
		 * Turn current bold
		 */
		$(".label > a.current").removeClass('current');
		$(this).addClass('current');
		/*
		 * Show menu
		 */
		var hidden_menu = $(this).parents('.tree_listing_row').first().find('.tree_listing_menu');
		var visible_menu = $(hidden_menu).clone();
		/*
		 * Positioning
		 */
		$(visible_menu).css({ 'left' : event.pageX + 'px', 'top' : event.pageY + 'px' });
		$('body').append(visible_menu);
		$(visible_menu).fadeIn('fast');
	});
	
	/*
	 * Show existing contents listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();

		var id = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/admin/content/xhr_render_tree_listing", { id : id }, function(data){
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
	 * Mostrar formulário de criação de conteúdo
	 */
	$("a.new.content").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_content_new", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_window").html(data.html).show();
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Mostrar formulário de criação de elemento
	 */
	$("a.new.element").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_element_new", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_window").html(data.html).show();
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Mostrar formulário de edição de conteúdo
	 */
	$("a.edit.content,a.edit.template,a.edit.meta").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		/*
		 * Which editor tab to show
		 */
		if ( $(this).hasClass("content") ) {
			var editor = 'content';
		}
		else if ( $(this).hasClass("template") ) {
			var editor = 'template';
		}
		else if ( $(this).hasClass("meta") ) {
			var editor = 'meta';
		}

		$.post("/admin/content/xhr_render_content_form", { id : id, editor : editor }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_window").html(data.html).show(function() {
						// WYSIWYG textarea activation
						$('#content_editor_form').find('textarea').wysiwyg();
					});
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});
	
	/*
	 * Mostrar formulário de edição de elemento
	 */
	$("a.edit.element").live('click', function(event) {
		event.preventDefault();

		// Bloqueio
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/admin/content/xhr_render_element_form", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$("#content_window").html(data.html).show(function() {
						// WYSIWYG textarea activation
						$('#content_editor_form').find('textarea').wysiwyg();
					});
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}

			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
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
		if ( $(this).hasClass("content") ) {
			var action = "/admin/content/xhr_erase_content";
		}
		else if ( $(this).hasClass("element") ) {
			var action = "/admin/content/xhr_erase_element";
		}

		// Bloqueio
		$("#blocker").fadeIn("fast");
		
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
				$("#blocker").stop().fadeOut("fast");
			}, "json");
		}
		else {
			// Bloqueio
			$("#blocker").stop().fadeOut("fast");
		}
	});

})

//]]>
