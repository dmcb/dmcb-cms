<?php

class Categories_model extends CI_Model {

    function Categories_model()
    {
        parent::__construct();
    }
	
	function add($categoryid, $postid)
	{
		$this->db->query("INSERT into posts_categories (categoryid, postid) VALUES (".$this->db->escape($categoryid).", ".$this->db->escape($postid).")");
	}
	
	function add_custom($name)
	{
		$urlname = preg_replace("/[^a-zA-Z0-9 -]/", "", $name);
		$urlname = str_replace(" ", "_", $urlname);
		$urlname = strtolower($urlname);
		$rootname = $urlname;
		$i=1;
		while ($this->get_categoryid($urlname) != NULL)
		{
			$i++;
			$urlname = $rootname.'_'.$i;
		}
		$this->db->query("INSERT into categories (name, urlname, heldback) VALUES (".$this->db->escape($name).",".$this->db->escape($urlname).", '1')");
		return $this->db->insert_id();
	}
	
	function approve($categoryid, $name)
	{
		$urlname = preg_replace("/[^a-zA-Z0-9 -]/", "", $name);
		$urlname = str_replace(" ", "_", $urlname);
		$urlname = strtolower($urlname);
		$rootname = $urlname;
		$i=1;
		while ($this->get_categoryid($urlname) != NULL && $this->get_categoryid($urlname) != $categoryid)
		{
			$i++;
			$urlname = $rootname.'_'.$i;
		}
		$this->db->query("UPDATE categories SET heldback='0', name = ".$this->db->escape($name).", urlname = ".$this->db->escape($urlname)." WHERE categoryid = ".$this->db->escape($categoryid));
	}
	
	function delete($categoryid)
	{
		$this->db->query("DELETE FROM categories WHERE categoryid = ".$this->db->escape($categoryid));
		$this->db->query("DELETE FROM posts_categories WHERE categoryid = ".$this->db->escape($categoryid));
	}
	
	function delete_by_post($postid)
	{
		$this->db->query("DELETE FROM posts_categories WHERE postid = ".$this->db->escape($postid));
	}
	
	function get($categoryid)
	{
		$query = $this->db->query("SELECT * FROM categories WHERE categoryid = ".$this->db->escape($categoryid)." ORDER BY name ASC");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_by_name($name)
	{
		$query = $this->db->query("SELECT categoryid FROM categories WHERE name = ".$this->db->escape($name));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row['categoryid'];
		}
	}
	
	function get_by_urlname($name)
	{
		$query = $this->db->query("SELECT categoryid FROM categories WHERE urlname = ".$this->db->escape($name));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row['categoryid'];
		}
	}
	
	function get_by_post($postid)
	{
		return $this->db->query("SELECT * FROM posts_categories, categories WHERE posts_categories.postid = ".$this->db->escape($postid)." AND posts_categories.categoryid = categories.categoryid ORDER BY categories.name ASC");
	}
	
	function get_by_post_published($postid)
	{
		return $this->db->query("SELECT * FROM posts_categories, categories WHERE posts_categories.postid = ".$this->db->escape($postid)." AND posts_categories.categoryid = categories.categoryid AND categories.heldback = '0' ORDER BY categories.name ASC");
	}
	
	function get_categoryid($urlname)
	{
		$query = $this->db->query("SELECT categoryid FROM categories WHERE urlname = ".$this->db->escape($urlname));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row['categoryid'];
		}
	}
	
	function get_list()
	{
		return $this->db->query("SELECT * FROM categories WHERE heldback = '0' ORDER BY categories.name ASC");
	}	
	
	function get_list_heldback()
	{
		return $this->db->query("SELECT * FROM categories WHERE heldback = '1' ORDER BY categories.name ASC");
	}

	function get_published($pages, $featured)
	{
		$sql_from = "categories, posts_categories, posts";
		$sql_page = "AND ";
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
			$pagenames = explode(";",$pages);
			for ($i=0; $i<sizeof($pagenames); $i++)
			{
				$sql_page .= "pages.urlname = ".$this->db->escape($pagenames[$i]);
				if ($i != sizeof($pagenames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			$sql_page .= ")";
		}

		$sql_featured = "AND posts.featured != '-1'";
		if (isset($featured))
		{	
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}
		return $this->db->query("SELECT categories.urlname, categories.name, categories.categoryid FROM $sql_from WHERE categories.heldback = '0' AND categories.categoryid = posts_categories.categoryid AND posts_categories.postid = posts.postid $sql_featured $sql_page AND posts.published = '1' GROUP BY categories.categoryid ORDER BY categories.name ASC");
	}

	function get_published_count($categoryid, $pages, $featured)
	{
		$sql_from = "categories, posts_categories, posts";
		$sql_page = "AND ";
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
			$pagenames = explode(";",$pages);
			for ($i=0; $i<sizeof($pagenames); $i++)
			{
				$sql_page .= "pages.urlname = ".$this->db->escape($pagenames[$i]);
				if ($i != sizeof($pagenames)-1)
				{
					$sql_page .= " OR ";
				}
			}
			$sql_page .= ")";
		}

		$sql_featured = "AND posts.featured != '-1'";
		if (isset($featured))
		{	
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}
		
		$query = $this->db->query("SELECT count(*) as total FROM $sql_from WHERE categories.heldback = '0' AND categories.categoryid = posts_categories.categoryid AND posts_categories.categoryid = ".$this->db->escape($categoryid)." AND posts.postid = posts_categories.postid $sql_featured $sql_page AND posts.published = '1'");
		$row = $query->row_array(); 
		return $row['total'];
	}	
	
	function get_posts($categoryid, $num, $offset)
	{
		return $this->db->query("SELECT * FROM posts, posts_categories WHERE posts_categories.categoryid = ".$this->db->escape($categoryid)." AND posts_categories.postid = posts.postid AND posts.published = '1' AND posts.featured != '-1' ORDER BY date DESC LIMIT $offset, $num");
	}
	
	function get_posts_by_page($categoryid, $pageid, $num, $offset)
	{
		return $this->db->query("SELECT * FROM posts, posts_categories WHERE posts_categories.categoryid = ".$this->db->escape($categoryid)." AND posts_categories.postid = posts.postid AND posts.pageid = ".$this->db->escape($pageid)." AND posts.published = '1' AND posts.featured != '-1' ORDER BY date DESC LIMIT $offset, $num");
	}
	
	function merge($tomerge, $mergeinto)
	{
		$this->db->query("UPDATE IGNORE posts_categories AS tomerge, posts_categories AS mergeinto SET tomerge.categoryid = ".$this->db->escape($mergeinto)." WHERE tomerge.categoryid = ".$this->db->escape($tomerge)." AND mergeinto.categoryid = ".$this->db->escape($mergeinto)." AND tomerge.postid != mergeinto.postid");
		$this->db->query("DELETE FROM posts_categories WHERE categoryid = ".$this->db->escape($tomerge));
		$this->db->query("DELETE FROM categories WHERE categoryid = ".$this->db->escape($tomerge));
	}
}
?>