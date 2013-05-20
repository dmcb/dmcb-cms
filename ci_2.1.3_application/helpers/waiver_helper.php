<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb template helper
 *
 * Functions to use the appropriate templates and blocks for a page
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
 * Template to use
 *
 * Returns the id of the first parent specifying a template or block as a default
 *
 * @access	public
 * @param	string	type
 * @param   array   page tree
 * @return	string
 */
if ( ! function_exists('check_waiver'))
{
 	function check_waiver($page_tree = NULL, $userid)
	{
		$CI =& get_instance();
		$CI->load->model('waivers_model');

		// Crawl through page tree array, returning the first waiver specified
		if (isset($page_tree))
		{
			foreach ($page_tree as $pageid)
			{
				$waiver = $CI->waivers_model->get($pageid, $userid);
				if ($waiver != NULL)
				{
					$last_acknowledged = $CI->waivers_model->check($waiver['waiverid'], $userid);
					if ($last_acknowledged == NULL || ($waiver['frequency'] != 0 && ($waiver['frequency'] < (time()-strtotime($last_acknowledged))/86400)))
					{
						return $waiver;
					}
				}
			}
		}

		return NULL;
	}
}
