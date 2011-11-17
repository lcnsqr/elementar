<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Sample {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * i18n settings
	 */
	private $LANG = 'por';
	private $URI_PREFIX = '';
	
	function __construct($params)
	{
		$this->CI =& get_instance();
		
		/*
		 * i18n: Default language
		 */
		$this->LANG = $params['lang'];
		$this->URI_PREFIX = $params['uri_prefix'];
		
	}
	
	function main()
	{
		echo 'Esse é o main';
	}

	function testing()
	{
		echo 'Esse é o testing';
	}
	
}
