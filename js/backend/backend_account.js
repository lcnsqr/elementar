//<![CDATA[

$(function() {

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
