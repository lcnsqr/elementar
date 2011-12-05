//<![CDATA[

$(function() {

	$("#login_form").submit(function(event){
		event.preventDefault();
	
		// Bloqueio
		$("#sections_blocker").fadeIn("fast");
			
		var action = $(this).attr("action") + document.location.hash;
		
		$.post("/user/login", $(this).serialize(), function(data) {
			try {
				if ( data.done == true ) {
					// Encaminhar para o endereço solicitado
					if ( action != "" ) {
						location.reload();
					}
				}
				else {
					showClientWarning(data.msg);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
			// Bloqueio
			$("#sections_blocker").fadeOut("fast");
		}, "json");
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

	// mostrar inclusão
	$("#user_add").live("click", function(event) {
		event.preventDefault();
		
		$("#user_add_form").show("slow", "easeInSine");
	});

	// inclusão de usuário
	$("#form_user_add").live("submit", function(event) {
		event.preventDefault();

		$.post("/backend/account/xhr_write_user", $(this).serialize(), function(data){
			try {
				if ( data.done == true ) {
					$(".user_info").first().before(data.html);
					$("#user_add").hide("slow", "easeOutSine");
				}
				else {
					$.each(data, function(index, value) { 
						//console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");

	});
	
	// remoção de usuário
	$(".user_del").live("click", function(event) {
		event.preventDefault();

		var userinfo = $(this).parents(".user_info").first();

		$.post("/backend/account/xhr_erase_user", { id : id }, function(data){
			try {
				if ( data.done == true ) {
					$(userinfo).hide("slow", "easeOutSine", function() {
						$(userinfo).remove();
					});
				}
				else {
					$.each(data, function(index, value) { 
						//console.log(index + ": " + value);
					});
				}
			}
			catch (err) {
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");
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
