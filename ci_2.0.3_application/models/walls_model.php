<?php

class Walls_model extends CI_Model {

    function Walls_model()
    {
        parent::__construct();
    }
	
	function add($content, $name, $city, $ip)
	{
		$date = date('Y-m-d H:i:s');
		$this->db->query("INSERT into walls (content, name, city, ip, date) VALUES (".$this->db->escape($content).",".$this->db->escape($name).",".$this->db->escape($city).",".$this->db->escape($ip).",".$this->db->escape($date).")");
	}
	
	function check($ip)
	{
		$query = $this->db->query("SELECT count(*) FROM walls WHERE ip = ".$this->db->escape($ip)." AND DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= date");
		$row = $query->row_array(); 
		if ($row['count(*)'] == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function get($num, $offset)
	{
		return $this->db->query("SELECT * FROM walls ORDER BY date DESC LIMIT $offset, $num");
	}
	
	function get_count()
	{
		$query = $this->db->query("SELECT count(*) FROM walls");	
		$row = $query->row_array(); 
		return $row['count(*)'];
	}
}
?>