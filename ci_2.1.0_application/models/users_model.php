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
class Users_model extends CI_Model {

    function Users_model()
    {
        parent::__construct();
    }

	function add($email, $displayname, $urlname, $password, $code, $mailinglist_code)
	{
		$this->db->query("INSERT into users (email, displayname, urlname, password, code, registered, datemodified, mailinglist, mailinglist_code, getmessages) VALUES (".$this->db->escape($email).",".$this->db->escape($displayname).",".$this->db->escape($urlname).",".$this->db->escape($password).",".$this->db->escape($code).", NOW(), NOW(), 1, ".$this->db->escape($mailinglist_code).", 1)");
		return $this->db->insert_id();
	}

	function add_blocked_user($userid, $blockedid)
	{
		$this->db->query("INSERT into users_blocked (userid, blockedid) VALUES (".$this->db->escape($userid).",".$this->db->escape($blockedid).")");
	}

	function add_rss($userid, $rssfeed)
	{
		$this->db->query("INSERT into users_blogs (userid, rssfeed) VALUES (".$this->db->escape($userid).",".$this->db->escape($rssfeed).")");
	}

	function autocomplete($value)
	{
		return $this->db->query("SELECT displayname AS result FROM users WHERE displayname REGEXP '[[:<:]]".$this->db->escape_like_str($value)."' ORDER BY displayname ASC");
	}

	function delete($userid)
	{
		$this->db->query("DELETE FROM users_blocked WHERE userid=".$this->db->escape($userid)." OR blockedid = ".$this->db->escape($userid));
		$this->db->query("DELETE FROM users WHERE userid=".$this->db->escape($userid));
	}

	function get($userid)
	{
		$query = $this->db->query("SELECT * FROM users WHERE userid=".$this->db->escape($userid));
		return $query->row_array();
	}

	function get_all()
	{
		return $this->db->query("SELECT userid FROM users");
	}

