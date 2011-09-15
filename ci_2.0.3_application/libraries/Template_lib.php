<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb template library
 *
 * Initializes a template and runs checks and operations on that block
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */ 
class Template_lib {

	public  $template     = array();
	public  $new_template = array();
	public  $fields       = array();
	public  $values       = array();
	public  $quotas       = array();

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function Template_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('templates_model');
		if (isset($params['id']))
		{
			$this->template = $this->CI->templates_model->get($params['id']);
			$this->_initialize_template();
			$this->new_template = $this->template;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize template
	 *
	 * Load the template variables and values
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_template()
	{
		if (isset($this->template['templateid']))
		{
			$result = $this->CI->templates_model->get_fields($this->template['templateid']);
			foreach ($result->result_array() as $field)
			{
				$this->fields[$field['htmlcode']] = $field;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize quotas
	 *
	 * Grabs quotas for template for the specific page or post
	 *
	 * @access	public
	 * @return	void
	 */	
	function initialize_quotas()
	{
		if (isset($this->template['templateid']))
		{
			$quotas = $this->CI->templates_model->get_quotas($this->template['templateid']);
			foreach ($quotas->result_array() as $quota)
			{
				$quota['filetypes'] = array();
				$quota_filetypes = $this->CI->templates_model->get_quota_filetypes($quota['quotaid']);
				foreach ($quota_filetypes->result_array() as $quota_filetype)
				{
					$quota['filetypes'][$quota_filetype['filetypeid']] = $quota_filetype;
				}
				
				$quota['protection'] = array();
				$quota_protections = $this->CI->templates_model->get_quota_protection($quota['quotaid']);
				foreach ($quota_protections->result_array() as $quota_protection)
				{
					$quota['protection'][$quota_protection['roleid']] = 1;
				}
			
				array_push($this->quotas, $quota);
			}
		}
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize values
	 *
	 * Generates values for template fields for the specific page or post
	 *
	 * @access	public
	 * @param	int   attachedid
	 * @return	void
	 */	
	function initialize_values($attachedid)
	{
		if (isset($this->template['templateid']))
		{
			$result = $this->CI->templates_model->get_values($this->template['templateid'], $this->template['type'], $attachedid);
			foreach ($result->result_array() as $value)
			{
				$this->values[$value['htmlcode']] = $value['value'];
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Add field
	 *
	 * Adds a template field
	 *
	 * @access	public
	 * @param	string   name
	 * @param	string   html code
	 * @param	int      form type code
	 * @param	int      required
	 * @return	void
	 */	
	function add_field($name, $htmlcode, $form_type, $required)
	{
		$this->CI->templates_model->add_field($this->template['templateid'], $name, $htmlcode, $form_type, $required);
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete
	 *
	 * Delete a template
	 *
	 * @access	public
	 * @return	void
	 */	
	function delete()
	{
		$this->CI->load->model('pages_model');
		$pages = $this->CI->pages_model->get_using_template($this->template['templateid']);
		foreach ($pages->result_array() as $page) 
		{
			$object = instantiate_library('page', $page['pageid']);
			if ($object->page['page_templateid'] == $this->template['templateid'])
			{
				$object->new_page['page_templateid'] = NULL;
			}
			if ($object->page['post_templateid'] == $this->template['templateid'])
			{
				$object->new_page['post_templateid'] = NULL;
			}
			$object->save();
		}
		$this->CI->templates_model->delete($this->template['templateid']);
		$this->load->helper('template');
		set_page_post_urlnames();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete
	 *
	 * Delete a template field
	 *
	 * @access	public
	 * @param	string   html code
	 * @return	void
	 */	
	function delete_field($htmlcode)
	{
		$this->CI->templates_model->delete_field($this->template['templateid'], $htmlcode);
		// Remove reference to that template field from the template itself
		$this->new_template['content'] = str_replace(
												"%".$htmlcode."%",
												"",
												$this->new_template['content']);
		$this->save();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Edit field
	 *
	 * Edits a template field
	 *
	 * @access	public
	 * @param	string   previous html code
	 * @param	string   name
	 * @param	string   html code
	 * @param	int      form type code
	 * @param	int      required
	 * @return	void
	 */	
	function edit_field($old_htmlcode, $name, $htmlcode, $form_type, $required)
	{
		$this->CI->templates_model->edit_field($this->template['templateid'], $old_htmlcode, $name, $htmlcode, $form_type, $required);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Save
	 *
	 * Save template properties
	 *
	 * @access	public
	 * @return	array   new templateid from template creation
	 */	
	function save()
	{
		// Check if the template wasn't initialized from an existing one
		if ($this->template == NULL) // If it wasn't, create a new template
		{
			$this->new_template['templateid'] = $this->CI->templates_model->add($this->new_template['title'], $this->new_template['type'], $this->new_template['pageid']);
			$this->template = $this->new_template;
		}
		else // If it was, update the existing template
		{		
			// If the template's handling of post names changes, change all pages the template affects
			if ($this->new_template['pagepostname'] != $this->template['pagepostname'])
			{
				$this->CI->load->helper('template');
				set_page_post_urlnames();
			}
			
			$this->CI->templates_model->update($this->template['templateid'], $this->new_template);
			$this->template = $this->new_template;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set default
	 *
	 * Sets a template as a default or not
	 *
	 * @access	public
	 * @param	int      pageid
	 * @param   boolean  enabled
	 * @return	void
	 */	
	function set_default($pageid, $enabled = TRUE)
	{
		$this->CI->templates_model->remove_default_template($pageid, $this->new_template['type']);
		if ($enabled)
		{
			$this->CI->templates_model->set_default_template($this->template['templateid'], $pageid);
		}
		$this->CI->load->helper('template');
		set_page_post_urlnames();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set values
	 *
	 * Saves the template values for a particular page or post
	 *
	 * @access	public
	 * @param	array   values
	 * @return	void
	 */	
	function set_values($values, $attachedid)
	{
		$modified = FALSE;
	
		foreach ($values as $htmlcode => $value)
		{
			if (!isset($this->values[$htmlcode]) || $this->values[$htmlcode] != $value)
			{
				$modified = TRUE;
			}
		}
	
		if ($modified)
		{
			$this->CI->templates_model->delete_values($this->template['templateid'], $this->template['type'], $attachedid);
			foreach ($values as $htmlcode => $value)
			{
				$this->CI->templates_model->add_value($this->template['templateid'], $this->template['type'], $attachedid, $htmlcode, $value);
			}
			
			// Since we've modified the template values for a page or post, update the date modified on that page or post
			$object = instantiate_library($this->template['type'], $attachedid);
			if ($this->template['type'] == "page")
			{
				$object->new_page['datemodified'] = date('Y-m-d H:i:s');
			}
			else
			{
				$object->new_post['datemodified'] = date('Y-m-d H:i:s');
			}
			$object->save();
		}
	}
}