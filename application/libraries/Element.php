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
	private $account_id;

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
		$this->account_id = $this->CI->storage->get_element_account_id($this->id);
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
	
	function get_account_id()
	{
		return $this->account_id;
	}

	function set_account_id($value)
	{
		$this->account_id = $value;
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
		$this->account_id = $this->CI->session->userdata('account_id');
		$this->CI->storage->put_element_status($this->id, $this->status);
		$this->CI->storage->put_element_account_id($this->id, $this->account_id);
		return $this->id;
	}

	function delete()
	{
		$this->CI->storage->delete_element($this->id);
	}
	
	/**
	 * Remove a related cached URI File
	 *
	 * @access	public
	 * @param 	string
	 * @return	void
	 */
	function erase_cache()
	{
		$path = $this->CI->config->item('cache_path');

		$cache_path = ($path == '') ? APPPATH.'cache/' : $path;

		if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
		{
			log_message('error', "Unable to write cache file: ".$cache_path);
			return;
		}
		
		$cache_files = array();
		$cache_files[] = $cache_path . md5(site_url($this->CI->storage->get_content_uri($this->parent_id)));

		/*
		 * Erase cache for descendants contents if spreaded element
		 */
		if ( $this->spread )
		{
			foreach ($this->CI->storage->get_content_descendants($this->parent_id) as $content )
			{
				$cache_files[] = $cache_path . md5(site_url($this->CI->storage->get_content_uri($content['id'])));
			}
		}

		foreach ( $cache_files as $cache_path )
		{
			if ( ! $fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE))
			{
				log_message('error', "Unable to write cache file: ".$cache_path);
				return;
			}
			unlink($cache_path);
		}
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
				$dropdown .= anchor($this->type_id, $this->CI->storage->get_element_type_name($this->type_id));
			}
			else
			{
				$dropdown .= anchor(key($types), current($types));
			}
		}
		else
		{
			$dropdown .= anchor('0', $this->CI->lang->line('elementar_new') . '...');
		}
		$dropdown .= div_open(array('class' => 'dropdown_items_listing_position'));
		$dropdown .= div_open(array('class' => 'dropdown_items_listing'));
		$dropdown_items = array();
		foreach ( $types as $type_id => $type )
		{
			$dropdown_items[] = anchor($type_id, $type, array('class' => 'dropdown_items_listing_element_type_target'));
		}
		// "New" link
		$dropdown_items[] = anchor('0', $this->CI->lang->line('elementar_new') . '...', array('id' => 'element_type_create', 'class' => 'dropdown_items_listing_element_type_target'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}


}

/* End of file Element.php */
/* Location: ./application/libraries/Element.php */
