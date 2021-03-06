<?php
/*
 *     storage.php
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
 * Create, read, update and delete in database
 */

class Storage extends CI_Model {
	
	/*
	 * Status defaults to “published” 
	 * to avoid listing draft contents
	 */
	var $STATUS = 'published';
	
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();

		/*
		* Correct boolean type for each DB driver
		*/
		switch ( $this->elementar->dbdriver )
		{
			case 'mysql':
			case 'mysqli':
			define('DB_TRUE', TRUE);
			define('DB_FALSE', FALSE);
			break;

			case 'postgre':
			define('DB_TRUE', 't');
			define('DB_FALSE', 'f');
			break;
		}
	}
	
	/*
	 * get site config
	 */
	function get_config($name)
	{
		$this->elementar->select('value');
		$this->elementar->from('config');
		$this->elementar->where('name', $name);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->value;
		}
		return NULL;
	}
	
	function put_config($name, $value)
	{
		$data = array(
			'name' => $name,
			'value' => $value
		);
		/*
		 * Check for existing entry first
		 */
		$this->elementar->select('id');
		$this->elementar->from('config');
		$this->elementar->where('name', $name);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->elementar->where('name', $name);
			$this->elementar->update('config', $data);
			return $row->id;
		}
		else
		{
			/*
			 * Insert new entry
			 */
			$inserted = $this->elementar->insert('config', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
		}
		return FALSE;
	}

	/*
	 * Write content template
	 */
	function put_template($template_id = NULL, $html, $css, $javascript, $head)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'html' => $html,
				'css' => $css,
				'javascript' => $javascript,
				'head' => $head
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'html' => $html,
				'css' => $css,
				'javascript' => $javascript,
				'head' => $head,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write html template
	 */
	function put_template_html($template_id = NULL, $html)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'html' => $html
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'html' => $html,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write template_id of a content
	 */
	function put_content_template_id($content_id, $template_id)
	{
		$data = array(
			'template_id' => $template_id
		);
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}

	/*
	 * Remove template
	 */
	function delete_template($template_id)
	{
		$this->elementar->delete('template', array('id' => $template_id)); 
	}

	/*
	 * Write content type
	 */
	function put_content_type($name, $template_id)
	{
		/*
		 * Verify existing name
		 */
		$this->elementar->select('id');
		$this->elementar->from('content_type');
		$this->elementar->where('name', $name);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			return FALSE;
		}
		else
		{
			$data = array(
				'name' => $name,
				'template_id' => $template_id
			);
			$inserted = $this->elementar->insert('content_type', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write content type field
	 */
	function put_content_type_field($type_id, $name, $sname, $field_type_id)
	{
		$data = array(
			'content_type_id' => $type_id,
			'name' => $name,
			'sname' => $sname,
			'field_type_id' => $field_type_id
		);
		$this->elementar->insert('content_type_field', $data);
	}

	/*
	 * Write element type
	 */
	function put_element_type($name, $sname)
	{
		/*
		 * Verify existing name
		 */
		$this->elementar->select('id');
		$this->elementar->from('element_type');
		$this->elementar->where('name', $name);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			return FALSE;
		}
		else
		{
			$data = array(
				'name' => $name,
				'sname' => $sname
			);
			$inserted = $this->elementar->insert('element_type', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write element type field
	 */
	function put_element_type_field($type_id, $name, $sname, $field_type_id)
	{
		$data = array(
			'element_type_id' => $type_id,
			'name' => $name,
			'sname' => $sname,
			'field_type_id' => $field_type_id
		);
		$this->elementar->insert('element_type_field', $data);
	}

	/*
	 * get content general details 
	 * return array
	 */
	function get_content($content_id)
	{
		$this->elementar->select('content.name as name, content.sname as sname, content_parent.parent_id as parent_id');
		$this->elementar->from('content');
		$this->elementar->join('content_parent', 'content_parent.content_id = content.id', 'inner');
		$this->elementar->where('content.id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('content.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$content = array(
				'name' => html_entity_decode($row->name, ENT_QUOTES, "UTF-8"),
				'sname' => $row->sname,
				'parent_id' => $row->parent_id
			);
			return $content;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content name (title)
	 */
	function get_content_name($content_id)
	{
		$this->elementar->select('name');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return html_entity_decode($row->name, ENT_QUOTES, "UTF-8");
		}
		else
		{
			return NULL;
		}
	}

	function get_content_parent_id($content_id)
	{
		$this->elementar->select('parent_id');
		$this->elementar->from('content_parent');
		$this->elementar->where('content_id', intval($content_id));
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->join('content', 'content.id = content_parent.parent_id', 'inner');
			$this->elementar->where('content.status', $this->STATUS);
		}
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->parent_id;
		}
	}
	
	/*
	 * get element general details 
	 * return array
	 */
	function get_element($element_id)
	{
		$this->elementar->select('element.name as name, element.sname as sname, element_parent.parent_id as parent_id');
		$this->elementar->from('element');
		$this->elementar->join('element_parent', 'element_parent.element_id = element.id', 'inner');
		$this->elementar->where('element.id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('element.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$element = array(
				'name' => html_entity_decode($row->name, ENT_QUOTES, "UTF-8"),
				'sname' => $row->sname,
				'parent_id' => $row->parent_id
			);
			return $element;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get element name (title)
	 */
	function get_element_name($element_id)
	{
		$this->elementar->select('name');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return html_entity_decode($row->name, ENT_QUOTES, "UTF-8");
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get element aname 
	 */
	function get_element_sname($element_id)
	{
		$this->elementar->select('sname');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->sname;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get element type sname
	 */
	function get_element_type_sname($id)
	{
		$this->elementar->select('sname');
		$this->elementar->from('element_type');
		$this->elementar->where('id', $id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->sname;
		}
		else
		{
			return NULL;
		}
	}
	
	/*
	 * get element type name
	 */
	function get_element_type_name($id)
	{
		$this->elementar->select('name');
		$this->elementar->from('element_type');
		$this->elementar->where('id', $id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->name;
		}
		else
		{
			return NULL;
		}
	}
	/*
	 * get content sname (slug)
	 */
	function get_content_sname($content_id)
	{
		$this->elementar->select('sname');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->sname;
		}
		else
		{
			return NULL;
		}
	}
	
	/*
	 * get content by sname (slug)
	 */
	function get_content_by_sname($sname)
	{
		$this->elementar->select('id');
		$this->elementar->from('content');
		$this->elementar->where('sname', $sname);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		else
		{
			return NULL;
		}
	}
	
	/*
	 * get content status
	 */
	function get_content_status($content_id)
	{
		$this->elementar->select('status');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->status;
		}
		else
		{
			return NULL;
		}
	}
		
	/*
	 * get element spread status
	 */
	function get_element_spread($element_id)
	{
		$this->elementar->select('spread');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->spread == DB_TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * get element status
	 */
	function get_element_status($element_id)
	{
		$this->elementar->select('status');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->status;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content author account
	 */
	function get_content_account_id($content_id)
	{
		$this->elementar->select('account_id');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->account_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get element author account
	 */
	function get_element_account_id($element_id)
	{
		$this->elementar->select('account_id');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->account_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get last element_id for element type
	 */
	function get_element_last_id($type_id)
	{
		$this->elementar->select('id');
		$this->elementar->from('element');
		$this->elementar->where('element_type_id', $type_id);
		$this->elementar->order_by('id', 'DESC');
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Write content
	 */
	function put_content($name, $sname, $content_type_id)
	{
		$data = array(
			'name' => $name,
			'sname' => $sname,
			'content_type_id' => $content_type_id,
			'created' => date("Y-m-d H:i:s")
		);
		$inserted = $this->elementar->insert('content', $data);
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
	 * Rename content
	 */
	function put_content_name($content_id, $name, $sname)
	{
		$data = array(
			'name' => $name,
			'sname' => $sname
		);
		
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}
		
	/*
	 * Write element
	 */
	function put_element($name, $sname, $element_type_id)
	{
		$data = array(
			'name' => $name,
			'sname' => $sname,
			'element_type_id' => $element_type_id,
			'created' => date("Y-m-d H:i:s")
		);
		$inserted = $this->elementar->insert('element', $data);
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
	 * Rename element
	 */
	function put_element_name($element_id, $name, $sname)
	{
		$data = array(
			'name' => $name,
			'sname' => $sname
		);
		
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}
		
	/*
	 * Write content status
	 */
	function put_content_status($content_id, $status)
	{
		$data = array(
			'status' => $status
		);
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}
		
	/*
	 * Write content author account id
	 */
	function put_content_account_id($content_id, $account_id)
	{
		$data = array(
			'account_id' => $account_id
		);
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}
		
	/*
	 * Write element author account id
	 */
	function put_element_account_id($element_id, $account_id)
	{
		$data = array(
			'account_id' => $account_id
		);
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}
		
	/*
	 * Write content modification time
	 */
	function put_content_modified($content_id, $modified = NULL)
	{
		if ( ! (bool) $modified )
		{
			$modified = date("Y-m-d H:i:s");
		}
		$data = array(
			'modified' => $modified
		);
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}

	/*
	 * write content field
	 */
	function put_content_field($content_id, $content_type_field_id, $value)
	{
		$this->elementar->select('id');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->where('content_type_field_id', $content_type_field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			/*
			 * Update
			 */
			$data = array(
				'value' => $value
			);
			$this->elementar->where('content_id', intval($content_id));
			$this->elementar->where('content_type_field_id', $content_type_field_id);
			$this->elementar->update('content_field', $data); 
			/*
			 * Update modified date in content table
			 */
			$this->put_content_modified($content_id);
			
			$row = $query->row();
			return $row->id;
		}
		else
		{
			/*
			 * Save
			 */
			$data = array(
				'content_id' => intval($content_id),
				'content_type_field_id' => $content_type_field_id,
				'value' => $value
			);
			$inserted = $this->elementar->insert('content_field', $data);
			if ($inserted)
			{
				/*
				 * Update modified date in content table
				 */
				$this->put_content_modified($content_id);

				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write element field
	 */
	function put_element_field($element_id, $element_type_field_id, $value)
	{
		$this->elementar->select('id');
		$this->elementar->from('element_field');
		$this->elementar->where('element_id', $element_id);
		$this->elementar->where('element_type_field_id', $element_type_field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			/*
			 * Update
			 */
			$data = array(
				'value' => $value
			);
			$this->elementar->where('element_id', $element_id);
			$this->elementar->where('element_type_field_id', $element_type_field_id);
			$this->elementar->update('element_field', $data); 
			/*
			 * Update modified date in element table
			 */
			$this->put_element_modified($element_id);

			$row = $query->row();
			return $row->id;
		}
		else
		{
			/*
			 * Save
			 */
			$data = array(
				'element_id' => $element_id,
				'element_type_field_id' => $element_type_field_id,
				'value' => $value
			);
			$inserted = $this->elementar->insert('element_field', $data);
			if ($inserted)
			{
				/*
				 * Update modified date in element table
				 */
				$this->put_element_modified($element_id);
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Write element status
	 */
	function put_element_status($element_id, $status)
	{
		$data = array(
			'status' => $status
		);
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}

	/*
	 * Write element modification time
	 */
	function put_element_modified($element_id, $modified = NULL)
	{
		if ( ! (bool) $modified )
		{
			$modified = date("Y-m-d H:i:s");
		}
		$data = array(
			'modified' => $modified
		);
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}

	/*
	 * Write element spread attribute
	 */
	function put_element_spread($element_id, $spread)
	{
		$data = array(
			'spread' => ( (bool) $spread ) ? DB_TRUE : DB_FALSE
		);
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}

	/*
	 * Read all content meta fields
	 */
	function get_meta_fields($content_id = 1)
	{
		$this->elementar->select('name, value');
		$this->elementar->from('html_meta');
		$this->elementar->where('content_id', intval($content_id));
		/*
		 * Priority used only in sitemap.xml
		 */
		$this->elementar->where('name !=', 'priority');
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Read content meta field
	 */
	function get_meta_field($content_id, $name)
	{
		$this->elementar->select('name, value');
		$this->elementar->from('html_meta');
		$this->elementar->where('name', $name);
		$this->elementar->limit(1);
		$this->elementar->where('content_id', intval($content_id));
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->value;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Delete content meta field
	 */
	function delete_meta_field($content_id, $name)
	{
		$this->elementar->delete('html_meta', array('content_id' => intval($content_id), 'name' => $name));
	}

	/*
	 * Write content meta field
	 */
	function put_meta_field($content_id, $name, $value)
	{
		if ( ! (bool) $value )
		{
			$this->delete_meta_field($content_id, $name);
			return;
		}
		$this->elementar->select('id');
		$this->elementar->from('html_meta');
		$this->elementar->where('name', $name);
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$data = array(
				'value' => $value
			);
			$this->elementar->where('id', $row->id);
			$this->elementar->update('html_meta', $data); 
			return $row->id;
		}
		else
		{
			$data = array(
				'content_id' => intval($content_id),
				'name' => $name,
				'value' => htmlentities($value, ENT_QUOTES, "UTF-8")
			);
			$inserted = $this->elementar->insert('html_meta', $data);
			if ($inserted)
			{
				$id = $this->elementar->insert_id();
				return $id;
			}
		}
	}

	/*
	 * Read content CSS
	 */
	function get_template_css($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('css');
		$this->elementar->from('template');
		$this->elementar->limit(1);
		$this->elementar->where('id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->css;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Write content CSS
	 */
	function put_template_css($template_id = NULL, $css)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'css' => $css
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'css' => $css,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Read content javascript
	 */
	function get_template_javascript($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('javascript');
		$this->elementar->from('template');
		$this->elementar->limit(1);
		$this->elementar->where('id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->javascript;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Write content javascript
	 */
	function put_template_javascript($template_id = NULL, $javascript)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'javascript' => $javascript
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'javascript' => $javascript,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Read content head 
	 */
	function get_template_head($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('head');
		$this->elementar->from('template');
		$this->elementar->limit(1);
		$this->elementar->where('id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->head;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Write content head
	 */
	function put_template_head($template_id = NULL, $head)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'head' => $head
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'head' => $head,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Read template filter 
	 */
	function get_template_filter($template_id)
	{
		$this->elementar->select('filter');
		$this->elementar->from('template');
		$this->elementar->limit(1);
		$this->elementar->where('id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->filter;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Write template filter
	 */
	function put_template_filter($template_id = NULL, $filter)
	{
		if ( (bool) $template_id )
		{
			/*
			 * Update
			 */
			$data = array(
				'filter' => $filter
			);
			$this->elementar->where('id', $template_id);
			$this->elementar->update('template', $data);
			return $template_id;
		}
		else
		{
			/*
			 * Insert
			 */
			$data = array(
				'filter' => $filter,
				'created' => date("Y-m-d H:i:s")
			);
			$inserted = $this->elementar->insert('template', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Read content HTML
	 */
	function get_template_html($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('html');
		$this->elementar->from('template');
		$this->elementar->limit(1);
		$this->elementar->where('id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->html;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Get all field of a content
	 */
	function get_content_fields($content_id)
	{
		$fields = array();
		$this->elementar->select('content.id as content_id, content_type_field.name as name, content_type_field.sname as sname, content_field.value as value, field_type.sname as type, field_type.i18n as i18n');
		$this->elementar->from('content');
		$this->elementar->join('content_field', 'content_field.content_id = content.id', 'inner');
		$this->elementar->join('content_type_field', 'content_type_field.id = content_field.content_type_field_id', 'inner');
		$this->elementar->join('field_type', 'field_type.id = content_type_field.field_type_id', 'inner');
		$this->elementar->where('content.id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('content.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'name' => $row->name,
				'sname' => $row->sname,
				'value' => html_entity_decode($row->value, ENT_QUOTES, "UTF-8"),
				'type' => $row->type,
				'i18n' => $row->i18n == DB_TRUE
			);
		}
		return $fields;
	}

	/*
	 * Get a content field
	 */
	function get_content_field($content_id, $content_type_field_id)
	{
		$this->elementar->select('value');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->where('content_type_field_id', $content_type_field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return html_entity_decode($row->value, ENT_QUOTES, "UTF-8");
		}
		else
		{
			return NULL;
		}

	}

	/*
	 * Get all fields of an element
	 */
	function get_element_fields($element_id)
	{
		$fields = array();
		$this->elementar->select('element.id as element_id, element_type_field.name as name, element_type_field.sname as sname, element_field.value as value, field_type.sname as type, field_type.i18n as i18n');
		$this->elementar->from('element');
		$this->elementar->join('element_field', 'element_field.element_id = element.id', 'inner');
		$this->elementar->join('element_type_field', 'element_type_field.id = element_field.element_type_field_id', 'inner');
		$this->elementar->join('field_type', 'field_type.id = element_type_field.field_type_id', 'inner');
		$this->elementar->where('element.id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('element.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'name' => $row->name,
				'sname' => $row->sname,
				'value' => html_entity_decode($row->value, ENT_QUOTES, "UTF-8"),
				'type' => $row->type,
				'i18n' => $row->i18n == DB_TRUE
			);
		}
		return $fields;
	}

	/*
	 * Get an element field
	 */
	function get_element_field($element_id, $element_type_field_id)
	{
		$this->elementar->select('value');
		$this->elementar->from('element_field');
		$this->elementar->where('element_id', $element_id);
		$this->elementar->where('element_type_field_id', $element_type_field_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->join('element', 'element.id = element_field.element_id', 'inner');
			$this->elementar->where('element.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return html_entity_decode($row->value, ENT_QUOTES, "UTF-8");
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Get anthe id of a content field
	 */
	function get_content_field_id($content_id, $content_type_field_id)
	{
		$this->elementar->select('id');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->where('content_type_field_id', $content_type_field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Get the details about a field type
	 * from the field itself
	 */
	function get_content_field_field_type($field_id)
	{
		$this->elementar->select('field_type.id, field_type.name, field_type.sname, field_type.description');
		$this->elementar->from('content_field');
		$this->elementar->join('content_type_field', 'content_type_field.id = content_field.content_type_field_id', 'inner');
		$this->elementar->join('field_type', 'field_type.id = content_type_field.field_type_id', 'inner');
		$this->elementar->where('content_field.id', $field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return array(
				'id' => $row->id,
				'name' => $row->name,
				'sname' => $row->sname,
				'description' => $row->description
			);
		}
	}

	/*
	 * Write content parent
	 */
	function put_content_parent($content_id, $parent_id)
	{
		/*
		 * Check if it is already associated
		 */
		$this->elementar->select('id');
		$this->elementar->from('content_parent');
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->where('parent_id', intval($parent_id));
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		
		/*
		 * Remove previous parent
		 */
		$this->elementar->where('content_id', intval($content_id));
		$this->elementar->delete('content_parent');

		/*
		 * Write association
		 */
		$data = array(
			'content_id' => intval($content_id),
			'parent_id' => intval($parent_id)
		);
		$inserted = $this->elementar->insert('content_parent', $data);
		if ($inserted)
		{
			return $this->elementar->insert_id();
		}
	}

	/*
	 * Write element parent
	 */
	function put_element_parent($element_id, $parent_id)
	{
		/*
		 * Check if is already associated
		 */
		$this->elementar->select('id');
		$this->elementar->from('element_parent');
		$this->elementar->where('element_id', $element_id);
		$this->elementar->where('parent_id', intval($parent_id));
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		
		/*
		 * Remove previous parent
		 */
		$this->elementar->where('element_id', $element_id);
		$this->elementar->delete('element_parent');

		/*
		 * Write association
		 */
		$data = array(
			'element_id' => $element_id,
			'parent_id' => intval($parent_id)
		);
		$inserted = $this->elementar->insert('element_parent', $data);
		if ($inserted)
		{
			return $this->elementar->insert_id();
		}
	}

	/*
	 * get content type
	 */
	function get_content_type_id($content_id)
	{
		$this->elementar->select('content_type_id');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->content_type_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get element type
	 */
	function get_element_type_id($id)
	{
		$this->elementar->select('element_type_id');
		$this->elementar->from('element');
		$this->elementar->where('id', $id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->element_type_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * list content types
	 */
	function get_content_types()
	{
		$types = array();
		$this->elementar->select('id, name');
		$this->elementar->from('content_type');
		$this->elementar->where('name !=', 'Home');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$types[$row->id] = $row->name;
		}
		return $types;
	}

	/*
	 * list element types
	 */
	function get_element_types()
	{
		$types = array();
		$this->elementar->select('id, name');
		$this->elementar->from('element_type');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$types[$row->id] = $row->name;
		}
		return $types;
	}

	/*
	 * get content type name
	 */
	function get_content_type_name($content_type_id)
	{
		$this->elementar->select('name');
		$this->elementar->from('content_type');
		$this->elementar->where('id', $content_type_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->name;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content type html template id
	 */
	function get_content_type_template_id($content_type_id)
	{
		$this->elementar->select('template_id');
		$this->elementar->from('content_type');
		$this->elementar->where('id', $content_type_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->template_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content type template
	 */
	function get_content_type_template($content_type_id)
	{
		$this->elementar->select('template.html, template.css, template.javascript, template.head');
		$this->elementar->from('template');
		$this->elementar->where('content_type.id', $content_type_id);
		$this->elementar->join('content_type', 'content_type.template_id = template.id', 'inner');
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$template = array(
				'html' => $row->html,
				'css' => $row->css,
				'javascript' => $row->javascript,
				'head' => $row->head
			);
			return $template;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content template id
	 */
	function get_content_template_id($content_id)
	{
		$this->elementar->select('template_id, content_type_id');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$template_id = $row->template_id;
			if ( (bool)$template_id )
			{
				return $template_id;
			}
			else
			{
				/*
				 * Content don't have a exclusive template,
				 * Get content_type template_id
				 */
				$content_type_id = $row->content_type_id;
				return $this->get_content_type_template_id($content_type_id);
			}
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get content template html, css, javascript 
	 */
	function get_template($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('filter, html, css, javascript, head');
		$this->elementar->from('template');
		$this->elementar->where('id', $template_id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$template = array(
				'filter' => $row->filter,
				'html' => $row->html,
				'css' => $row->css,
				'javascript' => $row->javascript,
				'head' => $row->head
			);
			return $template;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * list content type fields
	 */
	function get_content_type_fields($type_id)
	{
		$fields = array();
		$this->elementar->select('content_type_field.id, content_type_field.sname, content_type_field.name, content_type_field.field_type_id, field_type.sname as field_type_sname, field_type.i18n as field_type_i18n');
		$this->elementar->from('content_type_field');
		$this->elementar->join('field_type', 'field_type.id = content_type_field.field_type_id', 'inner');
		$this->elementar->where('content_type_field.content_type_id', $type_id);
		$this->elementar->order_by('content_type_field.id', 'asc');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'name' => $row->name,
				'sname' => $row->sname,
				'type' => $row->field_type_sname,
				'i18n' => $row->field_type_i18n == DB_TRUE
			);
		}
		return $fields;
	}

	/*
	 * list element type fields
	 */
	function get_element_type_fields($type_id)
	{
		$fields = array();
		$this->elementar->select('element_type_field.id, element_type_field.sname, element_type_field.name, element_type_field.field_type_id, field_type.sname as field_type_sname, field_type.i18n as field_type_i18n');
		$this->elementar->from('element_type_field');
		$this->elementar->join('field_type', 'field_type.id = element_type_field.field_type_id', 'inner');
		$this->elementar->where('element_type_field.element_type_id', $type_id);
		$this->elementar->order_by('element_type_field.id', 'asc');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'sname' => $row->sname,
				'name' => $row->name,
				'type' => $row->field_type_sname,
				'i18n' => $row->field_type_i18n == DB_TRUE
			);
		}
		return $fields;
	}

	/*
	 * list field types
	 */
	function get_field_types($except = NULL)
	{
		$fields = array();
		$this->elementar->select('id, sname');
		$this->elementar->from('field_type');
		if ( $except !== NULL )
			$this->elementar->where('sname !=', $except);
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'sname' => $row->sname
			);
		}
		return $fields;
	}

	/*
	 * get field type sname
	 */
	function get_field_type_sname($field_type_id)
	{
		$fields = array();
		$this->elementar->select('sname');
		$this->elementar->from('field_type');
		$this->elementar->where('id', $field_type_id);
		$query = $this->elementar->get();
		if ( $query->num_rows() > 0 )
		{
			$row = $query->row();
			$sname = $row->sname;
		}
		return $sname;
	}

	/*
	 * List contents by parent
	 */
	function get_contents_by_parent($content_id = 1)
	{
		$contents = array();
	
		$this->elementar->select('content.id, content.name, content.sname, content.created, content.modified');
		$this->elementar->from('content');
		$this->elementar->join('content_parent', 'content_parent.content_id = content.id', 'inner');
		$this->elementar->where('content_parent.parent_id', $content_id);
		$this->elementar->order_by("content.modified", "desc");
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('content.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$contents[] = array(
					'id' => $row->id, 
					'name' => html_entity_decode($row->name, ENT_QUOTES, "UTF-8"),
					'sname' => $row->sname,
					'created' => $row->created,
					'modified' => $row->modified,
					'children' => $this->get_content_has_children($row->id)
				);
			}
		}
		return $contents;
	}

	/*
	 * Get content by parent and sname
	 */
	function get_content_by_parent($parent_id, $sname)
	{
		$this->elementar->select('content.id, content.name, content.sname');
		$this->elementar->from('content');
		$this->elementar->join('content_parent', 'content_parent.content_id = content.id', 'inner');
		$this->elementar->where('content.sname', $sname);
		$this->elementar->where('content_parent.parent_id', $parent_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('content.status', $this->STATUS);
		}
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		}
	}

	/*
	 * List contents associated to a template
	 */
	function get_contents_by_template($template_id)
	{
		$contents = NULL;
		
		// Search for contents by its content type's template
		$this->elementar->select('content.id');
		$this->elementar->from('content');
		$this->elementar->join('content_type', 'content_type.id = content.content_type_id', 'inner');
		$this->elementar->where('content_type.template_id', $template_id);
		$this->elementar->where('content.template_id', NULL);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = array();
			foreach ($query->result() as $row)
			{
				$contents[] = array(
					'id' => $row->id
				);
			}
		}
		
		// Search for contents using an exclusive template
		$this->elementar->select('content.id');
		$this->elementar->from('content');
		$this->elementar->where('content.template_id', $template_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = ( is_array($contents ) ) ? $contents : array();
			foreach ($query->result() as $row)
			{
				$contents[] = array(
					'id' => $row->id
				);
			}
		}
		

		return $contents;
	}

	/*
	 * List element by parent
	 */
	function get_elements_by_parent($parent_id = 1)
	{
		$elements = NULL;

		$this->elementar->select('element.id, element_type.id as type_id, element_type.sname as type, element_type.name as type_name, element.name, element.sname, element.modified');
		$this->elementar->from('element');
		$this->elementar->join('element_parent', 'element_parent.element_id = element.id', 'inner');
		$this->elementar->join('element_type', 'element_type.id = element.element_type_id', 'inner');
		$this->elementar->where('element_parent.parent_id', $parent_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('element.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$elements = array();
			foreach ($query->result() as $row)
			{
				$elements[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'type_id' => $row->type_id,
					'type_name' => $row->type_name,
					'type' => $row->type
				);
			}
		}

		return $elements;
	}

	/*
	 * List all own and spreaded elements from upper parents
	 */
	function get_elements_by_parent_spreaded($content_id = 1, $elements = NULL)
	{
		/*
		 * Element associated/inherited to/from a content
		 */
		$this->elementar->select('element.id, element_type.id as type_id, element_type.sname as type, element_type.name as type_name, element.name, element.sname, element.created, element.modified, content_parent.parent_id');
		$this->elementar->from('element');
		$this->elementar->join('element_parent', 'element_parent.element_id = element.id', 'inner');
		$this->elementar->join('element_type', 'element_type.id = element.element_type_id', 'inner');
		$this->elementar->join('content_parent', 'content_parent.content_id = element_parent.parent_id', 'left');
		$this->elementar->where('element_parent.parent_id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('element.status', $this->STATUS);
		}
		if ( $elements !== NULL )
		{
			/*
			 * It's a second run. We're on a parent now,
			 * so ignore non spreaded elements
			 */
			$this->elementar->where('element.spread', DB_TRUE);
		}
		else
		{
			/*
			 * First run, no upper level elements to join
			 */
			$elements = array();
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$elements[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'created' => $row->created,
					'modified' => $row->modified,
					'type_id' => $row->type_id,
					'type_name' => $row->type_name,
					'type' => $row->type
				);
				$parent_id = (int) $row->parent_id;
			}
		}
		else
		{
			/*
			 * With no elements in here, just get
			 * the parent_id for this content
			 */
			$parent_id = $this->get_content_parent_id($content_id);
		}
		if ( $content_id != 1 ) // if not the home page
		{
			/*
			 * Pull upper parents elements
			 * with a new parent_id
			 */
			$elements = $this->get_elements_by_parent_spreaded($parent_id, $elements);
		}

		return $elements;
	}

	/*
	 * Element parent id
	 */
	function get_element_parent_id($element_id)
	{
		$this->elementar->select('parent_id');
		$this->elementar->from('element_parent');
		$this->elementar->where('element_id', $element_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->join('content', 'content.id = element_parent.parent_id', 'inner');
			$this->elementar->where('content.status', $this->STATUS);
		}
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->parent_id;
		}
	}

	/*
	 * Verify if content has any elements/contents associated to it
	 */
	function get_content_has_children($content_id, $elements = TRUE)
	{
		if ( $elements )
		{
			/*
			 * Check for elements too
			 */
			$this->elementar->select('*');
			$this->elementar->from('element_parent');
			$this->elementar->where('element_parent.parent_id', $content_id);
			if ( $this->STATUS != 'all' )
			{
				$this->elementar->join('element', 'element.id = element_parent.element_id', 'inner');
				$this->elementar->where('element.status', $this->STATUS);
			}
			$query = $this->elementar->get();
			if ($query->num_rows() > 0) 
			{
				return TRUE;
			}
		}

		$this->elementar->select('*');
		$this->elementar->from('content_parent');
		$this->elementar->where('content_parent.parent_id', $content_id);
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->join('content', 'content.id = content_parent.content_id', 'inner');
			$this->elementar->where('content.status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * Display all contents
	 */
	function get_contents()
	{

		$contents = array();

		$this->elementar->select('id, name, sname, modified, created');
		$this->elementar->from('content');
		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = $query->result_array();
		}
		return $contents;
	}

	/*
	 * All descendants contents
	 */
	function get_content_descendants($content_id)
	{
		$contents = array();
		if ( $this->get_content_has_children($content_id, FALSE) )
		{
			$contents = $this->get_contents_by_parent($content_id);
			foreach ( $contents as $content )
			{
				$contents = array_merge($contents, $this->get_content_descendants($content['id']));
			}
		}
		return $contents;
	}

	/*
	 * URI constructor for content
	 */
	function get_content_uri($content_id)
	{
		/*
		 * Return "/" to index content id 1
		 */
		if ( intval($content_id) == 1 )
		{
			return '/';
		}

		$this->elementar->select('content.id, content.name, content.sname');
		$this->elementar->from('content');
		$this->elementar->where('content.id', $content_id);
		$this->elementar->limit(1);

		if ( $this->STATUS != 'all' )
		{
			$this->elementar->where('status', $this->STATUS);
		}
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$uri = "/" . $row->sname;
			
			/*
			 * Check for parent
			 */
			$this->elementar->select('parent_id');
			$this->elementar->from('content_parent');
			$this->elementar->where('content_id', intval($content_id));
			$this->elementar->where('parent_id >', 1);
			$this->elementar->limit(1);
			$query = $this->elementar->get();
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$uri = $this->get_content_uri($row->parent_id) . $uri;
			}
			return $uri;
		}
	}
	
	/*
	 * Create/update file upload session
	 */
	function put_upload_session($upload_session_id = NULL, $field = NULL, $value = NULL)
	{
		if ( $upload_session_id == NULL )
		{
			$data = array(
				'name' => ''
			);
			$inserted = $this->elementar->insert('upload_session', $data);
			if ($inserted)
			{
				return $this->elementar->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			/*
			 * boolean fields
			 */
			if ( $field == 'done' || $field == 'error' )
			{
				$value = ( (bool) $value == TRUE ) ? DB_TRUE : DB_FALSE;
			}
			$data = array(
				$field => $value
			);
			$this->elementar->where('id', $upload_session_id);
			$this->elementar->update('upload_session', $data); 
		}
	}

	/*
	 * Check if file upload session is done
	 */
	function get_upload_session_done($upload_session_id)
	{
		$this->elementar->select('done');
		$this->elementar->from('upload_session');
		$this->elementar->where('id', $upload_session_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->done == DB_TRUE;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Get path for the uploaded file
	 */
	function get_upload_session_uri($upload_session_id)
	{
		$this->elementar->select('uri');
		$this->elementar->from('upload_session');
		$this->elementar->where('id', $upload_session_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->uri;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Get uploaded file name
	 */
	function get_upload_session_name($upload_session_id)
	{
		$this->elementar->select('name');
		$this->elementar->from('upload_session');
		$this->elementar->where('id', $upload_session_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->name;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Remove content
	 */
	function delete_content($content_id)
	{
		if ( $this->get_content_has_children($content_id) ) 
		{
			/*
			 * Delete associated elements
			 */
			$this->elementar->select('element_id');
			$this->elementar->from('element_parent');
			$this->elementar->where('parent_id', $content_id);
			$query = $this->elementar->get();
			foreach ($query->result() as $row)
			{
				$this->delete_element($row->element_id);
			}
			/*
			 * Delete associated contents
			 */
			$this->elementar->select('content_id');
			$this->elementar->from('content_parent');
			$this->elementar->where('parent_id', $content_id);
			$query = $this->elementar->get();
			foreach ($query->result() as $row)
			{
				$this->delete_content($row->content_id);
			}
		}
		
		$this->elementar->delete('content_parent', array('content_id' => intval($content_id))); 
		$this->elementar->delete('content_field', array('content_id' => intval($content_id))); 
		$this->elementar->delete('content', array('id' => intval($content_id)));
		
		/*
		 * Delete associated meta fields
		 */
		$this->elementar->delete('html_meta', array('content_id' => intval($content_id)));
	}
	
	/*
	 * Remove element
	 */
	function delete_element($element_id)
	{
		$this->elementar->delete('element_parent', array('element_id' => $element_id)); 
		$this->elementar->delete('element_field', array('element_id' => $element_id)); 
		$this->elementar->delete('element', array('id' => $element_id));
	}

}
