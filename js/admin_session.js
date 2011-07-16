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

});

//]]>
