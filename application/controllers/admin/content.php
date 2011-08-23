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
?>

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Content extends CI_Controller {

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
		 * User/session database
		 */
		$this->db_acc = $this->load->database('account', TRUE);

		/*
		 * Content database
		 */
		$this->db_cms = $this->load->database('cms', TRUE);

		/*
		 * Session model
		 */
		$this->load->model('M_session', 'sess');

		/*
		 * Account model
		 */
		$this->load->model('M_account', 'account');

		/*
		 * Content model (admin)
		 */
		$this->load->model('Elementar', 'elementar');
		
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
			'/js/admin_content_menu.js',
			'/js/admin_anchor.js',
			'/js/admin_content_anchor_section.js',
			'/js/admin_content_anchor_editor.js',
			'/js/admin_upload.js'
		);
		
		/*
		 * Resource menu
		 */
		$resource_menu = "<ul><li><a href=\"/admin/account\" title=\"Usuários\">Usuários</a></li><li><span class=\"diams\">&diams;</span></li><li><strong>Conteúdo</strong></li></ul>";

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => $resource_menu
		);

		$this->load->view('admin/admin_content', $data);

	}
	
	/**
	 * Atualizar conteúdo da seção
	 */
	function xhr_render_section()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
			$section = $this->input->post('section', TRUE);
			
			switch ($section)
			{				
				case "menu" : 
				/*
				 * Administração de menus
				 */
				$data['menus'] = $this->elementar->get_menus();
				$html = $this->load->view('admin/admin_content_menu', $data, true);
				break;
				
				case "editor" :
				$data['parent_id'] = 0;
				$data['parent'] = $this->config->item('site_name');
				$data['content_hierarchy_content'] = $this->elementar->get_contents_by_parent();
				$data['content_hierarchy_element'] = $this->elementar->get_elements_by_parent();
				$data['content_listing_id'] = NULL;
				$data['content_listing'] = NULL;
				$html = $this->load->view('admin/admin_content_editor', $data, true);
				break;
				
				default :
				$html = "";
				break;
			}

		$response = array(
			'done' => TRUE,
			'html' => $html
		);
		$this->common->ajax_response($response);

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
				$parent_id = $this->elementar->get_content_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( (bool) $tree_id )
				{
					$parent_id = $this->elementar->get_content_parent_id($tree_id);
					$tree = $this->_render_tree_listing($parent_id, $tree, $tree_id);
					$tree_id = $parent_id;
				}
				break;
				
				case "element" : 
				$parent_id = $this->elementar->get_element_parent_id($id);
				$tree = $this->_render_tree_listing($parent_id);
				$tree_id = $parent_id;
				while ( (bool) $tree_id )
				{
					$parent_id = $this->elementar->get_content_parent_id($tree_id);
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
			
		$form = "<hr /><p class=\"page_subtitle\">Campos do Modelo</p>";
		
		$attributes = array('class' => 'content_type_define_new_form', 'id' => 'content_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$attributes = array('class' => 'field_label');
		$form .= form_label("Nome do tipo", "name", $attributes);
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$form .= form_input($attributes);
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";
		
		/*
		 * div field model
		 */
		$form .= "<div id=\"type_define_new_field_0\" class=\"type_define_new_field\" >";
		
		/*
		 * field name
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$form .= form_label("Nome do campo", "field_0");
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$form .= form_input($attributes);
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";

		/*
		 * field type
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$form .= form_label("Tipo do campo", "field_type_0");
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$form .= $this->_render_field_type_dropdown();
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";

		/*
		 * close div field model
		 */
		$form .= "</div> <!-- #type_define_new_field_0 -->";

		$form .= "<p><a href=\"add_type_field\" id=\"add_type_field\">Incluir outro campo</a></p>";
		
		/*
		 * HTML template
		 */
		$form .= "<hr /><p class=\"page_subtitle\">Markup do Modelo</p>";

		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$form .= form_label("Template", "template");
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			'name' => 'template',
			'id' => 'template',
			'rows' => 8,
			'cols' => 32,
			'value' => ''
		);
		$form .= form_textarea($attributes);
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";

		$form .= "<div class=\"form_control_buttons\">";

		$form .= form_submit('type_save', 'Salvar');
		
		$form .= "</div>";

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
			
		$form = "<hr /><p class=\"page_subtitle\">Defini&ccedil;&atilde;o de novo tipo</p>";
		
		$attributes = array('class' => 'element_type_define_new_form', 'id' => 'element_type_define_new_form');
		$hidden = array('field_count' => 1);
		$form .= form_open('#', $attributes, $hidden);

		/*
		 * Type name
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$attributes = array('class' => 'field_label');
		$form .= form_label("Nome do tipo", "name", $attributes);
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			'name' => 'name',
			'id' => 'name'
		);
		$form .= form_input($attributes);
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";
		
		/*
		 * div field model
		 */
		$form .= "<div id=\"type_define_new_field_0\" class=\"type_define_new_field\" >";
		
		/*
		 * field name
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$form .= form_label("Nome do campo", "field_0");
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			"id" => "field_0",
			"name" => "field_0"
		);
		$form .= form_input($attributes);
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";

		/*
		 * field type
		 */
		$form .= "<div class=\"form_content_field\">";
		$form .= "<div class=\"form_window_column_label\">";
		$form .= form_label("Tipo do campo", "field_type_0");
		$form .= br(1);
		$form .= "</div> <!-- form_window_column_label -->";
		$form .= "<div class=\"form_window_column_input\">";
		$form .= $this->_render_field_type_dropdown();
		$form .= "</div> <!-- form_window_column_input -->";
		$form .= "</div> <!-- .form_content_field -->";

		/*
		 * close div field model
		 */
		$form .= "</div> <!-- #type_define_new_field_0 -->";

		$form .= "<p><a href=\"add_type_field\" id=\"add_type_field\">Incluir outro campo</a></p>";
		
		$form .= "<div class=\"form_control_buttons\">";

		$form .= form_submit('type_save', 'Salvar');
		
		$form .= "</div>";

		$form .= form_close();
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);

		$this->common->ajax_response($response);

	}

	/**
	 * Listar campos/snames (tags) do tipo
	 */
	function xhr_render_content_type_tags()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$type_id = $this->input->post('type_id', TRUE);

		$tags = "<p>Tags de " . $this->elementar->get_content_type_name($type_id) . ":</p>";
		$fields = $this->elementar->get_content_type_fields($type_id);
		$list = array();
		foreach ( $fields as $field )
		{
			$list[] = "<strong>" . $field['sname'] . "</strong>: " . $field['name'] . " (" . $field['description'] . ")";
		}
		$attributes = array(
			'class' => 'content_type_tags',
			'id'    => 'content_type_tags_' . $field['sname']
		);
		$tags .= ul($list, $attributes);

		$response = array(
			'done' => TRUE,
			'html' => $tags
		);
		$this->common->ajax_response($response);
	}

	/**
	 * Gerar formulário edição de meta fields
	 */
	function xhr_render_meta_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);

		$name = $this->elementar->get_content_name($id);
		$breadcrumb = $this->common->breadcrumb_content($id);

		if ( $id == "0" ) {
			$name = "Home";
		}
		else
		{
			$name = "“" . $name . "”";
		}
		$form = "<p class=\"page_subtitle\">" . $name . " Meta Fields</p><p>" . $breadcrumb . "</p><hr>";
		$attributes = array(
			'class' => 'noform',
			'name' => 'id',
			'value'=> $id,
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		/*
		 * Meta fields
		 */
		$fields = array(
			'Keywords' => 'keywords',
			'Description' => 'description',
			'Url' => 'url',
			'Author' => 'author',
			'Copyright' => 'copyright'
		);

		foreach ( $fields as $label => $name )
		{
			$form .= "<div class=\"form_content_field\">";
			$form .= "<div class=\"form_window_column_label\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label($label, $name, $attributes);
			$form .= br(1);
			$form .= "</div> <!-- form_window_column_label -->";
			$form .= "<div class=\"form_window_column_input\">";
			$attributes = array(
				'class' => 'noform',
				'name' => $name,
				'id' => $name,
				'value' => $this->elementar->get_meta_field($id, $name)
			);
			$form .= form_input($attributes);
			$form .= "</div> <!-- form_window_column_input -->";
			$form .= "</div> <!-- .form_content_field -->";
		}

		/*
		 *  Botão envio
		 */
		$form .= "<div class=\"form_control_buttons\">";
		$attributes = array(
		    'name' => 'button_meta_save',
		    'id' => 'button_meta_save',
		    'class' => 'noform',
		    'content' => 'Salvar'
		);
		$form .= form_button($attributes);

		$form .= "</div>";
		
		$response = array(
			'done' => TRUE,
			'html' => $form
		);
		$this->common->ajax_response($response);

	}
	
	/**
	 * Gerar formulário para inserção/edicão de menu
	 */
	function xhr_render_menu_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$parent_id = $this->input->post("parent_id", TRUE);
		$id = $this->input->post("id", TRUE);
		
		$data = array(
			'id' => $id,
			'target' => $this->elementar->get_menu_target($id)
		);		
			
		$form = "";
		/*
		 * Menu name
		 */
		$form .= "<div class=\"form_content_field\">";
		$attributes = array('class' => 'field_label');
		$form .= form_label("Nome", "name", $attributes);
		$form .= br(1);
		$attributes = array(
			'class' => 'noform',
			'name' => 'name',
			'id' => 'name',
			'value' => $this->elementar->get_menu_name($id)
		);
		$form .= form_input($attributes);
		$form .= "</div> <!-- .form_content_field -->";

		/*
		 * Menu target
		 */
		$form .= "<div class=\"form_content_field\">";
		$attributes = array('class' => 'field_label');
		$form .= form_label("Destino", "target", $attributes);
		$form .= br(1);
		$attributes = array(
			'class' => 'noform',
			'name' => 'target',
			'id' => 'menu_target',
			'value' => $this->elementar->get_menu_target($id)
		);
		$form .= form_input($attributes);
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = "<p><strong>Destinos internos</strong></p>";
		/*
		 * Conteúdos
		 */
		foreach ( $this->elementar->get_contents() as $content )
		{
			$listing[] = $this->common->breadcrumb_content($content['id']);
		}
		/*
		 * Controllers
		 */
		$controllers = array();		
		foreach( $this->common->controllers(array('Parser','Rss','User')) as $controller )
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
		$attributes = array(
			'class' => 'dropdown_items_listing_targets'
		);
		$form .= "<div class=\"dropdown_items_listing_position\"><div class=\"dropdown_items_listing\">";
		$form .= ul($listing, $attributes);
		$form .= "</div></div>";

		$form .= "</div> <!-- .form_content_field -->"; // target

		/*
		 * Menu parent id (hidden)
		 */
		$attributes = array(
			'class' => 'noform',
			'name' => 'parent_id',
			'value'=> $parent_id,
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		/*
		 * Menu id (hidden)
		 */
		$attributes = array(
			'class' => 'noform',
			'name' => 'id',
			'value'=> $id,
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		$form .= "<div class=\"form_control_buttons\">";

		/*
		 *  Botão envio
		 */
		$attributes = array(
		    'name' => 'button_menu_save',
		    'id' => 'button_menu_save',
		    'class' => 'noform',
		    'content' => 'Salvar'
		);
		$form .= form_button($attributes);

		$form .= "</div>";

		$data['form'] = $form;
		$html = $this->load->view('admin/admin_content_menu_form', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
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
		$data['breadcrumb'] = $this->common->breadcrumb_content($parent_id);
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
		$data['breadcrumb'] = $this->common->breadcrumb_content($parent_id);
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
		$form = "";
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
		return $form;
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
			$parent_id = $this->elementar->get_element_parent_id($element_id);
			$type_id = $this->elementar->get_element_type_id($element_id);		
			$data['breadcrumb'] = $this->common->breadcrumb_element($element_id);
		}
		else
		{
			/*
			 * Create
			 */
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
			$data['breadcrumb'] = $this->common->breadcrumb_element($parent_id);
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
			$form .= "<div class=\"form_content_field\">";
			$form .= "<div class=\"form_window_column_label\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Nome", "name", $attributes);
			$form .= br(1);
			$form .= "</div> <!-- form_window_column_label -->";
			$form .= "<div class=\"form_window_column_input\">";
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->elementar->get_element_name($element_id)
			);
			$form .= form_input($attributes);
			$form .= "</div> <!-- form_window_column_input -->";
			$form .= "</div> <!-- .form_content_field -->";

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
			$fields = $this->elementar->get_element_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$form .= "<div class=\"form_content_field\">";
				$form .= "<div class=\"form_window_column_label\">";
				$attributes = array('class' => 'field_label');
				$form .= form_label($field['name'], $field['sname'], $attributes);
				$form .= br(1);
				$form .= "</div> <!-- form_window_column_label -->";
				
				$form .= "<div class=\"form_window_column_input\">";
				/*
				 * Adequar ao tipo do campo
				 */
				$form .= $this->_render_form_custom_field($field, $this->elementar->get_element_field($element_id, $field['id']));
				$form .= "</div> <!-- form_window_column_input -->";

				$form .= "</div> <!-- .form_content_field -->";
			}

			/*
			 * spread
			 */
			$form .= "<div class=\"form_content_field\">";
			$form .= "<div class=\"form_window_column_label\">";
			if ( (bool) $element_id !== FALSE ) 
			{
				$checked = $this->elementar->get_element_spread($element_id);
			}
			else
			{
				// Default new element to spread
				$checked = TRUE;
			}
			$attributes = array('class' => 'field_label');
			$form .= form_label("Propagar", "spread", $attributes);
			$form .= "</div> <!-- form_window_column_label -->";

			$form .= "<div class=\"form_window_column_input\">";
			$attributes = array(
				'name'        => 'spread',
				'id'          => 'spread',
				'class' => 'noform',
				'value'       => 'true',
				'checked'     => $checked
			);
			$form .= form_checkbox($attributes);
			$form .= "</div> <!-- form_window_column_input -->";
			$form .= "</div> <!-- .form_content_field -->";

			/*
			 * status
			 */
			$form .= "<div class=\"form_content_field\">";
			$form .= "<div class=\"form_window_column_label\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Status", "status", $attributes);
			$form .= "</div> <!-- form_window_column_label -->";
			$form .= "<div class=\"form_window_column_input\">";
			$form .= $this->_render_status_dropdown($this->elementar->get_element_status($element_id));
			$form .= "</div> <!-- form_window_column_input -->";
			$form .= "</div> <!-- .form_content_field -->";

			$form .= "<div class=\"form_control_buttons\">";

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

			$form .= "</div>";
			
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

		if ( (bool) $content_id ) 
		{
			/*
			 * Update
			 */
			$parent_id = $this->elementar->get_content_parent_id($content_id);
			$type_id = $this->elementar->get_content_type_id($content_id);
			$template_id = $this->elementar->get_content_template_id($content_id);
			$template = $this->elementar->get_content_template($content_id);
			$data['breadcrumb'] = $this->common->breadcrumb_content($content_id);
		}
		else
		{
			/*
			 * Create
			 */
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
			$template_id = $this->elementar->get_content_type_template_id($type_id);
			$template = $this->elementar->get_content_type_template($type_id);
			$data['breadcrumb'] = $this->common->breadcrumb_content($parent_id);
		}

		$template_form = '';
		$attributes = array('class' => 'template_form', 'id' => 'template_form_' . $content_id);
		$hidden = array('template_id' => $template_id, 'content_id' => $content_id);
		$template_form .= form_open('/admin/content/xhr_write_template', $attributes, $hidden);

		/*
		 * Sole template
		 */
		$template_form .= "<div class=\"form_content_field\">";
		$template_form .= "<div class=\"form_window_column_label\">";
		$attributes = array('class' => 'field_label');
		$template_form .= form_label("Exclusivo", "sole", $attributes);
		$template_form .= "</div> <!-- form_window_column_label -->";
		$template_form .= "<div class=\"form_window_column_input\">";
		if ( (bool) $content_id ) {
			$checked = $this->elementar->get_content_type_template_id($type_id) != $this->elementar->get_content_template_id($content_id) ;
		}
		else 
		{
			$checked = FALSE;
		}
		$attributes = array(
			'name'        => 'sole',
			'id'          => 'sole_' . $content_id,
			'class' => 'template_form',
			'value'       => 'true',
			'checked'     => (bool) $checked
		);
		$template_form .= form_checkbox($attributes);
		$template_form .= "</div> <!-- form_window_column_input -->";
		$template_form .= "</div> <!-- .form_content_field -->";

		$template_form .= "<div class=\"form_content_field\">";
		$template_form .= "<div class=\"form_window_column_label\">";
		$attributes = array('class' => 'field_label');
		$template_form .= form_label("Template", 'template_' . $content_id, $attributes);
		$template_form .= br(1);
		$template_form .= "</div> <!-- form_window_column_label -->";
		$template_form .= "<div class=\"form_window_column_input\">";
		$attributes = array(
			'name' => 'template',
			'class' => 'template_textarea',
			'id' => 'template_' . $content_id,
			'rows' => 16,
			'cols' => 32,
			'value' => $template
		);
		$template_form .= form_textarea($attributes);
		$template_form .= "</div> <!-- form_window_column_input -->";
		$template_form .= "</div> <!-- .form_content_field -->";
		$template_form .= "<div class=\"form_control_buttons\">";
		$attributes = array(
		    'name' => 'button_template_save',
		    'id' => 'button_template_save',
		    'value' => 'Salvar'
		);
		$template_form .= form_submit($attributes);
		$template_form .= "</div> <!-- form_control_buttons -->";
		$template_form .= form_close();
		$data['template_form'] = $template_form;


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
			$content_form .= "<div class=\"form_content_field\">";
			$content_form .= "<div class=\"form_window_column_label\">";
			$attributes = array('class' => 'field_label');
			$content_form .= form_label("Nome", "name", $attributes);
			$content_form .= br(1);
			$content_form .= "</div> <!-- form_window_column_label -->";
			
			$content_form .= "<div class=\"form_window_column_input\">";
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->elementar->get_content_name($content_id)
			);
			$content_form .= form_input($attributes);
			$content_form .= "</div> <!-- form_window_column_input -->";

			$content_form .= "</div> <!-- .form_content_field -->";

			$fields = $this->elementar->get_content_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$content_form .= "<div class=\"form_content_field\">";
				$content_form .= "<div class=\"form_window_column_label\">";
				$attributes = array('class' => 'field_label');
				$content_form .= form_label($field['name'], $field['sname'], $attributes);
				
				$content_form .= br(1);
				$content_form .= "</div> <!-- form_window_column_label -->";
				
				$content_form .= "<div class=\"form_window_column_input\">";
				/*
				 * Adequar ao tipo do campo
				 */
				$content_form .= $this->_render_form_custom_field($field, $this->elementar->get_content_field($content_id, $field['id']));
				$content_form .= "</div> <!-- form_window_column_input -->";

				$content_form .= "</div> <!-- .form_content_field -->";
			}

			/*
			 * status
			 */
			$content_form .= "<div class=\"form_content_field\">";
			$content_form .= "<div class=\"form_window_column_label\">";
			$attributes = array('class' => 'field_label');
			$content_form .= form_label("Status", "status", $attributes);
			$content_form .= br(1);
			$content_form .= "</div> <!-- form_window_column_label -->";
			$content_form .= "<div class=\"form_window_column_input\">";
			$content_form .= $this->_render_status_dropdown($this->elementar->get_content_status($content_id));
			$content_form .= "</div> <!-- form_window_column_input -->";
			$content_form .= "</div> <!-- .form_content_field -->";

			$content_form .= "<div class=\"form_control_buttons\">";
			/*
			 *  Botão envio
			 */
			$attributes = array(
			    'name' => 'button_cont_save',
			    'id' => 'button_cont_save',
			    'class' => 'noform',
			    'content' => 'Salvar'
			);
			$content_form .= form_button($attributes);

			$content_form .= "</div> <!-- form_control_buttons -->";
			
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
		$types = $this->elementar->get_content_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $selected )
			{
				$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . $selected . "\">" . $this->elementar->get_content_type_name($selected) . "</a>";
			}
			else
			{
				$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . key($types) . "\">" . current($types) . "</a>";
			}
		}
		else
		{
			$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"0\">Novo...</a>";			
		}
		$dropdown .= "<div class=\"dropdown_items_listing_position\">";
		$dropdown .= "<div class=\"dropdown_items_listing\">";
		$dropdown .= "<ul class=\"dropdown_items_listing_targets\">";
		foreach ( $types as $type_id => $type )
		{
			$dropdown .= "<li><a class=\"dropdown_items_listing_content_type_target\" href=\"" . $type_id . "\">" . $type . "</a></li>";
		}
		// "New" link
		$dropdown .= "<li><a id=\"content_type_create\" class=\"dropdown_items_listing_content_type_target\" href=\"0\">Novo...</a></li>";
		$dropdown .= "</ul>";
		$dropdown .= "</div></div>";
		$dropdown .= "</div>";
		return $dropdown;
	}
	
	/**
	 * Element types HTML dropdown
	 * @param integer $selected Selected content type (id)
	 * @return HTML content (html dropdown widget)
	 */
	function _render_element_types_dropdown($selected = NULL )
	{
		$types = $this->elementar->get_element_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $selected )
			{
				$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . $selected . "\">" . $this->elementar->get_element_type_name($selected) . "</a>";
			}
			else
			{
				$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . key($types) . "\">" . current($types) . "</a>";
			}
		}
		else
		{
			$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"0\">Novo...</a>";			
		}
		$dropdown .= "<div class=\"dropdown_items_listing_position\">";
		$dropdown .= "<div class=\"dropdown_items_listing\">";
		$dropdown .= "<ul class=\"dropdown_items_listing_targets\">";
		foreach ( $types as $type_id => $type )
		{
			$dropdown .= "<li><a class=\"dropdown_items_listing_element_type_target\" href=\"" . $type_id . "\">" . $type . "</a></li>";
		}
		// "New" link
		$dropdown .= "<li><a id=\"element_type_create\" class=\"dropdown_items_listing_element_type_target\" href=\"0\">Novo...</a></li>";
		$dropdown .= "</ul>";
		$dropdown .= "</div></div>";
		$dropdown .= "</div>";
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
		$attributes = "id=\"new_cont_status\" class=\"noform\"";
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
		foreach ( $this->elementar->get_field_types() as $option )
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
		$template_id = $this->elementar->put_template($template);
		$type_id = $this->elementar->put_content_type($name, $template_id);
		
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
					$this->elementar->put_content_type_field($type_id, $field, $sname, $field_type);
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

		$type_id = $this->elementar->put_element_type($name, $sname);
		
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
					$this->elementar->put_element_type_field($type_id, $field, $sname, $field_type);
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
			$content_id = $this->elementar->put_content($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->elementar->put_content_name($content_id, $name, $sname);
		}
		
		/* 
		 * Armazenar campos
		 */
		foreach ( $this->elementar->get_content_type_fields($type_id) as $type)
		{
			$value = $this->input->post($type['sname'], TRUE);
			$this->elementar->put_content_field($content_id, $type['id'], $value);
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
					$this->elementar->put_image_title($image_id, $image_title);
				}
				break;
			}
		}
		
		/*
		 * Parent
		 */
		$parent_id = (int) $this->input->post('parent_id', TRUE);
		$this->elementar->put_content_parent($content_id, $parent_id);

		/* 
		 * Armazenar status
		 */
		$this->elementar->put_content_status($content_id, $this->input->post('status', TRUE));
		
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
		$this->elementar->delete_content($content_id);

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
		$this->elementar->delete_element($element_id);

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
			$element_id = $this->elementar->put_element($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->elementar->put_element_name($element_id, $name, $sname);
		}

		/* 
		 * Armazenar campos
		 */
		foreach ( $this->elementar->get_element_type_fields($type_id) as $type)
		{
			$value = $this->input->post($type['sname'], TRUE);
			$this->elementar->put_element_field($element_id, $type['id'], $value);
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
					$this->elementar->put_image_title($image_id, $image_title);
				}
				break;
			}
		}
		
		/*
		 * Write spread option
		 */
		if ( $this->input->post('spread', TRUE) )
		{
			$this->elementar->put_element_spread($element_id, TRUE);
		}
		else
		{
			$this->elementar->put_element_spread($element_id, FALSE);
		}

		/*
		 * Parent
		 */
		$this->elementar->put_element_parent($element_id, $parent_id);

		/* 
		 * Armazenar status
		 */
		$this->elementar->put_element_status($element_id, $this->input->post('status', TRUE));
		
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

		$data['parent'] = $this->elementar->get_content_name($id);
		$data['content_hierarchy_content'] = $this->elementar->get_contents_by_parent($id);
		$data['content_hierarchy_element'] = $this->elementar->get_elements_by_parent($id);
		// Inner listings, if any
		$data['content_listing_id'] = $listing_id;
		$data['content_listing'] = $listing;
		
		$html = $this->load->view('admin/admin_content_editor_tree', $data, true);
		
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

			$this->elementar->put_content_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->elementar->get_content_name($id), ENT_QUOTES, "UTF-8")
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

			$this->elementar->put_element_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->elementar->get_element_name($id), ENT_QUOTES, "UTF-8")
			);
		}			

		$this->common->ajax_response($response);

	}

	/**
	 * renomear menu
	 */
	function xhr_rename_menu() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$menu_id = $this->input->post('id', TRUE);
		
		$name = $this->input->post('name', TRUE);
		
		if ($menu_id != "" && $name != "" ) 
		{
			$sname = $this->common->normalize_string($name);

			$this->elementar->put_menu_name($menu_id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
			$this->common->ajax_response($response);
		}

	}

	/**
	 * Remover menu
	 */
	function xhr_erase_menu() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$menu_id = $this->input->post('id', TRUE);
		
		if ( (bool) $menu_id ) 
		{
			$this->elementar->delete_menu($menu_id);

			$response = array(
				'done' => TRUE
			);
			$this->common->ajax_response($response);
		}

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
			'Url' => 'url',
			'Author' => 'author',
			'Copyright' => 'copyright'
		);

		foreach ( $fields as $label => $name )
		{
			$value = $this->input->post($name, TRUE);
			if ( (bool) $value )
			{
				$this->elementar->put_meta_field($id, $name, $value);
			}
			else
			{
				// Remove meta field
				$this->elementar->delete_meta_field($id, $name);
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
		$template = $this->input->post('template', TRUE);
		
		/*
		 * Exclusive template ?
		 */
		if ( $this->input->post('sole', TRUE) )
		{
			if ( (bool) $content_id ) 
			{
				/*
				 * content_id received, means that 
				 * it's not a new content
				 */
				$content_type_template_id = $this->elementar->get_content_type_template_id($this->elementar->get_content_type_id($content_id));
				if ( $content_type_template_id != $template_id )
				{
					/*
					 * Content already have exclusive template, update it!
					 */
					$this->elementar->put_template($template, $template_id);
				}
				else
				{
					/*
					 * Add a new template for this content
					 */
					$content_template_id = $this->elementar->put_template($template);
					$this->elementar->put_content_template_id($content_id, $content_template_id);
				}
			}
		}
		else
		{
			if ( (bool) $content_id ) 
			{
				/*
				 * Ensure that content has no exclusive template
				 */
				$content_template_id = $this->elementar->get_content_template_id($content_id);
				if ( $content_template_id != $template_id )
				{
					$this->elementar->put_content_template_id($content_id, NULL);
					$this->elementar->delete_template($content_template_id);
				}
			}
			$this->elementar->put_template($template, $template_id);
		}

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE
		);
		
		$this->common->ajax_response($response);

	}

	/**
	 * Criar menu
	 */
	function xhr_write_menu() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);
		$parent_id = $this->input->post('parent_id', TRUE);
		$name = $this->input->post('name', TRUE);
		$target = $this->input->post('target', TRUE);
		
		if ( (bool) $name ) 
		{
			$sname = $this->common->normalize_string($name);
			
			if ( (bool) $id ) 
			{
				/*
				 * Update
				 */
				$this->elementar->put_menu_name($id, $name, $sname);
				$this->elementar->put_menu_target($id, $target);
			}
			else {
				$level = $this->elementar->get_menu_level($parent_id) + 1;
				$id = $this->elementar->put_menu($name, $sname, $parent_id, $level);
				$this->elementar->put_menu_target($id, $target);
			}
			$response = array(
				'done' => TRUE
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome do menu"
			);
		}
		$this->common->ajax_response($response);

	}

	/**
	 * Salvar menu target
	 */
	function xhr_write_menu_target()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$menu_id = $this->input->post('menu_id', TRUE);
		
		$target = $this->input->post('target', TRUE);
		
		$this->elementar->put_menu_target($menu_id, $target);

		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE
		);
		
		$this->common->ajax_response($response);

	}
	
	/**
	 * Atualizar árvore de menus
	 */
	function xhr_write_menu_tree() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$parent_id = $this->input->post("parent_id", TRUE);
		$level = $this->elementar->get_menu_level($parent_id) + 1;
		$menus = $this->input->post("menus", TRUE);
		
		// Atualizar parent, order & level
		foreach ( $menus as $order => $menu_id ) {
			$this->elementar->put_menu_parent($menu_id, $parent_id);
			$this->elementar->put_menu_level($menu_id, $level);
			$this->elementar->put_menu_order($menu_id, $order);
		}
		
		/*
		 * resposta
		 */
		$response = array(
			'done' => TRUE
		);
		
		$this->common->ajax_response($response);
	}
	
}