	function get_by_displayname($displayname)
	{
		$query = $this->db->query("SELECT userid FROM users WHERE displayname=".$this->db->escape($displayname));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['userid'];
		}
	}

	function get_by_email($email)
	{
		$query = $this->db->query("SELECT userid FROM users WHERE email=".$this->db->escape($email));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['userid'];
		}
	}

	function get_by_facebook_uid($facebook_uid)
	{
		$query = $this->db->query("SELECT userid FROM users WHERE facebook_uid=".$this->db->escape($facebook_uid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['userid'];
		}
	}

	function get_by_sessionid($sessionid)
	{
		$query = $this->db->query("SELECT user_data FROM ci_sessions WHERE session_id=".$this->db->escape($sessionid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			$session = unserialize($row['user_data']);
			if (isset($session['userid']))
			{
				return $session['userid'];
			}
			else
			{
				return NULL;
			}
		}
	}

	function get_by_twitter($twitter)
	{
		$query = $this->db->query("SELECT userid FROM users WHERE twitter=".$this->db->escape($twitter));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['userid'];
		}
	}

	function get_by_urlname($urlname)
	{
		$query = $this->db->query("SELECT userid FROM users WHERE urlname=".$this->db->escape($urlname));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['userid'];
		}
	}

	function get_list_purgatory()
	{
		return $this->db->query("SELECT userid FROM users WHERE code != ''");
	}

	function get_mailing_list()
	{
		return $this->db->query("SELECT userid FROM users WHERE mailinglist='1' ORDER BY displayname ASC");
	}

	function get_published_all()
	{
		$banned = $this->get_status_lowest();
		return $this->db->query("SELECT userid FROM users WHERE profile IS NOT NULL and statusid != ".$this->db->escape($banned));
	}

	function get_published_authors($num, $offset, $order)
	{
		$sql_order = "posts.date DESC";
		if ($order = "alphabetical")
		{
			$sql_order = "users.displayname ASC";
		}
		return $this->db->query("SELECT users.userid AS userid FROM posts,users WHERE users.userid = posts.userid AND posts.featured != -1 AND posts.published = 1 AND posts.userid IS NOT NULL GROUP BY users.userid ORDER BY $sql_order LIMIT $offset, $num");
	}

	function get_published_authors_count()
	{
		$query = $this->db->query("SELECT count(users.userid) as total FROM posts,users WHERE users.userid = posts.userid AND posts.featured != -1 AND posts.published = 1 AND posts.userid IS NOT NULL");
		$row = $query->row_array();
		return $row['total'];
	}

	function get_published_authors_new($num, $offset)
	{
		return $this->db->query("SELECT users.userid AS userid, postid, posts.urlname as posturlname, date, title FROM posts,users WHERE users.userid = posts.userid AND posts.featured != -1 AND posts.published = 1 AND posts.userid IS NOT NULL GROUP BY users.userid ORDER BY posts.date DESC LIMIT $offset, $num");
	}

	function get_published_authors_new_count()
	{
		$query = $this->db->query("SELECT count(users.userid) as total FROM posts,users WHERE users.userid = posts.userid AND posts.featured != -1 AND posts.published = 1 AND posts.userid IS NOT NULL");
		$row = $query->row_array();
		return $row['total'];
	}

	function get_statuses()
	{
		return $this->db->query("SELECT * FROM users_status ORDER BY statusid DESC");
	}

	function get_status_lowest()
	{
		$query = $this->db->query("SELECT statusid FROM users_status ORDER BY statusid ASC LIMIT 1");
		$row = $query->row_array();
		return $row['statusid'];
	}

	function get_status_highest()
	{
		$query = $this->db->query("SELECT statusid FROM users_status ORDER BY statusid DESC LIMIT 1");
		$row = $query->row_array();
		return $row['statusid'];
	}

	function get_status_name($statusid)
	{
		$query = $this->db->query("SELECT status FROM users_status WHERE statusid=".$this->db->escape($statusid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['status'];
		}
	}

	function get_user_count()
	{
		$query = $this->db->query("SELECT count(users.userid) as total FROM users");
		$row = $query->row_array();
		return $row['total'];
	}

	function get_user_blocked_users($userid)
	{
		return $this->db->query("SELECT * FROM users_blocked WHERE userid = ".$this->db->escape($userid));
	}

	function get_user_rss($userid)
	{
		return $this->db->query("SELECT * FROM users_blogs WHERE userid = ".$this->db->escape($userid));
	}

	function remove_blocked_users($userid)
	{
		$this->db->query("DELETE FROM users_blocked WHERE userid = ".$this->db->escape($userid));
	}

	function remove_rss($userid)
	{
		$this->db->query("DELETE FROM users_blogs WHERE userid = ".$this->db->escape($userid));
	}

	function search($searchby, $num, $offset)
	{
		$searchby =  html_entity_decode($searchby, ENT_QUOTES);
		return $this->db->query("SELECT DISTINCT userid FROM users WHERE displayname LIKE '%".$this->db->escape_like_str($searchby)."%' ORDER BY displayname ASC LIMIT $offset, $num");
	}

	function search_count($searchby)
	{
		$query = $this->db->query("SELECT DISTINCT count(userid) AS total FROM users WHERE displayname LIKE ".$this->db->escape('%'.$searchby.'%'));
		$row = $query->row_array();
		if (isset($row['total']))
		{
			return $row['total'];
		}
		else
		{
			return 0;
		}
	}

	function update($userid, $user)
	{
		if ($user['profile'] == "")
		{
			$user['profile'] = NULL;
		}
		$this->db->query("UPDATE users SET profile = ".$this->db->escape($user['profile']).",
			displayname = ".$this->db->escape($user['displayname']).",
			urlname = ".$this->db->escape($user['urlname']).",
			email = ".$this->db->escape($user['email']).",
			password = ".$this->db->escape($user['password']).",
			code = ".$this->db->escape($user['code']).",
			mailinglist = ".$this->db->escape($user['mailinglist']).",
			mailinglist_code = ".$this->db->escape($user['mailinglist_code']).",
			getmessages = ".$this->db->escape($user['getmessages']).",
			profilepicture = ".$this->db->escape($user['profilepicture']).",
			statusid = ".$this->db->escape($user['statusid']).",
			facebook_uid = ".$this->db->escape($user['facebook_uid']).",
			twitter = ".$this->db->escape($user['twitter']).",
			google = ".$this->db->escape($user['google']).",
			lastsignon = ".$this->db->escape($user['lastsignon']).",
			datemodified = ".$this->db->escape($user['datemodified'])."
			WHERE userid=".$this->db->escape($userid));
	}
}