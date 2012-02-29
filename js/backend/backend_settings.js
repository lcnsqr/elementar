$(function(){
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
