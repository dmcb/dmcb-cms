<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb menu helper
 *
 * Generates menu HTML from a starting point in the page tree
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */

// ------------------------------------------------------------------------

/**
 * Generate menu pages
 *
 * Recursively builds a page tree, returns page objects
 *
 * @access	public
 * @param	array    array of menu pages to add to
 * @param	string   menu
 * @param	string   optional pageid to start from
 * @param	string   optional maximum depth value
 * @param	boolean  optional value to show all menu items
 * @param	string   optional current depth in the tree
 * @return	void
 */
if ( ! function_exists('generate_menu_pages'))
{
	function generate_menu_pages(&$menu_pages, $menu, $pageid = NULL, $maxlevel = NULL, $all = FALSE, $level = 0)
	{
		$CI =& get_instance();
		$CI->load->model('pages_model');

		if ($maxlevel == NULL || $level < $maxlevel)
		{
			$children = $CI->pages_model->get_children($menu, $pageid);
			foreach ($children->result_array() as $child)
			{
				$object = instantiate_library('page', $child['pageid']);

				// If showing all pages isn't set, than check if the page isn't published, or it's set to be hidden from view if protected and the user doesn't have the proper role, don't show it in menu
				if ($all || ($object->page['published'] == 1 && (!$object->page['protected'] || ($object->page['protected'] && $CI->acl->access($object->page['protection'], $object)))))
				{
					$object->page['level'] = $level;
					array_push($menu_pages, $object->page);
					generate_menu_pages($menu_pages, $menu, $child['pageid'], $maxlevel, $all, $level+1);
				}
			}
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Generate menu html
 *
 * Recursively builds a page tree, returns menu html
 *
 * @access	public
 * @param   string   view
 * @param	string   menu
 * @param	string   optional pageid to start from
 * @param	string   optional maximum depth value
 * @param	boolean  optional value to show all menu items
 * @param	string   optional current depth in the tree
 * @return	string   menu HTML
 */
if ( ! function_exists('generate_menu_html'))
{
	function generate_menu_html($view, $menu, $pageid = NULL, $maxlevel = NULL, $back_button = FALSE, $alphabetical = FALSE, $all = FALSE, $level = 0, &$itemnumber = 0)
	{
		$CI =& get_instance();
		$CI->load->model('pages_model');
		$menu_html = "";

		if ($maxlevel == NULL || $level < $maxlevel)
		{
			// Get menu children
			$children = $CI->pages_model->get_children($menu, $pageid, $alphabetical);

			// If the back button is enabled and an specific page is used to build the menu off of, and there's only one level of menu items, place a back button
			if ($back_button)
			{
				$parent = NULL;
				if ($pageid != NULL && $maxlevel == 1)
				{
					$object = instantiate_library('page', $pageid);
					if (isset($object->page['pageid']))
					{
						// Grab the parent's parent, the grandparent, for potential use as a back button
						$object = instantiate_library('page', $object->page['pageof']);
						if (isset($object->page['pageid']))
						{
							$title = 'Back to '.$object->page['title'];
							$link = base_url().$object->page['urlname'];
						}
						else // If the grandparent doesn't exist, the potential back button would be the home page
						{
							$title = 'Back to main menu';
							$link = base_url();
						}

						$menu_html .= $CI->load->view($view, array('title' => $title, 'link' => $link, 'selected' => FALSE, 'children_html' => NULL, 'level' => 0, 'itemnumber' => 0), TRUE);
					}
				}
				else if ($maxlevel == 1)
				{
					// If there was no back button, give the template a chance to load filler
					if ($menu_html == "")
					{
						$menu_html .= $CI->load->view($view, array('title' => NULL, 'link' => NULL, 'selected' => NULL, 'children_html' => NULL, 'level' => 0, 'itemnumber' => 0), TRUE);
					}
				}
			}

			foreach ($children->result_array() as $child)
			{
				$object = instantiate_library('page', $child['pageid']);

				// If showing all pages isn't set, than check if the page isn't published, or it's set to be hidden from view if protected and the user doesn't have the proper role, don't show it in menu
				if ($all || ($object->page['published'] == 1 && (!$object->page['protected'] || ($object->page['protected'] && $CI->acl->access($object->page['protection'], $object)))))
				{
					$children_html = generate_menu_html($view, $menu, $object->page['pageid'], $maxlevel, $back_button, $alphabetical, $all, $level+1, $newset = 0);

					// Determine what the link of the menu item is and if it is selected
					$link;
					$selected = FALSE;
					if (!isset($object->page['urlname']) && !isset($object->page['link'])) // Menu item is a placeholder
					{
						$link = NULL;
					}
					else if ($object->page['link'] != NULL) // Menu item is a link
					{
						if (substr($object->page['link'],0,1) == "/") // Menu item links to an internal path
						{
							if (strpos(current_url(), $object->page['link']) !== false && substr($object->page['link'],0,1) == "/") // If current URL is internal path
							{
								$selected = TRUE;
							}
							if (substr($object->page['link'],1,strlen($object->page['link'])) == $CI->config->item('dmcb_default_location')) // If menu item internal path is our default location, link to base
							{
								if (current_url() == base_url()) // Since this internal link now goes to base URL, and we are at the base URL, it's the current URL
								{
									$selected = TRUE;
								}
								$link = base_url();
							}
							else // If menu item internal path isn't default location, link to this internal path
							{
								$link = base_url().substr($object->page['link'],1,strlen($object->page['link']));
							}
						}
						else  // Menu item links to an external path
						{
							$link = $object->page['link'];
						}
					}
					else // Menu item links to a page
					{
						if (strpos(current_url(), base_url().$object->page['urlname']) !== FALSE ||
							(!$CI->uri->total_segments() && $object->page['urlname'] == $CI->config->item('dmcb_default_page'))) // If current URL is the page, or current page urlname is empty and we are on the default page
						{
							$selected = TRUE;
						}
						$link = base_url().$object->page['urlname'];
					}

					$itemnumber++;
					$menu_html .= $CI->load->view($view, array('title' => $object->page['title'], 'link' => $link, 'imageid' => $object->page['imageid'], 'selected' => $selected, 'children_html' => $children_html, 'level' => $level, 'itemnumber' => $itemnumber), TRUE);
				}
			}
		}

		// If we are creating the menu configured to contain the sign on/sign off links, add them
		if ($menu == $CI->config->item('dmcb_signon_menu') && $pageid == NULL && $level == 0)
		{
			if ($CI->session->userdata('signedon') && $CI->config->item('dmcb_signoff_text') != "") // If sign off text defined, give it it's own link
			{
				$title = str_replace("%n", $CI->session->userdata('displayname'), $CI->config->item('dmcb_signoff_text'));
				if ($CI->session->userdata('facebook')) // Add facebook icon to sign off menu item should you be logged on via facebook
				{
					$title = '<img class="menuicon" src="'.base_url().'includes/images/facebook_connect_icon.png" alt="Facebook" /> '.$title;
				}
				$menu_html .= $CI->load->view($view, array('title' => $title, 'link' => base_url().'signoff', 'selected' => FALSE, 'children_html' => NULL, 'level' => $level), TRUE);
			}
			else if (!$CI->session->userdata('signedon'))
			{
				if ($CI->config->item('dmcb_signup_text') != "") // If there is specifically defined sign up text, give it it's own link
				{
					$menu_html .= $CI->load->view($view, array('title' => $CI->config->item('dmcb_signup_text'), 'link' => base_url().'signon/signup', 'selected' => FALSE, 'children_html' => NULL, 'level' => $level), TRUE);
				}
				if ($CI->config->item('dmcb_signon_text') != "") // If there is specifically defined sign on text, give it it's own link
				{
					$title = $CI->config->item('dmcb_signon_text');
					if ($CI->config->item('dmcb_signon_facebook') == "true") // Add facebook icon if facebook sign ons are supported
					{
						$title = '<img class="menuicon" src="'.base_url().'includes/images/facebook_connect_icon.png" alt="Facebook" /> '.$title;
					}
					$menu_html .= $CI->load->view($view, array('title' => $title, 'link' => base_url().'signon'.uri_string(), 'selected' => FALSE, 'children_html' => NULL, 'level' => $level), TRUE);
				}
			}
		}

		if ($level == 0) // Wrap the menu
		{
			$menu_html = $CI->load->view($view.'_wrapper', array('menu_html' => $menu_html), TRUE);
		}
		return $menu_html;
	}
}