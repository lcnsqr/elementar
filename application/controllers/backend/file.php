<?php
/*
 *      file.php
 *      
 *      Copyright 2012 Luciano Siqueira <lcnsqr@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** 
 * Backend File Class 
 * 
 * Remote file manager
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class File extends CI_Controller {
	
	var $ROOT;

	// i18n settings
	var $LANG;
	var $LANG_AVAIL = array();

	function __construct()
	{
		parent::__construct();

		// Disable caching in backend
		$this->output->disable_cache();

		// Elementar database
		$this->elementar = $this->load->database('elementar', TRUE);

		// URI helper
		$this->load->helper('url');

		// Create, read, update and delete Model
		$this->load->model('Storage', 'storage');
		$this->storage->STATUS = 'all';

		// Backend language file
		$this->lang->load('elementar', $this->config->item('language'));
		
		// Load encryption key before session library
		$this->config->set_item('encryption_key', $this->storage->get_config('encryption_key'));
		
		// Session library
		$this->load->library('session');
		
		// Elementar Common Library
		$this->load->library('common');

		// Exit if not authenticated admin session
		$this->common->backend_auth_check();

		// Load site i18n settings
		list($this->LANG, $this->LANG_AVAIL) = $this->common->load_i18n_settings();

		$this->ROOT = '/files';

	}

	/**
	 * File main method
	 *
	 * @access public
	 * @return void
	 */
	function index()
	{
		$this->manager();
	}

	/**
	 * Redirected from index method
	 *
	 * @access public
	 * @return void
	 */
	function manager()
	{
		$data = array();

		// Client controller (javascript files)
		$js = array(
			JQUERY,
			JQUERY_EASING,
			JQUERY_TIMERS,
			JQUERY_JSON,
			BACKEND_FILE,
			JQUERY_COOKIE
		);

		// CSS for file view
		$css = array(
			BACKEND_RESET_CSS,
			BACKEND_CSS,
			BACKEND_TREE_CSS,
			BACKEND_WINDOW_CSS
		);
		
		// Add tinymce support scripts if requested by file manager plugin
		if ( $this->input->get('parent', TRUE) == 'tinymce' )
		{
			$js[] = TINY_MCE_POPUP;
			$js[] = TINY_MCE_FILEMANAGER;
		}
		$data['js'] = $js;
		$data['css'] = $css;

		$data['folder'] = array(
			'name' => $this->lang->line('elementar_fm_root'),
			'path' => $this->ROOT,
			'children' => (bool) count($this->_subfolders($this->ROOT))
		);
		$data['folders'] = $this->_render_tree_folder($this->ROOT);
		$data['listing'] = $this->_render_listing($this->ROOT, $this->input->get('parent', TRUE));

		// Localized texts
		$data['fm_title'] = $this->lang->line('elementar_fm_title');
		$data['fm_new_folder'] = $this->lang->line('elementar_fm_new_folder');
		$data['fm_upload_file'] = $this->lang->line('elementar_fm_upload_file');
		
		$this->load->view('backend/backend_file', $data);
	}

	/**
	 * List public files dir
	 *
	 * @access public
	 * @return void
	 */
	function xhr_render_tree()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		if ( (bool) $this->input->post('path') )
		{
			$path = $this->input->post('path');
		}
		else
		{
			$path = $this->ROOT;
		}

		$html = $this->_render_tree_folder($path);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->output->set_output_json($response);
		
	}

	/**
	 * List files tree up to the requested item
	 *
	 * @access public
	 * @return void
	 */	
	function xhr_render_tree_unfold()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		if ( (bool) $this->input->post('path') )
		{
			$path = $this->input->post('path');
		}
		else
		{
			$path = $this->ROOT;
		}

		/*
		 * Verify if it's a file or a directory
		 * and assign proper variables
		 */
		$relative_path = '.' . $path;
		if ( is_file($relative_path) )
		{
			$relative_path = dirname($relative_path);
			$path = substr($relative_path, 1, strlen($relative_path) - 1);
		}

		if ( $path != $this->ROOT )
		{
			$parent_dir = dirname($path);
			$tree = $this->_render_tree_folder($parent_dir, $path);
			$tree_dir = $parent_dir;
			while ( $tree_dir != $this->ROOT )
			{
				$parent_dir = dirname($tree_dir);
				$tree = $this->_render_tree_folder($parent_dir, $path, $tree, $tree_dir);
				$tree_dir = $parent_dir;
			}
		}
		else
		{
			$tree = $this->_render_tree_folder($this->ROOT);
		}

		$response = array(
			'done' => TRUE,
			'html' => $tree
		);
		$this->output->set_output_json($response);
		
	}
	
	/**
	 * Render items in content div
	 *
	 * @access public
	 * @return void
	 */
	function xhr_render_contents()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		if ( (bool) $this->input->post('path') )
		{
			$path = $this->input->post('path');
		}
		else
		{
			$path = $this->ROOT;
		}
		
		// Caller field type
		$parent = $this->input->post('parent');

		$html = $this->_render_listing($path, $parent);

		/*
		 * Verify if it's a file and
		 * strip directory information
		 */
		$relative_path = '.' . $path;
		if ( is_file($relative_path) )
		{
			$relative_path = dirname($relative_path);
			$path = substr($relative_path, 1);
		}

		$title = basename($path);
		
		$response = array(
			'title' => $title,
			'path' => $path,
			'done' => TRUE,
			'html' => $html
		);
		$this->output->set_output_json($response);
	}
	
	/**
	 * Recursively load tree listings
	 *
	 * @access private
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	private function _render_tree_folder($path, $current = NULL, $tree = NULL, $tree_dir = NULL)
	{
		$this->load->helper('string');
		$folders = array();
		$folder_names = $this->_subfolders($path);
		foreach ( $folder_names as $folder )
		{
			$subfolders = $this->_subfolders($path . '/' . $folder);
			$children = ( count($subfolders) > 0 ) ? TRUE : FALSE;
			$folders[] = array(
				'name' => $folder,
				'path' => $path . '/' . $folder,
				'children' => $children
			);
		}
		$data = array('folders' => $folders);
		// Inner directories, if any
		$data['current'] = ( $current == NULL ) ? $this->ROOT : $current;
		$data['tree_dir'] = $tree_dir;
		$data['tree'] = $tree;
		$html = $this->load->view('backend/backend_file_tree', $data, true);
		return $html;
	}
	
	/**
	 * List subfolders in the requested folder
	 *
	 * @access private
	 * @param string
	 * @return string
	 */
	private function _subfolders($path)
	{
		$this->load->helper('directory');
		
		$relative_path = '.' . $path;
		
		// List top level directories
		$map = directory_map($relative_path, 1);
		$folders = array();
		foreach ( $map as $content )
		{
			if ( is_dir( $relative_path . '/' . $content ) )
			{
				$folders[] = $content;
			}
		}
		sort($folders);
		return $folders;
	}

	/**
	 * Detect file mime content type from suffix
	 *
	 * @access private
	 * @param string
	 * @return string
	 */
	private function _mime_type($filename) 
	{
		$mime_types = array(
		
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);
		
		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) 
		{
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) 
		{
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else 
		{
			return 'application/octet-stream';
		}
	}

	/**
	 * Method to sort and list folder contents
	 *
	 * @access private
	 * @param string
	 * @param string
	 * @return string
	 */
	function _render_listing($path, $parent = 'direct')
	{
		$this->load->helper(array('directory', 'file', 'html'));
		
		// Given path defines item selection
		$selected_path = $path; 
		
		/*
		 * Verify if it's a file or a directory
		 * and assign proper variables
		 */
		$relative_path = '.' . $path;
		if ( is_file($relative_path) )
		{
			$relative_path = dirname($relative_path);
			$path = substr($relative_path, 1);
		}

		// List top level directories & files
		$map = directory_map($relative_path, 1);

		// Directories first
		$folders = array();
		foreach ( $map as $content )
		{
			if ( is_dir( $relative_path . '/' . $content ) )
			{
				$label = ( strlen($content) > 8 ) ? substr($content, 0, 8) . '...' : $content;
				$folders[] = array(
					'name' => $content,
					'label' => $label,
					'icon' => '/css/backend/directory.png',
					'path' => $path . '/' . $content,
					'class' => ( $path . '/' . $content == $selected_path ) ? 'directory current' : 'directory'
				);
			}
		}
		sort($folders);
		
		// Files
		$files = array();
		foreach ( $map as $content )
		{
			if ( ! is_dir( $relative_path . '/' . $content ) )
			{
				$label = ( strlen($content) > 8 ) ? substr($content, 0, 8) . '...' : $content;

				$mime_content_type = $this->_mime_type($relative_path . '/' . $content);

				$size = filesize($relative_path . '/' . $content);
				$attrs = array(
					'name' => $content,
					'label' => $label,
					'icon' => '/css/backend/file.png',
					'path' => $path . '/' . $content,
					'mime' => $mime_content_type,
					'size' => $this->_byte_convert($size),
					'class' => ( $path . '/' . $content == $selected_path ) ? 'file current' : 'file'
				);
				
				// Image info
				switch ( $mime_content_type )
				{
					case 'image/png' :
					case 'image/jpeg' :
					case 'image/gif' :
					list($width, $height) = getimagesize($relative_path . '/' . $content);
					$attrs['width'] = $width;
					$attrs['height'] = $height;
					
					// img filled tag
					$properties = array(
						'src' => $path . '/' . $content,
						'alt' => $content,
						'width' => $width,
						'height' => $height,
						'title' => $content
					);
					$attrs['img'] = img($properties, TRUE);
					
					// Thumbnail
					if ( ! file_exists( $relative_path . '/.thumbnails' ) )
					{
						mkdir($relative_path . '/.thumbnails');
					}
					if ( ! file_exists( $relative_path . '/.thumbnails/' . $content) )
					{
						$this->_resize_image($relative_path . '/' . $content, $relative_path . '/.thumbnails/' . $content, 48, 48);
					}
					if ( file_exists( $relative_path . '/.thumbnails/' . $content) )
					{
						$attrs['icon'] = substr($relative_path, 1, strlen($relative_path) - 1) . '/.thumbnails/' . $content;
					}
					break;
				}

				$files[] = $attrs;
			}
		}
		sort($files);

		// File listing
		$data = array(
			'listing' => array_merge($folders, $files)
		);

		// Store parent window caller element type
		$data['parent'] = $parent;
		
		// Localized texts
		$data['fm_type'] = $this->lang->line('elementar_fm_type');
		$data['fm_size'] = $this->lang->line('elementar_fm_size');
		$data['fm_dimensions'] = $this->lang->line('elementar_fm_dimensions');
		$data['fm_rename'] = $this->lang->line('elementar_fm_rename');
		$data['fm_erase'] = $this->lang->line('elementar_fm_erase');
		$data['fm_and_all_its_contents'] = $this->lang->line('elementar_fm_and_all_its_contents');
		$data['fm_insert_link'] = $this->lang->line('elementar_fm_insert_link');
		$data['fm_insert_picture'] = $this->lang->line('elementar_fm_insert_picture');
		$data['fm_insert'] = $this->lang->line('elementar_fm_insert');

		$html = $this->load->view('backend/backend_file_listing', $data, true);
		return $html;
	}
	
	/**
	 * Resample images
	 *
	 * @access private
	 * @param string
	 * @param string
	 * @param integer
	 * @param integer
	 * @return void
	 */
	function _resize_image ($from, $to, $width, $height)
	{
		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($from);
		
		// Smaller images are just copied
		if ( $width_orig <= $width || $height_orig <= $height )
		{
			copy($from, $to);
			return;
		}
		
		$ratio_orig = $width_orig / $height_orig;
		
		if ($width / $height > $ratio_orig) 
		{
			$width = $height * $ratio_orig;
		}
		else 
		{
			$height = $width / $ratio_orig;
		}
		
		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		
		$mime_content_type = $this->_mime_type($from);

		switch($mime_content_type)
		{
			case "image/jpeg":
			$image = imagecreatefromjpeg($from); //jpeg file
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagejpeg($image_p, $to);
			break;
			case "image/gif":
			imagealphablending($image_p, false);
			imagesavealpha($image_p, true);
			$image = imagecreatefromgif($from); //gif file
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagegif($image_p, $to);
			break;
			case "image/png":
			imagealphablending($image_p, false);
			imagesavealpha($image_p, true);
			$image = imagecreatefrompng($from); //png file
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagepng($image_p, $to);
			break;
		}

		imagedestroy($image);
		imagedestroy($image_p);
	}

	/**
	 * Human readable file size
	 * 
	 * @access private
	 * @author Alejandro Fernandez Moraga, Luciano Siqueira
	 * @param integer
	 * @param integer
	 * @param string
	 * @param string
	 * @return string
	 */
	private function _byte_convert($bytes, $precision = 2, $dec_point = ',', $thousands_sep = '' )
	{
		$units = array('', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$unit = 0;
	   
		do {
			$bytes /= 1024;
			$unit++;
		} while ($bytes > 1024);
	   
		return number_format($bytes, $precision, $dec_point, $thousands_sep).$units[$unit];
	}

	/**
	 * Render file upload form
	 * 
	 * @access public
	 * @return string
	 */
	function xhr_render_upload_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		/*
		 * Create an upload session to hold sent data
		 * before saving the content/element
		 */
		$upload_session_id = $this->storage->put_upload_session();
		
		// Target folder
		$path = $this->input->post("path", TRUE);
		
		// Upload form properties
		$attributes = array(
			'target' => "iframeUpload_" . $upload_session_id,
			'name' => "upload_form_" . $upload_session_id,
			'id' => "upload_form_" . $upload_session_id,
			'class' => "upload_form"
		);

		/*
		 * Hidden fields to hold the upload session id
		 * and the actual image field name (which will carry
		 * the sent image id)
		 */
		$hidden = array(
			'upload_session_id' => $upload_session_id,
			'path' => $path
		);
		
		// The URI to handle the upload
		$this->load->helper('form');
		$form = form_open_multipart("/backend/file/upload", $attributes, $hidden);
		
		// Open file field
		$attributes = array(
			'class' => 'upload_file',
			'size' => 5,
			'name' => "upload_file",
			'id' => "upload_file_" . $upload_session_id
		);
		
		// IE change event bug (to upload automatic after file selection)
		$this->load->library('user_agent');
		if( $this->agent->browser() == 'Internet Explorer' )
		{
			$attributes['onchange'] = 'this.form.submit(); this.blur();';
		}

		$form .= form_upload($attributes);
		
		// Close upload form
		$form .= form_close();
		
		$data = array(
			'upload_form' => $form,
			'upload_session_id' => $upload_session_id
		);

		// Localized texts
		$data['fm_choose_file'] = $this->lang->line('elementar_fm_choose_file');

		$html = $this->load->view('backend/backend_file_upload', $data, TRUE); 
		
		$response = array(
			'done' => TRUE,
			'html' => $html,
			'upload_session_id' => $upload_session_id
		);
		$this->output->set_output_json($response);
		
	}

	/**
	 * Receive file upload
	 *
	 * @access public
	 * @return void
	 */
	function upload()
	{
		// Receive the upload session ID and target path
		$upload_session_id = $this->input->post("upload_session_id", TRUE);
		$path = $this->input->post("path", TRUE);
		
		// Check size ( < 10 MB ) before proceed
		if ($_FILES["upload_file"]["size"] >= 10485760)
		{
			// Mark an error in upload session
			$this->storage->put_upload_session($upload_session_id, "error", TRUE);
			return;
		}
		
		if ($_FILES["upload_file"]["error"] > 0)
		{
			// Mark an error in upload session
			$this->storage->put_upload_session($upload_session_id, "error", TRUE);
			return;
		}

		// Receive file properties
		$tmp_name = $_FILES["upload_file"]["tmp_name"];
		$name = $_FILES["upload_file"]["name"];

		// Move file to upload dir
		move_uploaded_file($tmp_name, '.' . $path . '/' . $name);

		// Write file properties to upload session
		$uri = $path . '/' . $name;
		$this->storage->put_upload_session($upload_session_id, "name", $name);
		$this->storage->put_upload_session($upload_session_id, "uri", $uri);
		$this->storage->put_upload_session($upload_session_id, "done", TRUE);
	}

	/**
	 * Uploading status
	 *
	 * @access public
	 * @return void
	 */
	function xhr_read_upload_status()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$upload_session_id = $this->input->post('upload_session_id', TRUE);

		$done = $this->storage->get_upload_session_done($upload_session_id);
		
		if ( (bool) $done )
		{
			$uri = $this->storage->get_upload_session_uri($upload_session_id);
			$name = $this->storage->get_upload_session_name($upload_session_id);
			$label = ( strlen($name) > 8 ) ? substr($name, 0, 24) . '...' : $name;
			$data = array(
				'label' => $label,
				'uri' => $uri,
				'title' => $name,
				'icon' => '/css/backend/file.png'
			);
			$html = $this->load->view('backend/backend_file_uploaded', $data, TRUE); 

			$response = array(
				'done' => TRUE,
				'html' => $html
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE
			);
		}

		$this->output->set_output_json($response);
	}
	
	/**
	 * Cancel upload process
	 *
	 * @access public
	 * @return void
	 */
	function cancel_upload()
	{
		echo NULL;
	}

	/**
	 * Remove a file or directory
	 *
	 * @access public
	 * @return void
	 */
	function xhr_rm()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$path = $this->input->post("path", TRUE);
		$relative_path = '.' . $path;
		
		$parent_dir = dirname($relative_path);
		
		// File or directory
		if ( is_file($relative_path) )
		{
			if ( unlink($relative_path) )
			{
				$response = array(
					'done' => TRUE,
					'path' => substr($parent_dir, 1),
					'title' => basename($parent_dir)
				);
			}
			else
			{
				$response = array(
					'done' => FALSE
				);
			}
		}
		else
		{
			$this->load->helper('file');
			
			// Delete contents recursively
			delete_files($relative_path, TRUE);
			
			// Check for hidden thumbnaisl dir
			if ( file_exists($relative_path . '/' . '.thumbnails') )
			{
				delete_files($relative_path . '/' . '.thumbnails', TRUE);
				rmdir($relative_path . '/' . '.thumbnails');
			}
			if ( rmdir($relative_path) )
			{
				$response = array(
					'done' => TRUE,
					'path' => substr($parent_dir, 1),
					'title' => basename($parent_dir)
				);
			}
			else
			{
				$response = array(
					'done' => FALSE
				);
			}
		}

		$this->output->set_output_json($response);
	}

	/**
	 * Create a directory
	 *
	 * @access public
	 * @return void
	 */
	function xhr_mkdir()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$path = $this->input->post('path', TRUE);
		$relative_path = '.' . $path;
				
		$newdir = $this->input->post('newdir', TRUE);
		
		if ( mkdir($relative_path . '/' . $newdir) )
		{
			$response = array(
				'done' => TRUE
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'msg' => $this->lang->line('elementar_file_mkdir_error') . $newdir
			);
		}

		$this->output->set_output_json($response);
	}

	/**
	 * Rename file or directory
	 *
	 * @access public
	 * @return void
	 */
	function xhr_rename()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$name = $this->input->post('name', TRUE);

		$path = $this->input->post('path', TRUE);
		$relative_path = '.' . $path;			

		// Ignore empty name
		$name = ( trim($name) == '' ) ? basename($relative_path) : $name;

		if ( rename($relative_path, dirname($relative_path) . '/' . $name) )
		{
			$response = array(
				'title' => $name,
				'path' => dirname($path),
				'done' => TRUE
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'msg' => $this->lang->line('elementar_file_rename_error') . $name
			);
		}

		$this->output->set_output_json($response);
	}
}

/* End of file file.php */
/* Location: ./application/controllers/backend/file.php */
