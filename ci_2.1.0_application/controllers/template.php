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
class Template extends MY_Controller {

	function Template()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->load->model('templates_model');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		$this->template = instantiate_library('template', $this->uri->segment(2));
		if (!isset($this->template->template['templateid']))
		{
			$this->_page_not_found();
		}
		else if ($this->template->template['pageid'] != 0 && !$this->acl->allow('page', 'templates', TRUE, 'page', $this->template->template['pageid']))
		{
			// User doesn't have rights to edit the page template
			$this->_access_denied();
		}
		else if ($this->template->template['pageid'] == 0 && !$this->acl->allow('site', 'manage_content', TRUE))
		{
			// User doesn't have rights to edit site-wide templates
			$this->_access_denied();
		}
		else
		{
			// Set required fields
			$this->required = array();
			array_push($this->required, array('name' => $this->template->template['type'].' content', 'htmlcode' => 'contenthere', 'custom' => FALSE));
			if ($this->template->template['type'] == "post")
			{
				array_push($this->required, array('name' => 'Comments', 'htmlcode' => 'commentshere', 'custom' => FALSE));
				array_push($this->required, array('name' => 'Listed files', 'htmlcode' => 'fileshere', 'custom' => FALSE));
				array_push($this->required, array('name' => 'Post references', 'htmlcode' => 'referenceshere', 'custom' => FALSE));
			}
			foreach ($this->template->fields as $field)
			{
				$field['custom'] = TRUE;
				array_push($this->required, $field);
			}

			// Set optional fields
			$this->optional = array();
			array_push($this->optional, array('name' => $this->template->template['type'].' title', 'htmlcode' => 'titlehere', 'custom' => FALSE));
			if ($this->template->template['type'] == "post")
			{
				array_push($this->optional, array('name' => 'Pingbacks', 'htmlcode' => 'pingbackshere', 'custom' => FALSE));
			}
			array_push($this->optional, array('name' => 'Featured image', 'htmlcode' => 'featuredimage', 'custom' => FALSE));
			array_push($this->optional, array('name' => 'Listed images', 'htmlcode' => 'listedimages', 'custom' => FALSE));

			$method = $this->uri->segment(3);
			if ($method == "edit" || $method == "field" || $method == "fields")
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
		$this->packages['tinymce'] = array('weight' => '3');

		// Tack on blocks java script array
		$this->load->model('blocks_model');
		$all_blocks = array();
		if ($this->template->template['pageid'] != 0)
		{
			$page = instantiate_library('page', $this->template->template['pageid']);
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
			$this->packages['tinymce_blocks'] = array('weight' => '2', 'properties' => array('blocks' => $all_blocks));
		}

		$data['template'] = $this->template->template;
		$data['required'] = $this->required;
		$data['optional'] = $this->optional;

		$this->_initialize_page('template', 'Edit template '.$this->template->template['title'], $data);
	}

	function edit()
	{
		$this->form_validation->set_rules('title', 'title', '|min_length[2]|max_length[100]|callback_templatetitle_check');
		$this->form_validation->set_rules('content', 'content', 'required|max_length[65000]|callback_template_check');
		$this->form_validation->set_rules('simple', 'simple', 'xss_clean|strip_tags');
		$this->form_validation->set_rules('pagepostname', 'posts page urlname', 'xss_clean|strip_tags');

		if ($this->form_validation->run())
		{
			if ($_POST['buttonchoice'] == "delete")
			{
				$this->template->delete();
			}
			else
			{
				$this->template->new_template['title'] = set_value('title');
				$this->template->new_template['content'] = html_entity_decode(set_value('content'), ENT_QUOTES);
				$this->template->new_template['simple'] = set_value('simple');
				if ($this->template->template['type'] == "page")
				{
					$this->template->new_template['pagepostname'] = set_value('pagepostname');
				}
				$this->template->save();
			}

			if ($this->template->template['pageid'] != 0)
			{
				$page = instantiate_library('page', $this->template->template['pageid']);
				redirect($page->page['urlname'].'/addtemplates');
			}
			else
			{
				redirect('manage_content/templates');
			}
		}
		else
		{
			$this->index();
		}
	}

