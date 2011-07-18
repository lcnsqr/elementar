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
		$this->load->model('M_cms_admin', 'cms');
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common');

		/*
		 * Site specific library
		 */
		$this->load->library('special');

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
			'/js/admin_client_warning.js',
			'/js/admin_content_tree.js',
			'/js/admin_content_window.js',
			'/js/admin_content_menu.js',
			'/js/admin_anchor.js',
			'/js/admin_content_anchor_section.js',
			'/js/admin_content_anchor_editor.js',
			'/js/admin_upload.js',
			'/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js'
		);
		
		/*
		 * Resource menu
		 */
		$resource_menu = "<ul><li><a href=\"/admin/account\" title=\"Usuários\">Usuários</a></li><li>|</li><li><strong>Conteúdo</strong></li></ul>";

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
				$data['menus'] = $this->cms->get_menus();
				$html = $this->load->view('admin/admin_content_menu', $data, true);
				break;
				
				case "editor" :
				$data['parent_id'] = 0;
				$data['parent'] = $this->config->item('site_name');
				$data['content_hierarchy_category'] = $this->cms->get_categories_by_parent();
				$data['content_hierarchy_content'] = $this->cms->get_contents_by_category();
				$data['content_hierarchy_element'] = $this->cms->get_element_by_category();
				$data['category_listing_id'] = NULL;
				$data['category_listing'] = NULL;
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
				$parent_id = $this->cms->get_content_category($id);
				$tree_request = "category";
				$tree = $this->_render_tree_listing($parent_id, $tree_request);
				$tree_id = $parent_id;
				while ( (bool) $tree_id )
				{
					$parent_id = $this->cms->get_category_parent($tree_id);
					$tree = $this->_render_tree_listing($parent_id, $tree_request, $tree, $tree_id);
					$tree_id = $parent_id;
				}
				break;
				
				case "element" : 
				$parent_id = $this->cms->get_element_parent($id);
				$tree_request = $this->cms->get_element_parent_type($id);
				$tree = $this->_render_tree_listing($parent_id, $tree_request);
				$tree_id = $parent_id;
				while ( (bool) $tree_id )
				{
					switch ( $tree_request )
					{
						case "category" :
						$parent_id = $this->cms->get_category_parent($tree_id);
						break;
						
						case "content" :
						$parent_id = $this->cms->get_content_category($tree_id);
						break;
					}
					$tree_request = "category";
					$tree = $this->_render_tree_listing($parent_id, $tree_request, $tree, $tree_id);
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

		$name = $this->input->post('name', TRUE);
		
		if ($name != "" ) 
		{
			$sname = $this->common->normalize_string($name);
			
			$form = "<p class=\"page_subtitle\">" . $name . "</p>";
			
			$attributes = array('class' => 'type_define_new_form', 'id' => 'type_define_new_form');
			$hidden = array('name' => $name, 'field_count' => 1);
			$form .= form_open('#', $attributes, $hidden);
			
			/*
			 * div field model
			 */
			$form .= "<div id=\"type_define_new_field_0\" class=\"type_define_new_field\" >";
			
			/*
			 * field name
			 */
			$form .= "<p>";
			$form .= form_label("Nome do campo", "field_0");
			$form .= br(1);
			$attributes = array(
				"id" => "field_0",
				"name" => "field_0"
			);
			$form .= form_input($attributes);
			$form .= "</p>";

			/*
			 * field type
			 */
			$form .= "<p>";
			$form .= form_label("Tipo do campo", "field_type_0");
			$form .= br(1);
			$form .= $this->_render_field_type_dropdown();
			$form .= "</p>";

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
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome para o novo tipo"
			);
		}
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

		$tags = "<p>Tags de " . $this->cms->get_content_type_name($type_id) . ":</p>";
		$fields = $this->cms->get_content_type_fields($type_id);
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
		$type = $this->input->post('request', TRUE);

		switch ( $type )
		{
			case "category" :
			$name = $this->cms->get_category_name($id);
			$breadcrumb = $this->common->breadcrumb_category($id);
			break;
			
			case "content" :
			$name = $this->cms->get_content_name($id);
			$breadcrumb = $this->common->breadcrumb_content($id);
			break;
		}

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

		$attributes = array(
			'class' => 'noform',
			'name' => 'type',
			'value'=> $type,
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
			$attributes = array('class' => 'field_label');
			$form .= form_label($label, $name, $attributes);
			$form .= br(1);
			$attributes = array(
				'class' => 'noform',
				'name' => $name,
				'id' => $name,
				'value' => $this->cms->get_meta_field($id, $type, $name)
			);
			$form .= form_input($attributes);
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
	 * Gerar formulário para inserção/edicão de categoria
	 */
	function xhr_render_category_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$parent_id = $this->input->post("parent_id", TRUE);
		if ( $parent_id !== FALSE )
		{
			$data = array();
			$data['breadcrumb'] = $this->common->breadcrumb_category($parent_id);
			$data['parent_id'] = $parent_id;
			
			$form = "";
			/*
			 * Category name
			 */
			$form .= "<div class=\"form_content_field\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Nome", "name", $attributes);
			$form .= br(1);
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name'
			);
			$form .= form_input($attributes);
			$form .= "</div> <!-- .form_content_field -->";

			/*
			 * Category parent_id (hidden)
			 */
			$attributes = array(
				'class' => 'noform',
				'name' => 'parent_id',
				'value'=> $parent_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			$form .= "<div class=\"form_control_buttons\">";

			/*
			 *  Botão envio
			 */
			$attributes = array(
			    'name' => 'button_category_save',
			    'id' => 'button_category_save',
			    'class' => 'noform',
			    'content' => 'Salvar'
			);
			$form .= form_button($attributes);

			$form .= "</div>";

			$data['form'] = $form;
			$html = $this->load->view('admin/admin_content_category', $data, true);

			$response = array(
				'done' => TRUE,
				'html' => $html
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome para a categoria"
			);
		}
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
			'target' => $this->cms->get_menu_target($id)
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
			'value' => $this->cms->get_menu_name($id)
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
			'value' => $this->cms->get_menu_target($id)
		);
		$form .= form_input($attributes);
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = "<p><strong>Destinos internos</strong></p>";
		foreach ( $this->cms->get_contents() as $content )
		{
			$listing[] = $this->common->breadcrumb_content($content['id']);
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

		$category_id = $this->input->post('id', TRUE);
		
		$data = array();
		$data['content_id'] = NULL;
		$data['category_id'] = $category_id;
		$data['breadcrumb'] = $this->common->breadcrumb_category($category_id);
		$data['content_types_dropdown'] = $this->_render_content_types_dropdown();
		
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
		 * Determine if it's for a category or element 
		 */

		$parent = $this->input->post('parent', TRUE);
		$parent_id = $this->input->post('id', TRUE);

		$data = array();
		$data['element_id'] = NULL;
		$data['parent_id'] = $parent_id;
		$data['parent'] = $parent;
		switch ($parent)
		{
			case "category" :
			$data['breadcrumb'] = $this->common->breadcrumb_category($parent_id);
			break;
			case "content" :
			$data['breadcrumb'] = $this->common->breadcrumb_content($parent_id);
			break;
		}
		$data['element_types_dropdown'] = $this->_render_element_types_dropdown();
		
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
			$attributes = array(
				'class' => 'noform ' . $field['type'],
				'name' => $field['sname'],
				'id' => $field['sname'],
				'rows' => 8,
				'cols' => 60,
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
		if ( (bool) $element_id !== FALSE ) 
		{
			/*
			 * Update. Render breadcrumb and type form too
			 */
			$parent = $this->cms->get_element_parent_type($element_id);
			$parent_id = $this->cms->get_element_parent($element_id);
			$type_id = $this->cms->get_element_type($element_id);		

			$data = array();
			$data['element_id'] = $element_id;
			$data['parent_id'] = intval($parent_id);
			$data['parent'] = $parent;
			switch ($parent)
			{
				case "category" :
				$data['breadcrumb'] = $this->common->breadcrumb_category($parent_id);
				break;
				case "content" :
				$data['breadcrumb'] = $this->common->breadcrumb_content($parent_id);
				break;
			}
			$data['element_types_dropdown'] = $this->_render_element_types_dropdown($type_id);
			$form = $this->load->view('admin/admin_content_element_new', $data, true);
		}
		else
		{
			$form = "";
			$parent = $this->input->post('parent', TRUE);
			$parent_id = $this->input->post('parent_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
		}
		
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
			$attributes = array('class' => 'field_label');
			$form .= form_label("Nome", "name", $attributes);
			$form .= br(1);
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->cms->get_element_name($element_id)
			);
			$form .= form_input($attributes);
			$form .= "</div> <!-- .form_content_field -->";

			/*
			 * Element parent (hidden)
			 */
			$attributes = array(
				'class' => 'noform',
				'name' => 'parent',
				'value'=> $parent,
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
			 * Element type fields
			 */
			$fields = $this->cms->get_element_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$form .= "<div class=\"form_content_field\">";
				$attributes = array('class' => 'field_label');
				$form .= form_label($field['name'], $field['sname'], $attributes);
				$form .= br(1);
				
				/*
				 * Adequar ao tipo do campo
				 */
				$form .= $this->_render_form_custom_field($field, $this->cms->get_element_field($element_id, $field['id']));

				$form .= "</div> <!-- .form_content_field -->";
			}
			
			/*
			 * Categorias
			 */
/*
			$checkbox = "";
			$attributes = array("id" => "category_fieldset");
			$checkbox .= form_fieldset('Categorias', $attributes);
			$cats = $this->cms->get_categories();
			$checkbox .= $this->_render_form_get_children_cat($cats);
			$checkbox .= form_fieldset_close(); 
			$form .= "<div class=\"form_cont_field\">";
			$form .= $checkbox;
			$form .= "</div> <!-- .form_cont_field -->";
*/

			/*
			 * status
			 */
			$form .= "<div class=\"form_cont_field\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Status", "status", $attributes);
			$form .= br(1);
			$form .= $this->_render_status_dropdown($this->cms->get_element_status($element_id));
			$form .= "</div> <!-- .form_cont_field -->";

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
			
			$response = array(
				'done' => TRUE,
				'form' => $form
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
		if ( (bool) $content_id ) 
		{
			/*
			 * Update. Render breadcrumb and type form too
			 */
			$category_id = $this->cms->get_content_category($content_id);
			$type_id = $this->cms->get_content_type($content_id);
			$data = array();
			$data['content_id'] = $content_id;
			$data['category_id'] = $category_id;
			$data['breadcrumb'] = $this->common->breadcrumb_category($category_id);
			$data['content_types_dropdown'] = $this->_render_content_types_dropdown($type_id);
			
			$form = $this->load->view('admin/admin_content_new', $data, true);
		}
		else
		{
			$form = "";
			$category_id = $this->input->post('category_id', TRUE);
			$type_id = $this->input->post('type_id', TRUE);
		}

		if ( $type_id != "" ) 
		{
			$attributes = array(
				'class' => 'noform',
				'name' => 'content_id',
				'value'=> $content_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			$attributes = array(
				'class' => 'noform',
				'name' => 'type_id',
				'value'=> $type_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			$attributes = array(
				'class' => 'noform',
				'name' => 'category_id',
				'value'=> $category_id,
				'type' => 'hidden'
			);
			$form .= form_input($attributes);

			/*
			 * Content name
			 */
			$form .= "<div class=\"form_content_field\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Nome", "name", $attributes);
			$form .= br(1);
			$attributes = array(
				'class' => 'noform',
				'name' => 'name',
				'id' => 'name',
				'value' => $this->cms->get_content_name($content_id)
			);
			$form .= form_input($attributes);
			$form .= "</div> <!-- .form_content_field -->";

			$fields = $this->cms->get_content_type_fields($type_id);
			foreach ( $fields as $field )
			{
				$form .= "<div class=\"form_content_field\">";
				$attributes = array('class' => 'field_label');
				$form .= form_label($field['name'], $field['sname'], $attributes);
				
				$form .= br(1);
				
				/*
				 * Adequar ao tipo do campo
				 */
				$form .= $this->_render_form_custom_field($field, $this->cms->get_content_field($content_id, $field['id']));

				$form .= "</div> <!-- .form_content_field -->";
			}

			$form .= br(1);
			
			/*
			 * Categorias
			 */
/*
			$checkbox = "";
			$attributes = array("id" => "category_fieldset");
			$checkbox .= form_fieldset('Categorias', $attributes);
			$cats = $this->cms->get_categories();
			$checkbox .= $this->_render_form_get_children_cat($cats, $content_id);
			$checkbox .= form_fieldset_close(); 
			$form .= "<div class=\"form_cont_field\">";
			$form .= $checkbox;
			$form .= "</div> <!-- .form_cont_field -->";
*/

			/*
			 * status
			 */
			$form .= "<div class=\"form_cont_field\">";
			$attributes = array('class' => 'field_label');
			$form .= form_label("Status", "status", $attributes);
			$form .= br(1);
			$form .= $this->_render_status_dropdown($this->cms->get_content_status($content_id));
			$form .= "</div> <!-- .form_cont_field -->";

			$form .= "<div class=\"form_control_buttons\">";
			/*
			 *  Botão envio
			 */
			$attributes = array(
			    'name' => 'button_cont_save',
			    'id' => 'button_cont_save',
			    'class' => 'noform',
			    'content' => 'Salvar'
			);
			$form .= form_button($attributes);

			$form .= "</div>";
			
			$response = array(
				'done' => TRUE,
				'form' => $form
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome para o conteúdo"
			);
		}
		$this->common->ajax_response($response);

	}

	/**
	 * Listar subcategorias no form 
	 * de criação/atualização de conteúdo
	 * (checkbox)
	 * @param array $cats Categorias/Subcategorias
	 * @param integer $content_id The associated content
	 * @return HTML content (list of checkboxes)
	 */
	function _render_form_get_children_cat($cats, $content_id = NULL)
	{
		$checkbox = "";
		foreach ( $cats as $cat )
		{
			$attributes = array(
				'class'       => 'noform',
			    'name'        => 'category[]',
			    'id'          => $cat['sname']."_".$cat['id'],
			    'value'       => $cat['id'],
			    'checked'     => $this->cms->get_category_has_content($cat['id'], $content_id),
			    'style'       => "margin-left: " . (15 * $cat['level']) . "px"
		    );
		    
			$checkbox .= form_checkbox($attributes);
			
			$checkbox .= form_label($cat['name'], $cat['sname']."_".$cat['id']);
			$checkbox .= br(1);

			if ( isset($cat['children']) )
				$checkbox .= $this->_render_form_get_children_cat($cat['children'], $content_id);

		}
		return $checkbox;
	}
	
	/**
	 * Content types HTML dropdown
	 * @param integer $selected Selected content type (id)
	 * @param string $id HTML element id
	 * @return HTML content (html widget)
	 */
	function _render_content_types_dropdown_bak($selected = "1", $id = "content_type" )
	{
		$options = $this->cms->get_content_types();
		$attributes = "id=\"" . $id . "\"";
		return form_dropdown('content_types', $options, $selected, $attributes);
	}
	function _render_content_types_dropdown($selected = "1", $id = "element_type" )
	{
		$types = $this->cms->get_content_types();
		$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . key($types) . "\">" . current($types) . "</a>";
		$dropdown .= "<div class=\"dropdown_items_listing_position\">";
		$dropdown .= "<div class=\"dropdown_items_listing\">";
		$dropdown .= "<ul class=\"dropdown_items_listing_targets\">";
		foreach ( $types as $type_id => $type )
		{
			$dropdown .= "<li><a class=\"dropdown_items_listing_content_type_target\" href=\"" . $type_id . "\">" . $type . "</a></li>";
		}
		$dropdown .= "</ul>";
		$dropdown .= "</div></div>";
		$dropdown .= "</div>";
		return $dropdown;
	}
	
	/**
	 * Element types HTML dropdown
	 * @param integer $selected Selected content type (id)
	 * @param string $id HTML element id
	 * @return HTML content (html dropdown widget)
	 */
	function _render_element_types_dropdown($selected = "1", $id = "element_type" )
	{
		$types = $this->cms->get_element_types();
		$dropdown = "<div class=\"dropdown_items_listing_inline\"><a class=\"up\" href=\"" . key($types) . "\">" . current($types) . "</a>";
		$dropdown .= "<div class=\"dropdown_items_listing_position\">";
		$dropdown .= "<div class=\"dropdown_items_listing\">";
		$dropdown .= "<ul class=\"dropdown_items_listing_targets\">";
		foreach ( $types as $type_id => $type )
		{
			$dropdown .= "<li><a class=\"dropdown_items_listing_element_type_target\" href=\"" . $type_id . "\">" . $type . "</a></li>";
		}
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
		foreach ( $this->cms->get_content_field_types() as $option )
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
		
		$name = $this->input->post('name', TRUE);
		
		$name = htmlentities($name, ENT_QUOTES, "UTF-8");
		
		$type_id = $this->cms->put_type($name);
		
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
				$this->cms->put_type_field($type_id, $field, $sname, $field_type);
			}
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
			$content_id = $this->cms->put_content($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->cms->put_content_name($content_id, $name, $sname);
		}
		
		/* 
		 * Armazenar campos
		 */
		foreach ( $this->cms->get_content_type_fields($type_id) as $type)
		{
			$value = $this->input->post($type['sname'], TRUE);
			$this->cms->put_content_field($content_id, $type['id'], $value);
		}
		
		/*
		 * Categoria
		 */
		$parent_id = $this->input->post('category_id', TRUE);
		if ( (bool) $parent_id ) 
		{
			$this->cms->put_content_category($content_id, $parent_id);
		}
		
		/*
		 * Armazenar categorias
		 */
/*
		if ( $this->input->post('category', TRUE) )
		{
			$this->cms->delete_content_rel($content_id);
			foreach ( $this->input->post('category', TRUE) as $cat)
			{
				$this->cms->put_content_category($content_id, $cat);
			}
		}
		else 
		{
			$this->cms->delete_content_rel($content_id);
		}

*/
		/* 
		 * Armazenar status
		 */
		$this->cms->put_content_status($content_id, $this->input->post('status', TRUE));
		
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
		$this->cms->delete_content($content_id);

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
		$this->cms->delete_element($element_id);

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
		$parent = $this->input->post('parent', TRUE);
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
			$element_id = $this->cms->put_element($name, $sname, $type_id);
		}
		elseif ( $name != "" ) 
		{
			// Renomear
			$this->cms->put_element_name($element_id, $name, $sname);
		}

		/* 
		 * Armazenar campos
		 */
		foreach ( $this->cms->get_element_type_fields($type_id) as $type)
		{
			$value = $this->input->post($type['sname'], TRUE);
			$this->cms->put_element_field($element_id, $type['id'], $value);
		}
		
		/*
		 * Armazenar categorias
		 */
		switch ( $parent ) 
		{
			case "category" :
			if ( (bool) $parent_id ) 
			{
				$this->cms->put_element_category($element_id, $parent_id);
			}
			break;
			
			case "content" :
			$this->cms->put_element_content($element_id, $parent_id);
			break;
		}
			
/*
		if ( $this->input->post('category', TRUE) )
		{
			foreach ( $this->input->post('category', TRUE) as $cat)
			{
				$this->cms->put_content_category($content_id, $cat);
			}
		}
*/

		/* 
		 * Armazenar status
		 */
		$this->cms->put_element_status($element_id, $this->input->post('status', TRUE));
		
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
	 * Listar categorias/conteúdos
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
		
		$request = $this->input->post('request');

		$html = $this->_render_tree_listing($id, $request);

		$response = array(
			'done' => TRUE,
			'id' => $id,
			'html' => $html
		);
		$this->common->ajax_response($response);
		
	}
	
	function _render_tree_listing($id, $request, $listing = NULL, $listing_id = NULL)
	{
		$data['parent_id'] = $id;

		/*
		 * Categoria ou conteúdo
		 */
		switch ( $request )
		{
			case 'category' :
			$data['parent'] = $this->cms->get_category_name($id);
			$data['content_hierarchy_category'] = $this->cms->get_categories_by_parent($id);
			$data['content_hierarchy_content'] = $this->cms->get_contents_by_category($id);
			$data['content_hierarchy_element'] = $this->cms->get_element_by_category($id);
			// Inner listings, if any
			$data['category_listing_id'] = $listing_id;
			$data['category_listing'] = $listing;
			$data['content_listing_id'] = NULL;
			$data['content_listing'] = NULL;
			break;

			case 'content' :
			$data['parent'] = $this->cms->get_content_name($id);
			$data['content_hierarchy_category'] = NULL;
			$data['content_hierarchy_content'] = NULL;
			$data['content_hierarchy_element'] = $this->cms->get_element_by_content($id);
			// Inner listings, if any
			$data['category_listing_id'] = NULL;
			$data['category_listing'] = NULL;
			$data['content_listing_id'] = $listing_id;
			$data['content_listing'] = $listing;
			break;
		}
		
		$html = $this->load->view('admin/admin_content_editor_tree', $data, true);
		
		return $html;
	}
	
	/**
	 * renomear categoria
	 */
	function xhr_rename_category() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$id = $this->input->post('id', TRUE);
		
		$name = $this->input->post('name', TRUE);

		if ($id != "" && $name != "" ) 
		{
			$sname = $this->common->normalize_string($name);

			$this->cms->put_category_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->cms->get_category_name($id), ENT_QUOTES, "UTF-8")
			);
		}			

		$this->common->ajax_response($response);

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

			$this->cms->put_content_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->cms->get_content_name($id), ENT_QUOTES, "UTF-8")
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

			$this->cms->put_element_name($id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
		}
		else {
			$response = array(
				'done' => FALSE,
				'error' => 'Nome inválido',
				'name' => html_entity_decode($this->cms->get_element_name($id), ENT_QUOTES, "UTF-8")
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

			$this->cms->put_menu_name($menu_id, $name, $sname);

			$response = array(
				'done' => TRUE,
				'sname' => $sname
			);
			$this->common->ajax_response($response);
		}

	}

	/**
	 * Remover categoria
	 */
	function xhr_erase_category() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$category_id = $this->input->post('id', TRUE);
		
		if ($category_id != "" ) 
		{
			$this->cms->delete_category($category_id);

			$response = array(
				'done' => TRUE
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
			$this->cms->delete_menu($menu_id);

			$response = array(
				'done' => TRUE
			);
			$this->common->ajax_response($response);
		}

	}

	/**
	 * Criar categoria
	 */
	function xhr_write_category() 
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$name = $this->input->post('name', TRUE);
		
		if ($name != "" ) 
		{
			$sname = $this->common->normalize_string($name);
			
			if ( $this->input->post('parent_id', TRUE) ) 
			{
				$parent_id = $this->input->post('parent_id', TRUE);
				$level = $this->cms->get_category_level($parent_id) + 1;
			}
			else {
				$parent_id = NULL;
				$level = 0;
			}
			
			$id = $this->cms->put_category($name, $sname, $parent_id, $level);
			
			if ( $id )
			{
				$response = array(
					'done' => TRUE,
					'name' => $name,
					'sname' => $sname,
					'id' => $id,
					'level' => $level
				);
			}
			else 
			{
				$response = array(
					'done' => FALSE,
					'error' => "Erro ao inserir a categoria"
				);
			}
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Forneça o nome da categoria"
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
		$type = $this->input->post('type', TRUE);
		
		if ( (bool) $type ) 
		{

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
				$content = $this->input->post($name, TRUE);
				if ( (bool) $content )
				{
					$this->cms->put_meta_field($id, $type, $name, $content);
				}
				else
				{
					// Remove meta field
					$this->cms->delete_meta_field($id, $type, $name);
				}
			}
			
			$response = array(
				'done' => TRUE
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE,
				'error' => "Erro ao gravar campos"
			);
		}
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
				$this->cms->put_menu_name($id, $name, $sname);
				$this->cms->put_menu_target($id, $target);
			}
			else {
				$level = $this->cms->get_menu_level($parent_id) + 1;
				$id = $this->cms->put_menu($name, $sname, $parent_id, $level);
				$this->cms->put_menu_target($id, $target);
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
		
		$this->cms->put_menu_target($menu_id, $target);

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
		$level = $this->cms->get_menu_level($parent_id) + 1;
		$menus = $this->input->post("menus", TRUE);
		
		// Atualizar parent, order & level
		foreach ( $menus as $order => $menu_id ) {
			$this->cms->put_menu_parent($menu_id, $parent_id);
			$this->cms->put_menu_level($menu_id, $level);
			$this->cms->put_menu_order($menu_id, $order);
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
