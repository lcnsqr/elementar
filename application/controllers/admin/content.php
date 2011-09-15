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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Content extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		/*
		 * Settings
		 */
		$this->config->set_item('site_name', 'Elementar');

		$this->config->set_item('smtp_host', 'ssl://smtp.googlemail.com');
		$this->config->set_item('smtp_port', '465');
		$this->config->set_item('smtp_user', 'lcnsqr@gmail.com');
		$this->config->set_item('smtp_pass', '');

		
		/*
		 *  CI helpers
		 */
		$this->load->helper(array('string', 'security', 'cookie', 'form', 'html', 'text', 'url'));
		
		/*
		 * CI libraries
		 */
		$this->load->library('session');
		
		/*
		 * User/session database
		 */
		$this->db_acc = $this->load->database('account', TRUE);

		/*
		 * Content database
		 */
		$this->elementar = $this->load->database('elementar', TRUE);

		/*
		 * Session model
		 */
		$this->load->model('M_session', 'sess');

		/*
		 * Account model
		 */
		$this->load->model('M_account', 'account');

		/*
		 * Create, read, update and delete Model
		 */
		$this->load->model('Crud', 'crud');
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common');

		/*
		 * Verificar sessão autenticada
		 * de usuário autorizado no admin
		 */
		$user_id = $this->account->logged($this->sess->session_id());
		if ( $user_id === FALSE )
		{
			$data = array(
				'is_logged' => FALSE,
				'title' => $this->config->item('site_name'),
				'js' => array('/js/jquery-1.6.2.min.js', '/js/admin_session.js', '/js/jquery.timers-1.2.js', '/js/admin_client_warning.js'),
				'action' => '/' . uri_string(),
				'elapsed_time' => $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			);
			$login = $this->load->view('admin/admin_login', $data, TRUE);
			exit($login);
		}

	}
	
	function index()
	{
		/*
		 * User info
		 */
		$user_id = $this->account->logged($this->sess->session_id());
		$is_logged = TRUE;
		$username = $this->account->get_user_name($user_id);

		/*
		 * client controller (javascript)
		 */
		$js = array(
			'/js/jquery-1.6.2.min.js',
			'/js/jquery.easing.1.3.js',
			'/js/jquery.timers-1.2.js',
			'/ckeditor/ckeditor.js',
			'/js/admin_client_warning.js',
			'/js/admin_content_tree.js',
			'/js/admin_content_window.js',
			'/js/admin_content_ckeditor.js',
			'/js/admin_content_menu_field.js',
			'/js/jquery.json-2.2.min.js',
			'/js/admin_anchor.js',
			'/js/admin_upload.js'
		);
		
		/*
		 * Resource menu
		 */
		/*
		$resource_menu = array(
			anchor("Usuários", array('href' => '/admin/account', 'title' => 'Usuários')),
			span("&bull;", array('class' => 'top_menu_sep')),
			"<strong>Conteúdo</strong>"
		);
		*/
		$resource_menu = array(
			"<strong>Conteúdo</strong>"
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
		
		$this->load->view('admin/admin_content', $data);

	}
	
	/**
	 * Dive into content tree
	 */
	function xhr_render_tree_unfold()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

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
				'error' => "Dados inconsistentes"
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
			exit('No direct script access allowed');
			
		$form = paragraph("Campos do Modelo", array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'content_type_define_new_form', 'id' => 'content_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$form .= form_label("Nome do tipo", "name", $attributes);
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
		$form .= form_label("Nome do campo", "field_0");
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
		$form .= form_label("Tipo do campo", "field_type_0");
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

		$form .= paragraph(anchor("&rarr; Incluir outro campo", array('href' => 'add_type_field', 'id' => 'add_type_field')));
		
		/*
		 * HTML template
		 */
		$form .= paragraph("Markup do Modelo", array('class' => 'page_subtitle'));

		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$form .= form_label("Template", "template");
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

		$form .= form_submit('type_save', 'Salvar');
		
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
			exit('No direct script access allowed');
			
		$form = paragraph("Defini&ccedil;&atilde;o de novo tipo", array('class' => 'page_subtitle'));
		
		$attributes = array('class' => 'element_type_define_new_form', 'id' => 'element_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= div_open(array('class' => 'form_content_field'));
		$form .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$form .= form_label("Nome do tipo", "name", $attributes);
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
		$form .= form_label("Nome do campo", "field_0");
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
		$form .= form_label("Tipo do campo", "field_type_0");
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

		$form .= paragraph(anchor("&rarr; Incluir outro campo", array('href' => 'add_type_field', 'id' => 'add_type_field')));
		
		$form .= div_open(array('class' => 'form_control_buttons'));

		$form .= form_submit('type_save', 'Salvar');
		
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
			exit('No direct script access allowed');

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
		
		$html = $this->load->view('admin/admin_content_new', $data, true);

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
			exit('No direct script access allowed');

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
		
		$html = $this->load->view('admin/admin_content_element_new', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->common->ajax_response($response);
	}

	function _render_form_custom_field($field, $value = NULL)
	{
		$form = div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$form .= form_label($field['name'], $field['sname'], $attributes);
		$form .= br(1);
		$form .= div_close("<!-- form_window_column_label -->");		
		$form .= div_open(array('class' => 'form_window_column_input'));

		/*
		 * Adequar ao tipo do campo
		 */
		switch ( $field['type'] )
		{
			case "line" :
			$attributes = array(
				'class' => 'noform',
				'name' => $field['sname'],
				'id' => $field['sname'],
				'value' => $value
			);
			$form .= form_input($attributes);
			break;

			case "p" :
			case "hypertext" :
			case "textarea" :
			$attributes = array(
				'class' => 'noform ' . $field['type'],
				'name' => $field['sname'],
				'id' => $field['sname'],
				'rows' => 16,
				'cols' => 32,
				'value' => $value
			);
			$form .= form_textarea($attributes);
			break;
			
			case "menu" :
			$form .= div_open(array('class' => 'menu_field'));
			/*
			 * Render menu field
			 */
			$data = array(
				'menu' => json_decode($value, TRUE), // decode as associative array
				'targets' => $this->_render_target_listing()
			);
			$form .= $this->load->view('admin/admin_content_menu_field', $data, true);
			/*
			 * The actual field
			 */
			$attributes = array(
				'class' => 'noform menu_actual_field',
				'type' => 'hidden',
				'name' => $field['sname'],
				'id' => $field['sname'],
				'value' => $value
			);
			$form .= form_input($attributes);
			$form .= div_close();
			break;

			case "img" : 
			$attributes = array(
				'class' => 'noform',
				'type' => 'hidden',
				'name' => $field['sname'],
				'value' => $value
			);
			$form .= form_input($attributes);
			$form .= $this->common->render_form_upload_image($field['sname'], $value);
			break;

			case "target" :
			$attributes = array(
				'class' => 'noform',
				'name' => $field['sname'],
				'id' => $field['sname'],
				'value' => $value
			);
			$form .= form_input($attributes);
			break;
		}
		$form .= div_close("<!-- form_window_column_input -->");

		return $form;
	}
	
	function _render_target_listing()
	{
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = paragraph("<strong>Destinos internos</strong>");
		/*
		 * Conteúdos
		 */
		foreach ( $this->crud->get_contents() as $content )
		{
			$listing[] = $this->common->breadcrumb_content($content['id']);
		}
		/*
		 * Controllers
		 */
		$controllers = array();		
		foreach( $this->common->controllers(array('Main','Rss','User')) as $controller )
		{
			$controllers[] = array(
				'path' => $controller['uri'],
				'name' => $controller['name']
			);
			// Controller methods
			if ( count($controller['methods']) > 0 )
			{
				foreach ( $controller['methods'] as $method ) 
				{
					$controllers[] = array(
						'path' => $method['uri'],
						'name' => $method['name']
					);
				}				
			}
		}
		foreach( $controllers as $controller )
		{
			$listing[] = $this->common->breadcrumb_path($controller['path']);
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
	
	/**
	 * Gerar formulário para inserção de elemento
	 */
	function xhr_render_element_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

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
			 * Element name
			 */
			$form .= div_open(array('class' => 'form_content_field'));
			$form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$form .= form_label("Nome", "name", $attributes);
			$form .= br(1);
			$form .= div_close("<!-- form_window_column_label -->");
			$form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->crud->get_element_name($element_id)
			);
			$form .= form_input($attributes);
			$form .= div_close("<!-- form_window_column_input -->");
			$form .= div_close("<!-- .form_content_field -->");

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
			 * Element type fields
			 */
			$fields = $this->crud->get_element_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$form .= div_open(array('class' => 'form_content_field'));
				$form .= $this->_render_form_custom_field($field, $this->crud->get_element_field($element_id, $field['id']));
				$form .= div_close("<!-- .form_content_field -->");
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
			$form .= form_label("Propagar", "spread", $attributes);
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
			$form .= form_label("Status", "status", $attributes);
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
			    'content' => 'Salvar'
			);
			$form .= form_button($attributes);

			$form .= div_close();
			
			$data['element_form'] = $form;
			
			$html = $this->load->view('admin/admin_content_element_form', $data, true);

			$response = array(
				'done' => TRUE,
				'html' => $html
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome para o elemento"
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
			exit('No direct script access allowed');

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
			$template_form .= form_open('/admin/content/xhr_write_template', $attributes, $hidden);

			/*
			 * Show Sole template checkbox (if not home)
			 */
			if ( $content_id != 1 )
			{
				$template_form .= div_open(array('class' => 'form_content_field'));
				$template_form .= div_open(array('class' => 'form_window_column_label'));
				$attributes = array('class' => 'field_label');
				$template_form .= form_label("Exclusivo", "sole", $attributes);
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
			$template_variables = array(
				'content_singles_title' => $this->crud->get_content_name($content_id),
				'content_singles' => array(),
				'element_singles_title' => 'Elementos',
				'element_singles' => array(),
				'element_pairs' => array()
			);
			/*
			 * Default single variables
			 */
			$template_variables['content_singles'][] = array(
				'sname' => '{name}',
				'name' => 'Name'
			);
			$template_variables['content_singles'][] = array(
				'sname' => '{breadcrumb}',
				'name' => 'Breadcrumb'
			);
			/*
			 * Content single variables
			 */
			foreach ( $this->crud->get_content_type_fields($type_id) as $content_field )
			{
				$template_variables['content_singles'][] = array(
					'sname' => '{' . $content_field['sname'] . '}',
					'name' => $content_field['name']
				);
			}
			/*
			 * Available elements variables
			 */
			foreach ( $this->crud->get_elements_by_parent_spreaded($content_id) as $element )
			{
				if ( ! isset($template_variables['element_singles'][$element['type_name']] ) )
				{
					/*
					 * Variable pair with element type fields
					 */
					$pair = '{' . $element['type'] . '}'  . "\n" ;
					foreach( $this->crud->get_element_type_fields($element['type_id']) as $type_field )
					{
						$pair .= "\t" . '{' . $type_field['sname'] . '}' . "\n";
					}
					$pair .= '{/' . $element['type'] . '}'  . "\n" ;
					$template_variables['element_singles'][$element['type_name']] = array(
						'pair' => urlencode($pair),
						'elements' => array()
					);
				}
				/*
				 * Join element fields for unique insert
				 */
				$fields = '';
				foreach ( $this->crud->get_element_fields($element['id']) as $element_field )
				{
					$fields .= '{' . $element['sname'] . '.' . $element_field['sname'] . '}' . "\n";
				}
				$template_variables['element_singles'][$element['type_name']]['elements'][] = array(
					'sname' => urlencode($fields),
					'name' => $element['name']
				);
			}

			/*
			 * HTML Template editor
			 */
			$template_form .= div_open(array('class' => 'form_content_field'));
			$template_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$template_form .= form_label("Template", 'template_' . $content_id, $attributes);
			$template_form .= br(1);
			$template_form .= div_close("<!-- form_window_column_label -->");
			$template_form .= div_open(array('class' => 'form_window_column_input'));
			$template_form .= $this->load->view('admin/admin_content_form_variables', $template_variables, true);			
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
			$template_form .= form_label("Style Sheet", 'css_' . $content_id, $attributes);
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
			$template_form .= form_label("Javascript", 'css_' . $content_id, $attributes);
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
			$template_form .= form_label("Extra Head Content", 'head_' . $content_id, $attributes);
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
			    'value' => 'Salvar'
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
				'Keywords' => 'keywords',
				'Description' => 'description',
				'Author' => 'author',
				'Copyright' => 'copyright'
			);
			
			if ( (int) $content_id == 1 )
			{
				$fields['Google Site Verification'] = 'google-site-verification';
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
			$meta_form .= div_open(array('class' => 'form_content_field'));
			$meta_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$meta_form .= form_label('Prioridade', 'priority', $attributes);
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
			    'content' => 'Salvar'
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
			$content_form .= div_open(array('class' => 'form_content_field'));
			$content_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$content_form .= form_label("Nome", "name", $attributes);
			$content_form .= br(1);
			$content_form .= div_close("<!-- form_window_column_label -->");
			
			$content_form .= div_open(array('class' => 'form_window_column_input'));
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->crud->get_content_name($content_id)
			);
			$content_form .= form_input($attributes);
			$content_form .= div_close("<!-- form_window_column_input -->");

			$content_form .= div_close("<!-- .form_content_field -->");

			$fields = $this->crud->get_content_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$content_form .= div_open(array('class' => 'form_content_field'));
				$content_form .= $this->_render_form_custom_field($field, $this->crud->get_content_field($content_id, $field['id']));
				$content_form .= div_close("<!-- .form_content_field -->");
			}

			/*
			 * status
			 */
			$content_form .= div_open(array('class' => 'form_content_field'));
			$content_form .= div_open(array('class' => 'form_window_column_label'));
			$attributes = array('class' => 'field_label');
			$content_form .= form_label("Status", "status", $attributes);
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
			    'content' => 'Salvar'
			);
			$content_form .= form_button($attributes);

			$content_form .= div_close("<!-- form_control_buttons -->");
			
			$data['content_form'] = $content_form;
			
			$html = $this->load->view('admin/admin_content_form', $data, true);
			
			$response = array(
				'done' => TRUE,
				'html' => $html
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Erro na criação do conteúdo"
			);
		}
		$this->common->ajax_response($response);

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
			$dropdown .= anchor("Novo...", array('href' => '0'));
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_content_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor("Novo...", array('id' => 'content_type_create', 'class' => 'dropdown_items_listing_content_type_target', 'href' => '0'));
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
			$dropdown .= anchor("Novo...", array('href' => '0'));
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_element_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor("Novo...", array('id' => 'element_type_create', 'class' => 'dropdown_items_listing_element_type_target', 'href' => '0'));
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
			"draft" => "Rascunho",
			"published" => "Publicado"
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
			exit('No direct script access allowed');

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
				'type_id' => $type_id
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido'
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
			exit('No direct script access allowed');

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
				'type_id' => $type_id
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido'
			);
		}
		$this->common->ajax_response($response);
	}

	/**
	 * Salvar conteúdo
	 */
	function xhr_write_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$type_id = $this->input->post('type_id', TRUE);

		$name = $this->input->post('name', TRUE);

		$sname = $this->common->normalize_string($name);

		if ( $name == "" )
		{
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido'
			);
			$this->common->ajax_response($response);
			return NULL;
		}

		$content_id = $this->input->post('content_id', TRUE);
		if ( ! $content_id )
		{
			/*
			 * Content ID not found, create new content
			 */
			$content_id = $this->crud->put_content($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->crud->put_content_name($content_id, $name, $sname);
		}
		
		/* 
		 * Armazenar campos
		 */
		foreach ( $this->crud->get_content_type_fields($type_id) as $type)
		{
			$value = $this->input->post($type['sname'], TRUE);
			$this->crud->put_content_field($content_id, $type['id'], $value);
			/*
			 * Extra fields for specific field types
			 */
			switch ( $type['type'] )
			{
				case 'img' :
				$image_id = $value;
				$image_title = $this->input->post($type['sname'] . '_title', TRUE);
				if ( (bool) $image_title )
				{
					$this->crud->put_image_title($image_id, $image_title);
				}
				break;
			}
		}
		
		/*
		 * Parent
		 */
		$parent_id = (int) $this->input->post('parent_id', TRUE);
		$this->crud->put_content_parent($content_id, $parent_id);

		/* 
		 * Armazenar status
		 */
		$this->crud->put_content_status($content_id, $this->input->post('status', TRUE));
		
		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE,
			'content_id' => $content_id
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Remover conteúdo
	 */
	function xhr_erase_content()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$content_id = $this->input->post('id', TRUE);

		/*
		 * remover conteúdo
		 */
		$this->crud->delete_content($content_id);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Remover elemento
	 */
	function xhr_erase_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$element_id = $this->input->post('id', TRUE);

		/*
		 * remover elemento
		 */
		$this->crud->delete_element($element_id);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Salvar elemento
	 */
	function xhr_write_element()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$type_id = $this->input->post('type_id', TRUE);
		$parent_id = $this->input->post('parent_id', TRUE);
		$name = $this->input->post('name', TRUE);
		$sname = $this->common->normalize_string($name);

		if ( (bool) $name === false )
		{
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido'
			);
			$this->common->ajax_response($response);
			return NULL;
		}

		$element_id = $this->input->post('element_id', TRUE);
		if ( ! $element_id )
		{
			/*
			 * Element ID not found, create new element
			 */
			$element_id = $this->crud->put_element($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->crud->put_element_name($element_id, $name, $sname);
		}

		/* 
		 * Armazenar campos
		 */
		foreach ( $this->crud->get_element_type_fields($type_id) as $type)
		{
			if ( $this->crud->get_element_type_sname($type_id) == 'head_element' )
			{
				/*
				 * Ignore XSS filter for Head element fields
				 */
				$value = $this->input->post($type['sname']);
			}
			else
			{
				$value = $this->input->post($type['sname'], TRUE);
			}
			$this->crud->put_element_field($element_id, $type['id'], $value);
			/*
			 * Extra fields for specific field types
			 */
			switch ( $type['type'] )
			{
				case 'img' :
				$image_id = $value;
				$image_title = $this->input->post($type['sname'] . '_title', TRUE);
				if ( (bool) $image_title )
				{
					$this->crud->put_image_title($image_id, $image_title);
				}
				break;
			}
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
		 * Parent
		 */
		$this->crud->put_element_parent($element_id, $parent_id);

		/* 
		 * Armazenar status
		 */
		$this->crud->put_element_status($element_id, $this->input->post('status', TRUE));
		
		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE,
			'element_id' => $element_id
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Listar conteúdos/elementos
	 */
	function xhr_render_tree_listing()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

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

		$data['parent'] = $this->crud->get_content_name($id);
		$data['content_hierarchy_content'] = $this->crud->get_contents_by_parent($id);
		$data['content_hierarchy_element'] = $this->crud->get_elements_by_parent($id);
		// Inner listings, if any
		$data['content_listing_id'] = $listing_id;
		$data['content_listing'] = $listing;
		
		$html = $this->load->view('admin/admin_content_tree', $data, true);
		
		return $html;
	}

	/**
	 * renomear conteudo
	 */
	function xhr_rename_content() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);
		
		$name = $this->input->post('name', TRUE);

		if ($id != "" && $name != "" ) 
		{
			$sname = $this->common->normalize_string($name);

			$this->crud->put_content_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->crud->get_content_name($id), ENT_QUOTES, "UTF-8")
			);
		}			

		$this->common->ajax_response($response);

	}

	/**
	 * renomear elemento
	 */
	function xhr_rename_element() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);
		
		$name = $this->input->post('name', TRUE);

		if ($id != "" && $name != "" ) 
		{
			$sname = $this->common->normalize_string($name);

			$this->crud->put_element_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->crud->get_element_name($id), ENT_QUOTES, "UTF-8")
			);
		}			

		$this->common->ajax_response($response);

	}

	/**
	 * Save meta fields
	 */
	function xhr_write_meta() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);
		
		/*
		 * Meta fields
		 */
		$fields = array(
			'Keywords' => 'keywords',
			'Description' => 'description',
			'Author' => 'author',
			'Copyright' => 'copyright',
			'Priority' => 'priority'
		);

		if ( (int) $id == 1 )
		{
			$fields['Google Site Verification'] = 'google-site-verification';
		}

		foreach ( $fields as $label => $name )
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
		
		$response = array(
			'done' => TRUE
		);
		$this->common->ajax_response($response);
	}

	/**
	 * Salvar template
	 */
	function xhr_write_template()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

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
			'head' => $template['head']
		);
		$this->common->ajax_response($response);
	}

}
