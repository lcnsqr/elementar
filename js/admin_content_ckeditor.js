var ckeditorConfig = {
	language: 'pt-br',
	skin: 'elementar',
	removePlugins: 'elementspath',
	extraPlugins : 'uicolor',
	toolbar:
	[
		['Link','Unlink','-','Bold','Italic','Underline','Strike','Subscript','Superscript']
	],
	toolbarCanCollapse: false,
	enterMode: CKEDITOR.ENTER_P,
	shiftEnterMode: CKEDITOR.ENTER_BR
};

function ckeditor() {
	// Find proper textareas and convert them to ckeditor
	$("#content_window").find("textarea:visible").each(function(){
		if ( $(this).hasClass('p') || $(this).hasClass('hypertext') ) {
			var id = $(this).attr('id');
			var exists = Object.keys(CKEDITOR.instances).some(function(element, index, array) {
				return (element == id);
			});
			if ( exists ) {
				eval("delete CKEDITOR.instances."+id);
			}
			CKEDITOR.replace(this, ckeditorConfig);
		}
	});
	// Cleanup removed textareas from CKEDITOR instances
	$(Object.keys(CKEDITOR.instances)).each(function(index, id){
		if ( $('textarea#'+id).length == 0 ) {
			eval("delete CKEDITOR.instances."+id);
		}
	});
}

