<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to url helper
 *
 * Adds comment URL parsing and short url generation
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
 * Shorten URL
 *
 * Creates a short URL from the URL given
 *
 * @access	public
 * @param	string  url to shorten
 * @return	string
 */
if ( ! function_exists('shorten_url'))
{
	function shorten_url($url)
	{
		$CI =& get_instance();
		$curl = curl_init();
		if ($CI->config->item('dmcb_url_shortener') == "bit.ly")
		{
			curl_setopt($curl, CURLOPT_URL, 'http://api.bit.ly/v3/shorten?login='.$CI->config->item('dmcb_bitly_login').'&apiKey='.$CI->config->item('dmcb_bitly_key').'&longUrl='.urlencode($url).'&format=txt');
		}
		else // Default to old school Tiny URL shortener
		{
			curl_setopt($curl, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url='.urlencode($url));
		}
		curl_setopt($curl, CURLOPT_USERAGENT, $CI->config->item('dmcb_title')."/1.0 +http://".$CI->config->item('dmcb_server'));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$result = curl_exec($curl);
		curl_close($curl);
		return trim($result);
	}
}
