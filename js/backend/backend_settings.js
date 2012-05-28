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
 * Backend Settings Javascript
 * 
 * Client side code for handling the backend main settings actions
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function(){
	
	// Display something by default
	if ( $.trim($("#content_window").html()) == '' )
	{
	}

	
	$('.settings > a').click(function(event){
		event.preventDefault();

		// Loading blocker in
		$("#blocker").fadeIn("fast");
		
		var page = $(this).attr('href');
		$.get('/backend/main/xhr_render_settings', { page : page }, function(data){
			if ( data.done )
			{
				$('#content_window').html(data.html);
			}
			else
			{
				showClientWarning(data.message);
			}
			// Loading blocker out
			$("#blocker").stop().fadeOut("fast");
		}, 'json');
	});
	
	$('#button_settings_save').live('click', function(event){
		var page = $(this).data('page');
		
		// Loading blocker in
		$("#blocker").fadeIn("fast");

		$.post('/backend/main/xhr_write_settings', $('.noform').serialize() + '&page=' + page, function(data){
			if ( data.done )
			{
				$('#content_window').html(data.html);
			}
			showClientWarning(data.message);
			// Loading blocker out
			$("#blocker").stop().fadeOut("fast");
		}, 'json');
	});

	/*
	 * Tree drag and drop
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

		/*
		 * Stop resizing tree
		 */
		if ( resizing == 1 ) {
			resizing = 0;
			$('body').css('cursor', 'inherit');
			// Remember position
			var position = parseInt($('#vertical_resizer').css('left'), 10);
			$.cookie('main_tree_width', position);
		}
	});
	
	/*
	 * Trigger drag and drop moving events
	 */
	$(window).mousemove(function(event){

		/*
		 * Resize tree width
		 */
		var limit_left = 140;
		var limit_right = parseInt(window.innerWidth - ( window.innerWidth / 2 ), 10);
		var current = parseInt($('#vertical_resizer').css('left'), 10);
		if ( mouseButton == 1 && resizing == 1 ) {
			var grip_left = event.pageX - resizing_offset_grip;
			if ( grip_left < limit_left || grip_left > limit_right ) {
				return;
			}
			$('#vertical_resizer').css('left', grip_left + 'px');
			var tree_width = event.pageX - resizing_offset_tree;
			$('#main_tree').width(tree_width);
			var editor_left = event.pageX + resizing_offset_editor;
			$('#main_window').css('left', editor_left + 'px');
		}

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
		resizing_offset_tree = event.pageX - $('#main_tree').width();
		resizing_offset_editor = parseInt($('#main_window').css('left'), 10) - event.pageX;
	});

	/*
	 * Load saved tree width
	 */
	var limit_left = 140;
	var limit_right = parseInt(window.innerWidth - ( window.innerWidth / 2 ), 10);
	var position = parseInt($.cookie('main_tree_width'), 10);
	if ( position > 0 ) {
		var grip_left = position;
		if ( grip_left >= limit_left && grip_left <= limit_right ) {
			$('#vertical_resizer').css('left', grip_left + 'px');
			var tree_width = position;
			$('#main_tree').width(tree_width);
			var editor_left = position + 4;
			$('#main_window').css('left', editor_left + 'px');
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
