<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to CI 2.0.2 session library
 *
 * Sessions now expire after browser closes if 'rememberme == 1' not found in session user_data
 * Sessions no longer issued for shockwave/adobe flash, since it kills sessions in ie7 using swf upload
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
class MY_Session extends CI_Session {

	public function __construct($params = array())
	{
		log_message('debug', "Session Class Initialized");

		// Set the super object to a local variable for use throughout the class
		$this->CI =& get_instance();

		// Set all the session preferences, which can either be set
		// manually via the $params array above or via the config file
		foreach (array('sess_encrypt_cookie', 'sess_use_database', 'sess_table_name', 'sess_expiration', 'sess_expire_on_close', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key') as $key)
		{
			$this->$key = (isset($params[$key])) ? $params[$key] : $this->CI->config->item($key);
		}

		if ($this->encryption_key == '')
		{
			show_error('In order to use the Session class you are required to set an encryption key in your config file.');
		}

		// Load the string helper so we can use the strip_slashes() function
		$this->CI->load->helper('string');

		// Do we need encryption? If so, load the encryption class
		if ($this->sess_encrypt_cookie == TRUE)
		{
			$this->CI->load->library('encrypt');
		}

		// Are we using a database?  If so, load it
		if ($this->sess_use_database === TRUE AND $this->sess_table_name != '')
		{
			$this->CI->load->database();
		}

		// Set the "now" time.  Can either be GMT or server time, based on the
		// config prefs.  We use this to set the "last activity" time
		$this->now = $this->_get_time();

		// Set the session length. If the session expiration is
		// set to zero we'll set the expiration two years from now.
		if ($this->sess_expiration == 0)
		{
			$this->sess_expiration = (60*60*24*365*2);
		}
		
		// Set the cookie name
		$this->sess_cookie_name = $this->cookie_prefix.$this->sess_cookie_name;

		// dmcb code change
		// encapsulating if statement to stop flash from creating a new session
		if (!stristr($this->CI->input->user_agent(),'shockwave'))
		{
			// Run the Session routine. If a session doesn't exist we'll
			// create a new one.  If it does, we'll update it.
			if ( ! $this->sess_read())
			{
				$this->sess_create();
			}
			else
			{
				$this->sess_update();
			}

			// Delete 'old' flashdata (from last request)
			$this->_flashdata_sweep();

			// Mark all new flashdata as old (data will be deleted before next request)
			$this->_flashdata_mark();

			// Delete expired sessions if necessary
			$this->_sess_gc();
		}
		// end dmcb code change
		log_message('debug', "Session routines successfully run");
	}

	function sess_write()
	{
		// Are we saving custom data to the DB?  If not, all we do is update the cookie
		if ($this->sess_use_database === FALSE)
		{
			$this->_set_cookie();
			return;
		}

		// set the custom userdata, the session data we will set in a second
		$custom_userdata = $this->userdata;
		$cookie_userdata = array();

		// Before continuing, we need to determine if there is any custom data to deal with.
		// Let's determine this by removing the default indexes to see if there's anything left in the array
		// and set the session data while we're at it
		foreach (array('session_id','ip_address','user_agent','last_activity') as $val)
		{
			unset($custom_userdata[$val]);
			$cookie_userdata[$val] = $this->userdata[$val];
		}
		
		// dmcb code change
		// Define rememberme value
		$rememberme = false;
		// end dmcb code change

		// Did we find any custom data?  If not, we turn the empty array into a string
		// since there's no reason to serialize and store an empty array in the DB
		if (count($custom_userdata) === 0)
		{
			$custom_userdata = '';
		}
		else
		{
			// dmcb code change
			// Check to see if rememberme = 1
			if (isset($custom_userdata["rememberme"]))
			{
				if ($custom_userdata["rememberme"] == 1)
				{
					$rememberme = TRUE;
				}
			}
			// end dmcb code change
		
			// Serialize the custom data array so we can store it
			$custom_userdata = $this->_serialize($custom_userdata);
		}

		// Run the update query
		$this->CI->db->where('session_id', $this->userdata['session_id']);
		$this->CI->db->update($this->sess_table_name, array('last_activity' => $this->userdata['last_activity'], 'user_data' => $custom_userdata));

		// Write the cookie.  Notice that we manually pass the cookie data array to the
		// _set_cookie() function. Normally that function will store $this->userdata, but
		// in this case that array contains custom data, which we do not want in the cookie.
		// dmcb code change
		// Added second parameter to _set_cookie to pass along rememberme value
		$this->_set_cookie($cookie_userdata, $rememberme);
		// end dmcb code change
	}

	// dmcb code change
	// Added $rememberme
	function _set_cookie($cookie_data = NULL, $rememberme = NULL)
	{
	// end dmcb code change
		if (is_null($cookie_data))
		{
			$cookie_data = $this->userdata;
		}
		
		// dmcb code change
		// Adding default remember me of false
		if (is_null($rememberme))
		{
			$rememberme = FALSE;
		}
		// end dmcb code change

		// Serialize the userdata for the cookie
		$cookie_data = $this->_serialize($cookie_data);

		if ($this->sess_encrypt_cookie == TRUE)
		{
			$cookie_data = $this->CI->encrypt->encode($cookie_data);
		}
		else
		{
			// if encryption is not used, we provide an md5 hash to prevent userside tampering
			$cookie_data = $cookie_data.md5($cookie_data.$this->encryption_key);
		}

		// dmcb code change
		// This:
		// $expire = ($this->sess_expire_on_close === TRUE) ? 0 : $this->sess_expiration + time();
		// Becomes this:
		$expire = ($rememberme === FALSE) ? 0 : $this->sess_expiration + time();
		// So that global config doesn't set session remembrance, but how the session is made by our forms sets it
		// end dmcb code change
		
		// Set the cookie
		setcookie(
					$this->sess_cookie_name,
					$cookie_data,
					$expire,
					$this->cookie_path,
					$this->cookie_domain,
					$this->cookie_secure
				);
	}
}