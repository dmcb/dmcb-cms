<?php

class Views_model extends CI_Model {

    function Views_model()
    {
        parent::__construct();
    }
	
	function add($userid, $ip, $type, $typeid)
	{
		$touched = date('Y-m-d H:i');
		$week = date('Y-m-d H:i',strtotime("1 January ".date('Y')." +".date('W')." week -1 week"));
		//the following query translates to 'insert new hit total for user on item for the week, unless a hit total for that user on that item for that week exists, in which case if the touched time is within the same minute as the last touch, maintain existing hit value, otherwise add another hit to the hit total, and always update touch time to the current minute
		//or, simply put, a hit is recorded for the page unless that person already hit that page within the same minute
		$this->db->query("INSERT into views (userid, ip, week, touched, type, typeid, hits) VALUES (".$this->db->escape($userid).",".$this->db->escape($ip).",".$this->db->escape($week).",".$this->db->escape($touched).",".$this->db->escape($type).",".$this->db->escape($typeid).",'1') ON DUPLICATE KEY UPDATE hits = IF(VALUES(touched) = views.touched,views.hits,views.hits+1), touched = ".$this->db->escape($touched));
	}
	
	function get($type, $typeid)
	{
		$query = $this->db->query("SELECT count(*) FROM views WHERE type = ".$this->db->escape($type)." AND typeid = ".$this->db->escape($typeid));
		$row = $query->row_array();
		$views =  $row['count(*)'];
		
		if ($type == "post")
		{
			$query = $this->db->query("SELECT views FROM posts WHERE postid = ".$this->db->escape($typeid));
			$row = $query->row_array();
			$views = $views + $row['views'];
		}
		
		return $views;
	}
	
	function get_most_popular_by_unfeatured()
	{
		return $this->db->query("SELECT posts.*, sum(views.hits) FROM views, posts WHERE DATE_SUB(CURDATE(),INTERVAL 14 DAY) <= views.week AND posts.postid = views.typeid AND posts.published = '1' AND posts.featured != '-1' AND posts.pageid IS NULL GROUP BY posts.postid ORDER BY sum(views.hits) DESC LIMIT 0, 10");
	}
}
?>