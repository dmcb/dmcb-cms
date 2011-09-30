<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb extension to CI 2.0.3 controller library
 *
 * Adds page rendering functions used by all controllers
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
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
			'You do not have proper access for this page.  If this is incorrect please contact support at <a href="mailto:support@'.$this->config->item('dmcb_server').'">support@'.$this->config->item('dmcb_server').'</a>'
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
		
		// Add menus
		$this->load->helper('menu_helper');
		$data['menu'] = array();
		$i=1;
		while ($this->config->item('dmcb_menu_'.$i))
		{
			$menu_definition = $this->config->item('dmcb_menu_'.$i);
			$view = $menu_definition[0];
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
		
			$data['menu'][$i] = generate_menu_html($view, $menu, $pageid, $levels, $back_button);
			$i++;
		}
		
		// Set a default focus of null, if a controller didn't set a focus (used for the javascript panels)
		if (!isset($this->focus))
		{
			$data['focus'] = "null";
		}
		else
		{
			$data['focus'] = $this->focus;
		}

		// Endow outputted page with any custom CSS added
		if (isset($this->css))
		{
			$data['css'] = $this->css;
		}
		if (isset($this->cssfiles))
		{
			$data['cssfiles'] = $this->cssfiles;
		}
		
		// Endow outputted page with any custom Javascript added
		if (isset($this->javascript))
		{
			$data['javascript'] = $this->javascript;
		}
		if (isset($this->jsfiles))
		{
			$data['jsfiles'] = $this->jsfiles;
		}

		// Specify page and title and load view
		$data['title'] = $title;
		$data['page'] = $page;
		
		// Wrap it (allows dynamically built pages to have different view information encapsulating it
		$view = $this->load->view($page, $data, TRUE);
		if ($dynamic)
		{
			$wrapped_view = $this->load->view('page_wrapper_dynamic', array('view' =>  $view, 'title' => $title), TRUE);
		}
		else
		{
			$wrapped_view = $this->load->view('page_wrapper_static', array('view' => $view, 'title' => $title), TRUE);		
		}
		$this->load->view($this->template, array('view' => $wrapped_view));
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