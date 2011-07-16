//<![CDATA[
function showClientWarning(msg) {
	$("#client_warning").stopTime("cliente_warning");
	$("#client_msg").html(msg);
	// Show client warning
	$("#client_warning").fadeIn("fast");
	$("#client_warning").oneTime(3000, "cliente_warning", function() {
		$("#client_warning").fadeOut("slow");
	});
}
//]]>
