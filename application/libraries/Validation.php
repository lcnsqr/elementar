<?php 
/*
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
	 * Assess account username
	 */
	function assess_username($value)
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
				'message' => $this->CI->lang->line('elementar_xhr_username_field_missing')
			);
		}	
		if ( preg_match("/[^a-z0-9_]/i", $value) ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_username_field_invalid_chars')
			);
		}		
		if ( strlen(trim($value)) < 5 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_username_field_small')
			);
		}	
		if ( strlen(trim($value)) > 20 ) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_username_field_big')
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
			return $response;
		}	
		if ( ! preg_match("/^[a-z0-9]+([_\.%!][_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $value)) 
		{
			$response = array(
				'done' => FALSE,
				'message' => $this->CI->lang->line('elementar_xhr_email_field_invalid')
			);
			return $response;
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
