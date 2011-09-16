<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;

	function __construct()
	{
		$this->CI =& get_instance();

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
		else
		{
			/*
			 * Ignore root 
			 */
			if ( $uri != '/' )
			{
				$current_uri = '/' . $this->CI->uri->uri_string();
				if ( substr($current_uri, -1) == '/' )
				{
					/*
					 * trim out trailing slash
					 */
					$current_uri = substr($current_uri, 0, -1);
				}
				if ( $current_uri == $uri )
				{
					return TRUE;
				}
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
			$class = ( $this->_uri_is_current($menu_item['target']) ) ? 'menu_item current' : 'menu_item';
			/*
			 * Set first and last menu for styling
			 */
			$class .= ( key($menu) == 0 ) ? ' first' : '';
			$class .= ( key($menu) == ( count($menu) - 1 ) ) ? ' last' : '';
			$attributes = array(
				'href' => $menu_item['target'],
				'title' => htmlspecialchars( $menu_item['name'] ),
				'class' => $class
			);
			$link = anchor(htmlspecialchars($menu_item['name']), $attributes);
			$submenu = $menu_item['menu'];
			if ( ! (bool) $submenu )
			{
				/*
				 * No submenu
				 */
				$menu_links[] = $link;
			}
			else
			{
				/*
				 * Recursive 
				 * through submenu
				 */
				$menu_links[$link] = $this->_make_menu($submenu);
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

		$content = (array) $this->CI->crud->get_content($content_id);
		
		if ( count( $content ) > 0 )
		{
			$content_uri = $this->CI->crud->get_content_uri($content_id);
			if ( (int) $content['parent_id'] > 1 ) 
			{
				$breadcrumb = $this->breadcrumb_content($content['parent_id'], $sep, " $sep <a href=\"" . $content_uri . "\">" . $content['name'] . "</a>" . $previous);
			}
			else
			{
				$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a> $sep <a href=\"" . $content_uri . "\">" . $content['name'] . "</a>" . $previous;
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
		
		$element = $this->CI->crud->get_element($element_id);
		
		if ( count($element) > 0 )
		{
			$element_uri = $this->CI->crud->get_content_uri($element['parent_id']) . "#" . $element['sname'];
			if ( (bool) $element['parent_id'] )
			{ 
				$breadcrumb = $this->breadcrumb_content($element['parent_id'], $sep, " $sep <a href=\"" . $element_uri . "\" >" . $element['name'] . "</a>");
			}
			else
			{
				$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a> $sep <a href=\"/" . $element_uri . "\" >" . $element['name'] . "</a>";
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

    function get_controller_methods($class = null)
    {
        // Use the PHP5 Reflection class to introspect the controller
        $controller = new ReflectionClass($class);
        
		$data = array();
        foreach($controller->getMethods() as $method)
        {
            // skip methods that begin with '_'
            if(substr($method->name, 0, 1) == '_') continue;

            // skip globally ignored names
            //if(in_array(strtolower($method->name), $this->ignore['*'])) continue;

            // skip ignored controller methods
            //if(isset($this->ignore[strtolower($class)]) AND in_array(strtolower($method->name), (array) $this->ignore[strtolower($class)])) continue;

            // skip index page
            if($method->name == 'index') continue;
            
            // skip get_instance method
            if($method->name == 'get_instance') continue;

			// skip XHR (ajax) methods
            if(substr($method->name, 0, 4) == 'xhr_') continue;

            // skip old-style constructor
            if(strtolower($method->name) == strtolower($class)) continue;

            // skip methods that aren't public
            if(!$method->isPublic()) continue;

            // build link data for parser class
            $data[] = array(
                'uri' => strtolower('/' . $class . '/' . $method->name),
                'name'=> ucwords(strtr($method->name, array('_'=>' '))),
            );
        }

        return $data;
	}

	function controllers($ignore = NULL)
	{
		$this->CI->load->helper('file');
		
		$data = array();
		$controllers_path = APPPATH.'controllers/';
		foreach(get_dir_file_info($controllers_path, TRUE) as $controller) 
		{
			// skip anything other than PHP files
			if ( substr($controller['name'], -4) == '.php' )
			{
				list($class, $ext) = explode('.', ucfirst($controller['name']));
				if ( in_array($class, $ignore) ) continue;
				//if(isset($this->ignore[strtolower($class)]) AND $this->ignore[strtolower($class)] == '*') continue;    // skip controllers marked as 'ignore'
				if(!class_exists($class)) { 
					include($controller['relative_path'] . '/' . $controller['name']);  // include the class for access
				}
				$data[] = array(
					'uri' => '/' . strtolower($class),
					'name'=> $class,
					'date' => $controller['date'],
					'methods' => $this->get_controller_methods($class)
				);
			}
		}
		return $data;
	}

	function sitemap()
	{
		$urls = array();
		/*
		 * Database contents
		 */
		foreach ( $this->CI->crud->get_contents() as $content )
		{
			$priority = $this->CI->crud->get_meta_field($content['id'], 'priority');
			$uri = $this->CI->crud->get_content_uri($content['id']);
			$url = $this->CI->crud->get_meta_field($content['id'], 'url');
			if ( $url == '' )
			{
				/*
				 * Change "/home" to "/" or use default path to content
				 */
				$url = ( $uri == '/home' ) ? site_url('/') : site_url($uri);
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
		 * Other controllers
		 */
		foreach ( $this->controllers(array('Main','Rss','User')) as $url ) 
		{
			$urls[] = array(
				'loc' => site_url($url['uri']),
				'lastmod' => date("Y-m-d", $url['date']),
				'changefreq' => 'daily',
				'priority' => '0.5'
			);
			// Controller methods
			if ( count($url['methods']) > 0 )
			{
				foreach ( $url['methods'] as $method ) 
				{
					$urls[] = array(
						'loc' => site_url($method['uri']),
						'lastmod' => date("Y-m-d", $url['date']),
						'changefreq' => 'daily',
						'priority' => '0.5'
					);
				}				
			}
		}
		$this->CI->output->set_header("Content-type: application/xml");
		$this->CI->load->view('sitemap', array('urls' => $urls));
	}

	/**
	 * Formulário para envio de imagem no campo imagem
	 * @param string $field_name Nome real (hidden input)
	 * @param integer $image_id Id de imagem armazenado 
	 * @return HTML content
	 */
	function render_form_upload_image($field_sname, $image_id = NULL)
	{
		$form_upload_session = $this->CI->crud->put_upload_session();
		
		$hidden = array('form_upload_session' => $form_upload_session, 'field_sname' => $field_sname);
		
		$attributes = array(
			'target' => "iframeUpload_" . $form_upload_session,
			'name' => "upload_image_" . $form_upload_session,
			'id' => "upload_image_" . $form_upload_session,
			'class' => "upload_image"
		);
		$form = form_open_multipart("/admin/upload/send_image", $attributes, $hidden);
		
		$attributes = array(
			'name' => "upload_image_field",
			'id' => "upload_image_field_" . $form_upload_session
		);
		
		/*
		 * IE change event bug
		 */
		$this->CI->load->library('user_agent');
		if( $this->CI->agent->browser() == 'Internet Explorer' )
		{
			$attributes['onchange'] = 'this.form.submit(); this.blur();';
		}
		
		$form .= form_label("Escolha a imagem", $attributes['id']);
		$form .= br(1);
		$form .= form_upload($attributes);

		/*
		$form .= br(1);
		$form .= form_submit("submit", "Enviar imagem");
		*/
		
		$form .= form_close();
		
		$data['form_upload'] = $form;
		$data['form_upload_session'] = $form_upload_session;
		
		$data['img_url'] = $this->CI->crud->get_image_uri_thumb($image_id);

		/*
		 * Título (alt text)
		 */
		$attributes = array(
			'class' => 'noform',
			'name' => $field_sname . '_title',
			'id' => $field_sname . '_title',
			'value' => $this->CI->crud->get_image_title($image_id)
		);
		$data['input_title'] = form_label("Título da imagem", $field_sname . '_title');
		$data['input_title'] .= br(1);
		$data['input_title'] .= form_input($attributes);
		
		return $this->CI->load->view("admin/admin_content_upload_image", $data, TRUE);
		
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
		$string = strtolower(url_title($string, 'underscore'));
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		return $string;
	}
	
	function render_field($field_type, $field_value)
	{
		switch ( $field_type )
		{
			case 'img' :
			$file_id = $field_value;
			$image = (array) $this->CI->crud->get_image($file_id);
			if ( count( $image ) > 0 )
			{
				/*
				 * Mark as composite field
				 */
				return array(
					'_composite' => TRUE,
					'uri' => (string) $image['uri'],
					'width' => (string) $image['width'],
					'height' => (string) $image['height'],
					'alt' => (string) $image['alt'] 
				);
			}
			else 
			{
				return array(
					'_composite' => TRUE,
					'uri' => '',
					'width' => '',
					'height' => '',
					'alt' => '' 
				);
			}
			break;

			case 'menu' :
			/*
			 * Render as HTML unordered list
			 */
			return ul($this->_make_menu(json_decode($field_value, TRUE)));
			break;
			
			default:
			return (string) $field_value;
			break;
		}
	}

	function render_content($content_id = 1)
	{
		$content = array();
		
		// Default fields
		$content['breadcrumb'] = $this->breadcrumb_content($content_id);
		
		// Content fields
		$fields = $this->CI->crud->get_content_fields($content_id);
		foreach ($fields as $field)
		{
			$content[$field['sname']] = $this->render_field($field['type'], $field['value']);			
		}
		
		/*
		 * Children contents
		 */
		$children = $this->CI->crud->get_contents_by_parent($content_id);
		if ( (bool) $children )
		{
			/*
			 * Rename keys to avoid conflict with parent variable names in template
			 */
			$children_variables = array();
			foreach ( $children as $child )
			{
				$children_variables[] = array(
					'children.id' => $child['id'],
					'children.sname' => $child['sname'],
					'children.name' => $child['name'],
					'children.uri' => $this->CI->crud->get_content_uri($child['id']),
					'children.children' => $child['children']
				);
			}
			$content['children'] = $children_variables;
		}
		else
		{
			$content['children'][] = array(
				'children.id' => '',
				'children.sname' => '',
				'children.name' => '',
				'children.uri' => '',
				'children.children' => ''
			);
		}

		/*
		 * Parent children contents → brothers :)
		 */
		if ( $content_id != 1 )
		{
			$parent_id = $this->CI->crud->get_content_parent_id($content_id);
			$brothers = $this->CI->crud->get_contents_by_parent($parent_id);
			if ( (bool) $brothers )
			{
				/*
				 * Rename keys to avoid conflict with parent variable names in template
				 */
				$brothers_variables = array();
				foreach ( $brothers as $brother )
				{
					$brothers_variables[] = array(
						'brothers.id' => $brother['id'],
						'brothers.sname' => $brother['sname'],
						'brothers.name' => $brother['name'],
						'brothers.uri' => $this->CI->crud->get_content_uri($brother['id']),
						'brothers.children' => $brother['children']
					);
				}
				$content['brothers'] = $brothers_variables;
			}
			else
			{
				$content['brothers'][] = array(
					'brothers.id' => '',
					'brothers.sname' => '',
					'brothers.name' => '',
					'brothers.uri' => '',
					'brothers.children' => ''
				);
			}
		}
		return $content;
	}

	function render_elements($content_id = 1) 
	{
		$elements = $this->CI->crud->get_elements_by_parent_spreaded($content_id);
		$data = array();
		foreach ($elements as $element)
		{
			$element_id = $element['id'];
			$element_name = $element['name'];
			$element_type_id = $element['type_id'];
			$element_type = $element['type'];
			
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
			$fields = $this->CI->crud->get_element_fields($element_id);

			/*
			 * To be added in the element type array
			 */
			$entry = array($element_type . '.name' => $element_name);
			
			/*
			 * Custom fields
			 */
			foreach ($fields as $field)
			{
				/*
				 * Format field value depending on field type
				 */
				$rendered_value = $this->render_field($field['type'], $field['value']);

				/*
				 * element type array item
				 */
				if ( is_array($rendered_value) )
				{
					foreach ( $rendered_value as $key => $value )
					{
						$entry[$element_type . '.'. $field['sname'] . '.' . $key] = $value ;
					}
				}
				else
				{
					$entry[$element_type . '.'. $field['sname']] = $rendered_value;
				}
				
				// Add element direct access (without a template loop)
				if ( ! array_key_exists($element['sname'], $data) )
				{
					// Adapt to template pseudo variables
					$data[$element['sname'] . '.name'] = $element_name;
					if ( is_array($rendered_value) )
					{
						foreach ( $rendered_value as $key => $value )
						{
							$data[$element['sname'] . '.' . $field['sname'] . '.' . $key] = $value ;
						}
					}
					else
					{
						$data[$element['sname'] . '.' . $field['sname']] = $rendered_value;
					}
				}
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
