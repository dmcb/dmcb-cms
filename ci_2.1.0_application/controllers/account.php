<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Account extends MY_Controller {

	function Account()
	{
		parent::__construct();

		$this->load->model('notifications_model');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		// Doesn't use ACLs, if you are signed on, you can edit your own account
		if ($this->session->userdata('signedon') || redirect(base_url().'signon'.$this->uri->uri_string()))
		{
			// Get your own account
			$yourself = instantiate_library('user', $this->session->userdata('userid'));

			// Check if we have picked a specific account to go to and if not, go to your own account
			if ($this->uri->segment(2) == "changepassword" ||
				$this->uri->segment(2) == "messagesettings" ||
				$this->uri->segment(2) == "updateemail")
			{
				redirect(base_url().'account/'.$yourself->user['urlname'].'/'.$this->uri->segment(2));
			}
			else if ($this->uri->segment(2) == NULL)
			{
				redirect(base_url().'account/'.$yourself->user['urlname']);
			}

			// Get user that we are editing the account of
			$this->user = instantiate_library('user', $this->uri->segment(2), 'urlname');

			// You can only edit the account if it's your own, or you have special privileges
			$this->data['self_editing'] = TRUE;
			$this->data['person_edited'] = "your";
			if ($yourself->user['userid'] != $this->user->user['userid'])
			{
				$this->data['person_edited'] = $this->user->user['displayname']."'s";
				$this->data['self_editing'] = FALSE;
			}

			if ($this->acl->allow('site', 'set_password') || $this->data['self_editing'] || $this->_access_denied())
			{
				// Get blogs and blocked users
				$this->blogs = $this->user->get_rss();
				$this->blocked = $this->user->get_blocked_users();

				$method = $this->uri->segment(3);
				if ($method == "changepassword" || $method == "messagesettings" || $method == "updateemail")
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

	function index()
	{
		// Retrieve blocked users
		$this->data['blocked'] = array();
		for ($i=0; $i<=$this->blocked->num_rows(); $i++)
		{
			if ($i!=$this->blocked->num_rows())
			{
				$block = $this->blocked->row_array($i);
				$this->blockuser = instantiate_library('user', $block['blockedid']);
				$block['displayname'] = $this->blockuser->user['displayname'];
				array_push($this->data['blocked'], $block);
			}
		}

		// Set email list to user
		$this->session->set_flashdata('maillist', array($this->user->user['userid']));

		// Get moderation activity
		$this->data['moderations'] = $this->notifications_model->get($this->user->user['userid']);

		// Get subscription
		if ($this->acl->enabled('site', 'subscribe'))
		{
			$this->load->model('subscriptions_model');
			$this->data['subscription'] = $this->subscriptions_model->get($this->user->user['userid']);
		}

		// Get all roles
		$this->load->model('acls_model');
		$roles = $this->acls_model->get_roles_all();
		$rolestable = array();
		foreach ($roles->result_array() as $role)
		{
			$rolestable[$role['roleid']] = $role['role'];
		}

		// Get any special site privileges for the user
		$this->data['privileges'] = array();
		$site_role = $this->acls_model->get($this->user->user['userid'], 'site');
		if ($site_role != NULL && $this->acls_model->get_role_name($site_role) != "member")
		{
			array_push($this->data['privileges'], array('on' => 'site', 'role' => $rolestable[$site_role]));
		}
		$page_privileges = $this->acls_model->get_all($this->user->user['userid'], 'page');
		foreach ($page_privileges->result_array() as $page_privilege)
		{
			$object = instantiate_library('page', $page_privilege['attachedid']);
			if (isset($object->page['pageid']))
			{
				array_push($this->data['privileges'], array('on' => 'page', 'role' => $rolestable[$page_privilege['roleid']], 'page' => $object->page));
			}
		}
		$post_privileges = $this->acls_model->get_all($this->user->user['userid'], 'post');
		foreach ($post_privileges->result_array() as $post_privilege)
		{
			$object = instantiate_library('post', $post_privilege['attachedid']);
			if (isset($object->post['postid']))
			{
				array_push($this->data['privileges'], array('on' => 'post', 'role' => $rolestable[$post_privilege['roleid']], 'post' => $object->post));
			}
		}

		if ($this->uri->segment(3) == "removefacebook")
		{
			$this->user->new_user['facebook_uid'] = NULL;
			$this->user->save();
		}

		// Load page
		$this->data['user'] = $this->user->user;
		$this->data['account_report'] = $this->load->view('account_report', $this->data, TRUE);
		$this->data['change_password'] = $this->load->view('form_account_changepassword', $this->data, TRUE);
		$this->data['update_email'] = $this->load->view('form_account_updateemail', $this->data, TRUE);
		$this->data['message_settings'] = $this->load->view('form_account_messagesettings', $this->data, TRUE);

		if ($this->config->item('dmcb_signon_facebook') == "true")
		{
			$this->data['facebook'] = $this->load->view('form_account_facebook', $this->data, TRUE);
		}

		$this->_initialize_page('account', 'Account', $this->data);
	}

	function blogsettings() // Not used any more
	{
		for ($i=0; $i<=$this->blogs->num_rows(); $i++)
		{
			$this->form_validation->set_rules('rssfeed'.($i+1), 'blog rss feed url', 'xss_clean|strip_tags|trim|prep_url|max_length[150]');
		}

		if ($this->form_validation->run())
		{
			$this->user->remove_rss();
			for ($i=0; $i<=$this->blogs->num_rows(); $i++)
			{
				$field = 'rssfeed'.($i+1);
				$this->user->add_rss(set_value($field));

			}
			$message = 'You have successfully updated '.$this->data['person_edited'].' blog settings. <a href="'.base_url().'account/'.$this->user->user['urlname'].'">Return to '.$this->data['person_edited'].' account</a>.';
			$this->_message("Account update", $message, "Success");
		}
		else
		{
			$this->index();
		}
	}

	function changepassword()
	{
		$this->form_validation->set_rules('oldpassword', 'old password', 'xss_clean|trim|required|callback_password_check|md5');
		$this->form_validation->set_rules('newpassword', 'new password', 'xss_clean|trim|required|min_length[6]|max_length[15]|matches[confirmpassword]|md5');
		$this->form_validation->set_rules('confirmpassword', 'confirm password', 'xss_clean|trim|required|min_length[6]|max_length[15]|md5');

		if ($this->form_validation->run())
		{
			$this->user->new_user['password'] = set_value('newpassword');
			$this->user->save();
			$message = 'You have successfully changed '.$this->data['person_edited'].' password. The change will take effect when you next log on. <a href="'.base_url().'account/'.$this->user->user['urlname'].'">Return to '.$this->data['person_edited'].' account</a>.';
			$this->_message("Account update", $message, "Success");
		}
		else
		{
			$this->index();
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

	function messagesettings()
	{
		$this->form_validation->set_rules('mailinglist', 'on mailing list', 'xss_clean|strip_tags');
		$this->form_validation->set_rules('getmessages', 'allow messages', 'xss_clean|strip_tags');
		for ($i=0; $i<=$this->blocked->num_rows(); $i++)
		{
			$this->form_validation->set_rules('block'.($i+1), 'display name of the blocked user', 'xss_clean|strip_tags|trim|min_length[3]|max_length[30]|callback_displayname_exists_check');
		}

		if ($this->form_validation->run())
		{
			$this->user->remove_blocked_users();
			$added = array();
			for ($i=0; $i<=$this->blocked->num_rows(); $i++)
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
			redirect('account/'.$this->user->user['urlname'].'/messagesettings');
		}
		else
		{
			$this->index();
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

	function updateemail()
	{
		$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|required||max_length[50]|valid_email|callback_email_check');

		if ($this->form_validation->run())
		{
			$this->user->new_user['email'] = set_value('email');
			$this->user->save();

			$note = "You have changed your email address at ".$this->config->item('dmcb_friendly_server').".";
			if ($this->data['self_editing'])
			{
				$note = "An administrator has changed your email address at ".$this->config->item('dmcb_friendly_server').".";
			}
			$this->load->model('notifications_model');
			$this->notifications_model->send(
				$this->user->user['email'],
				$this->config->item('dmcb_friendly_server').' account',
				$note."\n\nBefore you can log in you must activate your address by going to the following URL, ".base_url()."activate/".$this->user->user['userid']."/".$this->user->user['code']
			);
			$message = 'You have successfully updated '.$this->data['person_edited'].' email address. This new address must be activated, an activation email is being sent. <a href="'.base_url().'account/'.$this->user->user['urlname'].'">Return to '.$this->data['person_edited'].' account</a>.';
			$this->_message("Account update", $message, "Success");
		}
		else
		{
			$this->index();
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
}