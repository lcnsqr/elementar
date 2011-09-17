var ckeditorConfig = {
	language: 'pt-br',
	skin: 'elementar',
	removePlugins: 'elementspath',
	extraPlugins : 'uicolor',
	toolbar:
	[
		['Link','Unlink','-','Bold','Italic','Underline','Strike','Subscript','Superscript','Source']
	],
	toolbarCanCollapse: false,
	enterMode: CKEDITOR.ENTER_BR,
	shiftEnterMode: CKEDITOR.ENTER_P
};

function ckeditor() {
	// Find proper textareas and convert them to ckeditor
	$("#content_window").find("textarea").each(function(){
		if ( $(this).hasClass('p') || $(this).hasClass('hypertext') ) {
			var id = $(this).attr('id');
			// Remove exinsting instance of CKEditor for this field
			for ( var instance in CKEDITOR.instances ) {
				var instance_id = instance.toString();
				if ( id == instance_id )
				{
					eval("delete CKEDITOR.instances."+id);
				}
			}
			CKEDITOR.replace(this, ckeditorConfig);
		}
	});
	// Cleanup removed textareas from CKEDITOR instances
	for ( var instance in CKEDITOR.instances ) {
		if ( $('textarea#'+instance.toString()).length == 0 ) {
			eval("delete CKEDITOR.instances."+instance.toString());
		}
	}
}

