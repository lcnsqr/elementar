//<![CDATA[

/*
 * Menu editing functions
 */
$(function() {
	/*
	 * Novo menu topo
	 */
	$("a.menu_add").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".menu_field").first().children(".menu_parent:visible");
		if ( $(parent).length > 0 ) {
			var NewMenu = $(".menu_item_template").first().clone();
			// Redefinir
			$(NewMenu).removeClass("menu_item_template");
			$(NewMenu).css("display", "none");
			// Inserir
			$(parent).prepend(NewMenu);
			$(NewMenu).show("fast", "easeInSine");
		}
		else
		{
			var NewParent = $(".menu_parent_template").first().clone();
			var NewMenu = $(NewParent).find(".menu_item_template");
			// Redefinir
			$(NewParent).removeClass("menu_parent_template");
			$(NewParent).css("display", "none");
			$(NewMenu).removeClass("menu_item_template");
			// Inserir
			$(this).parent(".menu_parent_add").after(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Novo menu acima
	 */
	$("a.menu_add_up").live('click', function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Redefinir
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Inserir
		$(this).parents('div.menu_item').first().before(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * Novo menu abaixo
	 */
	$("a.menu_add_down").live('click', function(event) {
		event.preventDefault();
		var NewMenu = $(".menu_item_template").first().clone();
		// Redefinir
		$(NewMenu).removeClass("menu_item_template");
		$(NewMenu).css("display", "none");
		// Inserir
		$(this).parents('div.menu_item').first().after(NewMenu);
		$(NewMenu).show("fast", "easeInSine");
	});

	/*
	 * Novo submenu
	 */
	$("a.menu_add_submenu").live('click', function(event) {
		event.preventDefault();

		var NewParent = $(".menu_parent_template").first().clone();
		var NewMenu = $(NewParent).find(".menu_item_template");
		
		// Redefinir
		$(NewParent).removeClass("menu_parent_template");
		$(NewMenu).removeClass("menu_item_template");
		
		// Inserir
		var Menu = $(this).parents('div.menu_item').first();
		if ( $(Menu).find(".menu_parent").length > 0 )
		{
			$(NewMenu).css("display", "none");
			$(Menu).find(".menu_parent").first().prepend(NewMenu);
			$(NewMenu).show("fast", "easeInSine");
		}
		else
		{
			$(NewParent).css("display", "none");
			// Inserir menu parent
			$(this).parents('div.menu_item').first().append(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Remove menu
	 */
	$("a.menu_delete").live('click', function(event) {
		event.preventDefault();

		var menu_item = $(this).parents("div.menu_item").first();
		var parent = $(this).parents("div.menu_parent").first();
		if ( $(parent).find("div.menu_item").length == 1 && $(this).parents(".menu_field").first().find("div.menu_item:visible").length > 1)
		{
			$(parent).hide("slow", function() {
				$(this).remove();
			});
		}
		else if ( $(this).parents(".menu_field").first().find("div.menu_item:visible").length > 1 )
		{
			$(menu_item).hide("slow", function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Dropdown menu items
	 */	
	$(".menu_item_target > input[type='text']").live('click', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$(".menu_item_target > input[type='text']").live('blur', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});
	
	$(".dropdown_items_listing_targets > li > a").live('click', function(event) {
		event.preventDefault();
		var input = $(this).parents(".dropdown_items_listing_position").first().prev("input");
		$(input).val($(this).attr("href"));
	});

	/*
	 * Subir item de menu
	 */
	$("a.menu_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.menu_item").first();
		var swap = $(item).prev("div.menu_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Descer item de menu
	 */
	$("a.menu_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.menu_item").first();
		var swap = $(item).next("div.menu_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

});

/*
 * Serialize menus for saving
 */
$.fn.extend({
	prepareMenuField: function(){
		var menus = new Array();
		$(this).children('.menu_parent:not(.menu_parent_template)').each(function() {
			$(this).children('.menu_item:not(.menu_item_template)').each(function() {
				var name = $(this).find('input[name="name"]').val();
				var target = $(this).find('input[name="target"]').val();
				if ( $(this).children('.menu_parent:not(.menu_parent_template)').length > 0 ) {
					var submenu = $(this).prepareMenuField();
				}
				else {
					var submenu = null;
				}
				menus.push( { name : name, target : target, menu : submenu } );
			});
		});
		$(this).find('input.menu_actual_field').val($.toJSON(menus));
	}
});


//]]>