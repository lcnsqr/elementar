<?php 

class M_cms extends CI_Model {
	
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	/*
	 * Read content meta field
	 */
	function get_meta_fields($owner_id = 0, $owner_type = "category")
	{
		$this->db_cms->select('html_meta.name, html_meta.content');
		$this->db_cms->from('html_meta');
		$this->db_cms->join('html_meta_owner', 'html_meta_owner.html_meta_id = html_meta.id', 'inner');
		$this->db_cms->where('html_meta_owner.owner_id', $owner_id);
		$this->db_cms->where('html_meta_owner.owner_type', $owner_type);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			return $query->result_array();
		}
		else
		{
			return array();
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
	 * List element by content
	 */
	function get_element_by_content($content_id = NULL, $elements = NULL)
	{
		/*
		 * elemento não associado 
		 * a conteúdo
		 */
		if ( (bool) $content_id === FALSE )
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
			if ( $elements !== NULL )
			{
				/*
				 * It's a second run, ignore non spreaded elements
				 */
				$this->db_cms->where('spread', TRUE);
			}
			else
			{
				/*
				 * First run, no upper level elements to join
				 */
				$elements = array();
			}
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
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
			 * elemento associado/herdado a conteúdo
			 */
			$this->db_cms->select('element.id, element.name, element.sname');
			$this->db_cms->from('element');
			$this->db_cms->join('element_content', 'element_content.element_id = element.id', 'inner');
			$this->db_cms->where('element_content.content_id', $content_id);
			if ( $elements !== NULL )
			{
				/*
				 * It's a second run, ignore non spreaded elements
				 */
				$this->db_cms->where('element.spread', TRUE);
			}
			else
			{
				/*
				 * First run, no upper level elements to join
				 */
				$elements = array();
			}
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$elements[] = array(
						'id' => $row->id, 
						'name' => $row->name,
						'sname' => $row->sname
					);
				}
			}
			/*
			 * Pull upper level elements
			 */
			$elements = $this->get_element_by_category($this->get_content_category($content_id), $elements);
		}
		return $elements;
	}

	/*
	 * List element by category
	 */
	function get_element_by_category($category_id = NULL, $elements = NULL)
	{
		if ( (bool) $category_id === FALSE )
		{
			/*
			 * elemento não associado 
			 * a categoria
			 */
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
			if ( $elements !== NULL )
			{
				/*
				 * It's a second run, ignore non spreaded elements
				 */
				$this->db_cms->where('spread', TRUE);
			}
			else
			{
				/*
				 * First run, no upper level elements to join
				 */
				$elements = array();
			}
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
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
			 * elemento associado/herdado a categoria
			 */
			$this->db_cms->select('element.id, element.name, element.sname');
			$this->db_cms->from('element');
			$this->db_cms->join('element_category', 'element_category.element_id = element.id', 'inner');
			$this->db_cms->where('element_category.category_id', $category_id);
			if ( $elements !== NULL )
			{
				/*
				 * It's a second run, ignore non spreaded elements
				 */
				$this->db_cms->where('spread', TRUE);
			}
			else
			{
				/*
				 * First run, no upper level elements to join
				 */
				$elements = array();
			}
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$elements[] = array(
						'id' => $row->id, 
						'name' => $row->name,
						'sname' => $row->sname
					);
				}
			}
			/*
			 * Pull upper level elements
			 */
			$elements = $this->get_element_by_category($this->get_category_parent($category_id), $elements);
		}
		return $elements;
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
			return $row->value;
		}
		else
		{
			return NULL;
		}

	}

	/*
	 * get entry content type
	 */
	function get_content_type($id)
	{
		$this->db_cms->select('content_type_id');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$this->db_cms->limit(1);
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
	 * get field type sname
	 */
	function get_field_type_sname($id)
	{
		$this->db_cms->select('id, name, sname, description');
		$this->db_cms->from('field_type');
		$this->db_cms->where('id', $id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ( $query->num_rows() > 0 )
		{
			$row = $query->row();
			return $row->sname;
		}
		return NULL;
	}

	/*
	 * get field type description
	 */
	function get_field_type_description($id)
	{
		$this->db_cms->select('id, name, sname, description');
		$this->db_cms->from('field_type');
		$this->db_cms->where('id', $id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ( $query->num_rows() > 0 )
		{
			$row = $query->row();
			return $row->description;
			
		}
		return NULL;
	}

	function get_category_children($parent_id)
	{
		$children = array();

		$this->db_cms->select('name, sname');
		$this->db_cms->from('category');
		$this->db_cms->where('parent_id', $parent_id);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$children[] = array(
					'name' => $row->name,
					'uri' => "/" . $this->uri->uri_string() . "/" . $row->sname . "/"
				);
			}
		}

		return $children;

	}

	function get_category_id($sname, $level)
	{
		$this->db_cms->select('id');
		$this->db_cms->from('category');
		$this->db_cms->where('sname', $sname);
		$this->db_cms->where('level', $level);
		$this->db_cms->limit(1);
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

	function get_category_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('category');
		$this->db_cms->where('id', $id);
		$this->db_cms->limit(1);
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

	function get_category_template($id)
	{
		$this->db_cms->select('html_template.template');
		$this->db_cms->from('html_template');
		$this->db_cms->where('category.id', $id);
		$this->db_cms->join('category', 'category.html_template_id = html_template.id', 'inner');
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->template;
		}
		else
		{
			return NULL;
		}
	}

	function get_content_id($sname, $category_id = NULL)
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
			
			$this->db_cms->select('id');
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
			else
			{
				return NULL;
			}
		}
		else
		{
			/*
			 * conteudo associado à categoria
			 */
			$this->db_cms->select('content.id, content.name, content.sname');
			$this->db_cms->from('content');
			$this->db_cms->where('content.sname', $sname);
			$this->db_cms->join('content_category', 'content_category.content_id = content.id', 'inner');
			$this->db_cms->where('content_category.category_id', $category_id);
			$this->db_cms->limit(1);
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

	function get_content_name($id)
	{
		$this->db_cms->select('name');
		$this->db_cms->from('content');
		$this->db_cms->where('id', $id);
		$this->db_cms->limit(1);
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

	function get_contents()
	{

		$contents = array();

		$this->db_cms->select('id, name, sname, modified');
		$this->db_cms->from('content');
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$contents = $query->result_array();
		}
		return $contents;
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

	function get_content_template($id)
	{
		$this->db_cms->select('html_template.template');
		$this->db_cms->from('html_template');
		$this->db_cms->where('content.id', $id);
		$this->db_cms->join('content', 'content.html_template_id = html_template.id', 'inner');
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->template;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * List content by category
	 */
	function get_content_by_category($category_id = NULL)
	{
		$contents = array();
		
		/*
		 * conteudo não associado 
		 * à categoria
		 */
		if ( $category_id === NULL )
		{
			$this->db_cms->select('content_id');
			$this->db_cms->from('content_category');
			$this->db_cms->distinct();
			$query = $this->db_cms->get();
			$rel = array(0);
			foreach ($query->result() as $row)
			{
				$rel[] = $row->content_id;
			}
			
			$this->db_cms->select('id, name, sname');
			$this->db_cms->from('content');
			$this->db_cms->where_not_in('id', $rel);
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$contents[] = array(
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
			 * conteudo associado à categoria
			 */
			$this->db_cms->select('content.id, content.name, content.sname, content.modified');
			$this->db_cms->from('content');
			$this->db_cms->join('content_category', 'content_category.content_id = content.id', 'inner');
			$this->db_cms->where('content_category.category_id', $category_id);
			$query = $this->db_cms->get();
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$contents[] = array(
						'id' => $row->id, 
						'name' => $row->name,
						'sname' => $row->sname,
						'modified' => $row->modified
					);
				}
			}
		}
		return $contents;
	}

	/*
	 * Pegar uri de imagem
	 */
	function get_image_uri($field_id)
	{
		$this->db_cms->select('uri');
		$this->db_cms->from('image');
		$this->db_cms->where('id', $field_id);
		$this->db_cms->limit(1);
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
	 * Pegar largura de imagem
	 */
	function get_image_width($field_id)
	{
		$this->db_cms->select('width');
		$this->db_cms->from('image');
		$this->db_cms->where('id', $field_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->width;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Pegar altura de imagem
	 */
	function get_image_height($field_id)
	{
		$this->db_cms->select('height');
		$this->db_cms->from('image');
		$this->db_cms->where('id', $field_id);
		$this->db_cms->limit(1);
		$query = $this->db_cms->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->height;
		}
		else
		{
			return NULL;
		}
	}

	/*
	 * Verifica se sname
	 * é categoria
	 */
	function is_category($sname, $level)
	{
		$this->db_cms->select('id');
		$this->db_cms->from('category');
		$this->db_cms->where('sname', $sname);
		$this->db_cms->where('level', $level);
		$this->db_cms->limit(1);
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

}