	function template_check($str)
	{
		if (sizeof($str) > 0)
		{
			$errormessage = "";

			// Test to ensure all additional fields that have been created for this template are in the code
			foreach ($this->required as $field)
			{
				if (!preg_match('/%'.$field['htmlcode'].'(\[[a-z0-9,]+\])?%/i', $str))
				{
					$errormessage .= " &#37;".$field['htmlcode']."&#37;";
				}
			}

			if ($errormessage != "")
			{
				$this->form_validation->set_message('template_check', "The template does not contain the HTML".$errormessage);
				return FALSE;
			}
			return TRUE;
		}
		else
		{
			return TRUE;
		}
	}

	function templatetitle_check($str)
	{
		$object = instantiate_library('template', $str, 'title');
		if (isset($object->template['templateid']) && $object->template['templateid'] != $this->template->template['templateid'])
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

	function field()
	{
		if (!isset($this->template->fields[$this->uri->segment(4)]))
		{
			$this->_page_not_found();
		}
		else
		{
			$this->field = $this->template->fields[$this->uri->segment(4)];
			$this->form_validation->set_rules('fieldname', 'name', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[100]|callback_fieldname_check');
			$this->form_validation->set_rules('fieldcode', 'HTML code', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[20]|callback_fieldcode_check');
			$this->form_validation->set_rules('fieldtype', 'form type', 'xss_clean');
			$this->form_validation->set_rules('fieldrequired', 'required', 'xss_clean');

			if ($this->form_validation->run())
			{
				if ($_POST['buttonchoice'] == "delete")
				{
					$this->template->delete_field($this->field['htmlcode']);
				}
				else
				{
					$this->template->edit_field($this->field['htmlcode'], set_value('fieldname'), set_value('fieldcode'), set_value('fieldtype'), set_value('fieldrequired'));
				}
				redirect('template/'.$this->template->template['templateid']);
			}
			else
			{
				$data['template'] = $this->template->template;
				$data['field'] = $this->field;

				$this->_initialize_page('template_field', 'Edit template field '.$this->field['name'], $data);
			}
		}
	}

	function fields()
	{
		$this->form_validation->set_rules('fieldname', 'name', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[20]|callback_fieldname_check');
		$this->form_validation->set_rules('fieldcode', 'HTML code', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[10]|callback_fieldcode_check');
		$this->form_validation->set_rules('fieldtype', 'form type', 'xss_clean|strip_tags|trim|required');
		$this->form_validation->set_rules('fieldrequired', 'required', 'xss_clean');

		if ($this->form_validation->run())
		{
			$this->template->add_field(set_value('fieldname'), set_value('fieldcode'), set_value('fieldtype'), set_value('fieldrequired'));

			redirect('template/'.$this->template->template['templateid']);
		}
		else
		{
			$this->index();
		}
	}

	function fieldname_check($str)
	{
		$result = $this->templates_model->get_field_by_name($this->template->template['templateid'], $str);
		if ($result != NULL && $result['htmlcode'] != $this->field['htmlcode'])
		{
			$this->form_validation->set_message('fieldname_check', "The template field name $str is in use, please try a new name name.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	function fieldcode_check($str)
	{
		$result = $this->templates_model->get_field_by_htmlcode($this->template->template['templateid'], $str);
		if ($result != NULL && $result['name'] != $this->field['name'])
		{
			$this->form_validation->set_message('fieldcode_check', "The template field HTML code $str is in use, please try a new HTML code.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9]+$/i', $str))
		{
			$this->form_validation->set_message('fieldcode_check', "The template field HTML code must be made of only letters, and numbers.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}