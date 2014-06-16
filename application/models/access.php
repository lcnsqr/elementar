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

	function __construct()
	{
		parent::__construct();
	}

	function get_accounts($group_id = NULL)
	{
		$accounts = NULL;
		
		$this->elementar->select('account.id, account.username, account.email');
		$this->elementar->from('account');
		if ( (bool) $group_id )
		{
			$this->elementar->join('account_group', 'account_group.account_id = account.id', 'inner');
			$this->elementar->where('account_group.group_id', $group_id);
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
					'username' => $row->username,
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
		$this->elementar->where('id', intval($id));
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
		$this->elementar->where('id', intval($id));
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
		$this->elementar->where('id', intval($id));
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
		/*
		 * Remove group accounts
		 */
		$accounts = $this->get_accounts($group_id);
		$accounts = ( (bool) $accounts ) ? $accounts : array();
		foreach ($accounts as $account)
		{
			$this->delete_account($account['id']);
		}
		/*
		 * Remove group/account associations
		 */
		$this->elementar->delete('account_group', array('group_id' => $group_id)); 
		/*
		 * Remove account
		 */
		$this->elementar->delete('group', array('id' => $group_id));
	}

	/*
	 * Account group
	 */
	function get_account_group($account_id)
	{
		$this->elementar->select('group_id');
		$this->elementar->from('account_group');
		$this->elementar->where('account_id', $account_id);
		$query = $this->elementar->get();
		if ($query->num_rows > 0)
		{
			$row = $query->row();
			return $row->group_id;
		}
	}

	/*
	 * Get account username (login)
	 */
	function get_account_username($id)
	{
		$this->elementar->select('username');
		$this->elementar->from('account');
		$this->elementar->where('id', intval($id));
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->username;
		}
	}

	/*
	 * Get account id by username (login)
	 */
	function get_account_by_username($username)
	{
		if ( strlen(trim($username)) == 0 ) return 0;
		$this->elementar->select('id');
		$this->elementar->from('account');
		$this->elementar->where('username', $username);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
	}

	/*
	 * Get account password (hash)
	 */
	function get_account_password($id)
	{
		$this->elementar->select('password');
		$this->elementar->from('account');
		$this->elementar->where('id', intval($id));
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->password;
		}
	}

	/*
	 * Get account email
	 */
	function get_account_email($id)
	{
		$this->elementar->select('email');
		$this->elementar->from('account');
		$this->elementar->where('id', intval($id));
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->email;
		}
	}

	/*
	 * Get account id by email
	 */
	function get_account_by_email($email)
	{
		$this->elementar->select('id');
		$this->elementar->from('account');
		$this->elementar->where('email', $email);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
	}

	/*
	 * Get account id by register hash
	 */
	function get_account_by_register_hash($register_hash)
	{
		$this->elementar->select('id');
		$this->elementar->from('account');
		$this->elementar->where('register_hash', $register_hash);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
	}

	/*
	 * Get account id by reset hash (password reset)
	 */
	function get_account_by_reset_hash($reset_hash)
	{
		$this->elementar->select('id');
		$this->elementar->from('account');
		$this->elementar->where('reset_hash', $reset_hash);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
	}

	/*
	 * Write account
	 */
	function put_account($username, $email, $password, $hash = NULL)
	{
		$data = array(
			'username' => $username,
			'email' => $email,
			'password' => do_hash($password),
			'register_hash' => $hash,
			'created' => date("Y-m-d H:i:s")
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
	 * Write account username (login)
	 */
	function put_account_username($account_id, $username)
	{
		$data = array(
			'username' => $username
		);
		
		$this->elementar->where('id', $account_id);
		$this->elementar->update('account', $data); 
	}

	/*
	 * Write account email
	 */
	function put_account_email($account_id, $email)
	{
		$data = array(
			'email' => $email
		);
		
		$this->elementar->where('id', $account_id);
		$this->elementar->update('account', $data); 
	}

	/*
	 * Write account password
	 */
	function put_account_password($account_id, $password)
	{
		$data = array(
			'password' => do_hash($password)
		);
		
		$this->elementar->where('id', $account_id);
		$this->elementar->update('account', $data); 
	}

	/*
	 * Write account reset hash
	 */
	function put_account_reset_hash($account_id, $reset_hash)
	{
		$data = array(
			'reset_hash' => $reset_hash
		);
		
		$this->elementar->where('id', $account_id);
		$this->elementar->update('account', $data); 
	}

	/*
	 * Write group for account
	 */
	function put_account_group($account_id, $group_id)
	{
		/*
		 * Check if group is already associated
		 */
		$this->elementar->select('id');
		$this->elementar->from('account_group');
		$this->elementar->where('account_id', $account_id);
		$this->elementar->where('group_id', $group_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		
		/*
		 * Remove previous parent (for now, only one group per account)
		 */
		$this->elementar->where('account_id', $account_id);
		$this->elementar->delete('account_group');

		/*
		 * Attach account to group
		 */
		$data = array(
			'account_id' => $account_id,
			'group_id' => $group_id
		);
		$inserted = $this->elementar->insert('account_group', $data);
		if ($inserted)
		{
			return $this->elementar->insert_id();
		}
	}

	/* 
	 * Remove account
	 */
	function delete_account($account_id)
	{
		/*
		 * Remove group/account associations
		 */
		$this->elementar->delete('account_group', array('account_id' => $account_id)); 
		/*
		 * Remove account
		 */
		$this->elementar->delete('account', array('id' => $account_id));
	}

}
