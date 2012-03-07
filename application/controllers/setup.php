<?php
/*
 *      setup.php
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


class Setup extends CI_Controller {

	function __construct()
	{
		parent::__construct();		

		/*
		 * Backend language file
		 */
		$this->lang->load('elementar', $this->config->item('language'));

		/*
		 * database
		 */
		$this->elementar = $this->load->database('elementar', TRUE);
		$this->db =& $this->elementar;

		/*
		 * Access model
		 */
		$this->load->model('Access', 'access');

		/*
		 * Elementar Common Library
		 */
		$this->load->library('common', array(
			'lang' => '', 
			'lang_avail' => array(), 
			'uri_prefix' => ''
		));

		/*
		 * Fields validation library
		 */
		$this->load->library('validation');
		
		/*
		 * Required helpers
		 */
		$this->load->helper(array('url', 'security'));

	}

	function index()
	{
		$data = array(
			'title' => 'Elementar Setup', 
			'js' => array(
				'/js/backend/jquery-1.7.1.min.js', 
				'/js/backend/backend_setup.js', 
				'/js/backend/jquery.timers-1.2.js', 
				'/js/backend/backend_client_warning.js'
			),
			'is_logged' => FALSE
		);

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

		/*
		 * Localized texts
		 */
		$data['elementar_setup_pending'] = 'Ações pendentes';
		$data['elementar_setup_username'] = 'Nome de usuário do administrador';
		$data['elementar_setup_email'] = 'Email do administrador';
		$data['elementar_setup_password'] = 'Senha do administrador';
		$data['elementar_setup_submit'] = 'Salvar';
		$data['elementar_finished_in'] = $this->lang->line('elementar_finished_in');
		$data['elementar_finished_elapsed'] = $this->lang->line('elementar_finished_elapsed');
		$data['elementar_copyright'] = $this->lang->line('elementar_copyright');
		 
		$this->load->view('backend/backend_setup', $data);
	}
	
	function _check_pending_actions()
	{
		$pending_message = array();
		
		/*
		 * Check for database
		 */
		$this->load->dbutil();
		if (! $this->dbutil->database_exists('elementar'))
		{
			$pending_message[] = 'Database not found';
		}
		else
		{
			/*
			 * Load current DB schema
			 */
			$this->load->library('migration');
			if ( ! $this->migration->current())
			{
				$pending_message[] = $this->migration->error_string();
			}
		}
		
		/*
		 * Check for cache directory permissions
		 */
		$cache_path = ( $this->config->item('cache_path') == '' ) ? FCPATH . APPPATH . 'cache/' : $this->config->item('cache_path');
		if (! is_really_writable($cache_path) )
		{
			$pending_message[] = 'Sem permissão para escrever em ' . $cache_path;
		}

		/*
		 * Check for upload directory permissions
		 */
		$files_path = FCPATH . 'files/';
		if (! is_really_writable($files_path) )
		{
			$pending_message[] = 'Sem permissão para escrever em ' . $files_path;
		}

		/*
		 * Check for migrations directory permissions
		 */
		/*
		$migrations_path = FCPATH . APPPATH . 'migrations/';
		if (! is_really_writable($migrations_path) )
		{
			$pending_message[] = 'Sem permissão para escrever em ' . $migrations_path;
		}
		*/
		
		/*
		 * Admin password
		 */
		if ( (bool) $this->access->get_account_password(1) )
		{
			$pending_message[] = 'Configuração concluída';
		}
		return $pending_message;
	}

	function xhr_write_admin()
	{
		if ( ! $this->input->is_ajax_request() )
			exit($this->lang->line('elementar_no_direct_script_access'));
		
		/*
		 * Refuse if password already set
		 */
		if ( (bool) $this->access->get_account_password(1) )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_bad_request')
			);
			$this->common->ajax_response($response);
			return;
		}

		$username = $this->input->post('username', TRUE);
		$email = $this->input->post('email', TRUE);
		$password = $this->input->post('password', TRUE);

		/*
		 * Assess account username
		 */
		$response = $this->validation->assess_username($username);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->common->ajax_response($response);
			return;
		}

		/*
		 * Assess email
		 */
		$response = $this->validation->assess_email($email);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->common->ajax_response($response);
			return;
		}

		/*
		 * Assess password
		 */
		$response = $this->validation->assess_password($password);
		if ( (bool) $response['done'] == FALSE )
		{
			$this->common->ajax_response($response);
			return;
		}

		/*
		 * Update admin account
		 */
		$this->access->put_account_username(1, $username);
		$this->access->put_account_email(1, $email);
		$this->access->put_account_password(1, $password);
		
		/*
		 * Log admin in
		 */
		$this->load->library('session');
		$this->session->set_userdata('account_id', 1);
		
		$response = array(
			'done' => TRUE,
			'location' => site_url("backend"),
			'message' => $this->lang->line('elementar_xhr_write_account')
		);
		$this->common->ajax_response($response);
	}
}

/* End of file setup.php */
/* Location: ./application/controllers/setup.php */
