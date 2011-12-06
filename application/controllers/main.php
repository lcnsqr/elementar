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
	
	/*
	 * i18n settings
	 */
	var $LANG;
	var $LANG_AVAIL = array();
	
	/*
	 * Starting URI segment
	 */
	var $SEGMENT_STEP = 0;
	
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

		// Storage model 
		$this->load->model('Storage', 'storage');
		
		// Parser
		$this->load->library('parser');
		
		// Helper
		$this->load->helper('url');
	}

	/*
	 * Load configuration and remap to requested method
	 */
	public function _remap($method)
	{
		/*
		 * Load site config
		 */
		$settings = $this->storage->get_config();
		if ( ! is_array($settings) )
		{
			exit('Bad config. Call site administrator.');
		}
		foreach($settings as $setting)
		{
			switch ( $setting['name'] )
			{
				case 'i18n' :
				/*
				 * Language settings
				 */
				$i18n_settings = json_decode($setting['value'], TRUE);
				foreach($i18n_settings as $i18n_setting)
				{
					if ( (bool) $i18n_setting['default'] )
					{
						$this->LANG = $i18n_setting['code'];
						/*
						 * Default language is first in array
						 */
						$this->LANG_AVAIL = array_merge(array($i18n_setting['code'] => $i18n_setting['name']), $this->LANG_AVAIL);
					}
					else
					{
						$this->LANG_AVAIL[$i18n_setting['code']] = $i18n_setting['name'];
					}
				}
				break;
			}
		}
		
		/*
		 * Check language choice
		 */
		if ( $this->uri->total_segments() > 0 )
		{
			/*
			 * URI first segment must be the 
			 * language code or the default language
			 * will be used
			 */
			if ( array_key_exists($this->uri->segment(1), $this->LANG_AVAIL) )
			{
				$this->LANG = $this->uri->segment(1);
				$this->SEGMENT_STEP = 1;
			}
		}
		
		/*
		 * If selected lang is the default language,
		 * don't prepend lang code to URI
		 */
		if ( $this->LANG == key($this->LANG_AVAIL) )
		{
			$uri_prefix = '';
		}
		else
		{
			$uri_prefix = '/' . $this->LANG;
		}
		
		/*
		 * CMS Common Library called instead of
		 * in construct to pass the LANG parameter
		 */
		$this->load->library('common', array(
			'lang' => $this->LANG, 
			'uri_prefix' => $uri_prefix
		));

		/*
		 * Redirect to existing function or parser
		 */
		if ( $this->uri->total_segments() > $this->SEGMENT_STEP )
		{
			/*
			 * Step forward on segments if method is the controller myself
			 */
			if ( $this->uri->segment($this->SEGMENT_STEP + 1) == 'main' )
			{
				$request = $this->uri->segment($this->SEGMENT_STEP + 2);
			}
			else
			{
				$request = $this->uri->segment($this->SEGMENT_STEP + 1);
			}
			
			/*
			 * Load plugins
			 */
			$addons = $this->common->load_addons();
			foreach ( $addons as $addon )
			{
				if ( strtolower($request) == strtolower($addon['name']) )
				{
					/*
					 * Plugin requested
					 */
					$$addon['name'] = new $addon['name'](array(
						'lang' => $this->LANG, 
						'uri_prefix' => $uri_prefix
					));
					/*
					 * Check method
					 */
					$method = $this->uri->segment($this->SEGMENT_STEP + 2);

					if ( $method == '' && method_exists($$addon['name'], 'main') )
					{
						/*
						 * No specific method, load main method
						 */
						$$addon['name']->main();
					}
					elseif ( method_exists($$addon['name'], $method) )
					{
						$$addon['name']->$method();
					}
					exit(0);
				}
			}
			
			/*
			 * Local action
			 */
			if ( method_exists($this, $request) )
			{
				/*
				 * Redirect to existing method
				 */
				 $this->$request();
			}
			elseif ( method_exists($this, $method) )
			{
				/*
				 * Method called directly, without lang code
				 */
				$this->$method();
			}
			else
			{
				/*
				 * Redirect to parser
				 */
				$this->index();
			}
		}
		else
		{
			$this->index();
		}
	}

	function index()
	{
		/*
		 * Default content values
		 */
		$data = array();
		$data['site'] = htmlspecialchars( $this->config->item('site_name') );

		/*
		 * Array to carry content fields, elements,
		 * and other useful variables & rendered data
		 */
		$content = array(
			'year' => date("Y")
		);

		/*
		 * Language links
		 */
		reset($this->LANG_AVAIL);
		$default_lang = key($this->LANG_AVAIL);
		$content['lang'] = array();
		foreach($this->LANG_AVAIL as $code => $name)
		{
			/*
			 * Build current uri for each language
			 */
			if ( $this->SEGMENT_STEP == 1 )
			{
				/*
				 * Slash on home uri
				 */
				if ( $this->uri->total_segments() == $this->SEGMENT_STEP )
				{
					$uri = ($default_lang == $code) ? '/' : '/' . $code . '/';
				}
				else
				{
					/*
					 * Split out language prefix and 
					 * rebuild uri based on current page
					 */
					$uri = substr($this->uri->uri_string(), strlen($this->LANG));
					/*
					 * Add language prefix to non default languages
					 */
					$uri = ($default_lang == $code) ? $uri : '/' . $code . $uri;
				}
			}
			else
			{
				/*
				 * Add language prefix to non default languages
				 */
				$uri = ($default_lang == $code) ? '/' . $this->uri->uri_string() : '/' . $code . '/' . $this->uri->uri_string();
			}
			$content['lang'][] = array(
				'code' => $code, 
				'uri' => $uri, 
				'title' => $name, 
				'current' => ($this->LANG == $code) ? TRUE : FALSE 
			);
		}
		
		/*
		 * Parse URI
		 */
		if ( $this->uri->total_segments() == $this->SEGMENT_STEP )
		{
			/*
			 * No URI (besides eventual lang code), 
			 * show home page (content_id = 1)
			 */
			$content_id = 1;
			
			/*
			 * Standard values informed to raw template
			 */
			$data['content_id'] = $content_id;
			
			/*
			 * localized title
			 */
			$titles = json_decode($this->storage->get_content_name($content_id), TRUE);
			$data['title'] = $titles[$this->LANG];
			/*
			 * Metafields
			 */
			$data['metafields'] = (array) $this->storage->get_meta_fields($content_id);
			
			/*
			 * Content fields & relative contents
			 */
			$content['name'] = $data['site'];
			$content['title'] = $data['title'];
			$content = array_merge($content, $this->common->render_content($content_id));

			/*
			 * Render elements and allow them to be hard coded in template
			 */
			$data['elements'] = $this->common->render_elements($content_id);
			$content = array_merge($content, $data['elements']);

			/*
			 * Template
			 */
			$template = $this->storage->get_template($content_id);
		}
		else
		{
			/*
			 * Identify content ID from URI
			 */
			$content_id = 1; // The primeval parent
			$starting_segment = $this->SEGMENT_STEP + 1;
			for ( $c = $starting_segment; $c <= $this->uri->total_segments(); $c++ )
			{
				$sname = $this->uri->segment($c);
				$segment = (array) $this->storage->get_content_by_parent($content_id, $sname);
				if ( count($segment) > 0 )
				{
					$content_id = $segment['id'];
					/*
					 * localized name
					 */
					$names = json_decode($segment['name'], TRUE);
					$content_name = $names[$this->LANG];
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
				$data['metafields'] = (array) $this->storage->get_meta_fields($content_id);
				/*
				 * Common meta fields
				 */
				$data['metafields'][] = array(
					'name' => 'google-site-verification',
					'value' => $this->storage->get_meta_field(1, 'google-site-verification')
				);

				/*
				 * Template
				 */
				$template = $this->storage->get_template($content_id);

				/*
				 * Content fields & relative contents
				 */
				$content['name'] = $content_name;
				$content['title'] = $data['title'];
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
			$css = $this->storage->get_template_css(1);
		}
		/*
		 * Load individual CSS
		 */
		$css .= $this->storage->get_template_css($content_id);
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
			$javascript = $this->storage->get_template_javascript(1);
		}
		/*
		 * Load individual Javascript
		 */
		$javascript .= $this->storage->get_template_javascript($content_id);
		$this->output->set_header("Content-type: text/javascript");
		$this->output->set_output($javascript);
	}

}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
