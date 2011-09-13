<?php

class Templates_model extends CI_Model {

    function Templates_model()
    {
        parent::__construct();
    }

	function add($title, $type, $pageid)
	{
		$default_content = "%contenthere%";
		if ($type == "post")
		{
			$default_content .= " %commentshere% %fileshere% %referenceshere%";
		}
		
		$this->db->query("INSERT INTO templates (title, type, pageid, content) VALUES (".$this->db->escape($title).", ".$this->db->escape($type).", ".$this->db->escape($pageid).", '".$default_content."')");
		return $this->db->insert_id();
	}
	
	function add_field($templateid, $name, $htmlcode, $form_type, $required)
	{
		$this->db->query("INSERT INTO templates_fields (templateid, name, htmlcode, form_type, required) VALUES (".$this->db->escape($templateid).", ".$this->db->escape($name).", ".$this->db->escape($htmlcode).", ".$this->db->escape($form_type).", ".$this->db->escape($required).")");
	}
	
	function add_value($templateid, $attachedto, $attachedid, $htmlcode, $value)
	{
		$this->db->query("INSERT INTO templates_fields_values (templateid, attachedto, attachedid, htmlcode, value) VALUES (".$this->db->escape($templateid).", ".$this->db->escape($attachedto).", ".$this->db->escape($attachedid).", ".$this->db->escape($htmlcode).", ".$this->db->escape($value).")");	
	}
	
	function delete($templateid)
	{
		$this->db->query("DELETE FROM templates WHERE templateid=".$this->db->escape($templateid));
		$this->db->query("DELETE FROM templates_defaults WHERE templateid=".$this->db->escape($templateid));
		$this->db->query("DELETE FROM templates_fields WHERE templateid=".$this->db->escape($templateid));
		$this->db->query("DELETE FROM templates_fields_values WHERE templateid=".$this->db->escape($templateid));
	}
	
	function delete_field($templateid, $htmlcode)
	{
		$this->db->query("DELETE FROM templates_fields WHERE templateid = ".$this->db->escape($templateid)." AND htmlcode = ".$this->db->escape($htmlcode));
		$this->db->query("DELETE FROM templates_fields_values WHERE templateid = ".$this->db->escape($templateid)." AND htmlcode = ".$this->db->escape($htmlcode));
	}
	
	function delete_values($templateid, $attachedto, $attachedid)
	{
		$this->db->query("DELETE FROM templates_fields_values WHERE templateid = ".$this->db->escape($templateid)." AND attachedto = ".$this->db->escape($attachedto)." AND attachedid = ".$this->db->escape($attachedid));
	}
	
	function edit_field($templateid, $old_htmlcode, $name, $htmlcode, $form_type, $required)
	{
		$this->db->query("UPDATE templates_fields SET name = ".$this->db->escape($name).",
			htmlcode = ".$this->db->escape($htmlcode).", 
			form_type = ".$this->db->escape($form_type).", 
			required = ".$this->db->escape($required)."
			WHERE templateid = ".$this->db->escape($templateid)." AND htmlcode = ".$this->db->escape($old_htmlcode));
			
		$this->db->query("UPDATE templates_fields_values SET htmlcode = ".$this->db->escape($htmlcode)."
			WHERE templateid = ".$this->db->escape($templateid)." AND htmlcode = ".$this->db->escape($old_htmlcode));
	}

	function get($templateid)
	{
		$query = $this->db->query("SELECT * FROM templates WHERE templateid = ".$this->db->escape($templateid));
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
		$query = $this->db->query("SELECT templates.templateid FROM templates, templates_defaults WHERE templates.type = ".$this->db->escape($details[0])." AND templates.templateid = templates_defaults.templateid AND templates_defaults.pageid = ".$this->db->escape($details[1]));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['templateid']; 
		}
	}
	
	function get_by_title($title)
	{
		$query = $this->db->query("SELECT templateid FROM templates WHERE title = ".$this->db->escape($title));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['templateid']; 
		}	
	}
	
	function get_attached($pageid, $type = NULL)
	{		
		$type_sql = "";
		if ($type_sql != NULL)
		{
			$type_sql = " AND type = ".$this->db->escape($type);
		}
		return $this->db->query("SELECT templateid FROM templates WHERE pageid = ".$this->db->escape($pageid).$type_sql." ORDER BY type, title ASC");
	}
	
	function get_defaults($pageid)
	{
		return $this->db->query("SELECT templateid FROM templates_defaults WHERE pageid = ".$this->db->escape($pageid));
	}
	
	function get_fields($templateid)
	{
		return $this->db->query("SELECT * FROM templates_fields WHERE templateid = ".$this->db->escape($templateid)." ORDER BY form_type");
	}
	
	function get_values($templateid, $attachedto, $attachedid)
	{
		return $this->db->query("SELECT * FROM templates_fields_values WHERE templateid = ".$this->db->escape($templateid)." AND attachedto = ".$this->db->escape($attachedto)." AND attachedid = ".$this->db->escape($attachedid));
	}
	
	function get_field_by_htmlcode($templateid, $htmlcode)
	{
		$query = $this->db->query("SELECT * FROM templates_fields WHERE templateid = ".$this->db->escape($templateid)." AND htmlcode = ".$this->db->escape($htmlcode));
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
	
	function get_field_by_name($templateid, $name)
	{
		$query = $this->db->query("SELECT * FROM templates_fields WHERE templateid = ".$this->db->escape($templateid)." AND name = ".$this->db->escape($name));
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
	
	function get_quota_filetypes($quotaid)
	{
		return $this->db->query("SELECT * FROM quotas_filetypes WHERE quotaid = ".$this->db->escape($quotaid));
	}
	
	function get_quota_protection($quotaid)
	{
		return $this->db->query("SELECT * FROM quotas_protection WHERE quotaid = ".$this->db->escape($quotaid));
	}
	
	function get_quotas($templateid)
	{
		return $this->db->query("SELECT * FROM quotas, quotas_defaults WHERE quotas.quotaid = quotas_defaults.quotaid AND quotas_defaults.templateid = ".$this->db->escape($templateid));
	}
	
	function remove_default_template($pageid, $type)
	{
		$this->db->query("DELETE templates_defaults FROM templates_defaults LEFT JOIN templates ON templates_defaults.templateid = templates.templateid WHERE templates_defaults.pageid = ".$this->db->escape($pageid)." AND templates.type = ".$this->db->escape($type)); 
	}
	
	function set_default_template($templateid, $pageid)
	{
		$this->db->query("INSERT INTO templates_defaults (templateid, pageid) VALUES (".$this->db->escape($templateid).", ".$this->db->escape($pageid).")"); 
	}
	
	function update($templateid, $template)
	{
		$this->db->query("UPDATE templates SET title = ".$this->db->escape($template['title']).", 
			type = ".$this->db->escape($template['type']).",
			pageid = ".$this->db->escape($template['pageid']).",
			content = ".$this->db->escape($template['content']).", 
			simple = ".$this->db->escape($template['simple']).",
			pagepostname = ".$this->db->escape($template['pagepostname'])."
			WHERE templateid=".$this->db->escape($templateid));				
	}
}
?>