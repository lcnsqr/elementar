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

/*
 * Custom method to activate CKEditor in some textareas
 */
$.fn.extend({
	ckeditor: function () {
		if ( $(this).hasClass('p') || $(this).hasClass('hypertext') ) {
			var id = $(this).attr('id');
			// Remove existing instance of CKEditor for this field
			for ( var instance in CKEDITOR.instances ) {
				var instance_id = instance.toString();
				if ( id == instance_id )
				{
					eval("delete CKEDITOR.instances."+id);
				}
			}
			CKEDITOR.replace(id, ckeditorConfig);
		}
		// Cleanup removed textareas from CKEDITOR instances
		for ( var instance in CKEDITOR.instances ) {
			if ( $('textarea#'+instance.toString()).length == 0 ) {
				eval("delete CKEDITOR.instances."+instance.toString());
			}
		}
	}
});
