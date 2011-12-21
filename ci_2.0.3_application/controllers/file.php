<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class File extends MY_Controller {

	function File()
	{
		parent::__construct();
	}

	function _remap()
	{
		// Check for hot linking
		if ($this->config->item('dmcb_file_hotlinking') == "false" &&
			(isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], $this->config->item('dmcb_file_hotlinking_whitelist'))) &&
			(!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], base_url()) !== 0))
		{
			$message = 'You are not permitted to link directly to this file.';
			if ($this->uri->segment(2) != "site")
			{
				$url = base_url();
				if ($this->uri->segment(2) != "post" && $this->uri->segment(2) != "page")
				{
					if ($this->uri->segment(2) == "user")
					{
						$url .= 'profile/';
					}
					else
					{
						$url .= $this->uri->segment(2).'/';
					}
				}
				$i = 3;
				while (strpos($this->uri->segment($i), '.') === FALSE && $this->uri->segment($i) != NULL)
				{
					$url .= $this->uri->segment($i).'/';
					$i++;
				}
				$message .= ' This file is available at <a href="'.$url.'">'.$url.'</a>';
			}
			$available = FALSE;
			$this->_message(
				'Error',
				$message
			);
		}
		else
		{
			$available = TRUE;
			$embedded = TRUE;
			$downloadpath = "files";
			$downloadfilename = NULL;
			$downloadextension = NULL;
			$downloadwidth = NULL;
			$downloadheight = NULL;

			// Grab file name and width/height properties if given from URL, this is the OLD way, and it is to be removed
			$lastsegmentoffset = 0;
			if (ctype_digit($this->uri->segment($this->uri->total_segments()-1)) && ctype_digit($this->uri->segment($this->uri->total_segments()))) //check if last two segments are width and height numbers
			{
				$downloadwidth = $this->uri->segment($this->uri->total_segments()-1);
				$downloadheight = $this->uri->segment($this->uri->total_segments());
				$lastsegmentoffset = 2;
			}
			else if (ctype_digit($this->uri->segment($this->uri->total_segments()))) // Check if last segment is width number
			{
				$downloadwidth = $this->uri->segment($this->uri->total_segments());
				$lastsegmentoffset = 1;
			}

			 // Assemble the download details from the URI segments
			$nestedname = FALSE;
			$attachedto = $this->uri->segment(2);
			$attachedid = "";
			for ($i=2; $i< $this->uri->total_segments()-$lastsegmentoffset; $i++)
			{
				// Convert nested file path of page or post to a flat folder structure internally
				if ($this->uri->segment($i-2) == "page" || $this->uri->segment($i-2) == "post") // If the file's a page or post, it could be nested
				{
					$nestedname = TRUE;
				}

				if ($nestedname && $i != $this->uri->total_segments()-$lastsegmentoffset) // If nested mode is on and the segment isn't the last, it's a nested page so convert to flat folder structure
				{
					$downloadpath .= "+".$this->uri->segment($i);
				}
				else
				{
					$downloadpath .= "/".$this->uri->segment($i);
				}

				// Assemble the id of where the file is attached to
				if ($i > 2 && $i < $this->uri->total_segments()-$lastsegmentoffset)
				{
					$attachedid .= $this->uri->segment($i);
					if ($i < $this->uri->total_segments()-($lastsegmentoffset+1))
					{
						$attachedid .= "/";
					}
				}
			}

			// Final segment has to be the filename and file extension
			$filepieces = explode(".",$this->uri->segment($this->uri->total_segments()-$lastsegmentoffset));
			$downloadfilename = $filepieces[0];
			$downloadextension = $filepieces[sizeof($filepieces)-1];
			$downloadpath .= "/".$downloadfilename.".".$downloadextension;
			if (sizeof($filepieces) == 3 && ctype_digit($filepieces[1]))
			{
				$downloadwidth = $filepieces[1];
			}
			else if (sizeof($filepieces) == 4 && !ctype_digit($filepieces[1]) && ctype_digit($filepieces[2]))
			{
				$downloadheight = $filepieces[2];
			}
			else if (sizeof($filepieces) == 4 && ctype_digit($filepieces[1]) && ctype_digit($filepieces[2]))
			{
				$downloadwidth = $filepieces[1];
				$downloadheight = $filepieces[2];
			}

			// Cap maximum image height and width values if necessary, and round sizes based off config settings
			if ($downloadheight != NULL)
			{
				if ($downloadheight > $this->config->item('dmcb_max_image_height'))
				{
					$downloadheight = $this->config->item('dmcb_max_image_height');
				}
				$downloadheight = round($downloadheight/$this->config->item('dmcb_image_interval'))*$this->config->item('dmcb_image_interval');
			}
			if ($downloadwidth != NULL)
			{
				if ($downloadwidth > $this->config->item('dmcb_max_image_width'))
				{
					$downloadwidth = $this->config->item('dmcb_max_image_width');
				}
				$downloadwidth = round($downloadwidth/$this->config->item('dmcb_image_interval'))*$this->config->item('dmcb_image_interval');
			}

			if (!file_exists($downloadpath)) // If file is in managed area we can check db for additional security options
			{
				$available = FALSE; // Since file is managed, guilty until proven innocent
				$downloadpath = "files_managed".substr($downloadpath, strpos($downloadpath, "/"));; // Swap files for files_managed

				// Get internal attachedid from named attachedid
				$this->load->helper('attachment_helper');
				$attachedid = attached_id($attachedto, $attachedid);

				// Grab the file
				$file = instantiate_library('file', array($downloadfilename, $downloadextension, $attachedto, $attachedid), 'details');

				// Check if file also exists in the database correctly
				if (!isset($file->file['fileid']) || (isset($file->file['fileid']) && $attachedid == NULL && $attachedto != "site") || !file_exists($downloadpath))
				{
					$this->_message(
						'Error',
						"The file you have requested doesn't exist.",
						'Error '.$this->uri->uri_string()
					);
				}
				else
				{
					// The user is allowed to download the file until it's been proven that they aren't allowed
					$allowed = TRUE;

					// Get attached parents
					if ($attachedto == "post")
					{
						$post = instantiate_library('post', $attachedid);
						$page = instantiate_library('page', $post->post['pageid']);
					}
					else if ($attachedto == "page")
					{
						$page = instantiate_library('page', $attachedid);
					}

					// Files that are the post image will show regardless if it's protected
					if (!isset($post->post['postid']) || isset($post->post['postid']) && $post->post['imageid'] != $file->file['fileid'])
					{
						$this->load->model('subscriptions_model');

						// Log the user on to see if they are allowed permission to edit the file
						$allowed_to_edit = FALSE;
						if ($attachedto == "post")
						{
							$allowed_to_edit = $this->acl->allow('post', 'edit', TRUE, 'post', $post->post['postid']);
						}
						else if ($attachedto == "page")
						{
							$allowed_to_edit = $this->acl->allow('page', 'edit', TRUE, 'page', $page->page['pageid']);
						}

						if (((isset($post->post['postid']) && ($post->post['featured'] == -1 || $post->post['published'] == 0)) || (isset($page->page['pageid']) && !$page->page['published'])) && !$allowed_to_edit)
						{
							// File is attached to post that has been heldback or not published, or the parent page is not published, and the user isn't allowed to edit the post, deny them
							$allowed = FALSE;
							$this->_access_denied();
						}
						else if (isset($page->page['pageid']) && $page->page['published'] && $page->page['protected'] && !$allowed_to_edit && !$this->acl->access($page->page['protection'], $page, NULL, TRUE))
						{
							// File is attached to post has a parent page, it is published, but the page is protected and the user isn't allowed to view the page or edit the post, so deny them
							$allowed = FALSE;
							$this->_access_denied();
						}
						else if ($this->acl->enabled('site', 'subscribe') && ((isset($post->post['postid']) && $post->post['needsubscription']) || (isset($page->page['pageid']) && $page->page['needsubscription'])) && !$this->subscriptions_model->check($this->session->userdata('userid')) && (isset($post->post['postid']) && !$this->subscriptions_model->check_view($_SERVER['REMOTE_ADDR'],$post->post['postid'])) && !$allowed_to_edit)
						{
							// File is behind a subscription and the users subscription isn't valid
							if ($attachedto == "page" || $this->config->item('dmcb_post_subscriptions_free_views') == 0 || (isset($page->page['pageid']) && $page->page['needsubscription']))
							{
								$this->message = "'".$file->file['filename'].".".$file->file['extension']."' requires a subscription to view.";
							}
							else if ($this->config->item('dmcb_post_subscriptions_free_views') == 1)
							{
								$this->message = "You have used your ".$this->config->item('dmcb_post_subscriptions_free_views')." free ".$this->config->item('dmcb_post_subscriptions_free_views_range')." view on posts that require a subscription and won't be able to read '".$file->file['filename'].".".$file->file['extension']."'.";
							}
							else
							{
								$this->message = "You have used your ".$this->config->item('dmcb_post_subscriptions_free_views')." free ".$this->config->item('dmcb_post_subscriptions_free_views_range')." views on posts that require a subscription and won't be able to read '".$file->file['filename'].".".$file->file['extension']."'.";
							}

							if ($this->session->userdata('signedon'))
							{
								$this->message .= "<br/><br/>Your subscription has ended, you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
							}
							else
							{
								$this->message .= "<br/><br/>If you do have a subscription, please <a href=\"".base_url()."signon".uri_string()."\">sign on</a>.<br/>If you don't have a subscription, you can start a free trial by creating an account <a href=\"".base_url()."signon\">here</a>, or you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
							}
							$allowed = FALSE;
							$this->_message('Subscription required', $this->message);
						}
					}

					if ($allowed) // Download is now available, but is it listed as a download, or embedded?
					{
						$available = TRUE;
						if ($file->file['listed'] == 1)
						{
							// The file is not embedded, so count a download
							$embedded = FALSE;
							$file->new_file['downloadcount']++;
							$file->save();
						}
					}
				}
			}

			if ($available)
			{
				// If the file is meant to be rendered within a webpage, check if it's an image and run resizing, otherwise just cough it up
				if ($embedded)
				{
					// Attempt to grab image information
					$info = @getimagesize($downloadpath);

					// If the file is an image, we can extract out it's mime type and original dimensions
					if (isset($info[0]))
					{
						header("Content-type: ".$info['mime']);
					}
					else
					{
						$this->load->helper('file');
						header("Content-type: ".get_mime_by_extension($downloadpath));
					}

					// Caching information, perhaps make this smarter depending on the type of file?
					header("Expires: ".gmdate("D, d M Y H:i:s", time() + 3600)." GMT");

					// Resize if an image
					if (isset($info[0]))
					{
						if (isset($downloadwidth) && isset($downloadheight))
						{
							$downloadpath_with_dimensions = $downloadpath.".".$downloadwidth.".".$downloadheight;
						}
						else if (isset($downloadwidth))
						{
							$ratio = $info[0]/$info[1];
							$height = floor($downloadwidth/$ratio);
							if ($height > $this->config->item('dmcb_max_image_height'))
							{
								$height = $this->config->item('dmcb_max_image_height');
								$downloadwidth = floor($ratio*$height);
							}
							$downloadpath_with_dimensions = $downloadpath.".".$downloadwidth;
						}
						else if (isset($downloadheight))
						{
							$ratio = $info[0]/$info[1];
							$width = floor($downloadheight/$ratio);
							if ($width > $this->config->item('dmcb_max_image_width'))
							{
								$width = $this->config->item('dmcb_max_image_width');
								$downloadheight = floor($ratio*$width);
							}
							$downloadpath_with_dimensions = $downloadpath."..".$downloadheight;
						}
						else // If no width or height was specified, ensure the image comes in under the size of the maximum dimensions
						{
							$downloadwidth = $info[0];
							$downloadheight = $info[1];
							if ($downloadwidth > $this->config->item('dmcb_max_image_width'))
							{
								$downloadwidth = $this->config->item('dmcb_max_image_width');
							}
							$ratio = $info[0]/$info[1];
							$height = floor($downloadwidth/$ratio);
							if ($height > $this->config->item('dmcb_max_image_height'))
							{
								$height = $this->config->item('dmcb_max_image_height');
								$downloadwidth = floor($ratio*$height);
							}

							if ($downloadwidth == $info[0] && $downloadheight == $info[1])
							{
								$downloadpath_with_dimensions = $downloadpath;
							}
							else
							{
								$downloadpath_with_dimensions = $downloadpath.".".$downloadwidth;
							}
						}

						// If the file hasn't already been resized to those dimensions, do a resize
						if (!file_exists($downloadpath_with_dimensions) || !filesize($downloadpath_with_dimensions))
						{
							$this->_image_resize($downloadwidth, $downloadheight, $downloadpath, $downloadpath_with_dimensions);
						}
						header("Content-length: ".filesize($downloadpath_with_dimensions));
						readfile($downloadpath_with_dimensions);
					}
					else
					{
						header("Content-length: ".filesize($downloadpath));
						readfile($downloadpath);
					}
				}
				else // File was meant to be downloaded, not embedded, so serve it up as a download
				{
					header("Content-type: application/force-download");
					header("Content-Transfer-Encoding: Binary");
					header("Content-length: ".filesize($downloadpath));
					header("Content-disposition: attachment; filename=\"".basename($downloadpath)."\"");
					readfile($downloadpath);
				}
			}
		}
	}

	function _image_resize($newwidth = NULL, $newheight = NULL, $source, $dest)
	{
		$info = @getimagesize($source);
		$type = substr(strrchr($info['mime'], '/'), 1);

		switch ($type)
		{
			case 'jpeg':
			$image_create_func = 'ImageCreateFromJPEG';
			$image_save_func = 'ImageJPEG';
			break;

			case 'png':
			$image_create_func = 'ImageCreateFromPNG';
			$image_save_func = 'ImagePNG';
			break;

			case 'bmp':
			$image_create_func = 'ImageCreateFromBMP';
			$image_save_func = 'ImageBMP';
			break;

			case 'gif':
			$image_create_func = 'ImageCreateFromGIF';
			$image_save_func = 'ImageGIF';
			break;

			default:
			$image_create_func = 'ImageCreateFromJPEG';
			$image_save_func = 'ImageJPEG';
		}

		ini_set('memory_limit', '128M'); //hack for bad hosts (also try in .htaccess, 'php_value memory_limit 128M')
		$data = $image_create_func($source);

	    $width = $info[0];
	    $height = $info[1];
	    $ratio = $width/$height;

		$targetwidth = $newwidth;
		$targetheight = $newheight;

		$x_offset = 0;
		$y_offset = 0;

		if ($newheight == NULL)
		{
			$targetheight = floor($newwidth/$ratio);
		}
		else if ($newwidth == NULL)
		{
			$targetwidth = floor($newheight*$ratio);
		}

		$resizedimage = imagecreatetruecolor($targetwidth, $targetheight);
		imagealphablending($resizedimage, false);
		imagesavealpha($resizedimage,true);
		$transparent = imagecolorallocatealpha($resizedimage, 255, 255, 255, 127);
		imagefilledrectangle($resizedimage, 0, 0, $newwidth, $newheight, $transparent);

		if ($newheight != NULL && $newwidth != NULL) // We are specifying an exact size, so we will resize and crop
		{
			$widthscale = $newwidth/$width;
			$heightscale = $newheight/$height;
			if ($widthscale > $heightscale)
			{
				$targetwidth = $newwidth;
				$targetheight = $height*$widthscale;
				$y_offset = round(($newheight - $targetheight)/2);
			}
			else
			{
				$targetwidth = $width*$heightscale;
				$targetheight = $newheight;
				$x_offset = round(($newwidth - $targetwidth)/2);
			}
		}

		imagecopyresampled($resizedimage, $data, $x_offset, $y_offset, 0, 0, $targetwidth, $targetheight, $width, $height);

		if ($image_save_func == "ImageJPEG") $image_save_func($resizedimage,$dest,96);
		else $image_save_func($resizedimage,$dest);
		imagedestroy($data);
		imagedestroy($resizedimage);
	}
}