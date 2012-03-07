$(function() {

	$("#setup_form").submit(function(event) 
	{
		event.preventDefault();
		$("#blocker").fadeIn("fast");
		$.post("/setup/xhr_write_admin", $(this).serialize(), function(data)
		{
			if ( data.done )
			{
				window.location.replace(data.location);
			}
			else
			{
				showClientWarning(data.message);
				$("#blocker").fadeOut("fast");
			}
		}, "json");

	});

});
