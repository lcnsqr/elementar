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
});
