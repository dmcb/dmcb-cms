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
class Block extends MY_Controller {

	function Block()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		$this->block = instantiate_library('block', $this->uri->segment(2));
		if (!isset($this->block->block['blockinstanceid']))
		{
			$this->_page_not_found();
		}
		else if ($this->block->block['pageid'] != 0 && !$this->acl->allow('page', 'blocks', TRUE, 'page', $this->block->block['pageid']))
		{
			// User doesn't have rights to edit the page block
			$this->_access_denied();
		}
		else if ($this->block->block['pageid'] == 0 && !$this->acl->allow('site', 'manage_content', TRUE))
		{
			// User doesn't have rights to edit site-wide blocks
			$this->_access_denied();		
		}
		else
		{
			$method = $this->uri->segment(3);
			if ($method == "edit")
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
		// Add editing packages to page
		$data['packages_editing'] = $this->load->view('packages_editing', NULL, TRUE);	
			
		// Tack on blocks java script array
		$this->load->model('blocks_model');
		$all_blocks = array();
		if ($this->block->block['pageid'] != 0)
		{
			$page = instantiate_library('page', $this->block->block['pageid']);
			$page->initialize_page_tree();
			foreach ($page->page_tree as $pageid)
			{
				$blockids = $this->blocks_model->get_page_blocks($pageid);
				foreach ($blockids->result_array() as $blockid)
				{
					$object = instantiate_library('block', $blockid['blockinstanceid']);
					array_push($all_blocks, $object->block);
				}
			}
		}
		$blockids = $this->blocks_model->get_page_blocks(0);
		foreach ($blockids->result_array() as $blockid)
		{
			$object = instantiate_library('block', $blockid['blockinstanceid']);
			array_push($all_blocks, $object->block);
		}
		
		if (sizeof($all_blocks) > 0)
		{
			$data['packages_tinymce_blocks'] = $this->load->view('packages_tinymce_blocks', array('blocks' => $all_blocks), TRUE);
		}

		$data['block'] = $this->block->block;
		
		$this->_initialize_page('block', 'Edit block '.$this->block->block['title'], $data);
	}
	
	function edit()
	{
		// Set fields and dynamically generate all variable fields
		$this->form_validation->set_rules('blocktitle',' title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[20]|callback_blocktitle_check');
		$this->form_validation->set_rules('feedback',' title', 'xss_clean');
		
		foreach ($this->block->block['variables']->result_array() as $variable)
		{
			$variablename = $variable['variablename'];
			$rulestring = "trim|max_length[2000]";
			
			if ($variable['pattern'] != "+") // Any non-text box field should do xss_clean
			{
				$rulestring .= "|xss_clean|strip_tags";
			}
			
			// If there are custom rules defined, use them, otherwise use default
			if (isset($variable['rules']))
			{
				$rulestring .= "|".$variable['rules'];
			}
			else
			{
				$rulestring .= "|callback_blockvalue_check";
			}
			$this->form_validation->set_rules($variablename, $variablename, $rulestring);
			
			// If the variable allows for a selection choice OR a specific text input, add a specify field
			if ($variable['pattern'] != "*" && strstr($variable['pattern'], '*'))
			{
				$this->form_validation->set_rules($variablename.'_specify', $variablename.'_specify', $rulestring);
			}
		}
			
		if ($this->form_validation->run())
		{
			if ($_POST['buttonchoice'] == "delete")
			{
				$this->block->delete();
			}
			else
			{			
				$this->block->new_block['title'] = set_value('blocktitle');
				$this->block->new_block['feedback'] = set_value('feedback');
				$this->block->new_block['values'] = array();
				foreach ($this->block->block['variables']->result_array() as $variable)
				{
					$variablename = $variable['variablename'];
					$variablename_specify = $variablename."_specify";
					if (set_value($variablename_specify) != "")
					{
						$this->block->new_block['values'][$variablename] = html_entity_decode(set_value($variablename_specify), ENT_QUOTES);
					}
					else
					{
						$this->block->new_block['values'][$variablename] = html_entity_decode(set_value($variablename), ENT_QUOTES);
					}
				}
				$this->block->save();
			}
			
			if ($this->block->block['pageid'] != 0)
			{
				$page = instantiate_library('page', $this->block->block['pageid']);
				redirect($page->page['urlname'].'/blocks');
			}
			else
			{
				redirect('manage_content/blocks');
			}
		}
		else
		{
			$this->index();
		}
	}
	
	function blockvalue_check($str)
	{
		if ($str == "")
		{
			return TRUE;
		}
		if (!preg_match('/^[a-z0-9-_;]+$/i', $str))
		{
			$this->form_validation->set_message('blockvalue_check', "The value must be made of only letters, numbers, dashes, underscores and semi-colons to seperate multiple entries.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|^;+|\-+$|\_+$|;+$/i', $str))
		{
			$this->form_validation->set_message('blockvalue_check', "The value cannot start or end with dashes, underscores or semi-colons.");
			return FALSE;
		}
		else
		{
			return TRUE;
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
}