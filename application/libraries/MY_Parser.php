<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Parser extends CI_Parser {


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
			if ( eval("if ( '$val' $test ) return TRUE ;") )
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
		return $string;
	}

}
