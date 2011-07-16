//<![CDATA[

// Function which check if there are anchor changes, if there are, sends the ajax petition
var windowRequest = "";

checkAnchorHook.push("checkAnchorWindow()");
function checkAnchorWindow() {

	if (currentAnchor) {
		// Creates the  string callback
		var splits = currentAnchor.substring(1).split("|");
		// Create the path string
		var currentWindow = splits[1];
	}

	if ( currentWindow != windowRequest && $("#div_editor").children().length > 0 ) {
		windowRequest = currentWindow;
		if ( windowRequest ) {
			var request = windowRequest.split("=");
			var type = request[0];
			var id = request[1];

			switch (type) {
				case "element" :
				var action = "/admin/content/xhr_render_element_form";
				break;
				case "content" :
				var action = "/admin/content/xhr_render_content_form";
				break;
			}

			// Bloqueio
			$("#sections_blocker").fadeIn("fast");
	
			$.post(action, { id : id }, function(data){
				try {
					if ( data.done == true ) {
						$("#content_editor_window").html(data.form).show();
						// appropriate tinymce
						$("#content_editor_window").find("textarea").each(function() {
							make_editor($(this));
						});
					}
				}
				catch (err) {
					showClientWarning("Erro de comunicação com o servidor");
				}
	
				// Bloqueio
				$("#sections_blocker").fadeOut("fast");
			}, "json");
		}
	}

}

//]]>
