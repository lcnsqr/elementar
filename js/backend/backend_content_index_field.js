//<![CDATA[

/*
 * Index field functions
 */
$(function() {

	/*
	 * Dropdown contents items
	 */	
	$("input.index_field[type='text']").live('click', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeIn("fast");
	});
	$("input.index_field[type='text']").live('blur', function(event) {
		var dropdown = $(this).next(".dropdown_items_listing_position").first();
		var listing = $(dropdown).find(".dropdown_items_listing");
		$(listing).fadeOut("fast");
	});

	$(".dropdown_items_listing_contents > li > a").live('click', function(event) {
		event.preventDefault();
		var input = $(this).parents(".dropdown_items_listing_position").first().prev("input");
		$(input).val($(this).attr("href"));
	});

});

/*
 * Prepare index field for saving
 */
$.fn.extend({
	prepareIndexField: function(){
	}
});


//]]>
