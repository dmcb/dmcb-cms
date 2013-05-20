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
class Acls_model extends CI_Model {

    function Acls_model()
    {
        parent::__construct();
    }

	function add($userid, $roleid, $controller, $attachedid = NULL)
	{
		if ($attachedid == NULL)
		{
			$attachedid = 0;
		}
		$this->db->query("INSERT INTO acls (userid, roleid, controller, attachedid) VALUES (".$this->db->escape($userid).", ".$this->db->escape($roleid).", ".$this->db->escape($controller).", ".$this->db->escape($attachedid).")");
	}

	function add_role($role)
	{
		$this->db->query("INSERT INTO acls_roles (role, internal, custom) VALUES (".$this->db->escape($role).", '0', '1')");
	}

	function delete($userid = NULL, $controller = NULL, $attachedid = NULL)
	{
		if ($userid != NULL || ($controller != NULL && $attachedid != NULL))
		{
			$useridsql = "";
			if ($userid != NULL)
			{
				$useridsql = "userid = ".$this->db->escape($userid);
			}

			$controllersql = "";
			if ($controller != NULL)
			{
				if ($useridsql != "")
				{
					$controllersql = " AND ";
				}
				$controllersql .= "controller = ".$this->db->escape($controller);
			}

			$attachedidsql = "";
			if ($attachedid != NULL)
			{
				if ($useridsql != "" || $controllersql != "")
				{
					$attachedidsql = " AND ";
				}
				$attachedidsql .= "attachedid = ".$this->db->escape($attachedid);
			}
			$this->db->query("DELETE FROM acls WHERE $useridsql $controllersql $attachedidsql");
		}
	}

	function delete_role($roleid)
	{
		$this->db->query("DELETE FROM pages_protection WHERE roleid = ".$this->db->escape($roleid));
		$this->db->query("DELETE FROM acls_roles_privileges WHERE roleid = ANY (SELECT roleid FROM acls_roles WHERE roleid = ".$this->db->escape($roleid)." and custom = '1')");
		$this->db->query("DELETE FROM acls WHERE roleid = ANY (SELECT roleid FROM acls_roles WHERE roleid = ".$this->db->escape($roleid)." and custom = '1')");
		$this->db->query("DELETE FROM acls_roles WHERE roleid = ".$this->db->escape($roleid)." AND custom = '1'");
	}

