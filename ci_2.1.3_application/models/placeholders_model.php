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
class Placeholders_model extends CI_Model {

    function Placeholders_model()
    {
        parent::__construct();
    }
	
	function add($attachedto, $oldname, $newname)
	{
		// Update any existing placeholders that point to the old name to point to this new name as well
		$this->db->query("UPDATE IGNORE placeholders SET newname = ".$this->db->escape($newname)." WHERE newname = ".$this->db->escape($oldname)); 
		$this->db->query("INSERT IGNORE into placeholders (attachedto, oldname, newname, date) VALUES (".$this->db->escape($attachedto).",".$this->db->escape($oldname).",".$this->db->escape($newname).", NOW())");
		return $this->db->insert_id();
	}
	
	function delete($attachedto, $oldname)
	{
		$this->db->query("DELETE FROM placeholders WHERE attachedto = ".$this->db->escape($attachedto)." AND oldname=".$this->db->escape($oldname));
	}

	function get($attachedto, $oldname)
	{
		$query = $this->db->query("SELECT * FROM placeholders WHERE attachedto = ".$this->db->escape($attachedto)." AND oldname = ".$this->db->escape($oldname));
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