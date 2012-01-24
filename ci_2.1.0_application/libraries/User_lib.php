<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb user library
 *
 * Initalizes a user and runs checks and operations on that user
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class User_lib {

	public  $user     = array();
	public  $new_user = array();

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function User_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('users_model');
		if (isset($params['id']))
		{
			$this->new_user = $this->CI->users_model->get($params['id']);
			//$this->new_user = $this->CI->cache->model('users_model', 'get', $params['id']);
			$this->user = $this->new_user;
			$this->_initialize_info();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize info
	 *
	 * Set profile enabled status
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_info()
	{
		if ($this->CI->acl->enabled('profile', 'view'))
		{
			// Future spot of code for specific users profiles to be enabled or disabled, but for now, always enabled
			$this->user['enabledprofile'] = TRUE;
			$this->new_user['enabledprofile'] = TRUE;
		}
		else
		{
			$this->user['enabledprofile'] = FALSE;
			$this->new_user['enabledprofile'] = FALSE;
		}

		$this->CI->load->model('files_model');
		$this->user['avatar'] = NULL;
		if (isset($this->user['profilepicture'])) // Grab avatar if one exists
		{
			// We are not instantiating file library because it will create an infinite loop as it tries to instantiate this user to generate file path
			$object = $this->CI->files_model->get($this->user['profilepicture']);
			if (isset($object['fileid']))
			{
				$this->user['avatar'] = 'file/user/'.$this->user['urlname'].'/'.$object['filename'].'.'.$object['extension'];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add blocked user
	 *
	 * Block a user from messaging
	 *
	 * @access	public
	 * @param   string  displayname
	 * @return	void
	 */
	function add_blocked_user($displayname)
	{
		$object = instantiate_library('user', $displayname, 'displayname');
		if (isset($object->user['userid']))
		{
			$this->CI->users_model->add_blocked_user($this->user['userid'], $object->user['userid']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add rss
	 *
	 * Add a blog rss feed for the user
	 *
	 * @access	public
	 * @param   string  rssfeed
	 * @return	bool
	 */
	function add_rss($rssfeed)
	{
		if ($rssfeed != "")
		{
			$this->CI->users_model->add_rss($this->user['userid'], $rssfeed);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Check unlocked
	 *
	 * Checks if a user is activated
	 *
	 * @access	public
	 * @return	bool
	 */
	function check_activated()
	{
		if (!isset($this->user['code']) || $this->user['code'] == '')
		{
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Check ban
	 *
	 * Checks if a user is banned
	 *
	 * @access	public
	 * @return	bool
	 */
	function check_banned()
	{
		if (!isset($this->user['statusid']) || $this->user['statusid'] != $this->CI->users_model->get_status_lowest())
		{
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Delete a user and clears out references
	 *
	 * @access	public
	 * @return	void
	 */
	function delete()
	{
		$this->CI->load->model(array('files_model', 'acls_model'));
		$files = $this->CI->files_model->get_attached("user",$this->user['userid']);
		foreach ($files->result_array() as $file)
		{
			$object = instantiate_library('file', $file['fileid']);
			$object->delete();
		}
		$this->CI->acls_model->delete($this->user['userid']);
		$this->CI->users_model->delete($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Get blocked users
	 *
	 * Get all users blocked from messaging
	 *
	 * @access	public
	 * @return	void
	 */
	function get_blocked_users()
	{
		return $this->CI->users_model->get_user_blocked_users($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Get posts
	 *
	 * Get posts that the user has made
	 *
	 * @access	public
	 * @return	void
	 */
	function get_posts($num, $offsets)
	{
		$this->CI->load->model('posts_model');
		return $this->CI->posts_model->get_user_posts($this->user['userid'], $num, $offset);
	}

	// --------------------------------------------------------------------

	/**
	 * Get post count
	 *
	 * Get the number of posts the user has made
	 *
	 * @access	public
	 * @return	void
	 */
	function get_posts_count()
	{
		$this->CI->load->model('posts_model');
		return $this->CI->posts_model->get_user_posts_count($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Get rss
	 *
	 * Get user rss feeds
	 *
	 * @access	public
	 * @return	void
	 */
	function get_rss()
	{
		return $this->CI->users_model->get_user_rss($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Remove blocked users
	 *
	 * Remove all users blocked from messaging
	 *
	 * @access	public
	 * @return	void
	 */
	function remove_blocked_users()
	{
		$this->CI->users_model->remove_blocked_users($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Remove rss
	 *
	 * Remove user rss feeds
	 *
	 * @access	public
	 * @return	void
	 */
	function remove_rss()
	{
		$this->CI->users_model->remove_rss($this->user['userid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 *
	 * Save user properties
	 *
	 * @access	public
	 * @return	array   information from user creation
	 */
	function save()
	{
		$this->CI->load->helper('string');
		$result = array();

		$this->new_user['displayname'] = reduce_spacing($this->new_user['displayname']);
		$this->new_user['displayname'] = $this->suggest();
		$this->new_user['urlname'] = to_urlname($this->new_user['displayname']);

		// If the user has a name that's the same as an old placeholder, clear placeholder
		$this->CI->load->model('placeholders_model');
		$placeholder = $this->CI->placeholders_model->get('user', $this->new_user['urlname']);
		if ($placeholder != NULL)
		{
			$this->CI->placeholders_model->delete('user', $this->new_user['urlname']);
		}

		// Check if the user wasn't initialized from an existing one
		if ($this->user == NULL) // If it wasn't, create a new user
		{
			// Minimum requirements for creating a user
			if (isset($this->new_user['email']) && isset($this->new_user['displayname']))
			{
				if (!isset($this->new_user['password'])) // If a password isn't set, create one and return it
				{
					$result['password'] = random_string();
					$this->new_user['password'] = md5($result['password']);
				}
				if (!isset($this->new_user['roleid'])) // If a role isn't set, that means the user self registered and should be set an activation code
				{
					$result['code'] = md5(random_string());
					$this->new_user['code'] = $result['code'];
				}
				else
				{
					$this->new_user['code'] = "";
				}

				$this->new_user['userid'] = $this->CI->users_model->add($this->new_user['email'], $this->new_user['displayname'], $this->new_user['urlname'], $this->new_user['password'], $this->new_user['code']);
				$result['userid'] = $this->new_user['userid'];
				$this->user = $this->new_user;
				if (!isset($this->new_user['roleid'])) // If a role isn't set, set the user as a member
				{
					$this->CI->load->model('acls_model');
					$roleid = $this->CI->acls_model->get_roleid('member');
					$this->CI->acl->set($this->user['userid'], $roleid);
				}
				else
				{
					$this->CI->acl->set($this->user['userid'], $this->new_user['roleid']);
				}

				// Send activation emails
				$this->CI->lang->load('user', 'english', FALSE, TRUE, APPPATH.'site_specific_');

				if ($this->new_user['code'] == "") // User was created by an administrator
				{
					$message = sprintf($this->CI->lang->line('user_created_by_administrator_email'), $this->CI->config->item('dmcb_friendly_server'))."\n\n".
						sprintf($this->CI->lang->line('user_created_by_administrator_email_2'), $this->new_user['email'], $result['password'])."\n\n".
						sprintf($this->CI->lang->line('user_created_by_administrator_email_3'), base_url().'account/changepassword');

					$this->CI->load->model('subscriptions_model');
					if ($this->CI->acl->enabled('site', 'subscribe') && $this->CI->config->item('dmcb_post_subscriptions_trial_duration') > "0" && $this->new_user['roleid'] == 4)
					{
						$typeid = $this->CI->subscriptions_model->get_type_free();
						$this->CI->subscriptions_model->set($result['userid'], date("Ymd",mktime(0,0,0,date("m"),date("d")+$this->CI->config->item('dmcb_post_subscriptions_trial_duration'),date("Y"))),$typeid);
						$message .= "\n\n".sprintf($this->CI->lang->line('user_subscription_trial_start'), $this->CI->config->item('dmcb_post_subscriptions_trial_duration'))."\n";
					}

					$this->CI->load->model('notifications_model');
					if ($this->CI->notifications_model->send($this->new_user['email'], $this->CI->config->item('dmcb_friendly_server').' account', $message))
					{
						$result['subject'] = $this->CI->lang->line('activation_sent_admin_subject');
						$result['message'] = sprintf($this->CI->lang->line('activation_sent_admin'), $this->new_user['displayname'], $this->CI->config->item('dmcb_friendly_server')).' <a href="'.base_url().'manage_users">Return to editing</a>.';
					}
					else
					{
						$result['subject'] = $this->CI->lang->line('error_activation_failed_admin_subject');
						$result['message'] = sprintf($this->CI->lang->line('error_activation_failed_admin'), "<a href=\"mailto:support@".$this->CI->config->item('dmcb_server')."\">support@".$this->CI->config->item('dmcb_server')."</a>");
					}
				}
				else if (isset($this->new_user['facebook_uid'])) // User self-registered through Facebook
				{
					$message = sprintf($this->CI->lang->line('user_created_by_facebook_email'), $this->CI->config->item('dmcb_friendly_server'))."\n\n".
						sprintf($this->CI->lang->line('user_created_by_facebook_email_2'), $this->new_user['email'], $result['password'])."\n\n".
						sprintf($this->CI->lang->line('user_created_by_facebook_email_3'), base_url()."account/changepassword.");

					$this->CI->load->model('notifications_model');
					$this->CI->notifications_model->send($this->new_user['email'], $this->CI->config->item('dmcb_friendly_server').' account', $message);
				}
				else // User self-registered
				{
					$message = sprintf($this->CI->lang->line('user_created_by_self_email'), $this->CI->config->item('dmcb_friendly_server'))."\n\n".
						$this->CI->lang->line('user_created_by_self_email_2')."\n".
						base_url()."activate/".$result['userid']."/".$result['code'];

					$this->CI->load->model('notifications_model');
					if ($this->CI->notifications_model->send($this->new_user['email'], $this->CI->config->item('dmcb_friendly_server').' account', $message))
					{
						$result['subject'] = $this->CI->lang->line('activation_sent_self_subject');
						$result['message'] = sprintf($this->CI->lang->line('activation_sent_self'), $this->CI->config->item('dmcb_friendly_server'));
					}
					else {
						$result['subject'] = $this->CI->lang->line('error_activation_failed_self_subject');
						$result['message'] = sprintf($this->CI->lang->line('error_activation_failed_self'), $this->CI->config->item('dmcb_friendly_server'), "<a href=\"mailto:support@".$this->CI->config->item('dmcb_server')."\">support@".$this->CI->config->item('dmcb_server')."</a>");
					}
				}
			}
		}
		else // If it was, update the existing user
		{
			if ($this->new_user['profile'] != $this->user['profile'])
			{
				$this->new_user['datemodified'] = date('YmdHis');
			}
			if ($this->new_user['email'] != $this->user['email'])
			{
				$this->CI->load->helper('string');
				$this->new_user['code'] = md5(random_string());
			}
			if ($this->new_user['displayname'] != $this->user['displayname'])
			{
				// Add placeholder for URL name change
				$this->CI->load->model('placeholders_model');
				$this->CI->placeholders_model->add("user", $this->user['urlname'], $this->new_user['urlname']);

				// Rename corresponding files folder
				$this->CI->load->model('files_model');
				$this->CI->files_model->rename_folder("user", $this->user['urlname'], $this->new_user['urlname']);

				// Update any blocks that refer to this user's name
				$this->CI->load->model('blocks_model');
				$blockinstances = $this->CI->blocks_model->get_variable_blocks('user', $this->user['urlname']);
				foreach ($blockinstances->result_array() as $blockinstance)
				{
					$object = instantiate_library('block', $blockinstance['blockinstanceid']);
					$object->new_block['values']['user'] = $this->new_user['urlname'];
					$object->save();
				}
			}
			$this->CI->users_model->update($this->user['userid'], $this->new_user);
			$this->user = $this->new_user;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Suggest
	 *
	 * Check to see if the new display name already exists and suggest a new name
	 *
	 * @access	public
	 * @param   string proposed_name
	 * @return	string display name that is available
	 */
	function suggest($proposed_name = NULL)
	{
		if ($proposed_name == NULL)
		{
			$proposed_name = $this->new_user['displayname'];
		}

		$suggestion = $proposed_name;
		$i=1;
		$object = instantiate_library('user', $proposed_name, 'displayname');

		// If this isn't a new user, make sure we allow the name if it's the name of the user we are editing
		if (isset($this->user['userid']))
		{
			while (isset($object->user['userid']) && $object->user['userid'] != $this->user['userid'])
			{
				$i++;
				$suggestion = $proposed_name.'-'.$i;
				$object = instantiate_library('user', $suggestion, 'displayname');
			}
		}
		else
		{
			while (isset($object->user['userid']))
			{
				$i++;
				$suggestion = $proposed_name.'-'.$i;
				$object = instantiate_library('user', $suggestion, 'displayname');
			}
		}
		return $suggestion;
	}
}