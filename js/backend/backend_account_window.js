//<![CDATA[

$(function() {

	// Save group
	$("#button_group_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/account/xhr_write_group", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/account/xhr_render_tree_listing", { group_id : data.group_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/account/xhr_render_group_form", { group_id : data.group_id }, function(data){
					if ( data.done == true ) {
						$("#account_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#editor_form').find('textarea').wysiwyg();

							showClientWarning(message);

							// Bloqueio
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});

	// Save account
	$("#button_account_save").live('click', function(event) {
		event.preventDefault();
		
		// Bloqueio
		$("#blocker").fadeIn("fast");

		/*
		 * Composite fields
		 */
		$.prepareCompositeFields();
		
		$.post("/backend/account/xhr_write_account", $(".noform").serialize(), function(data){
			if ( data.done == true ) {
				var message = data.message;
				/*
				 * Reload Tree
				 */
				$.post("/backend/account/xhr_render_tree_listing", { group_id : data.group_id }, function(data) {
					$("#tree_listing_1").html(data.html);
				}, "json");
				/*
				 * Reload editor window
				 */
				$.post("/backend/account/xhr_render_account_form", { account_id : data.account_id }, function(data){
					if ( data.done == true ) {
						$("#account_window").html(data.html).show(function() {
							// WYSIWYG textarea activation
							$('#editor_form').find('textarea').wysiwyg();

							showClientWarning(message);

							// Bloqueio
							$("#blocker").stop().fadeOut("fast");
						});
					}
				}, "json");
			}
			else {
				// Bloqueio
				$("#blocker").stop().fadeOut("fast");
				showClientWarning(data.message);
			}
		}, "json");
	});

});


//]]>
