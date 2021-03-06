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
	private $uri;

	/*
	 * Author account id
	 */
	private $account_id;
	
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
		$this->uri = $this->CI->storage->get_content_uri($this->id);		
		$this->account_id = $this->CI->storage->get_content_account_id($this->id);

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
	
	function get_account_id()
	{
		return $this->account_id;
	}

	function set_account_id($value)
	{
		$this->account_id = $value;
	}
	
	function get_sname()
	{
		return $this->sname;
	}

	function set_sname($value)
	{
		$this->sname = $value;
	}
	
	function get_uri()
	{
		return $this->uri;
	}

	function set_uri($value)
	{
		$this->uri = $value;
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
		/*
		 * Avoid duplicated sname by appending content id to it
		 */
		$id = $this->CI->storage->get_content_by_sname($this->sname);
		if ( (bool) $id && (int) $id != $this->id )
		{
			$this->sname .= $this->id;
			$this->rename();
		}
		$this->CI->storage->put_content_parent($this->id, $this->parent_id);
		$this->CI->storage->put_content_status($this->id, $this->status);
		$this->account_id = $this->CI->session->userdata('account_id');
		$this->CI->storage->put_content_account_id($this->id, $this->account_id);
		$this->load();
		return $this->id;
	}

	function delete()
	{
		$this->CI->storage->delete_content($this->id);
	}
	
	/**
	 * Remove a cached URI and related files
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
			log_message('error', "Unable to erase cache file: ".$cache_path);
			return;
		}

		// Main cache files
		$cache_files = array(
			$cache_path . md5(site_url($this->uri)),
			$cache_path . md5(site_url('/main/css/' . $this->id)),
			$cache_path . md5(site_url('/main/javascript/' . $this->id)),
			$cache_path . md5(site_url('/sitemap.xml')),
			$cache_path . md5(site_url('/feed/' . $this->id)),
			$cache_path . md5(site_url('/feed/1')),
			$cache_path . md5(site_url('/feed/'))
		);

		// Cache files in other languages
		list($lang, $lang_avail) = $this->CI->common->load_i18n_settings();
		foreach ( $lang_avail as $lang_code => $lang_name )
		{
			$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . $this->uri));
		}

		// Main page cache
		$cache_files[] = $cache_path . md5(site_url('/'));
		foreach ( $lang_avail as $lang_code => $lang_name )
		{
			$cache_files[] = $cache_path . md5(site_url('/' . $lang_code));
		}

		// Feed files in other languages
		foreach ( $lang_avail as $lang_code => $lang_name )
		{
			$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . '/feed/' . $this->id));
			$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . '/feed/1'));
			$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . '/feed/'));
		}

		// Upper contents feeds
		$parent_id = $this->CI->storage->get_content_parent_id($this->id);
		while ( (int) $parent_id > 1 )
		{
			$cache_files[] = $cache_path . md5(site_url('/feed/' . $parent_id));
			// Feed file in other languages
			foreach ( $lang_avail as $lang_code => $lang_name )
			{
				$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . '/feed/' . $parent_id));
			}
			$parent_id = $this->CI->storage->get_content_parent_id($parent_id);
		}

		// Remove relative's partials cache
		if ( $this->id != 1 )
		{
			// Remove brother's cache from my brothers
			$brothers = $this->CI->storage->get_contents_by_parent($this->parent_id);
			$brothers = ( (bool) $brothers ) ? $brothers : array();
			// Include myself
			$brothers = array_merge(array('id' => (int) $this->id), $brothers);
			foreach ( $brothers as $brother )
			{
				$brother_uri = '/main/partial/brothers/' . $brother['id'];
				// Count ocurrences of brothers tag in template
				$template = $this->CI->storage->get_template($brother['id']);
				$html = $template['html'];
				$pair_count = 0;
				while ( preg_match("|" . '{brothers}' . "(.+?)". '{/brothers}' . "|s", $html, $match) )
				{
					$pos = strpos($html, $match['0']);
					$html = substr_replace($html, '', $pos, strlen($match['0']));
					$cache_files[] = $cache_path . md5(site_url($brother_uri . '/' . $pair_count));
					// Cache files in other languages
					foreach ( $lang_avail as $lang_code => $lang_name )
					{
						$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . $brother_uri . '/' . $pair_count));
					}
					$pair_count++;
				}
			}
			// Erase children cache for own parent
			$children_uri = '/main/partial/children/' . $this->parent_id;
			// Count ocurrences of children tag in template
			$parent_template = $this->CI->storage->get_template($this->parent_id);
			$html = $parent_template['html'];
			$pair_count = 0;
			while ( preg_match("|" . '{children}' . "(.+?)". '{/children}' . "|s", $html, $match) )
			{
				$pos = strpos($html, $match['0']);
				$html = substr_replace($html, '', $pos, strlen($match['0']));
				$cache_files[] = $cache_path . md5(site_url($children_uri . '/' . $pair_count));
				// Cache files in other languages
				foreach ( $lang_avail as $lang_code => $lang_name )
				{
					$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . $children_uri . '/' . $pair_count));
				}
				$pair_count++;
			}
		}
		
		// Erase partial for index field
		$index_uri = '/main/partial/index/' . $this->id;
		// Count ocurrences of index tag in template
		$html = $this->template_html;
		$pair_count = 0;
		while ( preg_match("|" . '{index}' . "(.+?)". '{/index}' . "|s", $html, $match) )
		{
			$pos = strpos($html, $match['0']);
			$html = substr_replace($html, '', $pos, strlen($match['0']));
			$cache_files[] = $cache_path . md5(site_url($index_uri . '/' . $pair_count));
			// Cache files in other languages
			foreach ( $lang_avail as $lang_code => $lang_name )
			{
				$cache_files[] = $cache_path . md5(site_url('/' . $lang_code . $index_uri . '/' . $pair_count));
			}
			$pair_count++;
		}

		// Erase all selected cache files
		foreach ( $cache_files as $cache_path )
		{
			if ( ! $fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE))
			{
				log_message('error', "Unable to erase cache file: ".$cache_path);
			}
			else
			{
				unlink($cache_path);
			}
		}
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
				$dropdown .= anchor($this->type_id, $this->CI->storage->get_content_type_name($this->type_id));
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
			$dropdown_items[] = anchor($type_id, $type, array('class' => 'dropdown_items_listing_content_type_target'));
		}
		// "New" link
		$dropdown_items[] = anchor('0', $this->CI->lang->line('elementar_new') . '...', array('id' => 'content_type_create', 'class' => 'dropdown_items_listing_content_type_target'));
		$dropdown .= ul($dropdown_items, array('class' => 'dropdown_items_listing_targets'));
		$dropdown .= div_close();
		$dropdown .= div_close();
		$dropdown .= div_close();
		return $dropdown;
	}

}

/* End of file Content.php */
/* Location: ./application/libraries/Content.php */
