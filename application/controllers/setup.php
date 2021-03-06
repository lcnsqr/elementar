<?php
/*
 *      setup.php
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
 * Setup Class 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Setup extends CI_Controller {

	/**
	 * Constructor - Load required libraries and database
	 *
	 * /application/config/database.php must be configured before loading this
	 */
	function __construct()
	{
		parent::__construct();		

		// Disable caching in backend
		$this->output->disable_cache();

		// Backend language file
		$this->lang->load('elementar', $this->config->item('language'));

		// Database
		$this->elementar = $this->load->database('elementar', TRUE);
		$this->db =& $this->elementar;

		// Create, read, update and delete Model
		$this->load->model('Storage', 'storage');

		// Access model
		$this->load->model('Access', 'access');

		// Fields validation library
		$this->load->library('validation');
		
		// Required helpers
		$this->load->helper(array('url', 'security'));

	}

	/**
	 * Show setup page
	 *
	 * @access	public
	 * @return	void
	 */
	function index()
	{
		// View variables
		$data = array(
			'title' => 'Elementar Setup', 
			'js' => array(
				JQUERY,
				BACKEND_SETUP,
				JQUERY_TIMERS,
				BACKEND_CLIENT_WARNING
			),
			'css' => array(
				BACKEND_RESET_CSS,
				BACKEND_CSS,
				BACKEND_TREE_CSS,
				BACKEND_WINDOW_CSS
			),
			'is_logged' => FALSE
		);

		// Actions needed before running setup
		$pending_message = $this->_check_pending_actions();

		$this->load->helper('html');
		if ( count($pending_message) > 0 )
		{
			$data['pending'] = TRUE;
			$data['pending_message'] = ul($pending_message);
		}
		else
		{
			$data['pending'] = FALSE;
			$data['pending_message'] = '';
		}

		// Localized texts
		$data['elementar_setup_pending'] = $this->lang->line('elementar_setup_pending');
		$data['elementar_setup_username'] = $this->lang->line('elementar_setup_username');
		$data['elementar_setup_email'] = $this->lang->line('elementar_setup_email');
		$data['elementar_setup_password'] = $this->lang->line('elementar_setup_password');
		$data['elementar_setup_submit'] = $this->lang->line('elementar_setup_submit');
		$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
		$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
		$data['elementar_copyright'] = $this->lang->line('elementar_copyright');
		 
		$this->load->view('backend/backend_setup', $data);
	}

	/**	
	 * Check actions needed before running setup
	 * 
	 * @return	array
	 */
	private function _check_pending_actions()
	{
		$pending_message = array();
		
		// Check for database
		$this->load->dbutil();
		if (! $this->dbutil->database_exists($this->db->database))
		{
			$pending_message[] = $this->lang->line('elementar_setup_database_not_found');
		}
		else
		{
			// Load current DB schema
			$this->load->library('migration');
			if ( ! $this->migration->current())
			{
				$pending_message[] = $this->migration->error_string();
			}
		}
		
		// Check for cache directory permissions
		$cache_path = ( $this->config->item('cache_path') == '' ) ? FCPATH . APPPATH . 'cache/' : $this->config->item('cache_path');
		if (! is_really_writable($cache_path) )
		{
			$pending_message[] = $this->lang->line('elementar_setup_no_write_perms_in') . $cache_path;
		}

		// Check for upload directory permissions
		$files_path = FCPATH . 'files/';
		if (! is_really_writable($files_path) )
		{
			$pending_message[] = $this->lang->line('elementar_setup_no_write_perms_in') . $files_path;
		}

		// Check for migrations directory permissions
		/*
		$migrations_path = FCPATH . APPPATH . 'migrations/';
		if (! is_really_writable($migrations_path) )
		{
			$pending_message[] = $this->lang->line('elementar_setup_no_write_perms_in') . $migrations_path;
		}
		*/
		
		// Abort setup if admin password is already set
		if ($this->db->table_exists('account'))
		{
			if ( (bool) $this->access->get_account_password(1) )
			{
				$pending_message[] = $this->lang->line('elementar_setup_done');
			}
		}
		return $pending_message;
	}

	/**	
	 * XHR request: Write main admin account
	 * 
	 * @return	void
	 */
	function xhr_write_admin()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
		
		// Abort if password already set
		if ( (bool) $this->access->get_account_password(1) )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->output->set_output_json($response);
			return;
		}

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

		// Assess email
		$response = $this->validation->assess_email($email);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->output->set_output_json($response);
			return;
		}

		// Assess password
		$response = $this->validation->assess_password($password);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->output->set_output_json($response);
			return;
		}

		// Update admin account
		$this->access->put_account_username(1, $username);
		$this->access->put_account_email(1, $email);
		$this->access->put_account_password(1, $password);
		
		// Load encryption key before session library
		$this->config->set_item('encryption_key', $this->storage->get_config('encryption_key'));
		
		// Log admin in
		$this->load->library('session');
		$this->session->set_userdata('account_id', 1);
		$this->session->set_userdata('group_id', 1);
		
		// Send XHR response
		$response = array(
			'done' => TRUE,
			'location' => site_url("backend"),
			'message' => $this->lang->line('elementar_xhr_write_account')
		);
		$this->output->set_output_json($response);
	}
}

/* End of file setup.php */
/* Location: ./application/controllers/setup.php */
