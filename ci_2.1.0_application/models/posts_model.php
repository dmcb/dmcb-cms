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
class Posts_model extends CI_Model {

    function Posts_model()
    {
        parent::__construct();
    }

	function add($pageid = 'NULL', $userid = 'NULL', $title, $urlname)
	{
		if ($pageid != 'NULL')
		{
			$pageid = $this->db->escape($pageid);
		}
		if ($userid != 'NULL')
		{
			$userid = $this->db->escape($userid);
		}
		$this->db->query("INSERT into posts (pageid, userid, title, urlname, date, datemodified) VALUES (".$pageid.",".$userid.",".$this->db->escape($title).",".$this->db->escape($urlname).", NOW(), NOW())");
		return $this->db->insert_id();
	}

	function add_contributor($postid, $userid)
	{
		$this->db->query("INSERT INTO posts_contributors (postid, userid) VALUES (".$this->db->escape($postid).", ".$this->db->escape($userid).")");
	}

	function add_theme_file($postid, $file, $type)
	{
		$this->db->query("INSERT into posts_theme_files (postid, file, type) VALUES (".$this->db->escape($postid).",".$this->db->escape($file).",".$this->db->escape($type).")");
	}

	function autocomplete($value)
	{
		return $this->db->query("SELECT urlname AS result FROM posts WHERE urlname REGEXP '[[:<:]]/?".$this->db->escape_like_str($value)."' ORDER BY urlname ASC");
	}

	function delete($postid)
	{
		$this->db->query("DELETE FROM posts_references WHERE postid=".$this->db->escape($postid));
		$this->db->query("DELETE FROM posts WHERE postid=".$this->db->escape($postid));
	}

	function get($postid)
	{
		$query = $this->db->query("SELECT * FROM posts WHERE postid=".$this->db->escape($postid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array();
		}
	}

	function get_by_urlname($urlname)
	{
		$query = $this->db->query("SELECT postid FROM posts WHERE urlname = ".$this->db->escape($urlname));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['postid'];
		}
	}

	function get_list_heldback()
	{
		return $this->db->query("SELECT postid FROM posts WHERE reviewed = '0' AND featured = '-1' AND published = '1'");
	}

	function get_list_review()
	{
		return $this->db->query("SELECT postid FROM posts WHERE reviewed = '0' AND featured = '0' AND published = '1' AND pageid IS NULL");
	}

	function get_page_drafts($pageid)
	{
		return $this->db->query("SELECT postid FROM posts WHERE pageid = ".$this->db->escape($pageid)." AND published = '0' ORDER BY date DESC");
	}

	function get_page_posts_all($pageid)
	{
		return $this->db->query("SELECT postid FROM posts WHERE pageid = ".$this->db->escape($pageid)." ORDER BY date ASC");
	}

