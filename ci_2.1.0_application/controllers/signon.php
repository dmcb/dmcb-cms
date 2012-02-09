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
class Signon extends MY_Controller {

	function Signon()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		$this->data = array();
		
		// Determine redirection in URL
		$this->data['redirection'] = NULL;
		$this->data['signoff_message'] = $this->session->flashdata('signoff_message');;
		if ($this->uri->segment(2) == "authenticate" || $this->uri->segment(2) == "signup" || $this->uri->segment(2) == "recover")
		{
			$i = 3;
		}
		else
		{
			$i = 2;
		}

		while ($this->uri->segment($i) != NULL) {
			$this->data['redirection'] .= "/".$this->uri->segment($i);
			$i++;
		}

		// If Facebook connect is enabled, load it up
		if ($this->config->item('dmcb_signon_facebook') == "true")
		{
			$this->load->library('facebook_connect');
			$this->data['session'] = $this->facebook_connect->session;
			$this->data['me'] = $this->facebook_connect->me;
			$this->data['uid'] = $this->facebook_connect->uid;
			$this->data['loginUrl'] = $this->facebook_connect->loginUrl;
			$this->data['logoutUrl'] = $this->facebook_connect->logoutUrl;
		}

		// If the user is already signed on, send them on their way
		if ($this->session->userdata('signedon'))
		{
			if ($this->data['redirection'] != NULL)
			{
				redirect($this->data['redirection']);
			}
			else
			{
				redirect($this->config->item('dmcb_default_signedon_location'));
			}
		}

		$method = $this->uri->segment(2);
		if ($method == "authenticate" || $method == "signup" || $method == "mailinglist" || $method == "recover")
		{
			$this->focus = $method;
			$this->$method();
		}
		else
		{
			$this->index();
		}
	}
	
	function index()
	{
		if ($this->config->item('dmcb_guest_signup'))
		{
			$this->_initialize_page('signon', 'Sign on / Sign up', $this->data);
		}
		else
		{
			$this->_initialize_page('signon', 'Sign on', $this->data);
		}
	}
	
	function authenticate()
	{
		$this->form_validation->set_rules('email', 'email', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_code_check|callback_banned_check');
		$this->form_validation->set_rules('password', 'password', 'xss_clean|trim|required|callback_login_check|md5');
		$this->form_validation->set_rules('rememberme', 'rememberme', 'xss_clean|strip_tags');

		if ($this->form_validation->run())
		{
			$object = instantiate_library('user', set_value('email'), 'email');
			$object->new_user['lastsignon'] = date('Y-m-d H:i:s');
			$object->save();

			$session = array(
				'userid' => $object->user['userid'],
				'displayname' => $object->user['displayname'],
				'urlname' => $object->user['urlname'],
				'rememberme' => set_value('rememberme'),
				'signedon' => TRUE
			);
			$this->session->set_userdata($session);

			if ($this->data['redirection'] == NULL)
			{
				redirect($this->config->item('dmcb_default_signedon_location'));
			}
			else
			{
				redirect($this->data['redirection']);
			}
		}
		else
		{
			$this->index();
		}
	}

	function banned_check($str)
	{
		$this->form_validation->set_message('banned_check', "Your account has been banned.  Contact ".$this->config->item('dmcb_email_support')." to be reinstated.");
		$object = instantiate_library('user', $str, 'email');
		return !$object->check_banned();
	}
	
	function code_check($str)
	{
		$object = instantiate_library('user', $str, 'email');
		if ($object->check_activated())
		{
			return TRUE;
		}
		else
		{
			$this->lang->load('user', 'english', FALSE, TRUE, APPPATH.'site_specific_');
			$message = sprintf($this->lang->line('user_created_by_self_email'), $this->config->item('dmcb_friendly_server'))."\n\n".
				$this->lang->line('user_created_by_self_email_activation')."\n".
				base_url()."activate/".$object->user['userid']."/".$object->user['code'];

			$this->load->model('notifications_model');
			$this->notifications_model->send($str, $this->config->item('dmcb_friendly_server').' account', $message);
			$this->form_validation->set_message('code_check', "You have not activated your account.  An activation email is being resent.");
			return FALSE;
		}
	}
	
	function login_check($str)
	{
		$object = instantiate_library('user', set_value('email'), 'email');
		if (isset($object->user['userid']) && $object->user['password'] == md5($str))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('login_check', "Incorrect email/password combination.");
			return FALSE;
		}
	}
	
	function signup()
	{
		if ($this->config->item('dmcb_guest_signup'))
		{
			$this->form_validation->set_rules('signup_email', 'email address', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_email_check');
			$this->form_validation->set_rules('signup_display', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_check');
			$this->form_validation->set_rules('signup_password', 'password', 'xss_clean|trim|required|min_length[6]|max_length[15]|matches[signup_password_confirm]|md5');
			$this->form_validation->set_rules('signup_password_confirm', 'confirm password', 'xss_clean|trim|required|min_length[6]|max_length[15]|md5');
		
			if ($this->form_validation->run())
			{
				$this->load->library('user_lib',NULL,'new_user');
				$this->new_user->new_user['email'] = set_value('signup_email');
				$this->new_user->new_user['displayname'] = set_value('signup_display');
				$this->new_user->new_user['password'] = set_value('signup_password');
				$result = $this->new_user->save();

				$this->_message("Sign up", $result['message'], $result['subject']);
			}
			else
			{
				$this->index();
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
	
	function mailinglist()
	{
	
	}
	
	function recover()
	{
		$this->form_validation->set_rules('emailforgot', 'email address', 'trim|required|max_length[50]|valid_email|callback_recover_check');
		
		if ($this->form_validation->run())
		{
			$object = instantiate_library('user', set_value('emailforgot'), 'email');
			$this->load->helper('string');
			$password = random_string();
			$object->new_user['password'] = md5($password);
			$object->save();
			$message = "You have successfully reset your password for ".$this->config->item('dmcb_friendly_server').".  Your temporary password is: ".$password."\n\nPlease change your password immediately by visting the following url and logging in with your temporary password:\n".base_url()."account/changepassword";
			$this->load->model('notifications_model');
			if ($this->notifications_model->send($object->user['email'], $this->config->item('dmcb_friendly_server').' password reset', $message))
			{
				$this->data['subject'] = "Success!";
				$this->data['message'] = "You have successfully reset your password at ".$this->config->item('dmcb_friendly_server').".  Please check your inbox for your new password.";
			}
			else {
				$this->data['subject'] = "Error";
				$this->data['message'] = "Password reset failed, please contact support at <a href=\"mailto:".$this->config->item('dmcb_email_support')."\">".$this->config->item('dmcb_email_support')."</a>.";
			}
			$this->_message("Recover", $this->data['message'], $this->data['subject']);
		}
		else
		{
			$this->index();
		}
	}

	function recover_check($str)
	{
		$object = instantiate_library('user', $str, 'email');
		if (isset($object->user['userid']))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('recover_check', "Please select a registered email address.");
			return FALSE;
		}
	}
}