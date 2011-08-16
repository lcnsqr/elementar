<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;

	function __construct()
	{
		$this->CI =& get_instance();
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
	 * Carregar menus
	 */
	function get_menus() 
	{	
		$menus = $this->CI->cms->get_menus();
		$data = array();
		foreach ( $menus as $menu )
		{
			$data[$menu['sname']] = $this->_make_menu($menu['children']);
		}
		return $data;
	}
		
	function _make_menu($menus)
	{
		if ( ! is_array($menus) )
			return NULL;

		$reduce = array();
		foreach ( $menus as $menu )
		{
			/*
			 * Identificar menu ativo (que fora invocado)
			 */
			$current = FALSE;
			if ( $this->CI->uri->total_segments() == 0 && $menu['target'] == "/" )
			{
				// home
				$menu['target'] = $this->CI->uri->uri_string();
				$current = TRUE;
			}
			elseif ( $menu['target'] != "/" )
			{
				if (strpos("/" . $this->CI->uri->uri_string() . "/", $menu['target']) === 0 )
				{
					$current = TRUE;
				}
			}
			$reduce[$menu['name']] = array(
				'target' => $menu['target'],
				'menu' => $this->_make_menu($menu['children']),
				'current' => $current
			);
		}
		return $reduce;
	}

	/**
	 * Clickable category path (breadcrumb) for category
	 */
	function breadcrumb_category($category_id, $sep = "&raquo;", $previous = "")
	{
		$breadcrumb = "";
		
		/*
		 * With no category, just return home link
		 */
		if ( (bool)$category_id == NULL )
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" ;
			return $breadcrumb;
		}

		$parent_id = $this->CI->cms->get_category_parent($category_id);
		
		if ( $parent_id != NULL ) 
		{
			$breadcrumb = $this->breadcrumb_category($parent_id, $sep, " $sep <a href=\"" . $this->CI->cms->get_category_uri($category_id) . "/\">" . $this->CI->cms->get_category_name($category_id) . "</a>" . $previous);
		}
		else
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" .  " $sep <a href=\"" . $this->CI->cms->get_category_uri($category_id) . "/\">" . $this->CI->cms->get_category_name($category_id) . "</a>" . $previous;
		}
		
		return $breadcrumb;
	}

	/**
	 * Clickable category path (breadcrumb) for content
	 */
	function breadcrumb_content($content_id, $sep = "&raquo;")
	{
		$breadcrumb = "";
		
		$parent_id = $this->CI->cms->get_content_category($content_id);
		
		if ( $parent_id != NULL ) 
		{
			$breadcrumb = $this->breadcrumb_category($parent_id, $sep, " $sep <a href=\"" . $this->CI->cms->get_content_uri($content_id) . "\">" . $this->CI->cms->get_content_name($content_id) . "</a>");
		}
		else
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>" .  " $sep <a href=\"" . $this->CI->cms->get_content_uri($content_id) . "\">" . $this->CI->cms->get_content_name($content_id) . "</a>";
		}
		
		return $breadcrumb;
	}

	/**
	 * Clickable category path (breadcrumb) for element
	 */
	function breadcrumb_element($element_id, $sep = "&raquo;")
	{
		$breadcrumb = "";
		
		$parent_id = $this->CI->cms->get_element_parent($element_id);
		
		if ( $parent_id != NULL ) 
		{
			$parent = $this->CI->cms->get_element_parent_type($element_id);
			switch ($parent)
			{
				case "category" :
				$breadcrumb = $this->breadcrumb_category($parent_id);
				break;
				case "content" :
				$breadcrumb = $this->breadcrumb_content($parent_id);
				break;
			}
		}
		else
		{
			$breadcrumb = "<a href=\"/\" >" . $this->CI->config->item('site_name') . "</a>";
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
		foreach ( $this->CI->cms->get_contents() as $content )
		{
			$urls[] = array(
				'loc' => site_url($this->CI->cms->get_content_uri($content['id'])),
				'lastmod' => date("Y-m-d", strtotime($content['modified'])),
				'changefreq' => 'daily',
				'priority' => '0.5'
			);
		}

		/*
		 * Other controllers
		 */
		foreach ( $this->controllers(array('Parser','Rss','User')) as $url ) 
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
		$form_upload_session = $this->CI->cms->put_upload_session();
		
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
		
		$data['img_url'] = $this->CI->cms->get_image_uri_thumb($image_id);

		/*
		 * Título (alt text)
		 */
		$attributes = array(
			'class' => 'noform',
			'name' => $field_sname . '_title',
			'id' => $field_sname . '_title',
			'value' => $this->CI->cms->get_image_title($image_id)
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

	function render_elements($elements) 
	{
		$data = array();
		if ( $elements !== NULL )
		{
			foreach ($elements as $element)
			{

				$element_type_id = $this->CI->cms->get_element_type($element['id']);
				$element_type = $this->CI->cms->get_element_type_sname($element_type_id);
				
				/*
				 * Initialize element type inner array
				 */
				if ( ! array_key_exists($element_type, $data) )
				{
					$data[$element_type] = array();
				}

				/*
				 * Do not overwrite same name element (from below)
				 */
				if ( ! array_key_exists($element['sname'], $data[$element_type]) )
				{
					$data[$element_type][$element['sname']]['name'] = $element['name'];
					$fields = $this->CI->cms->get_element_type_fields($element_type_id);
					
					foreach ($fields as $field)
					{
						if ( $field['type'] == "img")
						{
							$field_id = $this->CI->cms->get_element_field($element['id'], $field['id']);
							$uri = $this->CI->cms->get_image_uri($field_id);
							$width = $this->CI->cms->get_image_width($field_id);
							$height = $this->CI->cms->get_image_height($field_id);
							$alt = $this->CI->cms->get_image_title($field_id);
							
							$data[$element_type][$element['sname']][$field['sname']] = array(
								'uri' => ( strval($uri) == "") ? "" : $uri,
								'width' => ( strval($width) == "") ? "" : $width,
								'height' => ( strval($height) == "") ? "" : $height,
								'alt' => ( strval($alt) == "") ? "" : $alt
							);
						}
						else
						{
							$value = $this->CI->cms->get_element_field($element['id'], $field['id']);
							$data[$element_type][$element['sname']][$field['sname']] = ( strval($value) == "" ) ? "" : $value;
						}
					}
				}
			}
		}
		return $data;
	}
	
}

/* End of file Common.php */
/* Location: ./application/controllers/Common.php */
