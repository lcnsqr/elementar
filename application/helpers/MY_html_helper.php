<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('div_open'))
{
	function div_open($attributes = '')
	{
		$div = '<div';
		$div .= _parse_tag_attributes($attributes);
		$div .= '>';
		return $div;
	}
}

if ( ! function_exists('div_close'))
{
	function div_close($extra = '')
	{
		return "</div>".$extra;
	}
}

if ( ! function_exists('span'))
{
	function span($text, $attributes = '')
	{
		$span = '<span';
		$span .= _parse_tag_attributes($attributes);
		$span .= '>';
		$span .= $text;
		$span .= '</span>';
		return $span;
	}
}

if ( ! function_exists('paragraph'))
{
	function paragraph($text, $attributes = '')
	{
		$span = '<p';
		$span .= _parse_tag_attributes($attributes);
		$span .= '>';
		$span .= $text;
		$span .= '</p>';
		return $span;
	}
}

if ( ! function_exists('anchor'))
{
	function anchor($text, $attributes = '')
	{
		$anchor = '<a';
		$anchor .= _parse_tag_attributes($attributes);
		$anchor .= '>';
		$anchor .= $text;
		$anchor .= '</a>';
		return $anchor;
	}
}

if ( ! function_exists('_parse_tag_attributes'))
{
	function _parse_tag_attributes($attributes)
	{
		if ( is_array($attributes) )
		{
			$att = '';
	
			foreach ($attributes as $key => $val)
			{
				$att .= $key . '="' . $val . '" ';
			}
	
			return ' '.$att;
		}
	}
}
