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
class Waivers_model extends CI_Model {

    function Variables_model()
    {
        parent::__construct();
    }

	function acknowledge($waiverid, $userid)
	{
		$this->db->query("INSERT into users_waivers (waiverid, userid, last_acknowledged) VALUES (".$this->db->escape($waiverid).",".$this->db->escape($userid).",NOW()) ON DUPLICATE KEY UPDATE last_acknowledged = NOW()");
	}

	function add($title, $content, $accept_message, $is_mandatory, $frequency)
	{
		$date = date('Y-m-d H:i:s');
		$this->db->query("INSERT into waivers (title, content, accept_message, is_mandatory, frequency) VALUES (".$this->db->escape($title).",".$this->db->escape($content).",".$this->db->escape($accept_message).",".$this->db->escape($is_mandatory).",".$this->db->escape($frequency).")");
		return $this->db->insert_id();
	}

	function check($waiverid, $userid)
	{
		$query = $this->db->query("SELECT last_acknowledged FROM users_waivers WHERE userid = ".$this->db->escape($userid)." AND waiverid = ".$this->db->escape($waiverid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['last_acknowledged'];
		}
	}

	function delete($waiverid)
	{
		$this->db->query("DELETE FROM waiver WHERE waiverid = ".$this->db->escape($waiverid));
	}

	function get($pageid)
	{
		$query = $this->db->query("SELECT * FROM waivers, pages_waivers WHERE waivers.waiverid = pages_waivers.waiverid AND pages_waivers.pageid = ".$this->db->escape($pageid));
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

	function update($waiverid, $waiver)
	{
		$this->db->query("UPDATE waivers SET title = ".$this->db->escape($waiver['title']).",
			content = ".$this->db->escape($waiverwaiver['content']).",
			accept_message = ".$this->db->escape($waiver['accept_message']).",
			is_mandatory = ".$this->db->escape($waiver['is_mandatory']).",
			frequency = ".$this->db->escape($waiver['frequency'])."
			WHERE waiverid=".$this->db->escape($waiverid));
	}
}