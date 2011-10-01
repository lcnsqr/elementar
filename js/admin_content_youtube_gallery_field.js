//<![CDATA[

$(function() {

	/*
	 * Youtube gallery type field
	 */

	/*
	 * Novo vídeo
	 */
	$("a.youtube_add").live('click', function(event) {
		event.preventDefault();
		var parent = $(this).parents(".youtube_gallery_field").first().children(".youtube_parent:visible");
		if ( $(parent).length > 0 ) {
			var NewVideo = $("#youtube_item_template").clone();
			// Redefinir
			$(NewVideo).removeAttr("id");
			$(NewVideo).css("display", "none");
			// Inserir
			$(parent).prepend(NewVideo);
			$(NewVideo).show("fast", "easeInSine");
		}
		else
		{
			var NewParent = $("#youtube_parent_template").clone();
			var NewVideo = $(NewParent).find("#youtube_item_template");
			// Redefinir
			$(NewParent).removeAttr("id");
			$(NewParent).css("display", "none");
			$(NewVideo).removeAttr("id");
			// Inserir
			$(this).parent(".youtube_parent_add").after(NewParent);
			$(NewParent).show("fast", "easeInSine");
		}
	});

	/*
	 * Novo vídeo acima
	 */
	$("a.youtube_add_up").live('click', function(event) {
		event.preventDefault();
		var NewVideo = $("#youtube_item_template").clone();
		// Redefinir
		$(NewVideo).removeAttr("id");
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
		var NewVideo = $("#youtube_item_template").clone();
		// Redefinir
		$(NewVideo).removeAttr("id");
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
 * Serialize videos for writing
 */
function prepare_youtube_gallery_field(list) {
	var videos = new Array();
	$(list).children(".youtube_parent:visible").each(function() {
		$(this).children(".youtube_item:visible").each(function() {
			var url = $(this).find("input[name='url']").val();
			var description = $(this).find("input[name='description']").val();
			if ( url != '' ) {
				videos.push( { url : url, description : description } );
			}
		});
	});
	return videos;
}

//]]>
