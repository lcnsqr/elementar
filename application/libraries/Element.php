<?php 
/*
 *      Copyright 2012 Luciano Siqueira <lcnsqr@gmail.com>
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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Element {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * Element definitions
	 */
	private $parent_id = 1;
	private $type_id;
	private $id;
	private $status = 'draft';
	private $sname;
	private $name;
	private $spread;

	function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/*
	 * Load element attributes
	 */
	function load()
	{
		$this->parent_id = $this->CI->storage->get_element_parent_id($this->id);
		$this->type_id = $this->CI->storage->get_element_type_id($this->id);
		$this->status = $this->CI->storage->get_element_status($this->id);
		$this->sname = $this->CI->storage->get_element_sname($this->id);
		$this->name = $this->CI->storage->get_element_name($this->id);
		$this->spread = $this->CI->storage->get_element_spread($this->id);
	}
	
	/*
	 * Check existence
	 */
	function exists()
	{
		if ( (bool) $this->CI->storage->get_element_status($this->id) )
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
	
	function get_default_name()
	{
		/*
		 * Generate a default element name 
		 * from element type name and last id + 1
		 */
		return $this->CI->storage->get_element_type_name($this->type_id) . ' #' . ( $this->CI->storage->get_element_last_id($this->type_id) + 1 );
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
		return $this->CI->storage->get_element_type_fields($this->type_id);
	}

	function set_type_fields()
	{
		
	}
	
	function get_field($field_id)
	{
		return $this->CI->storage->get_element_field($this->id, $field_id);
	}

	function set_field($field_id, $value)
	{
		$this->CI->storage->put_element_field($this->id, $field_id, $value);
	}
	
	function get_spread()
	{
		return $this->spread;
	}

	function set_spread($value)
	{
		$this->spread = $value;
	}
	
	function rename()
	{
		$this->CI->storage->put_element_name($this->id, $this->name, $this->sname);
	}
	
	function save()
	{
		if ( (bool) $this->id )
		{
			$this->rename();
		}
		else
		{
			$this->id = $this->CI->storage->put_element($this->name, $this->sname, $this->type_id);
		}
		$this->CI->storage->put_element_parent($this->id, $this->parent_id);
		$this->CI->storage->put_element_spread($this->id, $this->spread);
		$this->CI->storage->put_element_status($this->id, $this->status);
		return $this->id;
	}

	function delete()
	{
		$this->CI->storage->delete_element($this->id);
	}
	
	/*
	 * Element types HTML dropdown
	 * @return HTML content (html dropdown widget)
	 */
	function render_element_types_dropdown()
	{
		$dropdown = div_open(array('class' => 'dropdown_items_listing_inline'));
		$types = $this->CI->storage->get_element_types();
		if ( count($types) > 0 )
		{
			if ( (bool) $this->type_id )
			{
				$dropdown .= anchor($this->CI->storage->get_element_type_name($this->type_id), array('href' => $this->type_id));
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
			$dropdown_items[] = anchor($type, array('class' => 'dropdown_items_listing_element_type_target', 'href' => $type_id));
		}
		// "New" link
		$dropdown_items[] = anchor($this->CI->lang->line('elementar_new') . '...', array('id' => 'element_type_create', 'class' => 'dropdown_items_listing_element_type_target', 'href' => '0'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}


}

/* End of file Element.php */
/* Location: ./application/libraries/Element.php */
