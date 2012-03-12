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
 * Backend Account Javascript
 * 
 * Client side user management
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	$("#login_form").submit(function(event){
		event.preventDefault();
	
		// Blocking
		$("#blocker").fadeIn("fast");
			
		var action = $(this).attr("action") + document.location.hash;
		
		$.post("/account/login", $(this).serialize(), function(data) {
			try {
				if ( data.done == true ) {
					// Reload after succesful auth
					if ( action != "" ) {
						location.reload();
					}
				}
				else {
					showClientWarning(data.message);
					// Blocking
					$("#blocker").fadeOut("fast");
				}
			}
			catch (err) {
				showClientWarning("Communication error");
			}
		}, "json");
	});

	$('.logout').click(function(event)
	{
		event.preventDefault();
		var location = $(this).attr('href');
		$.post('/account/logout', function(data)
		{
			if ( data.done == true ) 
			{
				window.location.replace(location);
			}
			else
			{
				alert(data.message);
			}
		}, 'json');
	});

	// Show user inclusion page
	$("#user_add").live("click", function(event) {
		event.preventDefault();
		
		$("#user_add_form").show("slow", "easeInSine");
	});

	// User inclusion action
	$("#form_user_add").live("submit", function(event) {
		event.preventDefault();

		$.post("/backend/account/xhr_write_user", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					$(".user_info").first().before(data.html);
					$("#user_add").hide("slow", "easeOutSine");
				}
				else {
					$.each(data, function(index, value) { 
						//console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Communication error");
			}
		}, "json");

	});
	
	// User removal
	$(".user_del").live("click", function(event) {
		event.preventDefault();

		var userinfo = $(this).parents(".user_info").first();

		$.post("/backend/account/xhr_erase_user", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$(userinfo).hide("slow", "easeOutSine", function() {
						$(userinfo).remove();
					});
				}
				else {
					$.each(data, function(index, value) { 
						//console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Communication error");
			}
		}, "json");
	});
	
});

// Confirm before leaving page while action is performed
var idle = true;
window.onbeforeunload = confirmExit;

function confirmExit() {
	if ( idle != true ) {
		return "There is an active action, leave page?";
	}
}
