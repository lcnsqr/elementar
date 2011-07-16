//<![CDATA[

// Function which check if there are anchor changes, if there are, sends the ajax petition
var section = "";
checkAnchorHook.push("checkAnchorAccountSection()");
function checkAnchorAccountSection(){

	// if there is not anchor, then loads the default section
	if(!currentAnchor) {
		// Usar primeiro item do menu esquerdo
		var currentSection = $("#left_menu").find("a").first().attr("href").substring(1);
	}
	else {
		// Creates the  string callback
		var splits = currentAnchor.substring(1).split("|");
		// Get the section
		var currentSection = splits[0];
	}

	if ( currentSection != section ) { 
		section = currentSection;
		
		// Block sections area
		$("#sections_blocker").fadeIn("fast");
		
		divid = "div_"+section;

		// Send the petition
		$.post("/admin/account/xhr_render_section", { section : section }, function(data){
			try {
				if ( data.done == true ) {
					/*
					 * current menu
					 */
					$("a[href!='#"+section+"']").removeClass("current");
					$("a[href='#"+section+"']").addClass("current");

					/*
					 * Section content
					 */

					if ( $("div.div_section[id!="+divid+"]:visible").length == 0 ) {
						// Exibir seção
						$('div#'+divid).html(data.html);
						$('div#'+divid).fadeIn("slow");
						$('div#'+divid).focus();
					}
					else {
						// Esconder anterior e exibir seção
						$("div.div_section[id!="+divid+"]:visible").fadeOut("fast", function() {
							$(this).html("");
							$('div#'+divid).html(data.html);
							$('div#'+divid).fadeIn("slow");
							$('div#'+divid).focus();
						});
					}
					
					$("#elapsed_time").html(data.elapsed_time);
					
				}
			}
			catch (err) {
				// console.log("Erro de comunicação com o servidor");
			}
			
			// Release sections area
			$("#sections_blocker").fadeOut("fast");		

		}, "json");

	}

}

//]]>
