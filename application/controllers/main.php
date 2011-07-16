<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		// DB
		$this->db_cms = $this->load->database('cms', TRUE);

		// Client model 
		$this->load->model('M_cms', 'cms', TRUE);
		
		// CMS Common Library
		$this->load->library('common');

		// Site specific Library
		$this->load->library('special');
	}

	function index()
	{

		/*
		 * client controller (javascript)
		 */
		$js = array(
			'/js/jquery-1.5.min.js',
			'/js/jquery.easing.1.3.js',
			'/js/jquery.timers-1.2.js'
		);
		
		/*
		 * Metafields
		 */
		$metafields = $this->cms->get_meta_fields();

		$data = array(
			'title' => $this->config->item('site_name'),
			'metafields' => $metafields,
			'js' => $js
		);

		/*
		 * Carregar menus
		 */
		$data = array_merge($data, $this->common->get_menus());

		/*
		 * Carregar view
		 */
		$this->load->view('main', $data);
	}
	
}

/* End of file main.php */
/* Location: ./application/controllers/main.php */
