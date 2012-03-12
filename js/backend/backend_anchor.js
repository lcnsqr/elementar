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
 * Backend Anchor Javascript
 * 
 * Client side code for handling anchor navigation
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

var checkAnchorHook = new Array();
var currentAnchor = null;

$(function() {
	// Anchor navigation
	setInterval(function() {
		// Check if it has changes
		if(currentAnchor != document.location.hash){
			currentAnchor = document.location.hash;
		}
		
		// Parse anchor functions
		var i = 0;
		for (i = 0; i < checkAnchorHook.length; i++)
		{
			eval(checkAnchorHook[i]);
		}
	}, 100);
});
