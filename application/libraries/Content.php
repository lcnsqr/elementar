<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Content {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * Content definitions
	 */
	private $parent_id = 1;
	private $type_id;
	private $id;
	private $status = 'draft';
	private $sname;
	private $name;

	private $template_id;
	private $template = array();
	private $template_html;
	private $template_css;
	private $template_javascript;
	private $template_head;

	function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/*
	 * Load content attributes
	 */
	function load()
	{
		$this->parent_id = $this->CI->storage->get_content_parent_id($this->id);
		$this->type_id = $this->CI->storage->get_content_type_id($this->id);
		$this->status = $this->CI->storage->get_content_status($this->id);
		$this->sname = $this->CI->storage->get_content_sname($this->id);
		$this->name = $this->CI->storage->get_content_name($this->id);

		$this->template_id = $this->CI->storage->get_content_template_id($this->id);
		$this->template = $this->CI->storage->get_template($this->id);
		$this->template_html = $this->template['html'];
		$this->template_css = $this->template['css'];
		$this->template_javascript = $this->template['javascript'];
		$this->template_head = $this->template['head'];
	}
	
	/*
	 * Check existence
	 */
	function exists()
	{
		if ( (bool) $this->CI->storage->get_content_status($this->id) )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/*
	 * Default getter and setters
	 */
	function get_parent_id()
	{
		return $this->parent_id;
	}

	function set_parent_id($value)
	{
		$this->parent_id = $value;
	}

	function get_type_id()
	{
		return $this->type_id;
	}

	function set_type_id($value)
	{
		$this->type_id = $value;
	}

	function get_id()
	{
		return $this->id;
	}

	function set_id($value)
	{
		$this->id = $value;
	}
	
	function get_status()
	{
		return $this->status;
	}

	function set_status($value)
	{
		$this->status = $value;
	}
	
	function get_sname()
	{
		return $this->sname;
	}

	function set_sname($value)
	{
		$this->sname = $value;
	}
	
	function get_name()
	{
		return $this->name;
	}

	function set_name($value)
	{
		$this->name = $value;
	}
	
	function get_type_fields()
	{
		return $this->CI->storage->get_content_type_fields($this->type_id);
	}

	function set_type_fields()
	{
		
	}
	
	function get_field($field_id)
	{
		return $this->CI->storage->get_content_field($this->id, $field_id);
	}

	function set_field($field_id, $value)
	{
		$this->CI->storage->put_content_field($this->id, $field_id, $value);
	}
	
	function get_spread()
	{
		return $this->spread;
	}

	function set_spread($value)
	{
		$this->spread = $value;
	}
	
	function get_template_id()
	{
		return $this->template_id;
	}

	function set_template_id($value)
	{
		$this->template_id = $value;
	}
	
	function get_template()
	{
		return $this->template;
	}

	function set_template($value)
	{
		$this->template = $value;
	}
	
	function get_template_css()
	{
		return $this->template_css;
	}

	function set_template_css($value)
	{
		$this->template_css = $value;
	}
	
	function get_template_head()
	{
		return $this->template_head;
	}

	function set_template_head($value)
	{
		$this->template_head = $value;
	}
	
	function get_template_html()
	{
		return $this->template_html;
	}

	function set_template_html($value)
	{
		$this->template_html = $value;
	}
	
	function get_template_javascript()
	{
		return $this->template_javascript;
	}

	function set_template_javascript($value)
	{
		$this->template_javascript = $value;
	}
	
	function rename()
	{
		$this->CI->storage->put_content_name($this->id, $this->name, $this->sname);
	}
	
	function save()
	{
		if ( (bool) $this->id )
		{
			$this->rename();
		}
		else
		{
			$this->id = $this->CI->storage->put_content($this->name, $this->sname, $this->type_id);
		}
		$this->CI->storage->put_content_parent($this->id, $this->parent_id);
		$this->CI->storage->put_content_status($this->id, $this->status);
		return $this->id;
	}

	function delete()
	{
		$this->CI->storage->delete_content($this->id);
	}
	
	/*
	 * Content types HTML dropdown
	 * @return HTML content (html widget)
	 */
	function render_content_types_dropdown()
	{
		$dropdown = div_open(array('class' => 'dropdown_items_listing_inline'));
		$types = $this->CI->storage->get_content_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $this->type_id )
			{
				$dropdown .= anchor($this->CI->storage->get_content_type_name($this->type_id), array('href' => $this->type_id));
			}
			else
			{
				$dropdown .= anchor(current($types), array('href' => key($types)));
			}
		}
		else
		{
			$dropdown .= anchor($this->CI->lang->line('elementar_new') . '...', array('href' => '0'));
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_content_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor($this->CI->lang->line('elementar_new') . '...', array('id' => 'content_type_create', 'class' => 'dropdown_items_listing_content_type_target', 'href' => '0'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}
	


}

/* End of file Content.php */
/* Location: ./application/libraries/Content.php */
