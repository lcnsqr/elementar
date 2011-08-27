<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contato extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->helper(array('string', 'security', 'cookie', 'form', 'html', 'text', 'url'));

		// DB
		$this->db_cms = $this->load->database('cms', TRUE);

		// Client model 
		$this->load->model('M_cms', 'cms', TRUE);
		
		// CMS Common Library
		$this->load->library('common');

		// Site specific Library
		$this->load->library('special');

		/*
		 * Email config
		 */
		$config = Array(
		    'protocol' => 'smtp',
		    'smtp_host' => $this->config->item('smtp_host'),
		    'smtp_port' => $this->config->item('smtp_port'),
		    'smtp_user' => $this->config->item('smtp_user'),
		    'smtp_pass' => $this->config->item('smtp_pass'),
		);
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");
	}

	function index()
	{

		/*
		 * client controller (javascript)
		 */
		$js = array(
			'/js/jquery-1.6.2.min.js',
			'/js/jquery.masonry.min.js',
			'/js/jquery.easing.1.3.js',
			'/js/jquery.timers-1.2.js',
			'/js/anchor.js',
			'/js/anchor_section.js',
			'/js/layout.js',
			'/js/form_contato.js'
		);
		
		/*
		 * Metafields
		 */
		$metafields = $this->cms->get_meta_fields();

		$data = array(
			'site_name' => $this->config->item('site_name'),
			'title' => 'Contato',
			'metafields' => $metafields,
			'js' => $js
		);
		
		$data['elements'] = $this->_retrieve_elements($this->cms->get_element_by_category());


		/*
		 * Carregar menus
		 */
		$data = array_merge($data, $this->common->get_menus());

		/*
		 * Carregar view
		 */
		//$this->load->view('main', $data);
		$this->load->view('contato', $data);
	}

	function _retrieve_elements($elements) 
	{
		$data = array();
		if ( $elements !== NULL )
		{
			foreach ($elements as $element)
			{

				$element_type_id = $this->cms->get_element_type($element['id']);
				$element_type = $this->cms->get_element_type_sname($element_type_id);
				$data[$element_type][$element['sname']]['name'] = $element['name'];
				$fields = $this->cms->get_element_type_fields($element_type_id);
				
				foreach ($fields as $field)
				{
					if ( $field['type'] == "img")
					{
						$value = $this->cms->get_image_uri($this->cms->get_element_field($element['id'], $field['id']));
						$data[$element_type][$element['sname']][$field['sname']] = ( strval($value) == "") ? "" : $value;
					}
					else
					{
						$value = $this->cms->get_element_field($element['id'], $field['id']);
						$data[$element_type][$element['sname']][$field['sname']] = ( strval($value) == "" ) ? "" : $value;
					}
				}
			}
		}
		return $data;
	}

	/*
	 * Formulário de contato
	 */
    function xhr_send_contato()
    {
		$required = array(
			'Nome' => array('value' => $this->input->post('Nome', TRUE), 'name' => 'Nome'),
			'Email' => array('value' => $this->input->post('Email', TRUE), 'name' => 'Email'),
			'TelefoneDDD' => array('value' => $this->input->post('TelefoneDDD', TRUE), 'name' => 'DDD'),
			'Telefone' => array('value' => $this->input->post('Telefone', TRUE), 'name' => 'Telefone')
		);

		$message = array(
			'Mensagem' => array('value' => $this->input->post('Mensagem', TRUE), 'name' => 'Mensagem')
		);

		/* 
		 * Verificação de campos obrigatórios 
		 */
		$valid = TRUE;

		$msg = "";
		$body = "Dados pessoais: \n\n";
		foreach ( $required as $name => $content )
		{
			if ( (bool) trim($content['value']) == FALSE )
			{
				$valid = FALSE;
				$msg .= "Informe o campo " . $content['name'] . "\n";
			}
			else 
			{
				$body .= "\t" . $content['name'] . ": " . $content['value'] . "\n";
			}
		}

		/*
		 * Mensagem
		 */		
		$body .= "\n\n" . "Mesagem:" . "\n\n";
		foreach ( $message as $name => $content )
		{
			if ( trim($content['value']) == "" )
			{
				$valid = FALSE;
				$msg .= "Informe o campo " . $content['name'] . "\n";
			}
			else 
			{
				$body .= $content['value'] . "\n\n";
			}
		}

		if ( (bool) $valid == FALSE )
		{
			$response = array(
				'done' => FALSE,
				'msg' => $msg
			);
	
			$this->common->ajax_response($response);
			
		}
		else
		{
			/*
			 * enviar email 
			 */
			$this->_email_contato($body, $this->input->post('Email', TRUE));
			
			$response = array(
				'done' => TRUE,
				'body' => $body
			);
		
			$this->common->ajax_response($response);
		}
	}


	function _email_contato($body, $from) {
		$this->email->from($from, 'lcnsqr@gmail.com');

		$this->email->to('lcnsqr@gmail.com');

		$this->email->subject('Contato Via Site');
		$this->email->message($body);
		
		$this->email->send();
		
		//$this->email->print_debugger();
		
	}
	
}

/* End of file contato.php */
/* Location: ./application/controllers/contato.php */
