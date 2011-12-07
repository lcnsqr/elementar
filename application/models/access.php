<?php
/*
 *     access.php
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


/*
 * Account and session related functions
 */

class Access extends CI_Model {
	
	/*
	 * Account status defaults to “all” 
	 */
	var $STATUS = 'all';

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



	/*
	 * new code
	 */



	function get_accounts($group_id = NULL)
	{
		$accounts = NULL;
		
		$this->elementar->select('account.id, account.user, account.email');
		$this->elementar->from('account');
		if ( (bool) $group_id )
		{
			$this->elementar->join('account_group', 'account_group.account_id = account.id', 'inner');
			$this->elementar->where('account_group.group_id', $group_id);
		}
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('account.status', $this->STATUS);
		}
		$this->elementar->order_by('account.created', 'desc');
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$accounts = array();
			foreach ($query->result() as $row)
			{
				$accounts[] = array(
					'id' => $row->id, 
					'user' => $row->user,
					'email' => $row->email
				);
			}
		}

		return $accounts;
	}

	/*
	 * List all groups
	 */
	function get_groups()
	{
		$groups = NULL;
		$this->elementar->select('group.id, group.name, group.description');
		$this->elementar->from('group');
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$groups[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'description' => $row->description,
					'children' => $this->get_group_has_account($row->id)
				);
			}
		}
		return $groups;
	}

	/*
	 * Get group info
	 */
	function get_group($id)
	{
		$group = NULL;
		$this->elementar->select('id, name, description');
		$this->elementar->from('group');
		$this->elementar->where('id', $id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$group = array(
				'id' => $row->id, 
				'name' => $row->name,
				'description' => $row->description,
				'children' => $this->get_group_has_account($row->id)
			);
		}
		return $group;
	}

	/*
	 * Get group name
	 */
	function get_group_name($id)
	{
		$this->elementar->select('name');
		$this->elementar->from('group');
		$this->elementar->where('id', $id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->name;
		}
	}

	/*
	 * Get group description
	 */
	function get_group_description($id)
	{
		$this->elementar->select('description');
		$this->elementar->from('group');
		$this->elementar->where('id', $id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->description;
		}
	}

	/*
	 * Check if group has any accounts
	 */
	function get_group_has_account($group_id)
	{
		$this->elementar->select('id');
		$this->elementar->from('account_group');
		$this->elementar->where('group_id', $group_id);
		$query = $this->elementar->get();
		if ($query->num_rows > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * Write group
	 */
	function put_group($name, $description)
	{
		$data = array(
			'name' => $name,
			'description' => $description
		);
		$inserted = $this->elementar->insert('group', $data);
		if ($inserted)
		{
			return $this->elementar->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Rename group
	 */
	function put_group_name($group_id, $name)
	{
		$data = array(
			'name' => $name
		);
		
		$this->elementar->where('id', $group_id);
		$this->elementar->update('group', $data); 
	}

	/*
	 * Write group description
	 */
	function put_group_description($group_id, $description)
	{
		$data = array(
			'description' => $description
		);
		
		$this->elementar->where('id', $group_id);
		$this->elementar->update('group', $data); 
	}

	/* 
	 * Remove group
	 */
	function delete_group($group_id)
	{
		$this->elementar->delete('account_group', array('group_id' => $group_id)); 
		$this->elementar->delete('group', array('id' => $group_id));
	}

}
