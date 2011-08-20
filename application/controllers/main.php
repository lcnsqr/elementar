<?php
/*
 *      main.php
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

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->output->enable_profiler(TRUE);

		// DB
		$this->db_cms = $this->load->database('cms', TRUE);

		// Client model 
		$this->load->model('M_cms', 'cms', TRUE);
		
		// CMS Common Library
		$this->load->library('common');

		// Parser
		$this->load->library('parser');
		
		// Helper
		$this->load->helper('url');
	}

	function index()
	{
		/*
		 * Check for URI
		 */
		if ( $this->uri->total_segments() > 0 )
		{
			/*
			 * Tomar último segmento do URI
			 */
			$sname = $this->uri->segment($this->uri->total_segments());
			
			/*
			 * Identificar nível a partir
			 * da posição do segmento na URI
			 */
			$level = $this->uri->total_segments() - 1;
			
			/*
			 * Identificar solicitacão (categoria ou conteúdo)
			 */
			$id = NULL; // 404
			if ( $this->cms->is_category($sname, $level) )
			{
				$type = "category";
				$id = $this->cms->get_category_id($sname, $level);
			}
			else
			{
				$type = "content";
				if ( $this->uri->total_segments() > 1 ) 
				{
					/*
					 * Identificar categoria
					 */
					$parent_sname = $this->uri->segment($this->uri->total_segments() - 1);
					$parent_id = $this->cms->get_category_id($parent_sname, $level - 1);
				}
				else
				{
					$parent_id = NULL;
				}
				$id = $this->cms->get_content_id($sname, $parent_id);
			}
	
			if ( (bool) $id === TRUE )
			{
				/*
				 * Identificar o tipo e definir título, template, metafields e breadcrumb
				 */
				switch ( $type )
				{
					case "category" :
					$title = $this->cms->get_category_name($id);
					$template = $this->cms->get_category_template($id);
					$breadcrumb = $this->common->breadcrumb_category($id);
					break;
					case "content" :
					$title = $this->cms->get_content_name($id);
					$template = $this->cms->get_content_template($id);
					$breadcrumb = $this->common->breadcrumb_content($id);
					break;
				}
				$metafields = $this->cms->get_meta_fields($id, $type);
		
				/*
				 * tags padrão
				 */
				$data = array(
					'site_name' => $this->config->item('site_name'),
					'title' => $title,
					'metafields' => $metafields,
					'breadcrumb' => $breadcrumb
				);
		
				/*
				 * Carregar menus
				 */
				$data = array_merge($data, $this->common->get_menus());
		
				/*
				 * Atribuir tags e campos
				 * específicas para a requisição
				 */
				switch ( $type )
				{
					case "category" :
					$data['categories'] = $this->cms->get_category_children($id);
					$data['contents'] = $this->cms->get_content_by_category($id);
					$data = array_merge($data, $this->common->render_elements($this->cms->get_element_by_category($id)));
					//$data['elements'] = $this->common->render_elements($this->cms->get_element_by_category($id));
					break;
		
					case "content" :
					$content_type_id = $this->cms->get_content_type($id);
					$fields = $this->cms->get_content_type_fields($content_type_id);
					
					foreach ($fields as $field)
					{
						if ( $field['type'] == "img")
						{
							$field_id = $this->cms->get_content_field($id, $field['id']);
							$uri = $this->cms->get_image_uri($field_id);
							$width = $this->cms->get_image_width($field_id);
							$height = $this->cms->get_image_height($field_id);
							$alt = $this->cms->get_image_title($field_id);
							$data[$field['sname']] = array(
								'uri' => ( strval($uri) == "") ? "" : $uri,
								'width' => ( strval($width) == "") ? "" : $width,
								'height' => ( strval($height) == "") ? "" : $height,
								'alt' => ( strval($alt) == "") ? "" : $alt
							);
						}
						else
						{
							$value = $this->cms->get_content_field($id, $field['id']);
							$data[$field['sname']] = ( strval($value) == "" ) ? "" : $value;
						}
					}
					$data['elements'] = $this->common->render_elements($this->cms->get_element_by_content($id));
					$data = array_merge($data, $this->common->render_elements($this->cms->get_element_by_content($id)));
					break;
				}
	
				$data['body'] = $this->parser->parse_string($template, $data, TRUE);
				
				$this->load->view('content', $data);
			}
			else
			{
				/*
				 * tags padrão (404)
				 */
				$data = array(
					'site_name' => $this->config->item('site_name'),
					'title' => "Página Não Encontrada"
				);
	
				$this->output->set_status_header('404');
				$this->load->view("404", $data);
			}
		}
		else
		{
			/*
			 * No URI, show home page
			 */
			/*
			 * client controller (javascript)
			 */
			$js = array(
				'/js/jquery-1.6.2.min.js',
				'/js/jquery.easing.1.3.js',
				'/js/jquery.timers-1.2.js'
			);
			
			/*
			 * Metafields
			 */
			$metafields = $this->cms->get_meta_fields();
	
			$data = array(
				'site_name' => $this->config->item('site_name'),
				'title' => 'Home',
				'metafields' => $metafields,
				'js' => $js
			);
	
			/*
			 * Carregar menus
			 */
			$data = array_merge($data, $this->common->get_menus());
	
			/*
			 * Elementos
			 */
			$data['elements'] = $this->common->render_elements($this->cms->get_element_by_category());
	
			/*
			 * Carregar view
			 */
			$this->load->view('main', $data);
			//$this->parser->parse('main', $data);
			 
		}
	}

	function sitemap()
	{
		$this->common->sitemap();
	}

}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
