/**
 *      Copyright 2012 Luciano Siqueira <lcnsqr@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

/** 
 * Backend Content Tree Javascript
 * 
 * Client side code for handling Contents/elements in backend tree
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	// Hide floating menus
	$("body").click(function() {
		$('body > .tree_listing_menu').fadeOut('fast', function() {
			$(this).remove();
			$(".label > a.current").removeClass('current');
		});
	});
	
	/*
	 * Hide floating menu indicator arrow on scroll
	 */
	$('#tree_parent_1').scroll(function(event){
		$('body > .tree_listing_menu > .menu_indicator').fadeOut('fast', function() {
			$(this).remove();
			$(".label > a.current").removeClass('current');
		});
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

		/*
		 * Exit if no menu
		 */
		if ( $(hidden_menu).length == 0 ) {
			return;
		}

		var visible_menu = $(hidden_menu).clone();
		
		/*
		 * Positioning
		 */
		var marginTop = -40;
		var marginLeft = 10;

		/*
		 * Menu indicator position
		 */
		var menu_indicator = $(visible_menu).children('.menu_indicator');
		var menu_indicator_top = $(this).offset().top - 5;
		var menu_indicator_left = event.pageX + 14;
		$(menu_indicator).css({position : 'fixed', top : menu_indicator_top + 'px', left : menu_indicator_left});
		/*
		 * Limit menu bottom position to the page bottom
		 */
		var menuBottom = ( ( event.pageY + $(hidden_menu).height() ) + marginTop ) - 20;
		if ( menuBottom > $(document).height() )
		{
			marginTop = marginTop - ( menuBottom - $(document).height() );
			$(visible_menu).css({left : (event.pageX + marginLeft) + 'px', bottom : '20px' });
		}
		else
		{
			$(visible_menu).css({left : (event.pageX + marginLeft) + 'px', top : (event.pageY + marginTop) + 'px' });
		}
		
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
	 * Show existing contents listing and rotate bullet arrow
	 */
	$("a.fold.folder_switch").live("click", function(event) {
		event.preventDefault();

		// Loading icon
		$("#content_tree_loading").fadeIn("fast");

		var id = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/backend/editor/xhr_render_tree_listing", { id : id }, function(data){
			if ( data.done == true ) {
				$(listing).html(data.html);
				$(listing).slideDown("fast", "easeInSine");
				$(bullet).addClass("unfold");
				$(bullet).removeClass("fold");
			}
			// Loading icon
			$("#content_tree_loading").fadeOut("fast");
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
	 * Show content creating form
	 */
	$("a.new.content").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/backend/editor/xhr_render_content_new", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show();
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Show element creating form
	 */
	$("a.new.element").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/backend/editor/xhr_render_element_new", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show();
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Show content editing form
	 */
	$("a.edit.content,a.edit.template,a.edit.meta").live('click', function(event) {
		event.preventDefault();

		// Blocking
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

		$.post("/backend/editor/xhr_render_content_form", { id : id, editor : editor }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show(function() {
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });
				});
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});
	
	/*
	 * Show element editing form
	 */
	$("a.edit.element").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		var id = $(this).attr('href');

		$.post("/backend/editor/xhr_render_element_form", { id : id }, function(data){
			if ( data.done == true ) {
				$("#content_window").html(data.html).show(function() {
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });
				});
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});
	
	/*
	 * Item removal
	 */
	$("a.remove").live('click', function(event) {
		event.preventDefault();

		var id = $(this).attr('href');

		/*
		 * Item type
		 */
		if ( $(this).hasClass("content") ) {
			var action = "/backend/editor/xhr_erase_content";
			var erase = $('#tree_listing_1').find('p.label.content > a[href="' + id + '"]').parents('.tree_parent').first();
		}
		else if ( $(this).hasClass("element") ) {
			var action = "/backend/editor/xhr_erase_element";
			var erase = $('#tree_listing_1').find('p.label.element > a[href="' + id + '"]').parents('.tree_parent').first();
		}

		// Blocking
		$("#blocker").fadeIn("fast");
		
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
	
				// Blocking
				$("#blocker").stop().fadeOut("fast");
			}, "json");
		}
		else {
			// Blocking
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
				var action = "/backend/editor/xhr_write_content_parent";
			}
			else if ( child_type == "element" ) {
				var action = "/backend/editor/xhr_write_element_parent";
			}
			
			/*
			 * Verify essential data before proceeding
			 */
			if ( ! parent_id || ! child_id ) {
				return null;
			}

			// Loading icon
			$("#content_tree_loading").fadeIn("fast");

			/*
			 * Update item parent
			 */
			$.post(action, { parent_id : parent_id, child_id : child_id }, function(data){
				if ( data.done == true ) {
					/*
					 * Reload Tree
					 */
					$.post("/backend/editor/xhr_render_tree_unfold", { request : child_type, id : child_id }, function(data) {
						$("#tree_listing_1").html(data.html);

						// Loading icon
						$("#content_tree_loading").fadeOut("fast");

					}, "json");
				}
				else {
					// Loading icon
					$("#content_tree_loading").fadeOut("fast");
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
	$('.tree_listing_icon').live('mousedown', function(event){
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
