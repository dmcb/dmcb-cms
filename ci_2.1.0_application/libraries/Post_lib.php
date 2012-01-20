<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb post library
 *
 * Initalizes a post and runs checks and operations on that post
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Post_lib {

	public  $post     = array();
	public  $new_post = array();

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function Post_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('posts_model');
		if (isset($params['id']))
		{
			$this->post = $this->CI->posts_model->get($params['id']);
			$this->_initialize_post();
			$this->new_post = $this->post;
		}
	} 
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize post
	 *
	 * Load the post contributors
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_post()
	{
		if ($this->CI->acl->enabled('post', 'addcomment'))
		{
			// Future spot of code for specific posts (or parent page) that allow for comments to be enabled or disabled, but for now, always enabled
			$this->post['enabledcomments'] = TRUE;
		}
		else
		{
			$this->post['enabledcomments'] = FALSE;
		}
	
		if (isset($this->post['postid']))
		{
			$this->post['contributors'] = array();
			$contributorlist = $this->CI->posts_model->get_post_contributors($this->post['postid']);
			foreach($contributorlist->result_array() as $contributor)
			{
				array_push($this->post['contributors'], $contributor['userid']);
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Add theme file
	 *
	 * Add a theme file to the post
	 *
	 * @access	public
	 * @param   string  file
	 * @param   string  type
	 * @return	void
	 */	
	function add_theme_file($file, $type)
	{
		$this->CI->posts_model->add_theme_file($this->post['postid'], $file, $type);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Delete
	 *
	 * Delete a post and clears out references
	 *
	 * @access	public
	 * @return	void
	 */	
	function delete()
	{
		$this->CI->load->model(array('files_model', 'events_model', 'categories_model', 'comments_model'));
		$files = $this->CI->files_model->get_attached("post",$this->post['postid']);
		foreach ($files->result_array() as $file) 
		{
			$object = instantiate_library('file', $file['fileid']);
			$object->delete();
		}
		$this->CI->events_model->delete($this->post['postid']);
		$this->CI->categories_model->delete_by_post($this->post['postid']);
		$this->CI->comments_model->delete_by_post($this->post['postid']);
		$this->CI->posts_model->delete($this->post['postid']);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get theme files
	 *
	 * Get all files used in the post's theme
	 *
	 * @access	public
	 * @return	void
	 */	
	function get_theme_files()
	{
		return $this->CI->posts_model->get_post_theme_files($this->post['postid']);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Remove theme files
	 *
	 * Remove all references to theme files
	 *
	 * @access	public
	 * @return	void
	 */	
	function remove_theme_files()
	{
		$this->CI->posts_model->remove_theme_files($this->post['postid']);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Save
	 *
	 * Save post properties
	 *
	 * @access	public
	 * @return	array   information from post creation
	 */	
	function save()
	{
		$this->CI->load->helper('string');
		
		// If the post has a name that's the same as an old placeholder, clear placeholder
		$this->CI->load->model(array('files_model','placeholders_model'));
		
		// Ensure integrity of URL name
		$this->new_post['urlname'] = to_urlname($this->new_post['urlname'], FALSE);
		
		$placeholder = $this->CI->placeholders_model->get('post', $this->new_post['urlname']);
		if ($placeholder != NULL)
		{
			$this->CI->placeholders_model->delete('post', $this->new_post['urlname']);
		}
		
		// Check if the post wasn't initialized from an existing one
		if ($this->post == NULL) // If it wasn't, create a new post
		{
			// Minimum requirements for creating a post
			if (isset($this->new_post['title']) && isset($this->new_post['urlname']) && isset($this->new_post['pageid']) || isset($this->new_post['userid']))
			{
				$this->new_post['title'] = reduce_spacing($this->new_post['title']);
				if (!isset($this->new_post['pageid'])) // If a pageid isn't specified, set it NULL for add
				{
					$this->new_post['pageid'] = NULL;
				}
				if (!isset($this->new_post['userid'])) // If a userid isn't specified, set it NULL for add
				{
					$this->new_post['userid'] = NULL;
				}
				else
				{
					$this->new_post['urlname'] = to_urlname($this->new_post['urlname'], FALSE);
				}
				
				$this->new_post['postid'] = $this->CI->posts_model->add($this->new_post['pageid'], $this->new_post['userid'], $this->new_post['title'], $this->new_post['urlname']);
				$this->post = $this->new_post;
			}
		}
		else // If it was, update the existing post
		{
			// Security of files has changed, update accordingly
			if ($this->new_post['published'] != $this->post['published'] || $this->new_post['needsubscription'] != $this->post['needsubscription'] || $this->new_post['featured'] != $this->post['featured'])
			{
				$files = $this->CI->files_model->get_attached("post",$this->post['postid']);
				foreach ($files->result_array() as $file) 
				{
					$object = instantiate_library('file', $file['fileid']);
					$object->manage();
				}
				// Post is being published for the first time, update time
				if ($this->new_post['published'] != $this->post['published'] && $this->new_post['published'])
				{
					$this->new_post['date'] = date('YmdHis');
					$this->new_post['datemodified'] = date('YmdHis');
					// If the draft had a different date that it was created, update it to the current published date in it's URL (if it's using YYYYMMDD/POSTNAME format)
					if ((date('Ymd', strtotime($this->new_post['date'])) != date('Ymd', strtotime($this->post['date']))) && strpos($this->new_post['urlname'], date('Ymd', strtotime($this->post['date']))) !== FALSE)
					{
						$this->new_post['urlname'] = str_replace(date('Ymd', strtotime($this->post['date'])), date('Ymd', strtotime($this->new_post['date'])), $this->new_post['urlname']);
					}
				}
			}
			// Remove contributor and re-add them if there was a change
			if (array_diff($this->post['contributors'], $this->new_post['contributors']) || array_diff($this->new_post['contributors'], $this->post['contributors']))
			{
				$this->CI->posts_model->remove_contributors($this->post['postid']);
				foreach ($this->new_post['contributors'] as $userid)
				{
					$this->CI->posts_model->add_contributor($this->post['postid'], $userid);
				}
				$this->_initialize_post();
			}
			if ($this->new_post['content'] != $this->post['content'] || $this->new_post['title'] != $this->post['title']) // If post or title have changed, change date and remove 'reviewed'
			{
				$this->new_post['datemodified'] = date('YmdHis');
				$this->new_post['reviewed'] = '0';
			}
			if ($this->new_post['title'] != $this->post['title'])
			{
				$this->new_post['title'] = reduce_spacing($this->new_post['title']);
			}
			if ($this->new_post['urlname'] != $this->post['urlname'])
			{
				// Ensure new url name doesn't collide with old one
				$this->new_post['urlname'] = $this->suggest();
			
				// Add placeholder for URL name change if published
				if ($this->new_post['published'] == 1)
				{
					$this->CI->load->model('placeholders_model');
					$this->CI->placeholders_model->add("post", $this->post['urlname'], $this->new_post['urlname']);
				}
				// Rename corresponding files folder
				$this->CI->files_model->rename_folder("post", str_replace('/', '+', $this->post['urlname']), str_replace('/', '+', $this->new_post['urlname']));
				$this->new_post['content'] = str_replace("/file/post/".$this->post['urlname']."/", "/file/post/".$this->new_post['urlname']."/", $this->new_post['content']);
			
				// Update any blocks that refer to this post's name
				$this->CI->load->model('blocks_model');
				$blockinstances = $this->CI->blocks_model->get_variable_blocks('post', $this->post['urlname']);
				foreach ($blockinstances->result_array() as $blockinstance)
				{
					$object = instantiate_library('block', $blockinstance['blockinstanceid']);
					$object->new_block['values']['post'] = $this->new_post['urlname'];
					$object->save();
				}
				
				// Update any internal link pages that refer to this post
				$object = instantiate_library('page', '/'.$this->post['urlname'], 'link');
				if (isset($object->page['pageid']))
				{
					$object->new_page['link'] = '/'.$this->new_post['urlname'];
					$object->save();
				}
				
				// Update the post CSS accordingly
				$this->new_post['css'] = str_replace(
					"/".$this->post['urlname'],
					"/".$this->new_post['urlname'],
					$this->post['css']);
					
				// Update theme file references
				$theme_files = $this->get_theme_files();
				$this->remove_theme_files();
				foreach ($theme_files->result_array() as $theme_file)
				{
					$this->add_theme_file(str_replace("/".$this->post['urlname'], "/".$this->new_post['urlname'], $theme_file['file']), $theme_file['type']);
				}
			}
			$this->CI->posts_model->update($this->post['postid'], $this->new_post);
			
			// Ping services on first time publish
			if ($this->new_post['published'] != $this->post['published'] && $this->new_post['published'])
			{
				$this->CI->load->helper('pingback');
				ping();
				pingback($this->post['postid']);
			}
			
			$this->post = $this->new_post;
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
			$proposed_name = $this->new_post['urlname'];
		}
		
		$suggestion = $proposed_name;
		$i=1;
		$object = instantiate_library('post', $proposed_name, 'urlname');
		while (isset($object->post['postid']) && $object->post['postid'] != $this->post['postid'])
		{
			$i++;
			$suggestion = $proposed_name.'-'.$i;
			$object = instantiate_library('post', $suggestion, 'urlname');
		}
		return $suggestion;
	}
}