<?php
/*
 *      main.php
 *      
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

/** 
 * Main Class 
 * 
 * Handles all frontend requests
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Main extends CI_Controller {
	
	// i18n settings
	var $LANG;
	var $LANG_AVAIL = array();
	var $URI_PREFIX;
	
	// Starting URI segment
	var $STARTING_SEGMENT = 0;
	
	// 24 hours caching
	var $cache_expire = 1440;
		
	function __construct()
	{
		parent::__construct();
		
		// Performance tests
		//$this->output->enable_profiler(TRUE);
		
		// Elementar DB
		$this->elementar = $this->load->database('elementar', TRUE);

		// Storage model 
		$this->load->model('Storage', 'storage');
		
		// Template parser
		$this->load->library('parser');
		
		// CI URL helper
		$this->load->helper('url');

		// Load encryption key before session library
		$this->config->set_item('encryption_key', $this->storage->get_config('encryption_key'));

		// Session library
		$this->load->library('session');
		
		// Create cache files by default
		$this->output->cache($this->cache_expire);

		// Elementar Common Library
		$this->load->library('common');

		// Load site i18n settings
		list($this->LANG, $this->LANG_AVAIL) = $this->common->load_i18n_settings();
	}

	/**
	 * Parse request and render proper response
	 * 
	 * @access public
	 * @param  string
	 * @return void
	 */
	public function _remap($method)
	{
		// Check language choice
		if ( $this->uri->total_segments() > 0 )
		{
			// URI first segment must be the 
			// language code or the default 
			// language will be used
			
			if ( array_key_exists($this->uri->segment(1), $this->LANG_AVAIL) )
			{
				$this->LANG = $this->uri->segment(1);
				$this->STARTING_SEGMENT = 1;
			}
		}

		// Load get parameters 
		$this->PARAMS = $this->input->get(NULL, TRUE);
		
		// If selected lang is the default language,
		// don't prepend lang code to URI
		
		if ( $this->LANG == key($this->LANG_AVAIL) )
		{
			$this->URI_PREFIX = '';
		}
		else
		{
			$this->URI_PREFIX = '/' . $this->LANG;
		}
		
		// Set choosen language and URI 
		// language prefix in Common Library 

		$this->common->set_lang($this->LANG);
		$this->common->set_uri_prefix($this->URI_PREFIX);

		// Language related Settings
		$site_names = json_decode($this->storage->get_config('name'), TRUE);
		$this->config->set_item('site_name', (array_key_exists($this->LANG, $site_names)) ? $site_names[$this->LANG] : '');

		// Email settings
		$email_settings = json_decode($this->storage->get_config('email') ,TRUE);
		$this->load->library('email', $email_settings);
		$this->email->set_newline("\r\n");

		// Redirect to existing function or parser
		if ( $this->uri->total_segments() > $this->STARTING_SEGMENT )
		{
			// Step forward on segments if method is the controller myself
			if ( $this->uri->segment($this->STARTING_SEGMENT + 1) == 'main' )
			{
				$request = $this->uri->segment($this->STARTING_SEGMENT + 2);
			}
			else
			{
				$request = $this->uri->segment($this->STARTING_SEGMENT + 1);
			}
			
			// Load addons
			$addons = $this->common->load_addons();
			foreach ( $addons as $addon )
			{
				if ( strtolower($request) == strtolower($addon['name']) )
				{
					// Addon requested
					$$addon['name'] = new $addon['name'](array(
						'lang' => $this->LANG, 
						'uri_prefix' => $this->URI_PREFIX
					));
					
					// Check method
					$method = $this->uri->segment($this->STARTING_SEGMENT + 2);

					if ( $method == '' && method_exists($$addon['name'], 'index') )
					{
						// No specific method, load main method
						$$addon['name']->index();
					}
					elseif ( method_exists($$addon['name'], $method) )
					{
						$$addon['name']->$method();
					}
					// End local actions
					exit(0);
				}
			}
			
			// Local action
			if ( method_exists($this, $request) )
			{
				 // Redirect to existing method
				 $this->$request();
			}
			elseif ( method_exists($this, $method) )
			{
				// Method called directly, without lang code
				$this->$method();
			}
			else
			{
				// Redirect to parser
				$this->index();
			}
		}
		else
		{
			// Redirect to parser
			$this->index();
		}
	}

	/**
	 * Parse non class method requests
	 * to database contents
	 * 
	 * @access public
	 * @return void
	 */
	function index()
	{
		// Default content values
		$data = array();
		$data['site'] = htmlspecialchars( $this->config->item('site_name') );
		
		/*
		 * Load favicon
		 */
		$favicon = json_decode($this->storage->get_config('favicon'), TRUE);
		if ( is_array($favicon) )
		{
			$data['favicon'] = ( $favicon['uri'] != '' ) ? $favicon['uri'] : '/favicon.ico';
		}
		else
		{
			$data['favicon'] = '/favicon.ico';
		}

		// Metafields
		$data['metafields'] = array();
		// common meta fields
		$google_site_verification = $this->storage->get_config('google-site-verification');
		if ( ! empty($google_site_verification) )
		{
			$data['metafields'][] = array(
				'name' => 'google-site-verification',
				'value' => $google_site_verification
			);
		}

		// Array to carry content fields, elements,
		// and other useful variables & rendered data
		
		$content = array(
			'year' => date("Y"),
			'uri_prefix' => $this->URI_PREFIX
		);

		// Language links
		reset($this->LANG_AVAIL);
		$default_lang = key($this->LANG_AVAIL);
		$content['lang'] = array();
		foreach($this->LANG_AVAIL as $code => $name)
		{
			// Build current uri detection for each language
			if ( $this->STARTING_SEGMENT == 1 )
			{
				// Slash on home uri
				if ( $this->uri->total_segments() == $this->STARTING_SEGMENT )
				{
					$uri = ($default_lang == $code) ? '/' : '/' . $code . '/';
				}
				else
				{
					// Split out language prefix and 
					// rebuild uri based on current page
					
					$uri = substr($this->uri->uri_string(), strlen($this->LANG));
					
					// Add language prefix to non default languages
					$uri = ($default_lang == $code) ? $uri : '/' . $code . $uri;
				}
			}
			else
			{
				// Add language prefix to non default languages
				$uri = ($default_lang == $code) ? '/' . $this->uri->uri_string() : '/' . $code . '/' . $this->uri->uri_string();
			}
			$content['lang'][] = array(
				'code' => $code, 
				'uri' => $uri, 
				'title' => $name, 
				'current' => ($this->LANG == $code) ? TRUE : FALSE 
			);
			
			/*
			 * HTML/XHTML lang tag in head
			 */
			$data['lang'] = $this->LANG;
		}
		
		// Parse requested URI
		if ( $this->uri->total_segments() == $this->STARTING_SEGMENT )
		{
			// No URI (besides eventual lang code), 
			// show home page (content_id = 1)
			
			$content_id = 1;
			
			// Standard values informed to raw template
			$data['content_id'] = $content_id;
			
			// Localized title
			$titles = json_decode($this->storage->get_content_name($content_id), TRUE);
			$data['title'] = (array_key_exists($this->LANG, $titles)) ? $titles[$this->LANG] : '';
			
			// Metafields
			$data['metafields'] = array_merge($data['metafields'] ,(array) $this->storage->get_meta_fields($content_id));
			
			// Content fields & associated contents
			$content['name'] = $data['site'];
			$content['title'] = $data['title'];
			$content = array_merge($content, $this->common->render_content($content_id));

			// Content template
			$template = $this->storage->get_template($content_id);

			// Render elements, parse them by filter
			// and allow them to be hard coded in template

			$data['elements'] = $this->common->render_elements($content_id, $template['filter']);
			$content = array_merge($content, $data['elements']);
		}
		else
		{
			// Identify content ID from URI
			
			// The primeval parent
			$content_id = 1;
			
			// Parse each segment until the last one
			$starting_segment = $this->STARTING_SEGMENT + 1;
			for ( $c = $starting_segment; $c <= $this->uri->total_segments(); $c++ )
			{
				$sname = $this->uri->segment($c);
				$segment = (array) $this->storage->get_content_by_parent($content_id, $sname);
				if ( count($segment) > 0 )
				{
					$content_id = $segment['id'];
					
					// localized name
					$names = json_decode($segment['name'], TRUE);
					$content_name = (array_key_exists($this->LANG, $names)) ? $names[$this->LANG] : '';
				}
				else
				{
					// Invalid request (404)
					$content_id = NULL;
					continue;
				}
			}
			if ( (bool) $content_id )
			{
				// Standard values passed to raw template
				$data['content_id'] = $content_id;
			
				// Metafields
				$data['title'] = $content_name;
				$data['metafields'] = array_merge($data['metafields'], (array) $this->storage->get_meta_fields($content_id));
				
				// Template
				$template = $this->storage->get_template($content_id);

				// Content fields & associated contents
				$content['name'] = $content_name;
				$content['title'] = $data['title'];
				$content = array_merge($content, $this->common->render_content($content_id));

				// Render elements, parse them by filter
				// and allow them to be hard coded in template
				
				$data['elements'] = $this->common->render_elements($content_id, $template['filter']);
				$content = array_merge($content, $data['elements']);
			}
			else
			{
				// 404
				$template = array(
					'html' => '<p>404: Página não encontrada</p>',
					'css' => '',
					'javascript' => '',
					'head' => ''
				);

				// Defaults to home content_id
				$data['content_id'] = 1;
				$data['title'] = 'Página não encontrada';
			}
		}

		// Parse the template
		$data['extra_head'] = $template['head'];
		$data['content'] = $this->parser->parse_string($template['html'], $content, TRUE);

		// HTTP headers
		$mtime = gmdate('D, d M Y H:i:s').' GMT';
		$this->output->set_header('ETag: ' . md5($mtime));
		$this->output->set_header('Last-Modified: ' . $mtime);
		$this->output->set_header('Content-Language: ' . $this->LANG);

		// Build final view and display the results
		$this->load->view('content', $data);
	}

	/**
	 * Generate /sitemap.xml
	 * 
	 * @access public
	 * @return void
	 */
	function sitemap()
	{
		$this->common->sitemap();
	}
	
	/**
	 * Load CSS from database
	 * 
	 * @access public
	 * @return void
	 */
	function css()
	{
 		$content_id = (int) $this->uri->segment($this->uri->total_segments());
		$css = '';
		if ( $content_id != 1 )
		{
			// Load main CSS too
			$css = $this->storage->get_template_css(1);
		}
		// Load individual CSS
		$css .= $this->storage->get_template_css($content_id);

		// HTTP headers
		$this->output->set_header("Content-type: text/css");
		$mtime = gmdate('D, d M Y H:i:s').' GMT';
		$this->output->set_header('ETag: ' . md5($mtime));
		$this->output->set_header('Last-Modified: ' . $mtime);

		$this->output->set_output($css);
	}

	/**
	 * Load Javascript from database
	 * 
	 * @access public
	 * @return void
	 */
	function javascript()
	{
		$content_id = (int) $this->uri->segment($this->uri->total_segments());
		$javascript = '';
		if ( $content_id != 1 )
		{
			// Load main Javascript too
			$javascript = $this->storage->get_template_javascript(1);
		}
		// Load individual Javascript
		$javascript .= $this->storage->get_template_javascript($content_id);

		// HTTP headers
		$this->output->set_header("Content-type: text/javascript");
		$mtime = gmdate('D, d M Y H:i:s').' GMT';
		$this->output->set_header('ETag: ' . md5($mtime));
		$this->output->set_header('Last-Modified: ' . $mtime);

		$this->output->set_output($javascript);
	}
	
	/**
	 * Load partial content by ajax
	 * 
	 * @access public
	 * @return void
	 */
	function partial() {
		// $variable can be brothers, children or index
		$variable = $this->uri->segment($this->STARTING_SEGMENT + 3);
		// $content_id holds the page loaded by client
		$content_id = $this->uri->segment($this->STARTING_SEGMENT + 4);
		// $requested_order is the variable pair position in template
		$requested_order = $this->uri->segment($this->STARTING_SEGMENT + 5);

		// Content template
		$template = $this->storage->get_template($content_id);

		// Match tag pair
		$l_delim = '{';
		$r_delim = '}';
		$order = 0;

		while ( preg_match("|" . preg_quote($l_delim) . $variable . preg_quote($r_delim) . "(.+?)". preg_quote($l_delim) . '/' . $variable . preg_quote($r_delim) . "|s", $template['html'], $match))
		{
			// Render only if corresponds to the requested order
			if ( $order == $requested_order )
			{
				switch ( $variable )
				{
					case 'brothers' :
					case 'children' :
					case 'account' :
					$content = $this->common->render_content_partial($content_id, $variable);
					$html = $this->parser->parse_string_partial($match['0'], $content, TRUE);
					break;

					default :
					$html = '<!-- unknown variable -->';
					break;
				}
				// Leave loop 
				break;
			}
			else
			{
				// Clear tag pair to next iteration
				switch ( $variable )
				{
					case 'brothers' :
					case 'children' :
					case 'account' :
					$pos = strpos($template['html'], $match['0']);
					$template['html'] = substr_replace($template['html'], '', $pos, strlen($match['0']));
					break;
				}
			}
			$order++;
		}

		// HTTP headers
		$this->output->set_header("Content-type: text/html");
		// Cache headers only used if not related to account
		if ( $variable != 'account' )
		{
			$mtime = gmdate('D, d M Y H:i:s').' GMT';
			$this->output->set_header('ETag: ' . md5($mtime));
			$this->output->set_header('Last-Modified: ' . $mtime);
		}
		else
		{
			// Try to prevent caching
			$this->output->cache(0);
			$this->output->set_header("Expires: " . gmdate("D, d M Y H:i:s", time() - 3600)." GMT");
			$this->output->set_header("Cache-Control: no-cache, no-store");
			$this->output->set_header("Pragma: no-cache");
		}
		$this->output->set_output($html);
	}
	
	/**
	 * Account related requests from frontend
	 * 
	 * @access public
	 * @return void
	 */
	function account()
	{
		// Don't create cache files for account related actions
		$this->output->cache(0);

		// Required CI helpers
		$this->load->helper(array('security', 'string', 'form'));
		
		// Access model 
		$this->load->model('Access', 'access');

		// Localized messages
		$this->lang->load('elementar', $this->config->item('language'));

		// Fields validation library
		$this->load->library('validation');

		// Determine action from the second URI segment
		$action = $this->uri->segment($this->STARTING_SEGMENT + 2);
		switch ( $action )
		{
			/*********
			 * Login *
			 *********/
			case 'login' :
			if ( ! $this->input->is_ajax_request() )
				exit($this->lang->line('elementar_no_direct_script_access'));

			$username = $this->input->post("username", TRUE);
			$password = $this->input->post("password", TRUE);

			$account_id = $this->access->get_account_by_username($username);
			
			// Avoid pending group
			$group_id = $this->access->get_account_group($account_id);
			
			if ( (bool) $account_id && do_hash($password) == $this->access->get_account_password($account_id) && (int) $group_id != 2 )
			{
				$this->session->set_userdata('account_id', $account_id);
				$this->session->set_userdata('group_id', $group_id);

				$response = array(
					'done' => TRUE
				);
				$this->output->set_output_json($response);
				return;
			}
			else
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_login_incorret')
				);
				$this->output->set_output_json($response);
				return;
			}
			break;
			
			/**********
			 * Logout *
			 **********/
			case 'logout' :
			if ( ! $this->input->is_ajax_request() )
				exit($this->lang->line('elementar_no_direct_script_access'));

			$this->session->unset_userdata('account_id');
			$this->session->unset_userdata('group_id');
			$response = array(
				'done' => TRUE
			);
			$this->output->set_output_json($response);
			break;
			
			/************
			 * Register *
			 ************/
			case 'register' :
			if ( ! $this->input->is_ajax_request() )
				exit($this->lang->line('elementar_no_direct_script_access'));
			
			// Other account fields
			$username = $this->input->post('username', TRUE);
			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			// Assess account username
			$response = $this->validation->assess_username($username);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->output->set_output_json($response);
				return;
			}
	
			if ( (bool) $this->access->get_account_by_username($username) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_username_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}

			// Assess email
			$response = $this->validation->assess_email($email);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->output->set_output_json($response);
				return;
			}
			if ( (bool) $this->access->get_account_by_email($email) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_field_used')
				);
				$this->output->set_output_json($response);
				return;
			}

			// Assess password
			$response = $this->validation->assess_password($password);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->output->set_output_json($response);
				return;
			}
			
			// Create account
			$register_hash = random_string('unique');
			$account_id = $this->access->put_account($username, $email, $password, $register_hash);
			
			// Add acount to pending group
			$this->access->put_account_group($account_id, 2);

			// Mail confirmation
			$this->email->subject($this->lang->line('elementar_xhr_signup_subject'));
			$this->email->message(site_url('account/confirm') . '/' . $register_hash);
			$this->email->from($this->access->get_account_email(1), $this->config->item('site_name'));
			$this->email->to($email);
			$this->email->send();

			$this->output->set_output_json($response);
			break;
			
			/********************************
			 * Confirm registration by hash *
			 ********************************/
			case 'confirm' :
			// Search register hash
			$register_hash = $this->uri->segment($this->STARTING_SEGMENT + 3);
			if ( (bool) $register_hash )
			{
				// Check existence and pending account
				$account_id = $this->access->get_account_by_register_hash($register_hash);
				if ( (bool) $account_id && (int) $this->access->get_account_group($account_id) == 2 )
				{
					// Detach account from pending group
					// attach account to default group

					$this->access->put_account_group($account_id, 3);
					echo 'Confirmed';
					return;
				}
			}
			echo 'Not valid';
			break;
			
			/****************************
			 * Send reset hash by email *
			 ****************************/
			case 'forgot' :
			if ( ! $this->input->is_ajax_request() )
				exit($this->lang->line('elementar_no_direct_script_access'));

			$email = $this->input->post('email', TRUE);
			
			// Assess email
			$response = $this->validation->assess_email($email);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->output->set_output_json($response);
				return;
			}
			$account_id = $this->access->get_account_by_email($email);
			if ( ! (bool) $account_id )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_not_found')
				);
				$this->output->set_output_json($response);
				return;
			}
			$reset_hash = random_string('unique');
			$this->access->put_account_reset_hash($account_id, $reset_hash);

			// Mail confirmation
			$this->email->subject($this->lang->line('elementar_xhr_password_subject'));
			$this->email->message(site_url('account/reset') . '/' . $reset_hash);
			$this->email->from($this->access->get-account_email(1), $this->config->item('site_name'));
			$this->email->to($email);
			$this->email->send();
			
			$response = array(
				'done' => TRUE,
				'message' => $this->lang->line('elementar_xhr_reset_email_sent')
			);
			$this->output->set_output_json($response);
			break;
			
			/***********************
			 * Reset password form *
			 ***********************/
			case 'reset' :
			$reset_hash = $this->uri->segment($this->STARTING_SEGMENT + 3);
			if ( (bool) $reset_hash )
			{
				// Check existence and not pending account
				$account_id = $this->access->get_account_by_reset_hash($reset_hash);
				if ( (bool) $account_id && (int) $this->access->get_account_group($account_id) != 2 )
				{
					// Show new password form
					$attributes = array('name' => 'reset_password', 'id' => 'reset_password');
					$hidden = array('reset_hash' => $reset_hash);
					$form = form_open('account/password', $attributes, $hidden);
					$attributes = array('name' => 'password', 'id' => 'password');
					$form .= form_password($attributes);
					$form .= form_submit('send', $this->lang->line('elementar_xhr_form_submit'));
					$form .= form_close();
					$data = array('form' => $form);
					$this->load->view('account_password', $data);
					return;
				}
			}
			echo 'Not valid';
			break;

			
			/******************
			 * Reset password *
			 ******************/
			case 'password' :
			$reset_hash = $this->input->post('reset_hash', TRUE);
			$password = $this->input->post('password', TRUE);
			$account_id = $this->access->get_account_by_reset_hash($reset_hash);
			if ( (bool) $reset_hash && (bool) $account_id )
			{
				// Assess password
				$response = $this->validation->assess_password($password);
				if ( (bool) $response['done'] == FALSE )
				{
					$this->output->set_output_json($response);
					return;
				}
				else
				{
					// Change password and erase reset hash
					$this->access->put_account_password($account_id, $password);
					$this->access->put_account_reset_hash($account_id, NULL);
					$response = array(
						'done' => TRUE,
						'message' => $this->lang->line('elementar_xhr_password_changed')
					);
					$this->output->set_output_json($response);
					return;
				}
			}
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_not_allowed')
			);
			$this->output->set_output_json($response);
			break;
			
			default:
			// Login form
			$account_id = $this->session->userdata('account_id');
			$data = array();
			if ( (bool) $account_id !== FALSE )
			{
				$data['is_logged'] = TRUE;
				$data['username'] = $this->access->get_account_username($account_id);
			}
			else
			{
				$data['is_logged'] = FALSE;
			}
			$this->load->view('account', $data);
			break;
		}
	}

}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
