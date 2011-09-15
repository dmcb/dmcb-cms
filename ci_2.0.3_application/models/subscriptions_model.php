<?php

class Subscriptions_model extends CI_Model {

    function Subscriptions_model()
    {
        parent::__construct();
    }
	
	function check($userid)
	{
		$query = $this->db->query("SELECT date FROM users_subscriptions WHERE userid=".$this->db->escape($userid));
		if ($query->num_rows() > 0)
		{
			$date = $query->row();
			if (strtotime($date->date) >= strtotime(date('Y-m-d')))
				return TRUE;
			else
				return FALSE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function check_view($ip, $postid)
	{
		$touched = date('Y-m-d H:i');
		$days = 1;
		if ($this->config->item('dmcb_post_subscriptions_free_views_range') == "daily")
		{
			$days = 1;
		}
		else if ($this->config->item('dmcb_post_subscriptions_free_views_range') == "weekly")
		{
			$days = 7;
		}
		
		$this->db->query("DELETE from users_subscriptions_views WHERE ip = ".$this->db->escape($ip)." AND DATE_SUB(".$this->db->escape($touched).",INTERVAL $days DAY) >= touched");
		$query = $this->db->query("SELECT count(*) FROM users_subscriptions_views WHERE ip = ".$this->db->escape($ip)." AND postid = ".$this->db->escape($postid)." AND DATE_SUB(".$this->db->escape($touched).",INTERVAL $days DAY) < touched ");
		$row = $query->row_array();
		if ($row['count(*)'] == 1)
		{
			return TRUE;
		}
		else 
		{
			$query = $this->db->query("SELECT count(*) FROM users_subscriptions_views WHERE ip = ".$this->db->escape($ip)." AND DATE_SUB(".$this->db->escape($touched).",INTERVAL $days DAY) < touched");
			$row = $query->row_array();
			$views =  $row['count(*)'];
			if ($views >= $this->config->item('dmcb_post_subscriptions_free_views'))
			{
				return FALSE;
			}
			else
			{
				$this->db->query("INSERT into users_subscriptions_views (ip, postid, touched) VALUES (".$this->db->escape($ip).",".$this->db->escape($postid).",".$this->db->escape($touched).")");
				return TRUE;
			}
		}
	}
	
	function delete($userid)
	{
		$this->db->query("DELETE FROM users_subscriptions WHERE userid=".$this->db->escape($userid));
	}

	function get($userid)
	{
		$query = $this->db->query("SELECT users_subscriptions.userid, users_subscriptions.typeid, users_subscriptions.date, users_subscriptions_types.type FROM users_subscriptions, users_subscriptions_types WHERE users_subscriptions.userid = ".$this->db->escape($userid)." AND users_subscriptions.typeid = users_subscriptions_types.typeid");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_list_by_type($typeid, $num = NULL, $offset = NULL)
	{
		$limit_sql = "";
		if ($num != NULL)
		{
			$limit_sql = "LIMIT $offset, $num";
		}
		return $this->db->query("SELECT users.userid, users_subscriptions.date FROM users, users_subscriptions WHERE users.userid = users_subscriptions.userid AND users_subscriptions.typeid = ".$this->db->escape($typeid)." AND NOW() <= users_subscriptions.date ORDER BY users_subscriptions.date ASC $limit_sql");
	}
	
	function get_list_by_type_count($typeid)
	{
		$query = $this->db->query("SELECT COUNT(users.userid) as total FROM users, users_subscriptions WHERE users.userid = users_subscriptions.userid AND users_subscriptions.typeid = ".$this->db->escape($typeid)." AND NOW() <= users_subscriptions.date");
		$row = $query->row_array();
		return $row['total'];
	}
	
	function get_list_by_type_expired($typeid, $num = NULL, $offset = NULL)
	{
		$limit_sql = "";
		if ($num != NULL)
		{
			$limit_sql = "LIMIT $offset, $num";
		}
		return $this->db->query("SELECT users.userid, users_subscriptions.date FROM users, users_subscriptions WHERE users.userid = users_subscriptions.userid AND users_subscriptions.typeid = ".$this->db->escape($typeid)." AND NOW() > users_subscriptions.date ORDER BY users_subscriptions.date DESC $limit_sql");
	}
	
	function get_list_by_type_expired_count($typeid)
	{
		$query = $this->db->query("SELECT COUNT(users.userid) as total FROM users, users_subscriptions WHERE users.userid = users_subscriptions.userid AND users_subscriptions.typeid = ".$this->db->escape($typeid)." AND NOW() > users_subscriptions.date");
		$row = $query->row_array();
		return $row['total'];
	}
	
	function get_list_by_none($num = NULL, $offset = NULL)
	{
		$limit_sql = "";
		if ($num != NULL)
		{
			$limit_sql = "LIMIT $offset, $num";
		}
		return $this->db->query("SELECT userid FROM users WHERE userid NOT IN (SELECT users_subscriptions.userid FROM users_subscriptions) $limit_sql");
	}
	
	function get_list_by_none_count($typeid)
	{
		$query = $this->db->query("SELECT COUNT(users.userid) as total FROM users WHERE userid NOT IN (SELECT users_subscriptions.userid FROM users_subscriptions)");
		$row = $query->row_array();
		return $row['total'];
	}
	
	function get_type($typeid)
	{
		$query = $this->db->query("SELECT * FROM users_subscriptions_types WHERE typeid=".$this->db->escape($typeid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_types()
	{
		return $this->db->query("SELECT * FROM users_subscriptions_types ORDER BY typeid ASC");
	}
	
	function get_types_by_price()
	{
		return $this->db->query("SELECT * FROM users_subscriptions_types ORDER BY price DESC");
	}
	
	function get_type_free()
	{
		$query = $this->db->query("SELECT typeid FROM users_subscriptions_types WHERE price = 0 LIMIT 1");
		if ($query->num_rows() == 0)
			return NULL;
		else
		{
			$row = $query->row_array(); 
			return $row['typeid'];		
		}
	}
	
	function set($userid, $date, $typeid)
	{
		$query = $this->db->query("SELECT * FROM users_subscriptions WHERE userid=".$this->db->escape($userid));
		if ($query->num_rows() > 0)
		{
			$this->db->query("UPDATE users_subscriptions SET date = ".$this->db->escape($date).", typeid = ".$this->db->escape($typeid)." WHERE userid = ".$this->db->escape($userid));
		}
		else
		{
			$this->db->query("INSERT into users_subscriptions (userid, date, typeid) VALUES (".$this->db->escape($userid).",".$this->db->escape($date).",".$this->db->escape($typeid).")");
		}
	}
}
?>