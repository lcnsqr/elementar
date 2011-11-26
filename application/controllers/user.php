<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		/*
		 * CI helpers
		 */
		$this->load->helper(array('string', 'security', 'cookie', 'url'));
		
		/*
		 * Session CI library
		 */
		$this->load->library('session');

		/*
		 * Elementar database
		 */
		$this->elementar = $this->load->database('elementar', TRUE);

		/*
		 * Account model
		 */
		$this->load->model('Account', 'account', TRUE);

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

	}
	
	function index()
	{
		/*
		 * Formulário de login
		 */
		$data = array('title' => "Identificação");
		$user_id = $this->session->userdata('user_id');
		if ( (bool) $user_id !== FALSE )
		{
			$data['is_logged'] = TRUE;
			$data['username'] = $this->account->get_user_name($user_id);
		}
		else
		{
			$data['is_logged'] = FALSE;
		}
		$this->load->view('session', $data);
		
	}
	
	function login()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$response = array('done' => FALSE);

		$username = $this->input->post("login_usuario", TRUE);
		$password = $this->input->post("login_senha", TRUE);

		$user_id = $this->account->get_user_id($username);
		
		if ( (bool) $user_id !== FALSE)
		{
			if ( $this->account->authenticate($user_id, $password) )
			{
				$this->session->set_userdata('user_id', $user_id);
				
				$response = array(
					'done' => TRUE,
					'msg' => "Login efetuado com sucesso"
				);
			}
			else
			{
				$response['msg'] = "Senha incorreta";
			}
		}
		else
		{
			$response['msg'] = "Login incorreto";
		}
		
		// Enviar resposta
		$this->_ajax_response($response);

	}
	
	function logout()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$this->session->unset_userdata('user_id');
		
		$response = array('done' => TRUE);
		$this->_ajax_response($response);
	}

	/*
	 * Verficiar campos e criar conta 
	 */
	function register()
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
			$username = $this->input->post('cadastro_usuario', TRUE);
			$valid = $this->account->validate_username($username);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['cadastro_usuario_erro'] = $valid;
			}
			
			/*
			 * Verificação de email
			 */
			$email = $this->input->post('cadastro_email', TRUE);
			$valid = $this->account->validate_email($email);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['cadastro_email_erro'] = $valid;
			}
			
			/*
			 * Verificação de senha
			 */
			$senha = $this->input->post('cadastro_senha', TRUE);
			$valid = $this->account->validate_password($senha);
			
			if ( $valid !== TRUE )
			{
				$response['done'] = FALSE;
				$response['cadastro_senha_erro'] = $valid;
			}
			
			/*
			 * Se tudo válido, registrar campos,
			 * hash e enviar email de
			 * confirmação
			 */
			if ( $response['done'] )
			{
				$hash = random_string('unique');
				$this->account->register_user($username, $email, $senha, $hash);
				
				// Enviar email
				$this->send_email($email, "Confirmação de cadastro", site_url("/user/confirm_registration/") . $hash);
			}
			
			// Enviar resposta
			$this->_ajax_response($response);
			
		}

	}
	
	/*
	 * Gerar hash para redefinição de senha
	 * e enviar por email
	 */
	function reset_password()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$email = $this->input->post("user_email", TRUE);
		
		$hash = random_string('unique');
		
		$verified = $this->account->reset_password_hash($email, $hash);
		if ( $verified === TRUE )
		{
			// Enviar email para redefinição da senha
			$this->send_email($email, "Redefinição de senha", site_url("/user/confirm_reset_password") . "/" . $hash);

			$response = array(
				'done' => TRUE
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'msg' => $verified
			);
		}
		
		// Enviar resposta
		$this->_ajax_response($response);
	}

	/*
	 * Alterar senha a partir de hash eviado por email
	 */
	function confirm_reset_password()
	{
		/*
		 * reset hash = url last segment
		 */
		$hash = $this->uri->segment($this->uri->total_segments());
		
		$data = array(
			'title' => 'Redefinir senha',
			'hash' => $hash
		);
		
		$data['verified'] = $this->account->verify_reset_password_hash($hash);
		
		$this->load->view('reset_password', $data);
	}

	/*
	 * Gerar hash para redefinição de senha
	 * e enviar por email
	 */
	function reset_new_password()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');

		$hash = $this->input->post("hash", TRUE);
		$password = $this->input->post("nova_senha", TRUE);
		
		$verified = $this->account->change_reset_password($hash, $password);
		if ( $verified === TRUE )
		{
			$response = array(
				'done' => TRUE
			);
		}
		else
		{
			$response = array(
				'done' => FALSE,
				'msg' => $verified
			);
		}
		
		// Enviar resposta
		$this->_ajax_response($response);
	}

	function send_email($to, $subject, $body)
	{
		$this->email->from('support@elementar.com', 'Elementar');

		$this->email->to($to);
		$this->email->subject($subject);
		$this->email->message($body);
		
		$this->email->send();
	}
	
	/*
	 * Link de confirmação de email
	 */
	function confirm_registration()
	{
		$hash = $this->uri->segment(3, 0);

		$confirm = $this->account->confirm_registration($hash);
		if ( $confirm === TRUE )
		{
			echo "<p>Cadastro ativado</p>";
		}
		else
		{
			echo "<p>Cadastro não localizado</p>";
		}
	}

	function _ajax_response($response)
	{
		// execution time
		$elapsed = array('elapsed_time' => $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
		$response = array_merge($response, $elapsed);
		
		$this->output->set_header('Cache-Control: no-cache, must-revalidate');
		$this->output->set_header('Content-type: application/json');
		
		echo json_encode($response);
	}

}


/* End of file user.php */
/* Location: ./application/controllers/user.php */
