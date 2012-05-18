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
 * Backend Content Window Javascript
 * 
 * Client side code for handling Content/element 
 * load/saving action in backend main window
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */

$(function() {

	// Display something by default
	if ( $.trim($("#content_window").html()) == '' )
	{
	}

	// Unfocus proper elements
	$("body").click(function() {
		// dropdown widget
		var widget = $(".dropdown_items_listing").parents(".dropdown_items_listing_inline").first();
		$(".dropdown_items_listing:visible").fadeOut("fast");
	});
	
	// Dropdown widget
	$(".dropdown_items_listing_inline > a").live('click', function(event) {
		event.preventDefault();
		var listing = $(this).parent().find(".dropdown_items_listing").first();
		if ( ! $(listing).is(":visible") ) {
			$(listing).fadeIn("fast");
		}
		else {
			$(listing).fadeOut("fast");
		}
	});

	// Save meta fields
	$("#button_meta_save").live('click', function(event) {
		event.preventDefault();
		
		// Blocking
		$("#blocker").fadeIn("fast");

		$.post("/backend/editor/xhr_write_meta", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
			}
			else {
				showClientWarning(data.message);
			}

			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Element type selection
	
	$(".dropdown_items_listing_element_type_target").live('click', function(event) {
		event.preventDefault();
		
		var type_id = $(this).attr("href");
		var type_name = $(this).html();
		
		if ( type_id != "0" ) {
			$(this).parents(".dropdown_items_listing_inline").children("a:first").attr("href", type_id);
			$(this).parents(".dropdown_items_listing_inline").children("a:first").html(type_name);
		}
	});

	// Create a new element under a content
	$("a#choose_element_type_for_parent_id").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		var parent_id = $(this).attr("href");
		var type_id = $(this).parents("div:first").find(".dropdown_items_listing_inline").find("a:first").attr("href");

		if ( type_id == "0" ) {
			// No element type, create a new one
			$("a#element_type_create").trigger('click');
			return null;
		}

		$.post("/backend/editor/xhr_render_element_form", { parent_id : parent_id, type_id : type_id }, function(data){
			if ( data.done == true ) {
				// Close type editor (if visible)
				$("#type_define_new_container:visible").fadeOut("slow");
				$("#editors_container").replaceWith(data.html).show(function(){
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });
				});
			}
			else {
				showClientWarning(data.message);
			}
			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Discard new element form
	$("#button_element_discard").live('click', function(event) {
		event.preventDefault();
		$("#element_editor_form").hide('slow', function() {
			$("#element_editor_form").html("");
		});
	});

	// Save new element
	$("#button_element_save").live('click', function(event) {
		event.preventDefault();
		
		// Blocking
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/editor/xhr_write_element", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/editor/xhr_render_tree_unfold", { request : 'element', id : data.element_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/editor/xhr_render_element_form", { id : data.element_id }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });

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

	// Content type selection
	$(".dropdown_items_listing_content_type_target").live('click', function(event) {
		event.preventDefault();
		
		var type_id = $(this).attr("href");
		var type_name = $(this).html();
		
		if ( type_id != "0" ) {
			$(this).parents(".dropdown_items_listing_inline").children("a:first").attr("href", type_id);
			$(this).parents(".dropdown_items_listing_inline").children("a:first").html(type_name);
		}
	});

	// Content creation form
	$("a#choose_content_type_for_parent_id").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		var parent_id = $(this).attr("href");
		var type_id = $(this).parents("div:first").find(".dropdown_items_listing_inline").find("a:first").attr("href");
		
		if ( type_id == "0" ) {
			// No element type, create a new one
			$("a#content_type_create").trigger('click');
			return null;
		}

		$.post("/backend/editor/xhr_render_content_form", { parent_id : parent_id, type_id : type_id, editor : 'content' }, function(data){
			if ( data.done == true ) {
				// Close type editor (if visible)
				$("#type_define_new_container:visible").fadeOut("slow");
				$("#editors_container").replaceWith(data.html).show(function(){
					// WYSIWYG textarea activation
					$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });
				});
			}
			else {
				showClientWarning(data.message);
			}
			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Discard new content form
	$("#button_content_discard").live('click', function(event) {
		event.preventDefault();
		$("#content_editor_form").hide('slow', function() {
			$("#content_editor_form").html("");
		});
	});

	// Save content
	$("#button_content_save").live('click', function(event) {
		event.preventDefault();
		
		// Blocking
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();

		$.post("/backend/editor/xhr_write_content", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree & editor window if not home
				 */
				if ( data.content_id != 1 ) {
					$.post("/backend/editor/xhr_render_tree_unfold", { request : 'content', id : data.content_id }, function(data) {
						$("#tree_listing_1").html(data.html);
					}, "json");
				}
				$.post("/backend/editor/xhr_render_content_form", { id : data.content_id, editor : 'content' }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#content_editor_form').find('textarea').each(function(){ $(this).wysiwyg(); });

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
	
	/*
	 * Show content's available pseudo variables
	 */
	$('.pseudo_variables_menu_switcher').live('click', function(event){
		event.preventDefault();
		var accordion = $(this).parents('.pseudo_variables_menu').first().find('.pseudo_variables_accordion').first();
		if ( $(this).hasClass('collapsed') ) {
			$(accordion).slideDown('fast', 'easeOutSine');
			$(this).removeClass('collapsed');
			$(this).addClass('expanded');
		}
		else {
			$(accordion).slideUp('fast', 'easeInSine');
			$(this).removeClass('expanded');
			$(this).addClass('collapsed');
		}
	});

	// Hide pseudo variables floating menus indicator arrow on scroll
	$('#content_window').scroll(function(event){
		$('body > .element_filter_menu > .menu_indicator').fadeOut('fast');
	});
	/*
	 * pseudo variables filtering/insertion menu
	 */
	$('.variable_pair_menu').live('click', function(event){
		event.preventDefault();

		/*
		 * Hide visible menus first
		 */
		$('body > .element_filter_menu').fadeOut('fast', function() {
			$(this).remove();
		});
		/*
		 * Show menu
		 */
		var hidden_menu = $(this).next('.element_filter_menu');
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
	 * Save filter
	 */
	$('.save_filter').live('click', function(event)
	{
		event.preventDefault();
		// Blocking
		$("#blocker").fadeIn("fast");

		var filter_form = $(this).parents('.filter_forms').find('div.order_by');
		
		var visible_menu = $(this).parents('div.element_filter_menu').first().clone();
		$(visible_menu).removeAttr('style');
		$(visible_menu).children('.menu_indicator').removeAttr('style');

		$.post("/backend/editor/xhr_write_template_filter", $(filter_form).find('input').serialize(), function(data){
			if ( data.done == true ) {
				/*
				 * Update hidden menu
				 */
				var hidden_menu = $('.variable_pair_menu[href="' + data.element_type + '"]').next('div.element_filter_menu');
				$(hidden_menu).replaceWith(visible_menu);
				
				showClientWarning(data.message);
			}
			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");

	});

	/*
	 * Close variables filter menu button hover
	 */
	$('.close_menu').live('mouseenter', function(event){
		$(this).addClass('close_menu_hover');
	});
	$('.close_menu').live('mouseleave', function(event){
		$(this).removeClass('close_menu_hover');
	});

	// Close pseudo variables floating menus
	$('.close_menu').live('click', function(event) {
		event.preventDefault();
		var menu = $(this).parents('.element_filter_menu').first();
		$(menu).fadeOut('fast', function() {
			$(menu).remove();
		});
	});

	/*
	 * Add pseudo variable to template
	 */
	$('.add_variable_single').live('click', function(event) {
		event.preventDefault();
		var variable = unescape($(this).attr('href'));
		var textarea = $(this).parents('.form_window_column_input').first().find('.template_textarea');
		$(textarea).insertAtCursor(variable);
	});
	
	/*
	 * Add relative pseudo variable pair (loop) to template
	 */
	$('.add_relative_variable_pair').live('click', function(event) {
		event.preventDefault();

		var insertion_form = $(this).parents('.filter_forms').find('div.insertion');

		var variable = unescape($(this).data('variable'));

		$('.template_textarea').insertAtCursor(variable);
	});
	
	/*
	 * Add filtered pseudo variable pair (loop) to template
	 */
	$('.add_variable_pair').live('click', function(event) {
		event.preventDefault();

		var insertion_form = $(this).parents('.filter_forms').find('div.insertion');

		var variable = '{' + $(this).attr('href') + '}' + "\n";

		$(insertion_form).find('input[checked="checked"]:checkbox').each(function(index)
		{
			variable = variable.concat('{' + $(this).attr('value') + '}' + "\n");
		});

		variable = variable.concat('{/' + $(this).attr('href') + '}' + "\n");

		$('.template_textarea').insertAtCursor(variable);
	});
	
	/*
	 * Template HTML actions
	 */
	$('.template_menu.add_file_uri').live('click', function(event){
		event.preventDefault();
		/*
		 * Identifies receptor input
		 */
		var identifier = $(this).data('identifier');
		/*
		 * Pass caller data to file manager 
		 */
		window.open('/backend/file/manager?parent=add_file_uri&identifier=' + identifier, '_blank', 'height=480, width=880');
	});
	
	/*
	 * insertAtCursor: jQuery extended function to 
	 * insert text at cursor on input
	 */
	$.fn.extend({
		insertAtCursor: function (value) {
			/*
			 * Based on code found in
			 * http://alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript
			 */
			// IE support
			if (document.selection) {
				$(this)[0].focus();
				sel = document.selection.createRange();
				sel.text = value;
			}
			// Other browsers
			else if ($(this)[0].selectionStart || $(this)[0].selectionStart == '0') {
				var startPos = $(this)[0].selectionStart;
				var endPos = $(this)[0].selectionEnd;
				$(this)[0].value = $(this)[0].value.substring(0, startPos)
				+ value
				+ $(this)[0].value.substring(endPos, $(this)[0].value.length);
			} 
			else {
				$(this)[0].value += value;
			}

			var CaretPos = $(this)[0].value.substring(0, startPos).length + value.length;	

			if($(this)[0].setSelectionRange)
			{
				$(this)[0].focus();
				$(this)[0].setSelectionRange(CaretPos,CaretPos);
			}
			else if ($(this)[0].createTextRange) {
				var range = $(this)[0].createTextRange();
				range.collapse(true);
				range.moveEnd('character', CaretPos);
				range.moveStart('character', CaretPos);
				range.select();
			}
		}
	});
	
	/* 
	 * Load new content_type form
	 */
	$("a#content_type_create").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		$.post("/backend/editor/xhr_render_content_type_form", function(data){
			if ( data.done == true ) {
				$("#type_define_new_container").html(data.html).show("slow");
			}
			else {
				showClientWarning(data.message);
			}
			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	/* 
	 * Load new element_type form
	 */
	$("a#element_type_create").live('click', function(event) {
		event.preventDefault();

		// Blocking
		$("#blocker").fadeIn("fast");

		$.post("/backend/editor/xhr_render_element_type_form", function(data){
			if ( data.done == true ) {
				$("#type_define_new_container").html(data.html).show("slow");
			}
			else {
				showClientWarning(data.message);
			}
			// Blocking
			$("#blocker").stop().fadeOut("fast");
		}, "json");
	});

	// Discard new content type form
	$(".type_define_new_form").live('reset', function(event) {
		event.preventDefault();
		$("#type_define_new_container").hide('slow', function() {
			$("#type_define_new_container").html("");
		});
	});
	
	/*
	 * Create a content_type
	 */
	$("#content_type_define_new_form").live('submit', function(event) {
		event.preventDefault();
		// Blocking
		$("#blocker").fadeIn("fast");

		$.post("/backend/editor/xhr_write_content_type", $(this).serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
				$("#type_define_new_container").hide('slow', function() {

					// Reload types dropdown widget with new value
					var id = $("#choose_content_type_for_parent_id").attr('href');
			
					$.post("/backend/editor/xhr_render_content_new", { id : id, type_id : data.type_id }, function(data){
						if ( data.done == true ) {
							$("#content_window").html(data.html).show();
						}
			
						// Blocking
						$("#blocker").stop().fadeOut("fast");
					}, "json");

				});
			}
			else {
				showClientWarning(data.message);
				// Blocking
				$("#blocker").stop().fadeOut("fast");
			}
		}, "json");
	});

	/*
	 * Create a element type
	 */
	$("#element_type_define_new_form").live('submit', function(event) {
		event.preventDefault();
		// Blocking
		$("#blocker").fadeIn("fast");
		$.post("/backend/editor/xhr_write_element_type", $(this).serialize(), function(data){
			if ( data.done == true ) {
				showClientWarning(data.message);
				$("#type_define_new_container").hide('slow', function() {
					$("#type_define_new_container").html("");
				});
				
				// Reload types dropdown widget with new value
				var id = $("#choose_element_type_for_parent_id").attr("href");
		
				$.post("/backend/editor/xhr_render_element_new", { id : id, type_id : data.type_id }, function(data){
					if ( data.done == true ) {
						$("#content_window").html(data.html).show();
					}
					// Blocking
					$("#blocker").stop().fadeOut("fast");
				}, "json");

			}
			else {
				showClientWarning(data.message);
				// Blocking
				$("#blocker").stop().fadeOut("fast");
			}
		}, "json");
	});

	/*
	 * Clone and redefine field type from template (new content type field)
	 */
	$("a#add_type_field").live('click', function(event) {
		event.preventDefault();
		var NewField = $("#type_define_new_field_0").clone();
		var field_label = $(NewField).find("label[for='field_0']");
		var field = $(NewField).find("#field_0");
		var field_type_label = $(NewField).find("label[for='field_type_0']");
		var field_type = $(NewField).find("#field_type_0");
		
		// Redefine
		var id = $("#type_define_new_container").find(".type_define_new_field").length;
		$(NewField).attr("id", "type_define_new_field_"+id);
		$(field_label).attr("for", "field_"+id);
		$(field).attr("id", "field_"+id);
		$(field).attr("name", "field_"+id);
		$(field).attr("value", "");
		$(field_type_label).attr("for", "field_type_"+id);
		$(field_type).attr("id", "field_type_"+id);
		$(field_type).attr("name", "field_type_"+id);
		$(NewField).css("display", "none");
		
		// Insert
		$(this).before(NewField);
		$(NewField).show("slow");

		// Field count (to tell the server controller)
		var count = $("#type_define_new_container").find(".type_define_new_field").length;
		$("#type_define_new_container").find("input[name='field_count']").val(count);
		
	});
	
	/*
	 * Content/template editor tabs
	 */
	$("a.editors_menu_item").live('click', function(event) {
		event.preventDefault();
		var target = $(this).attr('href');
		
		$("a.editors_menu_item[href!='"+target+"']").removeClass("current");
		$(this).addClass("current");
		
		$("div.editor_form[id!='"+target+"']").hide();
		$("div.editor_form[id='"+target+"']").show();
	});
	
	/*
	 * Template save
	 */
	$("#button_template_save").live('click', function(event) {
		event.preventDefault();
		
		var template_form = $(this).parents('#template_editor_form').first();

		/*
		 * Requires confirmation if default
		 * template is about to be overwritten
		 */
		var overwrite = true;
		var sole = $(template_form).find('input[name="template_sole"]').first();
		if ( ! $(sole).attr('checked') && $(sole).length > 0 ) {
			overwrite = confirm($('label.template_confirm_overwrite').html());
		}

		if ( overwrite == true ) {
			// Blocking
			$("#blocker").fadeIn("fast");

			/*
			 * Template textarea
			 */
			var template_textarea = $(template_form).find('.template_textarea');
	
			$.post("/backend/editor/xhr_write_template", $(template_form).find('.noform').serialize() + '&overwrite=' + overwrite, function(data){
				if ( data.done == true ) {
					//$(template_textarea).val(data.template);
					showClientWarning(data.message);
				}
				else {
					showClientWarning(data.message);
				}
				// Blocking
				$("#blocker").stop().fadeOut("fast");
			}, "json");
		}
	});

});
