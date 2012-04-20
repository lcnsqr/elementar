<?php
/*
 *      main.php
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
 * Backend Main Class 
 * 
 * Backend main config page
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Main extends CI_Controller {

	// i18n settings
	var $LANG;
	var $LANG_AVAIL = array();

	function __construct()
	{
		parent::__construct();

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
		
		// Language related Settings (site name)
		$site_names = json_decode($this->storage->get_config('name'), TRUE);
		$this->config->set_item('site_name', (array_key_exists($this->LANG, $site_names)) ? $site_names[$this->LANG] : '');
	}
	
	/**
	 * Config main method
	 * 
	 * @access public
	 * @return void
	 */
	function index()
	{
		// Account info
		$account_id = $this->session->userdata('account_id');
		$is_logged = TRUE;
		$username = $this->access->get_account_username($account_id);

		// Client controller (javascript files)
		$js = array(
			'/js/backend/jquery-1.7.2.min.js',
			'/js/backend/jquery.easing.1.3.js',
			'/js/backend/jquery.timers-1.2.js',
			'/js/backend/backend_account.js',
			'/js/backend/tiny_mce/jquery.tinymce.js',
			'/js/backend/backend_client_warning.js',
			'/js/backend/backend_settings.js',
			'/js/backend/backend_composite_field.js',
			'/js/backend/jquery.json-2.3.min.js',
			'/js/backend/backend_anchor.js'
		);
		
		// Top menu
		$resource_menu = array(
			'<strong>' . $this->lang->line('elementar_settings') . '</strong>',
			span('&bull;', array('class' => 'top_menu_sep')),
			anchor('/backend/account', $this->lang->line('elementar_accounts'), array('title' => $this->lang->line('elementar_accounts'))),
			span('&bull;', array('class' => 'top_menu_sep')),
			anchor('/backend/editor', $this->lang->line('elementar_editor'), array('title' => $this->lang->line('elementar_contents')))
		);

		// View main variables
		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => ul($resource_menu)
		);

		// Localized texts
		$data['elementar_exit'] = $this->lang->line('elementar_exit');
		$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
		$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
		$data['elementar_copyright'] = $this->lang->line('elementar_copyright');
		
		$data['elementar_main'] = $this->lang->line('elementar_main');
		$data['elementar_languages'] = $this->lang->line('elementar_languages');
		$data['elementar_email'] = $this->lang->line('elementar_email');

		$this->load->view('backend/backend_main', $data);

	}

	/**
	 * Render requested config page by XHR
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_settings()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$page = $this->input->get('page', TRUE);
		
		switch ( $page )
		{
			case 'main':
			$response = array(
				'done' => TRUE,
				'html' => $this->_render_main_form()
			);
			break;
			
			case 'languages':
			$response = array(
				'done' => TRUE,
				'html' => $this->_render_languages_form()
			);
			break;
			
			case 'email':
			$response = array(
				'done' => TRUE,
				'html' => $this->_render_email_form()
			);
			break;
			
			default:
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			break;
		}
		
		$this->output->set_output_json($response);
	}
	
	/**
	 * Write config settings by page, requested by XHR
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_settings()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$page = $this->input->post('page', TRUE);
		
		switch ( $page )
		{
			case 'main':
			$response = $this->_write_main();
			break;
			
			case 'languages':
			$response = $this->_write_languages();
			break;
			
			case 'email':
			$response = $this->_write_email();
			break;
			
			default:
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			break;
		}
		
		$this->output->set_output_json($response);
	}

	/**
	 * Generate main config form
	 * 
	 * @access private
	 * @return string
	 */
	private function _render_main_form()
	{
		// HTML rendered form
		$form = $this->common->render_form_field('name', $this->lang->line('elementar_site_name'), 'name', NULL, $this->storage->get_config('name'), TRUE);

		// Favicon choice
		$form .= $this->common->render_form_field('file', 'Favicon', 'favicon', NULL, $this->storage->get_config('favicon'), FALSE);

		// Google Site Verification
		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_meta_google-site-verification'), 'google-site-verification', NULL, $this->storage->get_config('google-site-verification'), FALSE);

		$form .= div_open(array('class' => 'form_control_buttons'));
		
		// Submit button
		$attributes = array(
		    'name' => 'button_settings_save',
		    'id' => 'button_settings_save',
		    'class' => 'noform',
		    'data-page' => 'main',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);

		$form .= div_close("<!-- form_control_buttons -->");

		$data = array(
			'page' => $this->lang->line('elementar_main'),
			'form' => $form
		);

		return $this->load->view('backend/backend_main_settings', $data, true);
	}
	
	/**
	 * Write main config
	 * 
	 * @access private
	 * @return array
	 */
	private function _write_main()
	{
		/*
		 * Site name
		 */
		$values = array();
		foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
		{
			$values[$lang_code] = htmlentities($this->input->post('name_' . $lang_code, TRUE), ENT_QUOTES, "UTF-8");
		}
		$name = json_encode($values);
		
		$this->storage->put_config('name', $name);
		
		/*
		 * Favicon
		 */
		$favicon = json_decode($this->input->post('favicon', TRUE), TRUE);
		if ( $favicon['uri'] != '' )
		{
			$this->storage->put_config('favicon', $this->input->post('favicon', TRUE));
		}
		
		// Google Site Verification
		$google_site_verification = $this->input->post('google-site-verification', TRUE);
		$this->storage->put_config('google-site-verification', $google_site_verification);

		// Erase all cache files
		$contents = $this->storage->get_content_descendants(1);
		// Including home page
		$contents = array_merge(array('id' => '1'), $contents);
		$this->load->library('content');
		foreach ( $contents as $content )
		{
			$this->content->set_id($content['id']);
			$this->content->load();
			// Erase cached content
			$this->content->erase_cache();
		}

		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_write_config'),
			'html' => $this->_render_main_form()
		);
		return $response;

	}

	/**
	 * Generate languages form
	 * 
	 * @access private
	 * @return string
	 */
	private function _render_languages_form()
	{
		// Ensure the current values
		$i18n_settings = json_decode($this->storage->get_config('i18n'), TRUE);
		foreach($i18n_settings as $i18n_setting)
		{
			if ( (bool) $i18n_setting['default'] )
			{
				$this->LANG = $i18n_setting['code'];
				// Default language is the first in array
				$this->LANG_AVAIL = array_merge(array($i18n_setting['code'] => $i18n_setting['name']), $this->LANG_AVAIL);
			}
			else
			{
				$this->LANG_AVAIL[$i18n_setting['code']] = $i18n_setting['name'];
			}
		}

		// HTML rendered form
		$form = "";
		
		$language_codes = implode(',', array_keys($this->LANG_AVAIL));

		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_language_codes'), 'language_codes', NULL, $language_codes, FALSE);

		$options = $this->LANG_AVAIL;
		$selected = $this->LANG;
		$attributes = "id=\"language_default\" class=\"noform\"";
		$default_language = form_dropdown('language_default', $options, $selected, $attributes);
		$label = form_label($this->lang->line('elementar_language_default'), NULL, array('class' => 'field_label'));
		$form .= backend_input_columns($label, $default_language);
		
		$form .= div_open(array('class' => 'form_control_buttons'));
		
		//  Submit button
		$attributes = array(
		    'name' => 'button_settings_save',
		    'id' => 'button_settings_save',
		    'class' => 'noform',
		    'data-page' => 'languages',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);

		$form .= div_close("<!-- form_control_buttons -->");

		$data = array(
			'page' => $this->lang->line('elementar_languages'),
			'form' => $form
		);

		return $this->load->view('backend/backend_main_settings', $data, true);
	}

	/**
	 * Write language settings
	 * 
	 * @access private
	 * @return array
	 */
	private function _write_languages()
	{
		$language_codes_param = $this->input->post('language_codes');
		$language_codes = explode(',', $language_codes_param);
		if ( ! (bool) $language_codes_param || ! is_array($language_codes) )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_language_undefined')
			);
			return $response;
		}
		
		$language_default = $this->input->post('language_default', TRUE);
		
		$i18n = array();
		foreach ( $language_codes as $lang_code )
		{
			$default = ($lang_code == $language_default) ? $default = TRUE : $default = FALSE;
			$i18n[] = array(
				'code' => $lang_code,
				'name' => $this->common->which_language($lang_code),
				'default' => $default
			);
		}
		
		$this->storage->put_config('i18n', json_encode($i18n));

		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_write_config'),
			'html' => $this->_render_languages_form()
		);
		return $response;

	}

	/**
	 * Generate email settings form
	 * 
	 * @access private
	 * @return string
	 */
	private function _render_email_form()
	{
		// HTML rendered form
		$form = "";
		
		$json = $this->storage->get_config('email');
		$email_settings = ( (bool) $json == FALSE ) ? array('smtp_host' => '', 'smtp_port' => '', 'smtp_user' => '', 'smtp_pass' => '') : json_decode($json, TRUE);

		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_smtp_host'), 'smtp_host', NULL, $email_settings['smtp_host'], FALSE);
		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_smtp_port'), 'smtp_port', NULL, $email_settings['smtp_port'], FALSE);
		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_smtp_user'), 'smtp_user', NULL, $email_settings['smtp_user'], FALSE);
		$form .= $this->common->render_form_field('password', $this->lang->line('elementar_smtp_pass'), 'smtp_pass', NULL, $email_settings['smtp_pass'], FALSE);

		$form .= div_open(array('class' => 'form_control_buttons'));
		
		//  Submit button
		$attributes = array(
		    'name' => 'button_settings_save',
		    'id' => 'button_settings_save',
		    'class' => 'noform',
		    'data-page' => 'email',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);

		$form .= div_close("<!-- form_control_buttons -->");

		$data = array(
			'page' => $this->lang->line('elementar_email'),
			'form' => $form
		);

		return $this->load->view('backend/backend_main_settings', $data, true);
	}

	/**
	 * Write email settings
	 * 
	 * @access private
	 * @return array
	 */
	private function _write_email()
	{
		$email_settings = array(
			'smtp_host' => $this->input->post('smtp_host', TRUE), 
			'smtp_port' => $this->input->post('smtp_port', TRUE), 
			'smtp_user' => $this->input->post('smtp_user', TRUE), 
			'smtp_pass' => $this->input->post('smtp_pass', TRUE)
		);
		$this->storage->put_config('email', json_encode($email_settings));

		$response = array(
			'done' => TRUE,
			'message' => $this->lang->line('elementar_xhr_write_config'),
			'html' => $this->_render_email_form()
		);
		return $response;

	}

}

/* End of file main.php */
/* Location: ./application/controllers/backend/main.php */
