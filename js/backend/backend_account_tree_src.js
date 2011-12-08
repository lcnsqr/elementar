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
		var marginTop = -20;
		var marginLeft = 5;
		$(visible_menu).css({ 'left' : (event.pageX + marginLeft) + 'px', 'top' : (event.pageY + marginTop) + 'px' });
		$('body').append(visible_menu);
		$(visible_menu).fadeIn('fast');
	});
	
	/*
	 * Prevent unintended default browser draging of link
	 */
	$('.label > a').live('mousedown', function(event){
		event.preventDefault();
	});

	/*
	 * Show existing group listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();

		// Loading icon
		$("#account_tree_loading").fadeIn("fast");

		var id = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/backend/account/xhr_render_group_listing", { id : id }, function(data){
			if ( data.done == true ) {
				$(listing).html(data.html);
				$(listing).slideDown("fast", "easeInSine");
				$(bullet).addClass("unfold");
				$(bullet).removeClass("fold");
			}
			// Loading icon
			$("#account_tree_loading").fadeOut("fast");
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
	 * Show group create/edit form
	 */
	$("a.new.group,a.edit.group,a.new.account,a.edit.account").live('click', function(event) {
		event.preventDefault();

		/*
		 * Item type
		 */
		if ( $(this).hasClass("group") ) {
			var action = "/backend/account/xhr_render_group_form";
		}
		else if ( $(this).hasClass("account") ) {
			var action = "/backend/account/xhr_render_account_form";
		}

		// Bloqueio
		$("#blocker").fadeIn("fast");
		
		if ( $(this).hasClass('new') ) {
			var id = '';
		}
		else if ( $(this).hasClass('edit') ) {
			var id = $(this).attr('href');
		}

		$.post(action, { id : id }, function(data){
			if ( data.done == true ) {
				$("#account_window").html(data.html).show();
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
		if ( $(this).hasClass("group") ) {
			var action = "/backend/account/xhr_erase_group";
		}
		else if ( $(this).hasClass("account") ) {
			var action = "/backend/account/xhr_erase_account";
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
					$(erase).slideUp("fast", "easeOutSine", function() {
						$(this).remove();
						if ( $(parent_listing).children().length == 0 ) {
							$(parent_listing).hide();
							$(parent).find(".tree_listing_bullet").first().html("<span class=\"bullet_placeholder\">&nbsp;</span>");
						}
					});
				}
				showClientWarning(data.message);
	
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
		/*
		 * Unset mouse button
		 */
		mouseButton = 0;
			
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
			 * Re-add droppable class to dragged row
			 */
			$('.tree_listing_row').not('.undroppable').not('.droppable').addClass('droppable');

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

			// Loading icon
			$("#account_tree_loading").fadeIn("fast");

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

						// Loading icon
						$("#account_tree_loading").fadeOut("fast");

					}, "json");
				}
				else {
					// Loading icon
					$("#account_tree_loading").fadeOut("fast");
					showClientWarning(data.message);
				}
			}, "json");

		}
		
	});
	
	/*
	 * Move item and highlight droppable row behind
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
			$('.tree_listing_row.droppable').not('.dragging').each(function(){
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
	$('.tree_listing_icon.draggable').live('mousedown', function(event){
		event.preventDefault();
		
		/*
		 * Get item row
		 */
		var row = $(this).parent('.tree_listing_row');
		
		/*
		 * Reject tree first parent (Home)
		 */
		if ( $(row).parent('#tree_parent_1').length > 0 ) {
			return null;
		}
		
		/*
		 * Offset position
		 */
		var offset = $(row).offset();
		offsetY = event.pageY - offset.top;
		offsetX = event.pageX - offset.left;
		
		/*
		 * Disable same item drop
		 */
		$(row).removeClass('droppable');
		
		var moving = $(row).clone();
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
