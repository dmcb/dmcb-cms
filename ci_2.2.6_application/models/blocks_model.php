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
class Blocks_model extends CI_Model {

    function Blocks_model()
    {
        parent::__construct();
    }

	function add($pageid, $function, $title)
	{
		$this->db->query("INSERT INTO blocks_instances (pageid, function, title) VALUES (".$this->db->escape($pageid).", ".$this->db->escape($function).", ".$this->db->escape($title).")");
		return $this->db->insert_id();
	}
	
	function add_value($blockinstanceid, $variablename, $value)
	{
		$this->db->query("INSERT INTO blocks_instances_values (blockinstanceid, variablename, value) VALUES (".$this->db->escape($blockinstanceid).", ".$this->db->escape($variablename).", ".$this->db->escape($value).")");
	}
	
	function delete($blockinstanceid)
	{
		$this->db->query("DELETE FROM blocks_defaults WHERE blockinstanceid=".$this->db->escape($blockinstanceid));
		$this->db->query("DELETE FROM blocks_instances WHERE blockinstanceid=".$this->db->escape($blockinstanceid));
		$this->db->query("DELETE FROM blocks_instances_values WHERE blockinstanceid=".$this->db->escape($blockinstanceid));
	}
	
	function disable_block($function)
	{
		$this->db->query("UPDATE blocks SET enabled = '0' WHERE function = ".$this->db->escape($function));		
	}
	
	function enable_block($function)
	{
		$this->db->query("UPDATE blocks SET enabled = '1' WHERE function = ".$this->db->escape($function));		
	}
	
	function get($blockinstanceid)
	{
		$query = $this->db->query("SELECT * FROM blocks_instances WHERE blockinstanceid = ".$this->db->escape($blockinstanceid));
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
	
	function get_by_default($details)
	{
		$query = $this->db->query("SELECT blockinstanceid FROM blocks_defaults WHERE type = ".$this->db->escape($details[0])." AND pageid = ".$this->db->escape($details[1]));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['blockinstanceid']; 
		}
	}
	
	function get_by_title($title)
	{
		$query = $this->db->query("SELECT blockinstanceid FROM blocks_instances WHERE title = ".$this->db->escape($title));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['blockinstanceid']; 
		}	
	}
	
	function get_defaults($pageid)
	{
		return $this->db->query("SELECT * FROM blocks_defaults WHERE pageid = ".$this->db->escape($pageid));
	}
	
	function get_function($function)
	{
		$query = $this->db->query("SELECT * FROM blocks WHERE function = ".$this->db->escape($function));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_functions_disabled()
	{
		return $this->db->query("SELECT function, name FROM blocks WHERE enabled = '0' ORDER BY name ASC");
	}

	function get_functions_enabled()
	{
		return $this->db->query("SELECT function, name FROM blocks WHERE enabled = '1' ORDER BY name ASC");
	}
	
	function get_instance_values($blockinstanceid)
	{
		return $this->db->query("SELECT * FROM blocks_instances_values WHERE blockinstanceid = ".$this->db->escape($blockinstanceid));
	}
	
	function get_page_blocks($pageid)
	{
		return $this->db->query("SELECT blockinstanceid FROM blocks_instances WHERE pageid = ".$this->db->escape($pageid)." ORDER BY title ASC");
	}
	
	function get_variable_blocks($variablename, $value)
	{
		return $this->db->query("SELECT DISTINCT blockinstanceid FROM blocks_instances_values WHERE variablename = ".$this->db->escape($variablename)." AND value = ".$this->db->escape($value));
	}
	
	function get_variables($function)
	{
		return $this->db->query("SELECT * FROM blocks_variables WHERE function = ".$this->db->escape($function));
	}

	function remove_default_block($pageid, $type)
	{
		$this->db->query("DELETE FROM blocks_defaults WHERE pageid = ".$this->db->escape($pageid)." AND type = ".$this->db->escape($type)); 
	}
	
	function remove_instance_values($blockinstanceid)
	{
		$this->db->query("DELETE FROM blocks_instances_values WHERE blockinstanceid = ".$this->db->escape($blockinstanceid));
	}

	function set_default_block($blockinstanceid, $pageid, $type)
	{
		$this->db->query("INSERT INTO blocks_defaults (blockinstanceid, pageid, type) VALUES (".$this->db->escape($blockinstanceid).", ".$this->db->escape($pageid).", ".$this->db->escape($type).")"); 
	}
	
	function update($blockinstanceid, $block)
	{
		$this->db->query("UPDATE blocks_instances SET function = ".$this->db->escape($block['function']).", 
			title = ".$this->db->escape($block['title']).",
			feedback = ".$this->db->escape($block['feedback'])."
			WHERE blockinstanceid=".$this->db->escape($blockinstanceid));	
	}
}