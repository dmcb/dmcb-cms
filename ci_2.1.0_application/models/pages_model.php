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
class Pages_model extends CI_Model {

    function Pages_model()
    {
        parent::__construct();
    }

	function add($menu, $title, $pageof)
	{
		$position = $this->get_lowest_position();
		$this->db->query("INSERT INTO pages (menu, title, pageof, datemodified, position) VALUES (".$this->db->escape($menu).", ".$this->db->escape($title).", ".$this->db->escape($pageof).", NOW(), ".$this->db->escape($position).")");
		return $this->db->insert_id();
	}
	
	function add_protection($pageid, $roleid)
	{
		$this->db->query("INSERT INTO pages_protection (pageid, roleid) VALUES (".$this->db->escape($pageid).", ".$this->db->escape($roleid).")");
	}
	
	function autocomplete($value)
	{
		return $this->db->query("SELECT urlname AS result FROM pages WHERE urlname REGEXP '[[:<:]]".$this->db->escape_like_str($value)."' ORDER BY urlname ASC");
	}
	
	function delete($pageid)
	{
		$this->db->query("DELETE FROM pages WHERE pageid=".$this->db->escape($pageid));
	}

	function get($pageid)
	{
		$query = $this->db->query("SELECT * FROM pages WHERE pageid = ".$this->db->escape($pageid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row;
		}
	}
	
	function get_by_link($link)
	{
		$query = $this->db->query("SELECT pageid FROM pages WHERE link = ".$this->db->escape($link));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['pageid'];
		}	
	}
	
