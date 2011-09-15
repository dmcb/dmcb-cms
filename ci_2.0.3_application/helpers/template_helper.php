<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb template helper
 *
 * Functions to use the appropriate templates and blocks for a page
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
 
// ------------------------------------------------------------------------

/**
 * Template to use
 *
 * Returns the id of the first parent specifying a template or block as a default
 *
 * @access	public
 * @param	string	type
 * @param   array   page tree
 * @return	string
 */
if ( ! function_exists('template_to_use'))
{
 	function template_to_use($library, $type, $page_tree = NULL)
	{
		$returnid = "templateid";
		if ($library == "block")
		{
			$returnid = "blockinstanceid";
		}
	
		// Crawl through page tree array, returning the first default instance specified
		if (isset($page_tree))
		{
			array_shift($page_tree); // Skip the first entry, we only want to grab parent's defaults
			foreach ($page_tree as $pageid)
			{
				$instance_to_use = instantiate_library($library, array($type, $pageid), 'default');
				$array = $instance_to_use->$library;
				if (isset($array[$returnid]))
				{
					return $array[$returnid];
				}
			}
		}
		
		// If no defaults were found in the page tree, or there was none (i.e. the post isn't attached to a page), grab the site default template if it exists
		$instance_to_use = instantiate_library($library, array($type, 0), 'default');
		$array = $instance_to_use->$library;
		if (isset($array[$returnid]))
		{
			return $array[$returnid];
		}
		return NULL;
	}
}

// ------------------------------------------------------------------------

/**
 * Set page post urlnames
 *
 * Sets proper page post urlnames based on what templates are used where
 *
 * @access	public
 * @return	void
 */
if ( ! function_exists('set_page_post_urlnames'))
{
	function set_page_post_urlnames()
	{
		$CI =& get_instance();
		
		$CI->load->model('pages_model');
		$pages = $CI->pages_model->get_all();

		foreach ($pages->result_array() as $pageid) 
		{
			$object = instantiate_library('page', $pageid['pageid']);
			
			if (isset($object->new_page['page_templateid']))
			{
				$template_object = instantiate_library('template', $object->new_page['page_templateid']);
				if (isset($template_object->new_template['templateid']))
				{
					$object->new_page['pagepostname'] = $template_object->new_template['pagepostname'];
				}
				else
				{
					$object->new_page['pagepostname'] = 0;
				}
			}
			else
			{
				$object->initialize_page_tree();
				$template_to_use = template_to_use('template', 'page', $object->page_tree);
				if (isset($template_to_use))
				{				
					$template_object = instantiate_library('template', $template_to_use);
					if (isset($template_object->new_template['templateid']))
					{
						$object->new_page['pagepostname'] = $template_object->new_template['pagepostname'];
					}
					else
					{
						$object->new_page['pagepostname'] = 0;
					}

				}
				else
				{
					$object->new_page['pagepostname'] = 0;
				}
			}
			$object->save();
		}
	}
}