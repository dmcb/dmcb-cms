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
class Signon extends MY_Controller {

	function Signon()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		// Determine redirection in URL
		$data['redirection'] = NULL;
		$data['signoff_message'] = $this->session->flashdata('signoff_message');;
		if ($this->uri->segment(2) != "signup" && $this->uri->segment(2) != "recover")
		{
			$i = 2;
			while ($this->uri->segment($i) != NULL) {
				$data['redirection'] .= $this->uri->segment($i)."/";
				$i++;
			}
		}

		// If Facebook connect is enabled, load it up
		if ($this->config->item('dmcb_signon_facebook') == "true")
		{
			$this->load->library('facebook_connect');
			$data['session'] = $this->facebook_connect->session;
			$data['me'] = $this->facebook_connect->me;
			$data['uid'] = $this->facebook_connect->uid;
			$data['loginUrl'] = $this->facebook_connect->loginUrl;
			$data['logoutUrl'] = $this->facebook_connect->logoutUrl;
		}

		// If the user is already signed on, send them on their way
		if ($this->session->userdata('signedon'))
		{
			if ($data['redirection'] != NULL)
			{
				redirect($data['redirection']);
			}
			else
			{
				redirect($this->config->item('dmcb_default_signedon_location'));
			}
		}

		if ($this->uri->segment(2) == "signup" && $this->config->item('dmcb_guest_signup'))
		{
			$this->focus = "signup";
			$this->form_validation->set_rules('signup_email', 'email address', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_email_check');
			$this->form_validation->set_rules('signup_display', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_check');
			$this->form_validation->set_rules('signup_password', 'password', 'xss_clean|trim|required|min_length[6]|max_length[15]|matches[signup_password_confirm]|md5');
			$this->form_validation->set_rules('signup_password_confirm', 'confirm password', 'xss_clean|trim|required|min_length[6]|max_length[15]|md5');
		}
		else if ($this->uri->segment(2) == "recover")
		{
			$this->focus = "recover";
			$this->form_validation->set_rules('emailforgot', 'email address', 'trim|required|max_length[50]|valid_email|callback_recover_check');
		}
		else
		{
			$this->form_validation->set_rules('email', 'email', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_code_check|callback_banned_check');
			$this->form_validation->set_rules('password', 'password', 'xss_clean|trim|required|callback_login_check|md5');
			$this->form_validation->set_rules('rememberme', 'rememberme', 'xss_clean|strip_tags');
		}

		if ($this->form_validation->run())
		{
			if ($this->uri->segment(2) == "signup" && $this->config->item('dmcb_guest_signup')) // Creating a new account
			{
				$this->load->library('user_lib',NULL,'new_user');
				$this->new_user->new_user['email'] = set_value('signup_email');
				$this->new_user->new_user['displayname'] = set_value('signup_display');
				$this->new_user->new_user['password'] = set_value('signup_password');
				$result = $this->new_user->save();

				$this->_message("Sign up", $result['message'], $result['subject']);
			}
			else if ($this->uri->segment(2) == "recover") // Recovering password
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
					$data['subject'] = "Success!";
					$data['message'] = "You have successfully reset your password at ".$this->config->item('dmcb_friendly_server').".  Please check your inbox for your new password.";
				}
				else {
					$data['subject'] = "Error";
					$data['message'] = "Password reset failed, please contact support at <a href=\"mailto:".$this->config->item('dmcb_email_support')."\">".$this->config->item('dmcb_email_support')."</a>.";
				}
				$this->_message("Recover", $data['message'], $data['subject']);
			}
			else // Otherwise we are doing a regular sign on
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

				if ($data['redirection'] == NULL)
				{
					redirect($this->config->item('dmcb_default_signedon_location'));
				}
				else
				{
					redirect($data['redirection']);
				}
			}
		}
		else
		{
			if ($this->config->item('dmcb_guest_signup'))
			{
				$this->_initialize_page('signon', 'Sign on / Sign up', $data);
			}
			else
			{
				$this->_initialize_page('signon', 'Sign on', $data);
			}
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

	function code_check($str)
	{
		$object = instantiate_library('user', $str, 'email');
		if ($object->check_activated())
		{
			return TRUE;
		}
		else
		{
			$code = $object->user['userid']."/".$object->user['code'];
			$message = "You have created an account at ".$this->config->item('dmcb_friendly_server').".\n\nBefore you can log in you must activate your account by going to the following URL, ".base_url()."activate/".$code;
			$this->load->model('notifications_model');
			$this->notifications_model->send($str, $this->config->item('dmcb_friendly_server').' account', $message);
			$this->form_validation->set_message('code_check', "You have not activated your account.  An activation email is being resent.");
			return FALSE;
		}
	}

	function banned_check($str)
	{
		$this->form_validation->set_message('banned_check', "Your account has been banned.  Contact ".$this->config->item('dmcb_email_support')." to be reinstated.");
		$object = instantiate_library('user', $str, 'email');
		return !$object->check_banned();
	}
}