	function get_by_urlname($urlname)
	{
		$query = $this->db->query("SELECT pageid FROM pages WHERE urlname = ".$this->db->escape($urlname));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['pageid']; 
		}
	}
	
	function get_all()
	{
		return $this->db->query("SELECT pageid FROM pages ORDER BY position ASC");
	}
	
	function get_all_menus()
	{
		return $this->db->query("SELECT * FROM pages_menus");
	}

	function get_children($menu, $pageid = NULL)
	{
		$menu_sql = '';
		if ($menu != NULL)
		{
			$menu_sql = 'menu = '.$this->db->escape($menu).' AND';
		}
		$pageof_sql = 'pageof IS NULL';
		if ($pageid != NULL)
		{
			$pageof_sql = 'pageof = '.$this->db->escape($pageid);
		}
		return $this->db->query("SELECT pageid FROM pages WHERE $menu_sql $pageof_sql ORDER BY position ASC");
	}
	
	function get_children_tree($urlname, &$result = array())
	{
		$query = $this->db->query("SELECT child.urlname FROM pages AS child, pages AS parent WHERE child.pageof = parent.pageid AND parent.urlname = ".$this->db->escape($urlname));	
		foreach ($query->result_array() as $row)
		{
			array_push($result, $row['urlname']);
			$this->get_children_tree($row['urlname'], $result);
		}
	}
	
	function get_children_tree_pageids($pageid, &$result = array())
	{
		$query = $this->db->query("SELECT pageid FROM pages WHERE pageof = ".$this->db->escape($pageid));	
		foreach ($query->result_array() as $row)
		{
			array_push($result, $row['pageid']);
			$this->get_children_tree_pageids($row['pageid'], $result);
		}
	}
	
	function get_lowest_position()
	{
		$query = $this->db->query("SELECT MAX(position) FROM pages");
		$row = $query->row_array();
		if ($row['MAX(position)'] == NULL) return "1";
		else return $row['MAX(position)'] + 1;	
	}

	function get_menu($pageid)
	{
		$query = $this->db->query("SELECT menu FROM pages WHERE pageid= ".$this->db->escape($pageid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row['menu'];
		}	
	}
	
	function get_nested_pages($urlname)
	{
		return $this->db->query("SELECT pageid FROM pages WHERE urlname LIKE ".$this->db->escape($urlname.'/%'));
	}
	
	function get_parent_tree($pageid, &$result = array())
	{
		array_push($result, $pageid);
	
		$query = $this->db->query("SELECT pageof FROM pages WHERE pageid = ".$this->db->escape($pageid));	
		$row = $query->row_array(); 
		
		if ($row['pageof'] != NULL)
		{
			$this->get_parent_tree($row['pageof'], $result);
		}
	}
	
	function get_page_protection($pageid)
	{
		return $this->db->query("SELECT roleid FROM pages_protection WHERE pageid = ".$this->db->escape($pageid)." ORDER BY roleid ASC");
	}
	
	function get_published_all()
	{
		return $this->db->query("SELECT pageid FROM pages WHERE published = '1' ORDER BY position ASC");
	}
	
	function get_recent_modified_date()
	{
		$query = $this->db->query("SELECT datemodified from pages UNION SELECT datemodified from posts ORDER BY datemodified DESC LIMIT 1");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array(); 
			return $row['datemodified'];
		}
	}
	
	function get_using_template($templateid)
	{
		return $this->db->query("SELECT pageid FROM pages WHERE page_templateid = ".$this->db->escape($templateid)." OR post_templateid = ".$this->db->escape($templateid));
	}

	function move_up($pageid)
	{
		$page = $this->get($pageid);
		$pages = $this->get_children($page['menu'], $page['pageof']);

		$next = FALSE;
		$done = FALSE;
		for ($i=$pages->num_rows(); $i>0; $i--) 
		{
			$item = $pages->row_array($i-1);
			$item = $this->get($item['pageid']);
			if ($next && !$done) 
			{
				$temp = $page['position'];
				$this->db->query("UPDATE pages SET position=".$this->db->escape($item['position'])." WHERE pageid = ".$this->db->escape($pageid));
				$this->db->query("UPDATE pages SET position=".$this->db->escape($temp)." WHERE pageid = ".$this->db->escape($item['pageid']));
				$done = TRUE;
			}
			else if ($item['position'] == $page['position'] && !$done) 
			{
				$next = TRUE;
			}
		}
		if ($next && !$done) 
		{

		}
	}

	function move_down($pageid)
	{
		$page = $this->get($pageid);
		$pages = $this->get_children($page['menu'], $page['pageof']);

		$next = FALSE;
		$done = FALSE;
		for ($i=0; $i<$pages->num_rows(); $i++) 
		{
			$item = $pages->row_array($i);
			$item = $this->get($item['pageid']);
			if ($next && !$done) 
			{
				$temp = $page['position'];
				$this->db->query("UPDATE pages SET position=".$this->db->escape($item['position'])." WHERE pageid = ".$this->db->escape($pageid));
				$this->db->query("UPDATE pages SET position=".$this->db->escape($temp)." WHERE pageid = ".$this->db->escape($item['pageid']));
				$done = TRUE;
			}
			else if ($item['position'] == $page['position'] && !$done)
			{			
				$next = TRUE;
			}
		}
		if ($next && !$done)
		{

		}
	}
	
	function remove_protection($pageid)
	{
		$this->db->query("DELETE FROM pages_protection WHERE pageid = ".$this->db->escape($pageid));
	}

	function search($searchby, $num = NULL, $offset = NULL)
	{
		$searchby =  html_entity_decode($searchby, ENT_QUOTES);
		return $this->db->query("SELECT DISTINCT pageid FROM pages WHERE published = '1' AND URLNAME IS NOT NULL AND content LIKE '%".$this->db->escape_like_str($searchby)."%' OR title LIKE '%".$this->db->escape_like_str($searchby)."%' ORDER BY title ASC LIMIT $offset, $num");
	}
	
	function search_count($searchby)
	{
		$query = $this->db->query("SELECT DISTINCT count(pageid) AS total FROM pages WHERE published = '1' AND URLNAME IS NOT NULL AND content LIKE ".$this->db->escape('%'.$searchby.'%')." OR title LIKE ".$this->db->escape('%'.$searchby.'%'));
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

	function update($pageid, $page)
	{
		$this->db->query("UPDATE pages SET title = ".$this->db->escape($page['title']).", 
			urlname = ".$this->db->escape($page['urlname']).", 
			link = ".$this->db->escape($page['link']).", 
			content = ".$this->db->escape($page['content']).", 
			datemodified = ".$this->db->escape($page['datemodified']).",
			menu = ".$this->db->escape($page['menu']).", 
			pageof = ".$this->db->escape($page['pageof']).",
			imageid = ".$this->db->escape($page['imageid']).",
			protected = ".$this->db->escape($page['protected']).", 
			published = ".$this->db->escape($page['published']).", 
			needsubscription = ".$this->db->escape($page['needsubscription']).",
			pagepostname = ".$this->db->escape($page['pagepostname']).",
			page_templateid = ".$this->db->escape($page['page_templateid']).",
			post_templateid = ".$this->db->escape($page['post_templateid']).",
			pagination_blockid = ".$this->db->escape($page['pagination_blockid']).",
			rss_blockid = ".$this->db->escape($page['rss_blockid'])."
			WHERE pageid=".$this->db->escape($pageid));	
	}
}