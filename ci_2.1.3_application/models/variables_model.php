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
class Variables_model extends CI_Model {

    function Variables_model()
    {
        parent::__construct();
    }

	function delete($key)
	{
		$this->db->query("DELETE FROM variables WHERE variable_key = ".$this->db->escape($key));
	}

	function get($key)
	{
		$query = $this->db->query("SELECT variable_value FROM variables WHERE variable_key = ".$this->db->escape($key)." LIMIT 1");
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['variable_value'];
		}
	}

	function set($key, $value)
	{
		$date = date('Y-m-d H:i:s');
		$this->db->query("INSERT into variables (variable_key, variable_value) VALUES (".$this->db->escape($key).",".$this->db->escape($value).") ON DUPLICATE KEY UPDATE variable_value = ".$this->db->escape($value));
	}
}