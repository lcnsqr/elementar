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

class MY_Parser extends CI_Parser {

	// --------------------------------------------------------------------

	/**
	 *  Parse a String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function parse_string($template, $data, $return = FALSE)
	{
		return $this->_parse($template, $data, $return);
	}

	/**
	 *  Parse a Partial String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with the data in the second param.
	 * Partial template requested by ajax
	 * 
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function parse_string_partial($template, $data, $return = FALSE)
	{
		$template = $this->_parse_pair(key($data), current($data), $template);

		if ($return == FALSE)
		{
			$CI =& get_instance();
			$CI->output->append_output($template);
		}

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function _parse($template, $data, $return = FALSE)
	{
		if ($template == '')
		{
			return FALSE;
		}

		/*
		 * Parse pair loops & composite fields first
		 * to avoid fields inside loop of being replaced
		 * by content fields with the same name
		 */
		foreach ($data as $key => $val)
		{
			// Partial pairs loaded by ajax are
			// replaced by javascript code
			switch ( $key )
			{
				case 'brothers' :
				case 'children' :
					$template = $this->_parse_partial($key, $val, $template);
				break;

				default :
					if (is_array($val))
					{
						$template = $this->_parse_pair($key, $val, $template);
					}
				break;
			}
		}
		/*
		 * Parse content fields
		 */
		foreach ($data as $key => $val)
		{
			if ( ! is_array($val))
			{
				$template = $this->_parse_single($key, (string)$val, $template);
			}
		}

		if ($return == FALSE)
		{
			$CI =& get_instance();
			$CI->output->append_output($template);
		}

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a single key/value
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _parse_single($key, $val, $string)
	{
		$string = $this->_match_if($string, $key, $val);
		return str_replace($this->l_delim.$key.$this->r_delim, $val, $string);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse a partial tag pair
	 *
	 * Parses tag pairs:  {some_tag} string... {/some_tag}
	 * and replaces by javascript code
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _parse_partial($variable, $value, $string)
	{
		// Mark pair order if there is more than one
		$order = 0;
		while ($match = $this->_match_pair($string, $variable))
		{
			$uri_prefix = $value[0];
			$content_id = $value[1];
	
			$partial_id = 'partial_' . random_string('unique');
	
			$script = <<<PART
<script id="$partial_id">if(typeof XMLHttpRequest=="undefined"){XMLHttpRequest=function(){try{return new ActiveXObject("Msxml2.XMLHTTP.6.0")}catch(a){}try{return new ActiveXObject("Msxml2.XMLHTTP.3.0")}catch(a){}try{return new ActiveXObject("Microsoft.XMLHTTP")}catch(a){}throw new Error("This browser does not support XMLHttpRequest.")}}var $partial_id=new XMLHttpRequest;{$partial_id}.onreadystatechange=function(){if({$partial_id}.readyState==4&&{$partial_id}.status==200){var a=document.getElementById("$partial_id");a.insertAdjacentHTML("afterend",{$partial_id}.responseText);a.parentNode.removeChild(a)}};{$partial_id}.open("GET","$uri_prefix/main/partial/$variable/$content_id/$order",true);{$partial_id}.send()</script>
PART;
			$pos = strpos($string, $match['0']);
			$string = substr_replace($string, $script, $pos, strlen($match['0']));
			
			$order++;
		}
		return $string;
	}

	/**
	 *  Parse a tag pair
	 *
	 * Parses tag pairs:  {some_tag} string... {/some_tag}
	 *
	 * @access	private
	 * @param	string
	 * @param	array
	 * @param	string
	 * @return	string
	 */
	function _parse_pair($variable, $data, $string)
	{
		if (FALSE === ($match = $this->_match_pair($string, $variable)))
		{
			return $string;
		}

		$str = '';
		foreach ($data as $row)
		{
			$temp = $match['1'];

			/*
			 * Subarray check
			 */
			if (is_array($row))
			{
				foreach ($row as $key => $val)
				{
					$temp = $this->_parse_pair($key, $val, $temp);
				}
			}

			foreach ($row as $key => $val)
			{
				if ( ! is_array($val))
				{
					$temp = $this->_parse_single($key, $val, $temp);
				}
				else
				{
					$temp = $this->_parse_pair($key, $val, $temp);
				}
			}

			$str .= $temp;
		}
		
		$string = str_replace($match['0'], $str, $string);

		/*
		 * Recursive to find same pair loop in template
		 */
		return $this->_parse_pair($variable, $data, $string);
	}

	// --------------------------------------------------------------------

	/**
	 *  Matches and tests a if conditional statement
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _match_if($string, $key, $val)
	{
		if ( preg_match("|" . preg_quote($this->l_delim) . 'if ' . $key . "(.+?)" . preg_quote($this->r_delim) . "(.+?)" . preg_quote($this->l_delim) . '/if' . preg_quote($this->r_delim) . "|s", $string, $match))
		{
			$test = $match[1];
			if ( eval('if ( "' . addslashes($val) . '" ' . $test . ' ) return TRUE ;') )
			{
				// good. Remove if statement
				$string = str_replace($match[0], $match[2], $string);
			}
			else
			{
				// bad. Remove all segment
				$string = str_replace($match[0], '', $string);
			}
		}
		/*
		 * Recursive to check for another if conditional
		 */
		if ( preg_match("|" . preg_quote($this->l_delim) . 'if ' . $key . "(.+?)" . preg_quote($this->r_delim) . "(.+?)" . preg_quote($this->l_delim) . '/if' . preg_quote($this->r_delim) . "|s", $string, $match))
		{
			$string = $this->_match_if($string, $key, $val);
		}
		return $string;
	}

}
