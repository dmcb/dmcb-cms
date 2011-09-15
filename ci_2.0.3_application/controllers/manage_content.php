<?php

class Manage_content extends MY_Controller {

	function Manage_content()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	function _remap()
	{
		if ($this->acl->allow('site', 'manage_content', TRUE) || $this->_access_denied())
		{
			$this->load->model(array('blocks_model', 'files_model', 'templates_model'));
		
			$method = $this->uri->segment(2);
		    if ($method == "attachments" || $method == "blocks" || $method == "manageblocks" || $method == "templates")
		    {
				$this->focus = $method;
		        $this->$method();
		    }
		    else
		    {
				$this->index();
		    }
		}
	}
	
	function index()
	{
		$data['packages_upload'] = $this->load->view('packages_upload', 
			array(
				'upload_url' => 'site', 
				'upload_size' => $this->config->item('dmcb_site_upload_size'),
				'upload_types' => $this->config->item('dmcb_site_upload_types'), 
				'upload_description' => $this->config->item('dmcb_site_upload_description')
			), TRUE);
		
		// Grab attachments
		$data['files'] = array();
		$fileids = $this->files_model->get_attached("site");
		foreach ($fileids->result_array() as $fileid)
		{
			$object = instantiate_library('file', $fileid['fileid']);
			array_push($data['files'], $object->file);
		}
		$data['stockimages'] = array();
		$stockimageids = $this->files_model->get_stockimages();
		foreach ($stockimageids->result_array() as $stockimageid)
		{
			$data['stockimages'][$stockimageid['fileid']] = 1;
		}
		
		// Grab block instances & functions
		$data['blocks'] = array();
		$blockids = $this->blocks_model->get_page_blocks('0');
		foreach ($blockids->result_array() as $blockid)
		{
			$object = instantiate_library('block', $blockid['blockinstanceid']);
			array_push($data['blocks'], $object->block);
		}
		$data['functions'] = $this->blocks_model->get_functions_enabled();
		$data['availablefunctions'] = $this->blocks_model->get_functions_disabled();
		
		// Grab templates
		$data['templates'] = array();
		$templateids = $this->templates_model->get_attached(0);
		foreach ($templateids->result_array() as $templateid)
		{
			$object = instantiate_library('template', $templateid['templateid']);
			array_push($data['templates'], $object->template);
		}		
		
		$this->load->helper('template');

		// Grab default templates and blocks
		$this->load->helper('template');
		$data['default_templates'] = array();
		$default_templateids = $this->templates_model->get_defaults(0);
		foreach ($default_templateids->result_array() as $default_templateid)
		{
			$data['default_templates'][$default_templateid['templateid']] = TRUE;
		}
	
		$data['default_blocks'] = array();
		$default_blockids = $this->blocks_model->get_defaults(0);
		foreach ($default_blockids->result_array() as $default_blockid)
		{
			$data['default_blocks'][$default_blockid['blockinstanceid'].$default_blockid['type']] = TRUE;
		}

		$this->_initialize_page('manage_content', 'Manage content', $data);
	}
	
	function attachments()
	{
		$this->attachment = instantiate_library('file', $this->uri->segment(4));
		// Ensure attachment selected is attached to site and not something else
		if ($this->uri->segment(4) != "" && (!isset($this->attachment->file['fileid']) || $this->attachment->file['attachedto'] != "site"))
		{
			$this->_access_denied();
		}
		else if ($this->uri->segment(3) == "delete")
		{
			$this->attachment->delete();
			redirect('manage_content/attachments');
		}
		else if ($this->uri->segment(3) == "removestock")
		{
			$this->files_model->remove_stockimage($this->attachment->file['fileid']);
			redirect('manage_content/attachments');
		}
		else if ($this->uri->segment(3) == "rename")
		{
			$this->form_validation->set_rules('filename', 'file name', 'xss_clean|strip_tags|trim|required|max_length[100]|callback_filename_check');
			
			if ($this->form_validation->run())
			{
				$this->attachment->new_file['filename'] = set_value('filename');
				$this->attachment->save();
				redirect('manage_content/attachments');
			}
			else
			{
				$this->_initialize_page('file_rename',"Rename file",array('attachment' => $this->attachment->file));
			}
		}
		else if ($this->uri->segment(3) == "setstock")
		{
			$this->files_model->set_stockimage($this->attachment->file['fileid']);
			redirect('manage_content/attachments');
		}
		else
		{
			$this->index();
		}
	}
	
