<?php
/*
 *      m_cms_admin.php
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
?>

<?php 

class M_cms_admin extends CI_Model {
	
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}

	/*
	 * Gravar content type
	 */
	function put_content_type($name)
	{
		/*
		 * Verify existing name
		 */
		$this->db_cms->select('id');
		$this->db_cms->from('content_type');
		$this->db_cms->where('name', $name);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return FALSE;
		}
		else
		{
			$data = array(
				'name' => $name
			);
			$inserted = $this->db_cms->insert('content_type', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
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
		$this->db_cms->insert('content_type_field', $data);
	}

	/*
	 * Gravar element type
	 */
	function put_element_type($name, $sname)
	{
		/*
		 * Verify existing name
		 */
		$this->db_cms->select('id');
		$this->db_cms->from('element_type');
		$this->db_cms->where('name', $name);
		$query = $this->db_cms->get();
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
			$inserted = $this->db_cms->insert('element_type', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
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
		$this->db_cms->insert('element_type_field', $data);
	}

	/*
	 * get category name (title)
	 */
	function get_category_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('category');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	 * get category level
	 */
	function get_category_level($id)
	{
		$this->db_cms->select('level');
		$this->db_cms->from('category');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->level;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get category parent
	 */
	function get_category_parent($id)
	{
		$this->db_cms->select('parent_id');
		$this->db_cms->from('category');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->parent_id;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get menu level
	 */
	function get_menu_level($id)
	{
		$this->db_cms->select('level');
		$this->db_cms->from('menu');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->level;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get menu name
	 */
	function get_menu_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('menu');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	 * get menu target
	 */
	function get_menu_target($id)
	{
		$this->db_cms->select('target');
		$this->db_cms->from('menu');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->target;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * get category/content relation 
	 */
	function get_category_has_content($category_id, $content_id)
	{
		$this->db_cms->select('id');
		$this->db_cms->from('content_category');
		$this->db_cms->where('content_id', $content_id);
		$this->db_cms->where('category_id', $category_id);
		$query = $this->db_cms->get();
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
	 * Verify if category has any content/element associated to it
	 */
	function get_category_has_children($category_id)
	{
		// Category
		$this->db_cms->select('id');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $category_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}

		// Content
		$this->db_cms->select('id');
		$this->db_cms->from('content_category');
		$this->db_cms->where('category_id', $category_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}

		// Element
		$this->db_cms->select('id');
		$this->db_cms->from('element_category');
		$this->db_cms->where('category_id', $category_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}

		return FALSE;
	}


	/*
	 * get content name (title)
	 */
	function get_content_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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

	function get_content_category($id)
	{
		$this->db_cms->select('category_id');
		$this->db_cms->from('content_category');
		$this->db_cms->where('content_id', $id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->category_id;
		}
		else
		{
			return NULL;
		}
	}
	
	/*
	 * get element name (title)
	 */
	function get_element_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('element');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_element_type_sname($id)
	{
		$this->db_cms->select('sname');
		$this->db_cms->from('element_type');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
		$this->db_cms->select('name');
		$this->db_cms->from('element_type');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_content_sname($id)
	{
		$this->db_cms->select('sname');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_content_status($id)
	{
		$this->db_cms->select('status');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	 * get element status
	 */
	function get_element_status($id)
	{
		$this->db_cms->select('status');
		$this->db_cms->from('element');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
		$inserted = $this->db_cms->insert('content', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Renomear conteúdo
	 */
	function put_content_name($id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->db_cms->where('id', $id);
		$this->db_cms->update('content', $data); 
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
		$inserted = $this->db_cms->insert('element', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Renomear elemento
	 */
	function put_element_name($id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->db_cms->where('id', $id);
		$this->db_cms->update('element', $data); 
	}
		
	/*
	 * Gravar status do conteúdo
	 */
	function put_content_status($content_id, $status)
	{
		$data = array(
			'status' => $status
		);
		$this->db_cms->where('id', $content_id);
		$this->db_cms->update('content', $data); 
	}

	/*
	 * Gravar campo de conteúdo
	 */
	function put_content_field($content_id, $content_type_field_id, $value)
	{
		$this->db_cms->select('id');
		$this->db_cms->from('content_field');
		$this->db_cms->where('content_id', $content_id);
		$this->db_cms->where('content_type_field_id', $content_type_field_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			/*
			 * Update
			 */
			$data = array(
				'value' => $value
			);
			$this->db_cms->where('content_id', $content_id);
			$this->db_cms->where('content_type_field_id', $content_type_field_id);
			$this->db_cms->update('content_field', $data); 

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
			$inserted = $this->db_cms->insert('content_field', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
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
		$this->db_cms->select('id');
		$this->db_cms->from('element_field');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->where('element_type_field_id', $element_type_field_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			/*
			 * Update
			 */
			$data = array(
				'value' => $value
			);
			$this->db_cms->where('element_id', $element_id);
			$this->db_cms->where('element_type_field_id', $element_type_field_id);
			$this->db_cms->update('element_field', $data); 

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
			$inserted = $this->db_cms->insert('element_field', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
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
		$this->db_cms->where('id', $element_id);
		$this->db_cms->update('element', $data); 
	}

	/*
	 * Read content meta field
	 */
	function get_meta_field($owner_id, $owner_type, $name)
	{
		$this->db_cms->select('html_meta.content');
		$this->db_cms->from('html_meta');
		$this->db_cms->join('html_meta_owner', 'html_meta_owner.html_meta_id = html_meta.id', 'inner');
		$this->db_cms->where('html_meta.name', $name);
		$this->db_cms->where('html_meta_owner.owner_id', $owner_id);
		$this->db_cms->where('html_meta_owner.owner_type', $owner_type);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->content;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Delete content meta field
	 */
	function delete_meta_field($owner_id, $owner_type, $name)
	{
		$this->db_cms->select('html_meta.id as html_meta_id, html_meta_owner.id as html_meta_owner_id');
		$this->db_cms->from('html_meta');
		$this->db_cms->join('html_meta_owner', 'html_meta_owner.html_meta_id = html_meta.id', 'inner');
		$this->db_cms->where('html_meta.name', $name);
		$this->db_cms->where('html_meta_owner.owner_id', $owner_id);
		$this->db_cms->where('html_meta_owner.owner_type', $owner_type);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$this->db_cms->delete('html_meta', array('id' => $row->html_meta_id));
			$this->db_cms->delete('html_meta_owner', array('id' => $row->html_meta_owner_id));
		}
	}

	/*
	 * Write content meta field
	 */
	function put_meta_field($owner_id, $owner_type, $name, $content)
	{
		$this->db_cms->select('html_meta.id');
		$this->db_cms->from('html_meta');
		$this->db_cms->join('html_meta_owner', 'html_meta_owner.html_meta_id = html_meta.id', 'inner');
		$this->db_cms->where('html_meta.name', $name);
		$this->db_cms->where('html_meta_owner.owner_id', $owner_id);
		$this->db_cms->where('html_meta_owner.owner_type', $owner_type);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$data = array(
				'content' => $content
			);
			$this->db_cms->where('id', $row->id);
			$this->db_cms->update('html_meta', $data); 
		}
		else
		{
			$data = array(
				'name' => $name,
				'content' => $content
			);
			$inserted = $this->db_cms->insert('html_meta', $data);
			if ($inserted)
			{
				$id = $this->db_cms->insert_id();
				$data = array(
					'html_meta_id' => $id,
					'owner_type' => $owner_type,
					'owner_id' => $owner_id
				);
				$this->db_cms->insert('html_meta_owner', $data);
			}
		}
	}


	/*
	 * Pegar campo de conteúdo
	 */
	function get_content_field($content_id, $content_type_field_id)
	{
		$this->db_cms->select('value');
		$this->db_cms->from('content_field');
		$this->db_cms->where('content_id', $content_id);
		$this->db_cms->where('content_type_field_id', $content_type_field_id);
		$query = $this->db_cms->get();
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
	 * Pegar campo de elemento
	 */
	function get_element_field($element_id, $element_type_field_id)
	{
		$this->db_cms->select('value');
		$this->db_cms->from('element_field');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->where('element_type_field_id', $element_type_field_id);
		$query = $this->db_cms->get();
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
		$this->db_cms->select('id');
		$this->db_cms->from('content_field');
		$this->db_cms->where('content_id', $content_id);
		$this->db_cms->where('content_type_field_id', $content_type_field_id);
		$query = $this->db_cms->get();
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
		$this->db_cms->select('field_type.id, field_type.name, field_type.sname, field_type.description');
		$this->db_cms->from('content_field');
		$this->db_cms->join('content_type_field', 'content_type_field.id = content_field.content_type_field_id', 'inner');
		$this->db_cms->join('field_type', 'field_type.id = content_type_field.field_type_id', 'inner');
		$this->db_cms->where('content_field.id', $field_id);
		$query = $this->db_cms->get();
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
	 * Pegar uri de imagem
	 */
	function get_image_uri($field_id)
	{
		$this->db_cms->select('uri');
		$this->db_cms->from('image');
		$this->db_cms->where('id', $field_id);
		$query = $this->db_cms->get();
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
		$this->db_cms->select('uri_thumb');
		$this->db_cms->from('image');
		$this->db_cms->where('id', $field_id);
		$query = $this->db_cms->get();
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
	 * Gravar categoria de conteúdo
	 */
	function put_content_category($content_id, $category_id)
	{
		if ( ! (bool) $category_id )
		{
			$this->db_cms->delete('content_category', array('content_id' => $content_id)); 
		}
		else
		{
			/*
			 * Verificar se conteúdo já está
			 * associado à categoria em qestão
			 */
			$this->db_cms->select('id');
			$this->db_cms->from('content_category');
			$this->db_cms->where('content_id', $content_id);
			$this->db_cms->where('category_id', $category_id);
			$query = $this->db_cms->get();
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
				'category_id' => $category_id
			);
			$inserted = $this->db_cms->insert('content_category', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
			}
			else
			{
				return FALSE;
			}
		}
	}

	/*
	 * Gravar categoria de elemento
	 */
	function put_element_category($element_id, $category_id)
	{
		/*
		 * Verificar se conteúdo já está
		 * associado à categoria em qestão
		 */
		$this->db_cms->select('id');
		$this->db_cms->from('element_category');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->where('category_id', $category_id);
		$query = $this->db_cms->get();
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
			'category_id' => $category_id
		);
		$inserted = $this->db_cms->insert('element_category', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Gravar conteúdo de elemento
	 */
	function put_element_content($element_id, $content_id)
	{
		/*
		 * Verificar se elemento já está
		 * associado a conteúdo em qestão
		 */
		$this->db_cms->select('id');
		$this->db_cms->from('element_content');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->where('content_id', $content_id);
		$query = $this->db_cms->get();
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
			'content_id' => $content_id
		);
		$inserted = $this->db_cms->insert('element_content', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * get content type
	 */
	function get_content_type($id)
	{
		$this->db_cms->select('content_type_id');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_element_type($id)
	{
		$this->db_cms->select('element_type_id');
		$this->db_cms->from('element');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
		$this->db_cms->select('id, name');
		$this->db_cms->from('content_type');
		$query = $this->db_cms->get();
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
		$this->db_cms->select('id, name');
		$this->db_cms->from('element_type');
		$query = $this->db_cms->get();
		foreach ($query->result() as $row)
		{
			$types[$row->id] = $row->name;
		}
		return $types;
	}

	/*
	 * get content type name
	 */
	function get_content_type_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('content_type');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	 * list content type fields
	 */
	function get_content_type_fields($type_id)
	{
		$fields = array();
		$this->db_cms->select('id, name, sname, field_type_id');
		$this->db_cms->from('content_type_field');
		$this->db_cms->where('content_type_id', $type_id);
		$this->db_cms->order_by('id', 'asc');
		$query = $this->db_cms->get();
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
		$this->db_cms->select('id, name, sname, field_type_id');
		$this->db_cms->from('element_type_field');
		$this->db_cms->where('element_type_id', $type_id);
		$this->db_cms->order_by('id', 'asc');
		$query = $this->db_cms->get();
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
	 * list field types
	 */
	function get_field_types($except = NULL)
	{
		$fields = array();
		$this->db_cms->select('id, name, sname, description');
		$this->db_cms->from('field_type');
		if ( $except !== NULL )
			$this->db_cms->where('sname !=', $except);
		$query = $this->db_cms->get();
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
	function get_field_type_sname($id)
	{
		$fields = array();
		$this->db_cms->select('id, name, sname, description');
		$this->db_cms->from('field_type');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_field_type_description($id)
	{
		$fields = array();
		$this->db_cms->select('id, name, sname, description');
		$this->db_cms->from('field_type');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ( $query->num_rows() > 0 )
		{
			$row = $query->row();
			$sname = $row->description;
		}
		return $sname;
	}

	/*
	 * List category by parent
	 */
	function get_categories_by_parent($parent_id = NULL)
	{

		$cats = array();

		$this->db_cms->select('id, name, sname');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $parent_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$cats[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'children' => $this->get_category_has_children($row->id)
				);
			}
		}
		return $cats;
	}

	/*
	 * Get category by parent
	 */
	function get_category_by_parent($parent_id = NULL, $sname)
	{
		$this->db_cms->select('id, name, sname');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $parent_id);
		$this->db_cms->where('sname', $sname);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->id;
		}
	}

	/*
	 * List contents by category
	 */
	function get_contents_by_category($category_id = NULL)
	{
		$contents = NULL;
		
		/*
		 * conteudo não associado 
		 * à categoria
		 */
		if ( $category_id === NULL )
		{
			$this->db_cms->select('content_id');
			$this->db_cms->from('content_category');
			$query = $this->db_cms->get();
			$rel = array(0);
			foreach ($query->result() as $row)
			{
				$rel[] = $row->content_id;
			}
			
			$this->db_cms->select('id, name, sname');
			$this->db_cms->from('content');
			$this->db_cms->where_not_in('id', $rel);
			$this->db_cms->order_by('created', 'desc');
			$query = $this->db_cms->get();
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
		}
		else
		{
			/*
			 * conteudo associado à categoria
			 */
			$this->db_cms->select('content.id, content.name, content.sname');
			$this->db_cms->from('content');
			$this->db_cms->join('content_category', 'content_category.content_id = content.id', 'inner');
			$this->db_cms->where('content_category.category_id', $category_id);
			$this->db_cms->order_by('content.created', 'desc');
			$query = $this->db_cms->get();
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
		}
		return $contents;
	}

	/*
	 * Get content by category
	 */
	function get_content_by_category($category_id = NULL, $sname)
	{
		/*
		 * conteudo não associado 
		 * à categoria
		 */
		if ( $category_id === NULL )
		{
			$this->db_cms->select('content_id');
			$this->db_cms->from('content_category');
			$query = $this->db_cms->get();
			$rel = array(0);
			foreach ($query->result() as $row)
			{
				$rel[] = $row->content_id;
			}
			
			$this->db_cms->select('id, name, sname');
			$this->db_cms->from('content');
			$this->db_cms->where('sname', $sname);
			$this->db_cms->where_not_in('id', $rel);
			$this->db_cms->limit(1);
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				return $row->id;
			}
		}
		else
		{
			/*
			 * conteudo associado à categoria
			 */
			$this->db_cms->select('content.id, content.name, content.sname');
			$this->db_cms->from('content');
			$this->db_cms->join('content_category', 'content_category.content_id = content.id', 'inner');
			$this->db_cms->where('content_category.category_id', $category_id);
			$this->db_cms->where('content.sname', $sname);
			$this->db_cms->limit(1);
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				return $row->id;
			}
		}
	}

	/*
	 * List element by content
	 */
	function get_element_by_content($content_id = NULL)
	{
		$elements = NULL;
		
		/*
		 * elemento não associado 
		 * a conteúdo
		 */
		if ( $content_id === NULL )
		{
			$this->db_cms->select('element_id');
			$this->db_cms->from('element_content');
			$query = $this->db_cms->get();
			$rel = array(0);
			foreach ($query->result() as $row)
			{
				$rel[] = $row->element_id;
			}
			
			$this->db_cms->select('id, name, sname');
			$this->db_cms->from('element');
			$this->db_cms->where_not_in('id', $rel);
			$query = $this->db_cms->get();
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
		}
		else
		{
			/*
			 * elemento associado a conteúdo
			 */
			$this->db_cms->select('element.id, element.name, element.sname');
			$this->db_cms->from('element');
			$this->db_cms->join('element_content', 'element_content.element_id = element.id', 'inner');
			$this->db_cms->where('element_content.content_id', $content_id);
			$query = $this->db_cms->get();
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
		}
		return $elements;
	}

	/*
	 * List element by category
	 */
	function get_element_by_category($category_id = NULL)
	{
		$elements = NULL;
		
		/*
		 * elemento não associado 
		 * a categoria
		 */
		if ( $category_id === NULL )
		{
			$rel = array(0);

			$this->db_cms->select('element_id');
			$this->db_cms->from('element_category');
			$query = $this->db_cms->get();
			foreach ($query->result() as $row)
			{
				$rel[] = $row->element_id;
			}
			
			$this->db_cms->select('element_id');
			$this->db_cms->from('element_content');
			$query = $this->db_cms->get();
			foreach ($query->result() as $row)
			{
				$rel[] = $row->element_id;
			}
			
			$this->db_cms->select('id, name, sname');
			$this->db_cms->from('element');
			$this->db_cms->where_not_in('id', $rel);
			$this->db_cms->order_by('created', 'desc');
			$query = $this->db_cms->get();
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
		}
		else
		{
			/*
			 * elemento associado a categoria
			 */
			$this->db_cms->select('element.id, element.name, element.sname');
			$this->db_cms->from('element');
			$this->db_cms->join('element_category', 'element_category.element_id = element.id', 'inner');
			$this->db_cms->where('element_category.category_id', $category_id);
			$this->db_cms->order_by('element.created', 'desc');
			$query = $this->db_cms->get();
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
		}
		return $elements;
	}

	/*
	 * Element parent (owner) id
	 */
	function get_element_parent($element_id)
	{
		/*
		 * to do: check for main owner
		 */
		
		/*
		 * First look for category parent
		 */
		$this->db_cms->select('category_id');
		$this->db_cms->from('element_category');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->category_id;
		}
		
		/*
		 * After look for content parent
		 */
		$this->db_cms->select('content_id');
		$this->db_cms->from('element_content');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->content_id;
		}

		return NULL;
	}

	/*
	 * Element parent (owner) type
	 */
	function get_element_parent_type($element_id)
	{
		/*
		 * to do: check for main owner
		 */
		
		/*
		 * First look for category parent
		 */
		$this->db_cms->select('category_id');
		$this->db_cms->from('element_category');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return "category";
		}
		
		/*
		 * After look for content parent
		 */
		$this->db_cms->select('content_id');
		$this->db_cms->from('element_content');
		$this->db_cms->where('element_id', $element_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return "content";
		}

		/*
		 * Default: category
		 */
		return "category";
	}

	/*
	 * Verify if content has any element associated to it
	 */
	function get_content_has_children($content_id)
	{
		$this->db_cms->select('id');
		$this->db_cms->from('element_content');
		$this->db_cms->where('content_id', $content_id);
		$query = $this->db_cms->get();
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
	 * Display all categories
	 */
	function get_categories()
	{

		$cats = array();

		$this->db_cms->select('id, name, sname, level, parent_id');
		$this->db_cms->from('category');
		$this->db_cms->where('level', 0);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$cats[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'level' => $row->level,
					'children' => $this->_get_category_children($row->id)
				);
			}
		}
		return $cats;
	}
	
	/*
	 * Display all contents
	 */
	function get_contents()
	{

		$contents = array();

		$this->db_cms->select('id, name, sname');
		$this->db_cms->from('content');
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$contents = $query->result_array();
		}
		return $contents;
	}
	
	/*
	 * Children categories
	 */
	function _get_category_children($cat_id)
	{
		$cats = array();
	
		$this->db_cms->select('id, name, sname, level');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $cat_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$cats[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'level' => $row->level,
					'children' => $this->_get_category_children($row->id)
				);
			}
			return $cats;
		}
		else
		{
			return NULL;
		}
	}
	
	/*
	 * URI constructor for category
	 */
	function get_category_uri($id)
	{
		$this->db_cms->select('id, name, sname, parent_id');
		$this->db_cms->from('category');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$uri = $row->sname;
			
			/*
			 * Checar por categoria superior
			 */
			if ( $row->parent_id != NULL )
			{
				$uri = $this->get_category_uri($row->parent_id) . "/" . $uri;
			}
			else
			{
				$uri = "/" . $uri;
			}
			return $uri;
		}
	}

	/*
	 * URI constructor for content
	 */
	function get_content_uri($id)
	{
		$this->db_cms->select('id, name, sname');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$uri = "/" . $row->sname;
			
			/*
			 * Checar por categoria 
			 */
			$this->db_cms->select('content_category.category_id as parent_id');
			$this->db_cms->from('content_category');
			$this->db_cms->join('category', 'content_category.category_id = category.id', 'inner');
			$this->db_cms->where('content_category.content_id', $id);
			$this->db_cms->order_by('category.level', 'desc'); 
			$this->db_cms->limit(1);
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$uri = $this->get_category_uri($row->parent_id) . $uri;
			}
			return $uri;
		}
	}

	/*
	 * Display all menus
	 */
	function get_menus($parent_id = NULL)
	{
		$menus = array();

		$this->db_cms->select('id, name, sname, level, target, order');
		$this->db_cms->from('menu');
		if ( $parent_id === NULL )
		{
			$this->db_cms->where('level', 0);
		}
		else
		{
			$this->db_cms->where('parent_id', $parent_id);
			$this->db_cms->order_by('order', 'asc');
		}
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$menus[] = array(
					'id' => $row->id, 
					'name' => $row->name,
					'sname' => $row->sname,
					'level' => $row->level,
					'target' => $row->target,
					'order' => $row->order,
					'children' => $this->get_menus($row->id)
				);
			}
			return $menus;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Children categories IDs
	 */
	function get_category_children_ids($cat_id)
	{	
		$this->db_cms->select('id');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $cat_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$cats[] = $row->id; 
				$cats = array_merge($cats, $this->get_category_children_ids($row->id));
			}
			return $cats;
		}
		else
		{
			return array();
		}
	}

	/*
	 * Children menu IDs
	 */
	function get_menu_children_ids($menu_id)
	{	
		$this->db_cms->select('id');
		$this->db_cms->from('menu');
		$this->db_cms->where('parent_id', $menu_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$menus[] = $row->id; 
				$menus = array_merge($menus, $this->get_menu_children_ids($row->id));
			}
			return $menus;
		}
		else
		{
			return array();
		}
	}

	/*
	 * Remover categoria
	 */
	function delete_category($cat_id)
	{
		/*
		 * Remover descendentes 
		 * e relacionamentos
		 */
		if ( $this->get_category_has_children($cat_id) )
		{
			foreach ($this->get_category_children_ids($cat_id) as $id)
			{
				/*
				 * Delete associated contents
				 */
				$this->db_cms->select('content_id');
				$this->db_cms->from('content_category');
				$this->db_cms->where('category_id', $id);
				$query = $this->db_cms->get();
				foreach ($query->result() as $row)
				{
					$this->delete_content($row->content_id);
				}

				/*
				 * Delete associated elements
				 */
				$this->db_cms->select('element_id');
				$this->db_cms->from('element_category');
				$this->db_cms->where('category_id', $id);
				$query = $this->db_cms->get();
				foreach ($query->result() as $row)
				{
					$this->delete_element($row->element_id);
				}

				$this->delete_category_rel($id);
				$this->db_cms->delete('category', array('id' => $id));
			}
		}
		/*
		 * Delete associated contents
		 */
		$this->db_cms->select('content_id');
		$this->db_cms->from('content_category');
		$this->db_cms->where('category_id', $cat_id);
		$query = $this->db_cms->get();
		foreach ($query->result() as $row)
		{
			$this->delete_content($row->content_id);
		}
		/*
		 * Delete associated elements
		 */
		$this->db_cms->select('element_id');
		$this->db_cms->from('element_category');
		$this->db_cms->where('category_id', $cat_id);
		$query = $this->db_cms->get();
		/* 
		 * Remover solicitada
		 */
		foreach ($query->result() as $row)
		{
			$this->delete_element($row->element_id);
		}
		$this->delete_category_rel($cat_id);
		$this->db_cms->delete('category', array('id' => $cat_id));
	}

	/*
	 * Remover menu
	 */
	function delete_menu($menu_id)
	{
		/*
		 * Remover descendentes 
		 */
		foreach ($this->get_menu_children_ids($menu_id) as $id)
		{
			$this->db_cms->delete('menu', array('id' => $menu_id));
		}
		/* 
		 * Remover solicitado
		 */
		$this->db_cms->delete('menu', array('id' => $menu_id));

	}

	/*
	 * Renomear menu
	 */
	function put_menu_name($menu_id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->db_cms->where('id', $menu_id);
		$this->db_cms->update('menu', $data); 
	}

	/*
	 * atualizar ordem do menu
	 */
	function put_menu_order($menu_id, $order)
	{
		$data = array(
			'order' => $order
		);
		
		$this->db_cms->where('id', $menu_id);
		$this->db_cms->update('menu', $data); 
	}

	/*
	 * Atualizar nível do menu
	 */
	function put_menu_level($menu_id, $level)
	{
		$data = array(
			'level' => $level
		);
		
		$this->db_cms->where('id', $menu_id);
		$this->db_cms->update('menu', $data); 
	}

	/*
	 * Atualizar pai do menu
	 */
	function put_menu_parent($menu_id, $parent_id)
	{
		$data = array(
			'parent_id' => $parent_id
		);
		
		$this->db_cms->where('id', $menu_id);
		$this->db_cms->update('menu', $data); 
	}

	/*
	 * Criar categoria
	 */
	function put_category($name, $sname, $parent_id = NULL, $level = 0)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname,
			'parent_id' => $parent_id,
			'level' => $level
		);
		$inserted = $this->db_cms->insert('category', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Renomear categoria
	 */
	function put_category_name($cat_id, $name, $sname)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname
		);
		
		$this->db_cms->where('id', $cat_id);
		$this->db_cms->update('category', $data); 
	}
		
	/*
	 * Criar menu
	 */
	function put_menu($name, $sname, $parent_id = NULL, $level = 0)
	{
		$data = array(
			'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
			'sname' => $sname,
			'parent_id' => $parent_id,
			'level' => $level
		);
		$inserted = $this->db_cms->insert('menu', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/*
	 * Save menu target
	 */
	function put_menu_target($menu_id, $target)
	{
		$data = array(
			'target' => $target
		);
		
		$this->db_cms->where('id', $menu_id);
		$this->db_cms->update('menu', $data); 
	}
	
	/*
	 * Criar/Atualizar sessão de upload de imagem
	 */
	function put_upload_session($id = NULL, $field = NULL, $value = NULL)
	{
		if ( $id == NULL )
		{
			$data = array(
				'id' => $id
			);
			$inserted = $this->db_cms->insert('upload_session', $data);
			if ($inserted)
			{
				return $this->db_cms->insert_id();
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
			$this->db_cms->where('id', $id);
			$this->db_cms->update('upload_session', $data); 
		}
	}

	/*
	 * Verificar término da sessão de upload de imagem
	 */
	function get_upload_session_done($id)
	{
		$this->db_cms->select('done');
		$this->db_cms->from('upload_session');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_upload_session_uri($id)
	{
		$this->db_cms->select('uri');
		$this->db_cms->from('upload_session');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_upload_session_image_id($id)
	{
		$this->db_cms->select('image_id');
		$this->db_cms->from('upload_session');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
	function get_upload_session_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('upload_session');
		$this->db_cms->where('id', $id);
		$query = $this->db_cms->get();
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
		$inserted = $this->db_cms->insert('image', $data);
		if ($inserted)
		{
			return $this->db_cms->insert_id();
		}
		else
		{
			return FALSE;
		}
	}
	

	/*
	 * Remover relacionamentos da categoria
	 */
	function delete_category_rel($id)
	{
		$this->db_cms->delete('content_category', array('category_id' => $id)); 
		$this->db_cms->delete('element_category', array('category_id' => $id)); 
	}
	
	/*
	 * Remover relacionamentos do conteúdo
	 */
	function delete_content_rel($id)
	{
		$this->db_cms->delete('content_category', array('content_id' => $id)); 
		$this->db_cms->delete('element_content', array('content_id' => $id)); 
	}
	
	/*
	 * Remover campos do conteúdo
	 */
	function delete_content_fields($id)
	{
		$this->db_cms->delete('content_field', array('content_id' => $id)); 
	}
	
	/*
	 * Remover conteúdo
	 */
	function delete_content($id)
	{
		if ( $this->get_content_has_children($id) ) 
		{
			/*
			 * Delete associated elements
			 */
			$this->db_cms->select('element_id');
			$this->db_cms->from('element_content');
			$this->db_cms->where('content_id', $id);
			$query = $this->db_cms->get();
			foreach ($query->result() as $row)
			{
				$this->delete_element($row->element_id);
			}
		}
		
		$this->delete_content_rel($id);
		$this->delete_content_fields($id);
		$this->db_cms->delete('content', array('id' => $id));
	}

	/*
	 * Remover relacionamentos do elemento
	 */
	function delete_element_rel($id)
	{
		$this->db_cms->delete('element_category', array('element_id' => $id)); 
		$this->db_cms->delete('element_content', array('element_id' => $id)); 
	}
	
	/*
	 * Remover campos do elemento
	 */
	function delete_element_fields($id)
	{
		$this->db_cms->delete('element_field', array('element_id' => $id)); 
	}
	
	/*
	 * Remover elemento
	 */
	function delete_element($id)
	{
		$this->delete_element_rel($id);
		$this->delete_element_fields($id);
		$this->db_cms->delete('element', array('id' => $id));
	}

}
