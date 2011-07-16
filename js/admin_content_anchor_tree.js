//<![CDATA[

// Function which check if there are anchor changes, if there are, sends the ajax petition
var path = "";

//checkAnchorHook.push("checkAnchorTree()");
function checkAnchorTree() {

	if (currentAnchor) {
		// Creates the  string callback
		var splits = currentAnchor.substring(1).split("|");
		// Create the path string
		var currentPath = splits[1];
	}

	if ( currentPath != path && $("#div_editor").children().length > 0 ) {
		path = currentPath;
		if ( ! path ) {
			// Check if it's not dropdown menu request
			if ( $(".dropdown:visible").length == 0 ) {
				// Clear all inner listings
				$(".tree_listing[id!='tree_listing_0']").slideUp("fast", "easeOutSine", function() {
					$(this).html("");
				});
				// Rotate inner arrow bullets
				$("a.unfold").each(function() {
					$(this).addClass("fold");
					$(this).removeClass("unfold");
				});
			}
		}
		else {
			// Send the petition
			$.post("/admin/content/xhr_render_tree_unfold", { path : path }, function(data){
				try {
					if ( data.done == true ) {
						unfoldTree(data.unfold, path);
					}
				}
				catch (err) {
					// console.log("Erro de comunicação com o servidor");
				}
			}, "json");
		}
	}

}

function unfoldTree(unfold, path) {
	if ( unfold.length > 0 ) {
		// Render each tree part on sequence
		var id = unfold[0].id;
		var request = unfold[0].type;
		var listing = $(".tree_listing[id='tree_listing_" + request + "_" + unfold[0].parent_id + "_" + unfold[0].id + "']");
		var sname = unfold[0].sname;

		$.post("/admin/content/xhr_render_tree_listing", { path : path, id : id, sname : sname, request : request }, function(data){
			try {
				if ( data.done == true ) {
					if ( $(listing).html() == "" ) {
						// Rotate arrow bullet
						var bullet = $(listing).parents(".tree_parent").first().find("a.fold").first();
						$(bullet).addClass("unfold");
						$(bullet).removeClass("fold");
						// Show requested listing with slide effect
						$(listing).html(data.content);
						//$(listing).find(".tree_listing_row").hide();
						//$(listing).find(".tree_listing_row").fadeIn("fast", "easeInSine");
						$(listing).slideDown("fast", "easeInSine");
					}
					// Pop out first item and do it again
					unfold.shift();
					unfoldTree(unfold, path);
				}
			}
			catch (err) {
				showClientWarning("Erro de comunicação com o servidor");
			}
		}, "json");
	}
}


//]]>
