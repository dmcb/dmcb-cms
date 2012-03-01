<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb mapper helper
 *
 * Maps internal links security to their actual pages & controllers
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */

// ------------------------------------------------------------------------

/**
 * Link security
 *
 * Grabs security of internal link however it can
 *
 * @access	public
 * @param	string   link
 * @return	array    array of roleids that are privileged
 */

if ( ! function_exists('link_security'))
{
	function link_security($link)
	{
		$CI =& get_instance();

		$protection = array();

		// Strip '/' off link
		$link = substr($link, 1);

		$controllers = array(
			'profile' => array('controller' => 'profile', 'function' => 'add'),
			'manage_activity' => array('controller' => 'site', 'function' => 'manage_activity'),
			'manage_content' => array('controller' => 'site', 'function' => 'manage_content'),
			'manage_pages' => array('controller' => 'site', 'function' => 'manage_pages'),
			'manage_security' => array('controller' => 'site', 'function' => 'manage_security'),
			'manage_users' => array('controller' => 'site', 'function' => 'manage_users'),
			'search' => array('controller' => 'site', 'function' => 'search'),
			'subscription' => array('controller' => 'site', 'function' => 'susbcribe')
		);

		if ($link == "account" || $link == "signoff") // Account + Signoff links are available to any role that isn't a guest
		{
			$CI->load->model('acls_model');
			$roles = $CI->acls_model->get_roles();
			foreach ($roles->result_array() as $role)
			{
				$protection[$role['roleid']] = 1;
			}
		}
		else if (isset($controllers[$link])) // If is an internal link to a controller, grab controller's security
		{
			$CI->load->model('acls_model');
			$function = $CI->acls_model->get_function_by_function($controllers[$link]['controller'], $controllers[$link]['function']);
			if (isset($function) && $function['enabled'])
			{
				$permissions = $CI->acls_model->get_privileged($controllers[$link]['controller'], $controllers[$link]['function']);
				foreach($permissions->result_array() as $permission)
				{
					$protection[$permission['roleid']] = 1;
				}
			}
			else
			{
				$protection[0] = 1;
			}
		}
		else if (preg_match('/^([0-9]{8})\/(.+)/', $link) || preg_match('/^(.+)\/post\/(.+)/', $link)) // If it has the format of a post, check to see if that post exists
		{
			$CI->load->model('pages_model');
			$post = instantiate_library('post', $link, 'urlname');
			if (isset($post->post['pageid'])) // It's a link to a post, grab post's parent's security
			{
				$permissions = $CI->pages_model->get_page_protection($post->post['pageid']);
				foreach($permissions->result_array() as $permission)
				{
					$protection[$permission['roleid']] = 1;
				}
			}
		}
		else
		{
			$CI->load->model('pages_model');
			$page = instantiate_library('page', $link, 'urlname');
			if (isset($page->page['pageid'])) // If it is an internal link to a page, grab page's security
			{
				$permissions = $CI->pages_model->get_page_protection($page->page['pageid']);
				foreach($permissions->result_array() as $permission)
				{
					$protection[$permission['roleid']] = 1;
				}
			}
		}

		return $protection;
	}
}