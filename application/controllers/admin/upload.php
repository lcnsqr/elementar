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
		$this->elementar = $this->load->database('elementar', TRUE);

		/*
		 * Session model
		 */
		$this->load->model('M_session', 'sess');

		/*
		 * Account model
		 */
		$this->load->model('M_account', 'account');

		/*
		 * Content model
		 */
		$this->load->model('Crud', 'crud');
		
		/*
		 * CMS Common Library
		 */
		$this->load->library('common');

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
				'js' => array('/js/jquery-1.6.2.min.js', '/js/admin_session.js', '/js/jquery.timers-1.2.js', '/js/admin_client_warning.js'),
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
			'/js/jquery-1.6.2.min.js',
			'/js/jquery.easing.1.3.js',
			'/js/jquery.timers-1.2.js',
			'/js/admin_client_warning.js',
			'/js/admin_upload.js'
		);
		

		$data = array(
			'title' => $this->config->item('site_name'),
			'js' => $js,
			'is_logged' => $is_logged,
			'username' => $username
		);

		$data['upload'] = $this->common->render_form_upload_image("9999");
		
		$this->load->view("admin/admin_upload", $data);
		
	}

	/**
	 * Uploading status
	 */
	function xhr_read_upload_status()
	{
		if ( ! $this->input->is_ajax_request() )
			exit('No direct script access allowed');
		
		$upload_session_id = $this->input->post('upload_session_id', TRUE);

		$done = $this->crud->get_upload_session_done($upload_session_id);
		
		if ( (bool) $done )
		{
			$uri = $this->crud->get_upload_session_uri($upload_session_id);
			$name = $this->crud->get_upload_session_name($upload_session_id);
			$response = array(
				'done' => TRUE,
				'image_id' => $this->crud->get_upload_session_image_id($upload_session_id),
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
		/*
		 * Receive the upload session ID
		 */
		$upload_session_id = $this->input->post("upload_session_id", TRUE);
		
		/*
		 * Check file type an size ( < 10 MB ) before proceed
		 */
		if ((($_FILES["upload_file"]["type"] == "image/gif") 
		OR ($_FILES["upload_file"]["type"] == "image/jpeg") 
		OR ($_FILES["upload_file"]["type"] == "image/pjpeg") 
		OR ($_FILES["upload_file"]["type"] == "image/png")) 
		&& ($_FILES["upload_file"]["size"] < 10485760)) 
		{
			if ($_FILES["file"]["error"] > 0)
			{
				/*
				 * Mark an error in upload session
				 */
				$this->crud->put_upload_session($upload_session_id, "error", TRUE);
			}
			else
			{
				/*
				 * Receive file properties
				 */
				$tmp_name = $_FILES["upload_file"]["tmp_name"];
				$name = $_FILES["upload_file"]["name"];
				/*
				 * Move file to upload dir
				 */
				$uri = "/img/upload/" . time() . "_" . $name;
				move_uploaded_file($tmp_name, "." . $uri);
				
				/*
				 * Generate thumbnail
				 */
				$config['image_library'] = 'gd2';
				$config['source_image']	= "." . $uri;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']	 = 128;
				$config['height']	= 128;
				$this->load->library('image_lib', $config); 
				$this->image_lib->resize();
				$uri_thumb = substr($uri, 0, strrpos($uri, ".")) . "_thumb" . substr($uri, strrpos($uri, ".") - strlen($uri), strlen($uri));
				
				/*
				 * Write file properties to session
				 */
				$this->crud->put_upload_session($upload_session_id, "name", $name);
				$this->crud->put_upload_session($upload_session_id, "uri", $uri);
				$this->crud->put_upload_session($upload_session_id, "done", TRUE);
				
				/*
				 * Get image dimensions
				 */
				list($width, $height, $type, $attr) = getimagesize("." . $uri);
				/*
				 * Write image properties in image table
				 * (use file name for alt text until the 
				 * content/element is saved)
				 */
				$image_id = $this->crud->put_image($name, $uri, $uri_thumb, $width, $height);
				/*
				 * Put image id in session table
				 */
				$this->crud->put_upload_session($upload_session_id, "image_id", $image_id);
			}
		}
		else
		{
			/*
			 * Mark an error in upload session
			 */
			$this->crud->put_upload_session($upload_session_id, "error", TRUE);
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
