<?php
/*
 *      account.php
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
 * Backend Account Class 
 * 
 * Users and groups management
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Account extends CI_Controller {

	// i18n settings
	var $LANG;
	var $LANG_AVAIL = array();

	function __construct()
	{
		parent::__construct();

		// Disable caching in backend
		$this->output->disable_cache();

		//  CI helpers
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
		
		// CI session class
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

		// Fields validation library
		$this->load->library('validation');
	}
	
	/**
	 * Backend Account manager main method
	 * 
	 * @access method
	 * @return void
	 */
	function index()
	{
		// Admin info
		$account_id = $this->session->userdata('account_id');
		$is_logged = TRUE;
		$username = $this->access->get_account_username($account_id);

		// Client controller (javascript files)
		$js = array(
			JQUERY,
			JQUERY_EASING,
			JQUERY_TIMERS,
			JQUERY_TINYMCE,
			BACKEND_COMPOSITE_FIELD,
			BACKEND_ACCOUNT,
			BACKEND_ACCOUNT_TREE,
			BACKEND_ACCOUNT_WINDOW,
			BACKEND_CLIENT_WARNING,
			BACKEND_ANCHOR,
			JQUERY_COOKIE
		);
		
		// CSS for account view
		$css = array(
			BACKEND_RESET_CSS,
			BACKEND_CSS,
			BACKEND_TREE_CSS,
			BACKEND_WINDOW_CSS
		);

		// Top menu
		$resource_menu = array(
			anchor('/backend', $this->lang->line('elementar_settings'), array('title' => $this->lang->line('elementar_settings'))),
			span('&bull;', array('class' => 'top_menu_sep')),
			'<strong>' . $this->lang->line('elementar_accounts') . '</strong>',
			span('&bull;', array('class' => 'top_menu_sep')),
			anchor('/backend/editor', $this->lang->line('elementar_editor'), array('title' => $this->lang->line('elementar_contents')))
		);

		// Backend common view variables
		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'css' => $css,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => ul($resource_menu)
		);

		// load tree
		$data['parent'] = $this->lang->line('elementar_accounts');
		$data['backend_account_tree'] = $this->_render_tree_listing();
		
		// Localized view messages
		$data['elementar_exit'] = $this->lang->line('elementar_exit');
		$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
		$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
		$data['elementar_copyright'] = $this->lang->line('elementar_copyright');

		$this->load->view('backend/backend_account', $data);

	}

	/**
	 * Render accounts in tree by group
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_group_listing()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$group_id = $this->input->post('group_id');

		if ( ! (bool) $group_id )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
			return;
		}

		$accounts = $this->access->get_accounts($group_id);
		$group = array('accounts' => ( (bool) $accounts ) ? $accounts : array());

		$data = array('group' => $group);

		// Localized texts
		$data['elementar_delete'] = $this->lang->line('elementar_delete');
		$data['elementar_edit'] = $this->lang->line('elementar_edit');
		$data['elementar_edit_group'] = $this->lang->line('elementar_edit_group');
		$data['elementar_new_group'] = $this->lang->line('elementar_new_group');
		$data['elementar_edit_account'] = $this->lang->line('elementar_edit_account');
		$data['elementar_new_account'] = $this->lang->line('elementar_new_account');

		$html = $this->load->view('backend/backend_account_tree_group', $data, TRUE);

		$response = array(
			'done' => TRUE,
			'id' => $group_id,
			'html' => $html
		);
		$this->output->set_output_json($response);
		
	}

	/**
	 * List groups in tree with a selected one
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_tree_listing()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$group_id = $this->input->post('group_id');

		if ( ! (bool) $group_id )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
			return;
		}

		$html = $this->_render_tree_listing($group_id);

		$response = array(
			'done' => TRUE,
			'id' => $group_id,
			'html' => $html
		);
		$this->output->set_output_json($response);
		
	}

	/**
	 * Render HTML tree listings
	 * 
	 * @access private
	 * @param integer
	 * @return string
	 */
	private function _render_tree_listing($group_id = NULL)
	{
		$groups = array();
		
		foreach ($this->access->get_groups() as $group)
		{
			$accounts = ( $group['id'] == $group_id ) ? $this->access->get_accounts($group['id']) : array();
			$display_accounts = ( $group['id'] == $group_id && count($accounts) > 0 ) ? TRUE : FALSE;
			$groups[] = array(
				'id' => $group['id'],
				'name' => ( $this->lang->line('elementar_group_' . $group['id']) != '' ) ? $this->lang->line('elementar_group_' . $group['id']) : $group['name'],
				'description' => ( $this->lang->line('elementar_group_' . $group['id'] . '_description') != '' ) ? $this->lang->line('elementar_group_' . $group['id'] . '_description') : $group['description'],
				'children' => $group['children'],
				'display_accounts' => $display_accounts,
				'accounts' => $accounts
			);
		}
		
		$data['groups'] = $groups;

		// Localized texts
		$data['elementar_delete'] = $this->lang->line('elementar_delete');
		$data['elementar_edit'] = $this->lang->line('elementar_edit');
		$data['elementar_edit_group'] = $this->lang->line('elementar_edit_group');
		$data['elementar_new_group'] = $this->lang->line('elementar_new_group');
		$data['elementar_edit_account'] = $this->lang->line('elementar_edit_account');
		$data['elementar_new_account'] = $this->lang->line('elementar_new_account');

		
		// Set default language for view
		$data['lang'] = $this->LANG;
		
		$html = $this->load->view('backend/backend_account_tree', $data, true);
		
		return $html;
	}
	
	/**
	 * Create/edit group form
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_group_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming group ID
		$group_id = $this->input->post('group_id', TRUE);

		// Group ID (if any, hidden)
		$attributes = array(
			'class' => 'noform',
			'name' => 'group_id',
			'value'=> $group_id,
			'type' => 'hidden'
		);
		$form = form_input($attributes);

		// Group name
		$value = $this->access->get_group_name($group_id);
		$form .= $this->common->render_form_field('name', $this->lang->line('elementar_name'), 'name', NULL, $value, FALSE);

		// Group description
		$value = $this->access->get_group_description($group_id);
		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_group_description'), 'description', NULL, $value, FALSE);

		// Save button
		$form .= div_open(array('class' => 'form_control_buttons'));
		$attributes = array(
		    'name' => 'button_group_save',
		    'id' => 'button_group_save',
		    'class' => 'noform',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);

		$form .= div_close();
		
		if ( (bool) $group_id )
		{
			$data['header'] = $this->lang->line('elementar_edit_group');
		}
		else
		{
			$data['header'] = $this->lang->line('elementar_new_group');
		}
		
		$data['form'] = $form;
		
		$html = $this->load->view('backend/backend_account_form', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);

		$this->output->set_output_json($response);

	}

	/**
	 * Save group
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_group()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming group ID
		$group_id = $this->input->post('group_id', TRUE);

		// Other group fields
		$name = $this->input->post('name', TRUE);
		$description = $this->input->post('description', TRUE);
		
		// Value verification
		if ( $name == '' )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_return_name_error')
			);
			$this->output->set_output_json($response);
			return;
		}
		
		if ( (bool) $group_id )
		{
			// Update group
			$this->access->put_group_name($group_id, $name);
			$this->access->put_group_description($group_id, $description);
		}
		else
		{
			// Create group
			$group_id = $this->access->put_group($name, $description);
		}
		
		$response = array(
			'done' => TRUE,
			'group_id' => $group_id,
			'message' => $this->lang->line('elementar_xhr_write_group')
		);
		$this->output->set_output_json($response);

	}

	/**
	 * Remove group
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_erase_group()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$group_id = $this->input->post('id', TRUE);
		$name = $this->access->get_group_name($group_id);

		if ( (int) $group_id > 1 )
		{
			$this->access->delete_group($group_id);
			$response = array(
				'done' => TRUE,
				'message' => $name . ' ' . $this->lang->line('elementar_xhr_erase')
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_erase_admin') . ' ' . $name
			);
		}
		
		// Send response
		$this->output->set_output_json($response);
	}

	/**
	 * Create/edit account form
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_render_account_form()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming account ID
		$account_id = $this->input->post('account_id', TRUE);

		// Account ID (if any, hidden)
		$attributes = array(
			'class' => 'noform',
			'name' => 'account_id',
			'value'=> $account_id,
			'type' => 'hidden'
		);
		$form = form_input($attributes);

		// Group ID (hidden field)
		if ( (bool) $account_id )
		{
			$group_id = $this->access->get_account_group($account_id);
		}
		else
		{
			$group_id = $this->input->post('group_id', TRUE);
		}
		$attributes = array(
			'class' => 'noform',
			'name' => 'group_id',
			'value'=> $group_id,
			'type' => 'hidden'
		);
		$form .= form_input($attributes);

		// Account name
		$value = $this->access->get_account_username($account_id);
		$form .= $this->common->render_form_field('name', $this->lang->line('elementar_account_username'), 'username', NULL, $value, FALSE);

		// Account email
		$value = $this->access->get_account_email($account_id);
		$form .= $this->common->render_form_field('line', $this->lang->line('elementar_account_email'), 'email', NULL, $value, FALSE);

		// Account password
		$value = '';
		$form .= $this->common->render_form_field('password', $this->lang->line('elementar_account_password'), 'password', NULL, $value, FALSE);

		// Save button
		$form .= div_open(array('class' => 'form_control_buttons'));
		$attributes = array(
		    'name' => 'button_account_save',
		    'id' => 'button_account_save',
		    'class' => 'noform',
		    'content' => $this->lang->line('elementar_save')
		);
		$form .= form_button($attributes);

		$form .= div_close();
		
		if ( (bool) $account_id )
		{
			$data['header'] = $this->lang->line('elementar_edit_account');
		}
		else
		{
			$data['header'] = $this->lang->line('elementar_new_account');
		}
		
		$data['form'] = $form;
		
		$html = $this->load->view('backend/backend_account_form', $data, true);

		$response = array(
			'done' => TRUE,
			'html' => $html
		);

		$this->output->set_output_json($response);

	}

	/**
	 * Save an account
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_account()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Create or update? Check for incoming group ID
		$account_id = $this->input->post('account_id', TRUE);

		// Other account fields
		$username = $this->input->post('username', TRUE);
		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);

		// Assess account username
		$response = $this->validation->assess_username($username);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->output->set_output_json($response);
			return;
		}

		if ( ! (bool) $account_id )
		{
			if ( (bool) $this->access->get_account_by_username($username) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_username_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}
		}
		else
		{
			if ( (bool) $this->access->get_account_by_username($username) && $username != $this->access->get_account_username($account_id) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_username_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}
		}

		// Assess email
		$response = $this->validation->assess_email($email);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->output->set_output_json($response);
			return;
		}
		if ( ! (bool) $account_id )
		{
			if ( (bool) $this->access->get_account_by_email($email) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}
		}
		else
		{
			if ( (bool) $this->access->get_account_by_email($email) && $email != $this->access->get_account_email($account_id) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}
		}

		// Assess password
		$response = $this->validation->assess_password($password);
		if ( (bool) $password )
		{
			if ( (bool) $response['done'] == FALSE )
			{
				$this->output->set_output_json($response);
				return;
			}
		}

		if ( (bool) $account_id )
		{
			// Update account
			$this->access->put_account_username($account_id, $username);
			$this->access->put_account_email($account_id, $email);
			if ( (bool) $password )
			{
				// Avoid writing empty password on update
				$this->access->put_account_password($account_id, $password);
			}
			$group_id = $this->input->post('group_id', TRUE);
		}
		else
		{
			// Create account
			$account_id = $this->access->put_account($username, $email, $password);
			
			// Add account to group
			$group_id = $this->input->post('group_id', TRUE);
			$this->access->put_account_group($account_id, $group_id);
		}
		
		$response = array(
			'done' => TRUE,
			'group_id' => $group_id,
			'account_id' => $account_id,
			'message' => $this->lang->line('elementar_xhr_write_account')
		);
		$this->output->set_output_json($response);

	}

	/**
	 * Remove an account
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_erase_account()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		$account_id = $this->input->post('id', TRUE);
		$username = $this->access->get_account_username($account_id);

		if ( (int) $account_id > 1 )
		{
			$this->access->delete_account($account_id);
			$response = array(
				'done' => TRUE,
				'message' => $username . ' ' . $this->lang->line('elementar_xhr_erase')
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_erase_admin') . ' ' . $username
			);
		}
		
		// Send response
		$this->output->set_output_json($response);

	}

	/**
	 * Associate account to group
	 * XHR request
	 * 
	 * @access public
	 * @return void
	 */
	function xhr_write_account_group()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));

		// Group id
		$group_id = $this->input->post('group_id', TRUE);

		// Account id
		$account_id = $this->input->post('account_id', TRUE);

		if ( (bool) $group_id && (bool) $account_id && ( $group_id != $account_id ) && ( 1 != (int) $account_id ) )
		{
			$this->access->put_account_group($account_id, $group_id);
			$response = array(
				'done' => TRUE,
				'group_id' => $group_id
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
}

/* End of file account.php */
/* Location: ./application/controllers/backend/account.php */
