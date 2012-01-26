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
class Page extends MY_Controller {

	function Page()
	{
		parent::__construct();

		$this->load->helper('picture');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model(array('blocks_model', 'pages_model'));
	}

	function _remap()
	{
		$this->data = array();

		// If subscriptions enabled, load model
		if ($this->acl->enabled('site', 'subscribe'))
		{
			$this->load->model('subscriptions_model');
		}

		// Crawl through URI segments, matching deepest nested possible URL name
		$this->base_segment = 1;
		$this->page_urlname = NULL;
		$urlname_to_test = $this->uri->segment($this->base_segment);
		$object = instantiate_library('page', $urlname_to_test, 'urlname');

		while (isset($object->page['pageid']) && $this->base_segment<=$this->uri->total_segments())
		{
			$this->page_urlname = $urlname_to_test;
			$this->base_segment++;
			$urlname_to_test .= '/'.$this->uri->segment($this->base_segment);
			$object = instantiate_library('page', $urlname_to_test, 'urlname');
		}
		$this->base_segment--;

		if ($this->uri->total_segments() == 0) // If no page specified, choose default
		{
			$this->page = instantiate_library('page', $this->config->item('dmcb_default_page'), 'urlname');
		}
		else
		{
			$this->page = instantiate_library('page', $this->page_urlname, 'urlname');
			// If we did load up a page and it's the default page, drop it's name from the URL (unless we are posting to the page and/or there's parameters after the URL)
			if (isset($this->page->page['pageid']) && $this->page->page['urlname'] == $this->config->item('dmcb_default_page') && $_SERVER['REQUEST_METHOD'] !== 'POST' && $this->uri->total_segments() == $this->base_segment)
			{
				redirect(base_url());
			}
		}

		// Page not found
		if (!isset($this->page->page['pageid']))
		{
			// If there is a placeholder for the page, point them to the new URL
			$this->load->model('placeholders_model');
			$placeholder = NULL;
			$i=$this->uri->total_segments();
			while ($placeholder == NULL && $i >= $this->base_segment)
			{
				$urlname_to_test = "";
				for ($j=1; $j<=$i; $j++)
				{
					$urlname_to_test .= $this->uri->segment($j);
					if ($j<$i)
					{
						$urlname_to_test .= '/';
					}
				}
				$placeholder = $this->placeholders_model->get('page', $urlname_to_test);
				$i--;
			}

			if ($placeholder != NULL)
			{
				$this->_redirect(base_url().$placeholder['newname'], $placeholder['redirect']);
			}
			else
			{
				$this->_page_not_found();
			}
		}
		else if (!$this->page->page['published'] && !$this->acl->allow('page', 'edit', TRUE, 'page', $this->page->page['pageid']))
		{
			// Page is not published and the person isn't allowed to edit the page, so deny them
			$this->_access_denied();
		}
		else if ($this->page->page['published'] && $this->page->page['protected'] && !$this->acl->allow('page', 'edit', TRUE, 'page', $this->page->page['pageid']) && !$this->acl->access($this->page->page['protection'], $this->page, NULL, TRUE))
		{
			// Page is published, but the page is protected and the user isn't allowed to view or edit the page, so deny them
			$this->_access_denied();
		}
		else if ($this->acl->enabled('site', 'subscribe') && $this->page->page['needsubscription'] && !$this->subscriptions_model->check($this->session->userdata('userid')) && !$this->acl->allow('page', 'edit', FALSE, 'page', $this->page->page['pageid']))
		{
			// Subscriptions are enabled, this page requires one, and the user isn't allowed to edit the page and doesn't have a subscription, so deny them
			$this->data['message'] = "'".$this->page->page['title']."' requires a subscription to view.";

			if ($this->session->userdata('signedon'))
			{
				$this->data['message'] .= "<br/><br/>Your subscription has ended, you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
			}
			else
			{
				$this->data['message'] .= "<br/><br/>If you do have a subscription, please <a href=\"".base_url()."signon".uri_string()."\">sign on</a>.<br/>If you don't have a subscription, you can start a free trial by creating an account <a href=\"".base_url()."signon\">here</a>, or you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
			}
			$this->_message("Subscription required", $this->data['message']);
		}
		else
		{
			// Get user roles for permissions
			if ($this->acl->allow('page', 'permissions', FALSE, 'page'))
			{
				$this->load->model('acls_model');
				$this->roles = $this->acls_model->get_roles();
				$this->userlist = array();
				// Store roles for use later
				$this->roles_table = array();
				foreach ($this->roles->result_array() as $role)
				{
					$this->roles_table[$role['roleid']] = $role['role'];
					$users = array();
					$items = $this->acls_model->get_userlist_by_role($role['roleid'], 'page', $this->page->page['pageid']);
					foreach($items->result_array() as $item)
					{
						$object = instantiate_library('user', $item['userid']);
						$object->user['roleid'] = $role['roleid'];
						array_push($users, $object->user);
					}
					array_push($this->userlist, $users);
				}
			}

			// Initialize array of page's parents
			$this->page->initialize_page_tree();

			// Grab this page's template and values
			if (isset($this->page->page['page_templateid']))
			{
				$templateid = $this->page->page['page_templateid'];
			}
			else
			{
				$this->load->helper('template');
				$templateid = template_to_use('template', 'page', $this->page->page_tree);
			}
			$this->template = instantiate_library('template', $templateid);
			$this->template->initialize_values($this->page->page['pageid']);

			// Grab this page's pagination
			if (isset($this->page->page['pagination_blockid']))
			{
				$this->pagination_blockid = $this->page->page['pagination_blockid'];
			}
			else
			{
				$this->load->helper('template');
				$this->pagination_blockid = template_to_use('block', 'pagination', $this->page->page_tree);
			}

			// Grab this page's RSS
			if (isset($this->page->page['rss_blockid']))
			{
				$this->rss_blockid = $this->page->page['rss_blockid'];
			}
			else
			{
				$this->load->helper('template');
				$this->rss_blockid = template_to_use('block', 'rss', $this->page->page_tree);
			}

			$method = $this->uri->segment($this->base_segment+1);
			if ($method == "addpage" || $method == "addpost" || $method == "addtemplates" || $method == "attachments" || $method == "blocks" || $method == "editpage" || $method == "permissions" || $method == "settemplate")
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
		// Render RSS feed if it exists and is called upon
		$object = instantiate_library('block', $this->rss_blockid);
		if (isset($object->block['blockinstanceid']) && $this->uri->segment($this->base_segment+1) == "rss") // Render RSS feed
		{
			$object->block['feedback'] = FALSE;
			$data['rsscontent'] = $object->output_rss();
			$data['date'] = $object->last_modified;
			$data['feed'] = base_url().$this->page->page['urlname'].'/rss';
			$this->_initialize_rss($this->page->page['title'], $data);
		}
		else // Render page
		{
			// Add RSS feed
			if (isset($object->block['blockinstanceid']))
			{
				$data['rss'] = $this->load->view('rssfeed', array('feed' => base_url().$this->page->page['urlname'].'/rss'), TRUE);
			}

			// Generate page by parsing through template and page content, loading up blocks as necessary
			$data['pagecontent'] = "";

			// If page was reached via search, highlight the searched word
			if ($this->uri->segment($this->base_segment+1) == "search" && $this->session->flashdata('search_term'))
			{
				// Keep search highlighting going (in the event user uses back button to go back to search results)
				$this->session->keep_flashdata('search_term');

				$this->page->page['content'] = preg_replace('/('.$this->session->flashdata('search_term').')(?![^<]*>)(?![\S]*%)/i', $this->load->view('content_highlight', array('content' => '$1'), TRUE), $this->page->page['content']);
			}

			$contents_to_parse = $this->page->page['content'];
			if (isset($this->template->template['templateid']))
			{
				$contents_to_parse = $this->template->template['content'];
				foreach ($this->template->fields as $field)
				{
					if (isset($this->template->values[$field['htmlcode']]))
					{
						$value = $this->template->values[$field['htmlcode']];
					}
					else
					{
						$value = "";
					}
					$contents_to_parse = str_replace('%'.$field['htmlcode'].'%', $value, $contents_to_parse);
				}
				$contents_to_parse = str_replace('%contenthere%', $this->page->page['content'], $contents_to_parse);
				$contents_to_parse = str_replace('%titlehere%', $this->page->page['title'], $contents_to_parse);

				// Get page images
				$this->page->page['image'] = NULL;
				$file = instantiate_library('file', $this->page->page['imageid']);
				if (isset($file->file['fileid']))
				{
					$this->page->page['image'] = $file->file;
				}
				else
				{
					// Stock image code
					$stockimage = stock_image($this->page->page['pageid']);
					if ($stockimage != NULL)
					{
						$this->page->page['image'] = $stockimage;
					}
				}
				$this->page->page['images'] = array();
				$fileids = $this->files_model->get_attached_images('page', $this->page->page['pageid']);
				foreach ($fileids->result_array() as $fileid)
				{
					$file = instantiate_library('file', $fileid['fileid']);
					if (isset($file->file['fileid']))
					{
						array_push($this->page->page['images'], $file->file);
					}
				}

				$featuredimage_section = $this->load->view('page_image', array('pageid' => $this->page->page['pageid'], 'image' => $this->page->page['image']), TRUE);
				if ($this->page->page['image'] == NULL) $featuredimage_section = NULL;
				$listedimages_section = $this->load->view('page_images', array('pageid' => $this->page->page['pageid'], 'image' => $this->page->page['image'], 'images' => $this->page->page['images']), TRUE);

				$contents_to_parse = str_replace('%featuredimage%', $featuredimage_section, $contents_to_parse);
				$contents_to_parse = str_replace('%listedimages%', $listedimages_section, $contents_to_parse);
			}

			$pagecontents = preg_split('/(%block_\S+%)/', $contents_to_parse, -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($pagecontents as $pagecontent)
			{
				if (preg_match('/^%block_\S+%$/', $pagecontent))
				{
					$object = instantiate_library('block', preg_replace('/^%block_(\S+)%$/', '$1', $pagecontent), 'title');

					// If we have a block on the page that is paginated AND we are using it, make sure to focus to it
					if (isset($object->block['blockinstanceid']) && $object->block['blockinstanceid'] == $this->pagination_blockid)
					{
						$object->pagination = TRUE;
						$this->load->helper('pagination_helper');
						if (get_pagination_uri() != NULL)
						{
							$this->focus = "pagination_block";
						}
					}

					$data['pagecontent'] .= $object->output($this->page->page_tree);
				}
				else
				{
					$data['pagecontent'] .= $pagecontent;
				}
			}

			// Enable editing
			if ($this->acl->allow('page', 'edit', FALSE, 'page', $this->page->page['pageid']))
			{
				$data['packages_editing'] = $this->load->view('packages_editing', NULL, TRUE);

				// Block instances
				if ($this->acl->enabled('page', 'blocks'))
				{
					// Grab default blocks
					$default_blocks = array();
					$default_blockids = $this->blocks_model->get_defaults($this->page->page['pageid']);
					foreach ($default_blockids->result_array() as $default_blockid)
					{
						$default_blocks[$default_blockid['blockinstanceid'].$default_blockid['type']] = TRUE;
					}

					// Grab all block instances this page is allowed to use for TinyMCE editor
					$all_blocks = array();
					foreach ($this->page->page_tree as $pageid)
					{
						$blockids = $this->blocks_model->get_page_blocks($pageid);
						foreach ($blockids->result_array() as $blockid)
						{
							$object = instantiate_library('block', $blockid['blockinstanceid']);
							array_push($all_blocks, $object->block);
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

					// Sort all blocks for editing
					if ($this->acl->allow('page', 'blocks', FALSE, 'page', $this->page->page['pageid']))
					{
						$editable_blocks = array();
						$non_editable_blocks = array();

						foreach ($all_blocks as $block)
						{
							if ($block['pageid'] == $this->page->page['pageid'])
							{
								array_push($editable_blocks, $block);
							}
							else
							{
								array_push($non_editable_blocks, $block);
							}
						}
						$page_blocks = array_merge($editable_blocks, $non_editable_blocks);

						$data['edit_blocks'] = $this->load->view('form_page_blocks', array('page' => $this->page->page, 'blocks' => $page_blocks, 'functions' => $this->blocks_model->get_functions_enabled(), 'default_blocks' => $default_blocks), TRUE);
					}
				}

				// Page and post templates
				if ($this->acl->allow('page', 'templates', FALSE, 'page', $this->page->page['pageid']))
				{
					// Grab default templates
					$this->load->helper('template');
					$this->load->model('templates_model');
					$default_templates = array();
					$default_templateids = $this->templates_model->get_defaults($this->page->page['pageid']);
					foreach ($default_templateids->result_array() as $default_templateid)
					{
						$default_templates[$default_templateid['templateid']] = TRUE;
					}

					// Grab all templates this page is allowed to use
					$all_templates = array();
					foreach ($this->page->page_tree as $pageid)
					{
						$templateids = $this->templates_model->get_attached($pageid);
						foreach ($templateids->result_array() as $templateid)
						{
							$object = instantiate_library('template', $templateid['templateid']);
							array_push($all_templates, $object->template);
						}
					}
					$templateids = $this->templates_model->get_attached(0);
					foreach ($templateids->result_array() as $templateid)
					{
						$object = instantiate_library('template', $templateid['templateid']);
						array_push($all_templates, $object->template);
					}

					// Sort all templates for editing
					$all_page_templates = array();
					$all_post_templates = array();
					$editable_page_templates = array();
					$editable_post_templates = array();

					foreach ($all_templates as $template)
					{
						if ($template['type'] == "page")
						{
							array_push($all_page_templates, $template);
							if ($template['pageid'] == $this->page->page['pageid'])
							{
								array_push($editable_page_templates, $template);
							}
						}
						else
						{
							array_push($all_post_templates, $template);
							if ($template['pageid'] == $this->page->page['pageid'])
							{
								array_push($editable_post_templates, $template);
							}
						}
					}
					$all_templates = array_merge($all_page_templates, $all_post_templates);
					$editable_templates = array_merge($editable_page_templates, $editable_post_templates);

					$data['set_template'] = $this->load->view('form_page_settemplate', array('page' => $this->page->page, 'template_in_use' => $this->template, 'templates' => $all_templates, 'default_templates' => $default_templates), TRUE);
					$data['add_templates'] = $this->load->view('form_page_addtemplates', array('page' => $this->page->page, 'templates' => $editable_templates), TRUE);
				}

				// Set editor type from template
				$simple_editor = FALSE;
				if (isset($this->template->template['simple']) && $this->template->template['simple'])
				{
					$simple_editor = TRUE;
				}
				$data['edit_page'] = $this->load->view('form_page_editpage', array('page' => $this->page->page, 'fields' => $this->template->fields, 'values' => $this->template->values, 'simple_editor' => $simple_editor), TRUE);
			}

			// Enable attachment editing
			if ($this->acl->allow('page', 'attachments', FALSE, 'page', $this->page->page['pageid']))
			{
				$data['packages_upload'] = $this->load->view('packages_upload',
					array(
						'upload_url' => 'page/'.$this->page->page['urlname'],
						'upload_size' => $this->config->item('dmcb_site_upload_size'),
						'upload_types' => $this->config->item('dmcb_site_upload_types'),
						'upload_description' => $this->config->item('dmcb_site_upload_description')
					), TRUE);

				// Grab attachments
				$this->load->model('files_model');
				$files = array();
				$fileids = $this->files_model->get_attached("page",$this->page->page['pageid']);
				foreach ($fileids->result_array() as $fileid)
				{
					$object = instantiate_library('file', $fileid['fileid']);
					array_push($files, $object->file);
				}
				// Grab stock images, if we have multiple, we will let the user choose if they want to set any as the post image
				$stockimages = array();
				$stockimageids = $this->files_model->get_stockimages();
				foreach ($stockimageids->result_array() as $stockimage)
				{
					$object = instantiate_library('file', $stockimage['fileid']);
					array_push($stockimages, $object->file);
				}
				$data['attachments'] = $this->load->view('form_page_attachments', array('files' => $files, 'stockimages' => $stockimages, 'page' => $this->page->page), TRUE);
			}

			// Adding child pages
			if ($this->acl->allow('page', 'addpage', FALSE, 'page', $this->page->page['pageid']))
			{
				$childpages = array();
				$childpageids = $this->pages_model->get_children($this->page->page['menu'], $this->page->page['pageid']);
				foreach ($childpageids->result_array() as $childpageid)
				{
					$object = instantiate_library('page', $childpageid['pageid']);
					array_push($childpages, $object->page);
				}
				$data['add_page'] = $this->load->view('form_page_addpage', array('page' => $this->page->page, 'childpages' => $childpages), TRUE);
			}

			// Adding posts
			if ($this->acl->allow('page', 'addpost', FALSE, 'page', $this->page->page['pageid']))
			{
				$data['add_post'] = $this->load->view('form_page_addpost', array('page' => $this->page->page), TRUE);

				// Grab drafts
				$this->load->model('posts_model');
				$drafts = array();
				$postids = $this->posts_model->get_page_drafts($this->page->page['pageid']);
				foreach ($postids->result_array() as $postid)
				{
					$object = instantiate_library('post', $postid['postid']);
					array_push($drafts, $object->post);
				}
				if (sizeof($drafts) > 0)
				{
					$data['edit_drafts'] = $this->load->view('form_page_editdrafts', array('drafts' => $drafts), TRUE);
				}
			}

			// Grab permissions
			if ($this->acl->allow('page', 'permissions', FALSE, 'page', $this->page->page['pageid']))
			{
				$data['permissions'] = $this->load->view('form_page_permissions', array('page' => $this->page->page, 'roles' => $this->roles, 'userlist' => $this->userlist), TRUE);
			}

			$data['page'] = $this->page->page;
			$data['base_segment'] = $this->base_segment;
			$this->_initialize_page('page', $this->page->page['title'], $data, TRUE);
		}
	}

	function addpage()
	{
		if ($this->acl->allow('page', 'addpage', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			if ($this->uri->segment($this->base_segment+2) == "publish")
			{
				$object = instantiate_library('page', $this->uri->segment($this->base_segment+3));
				$object->new_page['published'] = 1;
				$object->save();
				redirect($this->page->new_page['urlname'].'/addpage');

			}
			else if ($this->uri->segment($this->base_segment+2) == "unpublish")
			{
				$object = instantiate_library('page', $this->uri->segment($this->base_segment+3));
				$object->new_page['published'] = 0;
				$object->save();
				redirect($this->page->new_page['urlname'].'/addpage');
			}
			else
			{
				$this->form_validation->set_rules('pagetitle', 'title', 'xss_clean|strip_tags|trim|htmlentities|required|min_length[2]|max_length[50]');
				$this->form_validation->set_rules('pageurlname', 'url name', 'xss_clean|strip_tags|trim|strtolower|min_length[2]|max_length[55]|callback_childpageurlname_check');
				$this->form_validation->set_rules('nestedurl', 'nested url', 'xss_clean|strip_tags');

				if ($this->form_validation->run())
				{
					$this->load->library('page_lib',NULL,'new_page');
					$this->new_page->new_page['menu'] = $this->page->page['menu'];
					$this->new_page->new_page['pageof'] = $this->page->page['pageid'];
					$this->new_page->new_page['title'] = html_entity_decode(set_value('pagetitle'), ENT_QUOTES);
					$result = $this->new_page->save();

					$new_page = instantiate_library('page', $result);
					$new_page->new_page['urlname'] = set_value('pageurlname');

					// Give child same protection as parent
					$new_page->new_page['protection'] = $this->page->page['protection'];

					// If a nested URL is chosen
					if (set_value('nestedurl'))
					{
						$new_page->new_page['urlname'] = $this->page->page['urlname'].'/'.set_value('pageurlname');
					}
					$new_page->save();

					redirect($this->page->new_page['urlname'].'/addpage');
				}
				else
				{
					$this->index();
				}
			}
		}
	}

	function childpageurlname_check($str)
	{
		//Grab page methods to ensure page url can't be a page function
		$page_controller = fopen(APPPATH.'/controllers/page.php', 'r');
		$page_controller_contents = fread($page_controller, filesize(APPPATH.'/controllers/page.php'));
		fclose($page_controller);
		preg_match_all("/\s+function (\w*)\(.*\)/", $page_controller_contents, $functions);
		$page_controller_methods = implode("|",$functions[1]);

		if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('childpageurlname_check', "The url name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|\-+$|\_+$/i', $str))
		{
			$this->form_validation->set_message('childpageurlname_check', "The url name cannot start or end with dashes or underscores.");
			return FALSE;
		}
		else if (preg_match('/^[0-9]{8}$/', $str))
		{
			$this->form_validation->set_message('childpageurlname_check', "The url name cannot be 8 digits as this format is used by posts on the site.");
			return FALSE;
		}
		else if (strpos("|".$page_controller_methods."|".$this->config->item('dmcb_controllers')."|".$this->config->item('dmcb_page_controller')."|".$this->config->item('dmcb_post_controller')."|".$this->config->item('dmcb_reserved_names')."|", "|".$str."|") !== FALSE)
		{
			$this->form_validation->set_message('childpageurlname_check', "$str is a url name reserved by the website.");
			return FALSE;
		}
		else
		{
			// If a nested URL is chosen and a parent page is selected, add that URL name to the name we are testing
			if (isset($_POST['nestedurl']))
			{
				$str = $this->page->page['urlname'].'/'.$str;
			}

			// Check for name collisions and return suggested new name
			$suggestion = $this->page->suggest($str);
			if ($suggestion == $str)
			{
				return TRUE;
			}
			else
			{
				// If suggestion contains nested URL, remove it
				if (strrpos($suggestion, '/') != 0)
				{
					$suggestion = substr($suggestion, strrpos($suggestion, '/')+1);
				}
				$this->form_validation->set_message('childpageurlname_check', "The url name is in use.  We suggest $suggestion.");
				return FALSE;
			}
		}
	}

	function addpost()
	{
		if ($this->acl->allow('page', 'addpost', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('posttitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]');
			$this->form_validation->set_rules('posturlname', 'url name', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[35]|callback_posturlname_check');

			if ($this->form_validation->run())
			{
				$this->load->library('post_lib','','new_post');
				$this->new_post->new_post['pageid'] = $this->page->page['pageid'];
				$this->new_post->new_post['title'] = html_entity_decode(set_value('posttitle'), ENT_QUOTES);
				$this->new_post->new_post['urlname'] = set_value('posturlname');
				// If there's a parent page that specifies the post use it's name, use it, otherwise use the default date format
				if ($this->page->page['pagepostname'])
				{
					$this->new_post->new_post['urlname'] = $this->page->page['urlname'].'/post/'.$this->new_post->new_post['urlname'];
				}
				else
				{
					$this->new_post->new_post['urlname'] = date("Ymd").'/'.$this->new_post->new_post['urlname'];
				}
				$this->new_post->save();
				redirect($this->new_post->post['urlname'].'/editpost');
			}
			else
			{
				$this->index();
			}
		}
	}

	function posturlname_check($str)
	{
		if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('posturlname_check', "The url name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|\-+$|\_+$/i', $str))
		{
			$this->form_validation->set_message('posturlname_check', "The url name cannot start or end with dashes or underscores.");
			return FALSE;
		}
		else
		{
			// If there's a parent page that specifies the post use it's name, check against that, otherwise check against date form
			if (isset($this->page->page['pagepostname']) && $this->page->page['pagepostname'])
			{
				$str = $this->page->page['urlname'].'/post/'.$str;
			}
			else
			{
				$str = date("Ymd").'/'.$str;
			}

			// Check for name collisions and return suggested new name
			$this->load->library('post_lib','','test_post');
			$this->test_post->post['postid'] = '0';
			$suggestion = $this->test_post->suggest($str);
			if ($suggestion == $str)
			{
				return TRUE;
			}
			else
			{
				// Remove everything before slash for suggestion, so that suggested URL name is just the part the user has control over
				if (strrpos($suggestion, '/') != 0)
				{
					$suggestion = substr($suggestion, strrpos($suggestion, '/')+1);
				}
				$this->form_validation->set_message('posturlname_check', "The url name is in use.  We suggest $suggestion.");
				return FALSE;
			}
		}
	}

	function addtemplates()
	{
		if ($this->acl->allow('page', 'templates', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('templatetitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]|callback_templatetitle_check');
			$this->form_validation->set_rules('templatetype', 'type', 'xss_clean|strip_tags|trim|required');

			if ($this->form_validation->run())
			{
				$this->load->library('template_lib','','new_template');
				$this->new_template->new_template['title'] = set_value('templatetitle');
				$this->new_template->new_template['type'] = set_value('templatetype');
				$this->new_template->new_template['pageid'] = $this->page->page['pageid'];
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

	function attachments()
	{
		if ($this->acl->allow('page', 'attachments', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->attachment = instantiate_library('file', $this->uri->segment($this->base_segment+3));
			if ($this->uri->segment($this->base_segment+2) == "setimage" && ($this->files_model->check_stockimage($this->uri->segment($this->base_segment+3)) || ($this->attachment->file['attachedto'] == "page" && $this->attachment->file['attachedid'] == $this->page->page['pageid'])))
			{
				$this->page->new_page['imageid'] = $this->uri->segment($this->base_segment+3);
				$this->page->save();
				// Page images can't also be listed as downloads
				$this->attachment->new_file['listed'] = "0";
				$this->attachment->save();
				redirect($this->page->page['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "removeimage" && ($this->files_model->check_stockimage($this->uri->segment($this->base_segment+3)) || ($this->attachment->file['attachedto'] == "page" && $this->attachment->file['attachedid'] == $this->page->page['pageid'])))
			{
				$this->page->new_page['imageid'] = NULL;
				$this->page->save();
				redirect($this->page->page['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) != "" && ($this->attachment->file['attachedto'] != "page" || $this->attachment->file['attachedid'] != $this->page->page['pageid']))
			{
				$this->_access_denied();
			}
			else if ($this->uri->segment($this->base_segment+2) == "delete")
			{
				$this->attachment->delete();
				redirect($this->page->page['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "list")
			{
				$this->attachment->new_file['listed'] = "1";
				$this->attachment->save();
				redirect($this->page->page['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "unlist")
			{
				$this->attachment->new_file['listed'] = "0";
				$this->attachment->save();
				redirect($this->page->page['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "rename")
			{
				$this->form_validation->set_rules('filename', 'file name', 'xss_clean|strip_tags|trim|required|max_length[100]|callback_filename_check');

				if ($this->form_validation->run())
				{
					$this->attachment->new_file['filename'] = set_value('filename');
					$this->attachment->save();
					redirect($this->page->page['urlname'].'/attachments');
				}
				else
				{
					$this->_initialize_page('file_rename',"Rename file",array('attachment' => $this->attachment->file));
				}
			}
			else
			{
				$this->index();
			}
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
		{
			return TRUE;
		}
	}

	function blocks()
	{
		if ($this->acl->allow('page', 'blocks', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('blocktitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[20]|callback_blocktitle_check');
			$this->form_validation->set_rules('blockfunction', 'function', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[20]');

			if ($this->form_validation->run())
			{
				$this->load->library('block_lib','','new_block');
				$this->new_block->new_block['pageid'] = $this->page->page['pageid'];
				$this->new_block->new_block['title'] = set_value('blocktitle');
				$this->new_block->new_block['function'] = set_value('blockfunction');
				$this->new_block->save();
				redirect('block/'.$this->new_block->new_block['blockinstanceid']);
			}
			else
			{
				// Load up edited block
				$this->block = instantiate_library('block', $this->uri->segment($this->base_segment+3));

				// Ensure block selected is attached to site and not something else
				if ($this->uri->segment($this->base_segment+3) != NULL && (!isset($this->block->block['blockinstanceid']) || (!isset($this->page->page_tree[$this->block->block['pageid']]) && $this->block->block['pageid'] != 0)))
				{
					$this->_access_denied();
				}
				else if ($this->uri->segment($this->base_segment+2) == "remove_pagination_child")
				{
					$this->block->set_default($this->page->page['pageid'], 'pagination', FALSE);
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "set_pagination_child")
				{
					$this->block->set_default($this->page->page['pageid'], 'pagination');
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "remove_rss_child")
				{
					$this->block->set_default($this->page->page['pageid'], 'rss', FALSE);
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "set_rss_child")
				{
					$this->block->set_default($this->page->page['pageid'], 'rss');
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "remove_pagination_page")
				{
					$this->page->new_page['pagination_blockid'] = NULL;
					$this->page->save();
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "set_pagination_page")
				{
					$this->page->new_page['pagination_blockid'] = $this->block->block['blockinstanceid'];
					$this->page->save();
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "remove_rss_page")
				{
					$this->page->new_page['rss_blockid'] = NULL;
					$this->page->save();
					redirect($this->page->page['urlname'].'/blocks');
				}
				else if ($this->uri->segment($this->base_segment+2) == "set_rss_page")
				{
					$this->page->new_page['rss_blockid'] = $this->block->block['blockinstanceid'];
					$this->page->save();
					redirect($this->page->page['urlname'].'/blocks');
				}
				else
				{
					$this->index();
				}
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

	function editpage()
	{
		if ($this->acl->allow('page', 'edit', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('title', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('urlname', 'url name', 'xss_clean|strip_tags|trim|strtolower|required|min_length[2]|max_length[55]|callback_pageurlname_check');
			$this->form_validation->set_rules('content', 'content', 'max_length[65000]');
			$this->form_validation->set_rules('nestedurl', 'nested url', 'xss_clean');
			$this->form_validation->set_rules('pagesubscription', 'needs subscription', 'xss_clean');

			// Add in form validation for additional template fields
			foreach ($this->template->fields as $field)
			{
				$rulestring = 'max_length[9999]';
				if ($field['form_type'] == 1)
				{
					$rulestring .= '|xss_clean|strip_tags';
				}
				if ($field['required'])
				{
					$rulestring .= '|required';
				}

				$this->form_validation->set_rules($field['htmlcode'], $field['name'], $rulestring);
			}

			if ($this->form_validation->run())
			{
				$this->page->new_page['needsubscription'] = set_value('pagesubscription');
				$this->page->new_page['title'] = html_entity_decode(set_value('title'), ENT_QUOTES);
				$this->page->new_page['urlname'] = set_value('urlname');
				// If a nested URL is chosen and a parent page is selected, add that URL name to the name
				if (set_value('nestedurl') && $this->page->page['pageof'] != NULL)
				{
					$object = instantiate_library('page', $this->page->page['pageof']);
					$this->page->new_page['urlname'] = $object->page['urlname'].'/'.$this->page->new_page['urlname'];
				}

				$this->page->new_page['content'] = html_entity_decode(set_value('content'), ENT_QUOTES);
				$this->page->save();

				// Save additional template field values
				if (isset($this->template->fields))
				{
					$values = array();
					foreach ($this->template->fields as $field)
					{
						$values[$field['htmlcode']] = html_entity_decode(set_value($field['htmlcode']), ENT_QUOTES);
					}
					$this->template->set_values($values, $this->page->page['pageid']);
				}

				redirect($this->page->page['urlname'].'/editpage');
			}
			else
			{
				$this->index();
			}
		}
	}

	function pageurlname_check($str)
	{
		//Grab page methods to ensure page url can't be a page function
		$page_controller = fopen(APPPATH.'/controllers/page.php', 'r');
		$page_controller_contents = fread($page_controller, filesize(APPPATH.'/controllers/page.php'));
		fclose($page_controller);
		preg_match_all("/\s+function (\w*)\(.*\)/", $page_controller_contents, $functions);
		$page_controller_methods = implode("|",$functions[1]);

		if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('pageurlname_check', "The url name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|\-+$|\_+$/i', $str))
		{
			$this->form_validation->set_message('pageurlname_check', "The url name cannot start or end with dashes or underscores.");
			return FALSE;
		}
		else if (preg_match('/^[0-9]{8}$/', $str))
		{
			$this->form_validation->set_message('pageurlname_check', "The url name cannot be 8 digits as this format is used by posts on the site.");
			return FALSE;
		}
		else if (strpos("|".$page_controller_methods."|".$this->config->item('dmcb_controllers')."|".$this->config->item('dmcb_page_controller')."|".$this->config->item('dmcb_post_controller')."|".$this->config->item('dmcb_reserved_names')."|", "|".$str."|") !== FALSE)
		{
			$this->form_validation->set_message('pageurlname_check', "$str is a url name reserved by the website.");
			return FALSE;
		}
		else
		{
			// If a nested URL is chosen and a parent page is selected, add that URL name to the name we are testing
			if (isset($_POST['nestedurl']) && $this->page->page['pageof'] != NULL)
			{
				$object = instantiate_library('page', $this->page->page['pageof']);
				$str = $object->page['urlname'].'/'.$str;
			}

			// Check for name collisions and return suggested new name
			$suggestion = $this->page->suggest($str);
			if ($suggestion == $str)
			{
				return TRUE;
			}
			else
			{
				// If suggestion contains nested URL, remove it
				if (strrpos($suggestion, '/') != 0)
				{
					$suggestion = substr($suggestion, strrpos($suggestion, '/')+1);
				}
				$this->form_validation->set_message('pageurlname_check', "The url name is in use.  We suggest $suggestion.");
				return FALSE;
			}
		}
	}

	function permissions()
	{
		if ($this->acl->allow('page', 'permissions', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('displayname', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_permissions_check|callback_acl_exists_check');
			$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|max_length[50]|valid_email|callback_email_check');
			$this->form_validation->set_rules('role', 'role', 'xss_clean');

			$this->load->model('acls_model');
			if ($this->uri->segment($this->base_segment+2) == "set_role")
			{
				$this->acls_model->delete($this->uri->segment($this->base_segment+3), 'page', $this->page->page['pageid']);
				$this->acls_model->add($this->uri->segment($this->base_segment+3), $this->uri->segment($this->base_segment+4), 'page', $this->page->page['pageid']);

				// Do notification
				$this->session->set_flashdata('change', 'role change');
				$this->session->set_flashdata('action', 'set');
				$this->session->set_flashdata('actionon', 'user');
				$this->session->set_flashdata('actiononid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('parentid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('scope', 'page');
				$this->session->set_flashdata('scopeid', $this->page->page['pageid']);
				$this->session->set_flashdata('content', $this->roles_table[$this->uri->segment($this->base_segment+4)]);
				$this->session->set_flashdata('return', $this->page->page['urlname'].'/permissions');
				redirect('notify');
			}
			else if ($this->uri->segment($this->base_segment+2) == "delete")
			{
				$this->acls_model->delete($this->uri->segment($this->base_segment+3), 'page', $this->page->page['pageid']);

				// Do notification
				$this->session->set_flashdata('change', 'role removal');
				$this->session->set_flashdata('action', 'removed');
				$this->session->set_flashdata('actionon', 'user');
				$this->session->set_flashdata('actiononid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('parentid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('scope', 'page');
				$this->session->set_flashdata('scopeid', $this->page->page['pageid']);
				$this->session->set_flashdata('return', $this->page->page['urlname'].'/permissions');
				redirect('notify');
			}
			else if ($this->form_validation->run())
			{
				if (set_value('email') != "")
				{
					$this->load->library('user_lib',NULL,'new_user');
					$this->new_user->new_user['email'] = set_value('email');
					$this->new_user->new_user['displayname'] = set_value('displayname');
					$this->new_user->new_user['roleid'] = $this->acls_model->get_roleid('member');
					$result = $this->new_user->save();

					$this->acls_model->add($result['userid'], set_value('role'), 'page', $this->page->page['pageid']);

					redirect($this->page->page['urlname'].'/permissions');
				}
				else
				{
					$object = instantiate_library('user', set_value('displayname'), 'displayname');

					$this->acls_model->add($object->user['userid'], set_value('role'), 'page', $this->page->page['pageid']);

					// Do notification
					$this->session->set_flashdata('change', 'added role');
					$this->session->set_flashdata('action', 'set');
					$this->session->set_flashdata('actionon', 'user');
					$this->session->set_flashdata('actiononid', $object->user['userid']);
					$this->session->set_flashdata('parentid', $object->user['userid']);
					$this->session->set_flashdata('scope', 'page');
					$this->session->set_flashdata('scopeid', $this->page->page['pageid']);
					$this->session->set_flashdata('content', $this->roles_table[set_value('role')]);
					$this->session->set_flashdata('return', $this->page->page['urlname'].'/permissions');
					redirect('notify');
				}
			}
			else
			{
				$this->index();
			}
		}
	}

	function acl_exists_check($str)
	{
		if (isset($_POST['email']) && $_POST['email'] != NULL)
		{
			return TRUE;
		}
		else
		{
			$checkuser = instantiate_library('user', $str, 'displayname');
			$role = $this->acls_model->get($checkuser->user['userid'], 'post', $this->post->post['postid']);
			if (isset($checkuser->user['userid']) && $role != NULL)
			{
				$this->form_validation->set_message('acl_exists_check', "$str already has permissions on this post.");
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}

	function displayname_permissions_check($str)
	{
		if (isset($_POST['email']) && $_POST['email'] != NULL)
		{
			$displayname_check = instantiate_library('user', $str, 'displayname');
			if (strlen($str) < 3)
			{
				$this->form_validation->set_message('displayname_permissions_check', "The display name field must be at least 3 characters in length.");
				return FALSE;
			}
			else if (isset($displayname_check->user['userid']))
			{
				$this->form_validation->set_message('displayname_permissions_check', "The display name $str is in use, please try a new display name.");
				return FALSE;
			}
			else if (!preg_match('/^[a-z0-9- ]+$/i', $str))
			{
				$this->form_validation->set_message('displayname_permissions_check', "The display name must be made of only letters, numbers, dashes, and spaces.");
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$checkuser = instantiate_library('user', $str, 'displayname');
			if ($str != "" && !isset($checkuser->user['userid']))
			{
				$this->form_validation->set_message('displayname_permissions_check', "The display name $str doesn't exist, please try a new display name.");
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}

	function email_check($str)
	{
		$checkuser = instantiate_library('user', $str, 'email');
		if (isset($checkuser->user['userid']))
		{
			$this->form_validation->set_message('email_check', "The email address $str is in use, please try a new email address.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	function settemplate()
	{
		if ($this->acl->allow('page', 'templates', TRUE, 'page', $this->page->page['pageid']) || $this->_access_denied())
		{
			$template = instantiate_library('template', $this->uri->segment($this->base_segment+3));

			// Ensure template selected is usable by this page
			if ($this->uri->segment($this->base_segment+3) != "" && (!isset($template->template['templateid']) || (!isset($this->page->page_tree[$template->template['pageid']]) && $template->template['pageid'] != 0)))
			{
				$this->_access_denied();
			}
			else if ($this->uri->segment($this->base_segment+2) == "remove_child")
			{
				$template->set_default($this->page->page['pageid'], FALSE);
				redirect($this->page->page['urlname'].'/settemplate');
			}
			else if ($this->uri->segment($this->base_segment+2) == "set_child")
			{
				$template->set_default($this->page->page['pageid']);
				redirect($this->page->page['urlname'].'/settemplate');
			}
			else if ($this->uri->segment($this->base_segment+2) == "remove_page")
			{
				$this->page->new_page[$template->template['type'].'_templateid'] = NULL;
				$this->page->save();
				$this->load->helper('template');
				set_page_post_urlnames();
				redirect($this->page->page['urlname'].'/settemplate');
			}
			else if ($this->uri->segment($this->base_segment+2) == "set_page")
			{
				$this->page->new_page[$template->template['type'].'_templateid'] = $template->template['templateid'];
				$this->page->save();
				$this->load->helper('template');
				set_page_post_urlnames();
				redirect($this->page->page['urlname'].'/settemplate');
			}
			else
			{
				$this->form_validation->set_rules('pagepostname', 'page post name', 'xss_clean');

				if ($this->form_validation->run())
				{
					if (!isset($this->template->template['templateid']))
					{
						if (set_value('template') == "")
						{
							$this->page->new_page['template'] = NULL;
						}
						else
						{
							$this->page->new_page['template'] = set_value('template');
						}
						$this->page->new_page['pagepostname'] = set_value('pagepostname');
						$this->page->save();
					}
					redirect($this->page->page['urlname'].'/settemplate');
				}
				else
				{
					$this->index();
				}
			}
		}
	}
}