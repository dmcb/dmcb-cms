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
class Manage_security extends MY_Controller {

	function Manage_security()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	function _remap()
	{
		// Overrides ACLs - if you are an administrator, you can never be blocked out of setting security
		$this->load->model('acls_model');
		$roleid = $this->acls_model->get($this->session->userdata('userid'), "site"); // Admin can go in no matter what
		if ($this->acl->allow('site', 'manage_security', TRUE) || ($roleid != NULL && $this->acls_model->get_role_name($roleid) == "administrator") || $this->_access_denied())
		{
			// Get all roles
			$roles = $this->acls_model->get_roles_all();
			$this->rolestable = array();
			$data['roles'] = array();
			$data['customroles'] = array();
			foreach ($roles->result_array() as $role)
			{
				// Generate custom roles list
				if ($role['custom'] == 1)
				{
					array_push($data['customroles'], $role); 
				}
				$this->rolestable[$role['roleid']] = $role; 
				array_push($data['roles'], $role); 
			}
			
			// Get all priveleges
			$priveleges = $this->acls_model->get_priveleges_all();
			foreach ($priveleges->result_array() as $privelege)
			{
				$functionid = $privelege['functionid'];
				$roleid = $privelege['roleid'];
				if (!isset($privelege_table))
				{
					$privelege_table = array();
				}
				if (!isset($privelege_table[$functionid]))
				{
					$privelege_table[$functionid] = array();
				}
				if (!isset($privelege_table[$functionid][$roleid]))
				{
					$privelege_table[$functionid][$roleid] = 1; 
				}
			}
			
			// Get all enabled functions and sort through them
			$data['functions'] = array();
			$this->functionstable = array();
			$functions = $this->acls_model->get_functions_enabled();
			$functions = $this->_get_functions($functions);
			
			foreach ($functions as $function)
			{
				// Push function into table for look up later
				$this->functionstable[$function['functionid']] = $function;
				// But also push into a table sorting functions by controller for use on the web page
				$controller = $function['controller'];
				if (!isset($data['functions'][$controller]))
				{
					$data['functions'][$controller] = array();
				}
				// Go through each role, inserting either a 0 or 1 privelege for that function
				$function['priveleges'] = array();
				foreach ($data['roles'] as $role)
				{
					if (isset($privelege_table[$function['functionid']][$role['roleid']]))
					{
						array_push($function['priveleges'], 1);
					}
					else
					{
						array_push($function['priveleges'], 0);
					}
				}
				array_push($data['functions'][$controller], $function);
			}
			
			// Get all disabled functions and sort through them
			$data['availablefunctions'] = array();
			$functions = $this->acls_model->get_functions_available();
			foreach ($functions->result_array() as $function)
			{
				$controller = $function['controller'];
				if (!isset($data['availablefunctions'][$controller]))
				{
					$data['availablefunctions'][$controller] = array();
				}
				array_push($data['availablefunctions'][$controller], $function);
			}
			
			// Process actions
			if ($this->uri->segment(2) == "addrole")
			{
				$this->form_validation->set_rules('role', 'role name', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[50]|callback_rolename_check');
				$this->focus = "addrole";
				if ($this->form_validation->run())
				{
					$this->acls_model->add_role(set_value('role'));
					redirect('manage_security');
				}		
			}
			else if ($this->uri->segment(2) == "deleterole")
			{
				$userlist = $this->acls_model->get_userlist_by_role($this->uri->segment(3));
				$this->acls_model->delete_role($this->uri->segment(3));
				
				// Any user losing a special role site-wide will get back their old member role
				$roleid = $this->acls_model->get_roleid('member');
				foreach ($userlist->result_array() as $user)
				{
					if ($user['attachedto'] == "site")
					{
						$this->acls_model->add($user['userid'], $roleid, 'site');
					}
				}
				
				redirect('manage_security');
			}
			else if ($this->uri->segment(2) == "enablefunction")
			{
				$this->_enable_function($this->uri->segment(3));
				redirect('manage_security');
			}
			else if ($this->uri->segment(2) == "disablefunction")
			{
				$this->_disable_function($this->uri->segment(3));
				redirect('manage_security');
			}
			else if ($this->uri->segment(2) == "setprivelege")
			{
				if (isset($privelege_table[$this->uri->segment(3)][$this->uri->segment(4)]))
				{
					$this->_unset_privelege($this->uri->segment(3), $this->uri->segment(4));
				}
				else
				{
					$this->_set_privelege($this->uri->segment(3), $this->uri->segment(4));
				}
				redirect('manage_security');
			}
			$this->_initialize_page('manage_security', 'Manage acls', $data);
		}
	}
	
	function rolename_check($str)
	{	
		$roleid = $this->acls_model->get_roleid($str);
		if ($roleid != NULL)
		{
			$this->form_validation->set_message('rolename_check', "The role name $str exists, please try a new role name.");	
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function _get_functions($functions, $level = -1)
	{
		$level++;
		$results = array();
		foreach ($functions->result_array() as $function)
		{
			$function['level'] = $level;
			array_push($results, $function);
			$results = array_merge($results, $this->_get_functions($this->acls_model->get_functions_enabled_children($function['functionid']), $level));
		}
		
		return $results;
	}
	
	function _disable_function($functionid)
	{
		$this->acls_model->set_function_availability($functionid, 0);
		$children = $this->acls_model->get_functions_enabled_children($functionid);
		foreach ($children->result_array() as $child)
		{
			$this->_disable_function($child['functionid']);
		}
	}
	
	function _enable_function($functionid)
	{
		$this->acls_model->set_function_availability($functionid, 1);
		$function = $this->acls_model->get_function($functionid);
		if (isset($function['functionof']) && $function['functionof'] != NULL)
		{
			$this->_enable_function($function['functionof']);
		}
	}
	
	function _set_privelege($functionid, $roleid)
	{
		// Ensure that functions that don't allow guests or owners can't have set a privelege for them
		if (($this->functionstable[$functionid]['guestpossible'] && $this->rolestable[$roleid]['role'] == "guest") || 
			($this->functionstable[$functionid]['ownerpossible'] && $this->rolestable[$roleid]['role'] == "owner") || 
			($this->rolestable[$roleid]['role'] != "guest" && $this->rolestable[$roleid]['role'] != "owner"))
		{
			// Only set role if role isn't already set
			if (!$this->acls_model->get_privelege($roleid, $this->functionstable[$functionid]['controller'], $this->functionstable[$functionid]['function']))
			{
				$this->acls_model->set_function_privelege($functionid, $roleid);
			}
		}
		$function = $this->acls_model->get_function($functionid);
		if (isset($function['functionof']) && $function['functionof'] != NULL)
		{
			$this->_set_privelege($function['functionof'], $roleid);
		}	
	}
	
	function _unset_privelege($functionid, $roleid)
	{
		// Ensure that functions that don't allow guests or owners can't have set a privelege for them
		if (($this->functionstable[$functionid]['guestpossible'] && $this->rolestable[$roleid]['role'] == "guest") || 
			($this->functionstable[$functionid]['ownerpossible'] && $this->rolestable[$roleid]['role'] == "owner") || 
			($this->rolestable[$roleid]['role'] != "guest" && $this->rolestable[$roleid]['role'] != "owner"))
		{
			$this->acls_model->remove_function_privelege($functionid, $roleid);
		}
		$children = $this->acls_model->get_functions_enabled_children($functionid);
		foreach ($children->result_array() as $child)
		{
			$this->_unset_privelege($child['functionid'], $roleid);
		}
	}
}
?> 