	function function_enabled($controller, $function)
	{
		$query = $this->db->query("SELECT enabled FROM acls_functions WHERE controller = ".$this->db->escape($controller)." AND function = ".$this->db->escape($function));
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		else
		{
			$row = $query->row_array();
			if ($row['enabled'])
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	function get($userid, $controller, $attachedid = NULL)
	{
		$attachedsql = "";
		if ($attachedid != NULL)
		{
			$attachedsql = " AND attachedid = ".$this->db->escape($attachedid);
		}
		$query = $this->db->query("SELECT roleid FROM acls WHERE userid = ".$this->db->escape($userid)." AND controller = ".$this->db->escape($controller)." $attachedsql");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['roleid'];
		}
	}

	function get_all($userid, $controller)
	{
		return $this->db->query("SELECT roleid, attachedid FROM acls WHERE userid = ".$this->db->escape($userid)." AND controller = ".$this->db->escape($controller));
	}

	function get_function($functionid)
	{
		$query = $this->db->query("SELECT * FROM acls_functions WHERE functionid = ".$this->db->escape($functionid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array();
		}
	}

	function get_function_by_function($controller, $function)
	{
		$query = $this->db->query("SELECT * FROM acls_functions WHERE controller = ".$this->db->escape($controller)." AND function = ".$this->db->escape($function));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array();
		}
	}

	function get_functions_available()
	{
		return $this->db->query("SELECT * FROM acls_functions WHERE enabled = '0' ORDER BY controller, name ASC");
	}

	function get_functions_enabled()
	{
		return $this->db->query("SELECT * FROM acls_functions WHERE enabled = '1' AND functionof IS NULL ORDER BY controller, name ASC");
	}

	function get_functions_enabled_children($functionid)
	{
		return $this->db->query("SELECT * FROM acls_functions WHERE enabled = '1' AND functionof = ".$this->db->escape($functionid)." ORDER BY controller, name ASC");
	}

	function get_privilege($roleid, $controller, $function)
	{
		$query = $this->db->query("SELECT count(*) as total FROM acls_functions, acls_roles_privileges WHERE acls_functions.controller = ".$this->db->escape($controller)." AND acls_functions.function = ".$this->db->escape($function)." AND acls_functions.enabled = '1' AND acls_functions.functionid = acls_roles_privileges.functionid AND acls_roles_privileges.roleid = ".$this->db->escape($roleid));
		$row = $query->row_array();
		if ($row['total'])
		{
			return TRUE;
		}
		return FALSE;
	}

	function get_privileged($controller, $function)
	{
		return $this->db->query("SELECT acls_roles_privileges.roleid FROM acls_roles_privileges, acls_functions WHERE acls_functions.controller = ".$this->db->escape($controller)." AND acls_functions.function = ".$this->db->escape($function)." AND acls_functions.enabled = '1' AND acls_functions.functionid = acls_roles_privileges.functionid");
	}

	function get_privileges_all()
	{
		return $this->db->query("SELECT * FROM acls_roles_privileges ORDER BY functionid DESC");
	}

	function get_role_name($roleid)
	{
		$query = $this->db->query("SELECT role FROM acls_roles WHERE roleid = ".$this->db->escape($roleid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['role'];
		}
	}

	function get_roleid($role)
	{
		$query = $this->db->query("SELECT roleid FROM acls_roles WHERE role = ".$this->db->escape($role));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['roleid'];
		}
	}

	function get_roles()
	{
		return $this->db->query("SELECT * FROM acls_roles WHERE internal = '0' ORDER BY roleid ASC");
	}

	function get_roles_all()
	{
		return $this->db->query("SELECT * FROM acls_roles ORDER BY roleid ASC");
	}

	function get_userlist_by_role($roleid, $controller = 'site', $attachedid = NULL)
	{
		$attachedidsql = "";
		if ($attachedid != NULL)
		{
			$attachedidsql = " AND acls.attachedid = ".$this->db->escape($attachedid);
		}
		return $this->db->query("SELECT users.userid, acls.controller, acls.attachedid FROM acls, users WHERE acls.roleid=".$this->db->escape($roleid)." AND acls.controller = ".$this->db->escape($controller)." $attachedidsql AND acls.userid = users.userid ORDER BY users.displayname ASC");
	}

	function get_userlist_with_date($num, $offset)
	{
		return $this->db->query("SELECT users.userid, users.lastsignon, acls_roles.roleid FROM acls, acls_roles, users WHERE acls.controller = 'site' AND acls.userid = users.userid AND acls.roleid = acls_roles.roleid ORDER BY users.lastsignon DESC LIMIT $offset, $num");
	}

	function get_userlist_with_name($num, $offset)
	{
		return $this->db->query("SELECT users.userid, users.displayname, acls_roles.roleid FROM acls, acls_roles, users WHERE acls.controller = 'site' AND acls.userid = users.userid AND acls.roleid = acls_roles.roleid ORDER BY users.displayname ASC LIMIT $offset, $num");
	}

	function get_userlist_with_registration($num, $offset)
	{
		return $this->db->query("SELECT users.userid, users.registered, acls_roles.roleid FROM acls, acls_roles, users WHERE acls.controller = 'site' AND acls.userid = users.userid AND acls.roleid = acls_roles.roleid ORDER BY users.registered DESC LIMIT $offset, $num");
	}

	function get_userlist_with_role($num, $offset)
	{
		return $this->db->query("SELECT users.userid, acls_roles.role, acls_roles.roleid FROM acls, acls_roles, users WHERE acls.controller = 'site' AND acls.userid = users.userid AND acls.roleid = acls_roles.roleid ORDER BY acls_roles.roleid ASC LIMIT $offset, $num");
	}

	function get_userlist_with_status($num, $offset)
	{
		return $this->db->query("SELECT users.userid, users.statusid, acls_roles.roleid FROM acls, acls_roles, users WHERE acls.controller = 'site' AND acls.userid = users.userid AND acls.roleid = acls_roles.roleid ORDER BY users.statusid DESC LIMIT $offset, $num");
	}

	function remove_function_privilege($functionid, $roleid)
	{
		$this->db->query("DELETE FROM acls_roles_privileges WHERE functionid = ".$this->db->escape($functionid)." AND roleid = ".$this->db->escape($roleid));
	}

	function remove_function_privileges($functionid)
	{
		$this->db->query("DELETE FROM acls_roles_privileges WHERE functionid = ".$this->db->escape($functionid));
	}

	function set_function_availability($functionid, $available)
	{
		$this->db->query("UPDATE acls_functions SET enabled = ".$this->db->escape($available)." WHERE functionid = ".$this->db->escape($functionid));
	}

	function set_function_privilege($functionid, $roleid)
	{
		$this->db->query("INSERT IGNORE INTO acls_roles_privileges (functionid, roleid) VALUES (".$this->db->escape($functionid).", ".$this->db->escape($roleid).")");
	}
}