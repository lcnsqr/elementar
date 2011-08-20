<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Parser extends CI_Parser {

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

		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				if ( is_array(current($val)) )
				{
					if (count($val) > 0 )
					{
						// element(s)
						$template = $this->_parse_pair($key, $val, $template);
					}
				}
				else
				{
					/*
					 * Value with properties
					 */
					if (count($val) > 0 )
					{
						$template = $this->_parse_single_with_properties($key, $val, $template);
					}
				}
			}
			else
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
			foreach ($row as $key => $val)
			{
				if ( ! is_array($val))
				{
					$temp = $this->_parse_single($key, $val, $temp);
				}
				else
				{
					//$temp = $this->_parse_pair($key, $val, $temp);
					if ( is_array(current($val)) )
					{
						if (count($val) > 0 )
						{
							// element(s)
							$temp = $this->_parse_pair($key, $val, $temp);
						}
					}
					else
					{
						/*
						 * Value with properties
						 */
						if (count($val) > 0 )
						{
							$temp = $this->_parse_single_with_properties($key, $val, $temp);
						}
					}
				}
			}

			$str .= $temp;
		}

		return str_replace($match['0'], $str, $string);
	}

	/**
	 *  Parse a single key/value with properties (aka images)
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _parse_single_with_properties($key, $properties, $string)
	{
		foreach($properties as $property => $val)
		{
			$string = $this->_parse_single($key . '.' . $property, (string)$val, $string);
		}
		return $string;
	}

}

/* End of file MY_Parser.php */
/* Location: ./application/controllers/MY_Parser.php */
