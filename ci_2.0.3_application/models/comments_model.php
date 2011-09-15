<?php

class Comments_model extends CI_Model {

    function Comments_model()
    {
        parent::__construct();
    }
	
	function add($postid, $userid, $content, $featured, $reviewed)
	{
		$time = date('Y-m-d H:i:s');
		$this->db->query("INSERT into posts_comments (postid, userid, content, featured, reviewed, date, new) VALUES (".$this->db->escape($postid).",".$this->db->escape($userid).",".$this->db->escape($content).",".$this->db->escape($featured).",".$this->db->escape($reviewed).",".$this->db->escape($time).",'1')");
	}
	
	function add_anonymous($postid, $displayname, $email, $ip, $content, $featured, $reviewed)
	{
		$time = date('Y-m-d H:i:s');
		$this->db->query("INSERT into posts_comments (postid, displayname, email, ip, content, featured, reviewed, date, new) VALUES (".$this->db->escape($postid).",".$this->db->escape($displayname).",".$this->db->escape($email).",".$this->db->escape($ip).",".$this->db->escape($content).",".$this->db->escape($featured).",".$this->db->escape($reviewed).",".$this->db->escape($time).",'1')");
	}
	
	function check_banned($ip)
	{
		$days = $this->config->item('dmcb_anonymous_comment_ban_length');
		$query = $this->db->query("SELECT ip FROM posts_comments_banned WHERE ip=".$this->db->escape($ip)." AND DATE_SUB(CURDATE(),INTERVAL $days DAY) <= date");
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function convert($userid, $email)
	{
		$this->db->query("UPDATE posts_comments SET userid = ".$this->db->escape($userid).", displayname = NULL, email = NULL, ip = NULL WHERE email = ".$this->db->escape($email)." AND email IS NOT NULL");
	}
	
	function delete($commentid)
	{
		$this->db->query("DELETE FROM posts_comments WHERE commentid=".$this->db->escape($commentid));
	}

	function delete_by_post($postid)
	{
		$this->db->query("DELETE FROM posts_comments WHERE postid=".$this->db->escape($postid));
	}
	
	function delete_by_ip($ip)
	{
		$this->db->query("DELETE FROM posts_comments WHERE ip=".$this->db->escape($ip)." AND featured = '-1' AND userid IS NULL");
	}
	
	function get($commentid)
	{
		$query = $this->db->query("SELECT * FROM posts_comments WHERE commentid = ".$this->db->escape($commentid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_list_anonymous()
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE featured = '-1' AND reviewed = '0' AND userid IS NULL ORDER BY date ASC");
	}
	
	function get_list_heldback()
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE featured = '-1' AND reviewed = '0' AND userid IS NOT NULL ORDER BY date ASC");
	}
	
	function get_list_new()
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE featured != '-1' AND new = '1' AND userid IS NOT NULL ORDER BY date ASC");
	}
	
	function get_list_reported()
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE featured != '-1' AND new != '1' AND reviewed = '0' ORDER BY date ASC");
	}
	
	function get_published($num, $offset, $pages, $posts, $sort = NULL)
	{
		if (!isset($sort))
		{
			$sort = "ASC";
		}
	
		$sql_from = "posts_comments, posts";
		$sql_page = "AND ";
		if ($posts != NULL)
		{
			$sql_page .= "posts_comments.postid = posts.postid AND posts.published = '1' AND (";
			$postnames = explode(";",$posts);
			for ($i=0; $i<sizeof($postnames); $i++)
			{
				$sql_page .= "posts.urlname = ".$this->db->escape($postnames[$i]);
				if ($i != sizeof($postnames)-1)
				{
					$sql_page .= " OR ";
				}
			}		
			$sql_page .= ")";
		}
		else
		{
			if ($pages == "all")
			{
				$sql_page .= "posts.pageid IS NOT NULL";
			}
			else if ($pages == "nopage")
			{
				$sql_page .= "posts.pageid IS NULL";
			}
			else
			{
				$sql_from .= ", pages";
				$sql_page .= "posts.pageid = pages.pageid AND pages.published = '1' AND (";
				$urlnames = explode(";",$pages);
				for ($i=0; $i<sizeof($urlnames); $i++)
				{
					$sql_page .= "pages.urlname = ".$this->db->escape($urlnames[$i]);
					if ($i != sizeof($urlnames)-1)
					{
						$sql_page .= " OR ";
					}
				}
				$sql_page .= ")";
			}
		}
		return $this->db->query("SELECT posts_comments.* FROM $sql_from WHERE posts_comments.featured != '-1' $sql_page GROUP BY posts_comments.commentid ORDER BY posts_comments.date $sort LIMIT $offset, $num");
	}
	
