<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to string helper
 *
 * Random string function
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
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
	function to_urlname($value, $removeslash = TRUE, $removespace = TRUE)
	{
		// Remove all double spacing
		$value = reduce_spacing($value);

		// Convert remaining spacing to dashes
		$value = strtolower(preg_replace("/\s/","-",$value));

		// Remove anything not a letter, number, dash, or underscore
		$filter = 'a-z0-9-_';

		if (!$removeslash)
		{
			$filter .= '\/';
		}
		if (!$removespace)
		{
			$filter .= ' ';
		}

		return preg_replace('/[^'.$filter.']+/i',"", $value);
	}
}