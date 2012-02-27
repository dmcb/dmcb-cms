<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb acl library
 *
 * Gets the role of user on a particular function and returns their priveleges
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Acl {

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function Acl()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('acls_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Access
	 *
	 * Demtermines if a user has access to a page or post given an array of roles
	 *
	 * @access	private
	 * @param   array  page
	 * @param   bool   signon_prompt
	 * @return	bool
	 */
	function access($roles_array, $page_object, $postid = NULL, $signon_prompt = FALSE)
	{
		if (!$this->CI->session->userdata('signedon'))
		{
			$roleid = $this->CI->acls_model->get_roleid('guest');
			if (isset($roles_array[$roleid]))
			{
				return TRUE;
			}
			else if ($signon_prompt)
			{
				redirect('signon'.$this->CI->uri->uri_string());
			}
		}
		else
		{
			$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'site');
			if (isset($roles_array[$roleid]))
			{
				return TRUE;
			}
			else
			{
				if (isset($postid))
				{
					$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'post', $postid);
					if (isset($roles_array[$roleid]))
					{
						return TRUE;
					}
				}
				if (isset($page_object))
				{
					$page_object->initialize_page_tree();
					if (isset($page_object->page_tree))
					{
						foreach ($page_object->page_tree as $pageid)
						{
							$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'page', $pageid);
							if (isset($roles_array[$roleid]))
							{
								return TRUE;
							}
						}
					}
				}
			}
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Allow
	 *
	 * Return a true if the user has permission to execute function, otherwise returns false
	 *
	 * @access	private
	 * @param   string controller
	 * @param   string function
	 * @param   bool   signon_prompt
	 * @param   string attachedto
	 * @param   int    attachedid
	 * @return	bool
	 */
	function allow($controller, $function, $signon_prompt = FALSE, $attachedto = NULL, $attachedid = NULL)
	{
		if (!$this->CI->session->userdata('signedon')) // If the user isn't signed on, let's see if the guest has permissions
		{
			$roleid = $this->CI->acls_model->get_roleid('guest');
			if ($this->CI->acls_model->get_privelege($roleid, $controller, $function))
			{
				return TRUE;
			}
			else if ($signon_prompt) // If the guest isn't allowed, send the guest to sign on
			{
				redirect('signon'.$this->CI->uri->uri_string());
			}
		}
		else
		{
			// Get user's site role and see if that gives them privelege
			$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'site');
			if ($roleid != NULL && $this->CI->acls_model->get_privelege($roleid, $controller, $function))
			{
				return TRUE;
			}
			else // If the user's site role doesn't have permission, try getting role on current area (i.e. the page)
			{
				$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), $controller, $attachedid);
				if ($roleid != NULL && $this->CI->acls_model->get_privelege($roleid, $controller, $function))
				{
					return TRUE;
				}
				else if ($attachedto == "user") // If we're testing a user, we can check if the user in question is the user
				{
					if ($attachedid == $this->CI->session->userdata('userid'))
					{
						$roleid = $this->CI->acls_model->get_roleid('owner');
						if ($this->CI->acls_model->get_privelege($roleid, $controller, $function))
						{
							return TRUE;
						}
					}
				}
				else if ($attachedto == "post") // If we're testing a post, we can check if the user is an author, otherwise grab their role from the parent page
				{
					$object = instantiate_library('post', $attachedid);
					if (isset($object->post['userid']) && $object->post['userid'] == $this->CI->session->userdata('userid'))
					{
						$roleid = $this->CI->acls_model->get_roleid('owner');
						if ($this->CI->acls_model->get_privelege($roleid, $controller, $function))
						{
							return TRUE;
						}
					}
					if (isset($object->post['pageid']))
					{
						$page = instantiate_library('page', $object->post['pageid']);
						$page->initialize_page_tree();
						if (isset($page->page_tree))
						{
							foreach ($page->page_tree as $pageid)
							{
								$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'page', $pageid);
								if ($roleid != NULL && $this->CI->acls_model->get_privelege($roleid, $controller, $function))
								{
									return TRUE;
								}
							}
						}
					}
				}
				else if ($attachedto == "page") // If we're using a non-page controller function, but it acts on a page, let's check if user has a special role on that page
				{
					$page = instantiate_library('page', $attachedid);
					$page->initialize_page_tree();
					if (isset($page->page_tree))
					{
						foreach ($page->page_tree as $pageid)
						{
							$roleid = $this->CI->acls_model->get($this->CI->session->userdata('userid'), 'page', $pageid);
							if ($roleid != NULL && $this->CI->acls_model->get_privelege($roleid, $controller, $function))
							{
								return TRUE;
							}
						}
					}
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Enabled
	 *
	 * Check to see if a function is enabled, optionally check if function is enabled with a specific role
	 *
	 * @access	private
	 * @param   string controller
	 * @param   string function
	 * @param   string role
	 * @return	bool
	 */
	function enabled($controller, $function, $role = NULL)
	{
		$result = $this->CI->acls_model->function_enabled($controller, $function);
		if ($role == NULL || !$result)
		{
			return $result;
		}
		else
		{
			$roleid = $this->CI->acls_model->get_roleid($role);
			return $this->CI->acls_model->get_privelege($roleid, $controller, $function);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set
	 *
	 * Set an ACL for a specific userid on a specific area of the site
	 *
	 * @access	private
	 * @param   int    userid
	 * @param   int    roleid
	 * @param   string controller
	 * @param   int    attachedid
	 * @return	void
	 */
	function set($userid, $roleid, $controller = 'site', $attachedid = NULL)
	{
		$oldroleid = $this->CI->acls_model->get($userid, $controller, $attachedid);
		if ($oldroleid != NULL)
		{
			$this->CI->acls_model->delete($userid, $controller, $attachedid);
		}
		$this->CI->acls_model->add($userid, $roleid, $controller, $attachedid);
	}
}