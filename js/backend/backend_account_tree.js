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
 * Backend Account Tree Javascript
 * 
 * Client side code for handling Groups/accounts in backend tree
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
		});
		$(".label > a.current").removeClass('current');
	});
	
	$('#tree_parent_1').scroll(function(event){
		$('body > .tree_listing_menu').fadeOut('fast', function() {
			$(this).remove();
		});
		$(".label > a.current").removeClass('current');
	});
	
	/*
	 * Item menu
	 */
	$(document).on('click', '.label > a', function(event){
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
		var marginTop = -40;
		var marginLeft = 10;

		/*
		 * Menu indicator positioning
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
			$(visible_menu).css({position : 'fixed', left : (event.pageX + marginLeft) + 'px', bottom : '20px' });
		}
		else
		{
			$(visible_menu).css({position : 'fixed', left : (event.pageX + marginLeft) + 'px', top : (event.pageY + marginTop) + 'px' });
		}
		$('body').append(visible_menu);
		$(visible_menu).fadeIn('fast');
	});
	
	/*
	 * Prevent unintended default browser draging of link
	 */
	$(document).on('mousedown', '.label > a', function(event){
		event.preventDefault();
	});

	/*
	 * Show existing group listing and rotate bullet arrow
	 */
	$(document).on("click", "a.fold.folder_switch", function(event) {
		event.preventDefault();

		// Loading icon
		$("#account_tree_loading").fadeIn("fast");

		var group_id = $(this).attr("href");
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		var bullet = $(this);

		$.post("/backend/account/xhr_render_group_listing", { group_id : group_id }, function(data){
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
	$(document).on("click", "a.unfold.folder_switch", function(event) {
		event.preventDefault();
		var listing = $(this).parents(".tree_parent").first().find(".tree_listing").first();
		$(listing).slideUp("fast", "easeOutSine");
		$(this).addClass("fold");
		$(this).removeClass("unfold");
	});	

	/*
	 * Show group create/edit form
	 */
	$(document).on('click', "a.new.group,a.edit.group", function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");
		
		if ( $(this).hasClass('new') ) {
			var group_id = '';
		}
		else if ( $(this).hasClass('edit') ) {
			var group_id = $(this).attr('href');
		}

		$.post("/backend/account/xhr_render_group_form", { group_id : group_id }, function(data){
			if ( data.done == true ) {
				$("#account_window").html(data.html).show();
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Show account create/edit form
	 */
	$(document).on('click', "a.new.account,a.edit.account", function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");
		
		if ( $(this).hasClass('new') ) {
			var group_id = $(this).attr('href');
			var account_id = '';
		}
		else if ( $(this).hasClass('edit') ) {
			var group_id = '';
			var account_id = $(this).attr('href');
		}

		$.post("/backend/account/xhr_render_account_form", { group_id : group_id, account_id : account_id }, function(data){
			if ( data.done == true ) {
				$("#account_window").html(data.html).show();
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/*
	 * Item removal
	 */
	$(document).on('click', "a.remove", function(event) {
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

		// Blocking
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
			var account_id = $(child_label).children('a').attr('href');
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
			var group_id = $(parent_label).children('a').attr('href');
	
			/*
			 * Re-add droppable class to dragged row
			 */
			$('.tree_listing_row').not('.undroppable').not('.droppable').addClass('droppable');

			/*
			 * Unset droppable highlight
			 */
			$('.tree_listing_row.hover').removeClass('hover');
			
			/*
			 * Verify essential data before proceeding
			 */
			if ( ! group_id || ! account_id ) {
				return null;
			}

			// Loading icon
			$("#account_tree_loading").fadeIn("fast");

			/*
			 * Update item parent
			 */
			$.post("/backend/account/xhr_write_account_group", { group_id : group_id, account_id : account_id }, function(data){
				if ( data.done == true ) {
					/*
					 * Reload Tree
					 */
					$.post("/backend/account/xhr_render_tree_listing", { group_id : data.group_id }, function(data) {
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
		
		/*
		 * Stop resizing tree
		 */
		if ( resizing == 1 ) {
			resizing = 0;
			$('body').css('cursor', 'inherit');
			// Remember position
			var position = parseInt($('#vertical_resizer').css('left'), 10);
			$.cookie('account_tree_width', position);
		}
	});
	
	/*
	 * Trigger drag and drop moving events
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

		/*
		 * Resize tree width
		 */
		var limit_left = 140;
		var limit_right = parseInt(window.innerWidth - ( window.innerWidth / 2 ), 10);
		var current = parseInt($('#vertical_resizer').css('left'), 10);
		if ( mouseButton == 1 && resizing == 1 && ( event.pageX % 2 ) == 0 ) {
			var grip_left = event.pageX - resizing_offset_grip;
			if ( grip_left < limit_left || grip_left > limit_right ) {
				return;
			}
			$('#vertical_resizer').css('left', grip_left + 'px');
			var tree_width = event.pageX - resizing_offset_tree;
			$('#account_tree').width(tree_width);
			var editor_left = event.pageX + resizing_offset_editor;
			$('#account_editor_board').css('left', editor_left + 'px');
		}

	});
	
	/*
	 * Clone draggable row and add it to drag container
	 */
	$(document).on('mousedown', '.tree_listing_icon.draggable', function(event){
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
	
	/*
	 * Resize tree width
	 */
	$('#vertical_resizer').mousedown(function(event){
		//event.stopPropagation();  
		event.preventDefault();
		$('body').css('cursor', 'col-resize');
		resizing = 1;
		resizing_offset_grip = event.pageX - parseInt($(this).css('left'), 10);
		resizing_offset_tree = event.pageX - $('#account_tree').width();
		resizing_offset_editor = parseInt($('#account_editor_board').css('left'), 10) - event.pageX;
	});

	/*
	 * Load saved tree width
	 */
	var limit_left = 140;
	var limit_right = parseInt(window.innerWidth - ( window.innerWidth / 2 ), 10);
	var position = parseInt($.cookie('account_tree_width'), 10);
	if ( position > 0 ) {
		var grip_left = position;
		if ( grip_left >= limit_left && grip_left <= limit_right ) {
			$('#vertical_resizer').css('left', grip_left + 'px');
			var tree_width = position;
			$('#account_tree').width(tree_width);
			var editor_left = position + 4;
			$('#account_editor_board').css('left', editor_left + 'px');
		}
	}

});

/*
 * Drag and drop settings
 */
var offsetY = 0;
var offsetX = 0;
var mouseButton = 0;

/*
 * Vertical reesizer
 */
var resizing = 0;
var resizing_offset_grip = 0;
var resizing_offset_tree = 0;
var resizing_offset_editor = 0;
