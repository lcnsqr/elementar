<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| Javacript & other files needed by backend views
|--------------------------------------------------------------------------
|
*/

// Backend support
define('BACKEND_ACCOUNT', 						'/js/backend/backend_account.js');
define('BACKEND_CLIENT_WARNING', 			'/js/backend/backend_client_warning.js');
define('BACKEND_CONTENT_TREE', 				'/js/backend/backend_content_tree.js');
define('BACKEND_CONTENT_WINDOW', 			'/js/backend/backend_content_window.js');
define('BACKEND_COMPOSITE_FIELD',			'/js/backend/backend_composite_field.js');
define('BACKEND_ANCHOR',						'/js/backend/backend_anchor.js');
define('BACKEND_SETUP', 						'/js/backend/backend_setup.js');
define('BACKEND_ACCOUNT_TREE', 				'/js/backend/backend_account_tree.js');
define('BACKEND_ACCOUNT_WINDOW', 			'/js/backend/backend_account_window.js');
define('BACKEND_FILE', 							'/js/backend/backend_file.js');
define('BACKEND_SETTINGS', 					'/js/backend/backend_settings.js');
define('BACKEND_RESET_CSS', 					'/css/backend/reset.css');
define('BACKEND_CSS', 							'/css/backend/backend.css');
define('BACKEND_TREE_CSS', 					'/css/backend/backend_tree.css');
define('BACKEND_WINDOW_CSS', 					'/css/backend/backend_window.css');

// jQuery & plugins
define('JQUERY', 									'/js/backend/jquery-2.0.3.min.js');
define('JQUERY_EASING', 						'/js/backend/jquery.easing.1.3.js');
define('JQUERY_TIMERS', 						'/js/backend/jquery.timers-1.2.js');
define('JQUERY_JSON',							'/js/backend/jquery.json-2.3.min.js');

// TinyMCE
define('JQUERY_TINYMCE', 						'/js/backend/tinymce/jquery.tinymce.min.js');

// CodeMirror
define('CM_JS',									'/js/backend/codemirror/lib/codemirror.js');
define('CM_MODE_XML',							'/js/backend/codemirror/mode/xml/xml.js');
define('CM_MODE_CSS',							'/js/backend/codemirror/mode/css/css.js');
define('CM_MODE_JS',								'/js/backend/codemirror/mode/javascript/javascript.js');
define('CM_CSS',									'/js/backend/codemirror/lib/codemirror.css');


/* End of file constants.php */
/* Location: ./application/config/constants.php */
