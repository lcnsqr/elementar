<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * i18n settings
	 */
	private $LANG = 'por';
	private $LANG_AVAIL = array();
	private $URI_PREFIX = '';

	function __construct($params)
	{
		$this->CI =& get_instance();
		
		/*
		 * i18n: Default language
		 */
		$this->LANG = $params['lang'];
		$this->LANG_AVAIL = $params['lang_avail'];
		$this->URI_PREFIX = $params['uri_prefix'];

		/*
		 * BUG: By default
		 * Code Igniter don't load
		 * in libraries MY_* helpers 
		 * requested from controller,
		 * so we need to load it again
		 */
		$this->CI->load->helper('html');
	}
	
	function ajax_response($response)
	{
		// execution time
		$elapsed = array('elapsed_time' => $this->CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
		$response = array_merge($response, $elapsed);
		
		$this->CI->output->set_header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		$this->CI->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		$this->CI->output->set_header("Cache-Control: post-check=0, pre-check=0");
		$this->CI->output->set_header("Pragma: no-cache");
		$this->CI->output->set_header("Content-type: application/json");
		
		$data = json_encode($response);
		$this->CI->output->set_output($data);
	}
	
	/*
	 * Render backend html columns with label and input(s)
	 */
	function render_form_field($type, $name, $sname, $description = NULL, $value = NULL, $i18n)
	{
		$field = div_open(array('class' => 'form_content_field'));
		$field .= div_open(array('class' => 'form_window_column_label'));
		$attributes = array('class' => 'field_label');
		$field .= form_label($name, $sname, $attributes);
		$field .= br(1);
		$field .= div_close('<!-- form_window_column_label -->');
		$field .= div_open(array('class' => 'form_window_column_input'));
		
		/*
		 * Check multilanguage
		 */
		if ( (bool) $i18n )
		{
			/*
			 * Value array index is language code
			 */
			$value = json_decode($value, TRUE);

			/*
			 * One tab link for each language
			 */
			$input_lang_tab_links = array();
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				$current = ( $this->LANG == $lang_code ) ? ' current' : '';
				$input_lang_tab_links[] = anchor($lang_name, array('href' => $lang_code, 'class' => 'input_lang_tab_link' . $current));
			}
			$field .= div_open(array('class' => 'input_lang_menu'));
			$field .= ul($input_lang_tab_links);
			//field .= hr(array('class' => 'clear'));
			$field .= div_close('<!-- input_lang_menu -->');
			/*
			 * The input fields for each language
			 */
			foreach ( $this->LANG_AVAIL as $lang_code => $lang_name )
			{
				/*
				 * If language index does not exist, set empty
				 */
				$value = ( $value == NULL ) ? array() : $value;
				$lang_value = ( array_key_exists($lang_code, $value) ) ? $value[$lang_code] : '';
	
				$attributes = array('class' => 'input_lang_field input_lang_field_' . $lang_code);
				if ( $this->LANG == $lang_code ) 
				{
					$attributes['style'] = 'display: block;';
				}
				$field .= div_open($attributes);
				$field .= $this->_render_form_custom_field($type, $name, $sname . '_' . $lang_code, $description, $lang_value);
				$field .= div_close('<!-- input_lang_field -->');
			}
		}
		else
		{
			/*
			 * No multilanguage, no language tabs
			 */
			$attributes = array('class' => 'input_lang_field');
			$attributes['style'] = 'display: block;';
			$field .= div_open($attributes);
			$field .= $this->_render_form_custom_field($type, $name, $sname, $description, $value);
			$field .= div_close('<!-- input_lang_field -->');
			 
		}
		
		$field .= div_close('<!-- form_window_column_input -->');
		$field .= div_close('<!-- .form_content_field -->');
		return $field;
	}

	function _render_form_custom_field($type, $name, $sname, $description, $value)
	{
		/*
		 * Adequate input to field type
		 */
		switch ( $type )
		{
			case "name" :
			case "line" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = div_open(array('class' => 'text_input_container'));
			$field .= form_input($attributes);
			$field .= div_close();
			$field .= hr(array('class' => 'clear'));
			break;

			case "password" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = div_open(array('class' => 'text_input_container'));
			$field .= form_password($attributes);
			$field .= div_close();
			$field .= hr(array('class' => 'clear'));
			break;

			case "p" :
			case "hypertext" :
			case "textarea" :
			$attributes = array(
				'class' => 'noform ' . $type,
				'name' => $sname,
				'id' => $sname,
				'rows' => 16,
				'cols' => 32,
				'value' => $value
			);
			$field = form_textarea($attributes);
			break;
			
			case "menu" :
			$field = div_open(array('class' => 'menu_field'));
			/*
			 * Render menu field
			 */
			$data = array(
				'menu' => ( $value != '' ) ? json_decode($value, TRUE) : array(), 
				'targets' => $this->_render_target_listing()
			);
			
			/*
			 * Localized texts
			 */
			$data['elementar_menu_name'] = $this->CI->lang->line('elementar_menu_name');
			$data['elementar_menu_target'] = $this->CI->lang->line('elementar_menu_target');
			$data['elementar_menu_add'] = $this->CI->lang->line('elementar_menu_add');
			$data['elementar_menu_move_up'] = $this->CI->lang->line('elementar_menu_move_up');
			$data['elementar_menu_move_down'] = $this->CI->lang->line('elementar_menu_move_down');
			$data['elementar_menu_delete'] = $this->CI->lang->line('elementar_menu_delete');
			$data['elementar_menu_new_above'] = $this->CI->lang->line('elementar_menu_new_above');
			$data['elementar_menu_new_below'] = $this->CI->lang->line('elementar_menu_new_below');			
			
			$field .= $this->CI->load->view('backend/backend_content_menu_field', $data, true);
			/*
			 * The actual field
			 */
			$attributes = array(
				'class' => 'noform menu_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();
			break;

			case "file" : 
			$field = div_open(array('class' => 'file_field'));
			$attributes = array(
				'class' => 'noform ' . $type,
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			/*
			 * File field View variables
			 */
			$data = array();
			$data['input_name'] = $sname;
			if ( $value != '' )
			{
				$attributes = json_decode($value, TRUE);
				$data['thumbnail'] = $attributes['thumbnail'];
				$data['file_description'] = $attributes['title'];
				$data['file_uri'] = $attributes['uri'];
				$data['mime'] = $attributes['mime'];
				$data['size'] = $attributes['size'];
			}
			else
			{
				$data['thumbnail'] = '';
				$data['file_description'] = '';
				$data['file_uri'] = '';
				$data['mime'] = '';
				$data['size'] = '';
			}

			/*
			 * Localized texts
			 */
			$data['elementar_file_description'] = $this->CI->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->CI->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->CI->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->CI->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->CI->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->CI->lang->line('elementar_file_erase');

			$field .= $this->CI->load->view("backend/backend_content_file_field", $data, TRUE);
			$field .= div_close();
			break;

			case "file_gallery" : 
			$field = div_open(array('class' => 'file_gallery_field'));
			/*
			 * Input holds json data with 
			 * file uri, alt text, width, height,
			 * thumbnail and size
			 */
			$attributes = array(
				'class' => 'noform file_gallery_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			/*
			 * Render gallery field
			 */
			$data = array(
				'gallery' => ($value != '') ? json_decode($value, TRUE) : array()
			);

			/*
			 * Localized texts
			 */
			$data['elementar_file_description'] = $this->CI->lang->line('elementar_file_description');
			$data['elementar_file_uri'] = $this->CI->lang->line('elementar_file_uri');
			$data['elementar_file_type'] = $this->CI->lang->line('elementar_file_type');
			$data['elementar_file_size'] = $this->CI->lang->line('elementar_file_size');
			$data['elementar_file_browse'] = $this->CI->lang->line('elementar_file_browse');
			$data['elementar_file_erase'] = $this->CI->lang->line('elementar_file_erase');
			$data['elementar_file_add'] = $this->CI->lang->line('elementar_file_add');
			$data['elementar_file_move_up'] = $this->CI->lang->line('elementar_file_move_up');
			$data['elementar_file_move_down'] = $this->CI->lang->line('elementar_file_move_down');
			$data['elementar_file_delete'] = $this->CI->lang->line('elementar_file_delete');
			$data['elementar_file_new_above'] = $this->CI->lang->line('elementar_file_new_above');
			$data['elementar_file_new_below'] = $this->CI->lang->line('elementar_file_new_below');

			$field .= $this->CI->load->view("backend/backend_content_file_gallery_field", $data, TRUE);
			$field .= div_close();
			break;

			case "youtube_gallery" :
			$field = div_open(array('class' => 'youtube_gallery_field'));
			/*
			 * Render youtube_gallery field
			 */
			$data = array(
				'videos' => json_decode($value, TRUE) // decode as associative array
			);

			/*
			 * Localized texts
			 */
			$data['elementar_youtube_description'] = $this->CI->lang->line('elementar_youtube_description');
			$data['elementar_youtube_url'] = $this->CI->lang->line('elementar_youtube_url');
			$data['elementar_youtube_add'] = $this->CI->lang->line('elementar_youtube_add');
			$data['elementar_youtube_move_up'] = $this->CI->lang->line('elementar_youtube_move_up');
			$data['elementar_youtube_move_down'] = $this->CI->lang->line('elementar_youtube_move_down');
			$data['elementar_youtube_delete'] = $this->CI->lang->line('elementar_youtube_delete');
			$data['elementar_youtube_new_above'] = $this->CI->lang->line('elementar_youtube_new_above');
			$data['elementar_youtube_new_below'] = $this->CI->lang->line('elementar_youtube_new_below');

			$field .= $this->CI->load->view('backend/backend_content_youtube_gallery_field', $data, true);
			/*
			 * The actual field
			 */
			$attributes = array(
				'class' => 'noform youtube_gallery_actual_field',
				'type' => 'hidden',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field .= form_input($attributes);
			$field .= div_close();
			break;

			case "target" :
			$attributes = array(
				'class' => 'noform',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = form_input($attributes);
			break;

			case "index" :
			$attributes = array(
				'class' => 'noform index_field',
				'name' => $sname,
				'id' => $sname,
				'value' => $value
			);
			$field = form_input($attributes);
			$field .= $this->_render_contents_listing();
			break;
		}
		return $field;
	}
	
	function _render_target_listing()
	{
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->CI->lang->line('elementar_inside_targets') . '</strong>');
		/*
		 * Conteúdos
		 */
		foreach ( $this->CI->storage->get_contents() as $content )
		{
			$listing[] = $this->breadcrumb_content($content['id']);
		}
		/*
		 * Addons
		 */
		foreach ( $this->load_addons() as $addon ) 
		{
			$listing[] = $this->breadcrumb_path($addon['uri']);
			// Methods
			if ( count($addon['methods']) > 0 )
			{
				foreach ( $addon['methods'] as $method ) 
				{
	            // skip methods that begin with '_'
	            if ( substr($method, 0, 1) == '_' ) continue;
	
	            // skip default method
	            if ( $method == 'index' || $method == 'main' ) continue;
	            
					// skip XHR (ajax) methods
	            if ( substr($method, 0, 4) == 'xhr_' ) continue;
	
	            // skip old-style constructor
	            if ( strtolower($method) == strtolower($addon['name']) ) continue;

					$listing[] = $this->breadcrumb_path($addon['uri'] . '/' . $method);
				}				
			}
		}
		$targets = div_open(array('class' => 'dropdown_items_listing_position'));
		$targets .= div_open(array('class' => 'dropdown_items_listing'));
		$attributes = array(
			'class' => 'dropdown_items_listing_targets'
		);
		$targets .= ul($listing, $attributes);
		$targets .= div_close();
		$targets .= div_close();
		return $targets;
	}

	function _render_contents_listing()
	{
		/*
		 * dropdown target listing
		 */
		$listing = array();
		$listing[] = paragraph('<strong>' . $this->CI->lang->line('elementar_contents') . '</strong>');
		/*
		 * Conteúdos
		 */
		foreach ( $this->CI->storage->get_contents_by_parent() as $content )
		{
			$content_name = json_decode($content['name'], TRUE);
			$listing[] = anchor($content_name[$this->LANG], array('href' => $content['id']));
		}
		$contents = div_open(array('class' => 'dropdown_items_listing_position'));
		$contents .= div_open(array('class' => 'dropdown_items_listing'));
		$attributes = array(
			'class' => 'dropdown_items_listing_contents'
		);
		$contents .= ul($listing, $attributes);
		$contents .= div_close();
		$contents .= div_close();
		return $contents;
	}

	/*
	 * Is an URI the current location ?
	 */
	function _uri_is_current($uri)
	{
		if ( $this->CI->uri->total_segments() == 0 )
		{
			/*
			 * No URI segments, it's the home page
			 */
			if ( '/' == $uri ) 
			{
				return TRUE;
			}
		}
		
		/*
		 * trim out trailing slash
		 */
		$current_uri = '/' . $this->CI->uri->uri_string();
		if ( substr($current_uri, -1) == '/' )
		{
			$current_uri = substr($current_uri, 0, -1);
		}

		/*
		 * Check for localized home
		 */
		if ( $this->CI->uri->total_segments() == 1 )
		{
			if ( $this->URI_PREFIX == $current_uri && $current_uri . '/' == $uri )
			{
				return TRUE;
			}
		} 
		
		/*
		 * Non root URI
		 */
		if ( $uri != '/' )
		{
			if ( $current_uri == $uri )
			{
				return TRUE;
			}
		}

		/*
		 * Defaults to false
		 */
		return FALSE;
	}

	/*
	 * Render menu
	 */
	function _make_menu($menu)
	{
		if ( ! is_array($menu) )
		{
			return NULL;
		}
		/*
		 * Build menu items links
		 */
		$menu_links = array();
		while ( $menu_item = current($menu) )
		{
			/*
			 * Mark current menu
			 */
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $menu_item['target']) ) ? 'menu_item current' : 'menu_item';
			/*
			 * Set first and last menu for styling
			 */
			$class .= ( key($menu) == 0 ) ? ' first' : '';
			$class .= ( key($menu) == ( count($menu) - 1 ) ) ? ' last' : '';
			$attributes = array(
				'href' => $this->URI_PREFIX . $menu_item['target'],
				'title' => htmlspecialchars( $menu_item['name'] ),
				'class' => $class
			);
			$link = anchor(htmlspecialchars($menu_item['name']), $attributes);
			$link = '<a href="'.$this->URI_PREFIX . $menu_item['target'].'" title="'.htmlspecialchars( $menu_item['name'] ).'" class="'.$class.'">'.htmlspecialchars($menu_item['name']).'</a>';
			$submenu = $menu_item['menu'];
			if ( ! (bool) $submenu )
			{
				/*
				 * No submenu
				 */
				$menu_links[] = array(
					'link' => $link,
					'submenu' => NULL
				);
			}
			else
			{
				/*
				 * Recursive 
				 * through submenu
				 */
				$menu_links[] = array(
					'link' => $link,
					'submenu' => $this->_make_menu($submenu)
				);
			}
			next($menu);
		}
		return $menu_links;
	}

	/**
	 * Clickable path (breadcrumb) for content
	 */
	function breadcrumb_content($content_id, $sep = "&raquo;", $previous = "")
	{
		$breadcrumb = "";
		
		/*
		 * With content_id = 1, just return home link
		 */
		if ( (int)$content_id === 1 )
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" ;
			return $breadcrumb;
		}

		$content = (array) $this->CI->storage->get_content($content_id);
		
		if ( count( $content ) > 0 )
		{
			/*
			 * Localized name
			 */
			$names = json_decode($content['name'], TRUE);
			$name = $names[$this->LANG];
			$content_uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($content_id);
			if ( (int) $content['parent_id'] > 1 ) 
			{
				$breadcrumb = $this->breadcrumb_content($content['parent_id'], $sep, " $sep <a href=\"" . $content_uri . "\">" . $name . "</a>" . $previous);
			}
			else
			{
				$breadcrumb = '<a href="' . $this->URI_PREFIX . '/" >' . $this->CI->config->item('site_name') . "</a> $sep <a href=\"" . $content_uri . "\">" . $name . "</a>" . $previous;
			}
		}
		return $breadcrumb;
	}

	/**
	 * Clickable path (breadcrumb) for element
	 */
	function breadcrumb_element($element_id, $sep = "&raquo;")
	{
		$breadcrumb = "";
		
		$element = $this->CI->storage->get_element($element_id);
		
		if ( count($element) > 0 )
		{
			/*
			 * Localized name
			 */
			$name = $element['name'];
			$element_uri = $this->URI_PREFIX . $this->CI->storage->get_content_uri($element['parent_id']) . "#" . $element['sname'];
			if ( (bool) $element['parent_id'] )
			{ 
				$breadcrumb = $this->breadcrumb_content($element['parent_id'], $sep, " $sep <a href=\"" . $element_uri . "\" >" . $name . "</a>");
			}
			else
			{
				$breadcrumb = '<a href="' . $this->URI_PREFIX . '/" >' . $this->CI->config->item('site_name') . "</a> $sep <a href=\"/" . $element_uri . "\" >" . $name . "</a>";
			}
		}
		return $breadcrumb;
	}
	
	/*
	 * Generate breadcrumb from some path
	 */
	function breadcrumb_path($path, $sep = "&raquo;")
	{
		$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" ;
		$uri = "";
		
		/*
		 * With no path, just return home link
		 */
		if ( (bool) $path == FALSE )
		{
			return $breadcrumb;
		}

		$segments = explode("/", substr($path, 1));

		if ( count($segments) > 0 )
		{
			$breadcrumb .= " $sep ";
		}

		while ( $segment = current($segments) )
		{
			$uri .= "/" . $segment;
			$breadcrumb .= "<a href=\"" . $uri . "\" >" . $segment . "</a>" ;
			if ( next($segments) )
			{
				$breadcrumb .= " $sep ";
			}
		}
		return $breadcrumb;
	}

	function load_addons($ignore = NULL)
	{
		$ignore = ( NULL == $ignore ) ? array() : $ignore;
		$this->CI->load->helper('file');
		$addons = array();
		$addons_path = APPPATH.'addons/';
		foreach(get_dir_file_info($addons_path, TRUE) as $addon) 
		{
			/*
			 * Skip anything other than PHP files
			 */
			if ( substr($addon['name'], -4) == '.php' )
			{
				list($class, $ext) = explode('.', ucfirst($addon['name']));
				if ( in_array($class, $ignore) ) continue;

				if ( ! class_exists($class) ) 
				{ 
					/*
					 * Load addon
					 */
					include($addon['relative_path'] . $addon['name']);
				}
				$addons[] = array(
					'uri' => '/' . strtolower($class),
					'name'=> $class,
					'path' => $addon['relative_path'] . $addon['name'],
					'date' => $addon['date'],
					'methods' => get_class_methods($class)
				);
			}
		}
		return $addons;
	}

	function sitemap()
	{
		$urls = array();
		/*
		 * Database contents
		 */
		foreach ( $this->CI->storage->get_contents() as $content )
		{
			$priority = $this->CI->storage->get_meta_field($content['id'], 'priority');
			$uri = $this->CI->storage->get_content_uri($content['id']);
			$url = $this->CI->storage->get_meta_field($content['id'], 'url');
			if ( $url == '' )
			{
				/*
				 * Change "/home" to "/" or use default path to content
				 */
				$url = ( $uri == '/' . $this->CI->storage->get_content_sname(1) ) ? site_url('/') : site_url($uri);
			}
			$priority = ( (bool) $priority ) ? $priority : '0.5';
			$urls[] = array(
				'loc' => $url,
				'lastmod' => date("Y-m-d", strtotime($content['modified'])),
				'changefreq' => 'daily',
				'priority' => $priority
			);
		}
		/*
		 * Plugins
		 */
		foreach ( $this->load_addons() as $addon ) 
		{
			$urls[] = array(
				'loc' => site_url($addon['uri']),
				'lastmod' => date("Y-m-d", $addon['date']),
				'changefreq' => 'daily',
				'priority' => '0.5'
			);
			// Methods
			if ( count($addon['methods']) > 0 )
			{
				foreach ( $addon['methods'] as $method ) 
				{
	            // skip methods that begin with '_'
	            if ( substr($method, 0, 1) == '_' ) continue;
	
	            // skip default method
	            if ( $method == 'index' || $method == 'main' ) continue;
	            
					// skip XHR (ajax) methods
	            if ( substr($method, 0, 4) == 'xhr_' ) continue;
	
	            // skip old-style constructor
	            if ( strtolower($method) == strtolower($addon['name']) ) continue;
	
					$urls[] = array(
						'loc' => site_url($addon['uri'] . '/' . $method),
						'lastmod' => date("Y-m-d", $addon['date']),
						'changefreq' => 'daily',
						'priority' => '0.5'
					);
				}				
			}
		}
		$this->CI->output->set_header("Content-type: application/xml");
		$this->CI->output->append_output('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$this->CI->load->view('sitemap', array('urls' => $urls));
	}

	/**
	 * Convert non-ascii and other characters to ascii and underscores
	 * @param string $string Input string
	 * @return string Converted string
	 */
	function normalize_string($string)
	{
		$this->CI->load->helper('url');

		$acentos = array(
			'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
			'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
			'C' => '/&Ccedil;/',
			'c' => '/&ccedil;/',
			'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
			'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
			'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
			'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
			'N' => '/&Ntilde;/',
			'n' => '/&ntilde;/',
			'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
			'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
			'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
			'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
			'Y' => '/&Yacute;/',
			'y' => '/&yacute;|&yuml;/',
			'a.' => '/&ordf;/',
			'o.' => '/&ordm;/'
		);
		
		$string = htmlentities($string, ENT_QUOTES, "UTF-8");
		$string = preg_replace($acentos, array_keys($acentos), $string);               
		$string = trim($string);
		$string = strtolower(url_title($string));
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		return $string;
	}
	
	function render_field($field_attr, $field_value)
	{
		/*
		 * Check for multilanguage field
		 */
		if ( (bool) $field_attr['i18n'] )
		{
			/*
			 * Choose language
			 */
			$field_values = json_decode($field_value, TRUE);
			$field_value = $field_values[$this->LANG];
		}
		
		switch ( $field_attr['type'] )
		{
			case 'file' :
			$attributes = json_decode($field_value, TRUE);
			if ( count( $attributes ) > 0 )
			{
				/*
				 * Put attributes as a sub array
				 * so the nested fields can be parsed on 
				 * a template pair loop
				 */
				$nested = array();
				$nested[] = array(
					'uri' => (string) $attributes['uri'],
					'width' => (string) $attributes['width'],
					'height' => (string) $attributes['height'],
					'alt' => (string) $attributes['title'] 
				);
				return $nested;
			}
			break;

			case 'file_gallery' :
			$images = json_decode($field_value, TRUE);
			$gallery = array();
			foreach ( $images as $index => $image_item )
			{
				$image = json_decode($image_item, TRUE);
				if ( count( $image ) > 0 )
				{
					$gallery[] = array(
						'index' => (string) $index,
						'uri' => (string) $image['uri'],
						'width' => (string) $image['width'],
						'height' => (string) $image['height'],
						'alt' => (string) $image['title'] 
					);
				}
			}
			return $gallery;
			break;

			case 'youtube_gallery' :
			$videos = json_decode($field_value, TRUE);
			$gallery = array();
			foreach ( $videos as $index => $video )
			{
				if ( count( $video ) > 0 )
				{
					/*
					 * YouTube video id
					 */
					$video_url_segments = parse_url($video['url']);
					parse_str($video_url_segments['query'], $variables);
					$video_id = $variables['v'];
					$gallery[] = array(
						'index' => (string) $index,
						'description' => (string) $video['description'],
						'url' => (string) $video['url'],
						'video_id' => $video_id,
						'screenshot_large' => 'http://img.youtube.com/vi/' . $video_id . '/0.jpg',
						'screenshot_small' => 'http://img.youtube.com/vi/' . $video_id . '/2.jpg'
					);
				}
			}
			return $gallery;
			break;

			case 'menu' :
			/*
			 * Generate links with semantic classes
			 */
			return $this->_make_menu(json_decode($field_value, TRUE));
			break;
			
			case 'index' :
			/*
			 * Index listing
			 */
			$content_id = $field_value;
			$index = array();
			/*
			 * localized parent title
			 */
			$titles = json_decode($this->CI->storage->get_content_name($content_id), TRUE);
			$content_name = $titles[$this->LANG];
			$content_uri = $this->CI->storage->get_content_uri($content_id);
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $content_uri) ) ? 'index_item current' : 'index_item';
	
			$attributes = array(
				'href' => $this->URI_PREFIX . $content_uri,
				'title' => htmlspecialchars( $content_name ),
				'class' => $class
			);
			//$link = anchor(htmlspecialchars($menu_item['name']), $attributes);
			$link = '<a href="'.$this->URI_PREFIX . $content_uri.'" title="'.htmlspecialchars( $content_name ).'" class="'.$class.'">'.htmlspecialchars($content_name).'</a>';
			if ( $this->CI->storage->get_content_has_children($content_id, FALSE) )
			{
				$index[$link] = $this->_index_field($content_id);
			}
			else
			{
				$index[] = $link;
			}

			return ul($index);
			break;

			default:
			return (string) $field_value;
			break;
		}
	}
	
	function _index_field($content_id)
	{
		$index = array();
		$children = $this->CI->storage->get_content_children($content_id);
		$children = ( is_array($children) ) ? $children : array();
		foreach($children as $child)
		{
			$content_id = $child['id'];
			/*
			 * localized title
			 */
			$titles = json_decode($child['name'], TRUE);
			$content_name = $titles[$this->LANG];
			$content_uri = $this->CI->storage->get_content_uri($content_id);
			$class = ( $this->_uri_is_current($this->URI_PREFIX . $content_uri) ) ? 'index_item current' : 'index_item';
	
			$attributes = array(
				'href' => $this->URI_PREFIX . $content_uri,
				'title' => htmlspecialchars( $content_name ),
				'class' => $class
			);
			//$link = anchor(htmlspecialchars($menu_item['name']), $attributes);
			$link = '<span class="date">' . date('d/m/Y H:i:s', strtotime($child['modified'])) . '</span> <a href="'.$this->URI_PREFIX . $content_uri.'" title="'.htmlspecialchars( $content_name ).'" class="'.$class.'">'.htmlspecialchars($content_name).'</a>';
			if ( (bool) $child['children'] )
			{
				$index[$link] = $this->_index_field($content_id);
			}
			else
			{
				$index[] = $link;
			}
		}
		return $index;
	}

	function render_content($content_id = 1)
	{
		$content = array();
		
		// Default fields
		$content['breadcrumb'] = $this->breadcrumb_content($content_id);
		
		// Content fields
		$fields = $this->CI->storage->get_content_fields($content_id);
		foreach ($fields as $field)
		{
			$content[$field['sname']] = $this->render_field($field, $field['value']);			
		}
		
		/*
		 * Children contents listing
		 */
		$children = $this->CI->storage->get_contents_by_parent($content_id);
		if ( (bool) $children )
		{
			/*
			 * Rename keys to avoid conflict with parent variable names in template
			 */
			$children_variables = array();
			foreach ( $children as $child )
			{
				/*
				 * Localized name
				 */
				$names = json_decode($child['name'], TRUE);
				$children_variables[] = array(
					'id' => $child['id'],
					'sname' => $child['sname'],
					'name' => $names[$this->LANG],
					'uri' => $this->URI_PREFIX . $this->CI->storage->get_content_uri($child['id']),
					'children' => $child['children']
				);
			}
			$content['children'] = $children_variables;
		}

		/*
		 * Parent children contents → brothers :)
		 */
		if ( $content_id != 1 )
		{
			$parent_id = $this->CI->storage->get_content_parent_id($content_id);
			$brothers = $this->CI->storage->get_contents_by_parent($parent_id);
			if ( (bool) $brothers )
			{
				/*
				 * Rename keys to avoid conflict with parent variable names in template
				 */
				$brothers_variables = array();
				foreach ( $brothers as $brother )
				{
					/*
					 * Localized name
					 */
					$names = json_decode($brother['name'], TRUE);
					$brothers_variables[] = array(
						'id' => $brother['id'],
						'sname' => $brother['sname'],
						'name' => $names[$this->LANG],
						'uri' => $this->URI_PREFIX . $this->CI->storage->get_content_uri($brother['id']),
						'children' => $brother['children']
					);
				}
				$content['brothers'] = $brothers_variables;
			}
		}
		return $content;
	}

	function render_elements($content_id = 1) 
	{
		$elements = $this->CI->storage->get_elements_by_parent_spreaded($content_id);
		$data = array();
		foreach ($elements as $key => $element)
		{
			/*
			 * Localized name
			 */
			$names = json_decode($element['name'], TRUE);
			$element_id = $element['id'];
			$element_sname = $element['sname'];
			$element_name = $names[$this->LANG];
			$element_type_id = $element['type_id'];
			$element_type = $element['type'];
			
			/*
			 * Position in array
			 */
			if ( 0 == $key )
			{
				$lineup = 'first';
			}
			elseif ( count($elements) - 1 == $key )
			{
				$lineup = 'last';
			}
			else
			{
				$lineup = 'middle';
			}
			
			/*
			 * Initialize element type inner array
			 */
			if ( ! array_key_exists($element_type, $data) )
			{
				$data[$element_type] = array();
			}

			/*
			 * Render loop entries by element type sname
			 */
			$fields = $this->CI->storage->get_element_fields($element_id);

			/*
			 * To be added in the element type array
			 */
			$entry = array(
				'name' => $element_name,
				'sname' => $element_sname,
				'lineup' => $lineup
			);
			
			/*
			 * Custom fields
			 */
			foreach ($fields as $field)
			{
				/*
				 * Format field value depending on field type
				 */
				$rendered_value = $this->render_field($field, $field['value']);

				/*
				 * element type array item
				 */
				$entry[$field['sname']] = $rendered_value;
				
				// Add element direct access (without a template loop)
				// Adapt to template pseudo variables
				$data[$element['sname'] . '.sname'] = $element_sname;
				$data[$element['sname'] . '.name'] = $element_name;
				$data[$element['sname'] . '.' . $field['sname']] = $rendered_value;
			}
			/*
			 * Add element entry as a pseudo variable pair (loop)
			 */
			$data[$element_type][] = $entry;
		}
		return $data;
	}

}

/* End of file Common.php */
/* Location: ./application/controllers/Common.php */
