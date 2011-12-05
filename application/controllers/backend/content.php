<?php
/*
 *      content.php
 *      
 *      Copyright 2011 Luciano Siqueira <lcnsqr@gmail.com>
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

if ( ! defined('BASEPATH')) exit($this->lang->line('elementar_no_direct_script_access'));

class Content extends CI_Controller {

	/*
	 * i18n settings
	 */
	var $LANG;
	var $LANG_AVAIL = array();

	function __construct()
	{
		parent::__construct();
		
		/*
		 *  CI helpers
		 */
		$this->load->helper(array('string', 'security', 'cookie', 'form', 'html', 'text', 'url'));
		
		/*
		 * CI libraries
		 */
		$this->load->library('session');
		
		/*
		 * Elementar database
		 */
		$this->elementar = $this->load->database('elementar', TRUE);

		/*
		 * Account model
		 */
		$this->load->model('Account', 'account');

		/*
		 * Create, read, update and delete Model
		 */
		$this->load->model('Crud', 'crud');
		$this->crud->STATUS = 'all';
		
		/*
		 * Backend language file
		 */
		$this->lang->load('elementar', $this->config->item('language'));
		
		/*
		 * Load site config
		 */
		$settings = $this->crud->get_config();
		if ( ! is_array($settings) )
		{
			exit($this->lang->line('elementar_config_error'));
		}
		foreach($settings as $setting)
		{
			switch ( $setting['name'] )
			{
				case 'i18n' :
				/*
				 * Language settings
				 */
				$i18n_settings = json_decode($setting['value'], TRUE);
				foreach($i18n_settings as $i18n_setting)
				{
					if ( (bool) $i18n_setting['default'] )
					{
						$this->LANG = $i18n_setting['code'];
						/*
						 * Default language is first in array
						 */
						$this->LANG_AVAIL = array_merge(array($i18n_setting['code'] => $i18n_setting['name']), $this->LANG_AVAIL);
					}
					else
					{
						$this->LANG_AVAIL[$i18n_setting['code']] = $i18n_setting['name'];
					}
				}
				break;
			}
		}
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common', array(
			'lang' => $this->LANG, 
			'uri_prefix' => ''
		));

		$this->config->set_item('site_name', 'Elementar');

		/*
		 * Email config
		 */
		/*
		$this->config->set_item('smtp_host', 'ssl://smtp.googlemail.com');
		$this->config->set_item('smtp_port', '465');
		$this->config->set_item('smtp_user', '');
		$this->config->set_item('smtp_pass', '');
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");
		*/

		/*
		 * Verificar sessão autenticada
		 * de usuário autorizado no admin
		 */
		$user_id = $this->session->userdata('user_id');
		if ( (int) $user_id != 1 )
		{
			$data = array(
				'is_logged' => FALSE,
				'title' => $this->config->item('site_name'),
				'js' => array(
					'/js/backend/jquery-1.6.2.min.js', 
					'/js/backend/backend_account.js', 
					'/js/backend/jquery.timers-1.2.js', 
					'/js/backend/backend_client_warning.js'
				),
				'action' => '/' . uri_string(),
				'elapsed_time' => $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			);

			/*
			 * Localized texts
			 */
			$data['elementar_authentication_title'] = $this->lang->line('elementar_authentication_title');
			$data['elementar_authentication_account'] = $this->lang->line('elementar_authentication_account');
			$data['elementar_authentication_password'] = $this->lang->line('elementar_authentication_password');
			$data['elementar_authentication_login'] = $this->lang->line('elementar_authentication_login');

			$data['elementar_exit'] = $this->lang->line('elementar_exit');
			$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
			$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
			$data['elementar_copyright'] = $this->lang->line('elementar_copyright');

			$login = $this->load->view('backend/backend_login', $data, TRUE);
			exit($login);
		}

	}
	
	function index()
	{
		/*
		 * User info
		 */
		$user_id = $this->session->userdata('user_id');
		$is_logged = TRUE;
		$username = $this->account->get_user_name($user_id);

		/*
		 * client controller (javascript)
		 */
		$js = array(
			'/js/backend/jquery-1.6.2.min.js',
			'/js/backend/jquery.easing.1.3.js',
			'/js/backend/jquery.timers-1.2.js',
			'/js/backend/backend_account.js',
			'/js/backend/tiny_mce/jquery.tinymce.js',
			'/js/backend/backend_client_warning.js',
			'/js/backend/backend_content_tree.js',
			'/js/backend/backend_content_window.js',
			'/js/backend/backend_content_tinymce.js',
			'/js/backend/backend_content_menu_field.js',
			'/js/backend/backend_content_index_field.js',
			'/js/backend/backend_content_file_gallery_field.js',
			'/js/backend/backend_content_youtube_gallery_field.js',
			'/js/backend/jquery.json-2.2.min.js',
			'/js/backend/backend_anchor.js'
		);
		
		/*
		 * Resource menu
		 */
		/*
		$resource_menu = array(
			anchor('Usuários', array('href' => '/backend/account', 'title' => 'Usuários')),
			span("&bull;", array('class' => 'top_menu_sep')),
			'<strong>' . $this->lang->line('elementar_content') . '</strong>'
		);
		*/
		$resource_menu = array(
			'<strong>' . $this->lang->line('elementar_content') . '</strong>'
		);

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => ul($resource_menu)
		);

		$data['parent_id'] = 1;
		$data['parent'] = $this->config->item('site_name');
		$data['content_hierarchy_content'] = $this->crud->get_contents_by_parent(1);
		$data['content_hierarchy_element'] = $this->crud->get_elements_by_parent(1);
		$data['content_listing_id'] = NULL;
		$data['content_listing'] = NULL;
		
		/*
		 * Localized texts
		 */
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
	 * Dive into content tree
	 */
	function xhr_render_tree_unfold()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$id = $this->input->post('id', TRUE);
		$request = $this->input->post('request', TRUE);

		if ( (bool) $id && (bool) $request )
		{		
			/*
			 * Reload navigation tree
			 */
			switch ( $request )
			{
				case "content" :
				$parent_id = $this->crud->get_content_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( $tree_id > 1 )
				{
					$parent_id = $this->crud->get_content_parent_id($tree_id);
					$tree = $this->_render_tree_listing($parent_id, $tree, $tree_id);
					$tree_id = $parent_id;
				}
				break;
				
				case "element" : 
				$parent_id = $this->crud->get_element_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( $tree_id > 1 )
				{
					$parent_id = $this->crud->get_content_parent_id($tree_id);
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
		$this->common->ajax_response($response);

	}

	/**
	 * Gerar formulário para inserção de tipo de conteúdo
	 */
	function xhr_render_content_type_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
			
		$form = paragraph($this->lang->line('elementar_type_fields'), array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'content_type_define_new_form', 'id' => 'content_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$form .= form_label($this->lang->line('elementar_type_name'), 'name', $attributes);
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$form .= form_input($attributes);
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");
		
		/*
		 * div field model
		 */
		$form .= div_open(array('id' => 'type_define_new_field_0', 'class' => 'type_define_new_field'));
		
		/*
		 * field name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label($this->lang->line('elementar_type_field_name'), "field_0");
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$form .= form_input($attributes);
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");

		/*
		 * field type
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label($this->lang->line('elementar_type_field_type'), "field_type_0");
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$form .= $this->_render_field_type_dropdown();
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");

		/*
		 * close div field model
		 */
		$form .= div_close("<!-- #type_define_new_field_0 -->");

		$form .= paragraph(anchor('&rarr; ' . $this->lang->line('elementar_type_add_field'), array('href' => 'add_type_field', 'id' => 'add_type_field')));
		
		/*
		 * HTML template
		 */
		$form .= paragraph($this->lang->line('elementar_type_markup'), array('class' => 'page_subtitle'));

		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label($this->lang->line('elementar_type_markup_template'), "template");
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$attributes = array(
			'name' => 'template',
			'id' => 'template',
			'class' => 'template_textarea',
			'rows' => 8,
			'cols' => 32,
			'value' => ''
		);
		$form .= form_textarea($attributes);
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");

		$form .= div_open(array('class' => 'form_control_buttons'));

		$form .= form_submit('type_save', $this->lang->line('elementar_save'));
		
		$form .= div_close();

		$form .= form_close();
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);

		$this->common->ajax_response($response);

	}

	/**
	 * Gerar formulário para inserção de tipo de elemento
	 */
	function xhr_render_element_type_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
			
		$form = paragraph($this->lang->line('elementar_type_element_new'), array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'element_type_define_new_form', 'id' => 'element_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$form .= form_label($this->lang->line('elementar_type_name'), 'name', $attributes);
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$form .= form_input($attributes);
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");
		
		/*
		 * div field model
		 */
		$form .= div_open(array('id' => 'type_define_new_field_0', 'class' => 'type_define_new_field'));
		
		/*
		 * field name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label($this->lang->line('elementar_type_field_name'), "field_0");
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$form .= form_input($attributes);
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");

		/*
		 * field type
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label($this->lang->line('elementar_type_field_type'), "field_type_0");
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");
		$form .= div_open(array('class' => 'form_window_column_input'));
		$form .= $this->_render_field_type_dropdown();
		$form .= div_close("<!-- form_window_column_input -->");
		$form .= div_close("<!-- .form_content_field -->");

		/*
		 * close div field model
		 */
		$form .= div_close("<!-- #type_define_new_field_0 -->");

		$form .= paragraph(anchor('&rarr; ' . $this->lang->line('elementar_type_add_field'), array('href' => 'add_type_field', 'id' => 'add_type_field')));
		
		$form .= div_open(array('class' => 'form_control_buttons'));

		$form .= form_submit('type_save', $this->lang->line('elementar_save'));
		
		$form .= div_close();

		$form .= form_close();
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);

		$this->common->ajax_response($response);

	}

	/**
	 * Gerar formulário para inserção de conteúdo
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
		
		$data = array();
		$data['content_id'] = NULL;
		$data['parent_id'] = $parent_id;
		$data['breadcrumb'] = $this->common->breadcrumb_content((int)$parent_id);
		$data['content_types_dropdown'] = $this->_render_content_types_dropdown($type_id);
		
		/*
		 * Localized texts
		 */
		$data['elementar_new_content_from_type'] = $this->lang->line('elementar_new_content_from_type');
		$data['elementar_proceed'] = $this->lang->line('elementar_proceed');

		$html = $this->load->view('backend/backend_content_new', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->common->ajax_response($response);
	}

	/**
	 * Gerar formulário para escolha do tipo de elemento
	 */
	function xhr_render_element_new()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Determine parent id
		 */
		$parent_id = $this->input->post('id', TRUE);

		/*
		 * After creation of a new type, it's new id
		 * is posted to be selected by default
		 */
		$type_id = $this->input->post('type_id', TRUE);;

		$data = array();
		$data['element_id'] = NULL;
		$data['parent_id'] = $parent_id;
		$data['breadcrumb'] = $this->common->breadcrumb_content((int)$parent_id);
		$data['element_types_dropdown'] = $this->_render_element_types_dropdown($type_id);

		/*
		 * Localized texts
		 */
		$data['elementar_new_element_from_type'] = $this->lang->line('elementar_new_element_from_type');
		$data['elementar_proceed'] = $this->lang->line('elementar_proceed');
		
		$html = $this->load->view('backend/backend_content_element_new', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->common->ajax_response($response);
	}

	function _render_form_custom_field($type, $name, $sname, $description, $value)
	{
		/*
		 * Adequate input to field type
		 */
		switch ( $type )
		{
			case "name" :
			case "line" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = div_open(array('class' => 'text_input_container'));
			$field .= form_input($attributes);
			$field .= div_close();
			$field .= hr(array('class' => 'clear'));
			break;

			case "p" :
			case "hypertext" :
			case "textarea" :
			$attributes = array(
				'class' => 'noform ' . $type,
				'name' => $sname,
				'id' => $sname,
				'rows' => 16,
				'cols' => 32,
				'value' => $value
			);
			$field = form_textarea($attributes);
			break;
			
			case "menu" :
			$field = div_open(array('class' => 'menu_field'));
			/*
			 * Render menu field
			 */
			$data = array(
				'menu' => ( $value != '' ) ? json_decode($value, TRUE) : array(), 
				'targets' => $this->_render_target_listing()
			);
			
			/*
			 * Localized texts
			 */
			$data['elementar_menu_name'] = $this->lang->line('elementar_menu_name');
			$data['elementar_menu_target'] = $this->lang->line('elementar_menu_target');
			$data['elementar_menu_add'] = $this->lang->line('elementar_menu_add');
			$data['elementar_menu_move_up'] = $this->lang->line('elementar_menu_move_up');
			$data['elementar_menu_move_down'] = $this->lang->line('elementar_menu_move_down');
			$data['elementar_menu_delete'] = $this->lang->line('elementar_menu_delete');
			$data['elementar_menu_new_above'] = $this->lang->line('elementar_menu_new_above');
			$data['elementar_menu_new_below'] = $this->lang->line('elementar_menu_new_below');			
			
			$field .= $this->load->view('backend/backend_content_menu_field', $data, true);
			/*
			 * The actual field
			 */
			$attributes = array(
				'class' => 'noform menu_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();
			break;

			case "file" : 
			$field = div_open(array('class' => 'file_field'));
			$attributes = array(
				'class' => 'noform ' . $type,
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			/*
			 * File field View variables
			 */
			$data = array();
			$data['input_name'] = $sname;
			if ( $value != '' )
			{
				$attributes = json_decode($value, TRUE);
				$data['thumbnail'] = $attributes['thumbnail'];
				$data['file_description'] = $attributes['title'];
				$data['file_uri'] = $attributes['uri'];
				$data['mime'] = $attributes['mime'];
				$data['size'] = $attributes['size'];
			}
			else
			{
				$data['thumbnail'] = '';
				$data['file_description'] = '';
				$data['file_uri'] = '';
				$data['mime'] = '';
				$data['size'] = '';
			}

			/*
			 * Localized texts
			 */
			$data['elementar_file_description'] = $this->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->lang->line('elementar_file_erase');

			$field .= $this->load->view("backend/backend_content_file_field", $data, TRUE);
			$field .= div_close();
			break;

			case "file_gallery" : 
			$field = div_open(array('class' => 'file_gallery_field'));
			/*
			 * Input holds json data with 
			 * file uri, alt text, width, height,
			 * thumbnail and size
			 */
			$attributes = array(
				'class' => 'noform file_gallery_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			/*
			 * Render gallery field
			 */
			$data = array(
				'gallery' => ($value != '') ? json_decode($value, TRUE) : array()
			);

			/*
			 * Localized texts
			 */
			$data['elementar_file_description'] = $this->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->lang->line('elementar_file_erase');
			$data['elementar_file_add'] = $this->lang->line('elementar_file_add');
			$data['elementar_file_move_up'] = $this->lang->line('elementar_file_move_up');
			$data['elementar_file_move_down'] = $this->lang->line('elementar_file_move_down');
			$data['elementar_file_delete'] = $this->lang->line('elementar_file_delete');
			$data['elementar_file_new_above'] = $this->lang->line('elementar_file_new_above');
			$data['elementar_file_new_below'] = $this->lang->line('elementar_file_new_below');

			$field .= $this->load->view("backend/backend_content_file_gallery_field", $data, TRUE);
			$field .= div_close();
			break;

			case "youtube_gallery" :
			$field = div_open(array('class' => 'youtube_gallery_field'));
			/*
			 * Render youtube_gallery field
			 */
			$data = array(
				'videos' => json_decode($value, TRUE) // decode as associative array
			);

			/*
			 * Localized texts
			 */
			$data['elementar_youtube_description'] = $this->lang->line('elementar_youtube_description');
			$data['elementar_youtube_url'] = $this->lang->line('elementar_youtube_url');
			$data['elementar_youtube_add'] = $this->lang->line('elementar_youtube_add');
			$data['elementar_youtube_move_up'] = $this->lang->line('elementar_youtube_move_up');
			$data['elementar_youtube_move_down'] = $this->lang->line('elementar_youtube_move_down');
			$data['elementar_youtube_delete'] = $this->lang->line('elementar_youtube_delete');
			$data['elementar_youtube_new_above'] = $this->lang->line('elementar_youtube_new_above');
			$data['elementar_youtube_new_below'] = $this->lang->line('elementar_youtube_new_below');

			$field .= $this->load->view('backend/backend_content_youtube_gallery_field', $data, true);
			/*
			 * The actual field
			 */
			$attributes = array(
				'class' => 'noform youtube_gallery_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();
			break;

			case "target" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = form_input($attributes);
			break;

			case "index" :
			$attributes = array(
				'class' => 'noform index_field',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = form_input($attributes);
			$field .= $this->_render_contents_listing();
			break;
		}
		return $field;
	}
	
	function _render_target_listing()
	{
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->lang->line('elementar_inside_targets') . '</strong>');
		/*
		 * Conteúdos
		 */
		foreach ( $this->crud->get_contents() as $content )
		{
			$listing[] = $this->common->breadcrumb_content($content['id']);
		}
		/*
		 * Addons
		 */
		foreach ( $this->common->load_addons() as $addon ) 
		{
			$listing[] = $this->common->breadcrumb_path($addon['uri']);
			// Methods
			if ( count($addon['methods']) > 0 )
			{
				foreach ( $addon['methods'] as $method ) 
				{
	            // skip methods that begin with '_'
	            if ( substr($method, 0, 1) == '_' ) continue;
	
	            // skip default method
	            if ( $method == 'index' || $method == 'main' ) continue;
	            
					// skip XHR (ajax) methods
	            if ( substr($method, 0, 4) == 'xhr_' ) continue;
	
	            // skip old-style constructor
	            if ( strtolower($method) == strtolower($addon['name']) ) continue;

					$listing[] = $this->common->breadcrumb_path($addon['uri'] . '/' . $method);
				}				
			}
		}
		$targets = div_open(array('class' => 'dropdown_items_listing_position'));
		$targets .= div_open(array('class' => 'dropdown_items_listing'));
		$attributes = array(
			'class' => 'dropdown_items_listing_targets'
		);
		$targets .= ul($listing, $attributes);
		$targets .= div_close();
		$targets .= div_close();
		return $targets;
	}

	function _render_contents_listing()
	{
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->lang->line('elementar_contents') . '</strong>');
		/*
		 * Conteúdos
		 */
		foreach ( $this->crud->get_contents_by_parent() as $content )
		{
			$content_name = json_decode($content['name'], TRUE);
			$listing[] = anchor($content_name[$this->LANG], array('href' => $content['id']));
		}
		$contents = div_open(array('class' => 'dropdown_items_listing_position'));
		$contents .= div_open(array('class' => 'dropdown_items_listing'));
		$attributes = array(
			'class' => 'dropdown_items_listing_contents'
		);
		$contents .= ul($listing, $attributes);
		$contents .= div_close();
		$contents .= div_close();
		return $contents;
	}

	/**
	 * Gerar formulário para inserção de elemento
	 */
	function xhr_render_element_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Create or update? Check for incoming element ID
		 */
		$element_id = $this->input->post('id', TRUE);
		$data = array();

		if ( (bool) $element_id !== FALSE ) 
		{
			/*
			 * Update
			 */
			$parent_id = $this->crud->get_element_parent_id($element_id);
			$type_id = $this->crud->get_element_type_id($element_id);		
			$data['breadcrumb'] = $this->common->breadcrumb_element($element_id);
		}
		else
		{
			/*
			 * Create
			 */
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
			$data['breadcrumb'] = $this->common->breadcrumb_element((int)$parent_id);
		}
		
		$form = "";

		if ( (bool) $type_id ) 
		{

			/*
			 * Element ID (if any, hidden)
			 */
			$attributes = array(
				'class' => 'noform',
				'name' => 'element_id',
				'value'=> $element_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			/*
			 * Element parent_id (hidden)
			 */
			$attributes = array(
				'class' => 'noform',
				'name' => 'parent_id',
				'value'=> $parent_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			/*
			 * Element type id (hidden)
			 */
			$attributes = array(
				'class' => 'noform',
				'name' => 'type_id',
				'value'=> $type_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			/*
			 * Element name
			 */
			$value = $this->crud->get_element_name($element_id);
			$form .= $this->_render_form_field('name', $this->lang->line('elementar_name'), 'name', NULL, $value, FALSE);

			/*
			 * Element type fields
			 */
			$fields = $this->crud->get_element_type_fields($type_id);
			foreach ( $fields as $field )
			{
				/*
				 * Field value
				 */
				$value = $this->crud->get_element_field($element_id, $field['id']);
				$form .= $this->_render_form_field($field['type'], $field['name'], $field['sname'], $field['description'], $value, $field['i18n']);
			}

			/*
			 * Spread
			 */
			$form .= div_open(array('class' => 'form_content_field'));
			$form .= div_open(array('class' => 'form_window_column_label'));
			if ( (bool) $element_id !== FALSE ) 
			{
				$checked = $this->crud->get_element_spread($element_id);
			}
			else
			{
				// Default new element to spread
				$checked = TRUE;
			}
			$attributes = array('class' => 'field_label');
			$form .= form_label($this->lang->line('elementar_element_spread'), "spread", $attributes);
			$form .= div_close("<!-- form_window_column_label -->");

			$form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'name'        => 'spread',
				'id'          => 'spread',
				'class' => 'noform',
				'value'       => 'true',
				'checked'     => $checked
			);
			$form .= form_checkbox($attributes);
			$form .= div_close("<!-- form_window_column_input -->");
			$form .= div_close("<!-- .form_content_field -->");

			/*
			 * status
			 */
			$form .= div_open(array('class' => 'form_content_field'));
			$form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$form .= form_label($this->lang->line('elementar_status'), "status", $attributes);
			$form .= div_close("<!-- form_window_column_label -->");
			$form .= div_open(array('class' => 'form_window_column_input'));
			$form .= $this->_render_status_dropdown($this->crud->get_element_status($element_id));
			$form .= div_close("<!-- form_window_column_input -->");
			$form .= div_close("<!-- .form_content_field -->");

			$form .= div_open(array('class' => 'form_control_buttons'));

			/*
			 *  Botão envio
			 */
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
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
		}
		$this->common->ajax_response($response);

	}

	/**
	 * Gerar formulário para inserção/atualizacão de conteúdo
	 */
	function xhr_render_content_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Create or update? Check for incoming content ID
		 */
		$content_id = $this->input->post('id', TRUE);
		$data = array();
		/*
		 * Which editor to display
		 */
		$data['editor'] = $this->input->post('editor', TRUE);

		if ( (bool) $content_id ) 
		{
			/*
			 * Update
			 */
			$parent_id = $this->crud->get_content_parent_id($content_id);
			$type_id = $this->crud->get_content_type_id($content_id);
			$template_id = $this->crud->get_content_template_id($content_id);
			$template = $this->crud->get_template($content_id);
			$template_html = $template['html'];
			$template_css = $template['css'];
			$template_javascript = $template['javascript'];
			$template_head = $template['head'];

			$data['breadcrumb'] = $this->common->breadcrumb_content($content_id);
		}
		else
		{
			/*
			 * Create
			 */
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
			$template_id = $this->crud->get_content_type_template_id($type_id);
			$template = $this->crud->get_content_type_template($type_id);
			$template_html = $template['html'];
			$template_css = $template['css'];
			$template_javascript = $template['javascript'];
			$template_head = $template['head'];

			$data['breadcrumb'] = $this->common->breadcrumb_content((int)$parent_id);
		}

		/*
		 * Show template tab only to existing contents
		 */
		$data['show_tabs'] = (bool) $content_id;
		if ( $data['show_tabs'] )
		{			
			/*
			 * Template editor
			 */
			$template_form = '';
			$attributes = array('class' => 'template_form', 'id' => 'template_form_' . $content_id);
			$hidden = array('template_id' => $template_id, 'content_id' => $content_id);
			$template_form .= form_open('/backend/content/xhr_write_template', $attributes, $hidden);

			/*
			 * Show Sole template checkbox (if not home)
			 */
			if ( $content_id != 1 )
			{
				$template_form .= div_open(array('class' => 'form_content_field'));
				$template_form .= div_open(array('class' => 'form_window_column_label'));
				$attributes = array('class' => 'field_label');
				$template_form .= form_label($this->lang->line('elementar_template_sole'), "sole", $attributes);
				$template_form .= div_close("<!-- form_window_column_label -->");
				$template_form .= div_open(array('class' => 'form_window_column_input'));
				if ( (bool) $content_id ) 
				{
					$checked = $this->crud->get_content_type_template_id($type_id) != $this->crud->get_content_template_id($content_id) ;
				}
				else 
				{
					$checked = FALSE;
				}
				$attributes = array(
					'name'        => 'template_sole',
					'id'          => 'template_sole_' . $content_id,
					'class' => 'template_form',
					'value'       => 'true',
					'checked'     => (bool) $checked
				);
				$template_form .= form_checkbox($attributes);
				$template_form .= div_close("<!-- form_window_column_input -->");
				$template_form .= div_close("<!-- .form_content_field -->");
			}
			
			/*
			 * Template pseudo variables available for this content
			 */
			$title = json_decode($this->crud->get_content_name($content_id), TRUE);
			$template_variables = array(
				'content_variables_title' => $title[$this->LANG],
				'content_variables' => array(),
				'relative_content_variables_title' => $this->lang->line('elementar_contents'),
				'relative_content_variables' => array(),
				'element_variables_title' => $this->lang->line('elementar_elements'),
				'element_variables' => array()
			);
			/*
			 * Default single variables
			 */
			$template_variables['content_variables'][] = array(
				'sname' => '{name}',
				'name' => 'Name'
			);
			$template_variables['content_variables'][] = array(
				'sname' => '{breadcrumb}',
				'name' => 'Breadcrumb'
			);
			/*
			 * Content single variables
			 */
			foreach ( $this->crud->get_content_type_fields($type_id) as $content_field )
			{
				$template_variables['content_variables'][] = array(
					'sname' => '{' . $content_field['sname'] . '}',
					'name' => $content_field['name']
				);
			}
			
			/*
			 * There are two "types" of relative contents: children and brother 
			 */
			if ( $this->crud->get_content_has_children($content_id, FALSE) )
			{
				/*
				 * Children contents
				 */
				if ( ! isset($template_variables['relative_content_variables']['children'] ) )
				{
					/*
					 * Variable pair with element type fields
					 */
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
			/*
			 * if not parent, render content brothers
			 */
			if ( $content_id != 1 )
			{
				$parent_id = $this->crud->get_content_parent_id($content_id);
				if ( $this->crud->get_content_has_children($parent_id, FALSE) )
				{
					/*
					 * Dont set if singleton (only child)
					 */
					if ( count($this->crud->get_contents_by_parent($parent_id)) > 1 )
					{
						/*
						 * Brother contents
						 */
						if ( ! isset($template_variables['relative_content_variables']['brothers'] ) )
						{
							/*
							 * Variable pair with element type fields
							 */
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

			/*
			 * Available elements variables
			 */
			foreach ( $this->crud->get_elements_by_parent_spreaded($content_id) as $element )
			{
				if ( ! isset($template_variables['element_variables'][$element['type_name']] ) )
				{
					/*
					 * Variable pair with element type fields
					 */
					$pair = '{' . $element['type'] . '}'  . "\n" ;
					$pair .= "\t" . '{name}' . "\n";
					$pair .= "\t" . '{sname}' . "\n";
					foreach( $this->crud->get_element_type_fields($element['type_id']) as $type_field )
					{
						$pair .= "\t" . '{' . $type_field['sname'] . '}' . "\n";
					}
					$pair .= '{/' . $element['type'] . '}'  . "\n" ;
					$template_variables['element_variables'][$element['type_name']] = array(
						'pair' => urlencode($pair),
						'elements' => array()
					);
				}
				/*
				 * Join element fields for unique insert
				 */
				$fields = '';
				$fields .= '{' . $element['sname'] . '.name}' . "\n";
				$fields .= '{' . $element['sname'] . '.sname}' . "\n";
				foreach ( $this->crud->get_element_fields($element['id']) as $element_field )
				{
					$fields .= '{' . $element['sname'] . '.' . $element_field['sname'] . '}' . "\n";
				}
				$template_variables['element_variables'][$element['type_name']]['elements'][] = array(
					'sname' => urlencode($fields),
					'name' => $element['name']
				);
			}

			/*
			 * HTML Template editor
			 */
			$template_variables['elementar_template_variables_title'] = $this->lang->line('elementar_template_variables_title');
			$template_form .= div_open(array('class' => 'form_content_field'));
			$template_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$template_form .= form_label($this->lang->line('elementar_type_markup_template'), 'template_' . $content_id, $attributes);
			$template_form .= br(1);
			$template_form .= div_close("<!-- form_window_column_label -->");
			$template_form .= div_open(array('class' => 'form_window_column_input'));
			$template_form .= $this->load->view('backend/backend_content_form_variables', $template_variables, true);			
			$attributes = array(
				'name' => 'template',
				'class' => 'template_textarea',
				'id' => 'template_' . $content_id,
				'rows' => 16,
				'cols' => 32,
				'value' => $template_html
			);
			$template_form .= form_textarea($attributes);
			$template_form .= div_close("<!-- form_window_column_input -->");
			$template_form .= div_close("<!-- .form_content_field -->");

			/*
			 * CSS editor
			 */
			$template_form .= div_open(array('class' => 'form_content_field'));
			$template_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$template_form .= form_label($this->lang->line('elementar_type_css'), 'css_' . $content_id, $attributes);
			$template_form .= br(1);
			$template_form .= div_close("<!-- form_window_column_label -->");
			$template_form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'name' => 'css',
				'class' => 'css_textarea',
				'id' => 'css_' . $content_id,
				'rows' => 16,
				'cols' => 32,
				'value' => $template_css
			);
			$template_form .= form_textarea($attributes);
			$template_form .= div_close("<!-- form_window_column_input -->");
			$template_form .= div_close("<!-- .form_content_field -->");

			/*
			 * Javascript editor
			 */
			$template_form .= div_open(array('class' => 'form_content_field'));
			$template_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$template_form .= form_label($this->lang->line('elementar_type_javascript'), 'css_' . $content_id, $attributes);
			$template_form .= br(1);
			$template_form .= div_close("<!-- form_window_column_label -->");
			$template_form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'name' => 'javascript',
				'class' => 'javascript_textarea',
				'id' => 'javascript_' . $content_id,
				'rows' => 16,
				'cols' => 32,
				'value' => $template_javascript
			);
			$template_form .= form_textarea($attributes);
			$template_form .= div_close("<!-- form_window_column_input -->");
			$template_form .= div_close("<!-- .form_content_field -->");

			/*
			 * Extra Head editor
			 */
			$template_form .= div_open(array('class' => 'form_content_field'));
			$template_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$template_form .= form_label($this->lang->line('elementar_type_extra_head'), 'head_' . $content_id, $attributes);
			$template_form .= br(1);
			$template_form .= div_close("<!-- form_window_column_label -->");
			$template_form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'name' => 'head',
				'class' => 'head_textarea',
				'id' => 'head_' . $content_id,
				'rows' => 16,
				'cols' => 32,
				'value' => $template_head
			);
			$template_form .= form_textarea($attributes);
			$template_form .= div_close("<!-- form_window_column_input -->");
			$template_form .= div_close("<!-- .form_content_field -->");

			$template_form .= div_open(array('class' => 'form_control_buttons'));
			$attributes = array(
			    'name' => 'button_template_save',
			    'id' => 'button_template_save',
			    'value' => $this->lang->line('elementar_save')
			);
			$template_form .= form_submit($attributes);
			$template_form .= div_close("<!-- form_control_buttons -->");
			$template_form .= form_close();
			$data['template_form'] = $template_form;

			/* 
			 * Meta fields editor tab
			 */
			$meta_form = '';
			$attributes = array(
				'class' => 'noform',
				'name' => 'id',
				'value'=> $content_id,
				'type' => 'hidden'
			);
			$meta_form .= form_input($attributes);
			/*
			 * Meta fields
			 */
			$fields = array(
				$this->lang->line('elementar_meta_keywords') => 'keywords',
				$this->lang->line('elementar_meta_description') => 'description',
				$this->lang->line('elementar_meta_author') => 'author',
				$this->lang->line('elementar_meta_author') => 'copyright'
			);
			
			if ( (int) $content_id == 1 )
			{
				$fields[$this->lang->line('elementar_meta_google-site-verification')] = 'google-site-verification';
			}
			foreach ( $fields as $label => $name )
			{
				$meta_form .= div_open(array('class' => 'form_content_field'));
				$meta_form .= div_open(array('class' => 'form_window_column_label'));
				$attributes = array('class' => 'field_label');
				$meta_form .= form_label($label, $name, $attributes);
				$meta_form .= br(1);
				$meta_form .= div_close("<!-- form_window_column_label -->");
				$meta_form .= div_open(array('class' => 'form_window_column_input'));
				$attributes = array(
					'class' => 'noform',
					'name' => $name,
					'id' => $name,
					'value' => html_entity_decode($this->crud->get_meta_field($content_id, $name), ENT_QUOTES, "UTF-8")
				);
				$meta_form .= form_input($attributes);
				$meta_form .= div_close("<!-- form_window_column_input -->");
				$meta_form .= div_close("<!-- .form_content_field -->");
			}

			/*
			 * URL
			 */
			$meta_form .= div_open(array('class' => 'form_content_field'));
			$meta_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$meta_form .= form_label($this->lang->line('elementar_meta_url'), 'url', $attributes);
			$meta_form .= br(1);
			$meta_form .= div_close("<!-- form_window_column_label -->");
			$meta_form .= div_open(array('class' => 'form_window_column_input'));
			$uri = $this->crud->get_content_uri($content_id);
			$url = $this->crud->get_meta_field($content_id, 'url');
			if ( $url == '' )
			{
				/*
				 * Change "/home" to "/" or use default path to content
				 */
				$url = ( $uri == '/home' ) ? site_url('/') : site_url($uri);
			}
			$attributes = array(
				'class' => 'noform',
				'name' => 'url',
				'id' => 'url',
				'value' => $url
			);
			$meta_form .= form_input($attributes);
			$meta_form .= div_close("<!-- form_window_column_input -->");
			$meta_form .= div_close("<!-- .form_content_field -->");

			/*
			 * Priority
			 */
			$meta_form .= div_open(array('class' => 'form_content_field'));
			$meta_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$meta_form .= form_label($this->lang->line('elementar_meta_priority'), 'priority', $attributes);
			$meta_form .= br(1);
			$meta_form .= div_close("<!-- form_window_column_label -->");
			$meta_form .= div_open(array('class' => 'form_window_column_input'));
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
			$value = $this->crud->get_meta_field($content_id, 'priority');
			$selected = ( (bool) $value ) ? $value : '0.5';
			$meta_form .= form_dropdown('priority', $values, $selected , 'class="noform" id="priority"');
			$meta_form .= div_close("<!-- form_window_column_input -->");
			$meta_form .= div_close("<!-- .form_content_field -->");
			/*
			 *  Botão envio
			 */
			$meta_form .= div_open(array('class' => 'form_control_buttons'));
			$attributes = array(
			    'name' => 'button_meta_save',
			    'id' => 'button_meta_save',
			    'class' => 'noform',
			    'content' => $this->lang->line('elementar_save')
			);
			$meta_form .= form_button($attributes);
			$meta_form .= div_close();
			$data['meta_form'] = $meta_form;

		}

		$content_form = "";
		
		if ( $type_id != "" ) 
		{
			$attributes = array(
				'class' => 'noform',
				'name' => 'content_id',
				'value'=> $content_id,
				'type' => 'hidden'
			);
			$content_form .= form_input($attributes);

			$attributes = array(
				'class' => 'noform',
				'name' => 'type_id',
				'value'=> $type_id,
				'type' => 'hidden'
			);
			$content_form .= form_input($attributes);

			$attributes = array(
				'class' => 'noform',
				'name' => 'parent_id',
				'value'=> $parent_id,
				'type' => 'hidden'
			);
			$content_form .= form_input($attributes);

			/*
			 * Content name
			 */
			$value = $this->crud->get_content_name($content_id);
			$content_form .= $this->_render_form_field('name', $this->lang->line('elementar_name'), 'name', NULL, $value, TRUE);

			/*
			 * Render custom fields
			 */
			$fields = $this->crud->get_content_type_fields($type_id);
			foreach ( $fields as $field )
			{
				/*
				 * Field value
				 */
				$value = $this->crud->get_content_field($content_id, $field['id']);
				$content_form .= $this->_render_form_field($field['type'], $field['name'], $field['sname'], $field['description'], $value, $field['i18n']);
			}

			/*
			 * status
			 */
			$content_form .= div_open(array('class' => 'form_content_field'));
			$content_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$content_form .= form_label($this->lang->line('elementar_status'), "status", $attributes);
			$content_form .= br(1);
			$content_form .= div_close("<!-- form_window_column_label -->");
			$content_form .= div_open(array('class' => 'form_window_column_input'));
			$content_form .= $this->_render_status_dropdown($this->crud->get_content_status($content_id));
			$content_form .= div_close("<!-- form_window_column_input -->");
			$content_form .= div_close("<!-- .form_content_field -->");

			$content_form .= div_open(array('class' => 'form_control_buttons'));
			/*
			 *  Botão envio
			 */
			$attributes = array(
			    'name' => 'button_content_save',
			    'id' => 'button_content_save',
			    'class' => 'noform',
			    'content' => $this->lang->line('elementar_save')
			);
			$content_form .= form_button($attributes);

			$content_form .= div_close("<!-- form_control_buttons -->");
			
			$data['content_form'] = $content_form;
			
			/*
			 * Localized texts
			 */
			$data['elementar_editor_content'] = $this->lang->line('elementar_editor_content');
			$data['elementar_editor_template'] = $this->lang->line('elementar_editor_template');
			$data['elementar_editor_meta'] = $this->lang->line('elementar_editor_meta');
			
			$html = $this->load->view('backend/backend_content_form', $data, true);
			
			$response = array(
				'done' => TRUE,
				'html' => $html
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
		}
		$this->common->ajax_response($response);

	}

	/*
	 * Render html columns with label and input(s)
	 */
	function _render_form_field($type, $name, $sname, $description = NULL, $value = NULL, $i18n)
	{
		$field = div_open(array('class' => 'form_content_field'));
		$field .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$field .= form_label($name, $sname, $attributes);
		$field .= br(1);
		$field .= div_close('<!-- form_window_column_label -->');
		$field .= div_open(array('class' => 'form_window_column_input'));
		
		/*
		 * Check multilanguage
		 */
		if ( (bool) $i18n )
		{
			/*
			 * Value array index is language code
			 */
			$value = json_decode($value, TRUE);

			/*
			 * One tab link for each language
			 */
			$input_lang_tab_links = array();
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				$current = ( $this->LANG == $lang_code ) ? ' current' : '';
				$input_lang_tab_links[] = anchor($lang_name, array('href' => $lang_code, 'class' => 'input_lang_tab_link' . $current));
			}
			$field .= div_open(array('class' => 'input_lang_menu'));
			$field .= ul($input_lang_tab_links);
			//field .= hr(array('class' => 'clear'));
			$field .= div_close('<!-- input_lang_menu -->');
			/*
			 * The input fields for each language
			 */
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				/*
				 * If language index does not exist, set empty
				 */
				$value = ( $value == NULL ) ? array() : $value;
				$lang_value = ( array_key_exists($lang_code, $value) ) ? $value[$lang_code] : '';
	
				$attributes = array('class' => 'input_lang_field input_lang_field_' . $lang_code);
				if ( $this->LANG == $lang_code ) 
				{
					$attributes['style'] = 'display: block;';
				}
				$field .= div_open($attributes);
				$field .= $this->_render_form_custom_field($type, $name, $sname . '_' . $lang_code, $description, $lang_value);
				$field .= div_close('<!-- input_lang_field -->');
			}
		}
		else
		{
			/*
			 * No multilanguage, no language tabs
			 */
			$attributes = array('class' => 'input_lang_field');
			$attributes['style'] = 'display: block;';
			$field .= div_open($attributes);
			$field .= $this->_render_form_custom_field($type, $name, $sname, $description, $value);
			$field .= div_close('<!-- input_lang_field -->');
			 
		}
		
		$field .= div_close('<!-- form_window_column_input -->');
		$field .= div_close('<!-- .form_content_field -->');
		return $field;
	}

	/**
	 * Content types HTML dropdown
	 * @param integer $selected Selected content type (id)
	 * @return HTML content (html widget)
	 */
	function _render_content_types_dropdown($selected = NULL)
	{
		$dropdown = div_open(array('class' => 'dropdown_items_listing_inline'));
		$types = $this->crud->get_content_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $selected )
			{
				$dropdown .= anchor($this->crud->get_content_type_name($selected), array('href' => $selected));
			}
			else
			{
				$dropdown .= anchor(current($types), array('href' => key($types)));
			}
		}
		else
		{
			$dropdown .= anchor($this->lang->line('elementar_new') . '...', array('href' => '0'));
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_content_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor($this->lang->line('elementar_new') . '...', array('id' => 'content_type_create', 'class' => 'dropdown_items_listing_content_type_target', 'href' => '0'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}
	
	/**
	 * Element types HTML dropdown
	 * @param integer $selected Selected content type (id)
	 * @return HTML content (html dropdown widget)
	 */
	function _render_element_types_dropdown($selected = NULL )
	{
		$dropdown = div_open(array('class' => 'dropdown_items_listing_inline'));
		$types = $this->crud->get_element_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $selected )
			{
				$dropdown .= anchor($this->crud->get_element_type_name($selected), array('href' => $selected));
			}
			else
			{
				$dropdown .= anchor(current($types), array('href' => key($types)));
			}
		}
		else
		{
			$dropdown .= anchor($this->lang->line('elementar_new') . '...', array('href' => '0'));
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_element_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor($this->lang->line('elementar_new') . '...', array('id' => 'element_type_create', 'class' => 'dropdown_items_listing_element_type_target', 'href' => '0'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}

	/**
	 * Status HTML dropdown
	 * @param string $selected Selected item
	 * @return HTML content (form element)
	 */
	function _render_status_dropdown($selected = "draft")
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
	 * @param integer $selected Selected item (id)
	 * @return HTML content (form element)
	 */
	function _render_field_type_dropdown($selected = "1")
	{
		$options = array();
		foreach ( $this->crud->get_field_types() as $option )
		{
			$options[$option['id']] = $option['name'];
		}
		$attributes = "id=\"field_type_0\"";
		return form_dropdown('field_type_0', $options, $selected, $attributes);
	}

	/**
	 * Salvar novo tipo de conteúdo
	 */
	function xhr_write_content_type()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$count = $this->input->post('field_count', TRUE);
		$template = $this->input->post('template');
		$name = $this->input->post('name', TRUE);		
		$template_id = $this->crud->put_template_html(NULL, $template);
		$type_id = $this->crud->put_content_type($name, $template_id);
		
		if ( (bool) $type_id )
		{
			/* 
			 * Armazenar campos do tipo
			 */
			for ( $c = 0; $c < $count; $c++)
			{
				$field = $this->input->post("field_" . $c, TRUE);
				$sname = $this->common->normalize_string($field);
				$field_type = $this->input->post("field_type_" . $c, TRUE);
				if ( $field != "" )
				{
					$this->crud->put_content_type_field($type_id, $field, $sname, $field_type);
				}
			}
			
			/*
			 * resposta
			 */
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
		$this->common->ajax_response($response);
	}

	/**
	 * Salvar novo tipo de elemento
	 */
	function xhr_write_element_type()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$count = $this->input->post('field_count', TRUE);
		
		$name = $this->input->post('name', TRUE);

		$sname = $this->common->normalize_string($name);

		$type_id = $this->crud->put_element_type($name, $sname);
		
		if ( (bool) $type_id )
		{
			/* 
			 * Armazenar campos do tipo
			 */
			for ( $c = 0; $c < $count; $c++)
			{
				$field = $this->input->post("field_" . $c, TRUE);
				$sname = $this->common->normalize_string($field);
				$field_type = $this->input->post("field_type_" . $c, TRUE);
				if ( $field != "" )
				{
					$this->crud->put_element_type_field($type_id, $field, $sname, $field_type);
				}
			}
			
			/*
			 * resposta
			 */
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
		$this->common->ajax_response($response);
	}

	/*
	 * Save/update content
	 */
	function xhr_write_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Content sname determined by the default language
		 */
		$sname = $this->common->normalize_string($this->input->post('name_' . $this->LANG, TRUE));

		if ( $sname == "" )
		{
			/*
			 * Invalid sname, return an error
			 */
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_return_name_error')
			);
			$this->common->ajax_response($response);
			return NULL;
		}

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

		/*
		 * Locate content type
		 */
		$type_id = $this->input->post('type_id', TRUE);

		/*
		 * Locate content ID
		 */
		$content_id = $this->input->post('content_id', TRUE);
		if ( (bool) $content_id )
		{
			/*
			 * Content has ID. Just rename
			 */
			$this->crud->put_content_name($content_id, $name, $sname);
		}
		else
		{
			/*
			 * Content ID not found, create new content
			 */
			$content_id = $this->crud->put_content($name, $sname, $type_id);
		}
		
		/* 
		 * Store content fields based on it's type
		 */
		foreach ( $this->crud->get_content_type_fields($type_id) as $type)
		{
			/*
			 * Check for multilanguage field
			 */
			if ( (bool) $type['i18n'] )
			{
				/*
				 * Group each language's value on a array before saving
				 */
				$values = array();
				foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
				{
					$values[$lang_code] = $this->input->post($type['sname'] . '_' . $lang_code, TRUE);
				}
				$value = json_encode($values);
			}
			else
			{
				/*
				 * Not multilanguage
				 */
				$value = $this->input->post($type['sname'], TRUE);
			}
			$this->crud->put_content_field($content_id, $type['id'], $value);
		}
		
		/*
		 * Save content's parent
		 */
		$parent_id = (int) $this->input->post('parent_id', TRUE);
		$this->crud->put_content_parent($content_id, $parent_id);

		/* 
		 * Save status
		 */
		$this->crud->put_content_status($content_id, $this->input->post('status', TRUE));
		
		/*
		 * Return ajax response
		 */
		$response = array(
			'done' => TRUE,
			'content_id' => $content_id,
			'message' => $this->lang->line('elementar_xhr_write_content')
		);
		$this->common->ajax_response($response);

	}

	/**
	 * Remover conteúdo
	 */
	function xhr_erase_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$content_id = $this->input->post('id', TRUE);

		/*
		 * remover conteúdo
		 */
		$this->crud->delete_content($content_id);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_erase_content')
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Remover elemento
	 */
	function xhr_erase_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$element_id = $this->input->post('id', TRUE);

		/*
		 * remover elemento
		 */
		$this->crud->delete_element($element_id);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_erase_element')
		);
		
		$this->common->ajax_response($response);

	}

	/*
	 * Write element parent
	 */
	function xhr_write_element_parent()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Parent id
		 */
		$parent_id = $this->input->post('parent_id', TRUE);

		/*
		 * Element id
		 */
		$element_id = $this->input->post('child_id', TRUE);

		if ( (bool) $parent_id && (bool) $element_id && ( $parent_id != $element_id ) )
		{
			$this->crud->put_element_parent($element_id, $parent_id);
			$response = array(
				'done' => TRUE
			);
			$this->common->ajax_response($response);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->common->ajax_response($response);
		}

	}

	/*
	 * Write content parent
	 */
	function xhr_write_content_parent()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Parent id
		 */
		$parent_id = $this->input->post('parent_id', TRUE);

		/*
		 * Content id
		 */
		$content_id = $this->input->post('child_id', TRUE);

		if ( (bool) $parent_id && (bool) $content_id && ( $parent_id != $content_id ) )
		{
			$this->crud->put_content_parent($content_id, $parent_id);
			$response = array(
				'done' => TRUE
			);
			$this->common->ajax_response($response);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->common->ajax_response($response);
		}

	}

	/*
	 * Salvar elemento
	 */
	function xhr_write_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		/*
		 * Elements snames are not multilanguage
		 */
		$sname = $this->common->normalize_string($this->input->post('name', TRUE));

		if ( (bool) $sname === false )
		{
			/*
			 * Invalid sname, return an error
			 */
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_return_name_error')
			);
			$this->common->ajax_response($response);
			return NULL;
		}

		/*
		 * Elements names are not multilanguage
		 */
		$name = $this->input->post('name', TRUE);
		
		/*
		 * Locate element type
		 */
		$type_id = $this->input->post('type_id', TRUE);
		
		/*
		 * Locate element ID
		 */
		$element_id = $this->input->post('element_id', TRUE);
		if ( (bool) $element_id )
		{
			/*
			 * Content has ID. Just rename
			 */
			$this->crud->put_element_name($element_id, $name, $sname);
		}
		else
		{
			/*
			 * Element ID not found, create new element
			 */
			$element_id = $this->crud->put_element($name, $sname, $type_id);
		}

		/* 
		 * Store element fields based on it's type
		 */
		foreach ( $this->crud->get_element_type_fields($type_id) as $type)
		{
			/*
			 * Check for multilanguage field
			 */
			if ( (bool) $type['i18n'] )
			{
				/*
				 * Group each language's value on a array before saving
				 */
				$values = array();
				foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
				{
					$values[$lang_code] = $this->input->post($type['sname'] . '_' . $lang_code, TRUE);
				}
				$value = json_encode($values);
			}
			else
			{
				/*
				 * Not multilanguage
				 */
				$value = $this->input->post($type['sname'], TRUE);
			}
			$this->crud->put_element_field($element_id, $type['id'], $value);
		}
		
		/*
		 * Write spread option
		 */
		if ( $this->input->post('spread', TRUE) )
		{
			$this->crud->put_element_spread($element_id, TRUE);
		}
		else
		{
			$this->crud->put_element_spread($element_id, FALSE);
		}

		/*
		 * Element's Parent
		 */
		$parent_id = $this->input->post('parent_id', TRUE);
		$this->crud->put_element_parent($element_id, $parent_id);

		/* 
		 * Save status
		 */
		$this->crud->put_element_status($element_id, $this->input->post('status', TRUE));
		
		/*
		 * Ajax response
		 */
		$response = array(
			'done' => TRUE,
			'element_id' => $element_id,
			'message' => $this->lang->line('elementar_xhr_write_element')
		);
		$this->common->ajax_response($response);

	}

	/**
	 * Listar conteúdos/elementos
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
		$this->common->ajax_response($response);
		
	}
	
	function _render_tree_listing($id, $listing = NULL, $listing_id = NULL)
	{
		$data['parent_id'] = $id;
		
		/*
		 * Set default language for view
		 */
		$data['lang'] = $this->LANG;
		
		$data['content_hierarchy_content'] = $this->crud->get_contents_by_parent($id);
		$data['content_hierarchy_element'] = $this->crud->get_elements_by_parent($id);
		// Inner listings, if any
		$data['content_listing_id'] = $listing_id;
		$data['content_listing'] = $listing;
		
		/*
		 * Localized texts
		 */
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
	 */
	function xhr_write_meta() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$id = $this->input->post('id', TRUE);
		
		/*
		 * Meta fields
		 */
		$fields = array(
			'keywords',
			'description',
			'author',
			'copyright',
			'priority'
		);

		if ( (int) $id == 1 )
		{
			$fields[] = 'google-site-verification';
		}

		foreach ( $fields as $name )
		{
			$value = $this->input->post($name, TRUE);
			if ( (bool) $value )
			{
				$this->crud->put_meta_field($id, $name, $value);
			}
			else
			{
				// Remove meta field
				$this->crud->delete_meta_field($id, $name);
			}
		}
		
		$uri = $this->crud->get_content_uri($id);
		$url = $this->input->post('url', TRUE);
		if ( site_url($uri) != $url )
		{
			/*
			 * Write url meta field
			 */
			$this->crud->put_meta_field($id, 'url', $url);
		}
		
		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_write_meta')
		);
		$this->common->ajax_response($response);
	}

	/**
	 * Salvar template
	 */
	function xhr_write_template()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$content_id = $this->input->post('content_id', TRUE);
		$template_id = $this->input->post('template_id', TRUE);		

		$template = $this->input->post('template');  // dont use xss filter for template code
		$css = $this->input->post('css', TRUE);
		$javascript = $this->input->post('javascript'); // dont use xss filter for javascript code
		$head = $this->input->post('head'); // dont use xss filter for head code

		/*
		 * HTTP post sends boolean value as string
		 */
		$overwrite = ( $this->input->post('overwrite', TRUE) == 'true' ) ? TRUE : FALSE;
		
		if ( (int) $content_id == 1 )
		{
			/*
			 * Home, always write template
			 */
			$this->crud->put_template($template_id, $template, $css, $javascript, $head);
		}
		else
		{
			if ( $this->input->post('template_sole', TRUE) )
			{
				/*
				 * Exclusive template
				 */
				$content_type_template_id = $this->crud->get_content_type_template_id($this->crud->get_content_type_id($content_id));
				if ( $content_type_template_id != $template_id )
				{
					/*
					 * Content already have exclusive template, update it!
					 */
					$this->crud->put_template($template_id, $template, $css, $javascript, $head);
				}
				else
				{
					/*
					 * Add a new template for this content
					 */
					$content_template_id = $this->crud->put_template(NULL, $template, $css, $javascript, $head);
					$this->crud->put_content_template_id($content_id, $content_template_id);
				}
			}
			else
			{
				/*
				 * Ensure that content has no exclusive template
				 */
				$content_type_template_id = $this->crud->get_content_type_template_id($this->crud->get_content_type_id($content_id));
				$content_template_id = $this->crud->get_content_template_id($content_id);
				if ( $content_template_id != $content_type_template_id )
				{
					$this->crud->put_content_template_id($content_id, NULL);
					$this->crud->delete_template($content_template_id);
				}
	
				/*
				 * Overwrite type template upon confirmation only
				 */
				if ( $overwrite ) 
				{
					/*
					 * Overwrite type template
					 */
					$this->crud->put_template($content_type_template_id, $template, $css, $javascript, $head);
				}
			}
		}
		
		/*
		 * Reload content's template
		 */
		$template = $this->crud->get_template($content_id);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE,
			'template' => $template['html'],
			'css' => $template['css'],
			'javascript' => $template['javascript'],
			'head' => $template['head'],
			'message' => $this->lang->line('elementar_xhr_write_template')
		);
		$this->common->ajax_response($response);
	}
	
}
