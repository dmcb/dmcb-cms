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
class Upload extends MY_Controller {

	function Upload()
	{
		parent::__construct();
		$this->load->model('files_model');
	}

	function file()
	{
		ini_set('post_max_size', '128M');
		ini_set('upload_max_filesize', '120M');
		ini_set('max_execution_time', '360');
		ini_set('max_input_time', '360');

		$attachedid = NULL;
		$attachedto = $this->uri->segment(3);
		$attachedto_object = NULL;
		$attachedname = "";
		for ($i=4; $i <= $this->uri->total_segments(); $i++)
		{
			$attachedname .= $this->uri->segment($i);
			if ($i != $this->uri->total_segments())
			{
				$attachedname .= '/';
			}
		}

		if ($attachedto == "user") // Uploading to a user profile, limit to images
		{
			$config['allowed_types'] = str_replace('*.', '', str_replace(';','|',$this->config->item('dmcb_profile_upload_types')));
			$config['max_size']	= $this->config->item('dmcb_profile_upload_size');
			$attachedto_object = instantiate_library('user', $attachedname, 'urlname');
			$attachedid = $attachedto_object->user['userid'];
		}
		else // Upload any kind of files for site, page and post uploads
		{
			$config['allowed_types'] = str_replace('*.', '', str_replace(';','|',$this->config->item('dmcb_site_upload_types')));
			$config['max_size']	= $this->config->item('dmcb_site_upload_size');

			// Uploads directly to the site don't need an attachedid, but if it's uploaded to a post or page, grab it
			if ($attachedto == "page")
			{
				$attachedto_object = instantiate_library('page', $attachedname, 'urlname');
				$attachedid = $attachedto_object->page['pageid'];
			}
			else if ($attachedto == "post")
			{
				$attachedto_object = instantiate_library('post', $attachedname, 'urlname');
				$attachedid = $attachedto_object->post['postid'];
			}
		}

		// If the upload is replacing a file, grab the fileid of the file to replace
		$replacefileid = NULL;
		if (isset($_POST['replace']))
		{
			$object = instantiate_library('file', $_POST['replace']);
			if (isset($object->file['fileid']))
			{
				// Confirm we are replacing a file on the same area (i.e. same post or page)
				if ($object->file['attachedto'] == $attachedto && $object->file['attachedid'] == $attachedid)
				{
					$replacefileid = $_POST['replace'];
				}
			}
		}

		// If the upload is going to a specific file type, specify it
		$filetypeid = NULL;
		if (isset($_POST['filetype']))
		{
			$filetypeid = $_POST['filetype'];
		}

		$config['upload_path'] = 'files_managed/'.$attachedto.'/'.str_replace('/', '+', $attachedname); // Upload to managed area first, sort it out later, also replace / with + for flat file structure
		$config['remove_spaces'] = true;
		$config['encrypt_name'] = false;
		$this->load->library('upload', $config);

		if (!file_exists($config['upload_path']))
		{
			mkdir($config['upload_path']);
		}

		// Set the return path and other specific options depending on where the upload is to
		if ($attachedto == "site")
		{
			$returnurl = "manage_content/attachments";
		}
		else if ($attachedto == "email")
		{
			$returnurl = "manage_users/email";
		}
		else if ($attachedto == "page")
		{
			$returnurl = $attachedname.'/attachments';
		}
		else if ($attachedto == "user")
		{
			$returnurl = "profile/".$attachedname.'/attachments';
		}
		else if ($attachedto == "post")
		{
			$returnurl = $attachedname.'/attachments';
		}
		else
		{
			$returnurl = $attachedto.'/'.$attachedid.'/attachments';
		}

		$uploadtype = "";
		$result = "";
		if (isset($_FILES["swfuploadfile"])) // Upload is a swfupload, and it's swfupload's first pass, so physically upload file
		{
			$uploadtype = "swfuploadupload";
			$result = $this->upload->do_upload('swfuploadfile');
		}
		else if (isset($_POST["hidFileID"]) && $_POST["hidFileID"] != "" ) // We are on swfupload's second pass, the upload has already been done
		{
			$uploadtype = "swfuploadparse";
			$result = TRUE;
		}
		else // Upload is a non swfupload, physically upload file
		{
			$uploadtype = "regular";
			$result = $this->upload->do_upload('Filedata');
		}

		if(!$result && $uploadtype == "regular") // A non swfupload fail
		{
			$this->_message(
				'Manage uploads',
				$this->upload->display_errors('', '').' Click <a href="'.base_url().$returnurl.'">here</a> to return to editing.',
				'Error'
			);
		}
		else if (!$result)
		{
			// User won't see this area since something did fail, but this was on swfupload's first pass, so swfupload will report the error
		}
		else // Upload was a success
		{
			if ($uploadtype == "swfuploadparse") // It's swfupload's second pass after the physical upload let's grab the filename from swfupload
			{
				$filename = $_POST["hidFileID"];
			}
			else // Otherwise, it's a regular upload or swfupload's first pass, and we will get the file name through CI
			{
				$filedata = $this->upload->data();
				$filename = $filedata['file_name'];
			}

			if ($uploadtype != "swfuploadupload") // We aren't on swfupload's first pass, so we are either on its second or a regular upload, so we will parse the file into the database and return to the user
			{
				// However, we will only parse the file if the user has an established session
				// We couldn't check earlier in this code for the session because when swfupload does it's first pass, it's through the flash/ajax applet, and that doesn't handle sessions and can't grab CI session
				/* Which means, now that we are back to the CI form submission, if there's no valid session here and they don't have attachments privelege,
				   we will delete the file that was attempted to be uploaded and return an error */

				$allowed_to_upload = FALSE;
				$object = instantiate_library('user', $this->session->userdata('userid'));

				if (isset($object->user['userid']))
				{
					if ($attachedto == "page" && $this->acl->allow('page', 'attachments', FALSE, 'page', $attachedid))
					{
						$allowed_to_upload = TRUE;
					}
					else if ($attachedto == "post" && $this->acl->allow('post', 'attachments', FALSE, 'post', $attachedid))
					{
						// Grab post's parent so we can grab a template
						$attachedto_parent = instantiate_library('page', $attachedto_object->post['pageid']);

						if (isset($attachedto_parent->page['pageid']))
						{
							$attachedto_parent->initialize_page_tree();
							if (isset($attachedto_parent->page['post_templateid']))
							{
								$templateid = $attachedto_parent->page['post_templateid'];
							}
							else
							{
								$this->load->helper('template');
								$templateid = template_to_use('template', 'post', $attachedto_parent->page_tree);
							}
							$template = instantiate_library('template', $templateid);

							// Check if there's a template
							if (isset($template->template['templateid']))
							{
								// Grab template quotas
								$template->initialize_quotas();

								$quota_required = TRUE;
								$no_quota_in_use = TRUE;

								// Go through quotas determing which one the user is set to, if any
								foreach ($template->quotas as $filegroup)
								{
									$filegroup_editable = $this->acl->access($filegroup['protection'], $attachedto_parent, $attachedid);

									if ($filegroup_editable)
									{
										$no_quota_in_use = FALSE;
									}
									else if (!$filegroup_editable && $filegroup['other_roles_allowed'])
									{
										$quota_required = FALSE;
									}

									// If we are uploading to a specific file type, and that file type is permitted in this template, we must check if we reached the cap
									if ((!$quota_required || $filegroup_editable) && $filetypeid != NULL && isset($filegroup['filetypes'][$filetypeid]))
									{
										$attached = $this->files_model->get_attached('post', $attachedid, $filetypeid);
										if ($filegroup['filetypes'][$filetypeid]['cap'] != '*' && $attached->num_rows() >= $filegroup['filetypes'][$filetypeid]['cap'])
										{
											$failure_message = 'You cannot upload a '.strtolower($filegroup['filetypes'][$filetypeid]['name']).' file, the maximum allowed is '.$filegroup['filetypes'][$filetypeid]['cap'].'. '.
												'<a href="'.base_url().$returnurl.'">Please remove a file of this type first</a>.';
										}
										else
										{
											$allowed_to_upload = TRUE;
										}
									}
								}

								// If the user isn't assigned to a quota, or is assigned to one but not required to use it
								// And the file isn't a part of a quota but a regular file attachment, allow the upload
								if (!isset($failure_message) && $filetypeid == NULL && (!$quota_required || $no_quota_in_use))
								{
									$allowed_to_upload = TRUE;
								}
							}
							else // No template, which means they can upload
							{
								$allowed_to_upload = TRUE;
							}
						}
						else // No template, which means they can upload
						{
							$allowed_to_upload = TRUE;
						}
					}
					else if ($attachedto == "user" && $this->acl->allow('profile', 'edit', FALSE, 'user', $attachedid))
					{
						$allowed_to_upload = TRUE;
					}
					else if ($attachedto == "email" && $this->acl->allow('site', 'manage_users', FALSE))
					{
						$allowed_to_upload = TRUE;
					}
					else if ($this->acl->allow('site', 'manage_content', FALSE))
					{
						$allowed_to_upload = TRUE;
					}
				}

				if ($allowed_to_upload)
				{
					if ($attachedto != "email") // But only parse file into the database when it's not an email attachment, which are temporary uploads
					{
						$this->_parsefile($config['upload_path'], $filename, $attachedto, $attachedid, $replacefileid, $filetypeid);
					}
					else // Add temporary upload information to mailing list session
					{
						// Preserve mailing user list session information
						$this->session->keep_flashdata('maillist');
						$this->session->keep_flashdata('sort');
						$this->session->keep_flashdata('page');

						$mailattachments = $this->session->flashdata('mailattachments');
						array_push($mailattachments, $filename);
						$this->session->set_flashdata('mailattachments', $mailattachments);
					}
					redirect($returnurl);
				}
				else
				{
					if (file_exists($config['upload_path'].'/'.$filename))
					{
						unlink($config['upload_path'].'/'.$filename);
					}

					if (!isset($failure_message))
					{
						$this->_access_denied();
					}
					else
					{
						$this->_message('Upload error', $failure_message);
					}
				}
			}
			else // We are on swfupload's first pass, echo the filename so that it'll do some AJAX and stick that filename into the attachment form in preparation for it's second pass
			{
				echo $filename;
			}
		}
	}

