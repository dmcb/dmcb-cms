<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb picture helper
 *
 * Generates profile pictures and stock images
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
 
// ------------------------------------------------------------------------

/**
 * Generate profile picture
 *
 * Creates a profile picture of any size and defaults to gravatar
 *
 * @access	public
 * @param	string/int  size as 'small' or 'large' or specified number
 * @return	string      profile picture HTML
 */
if ( ! function_exists('generate_profile_picture'))
{
	function generate_profile_picture($size, $user, $display_name = NULL, $email = NULL)
	{
		$CI =& get_instance();
		
		$default_image = base_url().'includes/images/avatar.png';
		
		$square = FALSE;
		if (!is_int($size) && !ctype_digit($size))
		{
			if ($size == "large")
			{
				$default_image = base_url().'includes/images/avatar_large.png';
				$size = $CI->config->item('dmcb_avatar_size_large');
				if ($CI->config->item('dmcb_avatar_size_large_square') == "true")
				{
					$square = TRUE;
				}
			}
			else
			{
				$size = $CI->config->item('dmcb_avatar_size_small');
				if ($CI->config->item('dmcb_avatar_size_small_square') == "true")
				{
					$square = TRUE;
				}
			}
		}		
		
		if ($user == NULL)
		{
			$result = '<img src="http://www.gravatar.com/avatar/'.md5($email).'?s='.$size.'&amp;d='.$default_image.'" alt="'.$display_name.'" class="avatar" />';
		}
		else if ($user['avatar'] == NULL)
		{
			$result = '<img src="http://www.gravatar.com/avatar/'.md5($email).'?s='.$size.'&amp;d='.$default_image.'" alt="'.$user['displayname'].'" class="avatar" />';
		}
		else
		{
			$result = '<img src="'.$CI->config->slash_item('base_url').$user['avatar'].'/'.$size;
			if ($square)
			{
				$result .= '/'.$size;
			}
			$result .= '" alt="'.$user['displayname'].'" class="avatar" />';
		}

		if (isset($user['enabledprofile']) && $user['enabledprofile'])
		{
			$result = '<a href="'.$CI->config->slash_item('base_url').'profile/'.$user['urlname'].'">'.$result.'</a>';
		}
		
		return $result;
	}
}

// ------------------------------------------------------------------------

/**
 * Stock image
 *
 * Gets a random stock image that will be persistent for the post
 *
 * @access	public
 * @param	string
 * @return	string   image URL
 */
 if ( ! function_exists('stock_image'))
{
	function stock_image($id)
	{
		$CI =& get_instance();
		$CI->load->model('files_model');
		$stockimages = $CI->files_model->get_stockimages();
		$count = $stockimages->num_rows();
		
		if ($count == 0)
		{
			return NULL;
		}
		else
		{
			srand($id);
			$stockimage = $stockimages->row_array(rand()%$count);
			$file = instantiate_library('file', $stockimage['fileid']);
			return $file->file;
		}
	}
}
 