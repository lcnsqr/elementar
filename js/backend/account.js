//<![CDATA[

$(function() {

	// inclusão de usuário
	$("a#cadastrar").click(function(event) {
		event.preventDefault();

		$.post("/user/register", $("form[name='cadastro']").serialize(), function(data){
			try {
				if ( data.done == true ) {
					console.log("ok");
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