	function get_published_count($pages, $posts)
	{
		$sql_from = "posts_comments, posts";
		$sql_page = "AND ";
		if ($posts != NULL)
		{
			$sql_page .= "posts_comments.postid = posts.postid AND posts.published = '1' AND (";
			$postnames = explode(";",$posts);
			for ($i=0; $i<sizeof($postnames); $i++)
			{
				$sql_page .= "posts.urlname = ".$this->db->escape($postnames[$i]);
				if ($i != sizeof($postnames)-1)
				{
					$sql_page .= " OR ";
				}
			}		
			$sql_page .= ")";
		}
		else
		{
			if ($pages == "all")
			{
				$sql_page .= "posts.pageid IS NOT NULL";
			}
			else if ($pages == "nopage")
			{
				$sql_page .= "posts.pageid IS NULL";
			}
			else
			{
				$sql_from .= ", pages";
				$sql_page .= "posts.pageid = pages.pageid AND pages.published = '1' AND (";
				$urlnames = explode(";",$pages);
				for ($i=0; $i<sizeof($urlnames); $i++)
				{
					$sql_page .= "pages.urlname = ".$this->db->escape($urlnames[$i]);
					if ($i != sizeof($urlnames)-1)
					{
						$sql_page .= " OR ";
					}
				}
				$sql_page .= ")";
			}
		}
		$query = $this->db->query("SELECT count(*) as total FROM $sql_from WHERE posts_comments.featured != '-1' $sql_page");
		$row = $query->row_array();
		return $row['total'];
	}

	function get_post_comments_all($postid)
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE postid = ".$this->db->escape($postid)." AND featured != '-1' ORDER BY date ASC");
	}
	
	function get_post_comments_count($postid)
	{
		$query = $this->db->query("SELECT count(*) as total FROM posts_comments WHERE postid = ".$this->db->escape($postid)." AND featured != '-1'");
		$row = $query->row_array();
		return $row['total'];
	}
	
	function get_user_comments_count($userid)
	{
		$query = $this->db->query("SELECT count(*) as total FROM posts_comments WHERE userid = ".$this->db->escape($userid)." AND featured != '-1'");
		$row = $query->row_array();
		return $row['total'];
	}
	
	function get_user_heldback($userid)
	{
		return $this->db->query("SELECT * FROM posts_comments WHERE userid = ".$this->db->escape($userid)." AND featured = '-1' ORDER BY date DESC");
	}	
	
	function set_ban($ip)
	{
		$time = date('Y-m-d H:i:s');
		$query = $this->db->query("SELECT ip FROM posts_comments_banned WHERE ip=".$this->db->escape($ip));
		if ($query->num_rows() > 0)
		{
			$this->db->query("UPDATE posts_comments_banned SET date = ".$this->db->escape($time)." WHERE ip = ".$this->db->escape($ip));
		}
		else
		{
			$this->db->query("INSERT into posts_comments_banned (ip, date) VALUES (".$this->db->escape($ip).",".$this->db->escape($time).")");
		}
	}
	
	function set_featured($commentid, $featured)
	{
		$this->db->query("UPDATE posts_comments SET featured = ".$this->db->escape($featured).", reviewed='1', new = '0' WHERE commentid = ".$this->db->escape($commentid));
	}
		
	function set_reported($commentid)
	{
		$this->db->query("UPDATE posts_comments SET reviewed='0', new = '0' WHERE commentid = ".$this->db->escape($commentid));
	}
	
	function set_reviewed($commentid)
	{
		$this->db->query("UPDATE posts_comments SET reviewed='1', new = '0' WHERE commentid = ".$this->db->escape($commentid));
	}
	
	function update($commentid, $content)
	{
		$this->db->query("UPDATE posts_comments SET content = ".$this->db->escape($content).", reviewed = '0', new = '0' WHERE commentid = ".$this->db->escape($commentid));
	}
}
?>