<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Sample {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * i18n settings
	 */
	private $LANG = 'en';
	private $URI_PREFIX = '';
	
	/*
	 * Enabling
	 */
	public static $ENABLED = FALSE; 
	
	function __construct($params)
	{
		$this->CI =& get_instance();
		
		/*
		 * i18n: Default language
		 */
		$this->LANG = $params['lang'];
		$this->URI_PREFIX = $params['uri_prefix'];
		
	}
	
	function index()
	{
		echo 'Sample addon main output';
	}

	function testing()
	{
		echo 'Sample addon testing method output';
	}
	
}
