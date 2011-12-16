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
class Account extends MY_Controller {

	function Account()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	function _remap()
	{
		// Doesn't use ACLs, if you are signed on, you can edit your own account
		if ($this->session->userdata('signedon') || redirect(base_url().'signon'.$this->uri->uri_string()))
		{
			$this->user = instantiate_library('user', $this->session->userdata('userid'));
			$data['blogs'] = $this->user->get_rss();
			$blocked = $this->user->get_blocked_users();
			
			// Get all roles
			$this->load->model('acls_model');
			$roles = $this->acls_model->get_roles_all();
			$rolestable = array();
			foreach ($roles->result_array() as $role)
			{
				$rolestable[$role['roleid']] = $role['role']; 
			}
			
			// Get any special site priveleges for the user
			$data['priveleges'] = array();
			$site_role = $this->acls_model->get($this->user->user['userid'], 'site');
			if ($site_role != NULL && $this->acls_model->get_role_name($site_role) != "member")
			{
				array_push($data['priveleges'], array('on' => 'site', 'role' => $rolestable[$site_role])); 
			}
			$page_priveleges = $this->acls_model->get_all($this->user->user['userid'], 'page');
			foreach ($page_priveleges->result_array() as $page_privelege)
			{
				$object = instantiate_library('page', $page_privelege['attachedid']);
				if (isset($object->page['pageid']))
				{
					array_push($data['priveleges'], array('on' => 'page', 'role' => $rolestable[$page_privelege['roleid']], 'page' => $object->page)); 
				}
			}
			$post_priveleges = $this->acls_model->get_all($this->user->user['userid'], 'post');
			foreach ($post_priveleges->result_array() as $post_privelege)
			{
				$object = instantiate_library('post', $post_privelege['attachedid']);
				if (isset($object->post['postid']))
				{
					array_push($data['priveleges'], array('on' => 'post', 'role' => $rolestable[$post_privelege['roleid']], 'post' => $object->post)); 
				}
			}
		
			if ($this->uri->segment(2) == "changepassword")
			{
				$this->focus = "changepassword";
				$this->form_validation->set_rules('oldpassword', 'old password', 'xss_clean|trim|required|callback_password_check|md5');
				$this->form_validation->set_rules('newpassword', 'new password', 'xss_clean|trim|required|min_length[6]|max_length[15]|matches[confirmpassword]|md5');
				$this->form_validation->set_rules('confirmpassword', 'confirm password', 'xss_clean|trim|required|min_length[6]|max_length[15]|md5');
			}
			else if ($this->uri->segment(2) == "updateemail")
			{
				$this->focus = "updateemail";
				$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|required||max_length[50]|valid_email|callback_email_check');
			}
			else if ($this->uri->segment(2) == "messagesettings")
			{
				$this->form_validation->set_rules('mailinglist', 'on mailing list', 'xss_clean|strip_tags');
				$this->form_validation->set_rules('getmessages', 'allow messages', 'xss_clean|strip_tags');
				$this->focus = "messagesettings";
				for ($i=0; $i<=$data['blogs']->num_rows(); $i++)
				{
				
					$this->form_validation->set_rules('rssfeed'.($i+1), 'blog rss feed url', 'xss_clean|strip_tags|trim|prep_url|max_length[150]');
				}
				
				for ($i=0; $i<=$blocked->num_rows(); $i++)
				{
					$this->form_validation->set_rules('block'.($i+1), 'display name of the blocked user', 'xss_clean|strip_tags|trim|min_length[3]|max_length[30]|callback_displayname_exists_check');
				}
			}

			// Retrieve blocked users
			$data['blocked'] = array();
			for ($i=0; $i<=$blocked->num_rows(); $i++)
			{
				if ($i!=$blocked->num_rows())
				{
					$block = $blocked->row_array($i);
					$this->blockuser = instantiate_library('user', $block['blockedid']);
					$block['displayname'] = $this->blockuser->user['displayname'];
					array_push($data['blocked'], $block);
				}
			}

			if ($this->form_validation->run())
			{
				if ($this->uri->segment(2) == "changepassword")
				{
					$this->user->new_user['password'] = set_value('newpassword');
					$this->user->save();
					$this->message = 'You have successfully changed your password.  The change will take effect when you next log on. <a href="'.base_url().'account/">Return to your account</a>.';
				}
				else if ($this->uri->segment(2) == "updateemail")
				{
					$this->user->new_user['email'] = set_value('email');
					$this->user->save();
					$this->load->model('notifications_model');
					$this->notifications_model->send(
						$this->user->user['email'],
						$this->config->item('dmcb_friendly_server').' account',
						"You have changed your email address at ".$this->config->item('dmcb_friendly_server').".\n\nBefore you can log in you must activate your address by going to the following URL, ".base_url()."activate/".$this->user->user['userid']."/".$this->user->user['code']
					);
					$this->message = 'You have successfully updated your email address.  This new address must be activated, an activation email is being sent. <a href="'.base_url().'account/">Return to your account</a>.';
				}
				else if ($this->uri->segment(2) == "blogsettings")
				{
					$this->user->remove_rss();
					for ($i=0; $i<=$data['blogs']->num_rows(); $i++)
					{
						$field = 'rssfeed'.($i+1);
						$this->user->add_rss(set_value($field));

					}
					$this->message = 'You have successfully updated your blog settings. <a href="'.base_url().'account/">Return to your account</a>.';
				}
				else if ($this->uri->segment(2) == "messagesettings")
				{
					$this->user->remove_blocked_users();
					$added = array();
					for ($i=0; $i<=$blocked->num_rows(); $i++)
					{
						$field = 'block'.($i+1);
						if (set_value($field) != "")
						{
							$this->user->add_blocked_user(set_value($field));
							$added[strtolower(set_value($field))] = TRUE;
						}
					}
					$this->user->new_user['mailinglist'] = set_value('mailinglist');
					$this->user->new_user['getmessages'] = set_value('getmessages');
					$this->user->save();
					redirect('account/messagesettings');
				}
				$this->_message("Account update", $this->message, "Success");
			}
			else {
				if ($this->uri->segment(2) == "removefacebook")
				{
					$this->user->new_user['facebook_uid'] = NULL;
					$this->user->save();
				}
				$data['user'] = $this->user->user;
				$this->_initialize_page('account', 'Account', $data);
			}
		}
	}

	function email_check($str)
	{
		$checkuser = instantiate_library('user', $str, 'email');
		if (isset($checkuser->user['userid']))
		{
			$this->form_validation->set_message('email_check', "The email address $str is in use, please try a new email address.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function displayname_exists_check($str)
	{	
		$checkuser = instantiate_library('user', $str, 'displayname');
		if ($str != "" && !isset($checkuser->user['userid']))
		{
			$this->form_validation->set_message('displayname_exists_check', "The display name $str doesn't exist, please try a new display name.");	
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function password_check($str)
	{
		if (isset($this->user->user['userid']) && $this->user->user['password'] == md5(set_value('oldpassword')))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('password_check', "Incorrect password.");
			return FALSE;
		}
	}
}