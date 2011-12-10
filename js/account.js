//<![CDATA[

$(function() {

	$("#register_form").submit(function(event) 
	{
		event.preventDefault();

		$.post("/account/register", $(this).serialize(), function(data)
		{
			if ( data.done == true ) 
			{
				alert('O link de confirmação do cadastro foi enviado para seu email.');
			}
		}, "json");

	});
	
	$("#forgot_password").submit(function(event) 
	{
		event.preventDefault();
		
		$.post("/account/forgot", $("#forgot_password").serialize(), function(data){
			if ( data.done == true ) {
				alert(data.message);
			}
		}, "json");
	});	

	$("#reset_password").submit(function(event) 
	{
		event.preventDefault();
		
		$.post("/account/password", $("#reset_password").serialize(), function(data){
			alert(data.message);
		}, "json");
	});	
	
	$('#login_form').submit(function(event)
	{
		event.preventDefault();
		var location = $(this).attr('action');
		
		$.post('/account/login', $(this).serialize(), function(data)
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
		}, 'json');
	});
	
});

//]]>
