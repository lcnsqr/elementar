<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validation {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	function __construct()
	{
		$this->CI =& get_instance();
		
	}

	/*
	 * Assess account user
	 */
	function assess_user($value)
	{
		/*
		 * Default reponse
		 */
		$response = array(
			'done' => TRUE
		);
		
		if ( strlen(trim($value)) == 0 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_user_field_missing')
			);
		}	
		if ( preg_match("/[^a-z0-9_]/i", $value) ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_user_field_invalid_chars')
			);
		}		
		if ( strlen(trim($value)) < 5 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_user_field_small')
			);
		}	
		if ( strlen(trim($value)) > 20 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_user_field_big')
			);
		}
		return $response;
	}
	
	/*
	 * Assess email
	 */
	function assess_email($value)
	{
		/*
		 * Default reponse
		 */
		$response = array(
			'done' => TRUE
		);

		if ( strlen(trim($value)) == 0 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_email_field_missing')
			);
		}	
		if ( ! preg_match("/^[a-z0-9]+([_\.%!][_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $value)) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_email_field_invalid')
			);
		}
		list($login, $host) = explode("@", $value);
		if ( ! checkdnsrr($host, "MX") ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_email_field_invalid')
			);
		}
		return $response;
	}
	
	function assess_password($value)
	{
		/*
		 * Default reponse
		 */
		$response = array(
			'done' => TRUE
		);

		/*
		 * Assess password
		 */
		if ( strlen(trim($value)) == 0 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_password_field_missing')
			);
		}
		if ( (bool) $value && ! preg_match("#.*^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $value) )
		{
			/*
			 * Missing either letter, caps or number
			 */
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_password_field_missing_chars')
			);
		}
		if ( strlen($value) < 6 )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_password_field_small')
			);
		}
		if ( strlen($value) > 20 )
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_password_field_big')
			);
		}
		
		return $response;
	}
}
