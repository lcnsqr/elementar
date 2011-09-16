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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Main extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		/*
		 * Settings
		 */
		$this->config->set_item('site_name', 'Elementar');

		$this->config->set_item('smtp_host', 'ssl://smtp.googlemail.com');
		$this->config->set_item('smtp_port', '465');
		$this->config->set_item('smtp_user', 'lcnsqr@gmail.com');
		$this->config->set_item('smtp_pass', '');

		//$this->output->enable_profiler(TRUE);
		
		// View cache
		//$this->output->cache(1);

		// DB
		$this->elementar = $this->load->database('elementar', TRUE);

		// Client model 
		$this->load->model('Crud', 'crud');
		
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
		 * Default values
		 */
		$data = array();
		$data['site'] = htmlspecialchars( $this->config->item('site_name') );
		$content = array(); // Content fields & Content elements
		
		/*
		 * Parse URI
		 */
		if ( ! $this->uri->total_segments() > 0 )
		{
			/*
			 * No URI, show home page (content_id = 1)
			 */
			$content_id = 1;
			
			/*
			 * Standard values informed to raw template
			 */
			$data['content_id'] = $content_id;
			
			/*
			 * Metafields
			 */
			$data['title'] = $this->crud->get_content_name($content_id);
			$data['metafields'] = (array) $this->crud->get_meta_fields($content_id);
			
			/*
			 * Content fields & relative contents
			 */
			$content['name'] = $data['site'];
			$content = array_merge($content, $this->common->render_content($content_id));

			/*
			 * Render elements and allow them to be hard coded in template
			 */
			$data['elements'] = $this->common->render_elements($content_id);
			$content = array_merge($content, $data['elements']);

			/*
			 * Template
			 */
			$template = $this->crud->get_template($content_id);
		}
		else
		{
			/*
			 * Identify content ID from URI
			 */
			$content_id = 1; // The primeval parent
			for ( $c = 1; $c <= $this->uri->total_segments(); $c++ )
			{
				$sname = $this->uri->segment($c);
				$segment = (array) $this->crud->get_content_by_parent($content_id, $sname);
				if ( count($segment) > 0 )
				{
					$content_id = $segment['id'];
					$content_name = $segment['name'];
				}
				else
				{
					/*
					 * Invalid request (404)
					 */
					$content_id = NULL;
					continue;
				}
			}
			if ( (bool) $content_id )
			{
				/*
				 * Standard values informed to raw template
				 */
				$data['content_id'] = $content_id;
			
				/*
				 * Metafields
				 */
				$data['title'] = $content_name;
				$data['metafields'] = (array) $this->crud->get_meta_fields($content_id);
				/*
				 * Common meta fields
				 */
				$data['metafields'][] = array(
					'name' => 'google-site-verification',
					'value' => $this->crud->get_meta_field(1, 'google-site-verification')
				);

				/*
				 * Template
				 */
				$template = $this->crud->get_template($content_id);

				/*
				 * Content fields & relative contents
				 */
				$content['name'] = $content_name;
				$content = array_merge($content, $this->common->render_content($content_id));

				/*
				 * Render elements and allow them to be hard coded in template
				 */
				$data['elements'] = $this->common->render_elements($content_id);
				$content = array_merge($content, $data['elements']);
			}
			else
			{
				/*
				 * 404
				 */
				$template = array(
					'html' => '<p>404: Página não encontrada</p>',
					'css' => '',
					'javascript' => '',
					'head' => ''
				);
				$data['content_id'] = 1; // Defaults to home content_id
				$data['title'] = 'Página não encontrada';
				$data['metafields'] = array();
			}
		}

		/*
		 * Parse the template
		 */
		$data['extra_head'] = $template['head'];
		$data['content'] = $this->parser->parse_string($template['html'], $content, TRUE);

		/*
		 * Build final view and display the results
		 */
		$this->load->view('content', $data);
	}

	/*
	 * Generate sitemap.xml
	 */
	function sitemap()
	{
		$this->common->sitemap();
	}
	
	/*
	 * Load CSS from database
	 */
	function css()
	{
		$content_id = (int) $this->uri->segment($this->uri->total_segments());
		$css = '';
		if ( $content_id != 1 )
		{
			/*
			 * Load main CSS too
			 */
			$css = $this->crud->get_template_css(1);
		}
		/*
		 * Load individual CSS
		 */
		$css .= $this->crud->get_template_css($content_id);
		$this->output->set_header("Content-type: text/css");
		$this->output->set_output($css);
	}

	/*
	 * Load Javascript from database
	 */
	function javascript()
	{
		$content_id = (int) $this->uri->segment($this->uri->total_segments());
		$javascript = '';
		if ( $content_id != 1 )
		{
			/*
			 * Load main Javascript too
			 */
			$javascript = $this->crud->get_template_javascript(1);
		}
		/*
		 * Load individual Javascript
		 */
		$javascript .= $this->crud->get_template_javascript($content_id);
		$this->output->set_header("Content-type: text/javascript");
		$this->output->set_output($javascript);
	}

}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
