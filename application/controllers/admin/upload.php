<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		/*
		 *  CI helpers
		 */
		$this->load->helper(array('string', 'security', 'cookie', 'form', 'html', 'text', 'url'));
		
		/*
		 * CI libraries
		 */
		$this->load->library('session');
		
		/*
		 * User/session database
		 */
		$this->db_acc = $this->load->database('account', TRUE);

		/*
		 * Content database
		 */
		$this->db_cms = $this->load->database('cms', TRUE);

		/*
		 * Session model
		 */
		$this->load->model('M_session', 'sess');

		/*
		 * Account model
		 */
		$this->load->model('M_account', 'account');

		/*
		 * Content model (admin)
		 */
		$this->load->model('M_cms_admin', 'cms');
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common');

		/*
		 * Site specific library
		 */
		$this->load->library('special');

		/*
		 * Verificar sessão autenticada
		 * de usuário autorizado no admin
		 */
		$user_id = $this->account->logged($this->sess->session_id());
		if ( $user_id === FALSE )
		{
			$data = array(
				'is_logged' => FALSE,
				'title' => $this->config->item('site_name'),
				'js' => array('/js/jquery-1.5.min.js', '/js/admin_session.js', '/js/jquery.timers-1.2.js', '/js/admin_client_warning.js'),
				'action' => '/' . uri_string(),
				'elapsed_time' => $this->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end')
			);
			$login = $this->load->view('admin/admin_login', $data, TRUE);
			exit($login);
		}

	}
	
	function index()
	{
		
		/*
		 * User info
		 */
		$user_id = $this->account->logged($this->sess->session_id());
		$is_logged = TRUE;
		$username = $this->account->get_user_name($user_id);

		/*
		 * client controller (javascript)
		 */
		$js = array(
			'/js/jquery-1.5.min.js',
			'/js/jquery.easing.1.3.js',
			'/js/jquery.timers-1.2.js',
			'/js/admin_client_warning.js',
			'/js/admin_upload.js',
			'/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js',
			'/js/jquery-ui-1.8.11.custom/js/jquery-ui-1.8.11.custom.min.js'
		);
		
		/*
		 * Resource menu
		 */
		$resource_menu = "<ul><li><a href=\"/admin/account\" title=\"Usuários\">Usuários</a></li><li>|</li><li><strong>Conteúdo</strong></li></ul>";

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username,
			'resource_menu' => $resource_menu
		);

		$data['upload'] = $this->common->render_form_upload_image("9999");
		
		$this->load->view("admin/admin_upload", $data);
		
	}

	/**
	 * Image uploading status
	 */
	function xhr_read_image_status()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$form_upload_session = $this->input->post('form_upload_session', TRUE);

		$done = $this->cms->get_upload_session_done($form_upload_session);
		
		if ( $done )
		{
			$uri = $this->cms->get_upload_session_uri($form_upload_session);
			$name = $this->cms->get_upload_session_name($form_upload_session);
			$response = array(
				'done' => TRUE,
				'image_id' => $this->cms->get_upload_session_image_id($form_upload_session),
				'name' => $name,
				'uri' => $uri,
				'thumb_uri' => substr($uri, 0, strrpos($uri, ".")) . "_thumb" . substr($uri, strrpos($uri, ".") - strlen($uri), strlen($uri))
			);
		}
		else 
		{
			$response = array(
				'done' => FALSE
			);
		}

		$this->common->ajax_response($response);
	}

	function send_image()
	{
		// session
		$form_upload_session = $this->input->post("form_upload_session", TRUE);
		
		if ((($_FILES["upload_image_field"]["type"] == "image/gif") 
		OR ($_FILES["upload_image_field"]["type"] == "image/jpeg") 
		OR ($_FILES["upload_image_field"]["type"] == "image/pjpeg") 
		OR ($_FILES["upload_image_field"]["type"] == "image/png")) 
		&& ($_FILES["upload_image_field"]["size"] < 10485760))
		{
			if ($_FILES["upload_image_field"]["error"] > 0)
			{
				/*
				echo "Error: " . $_FILES["file"]["error"] . "<br />";
				*/
				$this->cms->put_upload_session($form_upload_session, "error", TRUE);
			}
			else
			{
				/*
				echo "Upload: " . $_FILES["file"]["name"] . "<br />";
				echo "Type: " . $_FILES["file"]["type"] . "<br />";
				echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
				echo "Stored in: " . $_FILES["file"]["tmp_name"];
				*/
				$tmp_name = $_FILES["upload_image_field"]["tmp_name"];
				$name = $_FILES["upload_image_field"]["name"];
				$uri = "/img/upload/" . time() . "_" . $name;
				move_uploaded_file($tmp_name, "." . $uri);
				
				/*
				 * thumbnail
				 */
				$config['image_library'] = 'gd2';
				$config['source_image']	= "." . $uri;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']	 = 128;
				$config['height']	= 128;
				$this->load->library('image_lib', $config); 
				$this->image_lib->resize();
				
				$this->cms->put_upload_session($form_upload_session, "name", $name);
				$this->cms->put_upload_session($form_upload_session, "uri", $uri);
				$this->cms->put_upload_session($form_upload_session, "done", TRUE);
				
				/*
				 * Escrever imagem na tabela
				 */
				$uri_thumb = substr($uri, 0, strrpos($uri, ".")) . "_thumb" . substr($uri, strrpos($uri, ".") - strlen($uri), strlen($uri));
				$image_id = $this->cms->put_image($name, $uri, $uri_thumb);
				$this->cms->put_upload_session($form_upload_session, "image_id", $image_id);
			}
		}
		else
		{
			$this->cms->put_upload_session($form_upload_session, "error", TRUE);
		}
	}
		
	/**
	 * Cancelar envio de imagem
	 */
	function empty_iframe() 
	{
		echo NULL;
	}

	/**
	 * Remover imagem do conteudo
	 */	
	function xhr_erase_image()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		echo NULL;
		
	}

}
