<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb facebook connect wrapper
 *
 * Initializes Facebook connect library
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2010, Derek McBurney
 * @link		http://dmcbdesign.com
 */ 

require_once(APPPATH . 'libraries/facebook/facebook.php');

class Facebook_connect
{
	public $session    = array();
	public $me         = array();
	public $uid;
	public $loginUrl;
	public $logoutUrl;

	function Facebook_connect()
	{
		$this->CI =& get_instance();

		$facebook = new Facebook(array(
			'appId' => $this->CI->config->item('dmcb_facebook_app_id'), 
			'secret' => $this->CI->config->item('dmcb_facebook_app_secret'),
			'cookie' => TRUE));
			
		$this->session = $facebook->getSession();
		
		$this->me = NULL;

		if ($this->session)
		{
			try 
			{
				$this->uid = $facebook->getUser();
				$this->me = $facebook->api('/me');
				
				// If the user is signed onto Facebook but not the site, sign them on
				if (!$this->CI->session->userdata('signedon'))
				{
					// Check if an account exists with that Facebook uid
					$object = instantiate_library('user', $this->uid, 'facebook_uid');
					if (!isset($object->user['userid'])) // If not, register a new account or link an existing one to that uid
					{
						$check_user = instantiate_library('user', $this->me['email'], 'email');
						if (isset($check_user->user['userid'])) 
						{
							// Update existing account with Facebook uid
							// Email the user as well informing them
							$message = "You have linked your account with Facebook at ".$this->CI->config->item('dmcb_friendly_server').".";
							
							$check_user->new_user['facebook_uid'] = $this->uid;
							if ($check_user->user['code'] != "") // If their account was locked out, activate it
							{
								$check_user->new_user['code'] = "";
								// Add to the message to let them know that their account is activated				
								$message .= " Your email address has also been verified, so please disregard any emails you may have recieved about activating your account.";
							}
							$check_user->save();
							
							$this->CI->load->model('notifications_model');
							$this->CI->notifications_model->send($check_user->user['email'], $this->CI->config->item('dmcb_friendly_server').' account', $message);
							
							$object->user = $check_user->user;
							$object->new_user = $object->user;
						}
						else
						{
							// Create a new user
							$this->CI->load->library('user_lib',NULL,'new_user');
							$this->CI->new_user->new_user['userid'] = 0;
							$this->CI->new_user->new_user['email'] = $this->me['email'];
							$this->CI->new_user->new_user['displayname'] = $this->me['name'];
							$result = $this->CI->new_user->save();
							
							$this->CI->new_user	= instantiate_library('user', $result['userid']);
							$this->CI->new_user->new_user['facebook_uid'] = $this->uid;
							$this->CI->new_user->new_user['code'] = "";
							$this->CI->new_user->save();							
							
							$object->user = $this->CI->new_user->user;
							$object->new_user = $object->user;
						}
					}
					
					
					$object->new_user['lastsignon'] = date('Y-m-d H:i:s');
					$object->save();
					
					// Sign the user on
					$session = array(
						'userid' => $object->user['userid'],
						'displayname' => $object->user['displayname'],
						'urlname' => $object->user['urlname'],
						'rememberme' => '1',
						'facebook' => '1',
						'signedon' => TRUE
					);
					$this->CI->session->set_userdata($session);	
				}
			} 
			catch (FacebookApiException $e) 
			{
				log_to_file('facebook_connect', $e);
			}
		}
		
		if ($this->me) 
		{
			$this->logoutUrl = $facebook->getLogoutUrl(array('next' => base_url().'signoff'));
		} 
		else 
		{
			$this->loginUrl = $facebook->getLoginUrl(array('req_perms' => 'email'));
		}
	}
}

?>