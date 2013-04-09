<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to CI 2.1.0 controller library
 *
 * Adds page rendering functions used by all controllers
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class MY_Controller extends CI_Controller {

	private $template;

    function __construct()
    {
        parent::__construct();

		// Make sure the DB is up to date
		$this->load->library('migration');
		if (!$this->migration->current())
		{
			show_error($this->migration->error_string());
		}

		// Define global variables that can be set by controllers, pages and blocks:
		$this->metadata = array();
		$this->packages = array();
		$this->focus = "null";

		// Switch to mobile theme if developed
		$this->load->library('user_agent');
        if ($this->agent->is_mobile() && $this->config->item('dmcb_mobile_template') == "true")
		{
			$this->template = "site_mobile";
		}
		else
		{
			$this->template = "site";
		}
    }

	// Generic access denied message
	function _access_denied()
	{
		$this->_message(
			'Access denied',
			'You do not have proper access for this page. If this is incorrect please contact support at <a href="mailto:'.$this->config->item('dmcb_email_support').'">'.$this->config->item('dmcb_email_support').'</a>'
		);
	}

	// Generic page not found message
	function _page_not_found()
	{
		$this->_message(
			'404 error',
			'This page no longer exists, <a href="'.base_url().'">return to '.$this->config->item('dmcb_title').'</a>.'
		);
	}

	// Sign on prompt
	function _signon_prompt($message = NULL)
	{
		if (isset($message))
		{
			$this->session->set_flashdata('signon_message', $message);
		}
		else
		{
			$this->lang->load('signon', 'english', FALSE, TRUE, APPPATH.'site_specific_');
			$this->session->set_flashdata('signon_message', $this->lang->line('signon_required'));
		}
		redirect('signon'.$this->uri->uri_string());
	}

	// Generic redirection page
	function _redirect($url, $instant = FALSE)
	{
		if ($instant)
		{
			redirect($url);
		}
		else
		{
			$this->_message(
				'Update your bookmark',
				'This link has now moved to <a href="'.$url.'">'.$url.'</a>, please update your bookmark.'
			);
		}
	}

	// Generic custom message
	function _message($title, $message, $subject = NULL)
	{
		if ($subject == NULL)
		{
			$subject = $title;
		}
		$this->_initialize_page('message', $title, array('subject' => $subject, 'message' => $message));
	}

	// Render page with contents
	function _initialize_page($page, $title, $data, $dynamic = FALSE)
	{
		// $this->output->enable_profiler(TRUE);
		// If not posting to a page, and not signed on, cache the page
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$this->session->userdata('signedon'))
		{
			//$this->output->cache(5);
		}

		// Add ping back header
		header("X-Pingback: ".base_url()."pingback");

		// Add menus from site configuration
		$this->load->helper('menu_helper');
		$data['menu'] = array();
		$i=1;
		while ($this->config->item('dmcb_menu_'.$i))
		{
			$menu_definition = $this->config->item('dmcb_menu_'.$i);
			$menu_pages = $menu_definition[0];
			$menu = $menu_definition[1];
			$current = $menu_definition[2];
			if ($current == "neighbours" && isset($data['page']['pageid']) && $data['page']['menu'] == $menu)
			{
				$pageid = $data['page']['pageof'];
			}
			else if ($current == "children" && isset($data['page']['pageid']) && $data['page']['menu'] == $menu)
			{
				$pageid = $data['page']['pageid'];
			}
			else
			{
				$pageid = NULL;
			}
			$levels = $menu_definition[3];
			$back_button = $menu_definition[4];

			$data['menu'][$i] = generate_menu_html($menu_pages, $menu, $pageid, $levels, $back_button);
			$i++;
		}

		// Specify page and title and load view
		$data['title'] = $title;
		$data['page'] = $page;
		$data['view'] = $this->load->view($page, $data, TRUE);

		// Set a default focus (used for the javascript panels)
		$data['focus'] = $this->focus;

		// Load up packages requested for this view
		$data['packages'] = "";
		
		// Add default metadata
		if ($this->config->item('dmcb_metadata'))
		{
			foreach ($this->config->item('dmcb_metadata') as $type => $values)
			{
				foreach ($values as $value)
				{
					$this->metadata[$type][] = $value;
				}
			}
		}
		$this->metadata['link'][] = array('pingback', base_url().'pingback');
		
		// Render metadata
		$data['metadata'] = "";
		foreach ($this->metadata as $metadata => $items) 
		{
			if ($metadata == "meta")
			{
				foreach ($items as $meta) 
				{
					$data['metadata'] .= "\t<meta name=\"".$meta[0]."\" content=\"".$meta[1]."\" />\n";
				}
			}
			else if ($metadata == "link")
			{
				foreach ($items as $link)
				{
					$data['metadata'] .= "\t<link rel=\"".$link[0]."\" href=\"".$link[1]."\" />\n";
				}
			}
		}

		// Add default packages
		if ($this->config->item('dmcb_packages'))
		{
			foreach ($this->config->item('dmcb_packages') as $weight => $value)
			{
				$this->packages[$weight] = $value;
			}
		}

		// Add default CSS files
		$this->packages[0]['cssfiles'][] = base_url().'assets/css/site.css';

		// Add default JS files
		$this->packages[3]['jsfiles'][] = base_url().'assets/js/functions.js';
		$this->packages[3]['jsfiles'][] = base_url().'assets/js/panels.js';

		// Add default Javascript
		$this->packages[4]['javascript'][] = "\nEffect.InitializePage('".$this->focus."');";

		// Render packages
		ksort($this->packages);
		foreach ($this->packages as $weight)
		{
			foreach ($weight as $key => $value)
			{
				if ($key == 'cssfiles')
				{
					$data['packages'] .= "\n\t<!-- dmcb styles -->\n\t<style type=\"text/css\">";
					foreach($value as $cssfile)
					{
						$data['packages'] .= "\n\t\t@import \"".$cssfile.'";';
					}
					$data['packages'] .= "\n\t</style>\n";
				}
				else if ($key == 'css')
				{
					$data['packages'] .= "\n\t<!-- dmcb styles -->\n\t<style type=\"text/css\">";
					foreach($value as $css)
					{
						$data['packages'] .= "\n\t\t".str_replace("\n", "\n\t\t", $css);
					}
					$data['packages'] .= "\n\t</style>\n";
				}
				else if ($key == 'jsfiles')
				{
					$data['packages'] .= "\n\t<!-- dmcb scripts -->\n";
					foreach($value as $jsfile)
					{
						$data['packages'] .= "\t<script type=\"text/javascript\" src=\"".$jsfile."\"></script>\n";
					}
				}
				else if ($key == 'javascript')
				{
					$data['packages'] .= "\n\t<!-- dmcb scripts -->\n\t<script type=\"text/javascript\">\n\t\t<!--\n\t\t\tdmcb.addLoadEvent(function () {";
					foreach($value as $javascript)
					{
						$data['packages'] .= "\n\t\t\t\t".str_replace("\n", "\n\t\t\t\t", $javascript);
					}
					$data['packages'] .= "\n\t\t\t});\n\t\t-->\n\t</script>\n";
				}
				else if ($key == 'no_wait_javascript')
				{
					$data['packages'] .= "\n\t<!-- dmcb scripts -->\n\t<script type=\"text/javascript\">\n\t\t<!--";
					foreach($value as $javascript)
					{
						$data['packages'] .= "\n\t\t\t".str_replace("\n", "\n\t\t\t\t", $javascript);
					}
					$data['packages'] .= "\n\t\t-->\n\t</script>\n";
				}
				else
				{
					$data['packages'] .= "\n".$this->load->view('package_'.$key, $value, TRUE);
				}
			}
		}

		// Wrap the view (allows dynamically built pages to have different view information encapsulating it)
		if ($dynamic)
		{
			$data['view'] = $this->load->view('page_wrapper_dynamic', $data, TRUE);
		}
		else
		{
			$data['view'] = $this->load->view('page_wrapper_static', $data, TRUE);
		}

		$data['site_content'] = $this->load->view($this->template.'_content', $data, TRUE);
		$this->load->view($this->template, $data);
	}

	// Render rss feed
	function _initialize_rss($title, $data)
	{
		// Add xml header
		header("Content-Type: application/xml; charset=utf-8");

		// Set feed and title and load view
		$data['title'] = $title;
		if (!isset($data['date']))
		{
			$this->load->model('pages_model');
			$data['date'] = $this->pages_model->get_recent_modified_date();
		}
		$this->load->view('rss', $data);
	}
}