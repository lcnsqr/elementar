<?php 

class Account extends CI_Model {
	
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	/*
	 * Verificar se usuário é válido
	 * e se não está cadastrado
	 */
	function validate_username($username)
	{
		if ( strlen(trim($username)) == 0 ) 
		{
			return "Informe o nome de usuário";
		}
	
		if ( preg_match("/[^a-z0-9_]/i", $username) ) 
		{
			return "O nome de usuário pode conter apenas letras (sem acentos), números e traço baixo";
		}		
		else 
		{
			/* 
			 * verifica se username já foi utilizado 
			 */
			$query = $this->elementar->get_where('account', array('user' => $username), '1');
			if ($query->num_rows() > 0 )
			{
				return "Este nome de usuário já foi cadastrado";
			}
			else 
			{
				return TRUE;
			}
		}
	}

	/*
	 * remover usuário
	 */
	function remove_user($user_id)
	{
		/* 
		 * Remover solicitado
		 */
		$this->elementar->delete('account', array('id' => $user_id));
	}
	
	/*
	 * Registrar usuário
	 */
	function register_user($user, $email, $password, $hash, $enabled = FALSE)
	{
		/* 
		 * Inserir usuário
		 */
		$data = array(
			'user' => $user,
			'email' => $email,
			'password' => do_hash($password),
			'register_hash' => $hash,
			'created' => date("Y-m-d H:i:s"),
			'enabled' => $enabled
		);
		$query = $this->elementar->insert('account', $data);
		if ($query)
		{
			return $this->elementar->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Confirmar registro
	 */
	function confirm_registration($hash)
	{
		/*
		 * Verificar existência do hash
		 */
		$query = $this->elementar->get_where('account', array('register_hash' => $hash), '1');
		if ($query->num_rows() > 0 )
		{
			/* 
			 * Confirmar registro pelo hash
			 */
			$data = array(
				'enabled' => TRUE
			);
			$query = $this->elementar->where('register_hash', $hash);
			$query = $this->elementar->update('account', $data);
			return TRUE;
		}

		return "Este cadastro não foi localizado";
	}

	/*
	 * write reset passord hash
	 */
	function reset_password_hash($email, $hash)
	{
		/*
		 * localizar email no cadastro
		 */
		$query = $this->elementar->get_where('account', array('email' => $email), '1');
		if ($query->num_rows() > 0 )
		{
			$data = array(
				'reset_hash' => $hash,
				'reset_hash_date' => date("Y-m-d H:i:s")
			);
			$query = $this->elementar->where('email', $email);
			$query = $this->elementar->update('account', $data);
			return TRUE;
		}
		else
		{
			return "O email informado não foi localizado";
		}
	}

	/*
	 * verify reset passord hash
	 */
	function verify_reset_password_hash($hash)
	{
		/*
		 * localizar reset hash
		 */
		$this->elementar->select('reset_hash_date');
		$this->elementar->from('account');
		$this->elementar->where('reset_hash', $hash);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			/*
			 * Verificar validade do hash
			 */
			$diff = time() - strtotime($row->reset_hash_date);
			
			if ( $diff <= 86400 )
			{
				/*
				 * hash válido, liberar alteração de senha
				 */
				return TRUE;
			}
			else
			{
				return "O código informado não é mais válido";
			}
		}
		else
		{
			return "O código informado não é válido";
		}
	}

	/*
	 * change reset passord by hash
	 */
	function change_reset_password($hash, $password)
	{
		/*
		 * Verificar hash novamente
		 */
		$verify_hash = $this->verify_reset_password_hash($hash);
		if ( $verify_hash === TRUE )
		{
			/*
			 * Verificar password
			 */
			$verify_password = $this->validate_password($password);
			if ( $verify_password === TRUE )
			{
				$data = array(
					'reset_hash' => NULL,
					'password' => do_hash($password)
				);
				$query = $this->elementar->where('reset_hash', $hash);
				$query = $this->elementar->update('account', $data);
				return TRUE;
			}
			else
			{
				return $verify_password;
			}
		}
		else
		{
			return $verify_hash;
		}
	}

	/*
	 * Verificar se email é válido
	 * e se não está cadastrado
	 */
	function validate_email($email)
	{
		if ( strlen(trim($email)) == 0 ) 
		{
			return "Informe o email";
		}
	
		if (preg_match("/^[a-z0-9]+([_\.%!][_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email)) 
		{
			list($login, $host) = explode("@", $email);
			if ( ! checkdnsrr($host, "MX") ) 
			{
					return "O email informado não é válido";
			}
			else 
			{
				/* 
				 * verifica se email já foi utilizado 
				 */
				$query = $this->elementar->get_where('account', array('email' => $email), '1');
				if ($query->num_rows() > 0 )
				{
					return "Este email já foi cadastrado";
				}
				else 
				{
					return TRUE;
				}
			}
		}
		else 
		{
			return "O email informado está incorreto";
		}		
	}
	
	/*
	 * Verificar se senha é válida
	 */
	function validate_password($password)
	{
		if ( strlen(trim($password)) == 0 ) 
		{
			return "Informe a senha";
		}
		return TRUE;
	}
	
	/*
	 * Registrar sessão
	 */
	function register_session($user_id, $session_id) 
	{
		/* 
		 * Registrar sessão do usuário
		 */
		$data = array(
			'user_id' => $user_id
		);
		$query = $this->elementar->where('hash', $session_id);
		$query = $this->elementar->update('acc_session', $data);
	}

	/*
	 * Desregistrar sessão
	 */
	function unregister_session($session_id) 
	{
		$data = array(
			'user_id' => NULL
		);
		$query = $this->elementar->where('hash', $session_id);
		$query = $this->elementar->update('acc_session', $data);
	}
	
	/*
	 * Desregistrar sessão
	 */
	function unregister_session_expired() 
	{
		$limit = time() - $this->sess->EXPIRATION;
		$data = array(
			'user_id' => NULL
		);
		$query = $this->elementar->where('created <', date("Y-m-d H:i:s", $limit));
		$query = $this->elementar->update('acc_session', $data);
	}
	
	/*
	 * Verificar sessão
	 */
	function logged($session_id)
	{
		$this->elementar->select('user_id, created');
		$this->elementar->from('acc_session');
		$this->elementar->where('hash', $session_id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			/*
			 * Verificar presença de usuário logado na sessão
			 */
			$row = $query->row();
			if ( $row->user_id !== NULL )
			{
				/*
				 * Excluir sessões expiradas
				 */
				$this->unregister_session_expired();

				/*
				 * Excluir possíveis sessões expiradas para o usuário
				 */
				$limit = time() - $this->sess->EXPIRATION;
				if ( strtotime($row->created) < $limit )
				{
					$this->unregister_session($session_id);
					return FALSE;
				}
				else
				{
					return TRUE;
				}
			}
			else 
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Verificar senha
	 */
	function authenticate($user_id, $password)
	{
		$query = $this->elementar->get_where('account', array('password' => do_hash($password)), '1');
		if ($query->num_rows() > 0 )
		{
			return TRUE;
		}
		return FALSE;
	}

	/*
	 * Verificar nome de usuário
	 * e retornar id
	 */
	function get_user_id($username)
	{
		$this->elementar->select('id');
		$this->elementar->from('account');
		$this->elementar->where('user', $username);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		else
		{
			return FALSE;
		}
	}
			
	/*
	 * Verificar id
	 * e retornar username
	 */
	function get_user_name($id)
	{
		$this->elementar->select('user');
		$this->elementar->from('account');
		$this->elementar->where('id', $id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->user;
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Verificar id
	 * e retornar email
	 */
	function get_user_email($id)
	{
		$this->elementar->select('email');
		$this->elementar->from('account');
		$this->elementar->where('id', $id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->email;
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * list all users
	 */
	function get_users($id = NULL)
	{
		$contents = array();
		$this->elementar->select('id, user, email, created');
		$this->elementar->from('account');
		if ($id !== NULL)
		{
			$this->elementar->where('id', $id);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = $query->result_array();
		}
		return $contents;
	}

}
