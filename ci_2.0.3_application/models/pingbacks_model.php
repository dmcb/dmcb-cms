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
class Pingbacks_model extends CI_Model {

    function Pingbacks_model()
    {
        parent::__construct();
    }
	
	function add($postid, $source, $title, $summary, $ip)
	{
		$time = date('Y-m-d H:i:s');
		$this->db->query("INSERT into pingbacks (postid, source, title, summary, date, ip, featured) VALUES (".$this->db->escape($postid).",".$this->db->escape($source).",".$this->db->escape($title).",".$this->db->escape($summary).",".$this->db->escape($time).",".$this->db->escape($ip).", '0')");
	}
	
	function check($postid, $source)
	{
		$query = $this->db->query("SELECT postid FROM pingbacks WHERE postid=".$this->db->escape($postid)." AND source=".$this->db->escape($source));
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function get($postid)
	{
		return $this->db->query("SELECT * FROM pingbacks WHERE postid=".$this->db->escape($postid)." AND featured != '-1' ORDER BY date DESC");
	}
	
	function get_new()
	{
		return $this->db->query("SELECT * FROM pingbacks WHERE featured = '0' ORDER BY date DESC");
	}
	
	function update($pingbackid, $featured)
	{
		$this->db->query("UPDATE pingbacks SET featured = ".$this->db->escape($featured)." WHERE pingbackid = ".$this->db->escape($pingbackid));	 
	}
}