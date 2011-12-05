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

		$.post("/backend/content/xhr_render_tree_listing", { id : id }, function(data){
			if ( data.done == true ) {
				$(listing).html(data.html);
				$(listing).slideDown("fast", "easeInSine");
				$(bullet).addClass("unfold");
				$(bullet).removeClass("fold");
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

		$.post("/backend/content/xhr_render_content_new", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show();
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

		$.post("/backend/content/xhr_render_element_new", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show();
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

		$.post("/backend/content/xhr_render_content_form", { id : id, editor : editor }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show(function() {
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').wysiwyg();
				});
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

		$.post("/backend/content/xhr_render_element_form", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show(function() {
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').wysiwyg();
				});
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
			var action = "/backend/content/xhr_erase_content";
		}
		else if ( $(this).hasClass("element") ) {
			var action = "/backend/content/xhr_erase_element";
		}

		// Bloqueio
		$("#blocker").fadeIn("fast");
		
		var id = $(this).attr('href');
		var erase = $('#tree_listing_1').find('p.label > a[href="' + id + '"]').parents('.tree_parent').first();
		var parent_listing = $(erase).parents(".tree_listing").first();
		var parent = $(erase).parents(".tree_parent").first();
		
		if (confirm($(this).attr("title") + "?")) { 

			$.post(action, { id : id }, function(data){
				if ( data.done == true ) {
					showClientWarning(data.message);
					$(erase).slideUp("fast", "easeOutSine", function() {
						$(this).remove();
						if ( $(parent_listing).children().length == 0 ) {
							$(parent_listing).hide();
							$(parent).find(".tree_listing_bullet").first().html("<span class=\"bullet_placeholder\">&nbsp;</span>");
						}
					});
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
	
	
	/*
	 * Tree item drag and drop
	 */

	/*
	 * Register pressed mouse button
	 */
	$(window).mousedown(function(event){
		mouseButton = 1;
	});

	/*
	 * Discard dragging item upon mouse button up
	 */
	$(window).mouseup(function(event){
		if ( $('#tree_drag_container').children().length > 0 )
		{
			/*
			 * Store dragged item details before discarding it
			 */
			var child_label = $('#tree_drag_container').find('p.label').first();
			var child_id = $(child_label).children('a').attr('href');
			if ( $(child_label).hasClass('element') ) {
				var child_type = 'element';
			}
			else if ( $(child_label).hasClass('content') ) {
				var child_type = 'content';
			}
			/*
			 * Discard dragged item
			 */
			$('#tree_drag_container').fadeOut('fast', function(){
				$('#tree_drag_container').html('');
				$('#tree_drag_container').hide();
			});
			
			/*
			 * Trigger dropped action on selected row
			 */
			var parent_label = $('.tree_listing_row.hover').find('p.label').first();
			var parent_id = $(parent_label).children('a').attr('href');
	
			/*
			 * Unset mouse button
			 */
			mouseButton = 0;
			
			/*
			 * Re-add dropable class to dragged row
			 */
			$('.tree_listing_row:not(.dropable)').addClass('dropable');

			/*
			 * Unset droppable highlight
			 */
			$('.tree_listing_row.hover').removeClass('hover');
			
			/*
			 * Item type
			 */
			if ( child_type == "content" ) {
				var action = "/backend/content/xhr_write_content_parent";
			}
			else if ( child_type == "element" ) {
				var action = "/backend/content/xhr_write_element_parent";
			}
			
			/*
			 * Verify essential data before proceeding
			 */
			if ( ! parent_id || ! child_id ) {
				return null;
			}

			// Bloqueio
			$("#blocker").fadeIn("fast");

			/*
			 * Update item parent
			 */
			$.post(action, { parent_id : parent_id, child_id : child_id }, function(data){
				if ( data.done == true ) {
					/*
					 * Reload Tree
					 */
					$.post("/backend/content/xhr_render_tree_unfold", { request : child_type, id : child_id }, function(data) {
						$("#tree_listing_1").html(data.html);

						// Bloqueio
						$("#blocker").stop().fadeOut("fast");

					}, "json");
				}
				else {
					// Bloqueio
					$("#blocker").stop().fadeOut("fast");
					showClientWarning(data.message);
				}
			}, "json");

		}
		
	});
	
	/*
	 * Move item and highlight dropable row behind
	 */
	$(window).mousemove(function(event){
		if ( mouseButton == 1 && $('#tree_drag_container').children().length > 0 ) {
			/*
			 * Show draging container if not visible
			 */
			$('#tree_drag_container:hidden').fadeIn('fast');

			/*
			 * Verify if there is dragging content and drag it
			 */
			$('#tree_drag_container').css('top', (event.pageY - offsetY) + 'px');
			$('#tree_drag_container').css('left', (event.pageX - offsetX) + 'px');
			
			/*
			 * Highlight row behind
			 */
			var pointerY = event.pageY;
			var pointerX = event.pageX;
			$('.tree_listing_row.dropable:not(.dragging)').each(function(){
				var row_top = $(this).offset().top;
				var row_right = $(this).offset().left + $(this).outerWidth();
				var row_bottom = $(this).offset().top + $(this).outerHeight();
				var row_left = $(this).offset().left;
				if ( pointerY > row_top && pointerX < row_right && pointerY < row_bottom && pointerY > row_left ) {
					$(this).addClass('hover');
				}
				else {
					$(this).removeClass('hover');
				}
			});
		}
	});
	
	/*
	 * Clone draggable row and add it to drag container
	 */
	$('.tree_listing_row').live('mousedown', function(event){
		event.preventDefault();
		
		/*
		 * Reject tree first parent (Home)
		 */
		if ( $(this).parent('#tree_parent_1').length > 0 ) {
			return null;
		}
		
		/*
		 * Offset position
		 */
		var offset = $(this).offset();
		offsetY = event.pageY - offset.top;
		offsetX = event.pageX - offset.left;
		
		/*
		 * Disable same item drop
		 */
		$(this).removeClass('dropable');
		
		var moving = $(this).clone();
		$(moving).addClass('dragging');
		$(moving).children().addClass('dragging');
		$('#tree_drag_container').html(moving);
	});

})

/*
 * Drag and drop settings
 */
var offsetY = 0;
var offsetX = 0;
var mouseButton = 0;

//]]>
