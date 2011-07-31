var ckeditorConfig = {
	language: 'pt-br',
	extraPlugins : 'uicolor',
	toolbar:
	[
		['Link','Unlink','-','Bold','Italic','Underline','Strike','Subscript','Superscript','-','Source','RemoveFormat']
	],
	enterMode: CKEDITOR.ENTER_P,
	shiftEnterMode: CKEDITOR.ENTER_BR
};

function ckeditor() {
	$("#content_editor_window").find("textarea.p:visible").each(function(){
		var instance = CKEDITOR.instances[$(this).attr("id")];
		if (instance) {
			CKEDITOR.remove(instance);
		}
		CKEDITOR.replace(this, ckeditorConfig);
	});
	if ( $("textarea.p:visible").length == 0 ) {
		$("#content_editor_window").stopTime('ckeditor');
	}
}

