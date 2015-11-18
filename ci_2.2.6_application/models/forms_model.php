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
		$query = $this->db->query("SELECT form FROM forms WHERE date > now() - INTERVAL 1 MINUTE AND email = ".$this->db->escape($email)." LIMIT 1");
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