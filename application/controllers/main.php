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
	var $URI_PREFIX;
	
	/*
	 * Starting URI segment
	 */
	var $SEGMENT_STEP = 0;
	
	/*
	 * 24 hours caching
	 */
	var $cache_expire = 1440;
		
	function __construct()
	{
		parent::__construct();
		
		/*
		 * CI libraries
		 */
		$this->load->library('session');
		
		/*
		 * Settings
		 */
		$this->config->set_item('site_name', 'Elementar');

		$config = array(
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => '465',
			'smtp_user' => 'lcnsqr@gmail.com',
			'smtp_pass' => ''
		);
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");

		//$this->output->enable_profiler(TRUE);
		
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
		 * Load site i18n config
		 */
		$i18n_settings = json_decode($this->storage->get_config('i18n'), TRUE);
		foreach($i18n_settings as $i18n_setting)
		{
			if ( (bool) $i18n_setting['default'] )
			{
				$this->LANG = $i18n_setting['code'];
				/*
				 * Default language is the first in array
				 */
				$this->LANG_AVAIL = array_merge(array($i18n_setting['code'] => $i18n_setting['name']), $this->LANG_AVAIL);
			}
			else
			{
				$this->LANG_AVAIL[$i18n_setting['code']] = $i18n_setting['name'];
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
			$this->URI_PREFIX = '';
		}
		else
		{
			$this->URI_PREFIX = '/' . $this->LANG;
		}
		
		/*
		 * CMS Common Library called instead of
		 * in construct to pass the LANG parameter
		 */
		$this->load->library('common', array(
			'lang' => $this->LANG, 
			'lang_avail' => $this->LANG_AVAIL, 
			'uri_prefix' => $this->URI_PREFIX
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
						'uri_prefix' => $this->URI_PREFIX
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
		 * Enable caching only in anonymous session
		 */
		if ( ! (bool) $this->session->userdata('account_id') )
		{
			// 24 hours caching
			$this->output->cache($this->cache_expire);
		}

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
			'year' => date("Y"),
			'uri_prefix' => $this->URI_PREFIX
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
			 * Template
			 */
			$template = $this->storage->get_template($content_id);

			/*
			 * Render elements, parse them by filter
			 * and allow them to be hard coded in template
			 */
			$data['elements'] = $this->common->render_elements($content_id, $template['filter']);
			$content = array_merge($content, $data['elements']);
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
				 * Render elements, parse them by filter
				 * and allow them to be hard coded in template
				 */
				$data['elements'] = $this->common->render_elements($content_id, $template['filter']);
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
		/*
		 * Enable caching only in anonymous session
		 */
		if ( ! (bool) $this->session->userdata('account_id') )
		{
			// 24 hours caching
			$this->output->cache($this->cache_expire);
		}
		$this->common->sitemap();
	}
	
	/*
	 * Load CSS from database
	 */
	function css()
	{
		/*
		 * Enable caching only in anonymous session
		 */
		if ( ! (bool) $this->session->userdata('account_id') )
		{
			// 24 hours caching
			$this->output->cache($this->cache_expire);
		}

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
		/*
		 * Enable caching only in anonymous session
		 */
		if ( ! (bool) $this->session->userdata('account_id') )
		{
			// 24 hours caching
			$this->output->cache($this->cache_expire);
		}

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
	
	function account()
	{
		/*
		 * Security helper
		 */
		$this->load->helper(array('security', 'string', 'form'));
		
		// Access model 
		$this->load->model('Access', 'access');

		/*
		 * Backend language file
		 */
		$this->lang->load('elementar', $this->config->item('language'));

		/*
		 * Fields validation library
		 */
		$this->load->library('validation');

		/*
		 * Determine action by second URI segment
		 */
		$action = $this->uri->segment($this->SEGMENT_STEP + 2);
		
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
			
			/*
			 * Avoid pending group
			 */
			$group_id = $this->access->get_account_group($account_id);
			
			if ( (bool) $account_id && do_hash($password) == $this->access->get_account_password($account_id) && (int) $group_id != 2 )
			{
				$this->session->set_userdata('account_id', $account_id);
				$response = array(
					'done' => TRUE
				);
				$this->common->ajax_response($response);
				return;
			}
			else
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_login_incorret')
				);
				$this->common->ajax_response($response);
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
			$response = array(
				'done' => TRUE
			);
			$this->common->ajax_response($response);
			break;
			
			/************
			 * Register *
			 ************/
			case 'register' :
			if ( ! $this->input->is_ajax_request() )
				exit($this->lang->line('elementar_no_direct_script_access'));
			
			/*
			 * Other account fields
			 */
			$username = $this->input->post('username', TRUE);
			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			/*
			 * Assess account username
			 */
			$response = $this->validation->assess_username($username);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->common->ajax_response($response);
				return;
			}
	
			if ( (bool) $this->access->get_account_by_username($username) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_username_field_used')
				);
				$this->common->ajax_response($response);
				return;
			}

			/*
			 * Assess email
			 */
			$response = $this->validation->assess_email($email);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->common->ajax_response($response);
				return;
			}
			if ( (bool) $this->access->get_account_by_email($email) )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_field_used')
				);
				$this->common->ajax_response($response);
				return;
			}

			/*
			 * Assess password
			 */
			$response = $this->validation->assess_password($password);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->common->ajax_response($response);
				return;
			}
			
			/*
			 * Create account
			 */
			$register_hash = random_string('unique');
			$account_id = $this->access->put_account($username, $email, $password, $register_hash);
			/*
			 * Add acount to pending group
			 */
			$this->access->put_account_group($account_id, 2);

			/*
			 * Mail confirmation
			 */
			$this->email->subject("Confirmação de cadastro");
			$this->email->message(site_url('account/confirm') . '/' . $register_hash);
			$this->email->from('support@elementar.com', 'Elementar');
			$this->email->to($email);
			$this->email->send();

			$this->common->ajax_response($response);
			break;
			
			/********************************
			 * Confirm registration by hash *
			 ********************************/
			case 'confirm' :
			/*
			 * Search register hash
			 */
			$register_hash = $this->uri->segment($this->SEGMENT_STEP + 3);
			if ( (bool) $register_hash )
			{
				/*
				 * Check existence and pending account
				 */
				$account_id = $this->access->get_account_by_register_hash($register_hash);
				if ( (bool) $account_id && (int) $this->access->get_account_group($account_id) == 2 )
				{
					/*
					 * Detach account from pending group
					 * attach account to default group
					 */
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
			
			/*
			 * Assess email
			 */
			$response = $this->validation->assess_email($email);
			if ( (bool) $response['done'] == FALSE )
			{
				$this->common->ajax_response($response);
				return;
			}
			$account_id = $this->access->get_account_by_email($email);
			if ( ! (bool) $account_id )
			{
				$response = array(
					'done' => FALSE,
					'message' => $this->lang->line('elementar_xhr_email_not_found')
				);
				$this->common->ajax_response($response);
				return;
			}
			$reset_hash = random_string('unique');
			$this->access->put_account_reset_hash($account_id, $reset_hash);

			/*
			 * Mail confirmation
			 */
			$this->email->subject("Redefinir senha");
			$this->email->message(site_url('account/reset') . '/' . $reset_hash);
			$this->email->from('support@elementar.com', 'Elementar');
			$this->email->to($email);
			$this->email->send();
			
			$response = array(
				'done' => TRUE,
				'message' => $this->lang->line('elementar_xhr_reset_email_sent')
			);
			$this->common->ajax_response($response);
			break;
			
			/***********************
			 * Reset password form *
			 ***********************/
			case 'reset' :
			$reset_hash = $this->uri->segment($this->SEGMENT_STEP + 3);
			if ( (bool) $reset_hash )
			{
				/*
				 * Check existence and not pending account
				 */
				$account_id = $this->access->get_account_by_reset_hash($reset_hash);
				if ( (bool) $account_id && (int) $this->access->get_account_group($account_id) != 2 )
				{
					/*
					 * Show new password form
					 */
					$attributes = array('name' => 'reset_password', 'id' => 'reset_password');
					$hidden = array('reset_hash' => $reset_hash);
					$form = form_open('account/password', $attributes, $hidden);
					$attributes = array('name' => 'password', 'id' => 'password');
					$form .= form_password($attributes);
					$form .= form_submit('send', 'Enviar');
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
				/*
				 * Assess password
				 */
				$response = $this->validation->assess_password($password);
				if ( (bool) $response['done'] == FALSE )
				{
					$this->common->ajax_response($response);
					return;
				}
				else
				{
					/*
					 * Change password and erase reset hash
					 */
					$this->access->put_account_password($account_id, $password);
					$this->access->put_account_reset_hash($account_id, NULL);
					$response = array(
						'done' => TRUE,
						'message' => $this->lang->line('elementar_xhr_password_changed')
					);
					$this->common->ajax_response($response);
					return;
				}
			}
			$response = array(
				'done' => FALSE,
				'message' => $this->lang->line('elementar_xhr_not_allowed')
			);
			$this->common->ajax_response($response);
			break;
			
			default:
			/*
			 * Formulário de login
			 */
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
