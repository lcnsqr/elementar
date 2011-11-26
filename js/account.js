//<![CDATA[

$(function() {

	// inclusão de usuário
	$("#form_cadastro").submit(function(event) {
		event.preventDefault();

		$.post("/user/register", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					alert('O link de confirmação do cadastro foi enviado para seu email.');
				}
				else {
					var msg = '';
					$.each(data, function(index, value) { 
						if ( index != 'done' && index != 'elapsed_time' )
						{
							msg += value + "\n";
						}
					});
					alert(msg);
				}
			}
			catch (err) {
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");

	});
	
	$("#reset_password").submit(function(event) {
		event.preventDefault();
		
		$.post("/user/reset_password", $("#reset_password").serialize(), function(data){
			try {
				if ( data.done == true ) {
					$.each(data, function(index, value) { 
						console.log(index + ": " + value);
					});
				}
				else {
					$.each(data, function(index, value) { 
						console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");
	});	

	$("#change_password").submit(function(event) {
		event.preventDefault();
		
		$.post("/user/reset_new_password", $("#change_password").serialize(), function(data){
			try {
				if ( data.done == true ) {
					$.each(data, function(index, value) { 
						console.log(index + ": " + value);
					});
				}
				else {
					$.each(data, function(index, value) { 
						console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");

	});
	
	$('#login_form').submit(function(event)
	{
		event.preventDefault();
		var location = $(this).attr('action');
		
		$.post('/user/login', $(this).serialize(), function(data)
		{
			if ( data.done == true )
			{
				window.location.replace(location);
			}
			else
			{
				alert(data.msg);
			}
		}, 'json');
	});
	
	$('.logout').click(function(event)
	{
		event.preventDefault();
		var location = $(this).attr('href');
		$.post('/user/logout', function(data)
		{
			if ( data.done == true ) 
			{
				window.location.replace(location);
			}
			else
			{
				alert(data.msg);
			}
		}, 'json');
	});
	
});

// Confirmar saída se há operação em andamento
var idle = true;
window.onbeforeunload = confirmExit;

function confirmExit() {
	if ( idle != true ) {
		return "Há uma operação em andamento. Tem certeza que deseja sair da página?";
	}
}

//]]>
