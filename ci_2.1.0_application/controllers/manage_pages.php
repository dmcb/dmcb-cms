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
class Manage_pages extends MY_Controller {

	function Manage_pages()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}

	function _remap()
	{
		if ($this->acl->allow('site', 'manage_pages', TRUE) || $this->_access_denied())
		{
			$object = instantiate_library('page', $this->uri->segment(3));
			// If we are manipulating a specific page, handle the operation
			if ($this->uri->segment(2) == "permissions")
			{
				// Get roles for editing
				$this->load->model('acls_model');
				$fields = array();
				$rules = array();
				$roles = array();
				$rolelist = $this->acls_model->get_roles_all();
				foreach ($rolelist->result_array() as $role)
				{
					if ($role['role'] != 'owner')
					{
						$role['rolefield'] = "role_".$role['roleid'];
						$this->form_validation->set_rules($role['rolefield'], $role['role'], 'xss_clean');
						array_push($roles, $role);
					}
				}

				// Grab parent
				$parent = instantiate_library('page', $object->page['pageof']);

				if ($this->form_validation->run())
				{
					// Limit page to roles chosen
					$protection = array();
					foreach($roles as $role)
					{
						if (set_value($role['rolefield']) == "1")
						{
							// Access to children must be equal or MORE restrictive than access to parent
							if (isset($parent->page['protected']) && $parent->page['protected'])
							{
								foreach ($parent->page['protection'] as $key => $value)
								{
									// Check if parent also limits access to that role
									// If so, it's okay for child to limit access to that role
									if ($key == $role['roleid'])
									{
										$protection[$role['roleid']] = 1;
									}
								}
							}
							else
							{
								$protection[$role['roleid']] = 1;
							}
						}
					}

					// If child is attempting to not restrict access at all, and parent is restricted, don't allow it
					if (sizeof($protection) || !isset($parent->page) || !$parent->page['protected'])
					{
						$object->new_page['protection'] = $protection;
						$object->save();
					}
					redirect('manage_pages');
				}
				else
				{
					$parentroles = array();
					if (isset($parent->page['protected']) && $parent->page['protected'])
					{
						foreach($roles as $role)
						{
							if (!isset($parent->page['protection'][$role['roleid']]))
							{
								$parentroles[$role['roleid']] = 1;
							}
						}
					}
					$this->_initialize_page('page_permissions', 'Edit permissions', array('item' => $object->page, 'roles' => $roles, 'parentroles' => $parentroles));
				}
			}
			else if ($this->uri->segment(2) == "delete")
			{
				$object->delete();
				redirect('manage_pages');
			}
			else if ($this->uri->segment(2) == "publish")
			{
				$object->new_page['published'] = 1;
				$object->save();
				redirect('manage_pages');
			}
			else if ($this->uri->segment(2) == "unpublish")
			{
				$object->new_page['published'] = 0;
				$object->save();
				redirect('manage_pages');
			}
			else if ($this->uri->segment(2) == "move")
			{
				// If we are moving to under another page, instead of directly under a menu
				if (ctype_digit($this->uri->segment(4)) && $this->uri->segment(3) != $this->uri->segment(4))
				{
					$new_parent_page = instantiate_library('page', $this->uri->segment(4));
					$object->new_page['pageof'] = $new_parent_page->page['pageid'];
					// If the page name was nested, rename it to the new name
					if (strrpos($object->new_page['urlname'], '/') != 0)
					{
						$object->new_page['urlname'] = $new_parent_page->page['urlname'].substr($object->new_page['urlname'], strrpos($object->new_page['urlname'], '/'));
					}
				}
				else
				{
					$object->new_page['menu'] = $this->uri->segment(4);
					// Regardless if the page name was nested or not, there is no parent page, so remove it
					$object->new_page['pageof'] = NULL;
					if (strrpos($object->new_page['urlname'], '/') != 0)
					{
						$object->new_page['urlname'] = substr($object->new_page['urlname'], strrpos($object->new_page['urlname'], '/')+1);
					}
				}
				$object->save();
				redirect('manage_pages');
			}
			else if ($this->uri->segment(2) == "move_up")
			{
				$this->pages_model->move_up($this->uri->segment(3));
				redirect('manage_pages');
			}
			else if ($this->uri->segment(2) == "move_down")
			{
				$this->pages_model->move_down($this->uri->segment(3));
				redirect('manage_pages');
			}
			else
			{
				if ($this->uri->segment(2) == "addpage")
				{
					$this->focus = "addpage";
				}

				$this->form_validation->set_rules('pageof', 'appears under', 'xss_clean|strip_tags');
				$this->form_validation->set_rules('nestedurl', 'nested url', 'xss_clean|strip_tags');
				$this->form_validation->set_rules('title', 'title', 'xss_clean|strip_tags|trim|htmlentities|required|min_length[2]|max_length[50]');
				$this->form_validation->set_rules('urlname', 'url name', 'xss_clean|strip_tags|trim|strtolower|min_length[2]|max_length[55]|callback_pageurlname_check');
				$this->form_validation->set_rules('link', 'link', 'xss_clean|strip_tags|trim|max_length[150]|callback_link_check');

				if ($this->form_validation->run()) // Otherwise if a form was submitted we are adding a page
				{
					if (!is_numeric(set_value('pageof')))
					{
						$menu = set_value('pageof');
						$pageof = NULL;
					}
					else
					{
						$menu = $this->pages_model->get_menu(set_value('pageof'));
						$pageof = set_value('pageof');
					}
					$this->load->library('page_lib',NULL,'new_page');
					$this->new_page->new_page['menu'] = $menu;
					$this->new_page->new_page['pageof'] = $pageof;
					$this->new_page->new_page['title'] = html_entity_decode(set_value('title'), ENT_QUOTES);
					$result = $this->new_page->save();

					$new_page = instantiate_library('page', $result);
					if (set_value('link') != "")
					{
						$new_page->new_page['link'] = set_value('link');
						$new_page->save();
					}
					else if (set_value('urlname') != "")
					{
						$new_page->new_page['urlname'] = set_value('urlname');
						// If a nested URL is chosen and a parent page is selected, add that URL name to the name
						if (set_value('nestedurl') && is_numeric(set_value('pageof')))
						{
							$object = instantiate_library('page', set_value('pageof'));
							$new_page->new_page['urlname'] = $object->page['urlname'].'/'.$new_page->new_page['urlname'];
						}
						$new_page->save();
					}
					redirect('manage_pages');
				}

				$this->load->model('pages_model');
				$data['menutypes'] = $this->pages_model->get_all_menus();
				$data['menusections'] = array();

				foreach ($data['menutypes']->result_array() as $menutype)
				{
					$this->load->helper('menu_helper');
					$menu_pages = array();
					generate_menu_pages($menu_pages, $menutype['menu'], NULL, NULL, TRUE);

					array_push($data['menusections'], $menu_pages);
				}

				$this->load->model('acls_model');
				$data['userroles'] = $this->acls_model->get_roles();
				$this->_initialize_page('manage_pages', 'Manage pages', $data);
			}
		}
	}

	function link_check($str)
	{
		if ($str == "")
		{
			return TRUE;
		}

		if (substr($str, 0, 1) != '/' && substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
		{
			$this->form_validation->set_message('link_check', "The link must start with a slash '/' for links to pages within the site, or http:// or https:// for links to external sites.");
			return FALSE;
		}

		if (substr($str, 0, 1) == '/') // Link points internally to site, let's check that where they are pointing to exists
		{
			$link = substr($str, 1); // Strip off leading slash
			$page = instantiate_library('page', $link, 'urlname');
			$post = instantiate_library('post', $link, 'urlname');
			if (strpos("|".$this->config->item('dmcb_controllers')."|", "|".$link."|") === FALSE &&
				!isset($page->page['pageid']) &&
				!isset($post->post['postid']))
			{
				$this->form_validation->set_message('link_check', "Your internal link points to an invalid location. Ensure you have no trailing slash.");
				return FALSE;
			}
		}
		return TRUE;
	}

	function pageurlname_check($str)
	{
		if ($str == "")
		{
			return TRUE;
		}

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
			if (set_value('nestedurl') && is_numeric(set_value('pageof')))
			{
				$object = instantiate_library('page', set_value('pageof'));
				$str = $object->page['urlname'].'/'.$str;
			}

			// Check for name collisions and return suggested new name
			$this->load->library('page_lib','','test_page');
			$this->test_page->page['pageid'] = '0';
			$suggestion = $this->test_page->suggest($str);
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
		return TRUE;
	}
}