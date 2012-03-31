<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('backend_input_columns'))
{
	function backend_input_columns($label = '', $input = '')
	{
		$html = div_open(array('class' => 'form_content_field'));
		$html .= div_open(array('class' => 'form_window_column_label'));
		$html .= $label;
		$html .= br(1);
		$html .= div_close("<!-- form_window_column_label -->");
		$html .= div_open(array('class' => 'form_window_column_input'));
		$html .= $input;
		$html .= div_close("<!-- form_window_column_input -->");
		$html .= div_close("<!-- .form_content_field -->");
		return $html;
	}
}

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

if ( ! function_exists('hr'))
{
	function hr($attributes = '')
	{
		$hr = '<hr';
		$hr .= _parse_tag_attributes($attributes);
		$hr .= ' />';
		return $hr;
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
