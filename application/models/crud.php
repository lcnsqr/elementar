<?php
/*
 *     crud.php
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
 * Create, read, update and delete contents & elements
 */

class Crud extends CI_Model {
	
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	/*
	 * Write html template
	 */
	function put_template($html, $template_id = NULL)
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
	 * Gravar template_id do conteúdo
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
	 * Remover template
	 */
	function delete_template($template_id)
	{
		$this->elementar->delete('template', array('id' => $template_id)); 
	}

	/*
	 * Gravar content type
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
	 * Gravar content type field
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
	 * Gravar element type
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
	 * Gravar element type field
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
		$this->elementar->where('content_id', $content_id);
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
	 * get element type sname
	 */
	function get_element_type_sname($element_id)
	{
		$this->elementar->select('sname');
		$this->elementar->from('element_type');
		$this->elementar->where('id', $element_id);
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
	function get_element_type_name($element_id)
	{
		$this->elementar->select('name');
		$this->elementar->from('element_type');
		$this->elementar->where('id', $element_id);
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
	function get_content_sname($element_id)
	{
		$this->elementar->select('sname');
		$this->elementar->from('content');
		$this->elementar->where('id', $element_id);
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
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->spread;
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
	 * Gravar conteúdo
	 */
	function put_content($name, $sname, $content_type_id)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
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
	 * Renomear conteúdo
	 */
	function put_content_name($content_id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->elementar->where('id', $content_id);
		$this->elementar->update('content', $data); 
	}
		
	/*
	 * Gravar elemento
	 */
	function put_element($name, $sname, $element_type_id)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
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
	 * Renomear elemento
	 */
	function put_element_name($element_id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->elementar->where('id', $element_id);
		$this->elementar->update('element', $data); 
	}
		
	/*
	 * Gravar status do conteúdo
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
	 * Gravar data de modificaçào do conteúdo
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
	 * Gravar campo de conteúdo
	 */
	function put_content_field($content_id, $content_type_field_id, $value)
	{
		$this->elementar->select('id');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', $content_id);
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
			$this->elementar->where('content_id', $content_id);
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
				'content_id' => $content_id,
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
	 * Gravar campo de elemento
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
	 * Gravar status do elemento
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
	 * Gravar data de modificação do elemento
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
	 * Gravar spread do elemento
	 */
	function put_element_spread($element_id, $spread)
	{
		$data = array(
			'spread' => $spread
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
		$this->elementar->where('content_id', $content_id);
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
		$this->elementar->where('content_id', $content_id);
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
		$this->elementar->delete('html_meta', array('content_id' => $content_id, 'name' => $name));
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
		$this->elementar->where('content_id', $content_id);
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
				'content_id' => $content_id,
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
	 * Pegar todos campos de um conteúdo
	 */
	function get_content_fields($content_id)
	{
		$fields = array();
		$this->elementar->select('content.id as content_id, content_type_field.name as name, content_type_field.sname as sname, content_field.value as value, field_type.sname as type, field_type.description as description');
		$this->elementar->from('content');
		$this->elementar->join('content_field', 'content_field.content_id = content.id', 'inner');
		$this->elementar->join('content_type_field', 'content_type_field.id = content_field.content_type_field_id', 'inner');
		$this->elementar->join('field_type', 'field_type.id = content_type_field.field_type_id', 'inner');
		$this->elementar->where('content.id', $content_id);
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'name' => $row->name,
				'sname' => $row->sname,
				'value' => html_entity_decode($row->value, ENT_QUOTES, "UTF-8"),
				'type' => $row->type,
				'description' => $row->description
			);
		}
		return $fields;
	}

	/*
	 * Pegar campo de conteúdo
	 */
	function get_content_field($content_id, $content_type_field_id)
	{
		$this->elementar->select('value');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', $content_id);
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
	 * Pegar todos campos de um elementos
	 */
	function get_element_fields($element_id)
	{
		$fields = array();
		$this->elementar->select('element.id as element_id, element_type_field.name as name, element_type_field.sname as sname, element_field.value as value, field_type.sname as type, field_type.description as description');
		$this->elementar->from('element');
		$this->elementar->join('element_field', 'element_field.element_id = element.id', 'inner');
		$this->elementar->join('element_type_field', 'element_type_field.id = element_field.element_type_field_id', 'inner');
		$this->elementar->join('field_type', 'field_type.id = element_type_field.field_type_id', 'inner');
		$this->elementar->where('element.id', $element_id);
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'name' => $row->name,
				'sname' => $row->sname,
				'value' => html_entity_decode($row->value, ENT_QUOTES, "UTF-8"),
				'type' => $row->type,
				'description' => $row->description
			);
		}
		return $fields;
	}

	/*
	 * Pegar campo de elemento
	 */
	function get_element_field($element_id, $element_type_field_id)
	{
		$this->elementar->select('value');
		$this->elementar->from('element_field');
		$this->elementar->where('element_id', $element_id);
		$this->elementar->where('element_type_field_id', $element_type_field_id);
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
	 * Pegar id do campo de conteúdo
	 */
	function get_content_field_id($content_id, $content_type_field_id)
	{
		$this->elementar->select('id');
		$this->elementar->from('content_field');
		$this->elementar->where('content_id', $content_id);
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
	 * Pegar detalhes do tipo do campo
	 * a partir do próprio campo
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
	 * Get image properties
	 * return array
	 */
	function get_image($field_id)
	{
		$this->elementar->select('uri, uri_thumb, alt, width, height');
		$this->elementar->from('image');
		$this->elementar->where('id', $field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			return $query->row_array();
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Pegar uri de imagem
	 */
	function get_image_uri($field_id)
	{
		$this->elementar->select('uri');
		$this->elementar->from('image');
		$this->elementar->where('id', $field_id);
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
	 * Pegar thumb de imagem
	 */
	function get_image_uri_thumb($field_id)
	{
		$this->elementar->select('uri_thumb');
		$this->elementar->from('image');
		$this->elementar->where('id', $field_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->uri_thumb;
		}
		else
		{
			return NULL;
		}

	}

	/*
	 * Gravar pai de conteúdo
	 */
	function put_content_parent($content_id, $parent_id)
	{
		/*
		 * Verificar se conteúdo já está
		 * associado ao pai em qestão
		 */
		$this->elementar->select('id');
		$this->elementar->from('content_parent');
		$this->elementar->where('content_id', $content_id);
		$this->elementar->where('parent_id', $parent_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		
		/*
		 * Criar relacionamento
		 */
		$data = array(
			'content_id' => $content_id,
			'parent_id' => $parent_id
		);
		$inserted = $this->elementar->insert('content_parent', $data);
		if ($inserted)
		{
			return $this->elementar->insert_id();
		}
	}

	/*
	 * Gravar pai de elemento
	 */
	function put_element_parent($element_id, $parent_id)
	{
		/*
		 * Verificar se elemento já está
		 * associado a pai em qestão
		 */
		$this->elementar->select('id');
		$this->elementar->from('element_parent');
		$this->elementar->where('element_id', $element_id);
		$this->elementar->where('parent_id', $parent_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
		
		/*
		 * Criar relacionamento
		 */
		$data = array(
			'element_id' => $element_id,
			'parent_id' => $parent_id
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
	function get_element_type_id($element_id)
	{
		$this->elementar->select('element_type_id');
		$this->elementar->from('element');
		$this->elementar->where('id', $element_id);
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
	 * get content type html template
	 */
	function get_content_type_template($content_type_id)
	{
		$this->elementar->select('template.html');
		$this->elementar->from('template');
		$this->elementar->where('content_type.id', $content_type_id);
		$this->elementar->join('content_type', 'content_type.template_id = template.id', 'inner');
		$this->elementar->limit(1);
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
	 * get content html template id
	 */
	function get_content_template_id($content_id)
	{
		$this->elementar->select('template_id, content_type_id');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
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
	 * get content html template 
	 */
	function get_content_template($content_id)
	{
		$template_id = $this->get_content_template_id($content_id);
		$this->elementar->select('html');
		$this->elementar->from('template');
		$this->elementar->where('id', $template_id);
		$this->elementar->limit(1);
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
	 * list content type fields
	 */
	function get_content_type_fields($type_id)
	{
		$fields = array();
		$this->elementar->select('id, name, sname, field_type_id');
		$this->elementar->from('content_type_field');
		$this->elementar->where('content_type_id', $type_id);
		$this->elementar->order_by('id', 'asc');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'name' => $row->name,
				'sname' => $row->sname,
				'type' => $this->get_field_type_sname($row->field_type_id),
				'description' => $this->get_field_type_description($row->field_type_id)
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
		$this->elementar->select('element_type_field.id, element_type_field.name, element_type_field.sname, element_type_field.field_type_id, field_type.sname as field_type_sname, field_type.description as field_type_description');
		$this->elementar->from('element_type_field');
		$this->elementar->join('field_type', 'field_type.id = element_type_field.field_type_id', 'inner');
		$this->elementar->where('element_type_field.element_type_id', $type_id);
		$this->elementar->order_by('element_type_field.id', 'asc');
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'name' => $row->name,
				'sname' => $row->sname,
				'type' => $row->field_type_sname,
				'description' => $row->field_type_description
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
		$this->elementar->select('id, name, sname, description');
		$this->elementar->from('field_type');
		if ( $except !== NULL )
			$this->elementar->where('sname !=', $except);
		$query = $this->elementar->get();
		foreach ($query->result() as $row)
		{
			$fields[] = array(
				'id' => $row->id,
				'name' => $row->name,
				'sname' => $row->sname,
				'description' => $row->description
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
	 * get field type description
	 */
	function get_field_type_description($field_type_id)
	{
		$fields = array();
		$this->elementar->select('description');
		$this->elementar->from('field_type');
		$this->elementar->where('id', $field_type_id);
		$query = $this->elementar->get();
		if ( $query->num_rows() > 0 )
		{
			$row = $query->row();
			$sname = $row->description;
		}
		return $sname;
	}

	/*
	 * List contents by parent
	 */
	function get_contents_by_parent($parent_id = 1)
	{
		$contents = NULL;
		
		$this->elementar->select('content.id, content.name, content.sname');
		$this->elementar->from('content');
		$this->elementar->join('content_parent', 'content_parent.content_id = content.id', 'inner');
		$this->elementar->where('content_parent.parent_id', $parent_id);
		$this->elementar->order_by('content.created', 'desc');
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = array();
			foreach ($query->result() as $row)
			{
				$contents[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
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
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		}
	}

	/*
	 * List element by parent
	 */
	function get_elements_by_parent($parent_id = 1)
	{
		$elements = NULL;

		$this->elementar->select('element.id, element.name, element.sname, element.modified');
		$this->elementar->from('element');
		$this->elementar->join('element_parent', 'element_parent.element_id = element.id', 'inner');
		$this->elementar->where('element_parent.parent_id', $parent_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$elements = array();
			foreach ($query->result() as $row)
			{
				$elements[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname
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
		 * Elemento associado/herdado a conteúdo
		 */
		$this->elementar->select('element.id, element_type.id as type_id, element_type.sname as type, element.name, element.sname, content_parent.parent_id');
		$this->elementar->from('element');
		$this->elementar->join('element_parent', 'element_parent.element_id = element.id', 'inner');
		$this->elementar->join('element_type', 'element_type.id = element.element_type_id', 'inner');
		$this->elementar->join('content_parent', 'content_parent.content_id = element_parent.parent_id', 'left');
		$this->elementar->where('element_parent.parent_id', $content_id);
		if ( $elements !== NULL )
		{
			/*
			 * It's a second run. We're on a upper parent now,
			 * so ignore non spreaded elements
			 */
			$this->elementar->where('element.spread', TRUE);
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
					'type_id' => $row->type_id,
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
		$this->elementar->select('*');
		$this->elementar->from('content_parent');
		$this->elementar->where('content_parent.parent_id', $content_id);
		if ( $elements )
		{
			/*
			 * Check for elements too
			 */
			$this->elementar->from('element_parent');
			$this->elementar->or_where('element_parent.parent_id', $content_id);
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

		$this->elementar->select('id, name, sname, modified');
		$this->elementar->from('content');
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$contents = $query->result_array();
		}
		return $contents;
	}
	
	/*
	 * Children contents
	 */
	function _get_content_children($content_id)
	{
		$contents = array();
	
		$this->elementar->select('content.id, content.name, content.sname');
		$this->elementar->from('content');
		$this->elementar->join('content_parent', 'content_parent.content_id = content.id', 'inner');
		$this->elementar->where('content_parent.parent_id', $content_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$contents[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'children' => $this->_get_content_children($row->id)
				);
			}
			return $contents;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * URI constructor for content
	 */
	function get_content_uri($content_id)
	{
		$this->elementar->select('id, name, sname');
		$this->elementar->from('content');
		$this->elementar->where('id', $content_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$uri = "/" . $row->sname;
			
			/*
			 * Checar por pai 
			 */
			$this->elementar->select('parent_id');
			$this->elementar->from('content_parent');
			$this->elementar->where('content_id', $content_id);
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
	 * Criar/Atualizar sessão de upload de imagem
	 */
	function put_upload_session($upload_session_id = NULL, $field = NULL, $value = NULL)
	{
		if ( $upload_session_id == NULL )
		{
			$data = array(
				'id' => $upload_session_id
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
			$data = array(
				$field => $value
			);
			$this->elementar->where('id', $upload_session_id);
			$this->elementar->update('upload_session', $data); 
		}
	}

	/*
	 * Verificar término da sessão de upload de imagem
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
			return $row->done;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Pegar caminho para o arquivo enviado
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
	 * Pegar id na tabela pra o arquivo enviado
	 */
	function get_upload_session_image_id($upload_session_id)
	{
		$this->elementar->select('image_id');
		$this->elementar->from('upload_session');
		$this->elementar->where('id', $upload_session_id);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->image_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Pegar nome do arquivo enviado
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
	 * Gravar informações de imagem na tabela
	 */
	function put_image($alt, $uri, $uri_thumb, $width = NULL, $height = NULL)
	{
		$data = array(
			'alt' => $alt,
			'uri' => $uri,
			'uri_thumb' => $uri_thumb,
			'width' => $width,
			'height' => $height
		);
		$inserted = $this->elementar->insert('image', $data);
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
	 * Gravar título (alt) de imagem na tabela
	 */
	function put_image_title($image_id, $title)
	{
		$data = array(
			'alt' => htmlentities($title, ENT_QUOTES, "UTF-8")
		);
		
		$this->elementar->where('id', $image_id);
		$this->elementar->update('image', $data); 
	}

	/*
	 * Pegar título de imagem
	 */
	function get_image_title($image_id)
	{
		$this->elementar->select('alt');
		$this->elementar->from('image');
		$this->elementar->where('id', $image_id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return html_entity_decode($row->alt, ENT_QUOTES, "UTF-8");
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Remover conteúdo
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
		
		$this->elementar->delete('content_parent', array('content_id' => $content_id)); 
		$this->elementar->delete('content_field', array('content_id' => $content_id)); 
		$this->elementar->delete('content', array('id' => $content_id));
	}
	
	/*
	 * Remover elemento
	 */
	function delete_element($element_id)
	{
		$this->elementar->delete('element_parent', array('element_id' => $element_id)); 
		$this->elementar->delete('element_field', array('element_id' => $element_id)); 
		$this->elementar->delete('element', array('id' => $element_id));
	}

}
