//<![CDATA[
var tinymceConfigHypertext = {
	// Location of TinyMCE script
	script_url : '/js/backend/tiny_mce/tiny_mce.js',

	// General options
	theme : "advanced",
	plugins : "filemanager",

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,cut,copy,paste,pastetext,pasteword,|,bullist,numlist,undo,redo,|,link,unlink,filemanager,|,code",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	width : "100%",
	
	theme_advanced_source_editor_width : 800,
	theme_advanced_source_editor_height : 480,
	relative_urls : false
};

var tinymceConfigParagraph = {
	// Location of TinyMCE script
	script_url : '/js/backend/tiny_mce/tiny_mce.js',

	// General options
	theme : "advanced",
	plugins : "",

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,cut,copy,paste,pastetext,|,undo,redo,|,link,unlink,|,code",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	width : "100%",

	force_br_newlines : true,
	force_p_newlines : false,
	forced_root_block : '',

	theme_advanced_source_editor_width : 800,
	theme_advanced_source_editor_height : 480,
	relative_urls : false
};

/*
 * Custom method to activate tinymce in some textareas
 */
$.fn.extend({
	wysiwyg: function () {
		if ( $(this).hasClass('p') ) {
			$(this).tinymce(tinymceConfigParagraph);
		}
		else if ( $(this).hasClass('hypertext') ) {
			$(this).tinymce(tinymceConfigHypertext);
		}
	}
});

//]]>
