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
class Manage_users extends MY_Controller {

	function Manage_users()
	{
		parent::__construct();

		$this->load->helper('pagination');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model(array('users_model', 'notifications_model'));
	}

	function _remap()
	{
		// Overrides ACLs - if you are the first user of the site, you can always set user roles and set yourself as an administrator
		if ($this->acl->allow('site', 'manage_users', TRUE) || $this->session->userdata('userid') == 1 || $this->_access_denied())
		{
			// Get status and role options
			$this->data['userstatus'] = $this->users_model->get_statuses();
			$this->load->model('acls_model');
			$this->data['userroles'] = $this->acls_model->get_roles();

			// Get member role
			$this->data['memberrole'] = $this->acls_model->get_roleid('member');

			// Determine how users are sorted
			$segments = $this->uri->segment_array();
			foreach ($segments as $segment)
			{
				if ($segment == "by_role" || $segment == "by_name" || $segment == "by_signon" || $segment == "by_status" || $segment == "by_registration" || ($this->acl->enabled('site', 'subscribe') && $segment == "by_subscription"))
				{
					$this->data['sort'] = $segment;
					$this->session->set_flashdata('sort', $segment);
				}
			}

			// Preserve pagination session data for user organization
			$this->session->keep_flashdata('page');

			// Check if no sorting was specified
			if (!isset($this->data['sort']))
			{
				// Try to load from session
				$this->data['sort'] = $this->session->flashdata('sort');
				$this->session->keep_flashdata('sort');

				 // If empty, start new session
				if ($this->data['sort'] == NULL)
				{
					$this->data['sort'] = "by_role";
					$this->session->set_flashdata('sort', "by_role");
				}
			}

			// Get subscription types
			if ($this->acl->enabled('site', 'subscribe'))
			{
				$this->load->model('subscriptions_model');
				$this->data['subscription_types'] = $this->subscriptions_model->get_types_by_price();
			}

			// Add editing packages (specifically for calendar option and email attachments)
			$data['packages_editing'] = $this->load->view('packages_editing', NULL, TRUE);
			$this->data['packages_upload'] = $this->load->view('packages_upload',
				array(
					'upload_url' => 'email',
					'upload_size' => $this->config->item('dmcb_site_upload_size'),
					'upload_types' => $this->config->item('dmcb_site_upload_types'),
					'upload_description' => $this->config->item('dmcb_site_upload_description')
				), TRUE);

			$method = $this->uri->segment(2);
			if ($method == "adduser" || $method == "email" || $method == "mailinglist" || $method == "password" || $method == "report" || $method == "subscription")
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

	function index()
	{
		// Handle user operations
		if ($this->uri->segment(2) == "set_role")
		{
			$user = instantiate_library('user', $this->uri->segment(3));
			$this->acl->set($this->uri->segment(3), $this->uri->segment(4));

			// Do notification
			$this->session->set_flashdata('change', 'role change');
			$this->session->set_flashdata('action', 'set');
			$this->session->set_flashdata('actionon', 'user');
			$this->session->set_flashdata('actiononid', $user->user['userid']);
			$this->session->set_flashdata('parentid', $user->user['userid']);
			$this->session->set_flashdata('content', strtolower($this->acls_model->get_role_name($this->uri->segment(4))));
			$this->session->set_flashdata('return', 'manage_users');
			redirect('notify');
		}
		else if ($this->uri->segment(2) == "set_status")
		{
			$user = instantiate_library('user', $this->uri->segment(3));
			$old_statusid = $user->user['statusid'];
			$user->new_user['statusid'] = $this->uri->segment(4);
			$user->save();

			// Do notification
			$this->session->set_flashdata('change', 'status change');
			if ($old_statusid > $this->uri->segment(4))
			{
				$this->session->set_flashdata('action', 'downgraded');
			}
			else
			{
				$this->session->set_flashdata('action', 'upgraded');
			}
			$this->session->set_flashdata('actionon', 'user');
			$this->session->set_flashdata('actiononid', $user->user['userid']);
			$this->session->set_flashdata('parentid', $user->user['userid']);
			$this->session->set_flashdata('content', strtolower($this->users_model->get_status_name($this->uri->segment(4))));
			$this->session->set_flashdata('return', 'manage_users');
			redirect('notify');
		}
		else if ($this->uri->segment(2) == "delete")
		{
			$user = instantiate_library('user', $this->uri->segment(3));
			$user->delete();
			redirect('manage_users');
		}
		else
		{
			// Render page

			// Check if no page was specified
			if (get_pagination_uri() == NULL)
			{
				// Try to load from session
				$this->data['page'] = $this->session->flashdata('page');

				// If there's page info in the session, redirect
				// This is kind of ugly, but pagination in Code Igniter is fairly broken
				if ($this->data['page'] != NULL)
				{
					redirect('manage_users/index/'.$this->data['page']);
				}
			}
			else
			{
				$offset = $this->uri->segment(get_pagination_uri());
				if ($offset == NULL)
				{
					$offset = 0;
				}
				$this->session->set_flashdata('page', $offset);
			}

			$this->data['userlist'] = array();
			$page_limit = 50;
			$offset = generate_pagination($this->users_model->get_user_count(), $page_limit);

			if ($this->data['sort'] == "by_name") // Organize users by their name
			{
				$items = $this->acls_model->get_userlist_with_name($page_limit, $offset);
				foreach($items->result_array() as $item)
				{
					$letter = strtolower($item['displayname'][0]);
					if (!isset($this->data['userlist'][$letter]))
					{
						$this->data['userlist'][$letter] = array();
					}

					$object = instantiate_library('user', $item['userid']);
					$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
					$object->user['roleid'] = $item['roleid'];
					array_push($this->data['userlist'][$letter], $object->user);
				}
			}
			else if ($this->data['sort'] == "by_role") // Organize users by their role
			{
				$items = $this->acls_model->get_userlist_with_role($page_limit, $offset);
				foreach($items->result_array() as $item)
				{
					$role = $item['role'].'s';
					if (!isset($this->data['userlist'][$role]))
					{
						$this->data['userlist'][$role] = array();
					}

					$object = instantiate_library('user', $item['userid']);
					$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
					$object->user['roleid'] = $item['roleid'];
					array_push($this->data['userlist'][$role], $object->user);
				}
			}
			else if ($this->data['sort'] == "by_signon") // Organize users by their last sign on date
			{
				$items = $this->acls_model->get_userlist_with_date($page_limit, $offset);
				foreach($items->result_array() as $item)
				{
					// Sort users in to when they last signed on
					$timespan = "Never";
					if ($item['lastsignon'] != "0000-00-00 00:00:00")
					{
						$lastsignon = strtotime($item['lastsignon']);
						if ($lastsignon > (time()-86400))
						{
							$timespan = "Today";
						}
						else if ($lastsignon > (time()-604800))
						{
							$timespan = "This week";
						}
						else if ($lastsignon > (time()-2419200))
						{
							$timespan = "This month";
						}
						else if ($lastsignon > (time()-31536000))
						{
							$timespan = "This year";
						}
						else
						{
							$timespan = "Later";
						}
					}
					if (!isset($this->data['userlist'][$timespan]))
					{
						$this->data['userlist'][$timespan] = array();
					}

					$object = instantiate_library('user', $item['userid']);
					$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
					$object->user['roleid'] = $item['roleid'];
					array_push($this->data['userlist'][$timespan], $object->user);
				}
			}
			else if ($this->data['sort'] == "by_registration") // Organize users by the date they registered
			{
				$items = $this->acls_model->get_userlist_with_registration($page_limit, $offset);
				foreach($items->result_array() as $item)
				{
					// Sort users in to when they last signed on
					$timespan = "Never";
					if ($item['registered'] != "0000-00-00 00:00:00")
					{
						$registered = strtotime($item['registered']);
						if ($registered > (time()-86400))
						{
							$timespan = "Today";
						}
						else if ($registered > (time()-604800))
						{
							$timespan = "This week";
						}
						else if ($registered > (time()-2419200))
						{
							$timespan = "This month";
						}
						else if ($registered > (time()-31536000))
						{
							$timespan = "This year";
						}
						else
						{
							$timespan = "Later";
						}
					}
					if (!isset($this->data['userlist'][$timespan]))
					{
						$this->data['userlist'][$timespan] = array();
					}

					$object = instantiate_library('user', $item['userid']);
					$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
					$object->user['roleid'] = $item['roleid'];
					array_push($this->data['userlist'][$timespan], $object->user);
				}
			}
			else if ($this->data['sort'] == "by_status") // Organize users by their status
			{
				// Convert statuses into a table
				$statuses = array();
				foreach ($this->data['userstatus']->result_array() as $status)
				{
					$statuses[$status['statusid']] = $status['status'];
				}

				$items = $this->acls_model->get_userlist_with_status($page_limit, $offset);
				foreach($items->result_array() as $item)
				{
					$status = $statuses[$item['statusid']];
					if (!isset($this->data['userlist'][$status]))
					{
						$this->data['userlist'][$status] = array();
					}

					$object = instantiate_library('user', $item['userid']);
					$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
					$object->user['roleid'] = $item['roleid'];
					array_push($this->data['userlist'][$status], $object->user);
				}
			}
			else if ($this->data['sort'] == "by_subscription") // Organize users by their subscription
			{
				foreach ($this->data['subscription_types']->result_array() as $subscription_type)
				{
					// Get current users of this subscription type
					$subscribers = $this->subscriptions_model->get_list_by_type($subscription_type['typeid'], "$page_limit", $offset);
					if ($subscribers->num_rows() > 0)
					{
						$subscription_name = $subscription_type['type'];
						$this->data['userlist'][$subscription_name] = array();
						foreach ($subscribers->result_array() as $item)
						{
							$object = instantiate_library('user', $item['userid']);
							$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
							$object->user['roleid'] = $this->acls_model->get($object->user['userid'], 'site');
							$object->user['subscriptiondate'] = $item['date'];
							array_push($this->data['userlist'][$subscription_name], $object->user);
						}
					}
					$page_limit -= $subscribers->num_rows();
					$offset -= $this->subscriptions_model->get_list_by_type_count($subscription_type['typeid']);
					if ($offset < 0)
					{
						$offset = 0;
					}

					// Get expired users of this subscription type
					$subscribers_expired = $this->subscriptions_model->get_list_by_type_expired($subscription_type['typeid'], "$page_limit", $offset);
					if ($subscribers_expired->num_rows() > 0)
					{
						$subscription_name = 'Expired '.$subscription_type['type'];
						$this->data['userlist'][$subscription_name] = array();
						foreach ($subscribers_expired->result_array() as $item)
						{
							$object = instantiate_library('user', $item['userid']);
							$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
							$object->user['roleid'] = $this->acls_model->get($object->user['userid'], 'site');
							$object->user['subscriptiondate'] = $item['date'];
							array_push($this->data['userlist'][$subscription_name], $object->user);
						}
					}
					$page_limit -= $subscribers_expired->num_rows();
					$offset -= $this->subscriptions_model->get_list_by_type_expired_count($subscription_type['typeid']);
					if ($offset < 0)
					{
						$offset = 0;
					}
				}
				$non_subscribers = $this->subscriptions_model->get_list_by_none("$page_limit", $offset);
				if ($non_subscribers->num_rows() > 0)
				{
					$subscription_name = 'Never subscribed';
					$this->data['userlist'][$subscription_name] = array();
					foreach ($non_subscribers->result_array() as $item)
					{
						$object = instantiate_library('user', $item['userid']);
						$object->user['plusminus'] = $this->notifications_model->get_plus_minus($object->user['userid']);
						$object->user['roleid'] = $this->acls_model->get($object->user['userid'], 'site');
						array_push($this->data['userlist'][$subscription_name], $object->user);
					}
				}
			}

			$this->_initialize_page('manage_users', 'Manage users', $this->data);
		}
	}

	function adduser()
	{
		$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_email_check');
		$this->form_validation->set_rules('displayname', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_check');
		$this->form_validation->set_rules('role', 'role', 'xss_clean|strip_tags');

		if ($this->form_validation->run())
		{
			$this->load->library('user_lib',NULL,'new_user');
			$this->new_user->new_user['email'] = set_value('email');
			$this->new_user->new_user['displayname'] = set_value('displayname');
			$this->new_user->new_user['roleid'] = set_value('role');
			$result = $this->new_user->save();

			$this->_message("Add a user", $result['message'], $result['subject']);
		}
		else
		{
			$this->index();
		}
	}

	function email()
	{
		$this->form_validation->set_rules('personalcopy', 'send yourself a copy', 'xss_clean|strip_tags');
		$this->form_validation->set_rules('emailsubject', 'email subject', 'xss_clean|strip_tags|trim|required|max_length[50]');
		$this->form_validation->set_rules('emailmessage', 'email message', 'xss_clean|strip_tags|trim|required|max_length[1000]');
		$this->form_validation->set_rules('maillist', 'mail list', 'xss_clean|strip_tags');

		// Grab submitted maillist from flash data, if it doesn't exist, it's already in the form and we will grab it from there
		$maillist = $this->session->flashdata('maillist');
		$this->session->keep_flashdata('maillist');
		if ($maillist == NULL)
		{
			$maillist = explode(';', set_value('maillist'));
		}

		// Loop through the maillist data and assemble it for the form
		$this->data['maillist'] = array();
		foreach ($maillist as $member)
		{
			if ($member != NULL)
			{
				$object = instantiate_library('user', $member);
				if (isset($object->user['userid']))
				{
					array_push($this->data['maillist'], $object->user);
				}
			}
		}

		// Clear out any attachments older than 1 hour that remain in the case where a mail was created with attachments and never sent out
		if ($handle = opendir('files_managed/email/'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != ".." && filemtime('files_managed/email/'.$file) < (time()-3600))
				{
					unlink('files_managed/email/'.$file);
				}
			}
			closedir($handle);
		}

		// Grab any attachments
		$this->data['upload_url'] = "email";
		$this->data['files'] = $this->session->flashdata('mailattachments');
		$this->session->keep_flashdata('mailattachments');
		if ($this->data['files'] == NULL)
		{
			$this->data['files'] = array();
			$this->session->set_flashdata('mailattachments', $this->data['files']);
		}

		if ($this->uri->segment(3) == "delete") // The user has chosen to delete an attachment from their email
		{
			$attachments = array();
			foreach ($this->data['files'] as $file)
			{
				if ($file == $this->uri->segment(4) && file_exists('files_managed/email/'.$this->uri->segment(4)))
				{
					unlink('files_managed/email/'.$this->uri->segment(4));
				}
				else
				{
					array_push($attachments, $file);
				}
			}
			$this->session->set_flashdata('mailattachments', $attachments);
			redirect('manage_users/email');
		}
		else if (sizeof($this->data['maillist'])) // Otherwise, if a mailing list exists, lets take them to the email form
		{
			// Loop through attachments, adding the full path for use in sending the email
			$attachments = array();
			foreach ($this->data['files'] as $file)
			{
				array_push($attachments, 'files_managed/email/'.$file);
			}

			if ($this->form_validation->run() && $this->session->flashdata('postdone') == NULL)
			{
				$personalcopy = set_value('personalcopy');
				$subject = html_entity_decode(set_value('emailsubject'), ENT_QUOTES);
				$message = html_entity_decode(set_value('emailmessage'), ENT_QUOTES);

				// Send a copy to the server
				$this->notifications_model->send_to_server('web@'.$this->config->item('dmcb_server'), $subject, $message, $attachments);

				// Send a copy to sender if specified
				if ($personalcopy)
				{
					$sender = instantiate_library('user', $this->session->userdata('userid'));
					$log = "You sent a message to the following users:\n\n";
					foreach ($this->data['maillist'] as $user)
					{
						if ($user['mailinglist'])
						{
							$log .= $user['displayname']." (".$user['email'].")\n";
						}
						else
						{
							$log .= $user['displayname']." (".$user['email'].") opted out of the mailing list, no email was sent\n";
						}
					}
					$log .= "\nThe message was as follows:\n\n";
					$this->notifications_model->send($sender->user['email'], $subject, $log.$message, $attachments);
				}

				// Send to all users individually
				foreach ($this->data['maillist'] as $user)
				{
					if ($user['mailinglist'])
					{
						$this->notifications_model->send($user['email'], $subject, $message, $attachments);
					}
				}

				// Indicate the post is done so a refresh doesn't spam the maillist again
				$this->session->set_flashdata('postdone', TRUE);

				// Clear out used attachments
				foreach ($attachments as $attachment)
				{
					if (file_exists($attachment))
					{
						unlink($attachment);
					}
				}

				// Render page
				$message = "You have sent out an update to the following email addresses:</p><br/><table>";
				foreach ($this->data['maillist'] as $user)
				{
					if ($user['mailinglist'])
					{
						$message .= '<tr><td>'.$user['displayname'].' ('.$user['email'].')</td></tr>';
					}
					else
					{
						$message .= '<tr><td><span class="restricted">'.$user['displayname'].' ('.$user['email'].') opted out of the mailing list, no email was sent</span></td></tr>';
					}
				}
				$message .= '</table><br/><p><a href="'.base_url().'manage_users">Return to managing users</a>';
				$this->_message("Send email", $message, "Success!");
			}
			else if ($this->session->flashdata('postdone') == NULL)
			{
				$this->_initialize_page('send_email', 'Send email', $this->data);
			}
			else
			{
				redirect('manage_users');
			}
		}
		else // No maillist was found, give a message saying the email list is empty
		{
			$message = 'No users matched your mailling list criteria. <a href="'.base_url().'manage_users/mailinglist">Assemble a new list</a>.';
			$this->_message("Send email", $message, "Empty list");
		}
	}

	function mailinglist()
	{
		// Assemble types of mailing lists
		$this->form_validation->set_rules('sendto_all', 'mailing list', 'xss_clean');

		foreach ($this->data['userroles']->result_array() as $role)
		{
			if ($this->data['memberrole'] == $role['roleid'])
			{
				foreach ($this->data['userstatus']->result_array() as $status)
				{
					$this->form_validation->set_rules('sendto_'.$role['roleid'].'_'.$status['status'], $status['status'].' '.strtolower($role['role'].'s'), 'xss_clean');
				}
			}
			else
			{
				$this->form_validation->set_rules('sendto_'.$role['roleid'], strtolower($role['role'].'s'), 'xss_clean');
			}
		}

		if ($this->acl->enabled('site', 'subscribe'))
		{
			$this->load->model('subscriptions_model');
			$this->data['subscription_types'] = $this->subscriptions_model->get_types_by_price();
			foreach ($this->data['subscription_types']->result_array() as $subscription_type)
			{
				$this->form_validation->set_rules('sendto_subscribers_'.strtolower($subscription_type['typeid']), strtolower($subscription_type['type']).' subscribers', 'xss_clean');
				$this->form_validation->set_rules('sendto_subscribers_'.strtolower($subscription_type['typeid']).'_expired', 'expired '.strtolower($subscription_type['type']).' subscribers', 'xss_clean');
			}
			$this->form_validation->set_rules('sendto_subscribers_none', 'expired trial subscribers', 'xss_clean');
		}

		if ($this->form_validation->run())
		{
			$list = array();

			//If sendto_all is specified, gather all users on mailing list to mail out to
			if (set_value('sendto_all') == "1")
			{
				$users = $this->users_model->get_mailing_list();
				foreach ($users->result_array() as $user)
				{
					array_push($list, $user['userid']);
				}
			}

			// Comb through all roles
			foreach ($this->data['userroles']->result_array() as $role)
			{
				// If the member role is selected, we will have more verbose matching against the status of the user
				if ($this->data['memberrole'] == $role['roleid'])
				{
					foreach ($this->data['userstatus']->result_array() as $status)
					{
						$group = 'sendto_'.$role['roleid'].'_'.$status['status'];
						if (set_value($group) == "1")
						{
							$users = $this->acls_model->get_userlist_by_role($role['roleid'], 'site');
							foreach ($users->result_array() as $user)
							{
								$object = instantiate_library('user', $user['userid']);
								if ($object->user['statusid'] == $status['statusid'])
								{
									array_push($list, $object->user['userid']);
								}
							}
						}
					}
				}
				else // Otherwise only match against the role
				{
					$group = 'sendto_'.$role['roleid'];
					if (set_value($group) == "1")
					{
						$users = $this->acls_model->get_userlist_by_role($role['roleid'], 'site');
						foreach ($users->result_array() as $user)
						{
							array_push($list, $user['userid']);
						}
					}
				}
			}

			// If subscriptions are enabled, have options to mail out to  paid, trial and expired subscribers
			if ($this->acl->enabled('site', 'subscribe'))
			{
				foreach ($this->data['subscription_types']->result_array() as $subscription_type)
				{
					$group = 'sendto_subscribers_'.$subscription_type['typeid'];
					if (set_value($group) == "1")
					{
						$users = $this->subscriptions_model->get_list_by_type($subscription_type['typeid']);
						foreach ($users->result_array() as $user)
						{
							array_push($list, $user['userid']);
						}
					}
					$group = 'sendto_subscribers_'.$subscription_type['typeid'].'_expired';
					if (set_value($group) == "1")
					{
						$users = $this->subscriptions_model->get_list_by_type_expired($subscription_type['typeid']);
						foreach ($users->result_array() as $user)
						{
							array_push($list, $user['userid']);
						}
					}
				}
				$group = 'sendto_subscribers_none';
				if (set_value($group) == "1")
				{
					$users = $this->subscriptions_model->get_list_by_none();
					foreach ($users->result_array() as $user)
					{
						array_push($list, $user['userid']);
					}
				}
			}

			// Serialize results list so that we can only grab unique results since some users may have been listed multiple times, and then unserialize
			foreach ($list as &$listvalue)
			{
				$listvalue=serialize($listvalue);
			}
			$list = array_unique($list);
			foreach ($list as &$listvalue)
			{
				$listvalue=unserialize($listvalue);
			}
			$this->session->set_flashdata('maillist', $list);
			redirect(base_url().'manage_users/email');
		}
		else
		{
			$this->index();
		}
	}

	function password()
	{
		$user = instantiate_library('user', $this->uri->segment(3));

		$this->load->helper('string');
		$password = random_string();
		$user->new_user['password'] = md5($password);

		$this->message = "";
		if ($user->user['code'] != "")
		{
			$user->new_user['code'] = "";
			$this->message = $user->user['displayname']."'s account has been activated.<br/><br/>";
			// Add subscription trial if subscriptions are enabled on the site and a trial duration greater than zero is specified
			if ($this->acl->enabled('site', 'subscribe') && $this->config->item('dmcb_post_subscriptions_trial_duration') > "0")
			{
				$typeid = $this->subscriptions_model->get_type_free();
				$this->subscriptions_model->set($user->user['userid'], date("Ymd",mktime(0,0,0,date("m"),date("d")+$this->config->item('dmcb_post_subscriptions_trial_duration'),date("Y"))),$typeid);
			}
		}
		$user->save();

		$message = "An administrator has reset your password for ".$this->config->item('dmcb_friendly_server').".  Your temporary password is: ".$password."\n\nPlease change your password immediately by visiting the following url and signing on with your temporary password:\n".base_url()."account/changepassword";
		if ($this->notifications_model->send($user->user['email'], $this->config->item('dmcb_friendly_server').' password reset', $message))
		{
			$this->subject = "Success!";
			$this->message .= "You have successfully generated a password for ".$user->user['displayname'].". The user has been sent an email. The password is: ".$password;
		}
		else {
			$this->subject = "Error";
			$this->message .= "You have successfully generated a password for ".$user->user['displayname'].". However a notification email to the user failed to be sent. The password is: ".$password;
		}
		$this->message .= "<br/><br/>Click <a href=\"".base_url()."manage_users\">here</a> to return to editing.";
		$this->_message("User password", $this->message, $this->subject);
	}

	function report()
	{
		$user = instantiate_library('user', $this->uri->segment(3));
		$this->data['user'] = $user->user;

		// Set email list to user
		$this->session->set_flashdata('maillist', array($user->user['userid']));

		// Get moderation activity
		$this->data['moderations'] = $this->notifications_model->get($user->user['userid']);

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
		$site_role = $this->acls_model->get($user->user['userid'], 'site');
		if ($site_role != NULL && $this->acls_model->get_role_name($site_role) != "member")
		{
			array_push($this->data['privileges'], array('on' => 'site', 'role' => $rolestable[$site_role]));
		}
		$page_priveleges = $this->acls_model->get_all($user->user['userid'], 'page');
		foreach ($page_priveleges->result_array() as $page_privelege)
		{
			$object = instantiate_library('page', $page_privelege['attachedid']);
			if (isset($object->page['pageid']))
			{
				array_push($this->data['privileges'], array('on' => 'page', 'role' => $rolestable[$page_privelege['roleid']], 'page' => $object->page));
			}
		}
		$post_priveleges = $this->acls_model->get_all($user->user['userid'], 'post');
		foreach ($post_priveleges->result_array() as $post_privelege)
		{
			$object = instantiate_library('post', $post_privelege['attachedid']);
			if (isset($object->post['postid']))
			{
				array_push($this->data['privileges'], array('on' => 'post', 'role' => $rolestable[$post_privelege['roleid']], 'post' => $object->post));
			}
		}

		// Get subscription
		$this->load->model('subscriptions_model');
		$this->data['subscription'] = $this->subscriptions_model->get($user->user['userid']);

		$this->_initialize_page("user_report", "User report", $this->data);
	}

	function subscription()
	{
		if ($this->acl->enabled('site', 'subscribe'))
		{
			$this->form_validation->set_rules('subscribetype', 'subscription type', 'xss_clean|strip_tags');
			$this->form_validation->set_rules('subscribedate', 'end date', 'xss_clean|strip_tags|trim|numeric|exact_length[8]');

			// Grab the user to modify the subscription of
			$user = instantiate_library('user', $this->uri->segment(3));

			if ($this->form_validation->run())
			{
				$this->data['subject'] = "Success!";

				if (set_value('subscribetype') == "" || set_value('subscribedate') == "") // Remove subscription
				{
					if ($this->subscriptions_model->get($user->user['userid']) != NULL)
					{
						$this->subscriptions_model->delete($user->user['userid']);

						// Do notification
						$this->session->set_flashdata('change', 'removal of their subscription');
						$this->session->set_flashdata('action', 'removed');
						$this->session->set_flashdata('actionon', 'subscription');
						$this->session->set_flashdata('actiononid', $user->user['userid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', "");
						$this->session->set_flashdata('return', 'manage_users');
						redirect('notify');
					}
					else
					{
						$this->data['message'] = 'Subscription never set for '.$user->user['displayname'].'. Click <a href="'.base_url().'manage_users">here</a> to return to editing.';
					}
				}
				else // Add/update subscription
				{
					$type = $this->subscriptions_model->get_type(set_value('subscribetype'));
					$this->subscriptions_model->set($user->user['userid'], set_value('subscribedate'), set_value('subscribetype'));

					// Do notification
					$this->session->set_flashdata('change', 'update to their subscription');
					$this->session->set_flashdata('action', 'updated');
					$this->session->set_flashdata('actionon', 'subscription');
					$this->session->set_flashdata('actiononid', $user->user['userid']);
					$this->session->set_flashdata('parentid', $user->user['userid']);
					$this->session->set_flashdata('content', $type['type']." subscription, expiring ".date("F jS, Y", strtotime(set_value('subscribedate'))));
					$this->session->set_flashdata('return', 'manage_users');
					redirect('notify');
				}
				$this->_message("User subscription", $this->data['message'], $this->data['subject']);
			}
			else
			{
				$this->data['subscription'] = $this->subscriptions_model->get($user->user['userid']);
				$this->data['subscriptiontypes'] = $this->subscriptions_model->get_types();
				$this->data['user'] = $user->user;
				$this->_initialize_page('user_subscription', 'User subscription', $this->data);
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
}