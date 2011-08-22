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
		
		// View cache
		//$this->output->cache(1);

		// DB
		$this->db_cms = $this->load->database('cms', TRUE);

		// Client model 
		$this->load->model('Elementar', 'elementar');
		
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
		$data['site'] = $this->config->item('site_name');
		$template = ''; // HTML template in database
		$content = array(); // Content fields & Content elements
		
		/*
		 * Parse URI
		 */
		if ( ! $this->uri->total_segments() > 0 )
		{
			/*
			 * No URI, show home page
			 */
			
			/*
			 * Metafields
			 */
			$data['title'] = 'Home';
			$data['metafields'] = (array) $this->elementar->get_meta_fields();
			
			/*
			 * Render elements
			 */
			$content = array_merge($content, $this->common->render_elements());

		}
		else
		{
			/*
			 * Identify content ID from URI
			 */
			$content_id = 0;
			for ( $c = 1; $c <= $this->uri->total_segments(); $c++ )
			{
				$sname = $this->uri->segment($c);
				$segment = (array) $this->elementar->get_content_by_parent($content_id, $sname);
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
				 * Metafields
				 */
				$data['title'] = $content_name;
				$data['metafields'] = (array) $this->elementar->get_meta_fields($content_id);

				$template = $this->elementar->get_content_template($content_id);

				/*
				 * Content fields
				 */
				$content['name'] = $content_name;
				$content = array_merge($content, $this->common->render_content($content_id));

				/*
				 * Render elements
				 */
				$content = array_merge($content, $this->common->render_elements($content_id));
			}
			else
			{
				/*
				 * 404
				 */
				$data['title'] = 'Página não encontrada';
				$data['metafields'] = array();
			}
		}

		/*
		 * Parse the template
		 */
		$data['content'] = $this->parser->parse_string($template, $content, TRUE);
		/*
		 * Build final view and display the results
		 */
		$this->load->view('content', $data);
	}

	function sitemap()
	{
		$this->common->sitemap();
	}

}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
