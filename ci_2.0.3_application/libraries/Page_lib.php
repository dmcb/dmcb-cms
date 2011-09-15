<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb page library
 *
 * Initializes a page and runs checks and operations on that page
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */ 
class Page_lib {

	public  $page      = array();
	public  $new_page  = array();
	public  $page_tree;

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function Page_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('pages_model');
		if (isset($params['id']))
		{
			$this->page = $this->CI->pages_model->get($params['id']);
			$this->_initialize_page();
			$this->new_page = $this->page;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize page
	 *
	 * Load the page protection
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_page()
	{
		if (isset($this->page['pageid']))
		{
			$this->page['protection'] = array();
			
			if ($this->page['link'] != NULL && substr($this->page['link'], 0, 1) == "/") // If the page is an internal link, let's check it out
			{
				$this->CI->load->helper('mapper');
				$this->page['protection'] = link_security($this->page['link']);
			}
			else // Otherwise, grab the page protection from what the user has set
			{
				$protectionlist = $this->CI->pages_model->get_page_protection($this->page['pageid']);
				foreach($protectionlist->result_array() as $protection)
				{
					$this->page['protection'][$protection['roleid']] = 1;
				}
			}
			
			// If the user was protected by roles that don't exist any more, remove protected status
			if (!sizeof($this->page['protection']))
			{
				$this->page['protected'] = 0;
			}
			else
			{
				$this->page['protected'] = 1;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete
	 *
	 * Delete a page and clears out references
	 *
	 * @access	public
	 * @return	void
	 */	
	function delete()
	{
		$this->CI->load->model(array('files_model', 'posts_model', 'acls_model', 'blocks_model', 'templates_model'));
		
		// Remove child posts
		$posts = $this->CI->posts_model->get_page_posts_all($this->page['pageid']);
		foreach ($posts->result_array() as $post) 
		{
			$object = instantiate_library('post', $post['postid']);
			$object->delete();
		}
		
		// Remove attached files
		$files = $this->CI->files_model->get_attached("page",$this->page['pageid']);
		foreach ($files->result_array() as $file) 
		{
			$object = instantiate_library('file', $file['fileid']);
			$object->delete();
		}
		
		// Remove attached blocks
		$blocks = $this->CI->blocks_model->get_page_blocks($this->page['pageid']);
		foreach ($blocks->result_array() as $block) 
		{
			$object = instantiate_library('block', $block['blockinstanceid']);
			$object->delete();
		}
		
		// Remove attached templates
		$templates = $this->CI->templates_model->get_attached($this->page['pageid']);
		foreach ($templates->result_array() as $template) 
		{
			$object = instantiate_library('template', $template['templateid']);
			$object->delete();
		}
		
		// Remove ACLs and security
		$this->CI->acls_model->delete(NULL, 'page', $this->page['pageid']);
		$this->CI->pages_model->remove_protection($this->page['pageid']);
		
		// Finally remove page
		$this->CI->pages_model->delete($this->page['pageid']);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize page tree
	 *
	 * Generates an array from the pageid of this page all the way to it's oldest grandparent's pageid
	 *
	 * @access	public
	 * @return	void
	 */	
	function initialize_page_tree()
	{
		if (isset($this->page['pageid']) && !isset($this->page_tree))
		{
			$this->page_tree = array();
			$this->CI->pages_model->get_parent_tree($this->page['pageid'], $this->page_tree);
			$this->page_tree = array_combine($this->page_tree, $this->page_tree);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Save
	 *
	 * Save page properties
	 *
	 * @access	public
	 * @return	int    new pageid from page creation
	 */	
	function save()
	{
		$this->CI->load->helper('string');
		$this->CI->load->model(array('files_model', 'posts_model'));
		
		// If the page has a name that's the same as an old placeholder, clear placeholder
		if (isset($this->new_page['urlname']))
		{		
			// Ensure integrity of URL name
			$this->new_page['urlname'] = to_urlname($this->new_page['urlname'], FALSE);
			
			$this->CI->load->model('placeholders_model');
			$placeholder = $this->CI->placeholders_model->get('page', $this->new_page['urlname']);
			if ($placeholder != NULL)
			{
				$this->CI->placeholders_model->delete('page', $this->new_page['urlname']);
			}
		}
		
		// Check if the page wasn't initialized from an existing one
		if ($this->page == NULL) // If it wasn't, create a new page
		{
			$this->new_page['pageid'] = $this->CI->pages_model->add($this->new_page['menu'], $this->new_page['title'], $this->new_page['pageof']);
			$this->page = $this->new_page;
			return $this->page['pageid'];
		}
		else // If it was, update the existing page
		{
			// Updates to subscription, protection and published level filter through all children
			if ($this->new_page['needsubscription'] != $this->page['needsubscription'] || serialize($this->page['protection']) != serialize($this->new_page['protection']) || $this->new_page['published'] != $this->page['published'])
			{
				// Remove protection and re-add it if there was a change
				if (serialize($this->page['protection']) != serialize($this->new_page['protection']))
				{
				
					$this->CI->pages_model->remove_protection($this->page['pageid']);
					foreach ($this->new_page['protection'] as $key => $value)
					{
						$this->CI->pages_model->add_protection($this->page['pageid'], $key);
					}
					if (sizeof($this->new_page['protection']))
					{
						$this->new_page['protected'] = 1;
					}
					else
					{
						$this->new_page['protected'] = 0;
					}
					$this->_initialize_page();
				}
			
				$pages = $this->CI->pages_model->get_children(NULL, $this->page['pageid']);
				foreach ($pages->result_array() as $page) 
				{
					$object = instantiate_library('page', $page['pageid']);
					if ($this->new_page['needsubscription'] != $this->page['needsubscription'])
					{
						$object->new_page['needsubscription'] = $this->new_page['needsubscription'];
					}
					// If protection changes
					if (serialize($this->page['protection']) != serialize($this->new_page['protection']))
					{
						$object->new_page['protection'] = $this->new_page['protection'];
					}
						if ($this->new_page['published'] != $this->page['published'])
					{
						$object->new_page['published'] = $this->new_page['published'];
					}
					$object->save();
				}
				$posts = $this->CI->posts_model->get_page_posts_all($this->page['pageid']);
				foreach ($posts->result_array() as $post)
				{
					$files = $this->CI->files_model->get_attached("post",$post['postid']);
					foreach ($files->result_array() as $file) 
					{
						$object = instantiate_library('file', $file['fileid']);
						$object->manage();
					}
				}
				$files = $this->CI->files_model->get_attached("page",$this->page['pageid']);
				foreach ($files->result_array() as $file) 
				{
					$object = instantiate_library('file', $file['fileid']);
					$object->manage();
				}
			}
			if ($this->new_page['content'] != $this->page['content'])
			{
				$this->new_page['datemodified'] = date('YmdHis');
			}
			if ($this->new_page['title'] != $this->page['title'])
			{
				$this->new_page['title'] = reduce_spacing($this->new_page['title']);
			}
			// If the page post name setting has changed, swap all child posts to DATE/POSTNAME format or PAGENAME/POST/POSTNAME format
			if ($this->new_page['pagepostname'] != $this->page['pagepostname'])
			{
				$child_posts = $this->CI->posts_model->get_page_posts_all($this->page['pageid']);
				foreach ($child_posts->result_array() as $child_post)
				{
					$object = instantiate_library('post', $child_post['postid']);
					if ($this->new_page['pagepostname'])
					{
						$object->new_post['urlname'] = $this->page['urlname'].'/post/'.substr($object->post['urlname'], strrpos($object->post['urlname'], '/')+1);
					}
					else
					{
						$object->new_post['urlname'] = date("Ymd", strtotime($object->post['date'])).'/'.substr($object->post['urlname'], strrpos($object->post['urlname'], '/')+1);
					}
					$object->save();
				}
			}
			// If the menu changes, ensure all child pages are set to that menu
			if ($this->new_page['menu'] != $this->page['menu'])
			{
				$pages = $this->CI->pages_model->get_children(NULL, $this->page['pageid']);
				foreach ($pages->result_array() as $page) 
				{
					$object = instantiate_library('page', $page['pageid']);
					$object->new_page['menu'] = $this->new_page['menu'];
					$object->save();
				}		
			}
			// If the page url name changes
			if ($this->new_page['urlname'] != $this->page['urlname'])
			{
				// Ensure new url name doesn't collide with old one
				$this->new_page['urlname'] = $this->suggest();
				
				// Add placeholder for URL name change if published
				if ($this->new_page['published'] == 1)
				{
					$this->CI->load->model('placeholders_model');
					$this->CI->placeholders_model->add("page", $this->page['urlname'], $this->new_page['urlname']);
				}
				
				// Change the URL names of any nested URL pages
				$nested_pages = $this->CI->pages_model->get_nested_pages($this->page['urlname']);
				foreach ($nested_pages->result_array() as $nested_page)
				{
					$object = instantiate_library('page', $nested_page['pageid']);
					$object->new_page['urlname'] = str_replace($this->page['urlname'], $this->new_page['urlname'], $object->new_page['urlname']);
					$object->save();
				}
				
				// Change the URL names of any child posts should their names be based off this page's URL
				if ($this->new_page['pagepostname'])
				{
					$child_posts = $this->CI->posts_model->get_page_posts_all($this->page['pageid']);
					foreach ($child_posts->result_array() as $child_post)
					{
						$object = instantiate_library('post', $child_post['postid']);
						$object->new_post['urlname'] = str_replace($this->page['urlname'], $this->new_page['urlname'], $object->new_post['urlname']);
						$object->save();
					}
				}
				
				// Rename corresponding files folder
				$this->CI->files_model->rename_folder("page", str_replace('/', '+', $this->page['urlname']), str_replace('/', '+', $this->new_page['urlname']));
				$this->new_page['content'] = str_replace("/file/page/".$this->page['urlname']."/", "/file/page/".$this->new_page['urlname']."/", $this->new_page['content']);
			
				// Update any blocks that refer to this page's name
				$this->CI->load->model('blocks_model');
				$blockinstances = $this->CI->blocks_model->get_variable_blocks('page', $this->page['urlname']);
				foreach ($blockinstances->result_array() as $blockinstance)
				{
					$object = instantiate_library('block', $blockinstance['blockinstanceid']);
					$object->new_block['values']['page'] = $this->new_page['urlname'];
					$object->save();
				}
				
				// Update any internal link pages that refer to this page
				$object = instantiate_library('page', '/'.$this->page['urlname'], 'link');
				if (isset($object->page['pageid']))
				{
					$object->new_page['link'] = '/'.$this->new_page['urlname'];
					$object->save();
				}
			}
			$this->CI->pages_model->update($this->page['pageid'], $this->new_page);
			$this->page = $this->new_page;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Suggest
	 *
	 * Check to see if the new URL name already exists and suggest a new name
	 *
	 * @access	public
	 * @param   string proposed_name
	 * @return	string urlname that is available
	 */	
	function suggest($proposed_name = NULL)
	{
		if ($proposed_name == NULL)
		{
			$proposed_name = $this->new_page['urlname'];
		}
		
		$suggestion = $proposed_name;
		$i=1;
		$object = instantiate_library('page', $proposed_name, 'urlname');
		while (isset($object->page['pageid']) && $object->page['pageid'] != $this->page['pageid'])
		{
			$i++;
			$suggestion = $proposed_name.'-'.$i;
			$object = instantiate_library('page', $suggestion, 'urlname');
		}
		return $suggestion;
	}
}