	function filename_check($str)
	{
		$object = instantiate_library('file', array($str, $this->attachment->file['extension'], $this->attachment->file['attachedto'], $this->attachment->file['attachedid']), 'details');
		if (isset($object->file['fileid']) && $object->file['fileid'] != $this->attachment->file['fileid'])
		{
			$this->form_validation->set_message('filename_check', "The file name $str is in use, please try a new file name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('filename_check', "The file name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|\_+$/i', $str))
		{
			$this->form_validation->set_message('filename_check', "The file name cannot start or end with underscores.");
			return FALSE;
		}
		else
			return TRUE;
	}

	function blocks()
	{
		$this->form_validation->set_rules('blocktitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[20]|callback_blocktitle_check');
		$this->form_validation->set_rules('blockfunction', 'function', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[20]');
		
		if ($this->form_validation->run())
		{
			$this->load->library('block_lib','','new_block');
			$this->new_block->new_block['pageid'] = '0';
			$this->new_block->new_block['title'] = set_value('blocktitle');
			$this->new_block->new_block['function'] = set_value('blockfunction');
			$this->new_block->save();
			redirect('manage_content/blocks/edit/'.$this->new_block->new_block['blockinstanceid']);
		}
		else
		{
			// Load up edited block
			$this->block = instantiate_library('block', $this->uri->segment(4));
			
			// Ensure block selected is attached to site and not something else
			if ($this->uri->segment(4) != NULL && (!isset($this->block->block['blockinstanceid']) || $this->block->block['pageid'] != "0"))
			{
				$this->_access_denied();
			}
			else if ($this->uri->segment(3) == "removepagination")
			{
				$this->block->set_default(0, 'pagination', FALSE);
				redirect('manage_content/blocks');
			}
			else if ($this->uri->segment(3) == "setpagination")
			{
				$this->block->set_default(0, 'pagination');
				redirect('manage_content/blocks');
			}
			else if ($this->uri->segment(3) == "removerss")
			{
				$this->block->set_default(0, 'rss', FALSE);
				redirect('manage_content/blocks');
			}
			else if ($this->uri->segment(3) == "setrss")
			{
				$this->block->set_default(0, 'rss');
				redirect('manage_content/blocks');
			}
			else
			{
				$this->index();
			}
		}
	}

	function blocktitle_check($str)
	{
		$object = instantiate_library('block', $str, 'title');
		if (isset($object->block['blockinstanceid']) && (!isset($this->block->block['blockinstanceid']) || $object->block['blockinstanceid'] != $this->block->block['blockinstanceid']))
		{
			$this->form_validation->set_message('blocktitle_check', "The block title $str is in use, please try a new block name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('blocktitle_check', "The block title must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function manageblocks()
	{
		if ($this->uri->segment(3) == "enable")
		{
			$this->blocks_model->enable_block($this->uri->segment(4));
		}
		if ($this->uri->segment(3) == "disable")
		{
			$this->blocks_model->disable_block($this->uri->segment(4));
		}
		$this->index();
	}
	
	function templates()
	{
		$this->template = instantiate_library('template', $this->uri->segment(4));
		// Ensure template selected is attached to site and not something else
		if ($this->uri->segment(4) != "" && (!isset($this->template->template['templateid']) || $this->template->template['pageid'] != 0))
		{
			$this->_access_denied();
		}
		else if ($this->uri->segment(3) == "removedefault")
		{
			$this->template->set_default(0, FALSE);
			redirect('manage_content/templates');
		}
		else if ($this->uri->segment(3) == "setdefault")
		{
			$this->template->set_default(0);
			redirect('manage_content/templates');
		}
		else
		{
			$this->form_validation->set_rules('templatetitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]|callback_templatetitle_check');
			$this->form_validation->set_rules('templatetype', 'type', 'xss_clean|strip_tags|trim|required');
			
			if ($this->form_validation->run())
			{
				$this->load->library('template_lib','','new_template');
				$this->new_template->new_template['title'] = set_value('templatetitle');
				$this->new_template->new_template['type'] = set_value('templatetype');
				$this->new_template->new_template['attachedto'] = 'site';
				$this->new_template->new_template['attachedid'] = NULL;
				$this->new_template->save();
				redirect('template/'.$this->new_template->new_template['templateid']);
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function templatetitle_check($str)
	{
		$object = instantiate_library('template', $str, 'title');
		if (isset($object->template['templateid']))
		{
			$this->form_validation->set_message('templatetitle_check', "The template title $str is in use, please try a new template name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9- ]+$/i', $str))
		{
			$this->form_validation->set_message('templatetitle_check', "The template title must be made of only letters, numbers, dashes, and spaces.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}
?> 