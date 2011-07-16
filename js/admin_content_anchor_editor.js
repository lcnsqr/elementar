//<![CDATA[

// Function which check if there are anchor changes, if there are, sends the ajax petition
var editorRequest = null;

checkAnchorHook.push("checkAnchorEditor()");
function checkAnchorEditor() {

	if (currentAnchor) {
		// Creates the  string callback
		var splits = currentAnchor.substring(1).split("|");
		// Create the path string
		var currentEditor = splits[1];
	}

	// Maybe editor elements not ready yet...
	if ( currentEditor != editorRequest && $("#div_editor").children().length > 0 ) {
		editorRequest = currentEditor;
		if ( editorRequest ) {
			var request = editorRequest.split("=");
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
						// Set tree to content/element category 
						$.post("/admin/content/xhr_render_tree_unfold", { request : type, id : id }, function(data) {
							$("#tree_listing_0").html(data.html);
						}, "json");
						
						// content/element
						$("#content_editor_window").html(data.form).show();
						// appropriate tinymce
						$("#content_editor_window").find("textarea").each(function() {
							make_editor($(this));
						});
						// Bloqueio
						$("#sections_blocker").fadeOut("fast");
					}
				}
				catch (err) {
					showClientWarning("Erro de comunicação com o servidor");
					// Bloqueio
					$("#sections_blocker").fadeOut("fast");
				}
			}, "json");
		}
	}

}

//]]>
