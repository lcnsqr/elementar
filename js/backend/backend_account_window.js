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
 * Backend Account Window Javascript
 * 
 * Client side code for handling Groups/accounts 
 * load/saving action in backend main window
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	// Save group
	$(document).on('click', "#button_group_save", function(event) {
		event.preventDefault();
		
		// Blocking
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/account/xhr_write_group", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/account/xhr_render_tree_listing", { group_id : data.group_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/account/xhr_render_group_form", { group_id : data.group_id }, function(data){
					if ( data.done == true ) {
						$("#account_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });

							showClientWarning(message);

							// Blocking
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Blocking
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});

	// Save account
	$(document).on('click', "#button_account_save", function(event) {
		event.preventDefault();
		
		// Blocking
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/account/xhr_write_account", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/account/xhr_render_tree_listing", { group_id : data.group_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/account/xhr_render_account_form", { account_id : data.account_id }, function(data){
					if ( data.done == true ) {
						$("#account_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });

							showClientWarning(message);

							// Blocking
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Blocking
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});

});
