<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to CI 2.0.2 security library
 *
 * Adds CSRF whitelist of servers
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
class MY_Security extends CI_Security {

	public function csrf_verify()
	{
		// dmcb code change
		if (isset($_POST['Filename']) && isset($_POST['sessionid']))		// This conditional is added, because we don't want SWFUpload having to go through CSRF
		{
			log_message('debug', "CSRF token ignored, file upload ");
		}
		else if (isset($_POST['txn_id']) ||strpos("|64.4.241.16". 			// This conditional is added, because we don't want PayPal having to go through CSRF
						"|64.4.241.32|64.4.241.33|64.4.241.34|64.4.241.35|64.4.241.36|64.4.241.37|64.4.241.38|64.4.241.39".
						"|216.113.188.32|216.113.188.33|216.113.188.34|216.113.188.35".
						"|216.113.188.64|216.113.188.65|216.113.188.66|216.113.188.67".
						"|66.211.169.2".
						"|66.211.169.65".
						"|216.113.188.39".
						"|216.113.188.71".
						"|66.211.168.91".
						"|66.211.168.123".
						"|216.113.188.52".
						"|216.113.188.84".
						"|66.211.168.92".
						"|66.211.168.124".
						"|216.113.188.10".
						"|66.211.168.126".
						"|216.113.188.11".
						"|66.211.168.125".
						"|216.113.188.202".
						"|216.113.188.203".
						"|216.113.188.204".
						"|66.211.170.66".
						"|66.135.197.163".
						"|216.113.169.205".
						"|66.135.197.160".
						"|66.135.197.162".
						"|66.135.197.141".
						"|66.135.197.164|",
						"|".$_SERVER['REMOTE_ADDR']."|") !== FALSE) // ips from https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/howto_api_golivechecklist
		{
			log_message('debug', "CSRF token ignored, PayPal ");
		}
		else // end dmcb code change
		{
			// If no POST data exists we will set the CSRF cookie
			if (count($_POST) == 0)
			{
				return $this->csrf_set_cookie();
			}

			// Do the tokens exist in both the _POST and _COOKIE arrays?
			if ( ! isset($_POST[$this->_csrf_token_name]) OR 
				 ! isset($_COOKIE[$this->_csrf_cookie_name]))
			{
				$this->csrf_show_error();
			}

			// Do the tokens match?
			if ($_POST[$this->_csrf_token_name] != $_COOKIE[$this->_csrf_cookie_name])
			{
				$this->csrf_show_error();
			}

			// We kill this since we're done and we don't want to 
			// polute the _POST array
			unset($_POST[$this->_csrf_token_name]);

			// Nothing should last forever
			unset($_COOKIE[$this->_csrf_cookie_name]);
			$this->_csrf_set_hash();
			$this->csrf_set_cookie();

			log_message('debug', "CSRF token verified ");
			
			return $this;
		}
	}
	
	protected function _csrf_set_hash()
	{
		if ($this->_csrf_hash == '')
		{
			// If the cookie exists we will use it's value.  
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded 
			// sub-pages causing this feature to fail
			if (isset($_COOKIE[$this->_csrf_cookie_name]) && 
				$_COOKIE[$this->_csrf_cookie_name] != '')
			{
				return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];
			}
			
			return $this->_csrf_hash = md5(uniqid(rand(), TRUE));
		}

		return $this->_csrf_hash;
	}
}