	function get_neighbour_post($direction, $date, $pageid, $userid)
	{
		$comparison = ">";
		$order = "ASC";
		if ($direction == "previous")
		{
			$comparison = "<";
			$order = "DESC";
		}

		$rules = "";
		if ($pageid != NULL)
		{
			$rules = "AND pageid = ".$this->db->escape($pageid);
		}
		else if ($this->config->item('dmcb_neighbour_posts') == 'user' && $userid != NULL)
		{
			$rules = "AND userid = ".$this->db->escape($userid);
		}
		else if ($this->config->item('dmcb_neighbour_posts') == 'only')
		{
			$rules = "AND featured = 1";
		}
		else if ($this->config->item('dmcb_neighbour_posts') == 'no')
		{
			$rules = "AND featured = 0";
		}

		$query = $this->db->query("SELECT postid FROM posts WHERE published = '1' AND date $comparison ".$this->db->escape($date)." $rules ORDER BY date $order LIMIT 1");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['postid'];
		}
	}

	function get_post_contributors($postid)
	{
		return $this->db->query("SELECT posts_contributors.userid FROM posts_contributors, users WHERE postid = ".$this->db->escape($postid)." AND posts_contributors.userid = users.userid ORDER BY users.displayname ASC");
	}

	function get_post_theme_files($postid)
	{
		return $this->db->query("SELECT * FROM posts_theme_files WHERE postid = ".$this->db->escape($postid));
	}

	function get_published($num, $offset, $pages, $featured, $category, $users, $sort = NULL)
	{
		if (!isset($sort))
		{
			$sort = "date DESC";
		}

		$sql_from = "posts";
		$sql_page = "AND ";
		if ($users != NULL)
		{
			// $sql_from .= ", posts_contributors, users";
			$sql_from .= ", users";
			$sql_page .= "((posts.userid = users.userid AND (";
			$usernames = explode(";",$users);
			for ($i=0; $i<sizeof($usernames); $i++)
			{
				$sql_page .= "users.urlname = ".$this->db->escape($usernames[$i]);
				if ($i != sizeof($usernames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			/*
			$sql_page .= ")) OR (posts.postid = posts_contributors.postid AND posts_contributors.userid = users.userid AND (";
			for ($i=0; $i<sizeof($usernames); $i++)
			{
				$sql_page .= "users.urlname = ".$this->db->escape($usernames[$i]);
				if ($i != sizeof($usernames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			*/
			$sql_page .= ")))";
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

		$sql_featured = "AND posts.featured != '-1'";
		if (isset($featured))
		{
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}

		$sql_category = "";
		if ($category == "0")
		{
			$sql_category = "AND NOT EXISTS (SELECT postid FROM posts_categories WHERE posts.postid = posts_categories.postid)";

		}
		else if ($category != NULL)
		{
			$sql_from .= ", posts_categories";
			$sql_category = "AND posts.postid = posts_categories.postid AND posts_categories.categoryid = ".$this->db->escape($category);
		}

		return $this->db->query("SELECT posts.postid FROM $sql_from WHERE posts.published = '1' $sql_category $sql_featured $sql_page GROUP BY posts.postid ORDER BY $sort LIMIT $offset, $num");
	}

	function get_published_all()
	{
		return $this->db->query("SELECT postid FROM posts WHERE featured != '-1' AND published = '1' ORDER BY date");
	}

	function get_published_count($pages, $featured, $category, $users)
	{
		$sql_from = "posts";
		$sql_page = "AND ";
		if ($users != NULL)
		{
			// $sql_from .= ", posts_contributors, users";
			$sql_from .= ", users";
			$sql_page .= "((posts.userid = users.userid AND (";
			$usernames = explode(";",$users);
			for ($i=0; $i<sizeof($usernames); $i++)
			{
				$sql_page .= "users.urlname = ".$this->db->escape($usernames[$i]);
				if ($i != sizeof($usernames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			/*
			$sql_page .= ")) OR (posts.postid = posts_contributors.postid AND posts_contributors.userid = users.userid AND (";
			for ($i=0; $i<sizeof($usernames); $i++)
			{
				$sql_page .= "users.urlname = ".$this->db->escape($usernames[$i]);
				if ($i != sizeof($usernames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			*/
			$sql_page .= ")))";
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

		$sql_featured = "AND posts.featured != '-1'";
		if (isset($featured))
		{
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}

		$sql_category = "";
		if ($category == "0")
		{
			$sql_category = "AND NOT EXISTS (SELECT postid FROM posts_categories WHERE posts.postid = posts_categories.postid)";

		}
		else if ($category != NULL)
		{
			$sql_from .= ", posts_categories";
			$sql_category = "AND posts.postid = posts_categories.postid AND posts_categories.categoryid = ".$this->db->escape($category);
		}

		$query = $this->db->query("SELECT count(posts.postid) as total FROM $sql_from WHERE posts.published = '1' $sql_category $sql_featured $sql_page");
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

	function get_potential_references_by_user($userid, $postid)
	{
		if ($postid == 0)
		{
			return $this->db->query("SELECT postid FROM posts WHERE userid = ".$this->db->escape($userid)." AND postid != ".$this->db->escape($postid)." AND published = '1' AND featured != '-1' ORDER BY date DESC");
		}
		else
		{
			return $this->db->query("SELECT postid FROM posts WHERE userid = ".$this->db->escape($userid)." AND postid != ".$this->db->escape($postid)." AND date < ANY (SELECT date FROM posts WHERE postid = ".$this->db->escape($postid).") AND published = '1' AND featured != '-1' ORDER BY date DESC");
		}
	}

	function get_potential_references_by_page($pageid, $postid)
	{
		if ($postid == 0)
		{
			return $this->db->query("SELECT postid FROM posts WHERE pageid = ".$this->db->escape($pageid)." AND postid != ".$this->db->escape($postid)." AND published = '1' AND featured != '-1' ORDER BY date DESC");
		}
		else
		{
			return $this->db->query("SELECT postid FROM posts WHERE pageid = ".$this->db->escape($pageid)." AND postid != ".$this->db->escape($postid)." AND date < ANY (SELECT date FROM posts WHERE postid = ".$this->db->escape($postid).") AND published = '1' AND featured != '-1' ORDER BY date DESC");
		}
	}

	function get_references($postid)
	{
		return $this->db->query("SELECT posts.postid FROM posts, posts_references WHERE posts_references.postid=".$this->db->escape($postid)." AND posts_references.referenceid = posts.postid");
	}

	function get_user_drafts($userid)
	{
		return $this->db->query("SELECT postid FROM posts WHERE userid = ".$this->db->escape($userid)." AND published = '0' ORDER BY date DESC");
	}

	function get_user_heldback($userid)
	{
		return $this->db->query("SELECT postid FROM posts WHERE userid = ".$this->db->escape($userid)." AND published = '1' AND featured = '-1'  ORDER BY date DESC");
	}

	function get_user_posts($userid, $num, $offset)
	{
		return $this->db->query("SELECT postid FROM posts WHERE userid = ".$this->db->escape($userid)." AND published = '1' AND featured != '-1' ORDER BY date DESC LIMIT $offset, $num");
	}

	function get_user_posts_count($userid)
	{
		$query = $this->db->query("SELECT count(*) as total FROM posts WHERE userid = ".$this->db->escape($userid)." AND published = '1' AND featured != '-1'");
		$row = $query->row_array();
		return $row['total'];
	}

	function remove_contributors($postid)
	{
		$this->db->query("DELETE FROM posts_contributors WHERE postid = ".$this->db->escape($postid));
	}

	function remove_references($postid)
	{
		$this->db->query("DELETE FROM posts_references WHERE postid = ".$this->db->escape($postid));
	}

	function remove_theme_files($postid)
	{
		$this->db->query("DELETE FROM posts_theme_files WHERE postid = ".$this->db->escape($postid));
	}

	function search($searchby, $num, $offset, $pageids = NULL)
	{
		$pageid_sql = NULL;
		if ($pageids != NULL)
		{
			$pages = explode(";",$pageids);
			for ($i=0; $i<sizeof($pages); $i++)
			{
				$pageid_sql .= "pageid = ".$this->db->escape($pages[$i]);
				if ($i != sizeof($pages)-1)
				{
					$pageid_sql .= " OR ";
				}
			}

			$pageid_sql = "AND (".$pageid_sql.")";
		}

		$searchby =  html_entity_decode($searchby, ENT_QUOTES);
		return $this->db->query("SELECT DISTINCT postid FROM posts WHERE published = '1' $pageid_sql AND (content LIKE '%".$this->db->escape_like_str($searchby)."%' OR title LIKE '%".$this->db->escape_like_str($searchby)."%') ORDER BY title ASC LIMIT $offset, $num");
	}

	function search_count($searchby, $pageids = NULL)
	{
		$pageid_sql = NULL;
		if ($pageids != NULL)
		{
			$pages = explode(";",$pageids);
			for ($i=0; $i<sizeof($pages); $i++)
			{
				$pageid_sql .= "pageid = ".$this->db->escape($pages[$i]);
				if ($i != sizeof($pages)-1)
				{
					$pageid_sql .= " OR ";
				}
			}

			$pageid_sql = "AND (".$pageid_sql.")";
		}
		$query = $this->db->query("SELECT DISTINCT count(postid) AS total FROM posts WHERE published = '1' $pageid_sql AND (content LIKE ".$this->db->escape('%'.$searchby.'%')." OR title LIKE ".$this->db->escape('%'.$searchby.'%').")");
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

	function set_reference($postid, $referenceid)
	{
		$this->db->query("INSERT into posts_references (postid, referenceid) VALUES (".$this->db->escape($postid).",".$this->db->escape($referenceid).")");
	}

	function update($postid, $post)
	{
		$this->db->query("UPDATE posts SET title = ".$this->db->escape($post['title']).",
			content = ".$this->db->escape($post['content']).",
			urlname = ".$this->db->escape($post['urlname']).",
			css = ".$this->db->escape($post['css']).",
			javascript = ".$this->db->escape($post['javascript']).",
			code = ".$this->db->escape($post['code']).",
			date = ".$this->db->escape($post['date']).",
			datemodified = ".$this->db->escape($post['datemodified']).",
			imageid = ".$this->db->escape($post['imageid']).",
			featured = ".$this->db->escape($post['featured']).",
			reviewed = ".$this->db->escape($post['reviewed']).",
			published = ".$this->db->escape($post['published']).",
			needsubscription = ".$this->db->escape($post['needsubscription'])."
			WHERE postid=".$this->db->escape($postid));
	}
}