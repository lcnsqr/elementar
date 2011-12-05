//<![CDATA[
/*
 * Youtube gallery functions
 */
$(function() {
	/*
	 * Novo vídeo
	 */
	$("a.youtube_add").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".youtube_gallery_field").first().children(".youtube_parent:visible");
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(parent).prepend(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Novo vídeo acima
	 */
	$("a.youtube_add_up").live('click', function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(this).parents('div.youtube_item').first().before(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Novo vídeo abaixo
	 */
	$("a.youtube_add_down").live('click', function(event) {
		event.preventDefault();
		var NewVideo = $(".youtube_item_template").first().clone();
		// Redefinir
		$(NewVideo).removeClass("youtube_item_template");
		$(NewVideo).css("display", "none");
		// Inserir
		$(this).parents('div.youtube_item').first().after(NewVideo);
		$(NewVideo).show("fast", "easeInSine");
	});

	/*
	 * Remove vídeo
	 */
	$("a.youtube_delete").live('click', function(event) {
		event.preventDefault();

		var youtube_item = $(this).parents("div.youtube_item").first();
		var parent = $(this).parents("div.youtube_parent").first();
		if ( $(parent).find("div.youtube_item").length == 1 && $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1)
		{
			$(parent).hide("slow", function() {
				$(this).remove();
			});
		}
		else if ( $(this).parents(".youtube_gallery_field").first().find("div.youtube_item:visible").length > 1 )
		{
			$(youtube_item).hide("slow", function() {
				$(this).remove();
			});
		}
	});

	/*
	 * Subir vídeo
	 */
	$("a.youtube_up").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.youtube_item").first();
		var swap = $(item).prev("div.youtube_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).before(move);
		}
	});

	/*
	 * Descer vídeo
	 */
	$("a.youtube_down").live("click", function(event) {
		event.preventDefault();
		var item = $(this).parents("div.youtube_item").first();
		var swap = $(item).next("div.youtube_item");
		if ( swap.length == 1 ) {
			var move = $(item).detach();
			$(swap).after(move);
		}
	});

});

/*
 * Serialize videos for saving
 */
$.fn.extend({
	prepareYoutubeGalleryField: function(){
		var videos = new Array();
		$(this).children('.youtube_parent:not(.youtube_parent_template)').each(function() {
			$(this).children('.youtube_item:not(.youtube_item_template)').each(function() {
				var url = $(this).find('input[name="url"]').val();
				var description = $(this).find('input[name="description"]').val();
				if ( url != '' ) {
					videos.push( { url : url, description : description } );
				}
			});
		});
		$(this).find('input.youtube_gallery_actual_field').val($.toJSON(videos));
	}
});

//]]>
