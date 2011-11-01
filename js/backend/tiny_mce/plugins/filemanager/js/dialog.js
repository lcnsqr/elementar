tinyMCEPopup.requireLangPack();

var FileManagerDialog = {
	init : function() {
		// Local actions from plugin call data
		//var f = document.forms[0];
		// Get the selected contents as text and place it in the input
		//f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		//f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');
	},

	insert : function(contents) {
		// Insert the contents 
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, contents);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(FileManagerDialog.init, FileManagerDialog);
