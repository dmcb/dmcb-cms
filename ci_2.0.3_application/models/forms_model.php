<?php

class Forms_model extends CI_Model {

    function Forms_model()
    {
        parent::__construct();
    }
	
	function add($form, $email, $ip)
	{
		$date = date('Y-m-d H:i:s');
		$this->db->query("INSERT into forms (form, email, ip, date) VALUES (".$this->db->escape($form).",".$this->db->escape($email).",".$this->db->escape($ip).",".$this->db->escape($date).")");
	}
	
	function get_recent($email)
	{
		$query = $this->db->query("SELECT * FROM forms WHERE DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= date AND email = ".$this->db->escape($email)." ORDER BY date DESC LIMIT 1");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
}

?>