<?php 
/*
 *      Common.php
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
 * Class used by both backend 
 * and frontend controllers 
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Common {

	// CodeIgniter Instance
	private $CI;
	
	// Default content language
	private $DEFAULT_LANG;

	// Available content languages
	private $LANG_AVAIL = array();

	// Frontend choosen language
	private $LANG;

	// Frontend URI language prefix
	private $URI_PREFIX;

	// Request GET parameters
	private $PARAMS = array();
	
	// Caller entity
	private $CALLER_ENTITY;
	
	function __construct()
	{
		$this->CI =& get_instance();
		
		// Load i18n settings
		list($this->DEFAULT_LANG, $this->LANG_AVAIL) = $this->load_i18n_settings();
		// Set LANG = DEFAULT_LANG on load
		$this->LANG = $this->DEFAULT_LANG;

		// Load get parameters 
		$this->PARAMS = $this->CI->input->get(NULL, TRUE);
		$this->PARAMS = ( is_array($this->PARAMS) ) ? $this->PARAMS : array();
		
		/*
		 * BUG: By default
		 * Code Igniter don't load
		 * in libraries MY_* helpers 
		 * requested from controller,
		 * so we need to load it again
		 */
		$this->CI->load->helper('html');
	}
	
	/**
	 * Set frontend choosen language
	 *
	 * @access public
	 * @return void
	 */
	public function set_lang($lang)
	{
		$this->LANG = $lang;
	}

	/**
	 * Set frontend URI language prefix
	 * 
	 * @access public
	 * @return void
	 */
	public function set_uri_prefix($prefix)
	{
		$this->URI_PREFIX = $prefix;
	}

	/**
	 * Set Caller entity, content or element
	 *
	 * @access public
	 * @return void
	 */
	public function set_caller_entity($entity)
	{
		$this->CALLER_ENTITY = $entity;
	}

	/**
	 * Get Caller entity, content or element
	 *
	 * @access public
	 * @return string
	 */
	public function get_caller_entity()
	{
		return $this->CALLER_ENTITY;
	}

	/**
	 * Load i18n settings from database
	 *
	 * @access public
	 * @return array
	 */
	function load_i18n_settings()
	{
		// Return values if already set
		if ( (bool) $this->DEFAULT_LANG && count($this->LANG_AVAIL) > 0 )
		{
			return array($this->DEFAULT_LANG, $this->LANG_AVAIL);
		}

		// Default content language (ISO-639-1 code)
		$lang = '';

		// List of available content languages (ISO-639-1 codes)
		$lang_avail = array();

		$i18n_settings = json_decode($this->CI->storage->get_config('i18n'), true);
		foreach($i18n_settings as $i18n_setting)
		{
			if ( (bool) $i18n_setting['default'] )
			{
				$lang = $i18n_setting['code'];
				
				// Default language is the first in array
				$lang_avail = array_merge(array($i18n_setting['code'] => $i18n_setting['name']), $lang_avail);
			}
			else
			{
				$lang_avail[$i18n_setting['code']] = $i18n_setting['name'];
			}
		}
		return array($lang, $lang_avail);
	}

	/**
	 * Exit if is not a backend authorized session
	 * 
	 * @access public
	 * @return void
	 */
	function backend_auth_check()
	{
		$account_id = $this->CI->session->userdata('account_id');
		$group_id = $this->CI->session->userdata('group_id');
		if ( (int) $group_id != 1 )
		{
			$data = array(
				'is_logged' => FALSE,
				'title' => $this->CI->config->item('site_name'),
				'js' => array(
					JQUERY,
					BACKEND_ACCOUNT,
					JQUERY_TIMERS,
					BACKEND_CLIENT_WARNING
				),
				'css' => array(
					BACKEND_RESET_CSS,
					BACKEND_CSS,
					BACKEND_TREE_CSS,
					BACKEND_WINDOW_CSS
				),
				'action' => '/' . uri_string(),
				'elapsed_time' => $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			);

			// Localized texts
			$data['elementar_authentication_title'] = $this->CI->lang->line('elementar_authentication_title');
			$data['elementar_authentication_account'] = $this->CI->lang->line('elementar_authentication_account');
			$data['elementar_authentication_password'] = $this->CI->lang->line('elementar_authentication_password');
			$data['elementar_authentication_login'] = $this->CI->lang->line('elementar_authentication_login');

			$data['elementar_exit'] = $this->CI->lang->line('elementar_exit');
			$data['elementar_finished_in'] = $this->CI->lang->line('elementar_finished_in');
			$data['elementar_finished_elapsed'] = $this->CI->lang->line('elementar_finished_elapsed');
			$data['elementar_copyright'] = $this->CI->lang->line('elementar_copyright');

			$login = $this->CI->load->view('backend/backend_login', $data, TRUE);
			exit($login);
		}
	}
	
	/**
	 * Render backend html columns with label and input(s)
	 * 
	 * @access public
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	function render_form_field($type, $name, $sname, $description = NULL, $value = NULL, $i18n)
	{
		$field = div_open(array('class' => 'form_content_field'));
		$field .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$field .= form_label($name, NULL, $attributes);
		$field .= br(1);
		$field .= div_close('<!-- form_window_column_label -->');
		$field .= div_open(array('class' => 'form_window_column_input'));
		
		// Check multilanguage
		if ( (bool) $i18n )
		{
			// Value array index is language code
			$value = json_decode($value, TRUE);

			// One tab link for each language
			$input_lang_tab_links = array();
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				$current = ( $this->LANG == $lang_code ) ? ' current' : '';
				$input_lang_tab_links[] = anchor($lang_code, $lang_name, array('class' => 'input_lang_tab_link' . $current));
			}
			$field .= div_open(array('class' => 'input_lang_menu'));
			$field .= ul($input_lang_tab_links);
			//field .= hr(array('class' => 'clear'));
			$field .= div_close('<!-- input_lang_menu -->');
			
			// The input fields for each language
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				// If language index does not exist, set empty
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
			// No multilanguage, no language tabs
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
	 * Render correct HTML for each field type 
	 * 
	 * @access private
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	private function _render_form_custom_field($type, $name, $sname, $description, $value)
	{
		// Adequate input to field type
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

			case "password" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = div_open(array('class' => 'text_input_container'));
			$field .= form_password($attributes);
			$field .= div_close();
			$field .= hr(array('class' => 'clear'));
			break;

			case "textarea" :
			$attributes = array(
				'class' => 'noform ' . $type,
				'name' => $sname,
				'id' => $sname,
				'rows' => 16,
				'cols' => 32,
				'value' => $value
			);
			$field = div_open(array('class' => 'textarea_limiter')) . form_textarea($attributes) . div_close("<!-- .textarea_limiter -->");
			break;

			case "p" :
			case "hypertext" :
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
			$menu = ( $value != '' ) ? json_decode($value, TRUE) : array();
			$html = div_open(array('class' => 'menu_parent'));
			$html .= $this->_render_menu_field($menu);
			$html .= div_close();
			
			// The actual field
			$attributes = array(
				'class' => 'noform menu_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$html .= form_input($attributes);
			$data = array(
				'html' => $html
			);

			// Localized texts
			$data['elementar_menu_name'] = $this->CI->lang->line('elementar_menu_name');
			$data['elementar_menu_target'] = $this->CI->lang->line('elementar_menu_target');
			$data['elementar_menu_add'] = $this->CI->lang->line('elementar_menu_add');
			$data['elementar_menu_move_up'] = $this->CI->lang->line('elementar_menu_move_up');
			$data['elementar_menu_move_down'] = $this->CI->lang->line('elementar_menu_move_down');
			$data['elementar_menu_delete'] = $this->CI->lang->line('elementar_menu_delete');
			$data['elementar_menu_new_above'] = $this->CI->lang->line('elementar_menu_new_above');
			$data['elementar_menu_new_below'] = $this->CI->lang->line('elementar_menu_new_below');			
			$data['elementar_menu_new_submenu'] = $this->CI->lang->line('elementar_menu_new_submenu');	
			$data['targets'] = $this->_render_target_listing();

			$field = $this->CI->load->view("backend/backend_content_menu_field", $data, TRUE);
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
			
			// File field View variables
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

			// Localized texts
			$data['elementar_file_description'] = $this->CI->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->CI->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->CI->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->CI->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->CI->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->CI->lang->line('elementar_file_erase');

			$field .= $this->CI->load->view("backend/backend_content_file_field", $data, TRUE);
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
			
			// Render gallery field
			$data = array(
				'gallery' => ($value != '') ? json_decode($value, TRUE) : array()
			);

			// Localized texts
			$data['elementar_file_description'] = $this->CI->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->CI->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->CI->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->CI->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->CI->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->CI->lang->line('elementar_file_erase');
			$data['elementar_file_add'] = $this->CI->lang->line('elementar_file_add');
			$data['elementar_file_move_up'] = $this->CI->lang->line('elementar_file_move_up');
			$data['elementar_file_move_down'] = $this->CI->lang->line('elementar_file_move_down');
			$data['elementar_file_delete'] = $this->CI->lang->line('elementar_file_delete');
			$data['elementar_file_new_above'] = $this->CI->lang->line('elementar_file_new_above');
			$data['elementar_file_new_below'] = $this->CI->lang->line('elementar_file_new_below');

			$field .= $this->CI->load->view("backend/backend_content_file_gallery_field", $data, TRUE);
			$field .= div_close();
			break;

			case "youtube_gallery" :
			$field = div_open(array('class' => 'youtube_gallery_field'));
			
			// Render youtube_gallery field
			$data = array(
				'videos' => json_decode($value, TRUE) // decode as associative array
			);

			// Localized texts
			$data['elementar_youtube_description'] = $this->CI->lang->line('elementar_youtube_description');
			$data['elementar_youtube_url'] = $this->CI->lang->line('elementar_youtube_url');
			$data['elementar_youtube_add'] = $this->CI->lang->line('elementar_youtube_add');
			$data['elementar_youtube_move_up'] = $this->CI->lang->line('elementar_youtube_move_up');
			$data['elementar_youtube_move_down'] = $this->CI->lang->line('elementar_youtube_move_down');
			$data['elementar_youtube_delete'] = $this->CI->lang->line('elementar_youtube_delete');
			$data['elementar_youtube_new_above'] = $this->CI->lang->line('elementar_youtube_new_above');
			$data['elementar_youtube_new_below'] = $this->CI->lang->line('elementar_youtube_new_below');

			$field .= $this->CI->load->view('backend/backend_content_youtube_gallery_field', $data, true);
			
			// The actual field
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
				'class' => 'noform target_field',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = form_input($attributes);
			$field .= $this->_render_target_listing();
			break;

			case "index" :
			// index content root and filters form
			$filter = ( $value != '' ) ? json_decode($value, TRUE) : array();
			if ( (bool) count($filter) )
			{
				$content_id = $filter['content_id'];
				$content_names = json_decode($this->CI->storage->get_content_name($content_id), TRUE);
				$content_name = (array_key_exists($this->LANG, $content_names)) ? $content_names[$this->LANG] : '';
				$order_by = $filter['order_by'];
				$direction = $filter['direction'];
				$limit = (int) $filter['limit'];
				$depth = (int) $filter['depth'];
				$form = $this->_render_index_field_form($sname, $content_id, $order_by, $direction, $limit, $depth);
			}
			else
			{
				$content_name = 'Escolher raiz...';
				$form = $this->_render_index_field_form($sname);
			}
			$field = div_open(array('class' => 'index_field'));
			$field .= div_open(array('class' => 'dropdown_items_listing_inline'));
			$field .= anchor($sname, $content_name);
			$field .= $this->_render_contents_listing("index_root_content");
			$field .= div_close();
			$field .= div_open(array('class' => 'filter_forms', 'id' => $sname . '_filter_forms'));
			$field .= $form;
			$field .= div_close();

			// The actual field
			$attributes = array(
				'class' => 'noform index_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();

			break;

			case "selection" :
			// content selection
			$filter = ( $value != '' ) ? json_decode($value, TRUE) : array();
			if ( (bool) count($filter) )
			{
				$content_id = $filter['content_id'];
				$content_names = json_decode($this->CI->storage->get_content_name($content_id), TRUE);
				$content_name = (array_key_exists($this->LANG, $content_names)) ? $content_names[$this->LANG] : '';
				$order_by = $filter['order_by'];
				$direction = $filter['direction'];
				$limit = (int) $filter['limit'];
				$content_types = $filter['content_types'];
				$form = $this->_render_selection_field_form($sname, $content_id, $order_by, $direction, $limit, $content_types);
			}
			else
			{
				$content_name = 'Escolher raiz...';
				$form = $this->_render_selection_field_form($sname);
			}
			$field = div_open(array('class' => 'selection_field'));
			$field .= div_open(array('class' => 'dropdown_items_listing_inline'));
			$field .= anchor($sname, $content_name);
			$field .= $this->_render_contents_listing("selection_root_content");
			$field .= div_close();
			$field .= div_open(array('class' => 'filter_forms', 'id' => $sname . '_filter_forms'));
			$field .= $form;
			$field .= div_close();

			// The actual field
			$attributes = array(
				'class' => 'noform index_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();

			break;
		}
		return $field;
	}
	
	/**
	 * Render HTML form for menu creation
	 * 
	 * @access private
	 * @param array
	 * @return string
	 */
	private function _render_menu_field($menus)
	{
		$html = '';
		$targets = $this->_render_target_listing();
		foreach ( $menus as $menu )
		{
			// Render menu field
			$data = array(
				'name' => $menu['name'], 
				'target' => $menu['target'], 
				'targets' => $targets
			);
			
			// Localized texts
			$data['elementar_menu_name'] = $this->CI->lang->line('elementar_menu_name');
			$data['elementar_menu_target'] = $this->CI->lang->line('elementar_menu_target');
			$data['elementar_menu_add'] = $this->CI->lang->line('elementar_menu_add');
			$data['elementar_menu_move_up'] = $this->CI->lang->line('elementar_menu_move_up');
			$data['elementar_menu_move_down'] = $this->CI->lang->line('elementar_menu_move_down');
			$data['elementar_menu_delete'] = $this->CI->lang->line('elementar_menu_delete');
			$data['elementar_menu_new_above'] = $this->CI->lang->line('elementar_menu_new_above');
			$data['elementar_menu_new_below'] = $this->CI->lang->line('elementar_menu_new_below');			
			$data['elementar_menu_new_submenu'] = $this->CI->lang->line('elementar_menu_new_submenu');	
	
			$html .= div_open(array('class' => 'menu_item'));
			$html .= $this->CI->load->view('backend/backend_content_menu_field_item', $data, true);

			if ( is_array($menu['menu']) )
			{
				$html .= div_open(array('class' => 'menu_parent'));
				$html .= $this->_render_menu_field($menu['menu']);
				$html .= div_close();
			}

			$html .= div_close();
		}
		return $html;
	}

	/**
	 * List field HTML elements
	 * 
	 * @access public
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	function _render_selection_field_form($field_sname, $content_id = '', $order_by_checked = 'created', $direction = 'desc', $limit = 10, $content_types = array())
	{
		if ( ! (bool) $content_id )
		{
			// No content_id, dont render form
			return NULL;
		}

		// Select content types
		$avail_content_types = $this->CI->storage->get_content_types();
		$content_type_input = "";
		foreach($avail_content_types as $content_type_id => $content_type){
			$label = form_label($content_type, "selection_content_type_" . $content_type_id, array('class' => 'field_label'));
			$attributes = array(
				'name' => 'selection_content_type[]',
				'id' => "selection_content_type_" . $content_type_id ,
				'class' => 'noform',
				'value' => $content_type_id ,
				'checked' => (in_array($content_type_id, $content_types)) ? "checked" : ''
			);
			$input = form_checkbox($attributes);
			$content_type_input .= $input . $label . "<br />";
		}

		$content_type_id = $this->CI->storage->get_content_type_id($content_id);
		
		$default_fields = array(
			array('name' => $this->CI->lang->line('elementar_index_created'), 'sname' => 'created'),
			array('name' => $this->CI->lang->line('elementar_index_modified'), 'sname' => 'modified'),
			array('name' => $this->CI->lang->line('elementar_index_name'), 'sname' => 'name')
		);
		
		$data = array(
			'index_sname' => $field_sname,
			'content_id' => $content_id,
			'order_by' => $default_fields,
			'order_by_checked' => $order_by_checked,
			'index_filter' => array(
				'direction' => $direction,
				'limit' => $limit
			),
			'content_type_input' => $content_type_input
		);
		
		return $this->CI->load->view('backend/backend_content_selection_field', $data, true);
	}

	/**
	 * Index field HTML elements
	 * 
	 * @access public
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	function _render_index_field_form($field_sname, $content_id = '', $order_by_checked = 'created', $direction = 'desc', $limit = 10, $depth = 1)
	{
		if ( ! (bool) $content_id )
		{
			// No content_id, dont render form
			return NULL;
		}

		$content_type_id = $this->CI->storage->get_content_type_id($content_id);
		
		$default_fields = array(
			array('name' => $this->CI->lang->line('elementar_index_created'), 'sname' => 'created'),
			array('name' => $this->CI->lang->line('elementar_index_modified'), 'sname' => 'modified'),
			array('name' => $this->CI->lang->line('elementar_index_name'), 'sname' => 'name')
		);
		
		$fields = $this->CI->storage->get_content_type_fields($content_type_id);
		
		$data = array(
			'index_sname' => $field_sname,
			'content_id' => $content_id,
			'order_by' => array_merge($default_fields, $fields),
			'order_by_checked' => $order_by_checked,
			'index_filter' => array(
				'direction' => $direction,
				'limit' => $limit,
				'depth' => $depth
			)
		);
		
		return $this->CI->load->view('backend/backend_content_index_field', $data, true);
	}
	
	/**
	 * List of links to all contents
	 *
	 * @access private
	 * @return string
	 */
	private function _render_target_listing()
	{
		// dropdown target listing
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->CI->lang->line('elementar_inside_targets') . '</strong>');
		// Contents
		$contents = $this->CI->storage->get_contents();
		// Order
		$order = new Filter("created", "desc");
		$order->set_is_date(TRUE);
		usort($contents, array($order, 'sort'));
		foreach ( $contents as $content )
		{
			$listing[] = $this->breadcrumb_content($content['id']);
		}
		// Addons
		foreach ( $this->load_addons() as $addon ) 
		{
			$listing[] = $this->breadcrumb_path($addon['uri']);
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

					$listing[] = $this->breadcrumb_path($addon['uri'] . '/' . $method);
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

	/**
	 * List of contents
	 * 
	 * @access private
	 * @return string
	 */
	private function _render_contents_listing($class)
	{
		// dropdown target listing
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->CI->lang->line('elementar_contents') . '</strong>');
		$content = $this->CI->storage->get_content(1);
		$content_name = json_decode($content['name'], TRUE);
		$listing[] = anchor(1, (array_key_exists($this->LANG, $content_name)) ? $content_name[$this->LANG] : '', array('class' => $class));
		// Contents
		foreach ( $this->CI->storage->get_contents_by_parent(1) as $content )
		{
			$content_name = json_decode($content['name'], TRUE);
			$listing[] = anchor($content['id'], (array_key_exists($this->LANG, $content_name)) ? $content_name[$this->LANG] : '', array('class' => $class));
		}
		$contents = div_open(array('class' => 'dropdown_items_listing_position'));
		$contents .= div_open(array('class' => 'dropdown_items_listing'));
		$attributes = array(
			'class' => 'dropdown_items_listing_targets'
		);
		$contents .= ul($listing, $attributes);
		$contents .= div_close();
		$contents .= div_close();
		return $contents;
	}

	/*
	 * Is an URI the current location?
	 * 
	 * @access private
	 * @param string
	 * @return boolean
	 */
	private function _uri_is_current($uri)
	{
		if ( $this->CI->uri->total_segments() == 0 )
		{
			// No URI segments, it's the home page
			if ( '/' == $uri ) 
			{
				return TRUE;
			}
		}
		
		// trim out trailing slash
		$current_uri = '/' . $this->CI->uri->uri_string();
		if ( substr($current_uri, -1) == '/' )
		{
			$current_uri = substr($current_uri, 0, -1);
		}

		// Check for localized home
		if ( $this->CI->uri->total_segments() == 1 )
		{
			if ( $this->URI_PREFIX == $current_uri && $current_uri . '/' == $uri )
			{
				return TRUE;
			}
		} 
		
		// Non root URI
		if ( $uri != '/' )
		{
			if ( $current_uri == $uri )
			{
				return TRUE;
			}
		}

		// Defaults to false
		return FALSE;
	}

	/**
	 * Render HTML menu
	 * 
	 * @access private
	 * @param array
	 * @return string
	 */
	private function _make_menu($menu)
	{
		if ( ! is_array($menu) )
		{
			return NULL;
		}
		// Build menu items links
		$menu_links = array();
		while ( $menu_item = current($menu) )
		{
			// Mark current menu
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $menu_item['target']) ) ? 'menu_item current' : 'menu_item';
			// Set first and last class selectors in menu for styling
			$class .= ( key($menu) == 0 ) ? ' first' : '';
			$class .= ( key($menu) == ( count($menu) - 1 ) ) ? ' last' : '';
			$attributes = array(
				'title' => htmlspecialchars( $menu_item['name'] ),
				'class' => $class
			);
			$link = anchor($this->URI_PREFIX . $menu_item['target'], htmlspecialchars($menu_item['name']), $attributes);
			//$link = '<a href="'.$this->URI_PREFIX . $menu_item['target'].'" title="'.htmlspecialchars( $menu_item['name'] ).'" class="'.$class.'">'.htmlspecialchars($menu_item['name']).'</a>';
			$submenu = $menu_item['menu'];
			if ( ! (bool) $submenu )
			{
				// No submenu
				$menu_links[] = array(
					'link' => $link,
					'submenu' => array()
				);
			}
			else
			{
				// Recursive through submenu
				$menu_links[] = array(
					'link' => $link,
					'submenu' => $this->_make_menu($submenu)
				);
			}
			next($menu);
		}
		return $menu_links;
	}

	/**
	 * Clickable path (breadcrumb) for content
	 * 
	 * @access public
	 * @param integer
	 * @param string
	 * @param string
	 * @return string
	 */
	function breadcrumb_content($content_id, $sep = "&raquo;", $previous = "")
	{
		$breadcrumb = "";
		
		// With content_id = 1, just return home link
		if ( (int)$content_id === 1 )
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" ;
			return $breadcrumb;
		}

		$content = (array) $this->CI->storage->get_content($content_id);
		
		if ( count( $content ) > 0 )
		{
			// Localized name
			$names = json_decode($content['name'], TRUE);
			$name = (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '';
			$content_uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($content_id);
			if ( (int) $content['parent_id'] > 1 ) 
			{
				$breadcrumb = $this->breadcrumb_content($content['parent_id'], $sep, " $sep <a href=\"" . $content_uri . "\">" . $name . "</a>" . $previous);
			}
			else
			{
				$breadcrumb = '<a href="' . $this->URI_PREFIX . '/" >' . $this->CI->config->item('site_name') . "</a> $sep <a href=\"" . $content_uri . "\">" . $name . "</a>" . $previous;
			}
		}
		return $breadcrumb;
	}

	/**
	 * Clickable path (breadcrumb) for element
	 * 
	 * @access public
	 * @param integer
	 * @param string
	 * @return string
	 */
	function breadcrumb_element($element_id, $sep = "&raquo;")
	{
		$breadcrumb = "";
		
		$element = $this->CI->storage->get_element($element_id);
		
		if ( count($element) > 0 )
		{
			// Localized name
			$names = json_decode($element['name'], TRUE);
			$name = (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '';
			$element_uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($element['parent_id']) . "#" . $element['sname'];
			if ( (bool) $element['parent_id'] )
			{ 
				$breadcrumb = $this->breadcrumb_content($element['parent_id'], $sep, " $sep <a href=\"" . $element_uri . "\" >" . $name . "</a>");
			}
			else
			{
				$breadcrumb = '<a href="' . $this->URI_PREFIX . '/" >' . $this->CI->config->item('site_name') . "</a> $sep <a href=\"/" . $element_uri . "\" >" . $name . "</a>";
			}
		}
		return $breadcrumb;
	}
	
	/**
	 * Generate breadcrumb from some path
	 * 
	 * @access public
	 * @param string
	 * @param string
	 * @return string
	 */
	function breadcrumb_path($path, $sep = "&raquo;")
	{
		$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" ;
		$uri = "";
		
		// With no path, just return home link
		if ( (bool) $path == FALSE )
		{
			return $breadcrumb;
		}

		$segments = explode("/", substr($path, 1));

		if ( count($segments) > 0 )
		{
			$breadcrumb .= " $sep ";
		}

		while ( $segment = current($segments) )
		{
			$uri .= "/" . $segment;
			$breadcrumb .= "<a href=\"" . $uri . "\" >" . $segment . "</a>" ;
			if ( next($segments) )
			{
				$breadcrumb .= " $sep ";
			}
		}
		return $breadcrumb;
	}

	/**
	 * Load addon classes
	 * 
	 * @access public
	 * @param array
	 * @return array
	 */
	function load_addons($ignore = NULL)
	{
		$ignore = ( NULL == $ignore ) ? array() : $ignore;
		$this->CI->load->helper('file');
		$addons = array();
		$addons_path = APPPATH.'addons/';
		foreach(get_dir_file_info($addons_path, TRUE) as $addon) 
		{
			// Skip anything other than PHP files
			if ( substr($addon['name'], -4) == '.php' )
			{
				list($class, $ext) = explode('.', ucfirst($addon['name']));

				if ( in_array($class, $ignore) ) continue;
				
				if ( ! class_exists($class) )
				{
					// Load addon class
					include($addon['relative_path'] . $addon['name']);
				}

				// Check for enabled property
				eval("\$enabled = $class::\$ENABLED;");
				if ( ! (bool) $enabled ) continue;

				$addons[] = array(
					'uri' => '/' . strtolower($class),
					'name'=> $class,
					'path' => $addon['relative_path'] . $addon['name'],
					'date' => $addon['date'],
					'methods' => get_class_methods($class)
				);
			}
		}
		return $addons;
	}

	/**
	 * sitemap.xml generation
	 * 
	 * @access public
	 * @return void
	 */
	function sitemap()
	{
		$urls = array();
		// Database contents
		foreach ( $this->CI->storage->get_contents() as $content )
		{
			$priority = $this->CI->storage->get_meta_field($content['id'], 'priority');
			$uri = $this->CI->storage->get_content_uri($content['id']);
			$priority = ( (bool) $priority ) ? $priority : '0.5';
			$urls[] = array(
				'loc' => site_url($uri),
				'lastmod' => date("Y-m-d", strtotime($content['modified'])),
				'changefreq' => 'daily',
				'priority' => $priority
			);
			// Add entry for each language
			reset($this->LANG_AVAIL);
			// Skip first (default) language
			next($this->LANG_AVAIL);
			while (current($this->LANG_AVAIL))
			{
				$urls[] = array(
					'loc' => site_url('/' . key($this->LANG_AVAIL) . $uri),
					'lastmod' => date("Y-m-d", strtotime($content['modified'])),
					'changefreq' => 'daily',
					'priority' => $priority
				);
				next($this->LANG_AVAIL);
			}
		}
		// Addons
		foreach ( $this->load_addons() as $addon ) 
		{
			$urls[] = array(
				'loc' => site_url($addon['uri']),
				'lastmod' => date("Y-m-d", $addon['date']),
				'changefreq' => 'daily',
				'priority' => '0.5'
			);
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
	
					$urls[] = array(
						'loc' => site_url($addon['uri'] . '/' . $method),
						'lastmod' => date("Y-m-d", $addon['date']),
						'changefreq' => 'daily',
						'priority' => '0.5'
					);
				}				
			}
		}
		// HTTP headers
		$this->CI->output->set_header("Content-type: application/xml");
		$mtime = gmdate('D, d M Y H:i:s').' GMT';
		$this->CI->output->set_header('ETag: ' . md5($mtime));
		$this->CI->output->set_header('Last-Modified: ' . $mtime);
		$this->CI->output->append_output('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$this->CI->load->view('sitemap', array('urls' => $urls));
	}

	/**
	 * RSS feed generation
	 * 
	 * @access public
	 * @return void
	 */
	function rss($content_id)
	{
		// HTTP headers
		$this->CI->output->set_header("Content-type: application/xml");
		$mtime = gmdate('D, d M Y H:i:s').' GMT';
		$this->CI->output->set_header('ETag: ' . md5($mtime));
		$this->CI->output->set_header('Last-Modified: ' . $mtime);
		$this->CI->output->append_output('<?xml version="1.0" encoding="UTF-8"?>' . "\n");

		$data = array();
		$data['title'] = htmlspecialchars( $this->CI->config->item('site_name') );
		if ( $content_id > 1 ) {
			$titles = json_decode($this->CI->storage->get_content_name($content_id), TRUE);
			$title = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
			$data['title'] = $title . " | " . $data['title'];
		}

		$uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($content_id);
		$url = $this->CI->storage->get_meta_field($content_id, 'url');
		if ( $url == '' )
		{
			// Using default content's URL
			$url = site_url($uri);
		}
		$data['link'] = $url;
		
		// Localized description from meta field
		$descriptions = (array) json_decode($this->CI->storage->get_meta_field($content_id, 'description'), TRUE);
		$data['description'] = (array_key_exists($this->LANG, $descriptions)) ? $descriptions[$this->LANG] : '';
		
		$data['lastBuildDate'] = $mtime;
		$data['language'] = $this->LANG;

		$favicon = json_decode($this->CI->storage->get_config('favicon'), TRUE);
		if ( is_array($favicon) )
		{
			$data['image_url'] = ( $favicon['uri'] != '' ) ? $favicon['uri'] : '/favicon.ico';
		}
		else
		{
			$data['image_url'] = '/favicon.ico';
		}

		$items = array();
		// Children contents as items
		$children = $this->CI->storage->get_content_descendants($content_id);
		// Order by modified desc
		$filter = new Filter('modified', 'desc');
		$filter->set_is_date(TRUE);
		usort($children, array($filter, 'sort'));
		// Limit to 30 items
		$children = array_slice($children, 0, 30);

		foreach ( $children as $content )
		{
			// Item title
			$titles = json_decode($this->CI->storage->get_content_name($content['id']), TRUE);
			$title = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';

			// Item link
			$uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($content['id']);
			$url = $this->CI->storage->get_meta_field($content['id'], 'url');
			if ( $url == '' )
			{
				// Using default content's URL
				$url = site_url($uri);
			}
			
			// Localized description from meta field
			$descriptions = (array) json_decode($this->CI->storage->get_meta_field($content['id'], 'description'), TRUE);
			$description = (array_key_exists($this->LANG, $descriptions)) ? $descriptions[$this->LANG] : '';
			
			// Assemble item
			$items[] = array(
				'title' => $title,
				'link' => $url,
				'description' => $description,
				'modified' => gmdate('D, d M Y H:i:s', strtotime($content['modified'])).' GMT'
			);
		}

		$data['items'] = $this->CI->load->view('rss_item', array('items' => $items), TRUE);

		$this->CI->load->view('rss', $data);
	}

	/**
	 * Convert non-ascii and other characters to ascii and underscores
	 * 
	 * @access public
	 * @param string $string Input string
	 * @return string Converted string
	 */
	function normalize_string($string)
	{
		$this->CI->load->helper('url');

		$acentos = array(
			'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
			'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
			'C' => '/&Ccedil;/',
			'c' => '/&ccedil;/',
			'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
			'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
			'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
			'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
			'N' => '/&Ntilde;/',
			'n' => '/&ntilde;/',
			'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
			'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
			'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
			'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
			'Y' => '/&Yacute;/',
			'y' => '/&yacute;|&yuml;/',
			'a.' => '/&ordf;/',
			'o.' => '/&ordm;/'
		);
		
		$string = htmlentities($string, ENT_QUOTES, "UTF-8");
		$string = preg_replace($acentos, array_keys($acentos), $string);               
		$string = trim($string);
		$string = strtolower(url_title($string));
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		return $string;
	}
	
	/**
	 * Field localized value
	 * 
	 * @access private
	 * @param string
	 * @param boolean
	 * @return string
	 */
	private function _field_localized_value($field_value, $i18n = TRUE)
	{
		if ( (bool) $i18n )
		{
			// Choose language
			$field_values = json_decode($field_value, TRUE);
			if ( $field_values ){
				$field_value = (array_key_exists($this->LANG, $field_values)) ? $field_values[$this->LANG] : '';
			}
		}
		return $field_value;
	}
	
	/**
	 * Render field contents
	 * 
	 * @access public
	 * @param array
	 * @param string
	 * @return string/array
	 */
	function render_field($field_attr, $field_value)
	{
		// Check for multilanguage field
		$field_value = $this->_field_localized_value($field_value, $field_attr['i18n']);

		switch ( $field_attr['type'] )
		{
			case 'file' :
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) count(json_decode($field_value, TRUE)) && $this->LANG != $this->DEFAULT_LANG )
			{
				$field_value = $field_values[$this->DEFAULT_LANG];
			}
			$attributes = json_decode($field_value, TRUE);
			if ( count( $attributes ) > 0 )
			{
				/*
				 * Put attributes as a sub array
				 * so the nested fields can be parsed on 
				 * a template pair loop
				 */
				$nested = array();
				$nested[] = array(
					'uri' => (string) $attributes['uri'],
					'width' => (string) $attributes['width'],
					'height' => (string) $attributes['height'],
					'alt' => (string) $attributes['title'] 
				);
				return $nested;
			}
			break;

			case 'file_gallery' :
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) count(json_decode($field_value, TRUE)) && $this->LANG != $this->DEFAULT_LANG )
			{
				$field_value = $field_values[$this->DEFAULT_LANG];
			}
			$images = json_decode($field_value, TRUE);
			$gallery = array();
			foreach ( $images as $index => $image_item )
			{
				$image = json_decode($image_item, TRUE);
				if ( count( $image ) > 0 )
				{
					$gallery[] = array(
						'index' => (string) $index,
						'uri' => (string) $image['uri'],
						'width' => (string) $image['width'],
						'height' => (string) $image['height'],
						'alt' => (string) $image['title'] 
					);
				}
			}
			return $gallery;
			break;

			case 'youtube_gallery' :
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) count(json_decode($field_value, TRUE)) && $this->LANG != $this->DEFAULT_LANG )
			{
				$field_value = $field_values[$this->DEFAULT_LANG];
			}
			$videos = json_decode($field_value, TRUE);
			$gallery = array();
			foreach ( $videos as $index => $video )
			{
				if ( count( $video ) > 0 )
				{
					// YouTube video id
					$video_url_segments = parse_url($video['url']);
					parse_str($video_url_segments['query'], $variables);
					$video_id = $variables['v'];
					$gallery[] = array(
						'index' => (string) $index,
						'description' => (string) $video['description'],
						'url' => (string) $video['url'],
						'video_id' => $video_id,
						'screenshot_large' => 'http://img.youtube.com/vi/' . $video_id . '/0.jpg',
						'screenshot_small' => 'http://img.youtube.com/vi/' . $video_id . '/2.jpg'
					);
				}
			}
			return $gallery;
			break;

			case 'menu' :
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) count(json_decode($field_value, TRUE)) && $this->LANG != $this->DEFAULT_LANG )
			{
				$field_value = $field_values[$this->DEFAULT_LANG];
			}
			// Generate links with semantic classes
			return $this->_make_menu(json_decode($field_value, TRUE));
			break;
			
			case 'index' :
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) count(json_decode($field_value, TRUE)) && $this->LANG != $this->DEFAULT_LANG )
			{
				$field_value = $field_values[$this->DEFAULT_LANG];
			}
			// Index filter values
			$filter = json_decode($field_value, TRUE);
			$content_id = $filter['content_id'];
			$order_by = $filter['order_by'];
			$direction = $filter['direction'];
			$limit = (int) $filter['limit'];
			$depth = (int) $filter['depth'];
			// Index listing
			$index = array();
			// localized parent title
			$titles = json_decode($this->CI->storage->get_content_name($content_id), TRUE);
			$content_name = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
			$content_uri = $this->CI->storage->get_content_uri($content_id);
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $content_uri) ) ? 'index_item current' : 'index_item';
	
			$attributes = array(
				'title' => htmlspecialchars( $content_name ),
				'class' => $class
			);
			$link = anchor($this->URI_PREFIX . $content_uri, htmlspecialchars($content_name), $attributes);
			//$link = '<a href="'.$this->URI_PREFIX . $content_uri.'" title="'.htmlspecialchars( $content_name ).'" class="'.$class.'">'.htmlspecialchars($content_name).'</a>';
			if ( $this->CI->storage->get_content_has_children($content_id, FALSE) && $depth > 0 )
			{
				$index[$link] = $this->_index_field($content_id, $order_by, $direction, $limit, $depth);
			}
			else
			{
				$index[] = $link;
			}
			return ul($index);
			break;

			case 'selection' :
			// List filter values
			$filter = json_decode($field_value, TRUE);
			$content_id = $filter['content_id'];
			$order_by = $filter['order_by'];
			$direction = $filter['direction'];
			$limit = (int) $filter['limit'];
			$content_types = $filter['content_types'];

			$items = $this->CI->storage->get_content_descendants($content_id);
			$selection = array();
			foreach ( $items as $index => $item )
			{
				if ( ! in_array($this->CI->storage->get_content_template_id($item['id']), $content_types) ) continue;
				$titles = json_decode($item['name'], TRUE);
				$content_name = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
				$content_uri = $this->CI->storage->get_content_uri($item['id']);
				$fields = array(
					'name' => $content_name,
					'uri' => $content_uri,
					'sname' => $item['sname'],
					'created' => $item['created'],
					'modified' => $item['modified']
				);
				// Include item custom fields
				$custom_fields = $this->CI->storage->get_content_fields($item['id']);
				foreach ( $custom_fields as $field ){
					$rendered_value = $this->render_field($field, $field['value']);
					$fields = array_merge($fields, array($field['sname'] => $rendered_value));
				}
				// Include parent name and uri
				$parent_id = $this->CI->storage->get_content_parent_id($item['id']);
				$parent_uri = $this->CI->storage->get_content_uri($parent_id);
				$fields = array_merge($fields, array("parent_uri" => $parent_uri));
				$parent_names = $this->CI->storage->get_content_name($parent_id);
				$titles = json_decode($parent_names, TRUE);
				$parent_name = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
				$fields = array_merge($fields, array("parent" => $parent_name));

				$selection[] = $fields;
			}
			// Order
			$order = new Filter($order_by, $direction);
			if ( $order_by == 'created' || $order_by == 'modified' )
			{
				$order->set_is_date(TRUE);
			}
			usort($selection, array($order, 'sort'));

			// Limit
			$selection = array_slice($selection, 0, $limit);

			return $selection;
			break;

			default:
			/*
			 * If value for specific language is not present,
			 * get the default for the site primary language
			 */
			if ( ! (bool) $field_value && $this->LANG != $this->DEFAULT_LANG )
			{
				if ( isset($field_values) ){
					$field_value = (array_key_exists($this->DEFAULT_LANG, $field_values)) ? $field_values[$this->DEFAULT_LANG] : '';
				}
				else {
					$field_value = "";
				}
			}
			return (string) $field_value;
			break;
		}
	}
	
	/**
	 * Generate HTML for index field content
	 * 
	 * @access private
	 * @param integer
	 * @param string
	 * @param string
	 * @param integer
	 * @param integer
	 * @param integer
	 * @return string
	 */
	private function _index_field($content_id, $order_by, $direction, $limit, $depth, $depth_count = 1)
	{
		$children = $this->CI->storage->get_contents_by_parent($content_id);
		$children = ( is_array($children) ) ? $children : array();
		/*
		 * Perform filtering first by the 
		 * specified ordering field
		 */
		$filter = new Filter($order_by, $direction);
		if ( $order_by == 'created' || $order_by == 'modified' )
		{
			$filter->set_is_date(TRUE);
		}
		$filter->set_lang($this->LANG);
		usort($children, array($filter, 'sort'));

		// Limit number of elements (if specified)
		if ( (bool) $limit )
		{
			$children = array_slice($children, 0, $limit);
		}

		$index = array();
		$depth_count++;
		foreach($children as $child)
		{
			$content_id = $child['id'];
			// localized title
			$titles = json_decode($child['name'], TRUE);
			$content_name = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
			$content_uri = $this->CI->storage->get_content_uri($content_id);
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $content_uri) ) ? 'index_item current' : 'index_item';
	
			$attributes = array(
				'title' => htmlspecialchars( $content_name ),
				'class' => $class,
				'data-date' => date('d/m/Y H:i:s', strtotime($child['modified']))
			);
			$link = anchor($this->URI_PREFIX . $content_uri, htmlspecialchars($content_name), $attributes);
			//$link = '<span class="date">' . date('d/m/Y H:i:s', strtotime($child['modified'])) . '</span> '. anchor($this->URI_PREFIX . $content_uri, htmlspecialchars($content_name), $attributes);
			//$link = '<span class="date">' . date('d/m/Y H:i:s', strtotime($child['modified'])) . '</span> <a href="'.$this->URI_PREFIX . $content_uri.'" title="'.htmlspecialchars( $content_name ).'" class="'.$class.'">'.htmlspecialchars($content_name).'</a>';
			if ( (bool) $child['children'] && $depth >= $depth_count )
			{
				$index[$link] = $this->_index_field($content_id, $order_by, $direction, $limit, $depth, $depth_count);
			}
			else
			{
				$index[] = $link;
			}
		}
		return $index;
	}

	/**
	 * Render all content's fields values
	 * 
	 * @access public
	 * @param integer
	 * @return array
	 */
	function render_content($content_id = 1)
	{
		$content = array();
		
		// Default fields
		$content['breadcrumb'] = $this->breadcrumb_content($content_id);
		
		// Content fields
		$fields = $this->CI->storage->get_content_fields($content_id);
		foreach ($fields as $field)
		{
			$content[$field['sname']] = $this->render_field($field, $field['value']);
		}
		
		// Identify and replace brothers and children variables 
		// by script to call and render these partials by ajax

		// Children contents listing
		$content['children'] = array($this->URI_PREFIX, $content_id);
		// Parent children contents → brothers :)
		if ( $content_id != 1 )
		{
			$content['brothers'] = array($this->URI_PREFIX, $content_id);
		}
		
		// Index field (automatic menu) pair
		$content['index'] = array($this->URI_PREFIX, $content_id);
		
		// Account data for ajax loading
		// These calls ignore language uri prefix
		$content['account'] = array('', $content_id);
		
		return $content;
	}
	
	/**
	 * Render values for a partial content's brothers, children or other
	 * 
	 * @access public
	 * @param integer
	 * @return array
	 */
	function render_content_partial($content_id, $variable)
	{
		$content = array();
		
		switch ( $variable )
		{ 
			case 'brothers' :
			// Parent children contents → brothers :)
			if ( $content_id != 1 )
			{
				$parent_id = $this->CI->storage->get_content_parent_id($content_id);
				$brothers = $this->CI->storage->get_contents_by_parent($parent_id);
				$brothers = ( (bool) $brothers ) ? $brothers : array();
				$brothers_variables = array();
				foreach ( $brothers as $brother )
				{
					if ( $content_id == $brother['id'] )
					{
						// Ignore current content in brothers listing
						continue;
					}
					// Localized name
					$names = json_decode($brother['name'], TRUE);
					$brothers_variables[] = array(
						'id' => $brother['id'],
						'sname' => $brother['sname'],
						'name' => (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '',
						'uri' => $this->URI_PREFIX . $this->CI->storage->get_content_uri($brother['id']),
						'children' => $brother['children']
					);
				}
				$content['brothers'] = $brothers_variables;
			}
			break;
			
			case 'children' :
			$children = $this->CI->storage->get_contents_by_parent($content_id);
			$children = ( (bool) $children ) ? $children : array();
			$children_variables = array();
			foreach ( $children as $child )
			{
				// Localized name
				$names = json_decode($child['name'], TRUE);
				$children_variables[] = array(
					'id' => $child['id'],
					'sname' => $child['sname'],
					'name' => (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '',
					'uri' => $this->URI_PREFIX . $this->CI->storage->get_content_uri($child['id']),
					'children' => $child['children']
				);
			}
			$content['children'] = $children_variables;
			break;
			
			case 'index' :
			// All content fields
			$index_variables = array();
			$fields = $this->CI->storage->get_content_fields($content_id);
			foreach ($fields as $field)
			{
				// Match variable
				if ( $field['type'] == 'index' )
				{
					$index_variables[] = array(
						$field['sname'] => $this->render_field($field, $field['value'])
					);
				}
			}
			$content['index'] = $index_variables;
			break;
			
			case 'account' :
			$account_id = $this->CI->session->userdata('account_id');
			if ( (bool) $account_id )
			{
				// Access model 
				$this->CI->load->model('Access', 'access');
				$content['account'] = array(
					array(
						'identified' => TRUE,
						'id' => $account_id,
						'user' => $this->CI->access->get_account_username($account_id),
						'email' => $this->CI->access->get_account_email($account_id)
					)
				);
			}
			else
			{
				$content['account'] = array(
					array(
						'identified' => FALSE,
						'id' => '',
						'user' => '',
						'email' => ''
					)
				);
			}
			break;
		}
		return $content;
	}

	/**
	 * Render all element's fields values
	 * 
	 * @access public
	 * @param integer
	 * @param string
	 * @return array
	 */
	function render_elements($content_id = 1, $filter) 
	{
		$elements = $this->CI->storage->get_elements_by_parent_spreaded($content_id);
		$data = array();
		foreach ($elements as $key => $element)
		{
			$element_id = $element['id'];
			$element_sname = $element['sname'];
			$names = json_decode($element['name'], TRUE);
			$element_name = (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '';
			$element_created = $element['created'];
			$element_modified = $element['modified'];
			$element_type_id = $element['type_id'];
			$element_type = $element['type'];
			
			// Position in array
			if ( 0 == $key )
			{
				$lineup = 'first';
			}
			elseif ( count($elements) - 1 == $key )
			{
				$lineup = 'last';
			}
			else
			{
				$lineup = 'middle';
			}
			
			// Initialize element type inner array
			if ( ! array_key_exists($element['type'], $data) )
			{
				$data[$element['type']] = array();
			}

			// Render loop entries by element type sname
			$fields = $this->CI->storage->get_element_fields($element['id']);

			// To be added in the element type array
			$entry = array(
				'id' => $element['id'],
				'name' => $element_name,
				'sname' => $element['sname'],
				'created' => $element['created'],
				'modified' => $element['modified'],
				'lineup' => $lineup
			);
			
			// Custom fields
			$data[$element['sname'] . '.id'] = $element['id'];

			// Add element direct access (without a template loop)
			// Adapt to template pseudo variables
			$data[$element['sname'] . '.sname'] = $element['sname'];
			$data[$element['sname'] . '.name'] = $element_name;
			$data[$element['sname'] . '.created'] = $element['created'];
			$data[$element['sname'] . '.modified'] = $element['modified'];
			foreach ($fields as $field)
			{
				// Format field value depending on field type
				$rendered_value = $this->render_field($field, $field['value']);

				// element type array item
				$entry[$field['sname']] = $rendered_value;
				
				// Add element direct access (without a template loop)
				// Adapt to template pseudo variables
				$data[$element['sname'] . '.' . $field['sname']] = $rendered_value;
			}
			
			// Add element entry as a pseudo variable pair (loop)
			$data[$element['type']][] = $entry;
		}

		/*
		 * Retrieve filters for template
		 * and parse on element type
		 */
		$rules = json_decode($filter, TRUE);
		if ( is_array($rules) )
		{
			foreach ( $rules as $element_type => $rule )
			{
				if ( array_key_exists($element_type, $data) )
				{
					/*
					 * One or more element matching rule, 
					 * perform filtering first by the specified
					 * ordering field
					 */
					$filter = new Filter($rule['order_by'], $rule['direction']);
					if ( $rule['order_by'] == 'created' || $rule['order_by'] == 'modified' )
					{
						$filter->set_is_date(TRUE);
					}
					usort($data[$element_type], array($filter, 'sort'));

					/*
					 * Limit number of elements (if specified)
					 */
					if ( (bool) $rule['limit'] )
					{
						$data[$element_type] = array_slice($data[$element_type], 0, $rule['limit']);
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Name associated to a language code
	 * 
	 * @access public
	 * @param string
	 * @return string
	 */
	function which_language($code)
	{
		$languages = array(
			'aa' => 'Afar',
			'ab' => 'Abkhazian',
			'af' => 'Afrikaans',
			'ak' => 'Akan',
			'sq' => 'Albanian',
			'am' => 'Amharic',
			'ar' => 'Arabic',
			'an' => 'Aragonese',
			'hy' => 'Armenian',
			'as' => 'Assamese',
			'av' => 'Avaric',
			'ae' => 'Avestan',
			'ay' => 'Aymara',
			'az' => 'Azerbaijani',
			'ba' => 'Bashkir',
			'bm' => 'Bambara',
			'eu' => 'Basque',
			'be' => 'Belarusian',
			'bn' => 'Bengali',
			'bh' => 'Bihari languages',
			'bi' => 'Bislama',
			'bs' => 'Bosnian',
			'br' => 'Breton',
			'bg' => 'Bulgarian',
			'my' => 'Burmese',
			'ca' => 'Catalan',
			'ch' => 'Chamorro',
			'ce' => 'Chechen',
			'zh' => 'Chinese',
			'cu' => 'Church Slavic',
			'cv' => 'Chuvash',
			'kw' => 'Cornish',
			'co' => 'Corsican',
			'cr' => 'Cree',
			'cs' => 'Czech',
			'da' => 'Danish',
			'dv' => 'Divehi',
			'nl' => 'Dutch',
			'dz' => 'Dzongkha',
			'en' => 'English',
			'eo' => 'Esperanto',
			'et' => 'Estonian',
			'ee' => 'Ewe',
			'fo' => 'Faroese',
			'fj' => 'Fijian',
			'fi' => 'Finnish',
			'fr' => 'French',
			'fy' => 'Western Frisian',
			'ff' => 'Fulah',
			'ka' => 'Georgian',
			'de' => 'German',
			'gd' => 'Gaelic',
			'ga' => 'Irish',
			'gl' => 'Galician',
			'gv' => 'Manx',
			'el' => 'Greek, Modern (1453-)',
			'gn' => 'Guarani',
			'gu' => 'Gujarati',
			'ht' => 'Haitian',
			'ha' => 'Hausa',
			'he' => 'Hebrew',
			'hz' => 'Herero',
			'hi' => 'Hindi',
			'ho' => 'Hiri Motu',
			'hr' => 'Croatian',
			'hu' => 'Hungarian',
			'ig' => 'Igbo',
			'is' => 'Icelandic',
			'io' => 'Ido',
			'ii' => 'Sichuan Yi',
			'iu' => 'Inuktitut',
			'ie' => 'Interlingue',
			'ia' => 'Interlingua',
			'id' => 'Indonesian',
			'ik' => 'Inupiaq',
			'it' => 'Italian',
			'jv' => 'Javanese',
			'ja' => 'Japanese',
			'kl' => 'Kalaallisut',
			'kn' => 'Kannada',
			'ks' => 'Kashmiri',
			'kr' => 'Kanuri',
			'kk' => 'Kazakh',
			'km' => 'Central Khmer',
			'ki' => 'Kikuyu',
			'rw' => 'Kinyarwanda',
			'ky' => 'Kirghiz',
			'kv' => 'Komi',
			'kg' => 'Kongo',
			'ko' => 'Korean',
			'kj' => 'Kuanyama',
			'ku' => 'Kurdish',
			'lo' => 'Lao',
			'la' => 'Latin',
			'lv' => 'Latvian',
			'li' => 'Limburgan',
			'ln' => 'Lingala',
			'lt' => 'Lithuanian',
			'lb' => 'Luxembourgish',
			'lu' => 'Luba-Katanga',
			'lg' => 'Ganda',
			'mk' => 'Macedonian',
			'mh' => 'Marshallese',
			'ml' => 'Malayalam',
			'mi' => 'Maori',
			'mr' => 'Marathi',
			'ms' => 'Malay',
			'mg' => 'Malagasy',
			'mt' => 'Maltese',
			'mn' => 'Mongolian',
			'na' => 'Nauru',
			'nv' => 'Navajo',
			'nr' => 'Ndebele, South',
			'nd' => 'Ndebele, North',
			'ng' => 'Ndonga',
			'ne' => 'Nepali',
			'nn' => 'Norwegian Nynorsk',
			'nb' => 'Bokmål, Norwegian',
			'no' => 'Norwegian',
			'ny' => 'Chichewa',
			'oc' => 'Occitan (post 1500)',
			'oj' => 'Ojibwa',
			'or' => 'Oriya',
			'om' => 'Oromo',
			'os' => 'Ossetian',
			'pa' => 'Panjabi',
			'fa' => 'Persian',
			'pi' => 'Pali',
			'pl' => 'Polish',
			'pt' => 'Português',
			'ps' => 'Pushto',
			'qu' => 'Quechua',
			'rm' => 'Romansh',
			'ro' => 'Romanian',
			'rn' => 'Rundi',
			'ru' => 'Russian',
			'sg' => 'Sango',
			'sa' => 'Sanskrit',
			'si' => 'Sinhala',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'se' => 'Northern Sami',
			'sm' => 'Samoan',
			'sn' => 'Shona',
			'sd' => 'Sindhi',
			'so' => 'Somali',
			'st' => 'Sotho, Southern',
			'es' => 'Español',
			'sc' => 'Sardinian',
			'sr' => 'Serbian',
			'ss' => 'Swati',
			'su' => 'Sundanese',
			'sw' => 'Swahili',
			'sv' => 'Swedish',
			'ty' => 'Tahitian',
			'ta' => 'Tamil',
			'tt' => 'Tatar',
			'te' => 'Telugu',
			'tg' => 'Tajik',
			'tl' => 'Tagalog',
			'th' => 'Thai',
			'bo' => 'Tibetan',
			'ti' => 'Tigrinya',
			'to' => 'Tonga (Tonga Islands)',
			'tn' => 'Tswana',
			'ts' => 'Tsonga',
			'tk' => 'Turkmen',
			'tr' => 'Turkish',
			'tw' => 'Twi',
			'ug' => 'Uighur',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			've' => 'Venda',
			'vi' => 'Vietnamese',
			'vo' => 'Volapük',
			'cy' => 'Welsh',
			'wa' => 'Walloon',
			'wo' => 'Wolof',
			'xh' => 'Xhosa',
			'yi' => 'Yiddish',
			'yo' => 'Yoruba',
			'za' => 'Zhuang',
			'zu' => 'Zulu'
		);
		if ( array_key_exists($code, $languages) )
		{
			return $languages[$code];
		}
		else
		{
			return $code;
		}
	}
}

/*
 * Element filtering callback class
 * Sorting criteria (like in SQL, ASC or DESC)
 */
class Filter {
	private $order_by = 'created';
	private $direction = 'desc';
	private $is_date;
	private $LANG;

	function __construct($order_by, $direction)
	{
		$this->order_by = $order_by;
		$this->direction = $direction;
	}

	function set_order_by($order_by)
	{
		$this->order_by = $order_by;
	}
	
	function set_direction($direction)
	{
		$this->direction = $direction;
	}
	
	function set_is_date($is_date)
	{
		$this->is_date = $is_date;
	}
	
	function set_lang($lang)
	{
		$this->LANG = $lang;
	}
	
	function sort($a, $b)
	{
		$previous = $a[$this->order_by];
		$next = $b[$this->order_by];

		// When comparing dates,
		// convert to unix timestamp
		if ( (bool) $this->is_date )
		{
			$previous = strtotime($a[$this->order_by]);
			$next = strtotime($b[$this->order_by]);
		}
		else
		{
			// Comparing by custom field content
			// Use apropriate language
			if ( (bool) $this->LANG )
			{
				$localized = json_decode($a[$this->order_by], TRUE);
				$previous = (array_key_exists($this->LANG, $localized)) ? $localized[$this->LANG] : '';
				$localized = json_decode($b[$this->order_by], TRUE);
				$next = (array_key_exists($this->LANG, $localized)) ? $localized[$this->LANG] : '';
			}
		}

		switch ( $this->direction )
		{
			case 'desc' :
			return ( -1 * strcmp($previous, $next) );
			break;
			
			case 'asc' :
			return strcmp($previous, $next);
			break;
		}
	}	
}

/* End of file Common.php */
/* Location: ./application/controllers/Common.php */
