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
class Events_model extends CI_Model {

    function Events_model()
    {
        parent::__construct();
    }

	function add($postid, $location, $address, $date, $time, $enddate, $endtime)
	{
		if ($location == "") $location = NULL;
		if ($address == "") $address = NULL;
		if ($time == "0:00" || $time == "") $time = NULL;
		if ($enddate == "") $enddate = NULL;
		if ($endtime == "0:00" || $endtime == "") $endtime = NULL;
		$this->db->query("INSERT into posts_events (postid, location, address, date, time, enddate, endtime) VALUES (".$this->db->escape($postid).",".$this->db->escape($location).",".$this->db->escape($address).",".$this->db->escape($date).",".$this->db->escape($time).",".$this->db->escape($enddate).",".$this->db->escape($endtime).")");
	}

	function delete($postid)
	{
		$this->db->query("DELETE FROM posts_events WHERE postid=".$this->db->escape($postid));
	}

	function get($postid)
	{
		$query = $this->db->query("SELECT * FROM posts_events WHERE postid = ".$this->db->escape($postid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array();
		}
	}

	function get_published($num, $offset, $timeline, $pages, $featured)
	{
		$sql_from = "posts_events, posts";
		$sql_upcoming = "<";
		if ($timline = "upcoming")
		{
			$sql_upcoming = ">=";
		}

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
			$sql_from .= ",pages ";
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

		$sql_featured = "AND posts.featured != -1";
		if (isset($featured))
		{
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}
		return $this->db->query("SELECT posts.*, posts_events.*, posts_events.date AS date, posts.date AS publisheddate FROM $sql_from WHERE (posts_events.date $sql_upcoming NOW() OR (posts_events.enddate IS NOT NULL AND posts_events.enddate $sql_upcoming NOW())) AND posts_events.postid = posts.postid AND posts.published = '1' $sql_featured $sql_page ORDER BY posts_events.date,posts_events.time ASC LIMIT $offset, $num");
	}

	function get_published_count($timeline, $pages, $featured)
	{
		$sql_from = "posts_events, posts";
		$sql_upcoming = "<";
		if ($timline = "upcoming")
		{
			$sql_upcoming = ">=";
		}

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
			$sql_from .= ",pages ";
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

		$sql_featured = "AND posts.featured != -1";
		if (isset($featured))
		{
			$sql_featured = "AND posts.featured = ".$this->db->escape($featured);
		}

		$query = $this->db->query("SELECT count(*) as total FROM $sql_from WHERE (posts_events.date $sql_upcoming NOW() OR (posts_events.enddate IS NOT NULL AND posts_events.enddate $sql_upcoming NOW())) AND posts_events.postid = posts.postid AND posts.published = '1' $sql_featured $sql_page");
		$row = $query->row_array();
		return $row['total'];
	}
}