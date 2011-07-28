<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {

	function __construct()
	{
		
	}
	
	function ajax_response($response)
	{
		$CI =& get_instance();
		// execution time
		$elapsed = array('elapsed_time' => $CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
		$response = array_merge($response, $elapsed);
		
		$CI->output->set_header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		$CI->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		$CI->output->set_header("Cache-Control: post-check=0, pre-check=0");
		$CI->output->set_header("Pragma: no-cache");
		$CI->output->set_header("Content-type: application/json");
		
		$data = json_encode($response);
		$CI->output->set_output($data);
	}
	
	/*
	 * Carregar menus
	 */
	function get_menus() 
	{	
		$CI =& get_instance();
		$menus = $CI->cms->get_menus();
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
		
		$CI =& get_instance();

		$reduce = array();
		foreach ( $menus as $menu )
		{
			/*
			 * Identificar menu ativo (que fora invocado)
			 */
			$current = FALSE;
			if ( $CI->uri->total_segments() == 0 && $menu['target'] == "/" )
			{
				// home
				$menu['target'] = $CI->uri->uri_string();
				$current = TRUE;
			}
			elseif ( $menu['target'] != "/" )
			{
				if (strpos("/" . $CI->uri->uri_string() . "/", $menu['target']) === 0 )
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
		$CI =& get_instance();

		$breadcrumb = "";
		
		/*
		 * With no category, just return home link
		 */
		if ( (bool)$category_id == NULL )
		{
			$breadcrumb = "<a href=\"/\" >" . $CI->config->item('site_name') . "</a>" ;
			return $breadcrumb;
		}

		$parent_id = $CI->cms->get_category_parent($category_id);
		
		if ( $parent_id != NULL ) 
		{
			$breadcrumb = $this->breadcrumb_category($parent_id, $sep, " $sep <a href=\"" . $CI->cms->get_category_uri($category_id) . "/\">" . $CI->cms->get_category_name($category_id) . "</a>" . $previous);
		}
		else
		{
			$breadcrumb = "<a href=\"/\" >" . $CI->config->item('site_name') . "</a>" .  " $sep <a href=\"" . $CI->cms->get_category_uri($category_id) . "/\">" . $CI->cms->get_category_name($category_id) . "</a>" . $previous;
		}
		
		return $breadcrumb;
	}

	/**
	 * Clickable category path (breadcrumb) for content
	 */
	function breadcrumb_content($content_id, $sep = "&raquo;")
	{
		$CI =& get_instance();

		$breadcrumb = "";
		
		$parent_id = $CI->cms->get_content_category($content_id);
		
		if ( $parent_id != NULL ) 
		{
			$breadcrumb = $this->breadcrumb_category($parent_id, $sep, " $sep <a href=\"" . $CI->cms->get_content_uri($content_id) . "\">" . $CI->cms->get_content_name($content_id) . "</a>");
		}
		else
		{
			$breadcrumb = "<a href=\"/\" >" . $CI->config->item('site_name') . "</a>" .  " $sep <a href=\"" . $CI->cms->get_content_uri($content_id) . "\">" . $CI->cms->get_content_name($content_id) . "</a>";
		}
		
		return $breadcrumb;
	}

	/**
	 * Clickable category path (breadcrumb) for element
	 */
	function breadcrumb_element($element_id, $sep = "&raquo;")
	{
		$CI =& get_instance();

		$breadcrumb = "";
		
		$parent_id = $CI->cms->get_element_parent($element_id);
		
		if ( $parent_id != NULL ) 
		{
			$parent = $CI->cms->get_element_parent_type($element_id);
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
			$breadcrumb = "<a href=\"/\" >" . $CI->config->item('site_name') . "</a>";
		}
		
		return $breadcrumb;
	}

	/**
	 * Formulário para envio de imagem no campo imagem
	 * @param string $field_name Nome real (hidden input)
	 * @param integer $image_id Id de imagem armazenado 
	 * @return HTML content
	 */
	function render_form_upload_image($field_sname, $image_id = NULL)
	{
		$CI =& get_instance();

		$form_upload_session = $CI->cms->put_upload_session();
		
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
		$CI->load->library('user_agent');
		if( $CI->agent->browser() == 'Internet Explorer' )
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
		
		$data['img_url'] = $CI->cms->get_image_uri_thumb($image_id);
		
		return $CI->load->view("admin/admin_content_upload_image", $data, TRUE);
		
	}
	/**
	 * Convert non-ascii and other characters to ascii and underscores
	 * @param string $string Input string
	 * @return string Converted string
	 */
	function normalize_string($string)
	{
		$CI =& get_instance();
		
		$CI->load->helper('url');

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
		$CI =& get_instance();

		$data = array();
		if ( $elements !== NULL )
		{
			foreach ($elements as $element)
			{

				$element_type_id = $CI->cms->get_element_type($element['id']);
				$element_type = $CI->cms->get_element_type_sname($element_type_id);
				
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
					$fields = $CI->cms->get_element_type_fields($element_type_id);
					
					foreach ($fields as $field)
					{
						if ( $field['type'] == "img")
						{
							$field_id = $CI->cms->get_element_field($element['id'], $field['id']);
							$uri = $CI->cms->get_image_uri($field_id);
							$width = $CI->cms->get_image_width($field_id);
							$height = $CI->cms->get_image_height($field_id);
							
							$data[$element_type][$element['sname']][$field['sname']] = array(
								'uri' => ( strval($uri) == "") ? "" : $uri,
								'width' => ( strval($width) == "") ? "" : $width,
								'height' => ( strval($height) == "") ? "" : $height
							);
						}
						else
						{
							$value = $CI->cms->get_element_field($element['id'], $field['id']);
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
