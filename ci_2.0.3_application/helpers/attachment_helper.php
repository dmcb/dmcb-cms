<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb attachment helper
 *
 * Grabs attachment information
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
 * Attached ID
 *
 * Generates a files attachedto ID
 *
 * @access	public
 * @param	string	type for attachment
 * @param	string  id for attachment
 * @return	string
 */
if ( ! function_exists('attached_id'))
{
 	function attached_id($attachedto, $attachedid)
	{
		// Turn flat file name into nested directory file name
		$attachedid = str_replace("+","/",$attachedid);
		// Convert URL name to respective user/page/post id if attached to one
		if ($attachedto == "user")
		{
			$user = instantiate_library('user', $attachedid, 'urlname');
			return $user->user['userid'];
		}
		else if ($attachedto == "page")
		{
			$page = instantiate_library('page', $attachedid, 'urlname');
			return $page->page['pageid'];
		}
		else if ($attachedto == "post")
		{
			$post = instantiate_library('post', $attachedid, 'urlname');
			return $post->post['postid'];
		}
		else
		{
			return NULL;
		}
	}
}