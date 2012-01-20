<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb pagination helper
 *
 * Generates pagination and styling
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
 * Generate pagination
 *
 * Grabs current page offset regardless of it's position in the URL
 * and generates offset number to use
 *
 * @access	public
 * @param	string	the total number of items to paginate through
 * @param	string  the number of items per page	
 * @return	string
 */
if ( ! function_exists('generate_pagination'))
{
	function generate_pagination($count, $perpage = 10, $page_base_url = NULL)
	{
		$CI =& get_instance();
		$offset = 0;
		
		// Get the URI segment of the pagination index
		$page_uri = get_pagination_uri();
		
		// If URI doesn't exist, we will place it
		if ($page_uri == NULL)
		{
			$page_uri = $CI->uri->total_segments()+2;
		}

		// Reconstruct base url of pagination by using everything but stripping the index value out 
		// This way subfunctions like 'addcomment' can be kept in the URL despite going through pages
		// If a page_base_url is already specified, then use specified value instead of dynamically generated one
		if ($page_base_url == NULL)
		{
			$page_base_url = base_url();
			for ($i=1; $i<=$CI->uri->total_segments(); $i++)
			{
				if ($i<$page_uri-1 && $CI->uri->segment($i) != NULL)
				{
					$page_base_url .= $CI->uri->segment($i).'/';
				}
			}
			
			if ($page_base_url == base_url()) // We are on a page that has no URL name, so therefore it's the default page
			{
				$page_base_url .= $CI->config->item('dmcb_default_page').'/';
			}
		}
		else
		{
			$page_base_url = base_url().$page_base_url.'/';
		}
		$page_base_url .= 'index';
		
		$CI->load->library('pagination');
		$config['per_page'] = $perpage;
		$config['full_tag_open'] = '<div class="pagination"><ul>';
		$config['full_tag_close'] = '</ul></div>';
		$config['first_link'] = '|<<';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_link'] = '>>|';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['next_link'] = '>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['prev_link'] = '<';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li><b>page ';
		$config['cur_tag_close'] = '</b></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['uri_segment'] = $page_uri;
		$config['base_url'] = $page_base_url;
		$config['total_rows'] = $count;
		$CI->pagination->initialize($config);

		if ($CI->uri->segment($page_uri) != NULL)
		{
			// If 10 items are allowed per page, and an index of 12 is specified (instead of expected multiple of 10) round down to nearest multiple of 10
			$offset = $CI->uri->segment($page_uri)-($CI->uri->segment($page_uri)%$perpage);
		}
		
		return $offset;
	}
}

// ------------------------------------------------------------------------

/**
 * Get pagination segment
 *
 * Grabs pagination index URI segment
 *
 * @access	public	
 * @return	integer  the number of the URI segment that has the pagination index value
 */
if ( ! function_exists('get_pagination_uri'))
{
	function get_pagination_uri()
	{
		$CI =& get_instance();
		
		$page_uri = NULL;
		// Find last possible URI segment where pagination index is specified
		for ($i=1; $i<=$CI->uri->total_segments(); $i++)
		{
			if ($CI->uri->segment($i) == "index") 
			{
				$page_uri = $i+1;
			}
		}
		return $page_uri;
	}
}
 