<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// string
		$this->load->helper('string');
		
		// security
		$this->load->helper('security');
		
		// session
		$this->load->library('session');
		
		// cookie
		$this->load->helper('cookie');

		// DB account
		$this->db_acc = $this->load->database('account', TRUE);

		// Modelo session
		$this->load->model('M_session', 'sess');

		// Modelo account
		$this->load->model('M_account', 'account');

		// DB cms
		$this->db_cms = $this->load->database('cms', TRUE);

		// CMS Admin model
		$this->load->model('M_cms_admin', 'cms');
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common');

		$this->load->helper(array('form', 'html', 'text', 'url'));
		
		// montar tabela
		$this->load->library('table');

		/*
		 * Verificar sessão autenticada
		 * de usuário autorizado no admin
		 */
		$user_id = $this->account->logged($this->sess->session_id());
		if ( $user_id !== FALSE )
		{
			$data = array(
				'is_logged' => TRUE,
				'username' => $this->account->get_user_name($user_id)
			);
		}
		else
		{
			$data = array(
				'is_logged' => FALSE,
				'title' => $this->config->item('site_name'),
				'js' => array(
					'/js/backend/jquery-1.6.2.min.js', 
					'/js/backend/backend_session.js', 
					'/js/backend/jquery.timers-1.2.js', 
					'/js/backend/backend_client_warning.js'
				),
				'action' => '/' . uri_string(),
				'elapsed_time' => $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			);
			$login = $this->load->view('backend/backend_login', $data, TRUE);
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
			'/js/backend/jquery-1.6.2.min.js',
			'/js/backend/jquery.easing.1.3.js',
			'/js/backend/jquery.timers-1.2.js',
			'/js/backend/backend_account.js',
			'/js/backend/backend_anchor.js',
			'/js/backend/backend_account_anchor_section.js',
			'/js/backend/backend_upload.js',
			'/js/backend/tinymce/jscripts/tiny_mce/jquery.tinymce.js'
		);
		
		/*
		 * Resource menu
		 */
		$resource_menu = "<ul><li><strong>Usuários</strong></li><li>|</li><li><a href=\"/backend/content\" title=\"Conteúdo\">Conteúdo</a></li></ul>";

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => $resource_menu
		);

		$this->load->view('backend/backend_account', $data);

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
				case "users" :
				$update = TRUE;
				$data = array(
					'users' => $this->_get_users()
				);
				$html = $this->load->view('backend/backend_account_users', $data, TRUE);
				break;
				
				default :
				$update = FALSE;
				$html = "";
				
			}

		$response = array(
			'done' => TRUE,
			'update' => $update,
			'html' => $html
		);
		$this->common->ajax_response($response);

	}
	
	/**
	 * Remover conta
	 */
	function xhr_erase_user()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$user_id = $this->input->post('id', TRUE);
		
		if ( $user_id > 1 )
		{
			$this->account->remove_user($user_id);
			$response = array('done' => TRUE);
		}
		else
		{
			$response = array('done' => FALSE);
		}
		
		// Enviar resposta
		$this->common->ajax_response($response);
	}

	/**
	 * Verficiar campos e criar conta 
	 */
	function xhr_write_user()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		/*
		 * Verificar se existem dados POST
		 */
		if ($_POST)
		{
			/*
			 * Verificação dos campos
			 */
			$response = array('done' => TRUE);
						 
			/*
			 * Verificação de usuário
			 */
			$username = $this->input->post('user_login', TRUE);
			$valid = $this->account->validate_username($username);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['user_login_erro'] = $valid;
			}
			
			/*
			 * Verificação de email
			 */
			$email = $this->input->post('user_email', TRUE);
			$valid = $this->account->validate_email($email);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['user_email_erro'] = $valid;
			}
			
			/*
			 * Verificação de senha
			 */
			$senha = $this->input->post('user_password', TRUE);
			$valid = $this->account->validate_password($senha);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['user_password_erro'] = $valid;
			}
			
			/*
			 * Se tudo válido, registrar 
			 */
			if ( $response['done'] )
			{
				$user_id = $this->account->register_user($username, $email, $senha, '', TRUE);
				$response['html'] = $this->_get_users($user_id);
			}
			
			// Enviar resposta
			$this->common->ajax_response($response);
			
		}

	}
	
	function _get_users($id = NULL)
	{
		$users = $this->account->get_users($id);
		$html = "";
		foreach ($users as $user)
		{
			$this->table->set_heading('Login', 'Email', 'Data', '');
			$this->table->add_row($user['user'], $user['email'], $user['created'], "<a href=\"" . $user['id'] . "\" class=\"user_del\">Remover</a>");
			$html .= "<div class=\"user_info\">";
			$html .= $this->table->generate();
			$html .= "</div>";
			$this->table->clear();
		}
		return $html;
	}
	
}
