<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);
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
