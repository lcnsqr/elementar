<?php
/*
 *      editor.php
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
 * Backend Editor Class 
 * 
 * Content and template management
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Editor extends CI_Controller {

	// i18n settings
	var $LANG;
	var $LANG_AVAIL = array();

	function __construct()
	{
		parent::__construct();
		
		// Disable caching in backend
		$this->output->disable_cache();

		// CI helpers
		$this->load->helper(array('string', 'security', 'cookie', 'form', 'html', 'text', 'url'));
		
		// Elementar database
		$this->elementar = $this->load->database('elementar', TRUE);

		// Access model
		$this->load->model('Access', 'access');

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
		
		// Language related Settings
		$site_names = json_decode($this->storage->get_config('name'), TRUE);
		$this->config->set_item('site_name', (array_key_exists($this->LANG, $site_names)) ? $site_names[$this->LANG] : '');

		// Email settings
		$email_settings = json_decode($this->storage->get_config('email') ,TRUE);
		$this->load->library('email', $email_settings);
		$this->email->set_newline("\r\n");
	}
	
	/**
	 * Backend Editor main method
	 * 
	 * @access method
	 * @return void
	 */
	function index()
	{
		// Admin Account info
		$account_id = $this->session->userdata('account_id');
		$is_logged = TRUE;
		$username = $this->access->get_account_username($account_id);

		// Client controller (javascript files)
		$js = array(
			'/js/backend/jquery-1.8.3.min.js',
			'/js/backend/jquery.easing.1.3.js',
			'/js/backend/jquery.timers-1.2.js',
			'/js/backend/backend_account.js',
			'/js/backend/tiny_mce/jquery.tinymce.js',
			'/js/backend/backend_client_warning.js',
			'/js/backend/backend_content_tree.js',
			'/js/backend/backend_content_window.js',
			'/js/backend/backend_composite_field.js',
			'/js/backend/jquery.json-2.3.min.js',
			'/js/backend/backend_anchor.js',
			'/js/backend/jquery.cookie.js',
			'/js/backend/codemirror/lib/codemirror.js',
			'/js/backend/codemirror/mode/xml/xml.js',
			'/js/backend/codemirror/mode/css/css.js',
			'/js/backend/codemirror/mode/javascript/javascript.js'
		);
		
		// CSS for editor view
		$css = array(
			'/js/backend/codemirror/lib/codemirror.css'
		);
		
		
		// Top menu
		$resource_menu = array(
			anchor('/backend', $this->lang->line('elementar_settings'), array('title' => $this->lang->line('elementar_settings'))),
			span('&bull;', array('class' => 'top_menu_sep')),
			anchor('/backend/account', $this->lang->line('elementar_accounts'), array('title' => $this->lang->line('elementar_accounts'))),
			span('&bull;', array('class' => 'top_menu_sep')),
			'<strong>' . $this->lang->line('elementar_editor') . '</strong>'
		);

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'css' => $css,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => ul($resource_menu)
		);

		$data['parent_id'] = 1;
		$data['parent'] = $this->config->item('site_name');
		$data['content_hierarchy_content'] = $this->storage->get_contents_by_parent(1);
		$data['content_hierarchy_element'] = $this->storage->get_elements_by_parent(1);
		$data['content_listing_id'] = NULL;
		$data['content_listing'] = NULL;
		
		// Localized texts
		$data['elementar_exit'] = $this->lang->line('elementar_exit');
		$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
		$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
		$data['elementar_copyright'] = $this->lang->line('elementar_copyright');
		$data['elementar_edit'] = $this->lang->line('elementar_edit');
		$data['elementar_delete'] = $this->lang->line('elementar_delete');
		$data['elementar_and_associated'] = $this->lang->line('elementar_and_associated');
		$data['elementar_edit_content'] = $this->lang->line('elementar_edit_content');
		$data['elementar_edit_template'] = $this->lang->line('elementar_edit_template');
		$data['elementar_edit_meta'] = $this->lang->line('elementar_edit_meta');
		$data['elementar_new_content'] = $this->lang->line('elementar_new_content');
		$data['elementar_new_element'] = $this->lang->line('elementar_new_element');
		
		$this->load->view('backend/backend_content', $data);
	}
	
	/**
	 * Unfold tree items until the requested one
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_tree_unfold()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$id = $this->input->post('id', TRUE);
		$request = $this->input->post('request', TRUE);

		if ( (bool) $id && (bool) $request )
		{		
			// Reload navigation tree
			switch ( $request )
			{
				case "content" :
				$parent_id = $this->storage->get_content_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( $tree_id > 1 )
				{
					$parent_id = $this->storage->get_content_parent_id($tree_id);
					$tree = $this->_render_tree_listing($parent_id, $tree, $tree_id);
					$tree_id = $parent_id;
				}
				break;
				
				case "element" : 
				$parent_id = $this->storage->get_element_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( $tree_id > 1 )
				{
					$parent_id = $this->storage->get_content_parent_id($tree_id);
					$tree = $this->_render_tree_listing($parent_id, $tree, $tree_id);
					$tree_id = $parent_id;
				}
				break;
			}				
			
			$response = array(
				'done' => TRUE,
				'html' => $tree
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
		}
		$this->output->set_output_json($response);
	}

	/**
	 * New content type form
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_content_type_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
			
		$form = paragraph($this->lang->line('elementar_type_fields'), array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'content_type_define_new_form', 'id' => 'content_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		// Content type name
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_name'), 'name', $attributes);
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$input = form_input($attributes);
		$form .= backend_input_columns($label, $input);

		// Load template for field type
		$form .= div_open(array('id' => 'type_define_new_field_0', 'class' => 'type_define_new_field'));
		
		// Field name
		$label = form_label($this->lang->line('elementar_type_field_name'), "field_0");
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$input = form_input($attributes);
		$form .= backend_input_columns($label, $input);

		// Field type
		$label = form_label($this->lang->line('elementar_type_field_type'), "field_type_0");
		$input = $this->_render_field_type_dropdown();
		$form .= backend_input_columns($label, $input);

		// Close field template
		$form .= div_close("<!-- #type_define_new_field_0 -->");

		$form .= paragraph(anchor('add_type_field', '&rarr; ' . $this->lang->line('elementar_type_add_field'), array('id' => 'add_type_field')));
		
		// HTML template for content type
		$form .= paragraph($this->lang->line('elementar_type_markup'), array('class' => 'page_subtitle'));

		$label = form_label($this->lang->line('elementar_type_markup_template'), "template");
		$attributes = array(
			'name' => 'template',
			'id' => 'template',
			'class' => 'template_textarea',
			'rows' => 8,
			'cols' => 32,
			'value' => ''
		);
		$input = div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
		$form .= backend_input_columns($label, $input);

		$form .= div_open(array('class' => 'form_control_buttons'));

		$form .= form_submit('type_save', $this->lang->line('elementar_save'));
		
		$form .= div_close();

		$form .= form_close();
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);

		$this->output->set_output_json($response);
	}

	/**
	 * Element type creation form
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_element_type_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
			
		$form = paragraph($this->lang->line('elementar_type_element_new'), array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'element_type_define_new_form', 'id' => 'element_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		// Type name
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_name'), 'name', $attributes);
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$input = form_input($attributes);
		$form .= backend_input_columns($label, $input);
		
		// Field template
		$form .= div_open(array('id' => 'type_define_new_field_0', 'class' => 'type_define_new_field'));
		
		// Field name
		$label = form_label($this->lang->line('elementar_type_field_name'), "field_0");
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$input = form_input($attributes);
		$form .= backend_input_columns($label, $input);

		// Field type
		$label = form_label($this->lang->line('elementar_type_field_type'), "field_type_0");
		$input = $this->_render_field_type_dropdown();
		$form .= backend_input_columns($label, $input);

		// Close field template
		$form .= div_close("<!-- #type_define_new_field_0 -->");

		$form .= paragraph(anchor('add_type_field', '&rarr; ' . $this->lang->line('elementar_type_add_field'), array('id' => 'add_type_field')));
		
		$form .= div_open(array('class' => 'form_control_buttons'));

		$form .= form_submit('type_save', $this->lang->line('elementar_save'));
		
		$form .= div_close();

		$form .= form_close();
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);

		$this->output->set_output_json($response);
	}

	/**
	 * Filters for index field type
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_index_filter()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$content_id = $this->input->post('content_id', TRUE);
		
		$field_sname = $this->input->post('field_sname', TRUE);
		
		$html = $this->common->_render_index_field_form($field_sname, $content_id);
		
		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Choose type for content creation
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_content_new()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$parent_id = $this->input->post('id', TRUE);
		
		/*
		 * After creation of a new type, it's new id
		 * is posted to be selected by default
		 */
		$type_id = $this->input->post('type_id', TRUE);;
		
		// Content instance
		$this->load->library('content');

		// Set initial values
		$this->content->set_parent_id($parent_id);
		$this->content->set_type_id($type_id);

		$data = array();
		$data['content_id'] = NULL;
		$data['parent_id'] = $parent_id;
		$data['breadcrumb'] = $this->common->breadcrumb_content((int)$parent_id);
		$data['content_types_dropdown'] = $this->content->render_content_types_dropdown();
		
		// Localized texts
		$data['elementar_new_content_from_type'] = $this->lang->line('elementar_new_content_from_type');
		$data['elementar_proceed'] = $this->lang->line('elementar_proceed');

		$html = $this->load->view('backend/backend_content_new', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Choose type for element creation form
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_element_new()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Parent id
		$parent_id = $this->input->post('id', TRUE);

		/*
		 * After creation of a new type, it's new id
		 * is posted to be selected by default
		 */
		$type_id = $this->input->post('type_id', TRUE);;

		// Element library
		$this->load->library('element');

		// Set initial values
		$this->element->set_parent_id($parent_id);
		$this->element->set_type_id($type_id);

		$data = array();
		$data['element_id'] = NULL;
		$data['parent_id'] = $parent_id;
		$data['breadcrumb'] = $this->common->breadcrumb_content( (int) $parent_id );
		$data['element_types_dropdown'] = $this->element->render_element_types_dropdown();

		// Localized texts
		$data['elementar_new_element_from_type'] = $this->lang->line('elementar_new_element_from_type');
		$data['elementar_proceed'] = $this->lang->line('elementar_proceed');
		
		$html = $this->load->view('backend/backend_content_element_new', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Element creation/editing form
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_element_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming element ID
		$id = $this->input->post('id', TRUE);

		// View data array
		$data = array();

		// Element library
		$this->load->library('element');

		if ( (bool) $id === TRUE ) 
		{
			// Update element
			$this->element->set_id($id);

			// Check if its real
			if ( ! (bool) $this->element->exists() )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_bad_request')
				);
				$this->output->set_output_json($response);
				return NULL;
			}

			// Load values from database
			$this->element->load();

			$data['breadcrumb'] = $this->common->breadcrumb_element($id);
		}
		else
		{
			// Create
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
			
			// Set initial values
			$this->element->set_parent_id($parent_id);
			$this->element->set_type_id($type_id);

			$data['breadcrumb'] = $this->common->breadcrumb_element((int)$parent_id);
		}
		
		// HTML rendered form
		$form = "";

		// Error exit if type_id not present
		if ( ! (bool) $this->element->get_type_id() ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
			return NULL;
		}

		// Element ID (if any, hidden)
		$attributes = array(
			'class' => 'noform',
			'name' => 'id',
			'value'=> $this->element->get_id(),
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		// Element parent_id (hidden)
		$attributes = array(
			'class' => 'noform',
			'name' => 'parent_id',
			'value'=> $this->element->get_parent_id(),
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		// Element type id (hidden)
		$attributes = array(
			'class' => 'noform',
			'name' => 'type_id',
			'value'=> $this->element->get_type_id(),
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		// Element name
		if ( (bool) $this->element->get_id() )
		{
			$value = $this->element->get_name();
		}
		else
		{
			// Generate a default name for element
			$value = json_encode(array($this->LANG => $this->element->get_default_name()));
		}

		/*
		 * Caller entity for common library
		 */
		$this->common->set_caller_entity('element');
		
		$form .= $this->common->render_form_field('name', $this->lang->line('elementar_name'), 'name', NULL, $value, TRUE);

		// Element type fields
		foreach ( $this->element->get_type_fields() as $field )
		{
			// Field value
			$value = $this->element->get_field($field['id']);
			$form .= $this->common->render_form_field($field['type'], $field['name'], $field['sname'], NULL, $value, $field['i18n']);
		}

		// Spread
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_element_spread'), "spread", $attributes);
		$attributes = array(
			'name' => 'spread',
			'id' => 'spread',
			'class' => 'noform',
			'value' => 'true',
			'checked' => $this->element->get_spread()
		);
		$input = form_checkbox($attributes);
		$form .= backend_input_columns($label, $input);

		// status
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_status'), NULL, $attributes);
		$input = $this->_render_status_dropdown($this->element->get_status());
		$form .= backend_input_columns($label, $input);

		// Save button
		$form .= div_open(array('class' => 'form_control_buttons'));
		$attributes = array(
		    'name' => 'button_element_save',
		    'id' => 'button_element_save',
		    'class' => 'noform',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);
		$form .= div_close();
		
		$data['element_form'] = $form;
		
		$html = $this->load->view('backend/backend_content_element_form', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);

		$this->output->set_output_json($response);
	}

	/**
	 * Render content associated variable 
	 * tags for insertion in template
	 * 
	 * @access private
	 * @param integer
	 * @return string
	 */ 
	private function _render_variables($content_id)
	{
		// Load template element filters (if any)
		$content_type_id = $this->storage->get_content_type_id($content_id);
		$template_id = $this->storage->get_content_template_id($content_id);
		$template_filter = json_decode($this->storage->get_template_filter($template_id), TRUE);
		
		// Template pseudo variables available for this content
		$title = json_decode($this->storage->get_content_name($content_id), TRUE);
		$template_variables = array(
			'template_id' => $template_id,
			'type_name' => $this->storage->get_content_type_name($this->storage->get_content_type_id($content_id)),
			'elementar_template_variables_title' => $this->lang->line('elementar_template_variables_title'),
			'content_variables_title' => (array_key_exists($this->LANG, $title)) ? $title[$this->LANG] : '',
			'content_variables' => array(),
			'relative_content_variables_title' => $this->lang->line('elementar_contents'),
			'relative_content_variables' => array(),
			'element_variables_title' => $this->lang->line('elementar_elements'),
			'element_variables' => array(),
			'filter_of' => $this->lang->line('elementar_filter_of'),
			'filter_at' => $this->lang->line('elementar_filter_at'),
			'filter_order_by' => $this->lang->line('elementar_filter_order_by'),
			'filter_limit' => $this->lang->line('elementar_filter_limit'),
			'filter_select' => $this->lang->line('elementar_filter_select'),
			'filter_save' => $this->lang->line('elementar_filter_save'),
			'filter_insert' => $this->lang->line('elementar_filter_insert')
		);
		
		// Default single variables
		$template_variables['content_variables'][] = array(
			'sname' => '{name}',
			'name' => 'Name'
		);
		$template_variables['content_variables'][] = array(
			'sname' => '{breadcrumb}',
			'name' => 'Breadcrumb'
		);
		
		// Content single variables
		$type_id = $this->storage->get_content_type_id($content_id);
		foreach ( $this->storage->get_content_type_fields($type_id) as $content_field )
		{
			// Some variables have different template syntax
			switch ( $content_field['type'] )
			{
				case 'index' :
				$template_variables['content_variables'][] = array(
					'sname' => '{index}{' . $content_field['sname'] . '}{/index}',
					'name' => $content_field['name']
				);
				break;

				default :
				$template_variables['content_variables'][] = array(
					'sname' => '{' . $content_field['sname'] . '}',
					'name' => $content_field['name']
				);
				break;
			}
		}
		
		// There are two "types" of relative contents: children and brother 
		if ( $this->storage->get_content_has_children($content_id, FALSE) )
		{
			// Children contents
			if ( ! isset($template_variables['relative_content_variables']['children'] ) )
			{
				// Variable pair with element type fields
				$pair = '{children}'  . "\n" ;
				$pair .= "\t" . '{name}' . "\n";
				$pair .= "\t" . '{sname}' . "\n";
				$pair .= "\t" . '{uri}' . "\n";
				$pair .= '{/children}'  . "\n" ;
				$template_variables['relative_content_variables']['children'] = array(
					'pair' => urlencode($pair)
				);
			}
		}
		
		// if not parent, render content brothers
		if ( $content_id != 1 )
		{
			$parent_id = $this->storage->get_content_parent_id($content_id);
			if ( $this->storage->get_content_has_children($parent_id, FALSE) )
			{
				// Dont set if singleton (only child)
				if ( count($this->storage->get_contents_by_parent($parent_id)) > 1 )
				{
					// Brother contents
					if ( ! isset($template_variables['relative_content_variables']['brothers'] ) )
					{
						// Variable pair with element type fields
						$pair = '{brothers}'  . "\n" ;
						$pair .= "\t" . '{name}' . "\n";
						$pair .= "\t" . '{sname}' . "\n";
						$pair .= "\t" . '{uri}' . "\n";
						$pair .= '{/brothers}'  . "\n" ;
						$template_variables['relative_content_variables']['brothers'] = array(
							'pair' => urlencode($pair)
						);
					}
				}
			}
		}

		// Available elements variables
		foreach ( $this->storage->get_elements_by_parent_spreaded($content_id) as $element )
		{
			if ( ! isset($template_variables['element_variables'][$element['type_name']] ) )
			{
				// Check for element filter in template and render filter form
				if ( (bool) count($template_filter) && array_key_exists($element['type'], $template_filter) )
				{
					$order_by = $template_filter[$element['type']]['order_by'];
					$direction = $template_filter[$element['type']]['direction'];
					$limit = $template_filter[$element['type']]['limit'];
				}
				else
				{
					// Default rules for unavailable element filter
					$order_by = 'created';
					$direction = 'desc';
					$limit = '';
				}

				// Element type fields
				$element_fields = $this->storage->get_element_type_fields($element['type_id']);
				
				// Element filter form
				$filter_form = array(
					'order_by' => array(
						'created' => array(
							'name' => $this->lang->line('elementar_filter_created'),
							'selected' => ( $order_by == 'created' ) ? TRUE : FALSE
						),
						'modified' => array(
							'name' => $this->lang->line('elementar_filter_modified'),
							'selected' => ( $order_by == 'modified' ) ? TRUE : FALSE
						),
						'name' => array(
							'name' => $this->lang->line('elementar_filter_name'),
							'selected' => ( $order_by == 'name' ) ? TRUE : FALSE
						)
					),
					'direction' => $direction,
					'limit' => $limit,
					'insert' => array(
						'created' => array(
							'name' => $this->lang->line('elementar_filter_created')
						),
						'modified' => array(
							'name' => $this->lang->line('elementar_filter_modified')
						),
						'name' => array(
							'name' => $this->lang->line('elementar_filter_name')
						)
					)
				);
				
				// Add element type fields to filter form
				foreach ( $element_fields as $element_field )
				{
					$filter_form['order_by'][$element_field['sname']] = array(
						'name' => $element_field['name'],
						'selected' => ( $order_by == $element_field['sname'] ) ? TRUE : FALSE
					);
					$filter_form['insert'][$element_field['sname']] = array(
						'name' => $element_field['name']
					);
				}

				// Element type variable pair
				$template_variables['element_variables'][$element['type_name']] = array(
					'sname' => $element['type'],
					'filter_form' => $filter_form,
					'elements' => array()
				);
			}
			
			// Join element fields for exclusive insert
			$fields = '';
			$fields .= '{' . $element['sname'] . '.name}' . "\n";
			$fields .= '{' . $element['sname'] . '.sname}' . "\n";
			foreach ( $this->storage->get_element_fields($element['id']) as $element_field )
			{
				$fields .= '{' . $element['sname'] . '.' . $element_field['sname'] . '}' . "\n";
			}
			$names = json_decode($element['name'], TRUE);
			$name = (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '';
			$template_variables['element_variables'][$element['type_name']]['elements'][] = array(
				'sname' => urlencode($fields),
				'name' => $name
			);
		}
		return $this->load->view('backend/backend_content_form_variables', $template_variables, true);
	}

	/**
	 * Render form for content creation or editing
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_content_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming content ID
		$id = $this->input->post('id', TRUE);
		$data = array();

		// Which editor to display
		$data['editor'] = $this->input->post('editor', TRUE);

		// Content instance
		$this->load->library('content');
		$this->content->set_id($id);

		if ( (bool) $this->content->get_id() ) 
		{
			// check if its real
			if ( ! (bool) $this->content->exists() )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_bad_request')
				);
				$this->output->set_output_json($response);
				return NULL;
			}

			// Load values from database
			$this->content->load();

			$data['breadcrumb'] = $this->common->breadcrumb_content($id);
		}
		else
		{
			// Create
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);

			// Set initial values
			$this->content->set_parent_id($parent_id);
			$this->content->set_type_id($type_id);
			$this->content->set_template_id($this->storage->get_content_type_template_id($type_id));
			$this->content->set_template($this->storage->get_content_type_template($type_id));
			$template = $this->storage->get_content_type_template($type_id);
			$this->content->set_template_html($template['html']);
			$this->content->set_template_css($template['css']);
			$this->content->set_template_javascript($template['javascript']);
			$this->content->set_template_head($template['head']);

			$data['breadcrumb'] = $this->common->breadcrumb_content((int)$parent_id);
		}

		// Content main form
		$content_form = "";
		
		if ( ! (bool) $this->content->get_type_id() ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
			return NULL;
		}
		$attributes = array(
			'class' => 'noform',
			'name' => 'content_id',
			'value'=> $this->content->get_id(),
			'type' => 'hidden'
		);
		$content_form .= form_input($attributes);

		$attributes = array(
			'class' => 'noform',
			'name' => 'type_id',
			'value'=> $this->content->get_type_id(),
			'type' => 'hidden'
		);
		$content_form .= form_input($attributes);

		$attributes = array(
			'class' => 'noform',
			'name' => 'parent_id',
			'value'=> $this->content->get_parent_id(),
			'type' => 'hidden'
		);
		$content_form .= form_input($attributes);

		/*
		 * Caller entity for common library
		 */
		$this->common->set_caller_entity('content');
		
		// Content name
		$content_form .= $this->common->render_form_field('name', $this->lang->line('elementar_name'), 'name', NULL, $this->content->get_name(), TRUE);

		// Render custom fields
		$fields = $this->storage->get_content_type_fields($this->content->get_type_id());
		foreach ( $fields as $field )
		{
			// Field value
			$value = $this->content->get_field($field['id']);
			$content_form .= $this->common->render_form_field($field['type'], $field['name'], $field['sname'], NULL, $value, $field['i18n']);
		}

		// status
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_status'), NULL, $attributes);
		$input = $this->_render_status_dropdown($this->content->get_status());
		$content_form .= backend_input_columns($label, $input);

		$content_form .= div_open(array('class' => 'form_control_buttons'));
		
		// Save button
		$attributes = array(
		    'name' => 'button_content_save',
		    'id' => 'button_content_save',
		    'class' => 'noform',
		    'content' => $this->lang->line('elementar_save')
		);
		$content_form .= form_button($attributes);

		$content_form .= div_close("<!-- form_control_buttons -->");
		
		$data['content_form'] = $content_form;

		// Show template tab only to existing contents
		$data['show_tabs'] = (bool) $this->content->get_id();

		if ( $data['show_tabs'] )
		{
			$data['template_form'] = $this->_render_template_form();
			$data['meta_form'] = $this->_render_meta_form();
		}
		
		// Localized texts
		$data['elementar_editor_content'] = $this->lang->line('elementar_editor_content');
		$data['elementar_editor_template'] = $this->lang->line('elementar_editor_template');
		$data['elementar_editor_meta'] = $this->lang->line('elementar_editor_meta');
		
		$html = $this->load->view('backend/backend_content_form', $data, true);
		
		$response = array(
			'done' => TRUE,
			'html' => $html
		);

		$this->output->set_output_json($response);
	}
	
	/**
	 * Render the template form for a content
	 *
	 * @access private
	 * @return string
	 */
	private function _render_template_form()
	{
		// Template editor
		$template_form = '';
		/*
		$attributes = array('class' => 'template_form', 'id' => 'template_form_' . $content_id);
		$hidden = array('template_id' => $template_id, 'content_id' => $content_id);
		$template_form .= form_open('/backend/content/xhr_write_template', $attributes, $hidden);
		*/

		$attributes = array(
			'class' => 'noform',
			'name' => 'template_id',
			'value'=> $this->content->get_template_id(),
			'type' => 'hidden'
		);
		$template_form .= form_input($attributes);

		$attributes = array(
			'class' => 'noform',
			'name' => 'content_id',
			'value'=> $this->content->get_id(),
			'type' => 'hidden'
		);
		$template_form .= form_input($attributes);

		// Show Sole template checkbox (if not home)
		if ( $this->content->get_id() != 1 )
		{
			$attributes = array('class' => 'field_label');
			$label = form_label($this->lang->line('elementar_template_sole'), "sole", $attributes);
			if ( (bool) $this->content->get_id() ) 
			{
				$checked = $this->storage->get_content_type_template_id($this->content->get_type_id()) != $this->content->get_template_id() ;
			}
			else 
			{
				$checked = FALSE;
			}
			$attributes = array(
				'name'        => 'template_sole',
				'id'          => 'template_sole_' . $this->content->get_id(),
				'class' => 'template_form noform',
				'value'       => 'true',
				'checked'     => (bool) $checked
			);
			$input = form_checkbox($attributes);
			$input .= '<label class="template_confirm_overwrite" for="' . 'template_sole_' . $this->content->get_id() . '">' . $this->lang->line('elementar_template_confirm_overwrite') . '</label>';
			$template_form .= backend_input_columns($label, $input);
		}
		
		// Template pseudo variables available for this content
		$variables = $this->_render_variables($this->content->get_id());

		// HTML Template editor
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_markup_template'), 'template_' . $this->content->get_id(), $attributes);
		$template_menu = ul(array(
			anchor('add_file_uri', $this->lang->line('elementar_add_file_uri'), array('class' => 'template_menu add_file_uri', 'data-identifier' => 'template_' . $this->content->get_id()))
		), array('class' => 'template_actions'));
		// Include variables section
		$input = $variables;			
		$attributes = array(
			'name' => 'template',
			'class' => 'template_textarea noform',
			'id' => 'template_' . $this->content->get_id(),
			'rows' => 16,
			'cols' => 32,
			'value' => $this->content->get_template_html()
		);
		$input .= div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
		$template_form .= backend_input_columns($label.$template_menu, $input);

		// CSS editor
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_css'), 'css_' . $this->content->get_id(), $attributes);
		$template_menu = ul(array(
			anchor('add_file_uri', $this->lang->line('elementar_add_file_uri'), array('class' => 'template_menu add_file_uri', 'data-identifier' => 'css_' . $this->content->get_id()))
		), array('class' => 'template_actions'));
		$attributes = array(
			'name' => 'css',
			'class' => 'css_textarea noform',
			'id' => 'css_' . $this->content->get_id(),
			'rows' => 16,
			'cols' => 32,
			'value' => $this->content->get_template_css()
		);
		$input = div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
		$template_form .= backend_input_columns($label.$template_menu, $input);

		// Javascript editor
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_javascript'), 'css_' . $this->content->get_id(), $attributes);
		$template_menu = ul(array(
			anchor('add_file_uri', $this->lang->line('elementar_add_file_uri'), array('class' => 'template_menu add_file_uri', 'data-identifier' => 'javascript_' . $this->content->get_id()))
		), array('class' => 'template_actions'));
		$attributes = array(
			'name' => 'javascript',
			'class' => 'javascript_textarea noform',
			'id' => 'javascript_' . $this->content->get_id(),
			'rows' => 16,
			'cols' => 32,
			'value' => $this->content->get_template_javascript()
		);
		$input = div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
		$template_form .= backend_input_columns($label.$template_menu, $input);

		// Extra Head editor
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_type_extra_head'), 'head_' . $this->content->get_id(), $attributes);
		$template_menu = ul(array(
			anchor('add_file_uri', $this->lang->line('elementar_add_file_uri'), array('class' => 'template_menu add_file_uri', 'data-identifier' => 'head_' . $this->content->get_id()))
		), array('class' => 'template_actions'));
		$attributes = array(
			'name' => 'head',
			'class' => 'head_textarea noform',
			'id' => 'head_' . $this->content->get_id(),
			'rows' => 16,
			'cols' => 32,
			'value' => $this->content->get_template_head()
		);
		$input = div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
		$template_form .= backend_input_columns($label.$template_menu, $input);

		$template_form .= div_open(array('class' => 'form_control_buttons'));
		$attributes = array(
			'name' => 'button_template_save',
			'id' => 'button_template_save',
			'value' => $this->lang->line('elementar_save')
		);
		$template_form .= form_submit($attributes);
		$template_form .= div_close("<!-- form_control_buttons -->");
		/*
		$template_form .= form_close();
		*/
		return $template_form;
	}

	/**
	 * Render the meta fields form for a content
	 *
	 * @access private
	 * @return string
	 */
	private function _render_meta_form()
	{
		// Meta fields editor tab
		$meta_form = '';
		$attributes = array(
			'class' => 'noform',
			'name' => 'id',
			'value'=> $this->content->get_id(),
			'type' => 'hidden'
		);
		$meta_form .= form_input($attributes);
		
		// Meta fields
		$fields = array(
			array(
				'name' => 'keywords',
				'label' => $this->lang->line('elementar_meta_keywords'),
				'type' => 'line'
			),
			array(
				'name' => 'description',
				'label' => $this->lang->line('elementar_meta_description'),
				'type' => 'textarea'
			),
			array(
				'name' => 'author',
				'label' => $this->lang->line('elementar_meta_author'),
				'type' => 'line'
			),
			array(
				'name' => 'copyright',
				'label' => $this->lang->line('elementar_meta_copyright'),
				'type' => 'line'
			)
		);
		
		foreach ( $fields as $field )
		{
			$value = html_entity_decode($this->storage->get_meta_field($this->content->get_id(), $field['name']), ENT_QUOTES, "UTF-8");
			$meta_form .= $this->common->render_form_field($field['type'], $field['label'], $field['name'], NULL, $value, TRUE);
		}

		// Priority
		$attributes = array('class' => 'field_label');
		$label = form_label($this->lang->line('elementar_meta_priority'), 'priority', $attributes);
		$values = array(
			'0.0' => '0.0',
			'0.1' => '0.1',
			'0.2' => '0.2',
			'0.3' => '0.3',
			'0.4' => '0.4',
			'0.5' => '0.5',
			'0.6' => '0.6',
			'0.7' => '0.7',
			'0.8' => '0.8',
			'0.9' => '0.9',
			'1.0' => '1.0'
		);
		$value = $this->storage->get_meta_field($this->content->get_id(), 'priority');
		$selected = ( (bool) $value ) ? $value : '0.5';
		$input = form_dropdown('priority', $values, $selected , 'class="noform" id="priority"');
		$meta_form .= backend_input_columns($label, $input);

		// Save button
		$meta_form .= div_open(array('class' => 'form_control_buttons'));
		$attributes = array(
			'name' => 'button_meta_save',
			'id' => 'button_meta_save',
			'class' => 'noform',
			'content' => $this->lang->line('elementar_save')
		);
		$meta_form .= form_button($attributes);
		$meta_form .= div_close();
		return $meta_form;
	}
	
	/**
	 * Content/element status HTML dropdown
	 * 
	 * @access private
	 * @param string
	 * @return string
	 */
	private function _render_status_dropdown($selected = "draft")
	{
		$options = array(
			"draft" => $this->lang->line('elementar_draft'),
			"published" => $this->lang->line('elementar_published')
		); 
		$attributes = "id=\"new_content_status\" class=\"noform\"";
		return form_dropdown('status', $options, $selected, $attributes);
	}

	/**
	 * Field type HTML dropdown
	 * 
	 * @access private
	 * @param integer
	 * @return string
	 */
	private function _render_field_type_dropdown($selected = "1")
	{
		$options = array();
		foreach ( $this->storage->get_field_types() as $option )
		{
			$options[$option['id']] = $this->lang->line('elementar_field_type_' . $option['sname']);
		}
		$attributes = "id=\"field_type_0\"";
		return form_dropdown('field_type_0', $options, $selected, $attributes);
	}

	/**
	 * Save new content type
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_content_type()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$count = $this->input->post('field_count', TRUE);
		$template = $this->input->post('template');
		$name = $this->input->post('name', TRUE);		
		$template_id = $this->storage->put_template_html(NULL, $template);
		$type_id = $this->storage->put_content_type($name, $template_id);
		
		if ( (bool) $type_id )
		{
			// Store type fields
			for ( $c = 0; $c < $count; $c++)
			{
				$field = $this->input->post("field_" . $c, TRUE);
				$sname = $this->common->normalize_string($field);
				$field_type = $this->input->post("field_type_" . $c, TRUE);
				if ( $field != "" )
				{
					$this->storage->put_content_type_field($type_id, $field, $sname, $field_type);
				}
			}
			
			// response
			$response = array(
				'done' => TRUE,
				'type_id' => $type_id,
				'message' => $this->lang->line('elementar_xhr_write_content_type')
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
		}
		$this->output->set_output_json($response);
	}

	/**
	 * Save new element type
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_element_type()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$count = $this->input->post('field_count', TRUE);
		
		$name = $this->input->post('name', TRUE);

		$sname = $this->common->normalize_string($name);

		$type_id = $this->storage->put_element_type($name, $sname);
		
		if ( (bool) $type_id )
		{
			// Store type fields
			for ( $c = 0; $c < $count; $c++)
			{
				$field = $this->input->post("field_" . $c, TRUE);
				$sname = $this->common->normalize_string($field);
				$field_type = $this->input->post("field_type_" . $c, TRUE);
				if ( $field != "" )
				{
					$this->storage->put_element_type_field($type_id, $field, $sname, $field_type);
				}
			}
			
			// Response
			$response = array(
				'done' => TRUE,
				'type_id' => $type_id,
				'message' => $this->lang->line('elementar_xhr_write_element_type')
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
		}
		$this->output->set_output_json($response);
	}

	/**
	 * Save/update content
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Content sname determined by the default language
		$sname = $this->common->normalize_string($this->input->post('name_' . $this->LANG, TRUE));

		if ( $sname == "" )
		{
			// Invalid sname, return an error
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_return_name_error')
			);
			$this->output->set_output_json($response);
			return NULL;
		}

		// Content instance
		$this->load->library('content');
		$this->content->set_sname($sname);
		
		/*
		 * For content name saving,
		 * Group each language's value 
		 * on a array before saving
		 */
		$values = array();
		foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
		{
			$values[$lang_code] = htmlentities($this->input->post('name_' . $lang_code, TRUE), ENT_QUOTES, "UTF-8");
		}
		$name = json_encode($values);
		$this->content->set_name($name);
		
		// Locate content type
		$this->content->set_type_id($this->input->post('type_id', TRUE));

		// Content's Parent
		$this->content->set_parent_id($this->input->post('parent_id', TRUE));

		// Content Status
		$this->content->set_status($this->input->post('status', TRUE));

		// Locate content ID
		$content_id = $this->input->post('id', TRUE);

		if ( (bool) $content_id )
		{
			// Content rewrite
			$this->content->set_id($content_id);
			
			// check if its real
			if ( ! (bool) $this->content->exists() )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_write_content_error')
				);
				$this->output->set_output_json($response);
				return NULL;
			}
		}

		// Save new or rewrite content (main fields)
		$this->content->save();
		
		// Store content fields based on it's type
		foreach ( $this->content->get_type_fields() as $field)
		{
			// Check for multilanguage field
			if ( (bool) $field['i18n'] )
			{
				// Group each language's value on a array before saving
				$values = array();
				foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
				{
					$values[$lang_code] = $this->input->post($field['sname'] . '_' . $lang_code, FALSE);
				}
				$value = json_encode($values);
			}
			else
			{
				// Not multilanguage
				$value = $this->input->post($field['sname'], FALSE);
			}
			$this->content->set_field($field['id'], $value);
		}
		
		// Erase cached content
		$this->content->erase_cache();
		
		// Return ajax response
		$response = array(
			'done' => TRUE,
			'content_id' => $this->content->get_id(),
			'message' => $this->lang->line('elementar_xhr_write_content')
		);
		$this->output->set_output_json($response);

	}

	/**
	 * Remove content
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_erase_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$content_id = $this->input->post('id', TRUE);

		// Content library
		$this->load->library('content');
		$this->content->set_id($content_id);

		// Erase cached content
		$this->content->erase_cache();

		// remove content
		$this->content->delete();

		// Answer to client
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_erase_content')
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Remove element
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_erase_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$element_id = $this->input->post('id', TRUE);

		// Element library
		$this->load->library('element');
		$this->element->set_id($element_id);

		// Erase previous cached content
		$this->element->erase_cache();

		// remove element
		$this->element->delete();

		// answer to client
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_erase_element')
		);
		
		$this->output->set_output_json($response);
	}

	/**
	 * Write element parent
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_element_parent()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Element id
		$element_id = $this->input->post('child_id', TRUE);

		// Element instance
		$this->load->library('element');
		$this->element->set_id($element_id);
		$this->element->load();

		// Parent id
		$parent_id = $this->input->post('parent_id', TRUE);

		// check if its real
		if ( ! (bool) $this->storage->get_content_status($parent_id) || ! (bool) $this->element->exists() )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_write_element_error')
			);
			$this->output->set_output_json($response);
			return;
		}

		if ( (bool) $parent_id && (bool) $element_id && ( $parent_id != $element_id ) )
		{
			// Erase previous cached content
			$this->element->erase_cache();
		
			$this->element->set_parent_id($parent_id);
			$this->element->save();

			// Erase new cached content
			$this->element->erase_cache();

			$response = array(
				'done' => TRUE
			);
			$this->output->set_output_json($response);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
		}

	}

	/**
	 * Write content parent
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_content_parent()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Content id
		$content_id = $this->input->post('child_id', TRUE);
		
		// Content instance
		$this->load->library('content');
		$this->content->set_id($content_id);
		$this->content->load();

		// Parent id
		$parent_id = $this->input->post('parent_id', TRUE);

		// check if its real
		if ( ! (bool) $this->storage->get_content_status($parent_id) || ! (bool) $this->content->exists() )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_write_content_error')
			);
			$this->output->set_output_json($response);
			return;
		}

		// Avoid placing into own children
		$above_id = $this->storage->get_content_parent_id($parent_id);
		while ( (bool) $above_id )
		{
			if ( $content_id == $above_id )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_not_allowed')
				);
				$this->output->set_output_json($response);
				return;
			}
			$above_id = $this->storage->get_content_parent_id($above_id);
		}

		if ( (bool) $parent_id && (bool) $content_id && ( $parent_id != $content_id ) )
		{
			// Erase old cached content
			$this->content->erase_cache();

			$this->content->set_parent_id($parent_id);
			$this->content->save();

			// Erase existing cached content
			$this->content->erase_cache();

			$response = array(
				'done' => TRUE
			);
			$this->output->set_output_json($response);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
		}

	}

	/**
	 * Save element
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Group each language's value on a array before saving
		$names = array();
		foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
		{
			$names[$lang_code] = $this->input->post('name' . '_' . $lang_code, TRUE);
		}
		$name = json_encode($names);

		// Element sname built from the default language's name
		$sname = $this->common->normalize_string($names[$this->LANG]);

		if ( (bool) $sname === false )
		{
			// Invalid sname, return an error
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_return_name_error')
			);
			$this->output->set_output_json($response);
			return NULL;
		}

		// Element instance
		$this->load->library('element');
		$this->element->set_sname($sname);
		
		// Element name are not multilanguage
		$this->element->set_name($name);
		
		// Locate element type
		$this->element->set_type_id($this->input->post('type_id', TRUE));

		// Element's Parent
		$this->element->set_parent_id($this->input->post('parent_id', TRUE));

		// Spread option
		$this->element->set_spread( (bool) $this->input->post('spread', TRUE) );

		// Element Status
		$this->element->set_status($this->input->post('status', TRUE));

		// Locate element ID
		$element_id = $this->input->post('id', TRUE);
		
		if ( (bool) $element_id )
		{
			// Element rewrite
			$this->element->set_id($element_id);
			
			// check if its real
			if ( ! (bool) $this->element->exists() ) 
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_write_element_error')
				);
				$this->output->set_output_json($response);
				return NULL;
			}
		}

		// Save new or rewrite element (main fields)
		$this->element->save();

		// Store element fields based on it's type
		foreach ( $this->element->get_type_fields() as $field)
		{
			// Check for multilanguage field
			if ( (bool) $field['i18n'] )
			{
				// Group each language's value on a array before saving
				$values = array();
				foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
				{
					$values[$lang_code] = $this->input->post($field['sname'] . '_' . $lang_code, FALSE);
				}
				$value = json_encode($values);
			}
			else
			{
				// Not multilanguage
				$value = $this->input->post($field['sname'], FALSE);
			}
			$this->element->set_field($field['id'], $value);
		}
		
		// Erase cached content
		$this->element->erase_cache();
		
		// Ajax response
		$response = array(
			'done' => TRUE,
			'element_id' => $this->element->get_id(),
			'message' => $this->lang->line('elementar_xhr_write_element')
		);
		$this->output->set_output_json($response);
	}

	/**
	 * List content/element tree up to the requested one
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_tree_listing()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		if ( $this->input->post('id') )
		{
			$id = $this->input->post('id');
		}
		else
		{
			$id = NULL;
		}

		$html = $this->_render_tree_listing($id);

		$response = array(
			'done' => TRUE,
			'id' => $id,
			'html' => $html
		);
		$this->output->set_output_json($response);		
	}
	
	/**
	 * Render each content tree listing recursively
	 * 
	 * @access private
	 * @param integer
	 * @param string
	 * @param integer
	 * @return void
	 */
	private function _render_tree_listing($id, $listing = NULL, $listing_id = NULL)
	{
		$data['parent_id'] = $id;
		
		// Set default language for view
		$data['lang'] = $this->LANG;
		
		$data['content_hierarchy_content'] = $this->storage->get_contents_by_parent($id);
		$data['content_hierarchy_element'] = $this->storage->get_elements_by_parent($id);
		
		// Inner listings, if any
		$data['content_listing_id'] = $listing_id;
		$data['content_listing'] = $listing;
		
		// Localized texts
		$data['elementar_edit'] = $this->lang->line('elementar_edit');
		$data['elementar_delete'] = $this->lang->line('elementar_delete');
		$data['elementar_and_associated'] = $this->lang->line('elementar_and_associated');
		$data['elementar_edit_content'] = $this->lang->line('elementar_edit_content');
		$data['elementar_edit_template'] = $this->lang->line('elementar_edit_template');
		$data['elementar_edit_meta'] = $this->lang->line('elementar_edit_meta');
		$data['elementar_new_content'] = $this->lang->line('elementar_new_content');
		$data['elementar_new_element'] = $this->lang->line('elementar_new_element');

		
		$html = $this->load->view('backend/backend_content_tree', $data, true);
		
		return $html;
	}

	/**
	 * Save meta fields
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_meta() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$id = $this->input->post('id', TRUE);
		
		// check if its real
		if ( ! (bool) $this->storage->get_content_status($id) )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_write_content_error')
			);
			$this->output->set_output_json($response);
			return;
		}

		// Meta fields
		$fields = array(
			'keywords',
			'description',
			'author',
			'copyright'
		);

		foreach ( $fields as $name )
		{
			$values = array();
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				$values[$lang_code] = htmlentities($this->input->post($name . '_' . $lang_code, TRUE, ENT_QUOTES, "UTF-8"));
			}
			$value = json_encode($values);

			$this->storage->put_meta_field($id, $name, $value);
		}
		
		$priority = $this->input->post('priority', TRUE);
		$this->storage->put_meta_field($id, 'priority', $priority);
		
		// Erase cached content
		$this->load->library('content');
		$this->content->set_id($id);
		$this->content->load();
		$this->content->erase_cache();
		
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_write_meta')
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Save template
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_template()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$content_id = $this->input->post('content_id', TRUE);
		
		// check if its real
		if ( ! (bool) $this->storage->get_content_status($content_id) )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_write_content_error')
			);
			$this->output->set_output_json($response);
			return;
		}

		$template_id = $this->input->post('template_id', TRUE);		

		$template = $this->input->post('template');  // dont use xss filter for template code
		$css = $this->input->post('css', TRUE);
		$javascript = $this->input->post('javascript'); // dont use xss filter for javascript code
		$head = $this->input->post('head'); // dont use xss filter for head code

		// HTTP post sends boolean value as string
		$overwrite = ( $this->input->post('overwrite', TRUE) == 'true' ) ? TRUE : FALSE;
		
		if ( (int) $content_id == 1 )
		{
			// Home, always write template
			$this->storage->put_template($template_id, $template, $css, $javascript, $head);
		}
		else
		{
			if ( $this->input->post('template_sole', TRUE) )
			{
				// Exclusive template
				$content_type_template_id = $this->storage->get_content_type_template_id($this->storage->get_content_type_id($content_id));
				if ( $content_type_template_id != $template_id )
				{
					// Content already have exclusive template, update it!
					$this->storage->put_template($template_id, $template, $css, $javascript, $head);
				}
				else
				{
					// Add a new template for this content
					$content_template_id = $this->storage->put_template(NULL, $template, $css, $javascript, $head);
					$this->storage->put_content_template_id($content_id, $content_template_id);
				}
			}
			else
			{
				// Ensure that content has no exclusive template
				$content_type_template_id = $this->storage->get_content_type_template_id($this->storage->get_content_type_id($content_id));
				$content_template_id = $this->storage->get_content_template_id($content_id);
				if ( $content_template_id != $content_type_template_id )
				{
					$this->storage->put_content_template_id($content_id, NULL);
					$this->storage->delete_template($content_template_id);
				}
	
				// Overwrite type template upon confirmation only
				if ( $overwrite ) 
				{
					// Overwrite type template
					$this->storage->put_template($content_type_template_id, $template, $css, $javascript, $head);
				}
			}
		}
		
		// Reload content's template
		$template = $this->storage->get_template($content_id);

		/*
		 * All contents associated to template 
		 * shold have their cache files erased
		 */
		$contents = $this->storage->get_contents_by_template($template_id);
		$contents = ( (bool) $contents ) ? $contents : array();
		// Content instance
		$this->load->library('content');
		foreach ( $contents as $content )
		{
			$this->content->set_id($content['id']);
			$this->content->load();
			// Erase cached content
			$this->content->erase_cache();
		}

		// Response
		$response = array(
			'done' => TRUE,
			'template' => $template['html'],
			'css' => $template['css'],
			'javascript' => $template['javascript'],
			'head' => $template['head'],
			'message' => $this->lang->line('elementar_xhr_write_template')
		);
		$this->output->set_output_json($response);
	}

	/**
	 * Save template filter
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_template_filter()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$template_id = $this->input->post('template_id', TRUE);
		$element_type = $this->input->post('element_type', TRUE);
		
		// Filter values
		$order_by = $this->input->post('order_by', TRUE);
		$direction = $this->input->post('direction', TRUE);
		$limit = (int) $this->input->post('limit', TRUE);

		$template_filter = json_decode($this->storage->get_template_filter($template_id), TRUE);

		if ( (bool) count($template_filter) )
		{
			$template_filter[$element_type]['order_by'] = $order_by;
			$template_filter[$element_type]['direction'] = $direction;
			$template_filter[$element_type]['limit'] = $limit;
		}
		else
		{
			$template_filter = array(
				$element_type => array(
					'order_by' => $order_by,
					'direction' => $direction,
					'limit' => $limit
				)
			);
		}
		
		// write filter
		$this->storage->put_template_filter($template_id, json_encode($template_filter));

		/*
		 * All contents associated to template 
		 * shold have their cache files erased
		 */
		$contents = $this->storage->get_contents_by_template($template_id);
		$contents = ( (bool) $contents ) ? $contents : array();
		// Content instance
		$this->load->library('content');
		foreach ( $contents as $content )
		{
			$this->content->set_id($content['id']);
			$this->content->load();
			// Erase cached content
			$this->content->erase_cache();
		}

		// Response
		$response = array(
			'done' => TRUE,
			'element_type' => $element_type,
			'message' => $this->lang->line('elementar_xhr_write_template_filter')
		);
		$this->output->set_output_json($response);
	}
}

/* End of file editor.php */
/* Location: ./application/controllers/backend/editor.php */
