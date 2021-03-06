<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to string helper
 *
 * Random string function
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */

// ------------------------------------------------------------------------

/**
 * Random string
 *
 * Returns random string of specified length
 * Different than CI string helper since it removes vowels from pool, preventing offensive word generation
 *
 * @access	public
 * @param   int     optional string length to return
 * @return	string
 */
if ( ! function_exists('random_string'))
{
	function random_string($length = 8)
	{
		$result = "";
		$pool = "0123456789bcdfghjkmnpqrstvwxyz";
		for ($i = 0; $i < $length; $i++)
		{
			$char = substr($pool, mt_rand(0, strlen($pool)-1), 1);
			$result .= $char;
		}
		return $result;
	}
}

// ------------------------------------------------------------------------

/**
 * Reduce spacing
 *
 * Removes any double spacing
 *
 * @access	public
 * @param   string  string to parse
 * @return	string
 */
if ( ! function_exists('reduce_spacing'))
{
	function reduce_spacing($value)
	{
		// Remove all double spacing
		$value = preg_replace('/\s+/'," ",trim($value));
		$value = preg_replace('/\_+/',"_",$value);
		return preg_replace('/\-+/',"-",$value);
	}
}

// ------------------------------------------------------------------------

/**
 * Shift generated view over
 *
 * Generate properly indented html
 *
 * @access	public
 * @param   string  html to shift
 * @param   int     number of tabs to shift by
 * @return	string
 */
if ( ! function_exists('shift_over'))
{
	function shift_over($html, $tabs)
	{
		// Remove trailing new line
		$html = preg_replace("/\n$/m", "", $html);
		$spacing = "";
		for ($i = 0; $i < $tabs; $i++)
		{
			$spacing .= "\t";
		}
		return str_replace("\n", "\n".$spacing, $html)."\n";
	}
}


// ------------------------------------------------------------------------

/**
 * To friendly name
 *
 * Generate friendly name
 *
 * @access	public
 * @param   string  string to parse
 * @return	string
 */
if ( ! function_exists('to_friendlyname'))
{
	function to_friendlyname($value)
	{
		// Remove all double spacing
		$value = reduce_spacing($value);

		// Remove anything not a letter, number, dash, or underscore
		$filter = 'a-z0-9-_ ';

		return preg_replace('/[^'.$filter.']+/i',"", $value);
	}
}

// ------------------------------------------------------------------------

/**
 * To url name
 *
 * Generate url friendly name
 *
 * @access	public
 * @param   string  string to parse
 * @param   bool    option to preserve slash
 * @return	string
 */
if ( ! function_exists('to_urlname'))
{
	function to_urlname($value, $removeslash = TRUE)
	{
		// Remove all double spacing
		$value = reduce_spacing($value);

		// Convert remaining spacing to dashes
		$value = preg_replace("/\s/","-",$value);

		// Lowercase
		$value = strtolower($value);

		// Remove anything not a letter, number, dash, or underscore
		$filter = 'a-z0-9-_';

		if (!$removeslash)
		{
			$filter .= '\/';
		}

		return preg_replace('/[^'.$filter.']+/i',"", $value);
	}
}