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
class Profile extends MY_Controller {

	function Profile()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		$this->user = instantiate_library('user', $this->uri->segment(2), 'urlname');
		if ($this->acl->allow('profile', 'view', TRUE) || $this->_access_denied())
		{
			if (!$this->session->userdata('signedon') && (($this->uri->segment(2) == "-" && $this->uri->segment(3) != NULL) || $this->uri->segment(2) == NULL))
			{
				redirect('signon'.uri_string());
			}
			else if ($this->uri->segment(2) == "-" && $this->uri->segment(3) != NULL)
			{
				redirect('profile/'.$this->session->userdata('urlname').'/'.$this->uri->segment(3));
			}
			else if ($this->uri->segment(2) == NULL && $this->session->userdata('signedon'))
			{
				redirect('profile/'.$this->session->userdata('urlname'));
			}
			else if (!isset($this->user->user['userid']))
			{
				// If the link fails but there is a placeholder for it, point them to the new URL
				$this->load->model('placeholders_model');
				$placeholder = $this->placeholders_model->get('user', $this->uri->segment(2));
				if ($placeholder != NULL)
				{
					$this->_redirect(base_url().'user/'.$placeholder['newname']);
				}
				else
				{
					$this->_page_not_found();
				}
			}
			else
			{
				if ($this->user->user['enabledprofile'] || $this->_access_denied())
				{
					// Determine if the user is blocking viewer
					$this->blocked = FALSE;
					$blockedlist = $this->user->get_blocked_users();
					foreach ($blockedlist->result_array() as $blockeduser)
					{
						if ($blockeduser['blockedid'] == $this->session->userdata('userid'))
						{
							$this->blocked = TRUE;
						}
					}
				
					// Get held comments
					$this->load->model('comments_model');
					$this->heldcomments = $this->comments_model->get_user_heldback($this->user->user['userid']);

					$method = $this->uri->segment(3);
					if ($method == "addpost" || $method == "attachments" || $method == "heldcomments" || $method == "editname" || $method == "editprofile" || $method == "editsettings" || $method == "message")
					{
						$this->focus = $method;
						$this->$method();
					}
					else
					{
						$this->index();
					}
				}
			}
		}
	}
	
	function index()
	{
		// Profile data
		$this->load->helper('picture');
		$data['user'] = $this->user->user;
			
		// Grab posts
		if ($this->acl->enabled('profile', 'addpost'))
		{
			$this->page_urlname = NULL;
			$this->load->library('block_lib', NULL, 'posts_block');
			$this->posts_block->block['function'] = "posts";
			$this->posts_block->block['values'] = array('detail' => 'small_listing', 'user' => $this->user->user['urlname'], 'limit' => '10');
			$this->posts_block->block['pagination'] = 1;
			$this->posts_block->block['rss'] = 1;
			$this->posts_block->block['feedback'] = 1;
			$data['posts'] = $this->posts_block->output();
		}
		
		// Grab tweets
		if ($this->acl->enabled('profile', 'twitter') && $this->user->user['twitter'] != "" && $this->user->user['twitter'] != NULL)
		{
			$this->load->library('block_lib', NULL, 'tweets_block');
			$this->tweets_block->block['function'] = "twitter";
			$this->tweets_block->block['values'] = array('query' => '&from='.$this->user->user['twitter'], 'limit' => '3');
			$this->tweets_block->block['pagination'] = 0;
			$this->tweets_block->block['rss'] = 0;
			$this->posts_block->block['feedback'] = 0;
			$data['tweets'] = $this->tweets_block->output();
		}
		
		// Enable messaging
		if ($this->acl->allow('profile', 'message', FALSE, 'user', $this->user->user['userid']) && $this->user->user['getmessages'] == 1 && !$this->blocked)
		{
			// Ensure you can't message yourself
			if (!$this->session->userdata('signedon') || ($this->session->userdata('signedon') && $this->session->userdata('userid') != $this->user->user['userid']))
			{
				$data['messages'] = $this->load->view('form_profile_message', array('user' => $this->user->user), TRUE);
			}
		}
		else if ($this->acl->enabled('profile', 'message', 'member') && $this->user->user['getmessages'] == 1 && !$this->blocked)
		{
			$data['messages'] = $this->load->view('form_profile_messageteaser', array('user' => $this->user->user), TRUE);
		}
		
		// User profile editing
		if ($this->acl->allow('profile', 'edit', FALSE, 'user', $this->user->user['userid']))
		{
			$data['packages_upload'] = $this->load->view('packages_upload', 
				array(
					'upload_url' => 'user/'.$this->user->user['urlname'], 
					'upload_size' => $this->config->item('dmcb_profile_upload_size'),
					'upload_types' => $this->config->item('dmcb_profile_upload_types'), 
					'upload_description' => $this->config->item('dmcb_profile_upload_description')
				), TRUE);
			$data['edit_name'] = $this->load->view('form_profile_editname', array('user' => $this->user->user), TRUE);
			$data['edit_profile'] = $this->load->view('form_profile_editprofile', array('user' => $this->user->user), TRUE);
			
			// Grab profile picture attachments
			$this->load->model('files_model');
			$files = array();
			$fileids = $this->files_model->get_attached("user",$this->user->user['userid']);
			foreach ($fileids->result_array() as $fileid)
			{
				$object = instantiate_library('file', $fileid['fileid']);
				array_push($files, $object->file);
			}
			$data['attachments'] = $this->load->view('form_profile_attachments', array('upload_url' => 'user/'.$this->user->user['urlname'], 'files' => $files, 'user' => $this->user->user), TRUE);
			
			// Load up held back comments
			if ($this->heldcomments->num_rows() > 0 && $this->acl->enabled('post', 'addcomment'))
			{
				$data['edit_heldbackcomments'] = $this->load->view('form_profile_heldcomments', array('heldcomments' => $this->heldcomments), TRUE);
			}
		}
		
		// User twitter settings
		if ($this->acl->allow('profile', 'twitter', FALSE, 'user', $this->user->user['userid']))
		{
			$data['edit_settings'] = $this->load->view('form_profile_editsettings', array('user' => $this->user->user), TRUE);
		}
		
		// User post editing
		if ($this->acl->allow('profile', 'addpost', FALSE, 'user', $this->user->user['userid']))
		{
			$data['add_post'] = $this->load->view('form_profile_addpost', NULL, TRUE);
			
			// Load up drafts
			$this->load->model('posts_model');
			$drafts = array();
			$draftids = $this->posts_model->get_user_drafts($this->user->user['userid']);
			foreach ($draftids->result_array() as $draftid)
			{
				$object = instantiate_library('post', $draftid['postid']);
				array_push($drafts, $object->post);
			}
			if (sizeof($drafts) > 0)
			{
				$data['edit_drafts'] = $this->load->view('form_profile_editdrafts', array('drafts' => $drafts), TRUE);
			}
			
			// Load up held back posts
			$heldposts = array();
			$heldpostids = $this->posts_model->get_user_heldback($this->user->user['userid']);
			foreach ($heldpostids->result_array() as $heldpostid)
			{
				$object = instantiate_library('post', $heldpostid['postid']);
				array_push($heldposts, $object->post);
			}
			if (sizeof($heldposts) > 0)
			{
				$data['edit_heldbackposts'] = $this->load->view('form_profile_heldposts', array('heldposts' => $heldposts), TRUE);
			}
		}

		$this->_initialize_page('profile', $this->user->user['displayname'], $data);
	}
	
	function addpost()
	{
		if ($this->acl->allow('profile', 'addpost', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('posttitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]');
			$this->form_validation->set_rules('posturlname', 'url name', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[35]|callback_posturlname_check');
			
			if ($this->form_validation->run())
			{
				$this->load->library('post_lib','','new_post');
				$this->new_post->new_post['userid'] = $this->user->user['userid'];
				$this->new_post->new_post['title'] = set_value('posttitle');
				$this->new_post->new_post['urlname'] = set_value('posturlname');
				$this->new_post->new_post['urlname'] = date("Ymd", time()).'/'.$this->new_post->new_post['urlname'];
				$this->new_post->save();
				$object = instantiate_library('post', $this->new_post->post['postid']);
				$object->new_post['featured'] = $this->user->user['statusid'];
				$object->save();
				redirect($this->new_post->post['urlname'].'/editpost');
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function posturlname_check($str)
	{
		if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('posturlname_check', "The url name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|\-+$|\_+$/i', $str))
		{
			$this->form_validation->set_message('posturlname_check', "The url name cannot start or end with dashes or underscores.");
			return FALSE;
		}
		else
		{
			// Profile posts of format YYYYMMDD/POSTNAME
			$str = date("Ymd").'/'.$str;
			
			// Check for name collisions and return suggested new name
			$this->load->library('post_lib','','test_post');
			$this->test_post->post['postid'] = '0';
			$suggestion = $this->test_post->suggest($str);
			if ($suggestion == $str)
			{
				return TRUE;
			}
			else
			{
				// Remove everything before slash for suggestion, so that suggested URL name is just the part the user has control over
				if (strrpos($suggestion, '/') != 0)
				{
					$suggestion = substr($suggestion, strrpos($suggestion, '/')+1);
				}
				$this->form_validation->set_message('posturlname_check', "The url name is in use.  We suggest $suggestion.");
				return FALSE;
			}
		}
	}
	
	function attachments()
	{
		if ($this->acl->allow('profile', 'edit', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			$this->attachment = instantiate_library('file', $this->uri->segment(5));
			if ($this->uri->segment(4) != "" && ($this->attachment->file['attachedto'] != "user" || $this->attachment->file['attachedid'] != $this->user->user['userid']))
			{
				$this->_access_denied();
			}
			else if ($this->uri->segment(4) == "delete")
			{
				$this->attachment->delete();
				redirect('profile/'.$this->user->user['urlname'].'/attachments');
			}
			else if ($this->uri->segment(4) == "setimage")
			{
				$this->user->new_user['profilepicture'] = $this->uri->segment(5);
				$this->user->save();
				redirect('profile/'.$this->user->user['urlname'].'/attachments');
			}
			else if ($this->uri->segment(4) == "removeimage")
			{
				$this->user->new_user['profilepicture'] = NULL;
				$this->user->save();
				redirect('profile/'.$this->user->user['urlname'].'/attachments');
			}
			else if ($this->uri->segment(4) == "rename")
			{
				$this->form_validation->set_rules('filename', 'file name', 'xss_clean|strip_tags|trim|required|max_length[100]|callback_filename_check');
				
				if ($this->form_validation->run())
				{
					$this->attachment->new_file['filename'] = set_value('filename');
					$this->attachment->save();
					redirect('profile/'.$this->user->user['urlname'].'/attachments');
				}
				else
				{
					$this->_initialize_page('file_rename',"Rename file",array('attachment' => $this->attachment->file));
				}
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function filename_check($str)
	{
		$object = instantiate_library('file', array($str, $this->attachment->file['extension'], $this->attachment->file['attachedto'], $this->attachment->file['attachedid']), 'details');
		if (isset($object->file['fileid']) && $object->file['fileid'] != $this->attachment->file['fileid'])
		{
			$this->form_validation->set_message('filename_check', "The file name $str is in use, please try a new file name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('filename_check', "The file name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|\_+$/i', $str))
		{
			$this->form_validation->set_message('filename_check', "The file name cannot start or end with underscores.");
			return FALSE;
		}
		else
			return TRUE;
	}
	
	function editname()
	{
		if ($this->acl->allow('profile', 'edit', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('displayname', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_check');
			
			if ($this->form_validation->run())
			{
				$this->user->new_user['displayname'] = set_value('displayname');
				$this->user->save();
				$this->session->set_userdata('displayname',$this->user->user['displayname']);
				$this->session->set_userdata('urlname',$this->user->user['urlname']);
				redirect('profile/'.$this->user->user['urlname']);
			}
			else
			{
				$this->index();
			}
		}	
	}

	function displayname_check($str)
	{
		$checkuser = instantiate_library('user', $str, 'displayname');
		if (isset($checkuser->user['userid']))
		{
			$this->form_validation->set_message('displayname_check', "The display name $str is in use, please try a new display name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9- ]+$/i', $str))
		{
			$this->form_validation->set_message('displayname_check', "The display name must be made of only letters, numbers, dashes, and spaces.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function editprofile()
	{
		if ($this->acl->allow('profile', 'edit', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('profile', 'profile', 'xss_clean|strip_tags|trim|max_length[15000]');
			
			if ($this->form_validation->run())
			{
				$profile = str_replace("\n","<br/>",set_value('profile'));
				$this->user->new_user['profile'] = preg_replace("/<br\/>(<br\/>)+/","<br/><br/>",$profile);
				$this->user->save();
				redirect('profile/'.$this->user->user['urlname']);
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function editsettings()
	{
		if ($this->acl->allow('profile', 'twitter', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('twitter', 'twitter account name', 'xss_clean|strip_tags|trim|alpha_dash|max_length[30]');
			
			if ($this->form_validation->run())
			{
				$this->user->new_user['twitter'] = set_value('twitter');
				$this->user->save();
				redirect('profile/'.$this->user->user['urlname']);
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function heldcomments()
	{
		if ($this->acl->allow('profile', 'edit', TRUE, 'user', $this->user->user['userid']) || $this->_access_denied())
		{
			if (!isset($_POST['buttonchoice']))
			{
				$this->index();
			}
			else if (strpos($_POST['buttonchoice'], "edit") !== FALSE)
			{
				$field = substr($_POST['buttonchoice'], 0, strlen($_POST['buttonchoice'])-4);
				$this->form_validation->set_rules($field, 'comment', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[15000]');
				$commentid = substr($field, 7, strlen($field));
				$comment = $this->comments_model->get($commentid);
				
				if ($this->user->user['userid'] == $comment['userid'])
				{
					if ($this->form_validation->run())
					{
						$comment = str_replace("\n","<br/>",set_value($field));
						$this->comments_model->update($commentid, $comment);
						
						redirect('profile/'.$this->user->user['urlname'].'/heldcomments');
					}
					else
					{
						$this->index();
					}
				}
				else
				{
					$this->_access_denied();
				}
			}
			else if (strpos($_POST['buttonchoice'], "delete") !== FALSE)
			{
				$field = substr($_POST['buttonchoice'], 0, strlen($_POST['buttonchoice'])-6);
				$commentid = substr($field, 7, strlen($field));
				$comment = $this->comments_model->get($commentid);
				
				if ($this->user->user['userid'] == $comment['userid'])
				{
					$this->comments_model->delete($commentid, $comment);
					
					redirect('profile/'.$this->user->user['urlname'].'/heldcomments');
				}
				else
				{
					$this->_access_denied();
				}
			}
		}
	}
	
	function message()
	{
		if ($this->acl->allow('profile', 'message', TRUE, 'user', $this->user->user['userid']) && $this->user->user['getmessages'] == 1 && !$this->blocked)
		{
			$this->form_validation->set_rules('content', 'message', 'xss_clean|strip_tags|trim|required|min_length[10]|max_length[1000]');
			
			if ($this->form_validation->run())
			{
				$subject = $this->session->userdata('displayname')." has sent you a message via ".$this->config->item('dmcb_title');
				$message = 
					$this->session->userdata('displayname')." has sent you a message:\n\n".
					html_entity_decode(set_value('content'), ENT_QUOTES)."\n\n\n\n".
					"To view ".$this->session->userdata('displayname')."'s profile and send a message back, follow this link:\n".
					base_url()."profile/".$this->session->userdata('urlname')."/message\n\n".
					"If you no longer wish to receive messages, you can change your mail settings at the link below:\n".
					base_url()."account/messagesettings\n\n";					
			
				$this->load->model('notifications_model');
				$this->notifications_model->send($this->user->user['email'], $subject, $message);
				$this->_message(
					'Message sent', 
					'You have sent a message to '.$this->user->user['displayname'].'. Click <a href="'.base_url().'profile/'.$this->user->user['urlname'].'">here</a> to return to their profile.',
					'Success'
				);
			}
			else
			{
				$this->index();
			}
		}
		else if ($this->session->userdata('signedon') && $this->session->userdata('userid') == $this->user->user['userid'])
		{
			$this->_message(
				'Send message', 
				'You want to send a message to yourself?  You so crazy!',
				'Error'
			);
		}
		else if ($this->session->userdata('signedon'))
		{
			$this->_message(
				'Send message', 
				$this->user->user['displayname'].' does not want to receive messages, sorry.',
				'Error'
			);
		}
		else
		{
			redirect('signon'.uri_string());
		}
	}
}
?>