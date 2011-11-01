//<![CDATA[

$(function() {

$("#login_form").submit(function(event){
	event.preventDefault();
	
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
				console.log(data.msg);
			}
		}
		catch (err) {
			//console.log("Erro de comunicação com o servidor");
		}
	}, "json");
});

});

//]]>