	function _parsefile($filepath, $filename, $attachedto, $attachedid, $replacefileid, $filetypeid)
	{
		// If the file isn't replacing an existing one, we need to create it in the database
		if ($replacefileid == NULL)
		{
			// Determine file is an image or not
			$info = @getimagesize($filepath.'/'.$filename);
			if(!$info)
			{
				$isimage = "0";
			}
			else
			{
				$isimage = "1";
			}

			// Break down filename into it's name and extension
			$filepieces = explode(".", $filename);
			$extension = $filepieces[count($filepieces)-1];
			$filename = substr($filename, 0, strrpos($filename, "."));

			// Create file
			$this->load->library('file_lib','','new_file');
			$this->new_file->new_file['filename'] = $filename;
			$this->new_file->new_file['extension'] = $extension;
			$this->new_file->new_file['isimage'] = $isimage;
			$this->new_file->new_file['attachedto'] = $attachedto;
			$this->new_file->new_file['attachedid'] = $attachedid;
			$this->new_file->new_file['filetypeid'] = $filetypeid;

			// Although CI ensures a unique upload name, our upload was to 'files_managed', and if there's a file with the same name sitting in the 'files' folder we will have problems
			// Let's have the file library suggest a new file name that lacks spaces, invalid symbols and caps doesn't collide with an existing file
			$this->new_file->suggest();

			if ($filename.'.'.$extension != $this->new_file->new_file['filename'].'.'.$this->new_file->new_file['extension'])
			{
				copy($filepath.'/'.$filename.'.'.$extension, $filepath.'/'.$this->new_file->new_file['filename'].'.'.$this->new_file->new_file['extension']);
				unlink($filepath.'/'.$filename.'.'.$extension);
			}

			$this->new_file->save();
		}
		else
		{
			$object = instantiate_library('file', $replacefileid);
			$object->overwrite($filepath.'/'.$filename);
		}
	}
}

?>