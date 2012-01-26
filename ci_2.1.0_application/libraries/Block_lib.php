<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb block library
 *
 * Initializes a block instance and runs checks and operations on that block
 * Blocks are segments of code that can be inserted into pages that dynamically pull website/internet data
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Block_lib {

	public  $block     = array();
	public  $new_block = array();
	public  $pagination;
	public  $last_modified;
	private $contents  = array();
	private $error;

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function Block_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('blocks_model');
		if (isset($params['id']))
		{
			$this->block = $this->CI->blocks_model->get($params['id']);
			$this->_initialize_block();
			$this->new_block = $this->block;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize block
	 *
	 * Load the parent block values and variables
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_block()
	{
		if (isset($this->block['function']))
		{
			$this->pagination = FALSE;
			$this->block['parent'] = $this->CI->blocks_model->get_function($this->block['function']);
			$this->block['variables'] = $this->CI->blocks_model->get_variables($this->block['function']);
			$this->block['values'] = array();
			$values = $this->CI->blocks_model->get_instance_values($this->block['blockinstanceid']);
			foreach($values->result_array() as $value)
			{
				$variablename = $value['variablename'];
				$this->block['values'][$variablename] = $value['value'];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Delete a block instance
	 *
	 * @access	public
	 * @return	void
	 */
	function delete()
	{
		// Update page content to reflect deleted block
		$object = instantiate_library('page', $this->block['pageid']);
		if (isset($object->page['pageid']))
		{
			$object->new_page['content'] = str_replace(
													"%block_".$this->block['title']."%",
													"",
													$object->page['content']);
			$object->save();
		}

		$this->CI->blocks_model->delete($this->block['blockinstanceid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate
	 *
	 * Calls the block function to generate view data and potential errors
	 *
	 * @access	private
	 * @return	void
	 */
	function _generate()
	{
		// Clear output
		$this->contents = array();
		$this->error = "";

		if (isset($this->block['function']))
		{
			$function = "_block_".$this->block['function'];
			if (method_exists('Block_lib', $function))
			{
				$this->$function();
			}
			else
			{
				$this->error = $this->CI->load->view('block_error', array("content" => "Invalid function '$function', database is specifying a block type not programmed into library."), TRUE);
			}
		}
		else
		{
			$this->error = $this->CI->load->view('block_error', array("content" => "Invalid block name."), TRUE);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Output
	 *
	 * Output block data to the web page
	 *
	 * @access	public
	 * @param   optional parameter for a page tree
	 * @return	string formatted block views
	 */
	function output($page_tree = NULL)
	{
		// Get block view contents
		$this->_generate();
		$output = "";

		// If there were no errors, do a couple of final checks
		if ($this->error == NULL)
		{
			// Don't render block if it's function isn't enabled and it isn't an internally defined block
			if (isset($this->block['parent']) && !$this->block['parent']['enabled'])
			{
				$this->error = "Block type isn't enabled";
			}

			// Don't render block if a page tree is specified, and this block isn't attached to any of it or the site
			if (isset($page_tree) && $this->block['pageid'] != '0' && !isset($page_tree[$this->block['pageid']]))
			{
				$this->error = "Block exists but is not usable on this page";
			}
		}

		// If error message was generated, render it instead of data
		if ($this->error != NULL)
		{
			// Only return error output if block allows it
			if (!isset($this->block['feedback']) || $this->block['feedback'])
			{
				$output = $this->CI->load->view('block_error', array("content" => $this->error), TRUE);
			}
		}
		else
		{
			if ($this->pagination)
			{
				array_push($this->contents, array('view' => 'content', 'data' => array('content' => $this->CI->pagination->create_links())));
			}
			foreach ($this->contents as $content)
			{
				$output .= $this->CI->load->view($content['view'], $content['data'], TRUE);
			}
			if ($this->pagination)
			{
				$output = '<div id="pagination_block">'.$output.'</div>';
			}
		}

		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Output rss
	 *
	 * Output block data to rss feed
	 *
	 * @access	public
	 * @return	string  formatted block views
	 */
	function output_rss()
	{
		// Get block view contents
		$this->_generate();
		if ($this->error == NULL) // If there was no error, generate RSS
		{
			$output = "";
			foreach ($this->contents as $content)
			{
				$view = "rss_block_".$this->block['function'];
				if (isset($content['data']))
				{
					$output .=  $this->CI->load->view($view, $content['data'], TRUE);
				}
				else
				{
					$output .=  $this->CI->load->view($view, NULL, TRUE);
				}
			}
			return $output;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 *
	 * Save block properties
	 *
	 * @access	public
	 * @return	array   information from block creation
	 */
	function save()
	{
		// Check if the block wasn't initialized from an existing one
		if ($this->block == NULL) // If it wasn't, create a new block
		{
			$this->new_block['blockinstanceid'] = $this->CI->blocks_model->add($this->new_block['pageid'], $this->new_block['function'], $this->new_block['title']);
			$this->block = $this->new_block;
		}
		else // If it was, update the existing block
		{
			// Whenever a block instance id has been updated, clear and add any values
			$this->CI->blocks_model->remove_instance_values($this->block['blockinstanceid']);
			if (isset($this->new_block['values']))
			{
				foreach ($this->new_block['values'] as $key => $value)
				{
					if ($value != "")
					{
						$this->CI->blocks_model->add_value($this->block['blockinstanceid'], $key, $value);
					}
				}
			}

			// If block has been set to be rss or pagination, remove others from rss or pagination for that page
			/*
			if ($this->new_block['rss'] != $this->rss && $this->new_block['rss'] == 1)
			{
				$blocks = $this->CI->blocks_model->get_page_blocks($this->block['pageid']);
				foreach ($blocks->result_array() as $block)
				{
					if ($block['blockinstanceid'] != $this->block['blockinstanceid'])
					{
						$object = instantiate_library('block', $block['blockinstanceid']);
						$object->new_block['rss'] = 0;
						$object->save();
					}
				}
			}
			if ($this->new_block['pagination'] != $this->pagination && $this->new_block['pagination'] == 1)
			{
				$blocks = $this->CI->blocks_model->get_page_blocks($this->block['pageid']);
				foreach ($blocks->result_array() as $block)
				{
					if ($block['blockinstanceid'] != $this->block['blockinstanceid'])
					{
						$object = instantiate_library('block', $block['blockinstanceid']);
						$object->new_block['pagination'] = 0;
						$object->save();
					}
				}
			}*/

			// If the title changes, update page content
			if ($this->new_block['title'] != $this->block['title'])
			{
				if ($this->block['pageid'] == 0) // it's a site-wide block, find all pages that may reference it
				{
					$this->CI->load->model('pages_model');
					$pageids = $this->CI->pages_model->search("%block_".$this->block['title']);
					foreach ($pageids->result_array() as $pageid)
					{
						$object = instantiate_library('page', $pageid['pageid']);
						if (isset($object->page['pageid']))
						{
							$object->new_page['content'] = str_replace(
																	"%block_".$this->block['title']."%",
																	"%block_".$this->new_block['title']."%",
																	$object->page['content']);
							$object->save();
						}
					}
				}
				else // update specific page that block is attached to
				{
					$object = instantiate_library('page', $this->block['pageid']);
					if (isset($object->page['pageid']))
					{
						$object->new_page['content'] = str_replace(
																"%block_".$this->block['title']."%",
																"%block_".$this->new_block['title']."%",
																$object->page['content']);
						$object->save();
					}
				}
			}

			$this->CI->blocks_model->update($this->block['blockinstanceid'], $this->new_block);
			$this->block = $this->new_block;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set default
	 *
	 * Sets a block as a pagination or rss default or not
	 *
	 * @access	public
	 * @param	int      pageid
	 * @param   boolean  enabled
	 * @return	void
	 */
	function set_default($pageid, $type, $enabled = TRUE)
	{
		$this->CI->blocks_model->remove_default_block($pageid, $type);
		if ($enabled)
		{
			$this->CI->blocks_model->set_default_block($this->block['blockinstanceid'], $pageid, $type);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Block functions below
	 */
	// --------------------------------------------------------------------

	/**
	 * Authors block
	 *
	 * Authors block generates biographies of the contributors of the website
	 * Expects:
	 * detail - optional value, formatted 'listing|full'
	 * sort - optional value, formatted 'alphabetical|chronological';
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_authors()
	{
		$this->CI->load->helper(array('pagination', 'picture'));
		$this->CI->load->model(array('users_model'));

		// Set defaults
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "full";
		}
		if (!isset($this->block['values']['sort']))
		{
			$this->block['values']['sort'] = "alphabetical";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 3;
		}

		// Get data and process
		$this->offset = 0;
		if ($this->pagination)
		{
			$this->count = $this->CI->users_model->get_published_authors_count();
			$this->offset = generate_pagination($this->count, $this->block['values']['limit']);
		}
		$this->authors = $this->CI->users_model->get_published_authors($this->block['values']['limit'], $this->offset, $this->block['values']['sort']);
		if ($this->authors->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "There are no authors yet.")));
		}
		else
		{
			$count = $this->authors->num_rows();
			$current = 0;
			foreach ($this->authors->result_array() as $author)
			{
				$current++;
				$object = instantiate_library('user', $author['userid']);
				if (!isset($this->last_modified) || $this->last_modified < $object->user['datemodified'])
				{
					$this->last_modified = $object->user['datemodified'];
				}
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("user" => $object->user, "count" => $count, "current" => $current)));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Authors_new block
	 *
	 * Authors_new block generates listing of post authors on the site in order of their first contribution
	 * Expects:
	 * detail - optional value, formatted 'listing|full'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_authors_new()
	{
		$this->CI->load->helper(array('pagination', 'picture'));
		$this->CI->load->model(array('users_model', 'comments_model'));

		// Set defaults
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "full";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 3;
		}

		// Get data and process
		$this->offset = 0;
		if ($this->pagination)
		{
			$this->count = $this->CI->users_model->get_published_authors_new_count();
			$this->offset = generate_pagination($this->count, $this->block['values']['limit']);
		}
		$this->authors = $this->CI->users_model->get_published_authors_new($this->block['values']['limit'], $this->offset);
		if ($this->authors->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "There are no authors yet.")));
		}
		else
		{
			$count = $this->authors->num_rows();
			$current = 0;
			foreach ($this->authors->result_array() as $author)
			{
				$current++;
				$object = instantiate_library('user', $author['userid']);
				if (!isset($this->last_modified) || $this->last_modified < $object->user['datemodified'])
				{
					$this->last_modified = $object->user['datemodified'];
				}
				$author['commentcount'] = $this->CI->comments_model->get_post_comments_count($author['postid']);
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("author" => $author, "user" => $object->user, "count" => $count, "current" => $current)));
			}
		}

	}

	// --------------------------------------------------------------------

	/**
	 * Breadcrumb block
	 *
	 * Breadcrumb block generates breadcrumb navigation for a page
	 * Expects:
	 * page - optional value, formatted '[A-Za-z0-9-_]+'
	 * home - optional value, formatted '/d*'
	 * placeholders - optional value, formatted 'yes|no'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_breadcrumb()
	{
		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['placeholders']))
		{
			$this->block['values']['placeholders'] = "no";
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		// Get data and process
		$object = instantiate_library('page', $this->block['values']['page'], 'urlname');
		if (!isset($object->page['pageid']))
		{
			$this->error = "There is no page with the name '".$this->block['values']['page']."' to create breadcrumb navigation.";
		}
		else
		{
			$links = array();

			// If we are on a post, grab it
			if (isset($this->CI->post_urlname))
			{
				$post = instantiate_library('post', $this->CI->post_urlname, 'urlname');
				if (isset($post->post['postid']))
				{
					array_unshift($links, array("title" => $post->post['title'], "url" => $post->post['urlname']));
				}
			}

			array_unshift($links, array("title" => $object->page['title'], "url" => $object->page['urlname']));
			while ($object->page['pageof'] != NULL)
			{
				$object = instantiate_library('page', $object->page['pageof']);
				if (isset($object->page['urlname']) || $this->block['values']['placeholders'] == "yes")
				{
					array_unshift($links, array("title" => $object->page['title'], "url" => $object->page['urlname']));
				}
				else if (isset($object->page['link']) && $this->block['values']['placeholders'] == "yes")
				{
					array_unshift($links, array("title" => $object->page['title'], "url" => $object->page['link']));
				}
			}

			if (isset($this->block['values']['home']))
			{
				array_unshift($links, array("title" => $this->block['values']['home'], "url" => ""));
			}

			array_push($this->contents, array("view" => "block_".$this->block['function'], "data" => array("links" => $links)));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Categories block
	 *
	 * Categories block generates listing of categories on a page
	 * Expects:
	 * page - optional value, formatted 'current|page|nopage'
	 * featured - optional value, formatted 'yes|no|only'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_categories()
	{
		$this->CI->load->model('categories_model');
		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "listing";
		}
		if (!isset($this->block['values']['featured']))
		{
			$this->block['values']['featured'] = "yes";
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		if ($this->block['values']['featured'] == "yes")
		{
			$featured = NULL;
		}
		else if ($this->block['values']['featured'] == "only")
		{
			$featured = "1";
		}
		else if ($this->block['values']['featured'] == "no")
		{
			$featured = "0";
		}

		// Get data and process
		$categories = $this->CI->categories_model->get_published($this->block['values']['page'], $featured);
		if ($categories->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "No topics have been tagged.")));
		}
		else
		{
			$count = $categories->num_rows();
			$current = 0;
			$category['count'] = NULL;
			if ($this->block['values']['page'] == "all" || $this->block['values']['page'] == "nopage")
			{
				$category['url'] = $this->CI->page_urlname;
			}
			else
			{
				$category['url'] = $this->block['values']['page'];
			}

			if ($this->block['values']['detail'] != "tagcloud")
			{
				$category['name'] = "All";
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("category" => $category)));
			}

			$category_list = array();
			$max_count = 0;
			$min_count = 999999;
			foreach ($categories->result_array() as $category)
			{
				$category['count'] = $this->CI->categories_model->get_published_count($category['categoryid'], $this->block['values']['page'], $featured);
				if ($category['count'] < $min_count)
				{
					$min_count = $category['count'];
				}
				else if ($category['count'] > $max_count)
				{
					$max_count = $category['count'];
				}
				array_push($category_list, $category);
			}

			foreach ($category_list as $category)
			{
				$current++;
				if ($this->block['values']['page'] == "all" || $this->block['values']['page'] == "nopage")
				{
					$category['url'] = $this->CI->page_urlname.'/category/'.$category['urlname'];
				}
				else
				{
					$category['url'] = $this->block['values']['page'].'/category/'.$category['urlname'];
				}

				$category['size'] = ((($category['count'] - $min_count + 1) / ($max_count - $min_count + 1) * 100) + 100)."%";

				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("category" => $category, "count" => $count, "current" => $current)));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Comments block
	 *
	 * Comments block generates comments from a specified area
	 * Expects:
	 * page - optional value, formatted 'current|page|nopage'
	 * post - optional value, overrides page, formatted 'urlname'
	 * detail - optional value, formatted 'small|large'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_comments()
	{
		$this->CI->load->helper(array('pagination','picture'));
		$this->CI->load->model('comments_model');

		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "large";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 5;
		}
		if (!isset($this->block['values']['post']))
		{
			$this->block['values']['post'] = NULL;
		}
		if (!isset($this->block['values']['sort']))
		{
			$this->block['values']['sort'] = "desc";
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		// Get data and process
		$this->offset = 0;
		if ($this->pagination)
		{
			$this->count = $this->CI->comments_model->get_published_count($this->block['values']['page'], $this->block['values']['post']);
			$this->offset = generate_pagination($this->count, $this->block['values']['limit']);
		}
		$comments = $this->CI->comments_model->get_published($this->block['values']['limit'], $this->offset, $this->block['values']['page'], $this->block['values']['post'], $this->block['values']['sort']);
		if ($comments->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "No comments have been made.")));
		}
		else
		{
			$count = $comments->num_rows();
			$current = 0;
			foreach ($comments->result_array() as $comment)
			{
				$current++;
				if (!isset($this->last_modified) || $this->last_modified < $comment['date'])
				{
					$this->last_modified = $comment['date'];
				}
				$post = instantiate_library('post', $comment['postid']);
				$comment['post'] = $post->post;
				$user = instantiate_library('user', $comment['userid']);
				$comment['user'] = $user->user;
				if ($comment['user'] != NULL)
				{
					$comment['displayname'] = $comment['user']['displayname'];
					$comment['email'] = $comment['user']['email'];
				}
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("comment" => $comment, "count" => $count, "current" => $current)));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Events block
	 *
	 * Events block generates posts from a specified area
	 * Expects:
	 * page - optional value, formatted 'current|page|nopage'
	 * page_children - optional vlaue, formatted 'no|yes'
	 * timeline - optional value, formatted 'upcoming|previous'
	 * detail - optional value, formatted 'listing|preview|full'
	 * featured - optional value, formatted 'yes|no|only'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_events()
	{
		$this->CI->load->helper(array('pagination', 'picture'));
		$this->CI->load->model(array('events_model', 'categories_model', 'comments_model', 'users_model'));

		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['page_children']))
		{
			$this->block['values']['page_children'] = "no";
		}
		if (!isset($this->block['values']['timeline']))
		{
			$this->block['values']['timeline'] = "upcoming";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "preview";
		}
		if (!isset($this->block['values']['featured']))
		{
			$this->block['values']['featured'] = "yes";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 10;
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		// If pages children are specified, we have to get them
		if ($this->block['values']['page'] != "all" && $this->block['values']['page'] != "nopage" && $this->block['values']['page_children'] == "yes")
		{
			$urlnames = explode(";",$this->block['values']['page']);
			$this->block['values']['page'] = "";
			for ($i=0; $i<sizeof($urlnames); $i++)
			{
				if ($i != 0)
				{
					$this->block['values']['page'] .= ";";
				}
				$this->block['values']['page'] .= $urlnames[$i];
				$children = array();
				$this->CI->pages_model->get_children_tree($urlnames[$i], $children);
				foreach ($children as $child)
				{
					$this->block['values']['page'] .= ";".$child;
				}
			}
		}

		if ($this->block['values']['featured'] == "yes")
		{
			$featured = NULL;
		}
		else if ($this->block['values']['featured'] == "only")
		{
			$featured = "1";
		}
		else if ($this->block['values']['featured'] == "no")
		{
			$featured = "0";
		}

		// Get data and process
		$this->offset = 0;
		if ($this->pagination)
		{
			$this->count = $this->CI->events_model->get_published_count($this->block['values']['timeline'], $this->block['values']['page'], $featured);
			$this->offset = generate_pagination($this->count, $this->block['values']['limit']);
		}
		$events = $this->CI->events_model->get_published($this->block['values']['limit'], $this->offset, $this->block['values']['timeline'], $this->block['values']['page'], $featured);
		if ($events->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "There are no ".$this->block['values']['timeline']." events.")));
		}
		else
		{
			$count = $events->num_rows();
			$current = 0;
			foreach ($events->result_array() as $event)
			{
				$current++;
				if (!isset($this->last_modified) || $this->last_modified < $event['datemodified'])
				{
					$this->last_modified = $event['datemodified'];
				}
				$event['canedit'] = FALSE;
				if ($this->CI->acl->allow('post', 'event', FALSE, 'post', $event['postid']))
				{
					$event['canedit'] = TRUE;
				}
				if ($event['userid'] != NULL)
				{
					$user = instantiate_library('user', $event['userid']);
					$event['user'] = $user->user;
				}
				$event['categories'] = $this->CI->categories_model->get_by_post_published($event['postid']);
				$event['commentcount'] = $this->CI->comments_model->get_post_comments_count($event['postid']);
				$file = instantiate_library('file', $event['imageid']);
				if (isset($file->file['fileid']))
				{
					$event['image'] = $file->file['urlpath'];
				}
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("event" => $event, "count" => $count, "current" => $current)));
			}

		}
	}

	// --------------------------------------------------------------------

	/**
	 * Facebook block
	 *
	 * Facebook block adds a Facebook like button
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_facebook()
	{
		if (isset($this->CI->post_urlname))
		{
			$urlname = $this->CI->post_urlname;
		}
		else if (isset($this->CI->page_urlname))
		{
			$urlname = $this->CI->page_urlname;
		}

		if (!isset($urlname))
		{
			if ($this->block['feedback'])
			{
				array_push($this->contents, array("view" => "content", "data" => array("content" => "Error determining URL.")));
			}
		}
		else
		{
			array_push($this->contents, array("view" => "block_".$this->block['function'], "data" => array("urlname" => urlencode(base_url().$urlname))));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Files block
	 *
	 * Files block generates a list of attached files with information
	 * Expects:
	 * page - optional value, formatted 'current|page|nopage'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_files()
	{
		$this->CI->load->model(array('pages_model', 'files_model'));
		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}

		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		// Get data and process
		$object = instantiate_library('page', $this->block['values']['page'], 'urlname');
		if ($object->page['pageid'] == NULL && $this->block['values']['page'] != "nopage")
		{
			$this->error = "There is no page with the name '".$this->block['values']['page']."' to pull files from.";
		}
		else
		{
			$this->files = $this->CI->files_model->get_attached_listed("page",$object->page['pageid']);
		}

		if (isset($this->files))
		{
			$count = $this->files->num_rows();
			$current = 0;
			foreach ($this->files->result_array() as $file)
			{
				$current++;
				$object = instantiate_library('file', $file['fileid']);
				if (!isset($this->last_modified) || $this->last_modified < $object->file['filemodified'])
				{
					$this->last_modified = $object->file['filemodified'];
				}
				array_push($this->contents, array("view" => "block_".$this->block['function'], "data" => array("file" => $object->file, "count" => $count, "current" => $current)));
			}
			if ($this->files->num_rows() == 0)
			{
				array_push($this->contents, array("view" => "content", "data" => array("content" => "No files available for download.")));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Flickr block
	 *
	 * Flickr block generates images from a flickr feed
	 * Expects:
	 * query - formatted '&userid=\S+&tags=\S+|&userid=\S+|&tags=\S+'
	 * size - optional value, formatted 'square|thumbnail|small|medium'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_flickr()
	{
		$flickrviews = array('square' => 'thumbnail', 'thumbnail' => 'thumbnail', 'small' => 'full', 'medium' => 'full');
		$flickrextension = array('square' => '_s.', 'thumbnail' => '_t.', 'small' => '_m.', 'medium' => '.');
		// Set defaults
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 8;
		}
		if (!isset($this->block['values']['size']))
		{
			$this->block['values']['size'] = 'small';
		}
		if (isset($this->block['values']['query']))
		{
			$this->CI->load->library('simplepie');
			$this->CI->simplepie->set_feed_url("http://api.flickr.com/services/feeds/photos_public.gne?".$this->block['values']['query']."&format=rss_200");
			$this->CI->simplepie->init();
			$photos = $this->CI->simplepie;
			foreach ($photos->get_items(0, $this->block['values']['limit']) as $photo)
			{
				preg_match_all('/<img src="([^"]*)"([^>]*)>/i', $photo->get_description(), $matches);
				$url = explode('/', $matches[1][0]);
				$filename = array_pop($url);
				$url[] = preg_replace('/(_(s|t|m|b))?\./i', $flickrextension[$this->block['values']['size']], $filename);
				$url = implode('/', $url);
				array_push($this->contents, array("view" => "block_".$this->block['function']."_".$flickrviews[$this->block['values']['size']], "data" => array("url" => $url, "photo" => $photo)));
			}
		}
		else
		{
			$this->error = "No query specified for flickr results.";
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Form block
	 *
	 * Form block generates CSRF code so that custom forms can be put on to a page
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_form()
	{
		if ($this->CI->config->item('csrf_protection'))
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => '<input type="hidden" name="'.$this->CI->security->get_csrf_token_name().'" value="'.$this->CI->security->get_csrf_hash().'" />')));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Image block
	 *
	 * Image block generates page image
	 * Expects:
	 * page - optional value, formatted 'current|page'
	 * detail - optional value, formatted 'image|filename'
	 * stock - optional value, formatted 'yes|no'
	 * maxwidth - optional value, formatted '\d+'
	 * maxheight - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_image()
	{
		$this->CI->load->helper('picture');
		$this->CI->load->model('pages_model');

		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "image";
		}
		if (!isset($this->block['values']['stock']))
		{
			$this->block['values']['stock'] = "yes";
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		// Get data and process
		$page = instantiate_library('page', $this->block['values']['page'], 'urlname');
		if (!isset($page->page['pageid']))
		{
			$this->error = "There is no page with the name '".$this->block['values']['page']."' to pull posts from.";
		}
		else
		{
			$image = NULL;
			$file = instantiate_library('file', $page->page['imageid']);
			if (isset($file->file['fileid']))
			{
				$image = $file->file['urlpath'];
			}
			else if ($this->block['values']['stock'] == "yes")
			{
				$image = stock_image($page->page['pageid']);
				$image = $image['urlpath'];
			}

			if (isset($image))
			{
				$width = isset($this->block['values']['maxwidth']) ? $this->block['values']['maxwidth'] : NULL;
				$height = isset($this->block['values']['maxheight']) ? $this->block['values']['maxheight'] : NULL;
				$image = size_image($image, $width, $height);
				array_push($this->contents, array("view" => 'block_image_'.$this->block['values']['detail'], "data" => array("image" => base_url().$image)));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Menu block
	 *
	 * Menu block generates a menu of page siblings
	 * Expects:
	 * page - optional value, formatted 'current|page'
	 * back_button - optional value, formatted 'no|yes'
	 * menu - optional value, formatted 'menu'
	 * detail - optional value, formatted 'adxmenu|horizontal|vertical'
	 * items - optional value, formatted 'neighbours|children'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_menu()
	{
		$this->CI->load->model('pages_model');

		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['back_button']))
		{
			$this->block['values']['back_button'] = "no";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "vertical";
		}
		if (!isset($this->block['values']['items']))
		{
			$this->block['values']['items'] = "neighbours";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = NULL;
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}
		if ($this->block['values']['back_button'] == "yes")
		{
			$this->block['values']['back_button'] = TRUE;
		}
		else
		{
			$this->block['values']['back_button'] = FALSE;
		}

		// Get data and process
		$page = instantiate_library('page', $this->block['values']['page'], 'urlname');
		if (!isset($page->page['pageid']))
		{
			$this->error = "There is no page with the name '".$this->block['values']['page']."' to pull posts from.";
		}
		else
		{
			if (!isset($this->block['values']['menu']))
			{
				$this->block['values']['menu'] = $page->page['menu'];
			}

			$pageid = NULL;
			if ($this->block['values']['items'] == "neighbours" && $page->page['menu'] == $this->block['values']['menu'])
			{
				$pageid = $page->page['pageof'];
			}
			else if ($page->page['menu'] == $this->block['values']['menu'])
			{
				$pageid = $page->page['pageid'];
			}
			else
			{
				$pageid = NULL;
			}

			$this->CI->load->helper('menu_helper');
			$menu_html = generate_menu_html('block_menu_'.$this->block['values']['detail'], $this->block['values']['menu'], $pageid, $this->block['values']['limit'], $this->block['values']['back_button']);

			array_push($this->contents, array("view" => "content", "data" => array("content" => $menu_html)));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Posts block
	 *
	 * Posts block generates posts from a specified area
	 * Expects:
	 * page - optional value, formatted 'current|page|nopage'
	 * page_children - optional vlaue, formatted 'no|yes'
	 * user - optional value, overrides page, formatted 'urlname'
	 * detail - optional value, formatted 'small_listing|listing|preview|full|featured|template'
	 * featured - optional value, formatted 'yes|no|only'
	 * sort - optional value, formatted 'chronological|popularity'
	 * category - optional value, formatted 'all|none|category'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_posts()
	{
		$this->CI->load->helper(array('pagination', 'picture'));
		$this->CI->load->model(array('pages_model', 'posts_model', 'categories_model', 'files_model', 'comments_model', 'users_model'));

		// Set defaults
		if (!isset($this->block['values']['page']))
		{
			$this->block['values']['page'] = "current";
		}
		if (!isset($this->block['values']['page_children']))
		{
			$this->block['values']['page_children'] = "no";
		}
		if (!isset($this->block['values']['detail']))
		{
			$this->block['values']['detail'] = "full";
		}
		if (!isset($this->block['values']['featured']))
		{
			$this->block['values']['featured'] = "yes";
		}
		if (!isset($this->block['values']['sort']))
		{
			$this->block['values']['sort'] = "creation-date";
		}
		if (!isset($this->block['values']['category']))
		{
			$this->block['values']['category'] = "all";
		}
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 3;
		}
		if (!isset($this->block['values']['user']))
		{
			$this->block['values']['user'] = NULL;
		}

		// Convert parameters to something usable
		if ($this->block['values']['page'] == "current")
		{
			$this->block['values']['page'] = $this->CI->config->item('dmcb_default_page');
			if (isset($this->CI->page_urlname) && $this->CI->page_urlname != NULL)
			{
				$this->block['values']['page'] = $this->CI->page_urlname;
			}
		}

		 // If pages children are specified, we have to get them
		if ($this->block['values']['page'] != "all" && $this->block['values']['page'] != "nopage" && $this->block['values']['page_children'] == "yes")
		{
			$urlnames = explode(";",$this->block['values']['page']);
			$this->block['values']['page'] = "";
			for ($i=0; $i<sizeof($urlnames); $i++)
			{
				if ($i != 0)
				{
					$this->block['values']['page'] .= ";";
				}
				$this->block['values']['page'] .= $urlnames[$i];
				$children = array();
				$this->CI->pages_model->get_children_tree($urlnames[$i], $children);
				foreach ($children as $child)
				{
					$this->block['values']['page'] .= ";".$child;
				}
			}
		}

		if ($this->block['values']['sort'] == "popularity")
		{
			$sort = "posts.views DESC";
		}
		else if ($this->block['values']['sort'] == "alphabetical")
		{
			$sort = "posts.title ASC";
		}
		else if ($this->block['values']['sort'] == "modified-date")
		{
			$sort = "posts.datemodified DESC";
		}
		else
		{
			$sort = "posts.date DESC";
		}

		if ($this->block['values']['featured'] == "yes")
		{
			$featured = NULL;
		}
		else if ($this->block['values']['featured'] == "only")
		{
			$featured = "1";
		}
		else if ($this->block['values']['featured'] == "no")
		{
			$featured = "0";
		}

		// Grab category data
		$categoryid = NULL;
		if ($this->block['values']['category'] == "all") // This posts block is open to all categories so grab from URL
		{
			$segments = $this->CI->uri->segment_array();
			$i = 0;
			foreach ($segments as $segment)
			{
				$i++;
				if ($segment == "category" && $i+1 <= $this->CI->uri->total_segments())
				{
					$categoryid = $this->CI->categories_model->get_by_urlname($this->CI->uri->segment($i+1));
				}
			}
		}
		else if ($this->block['values']['category'] == "none") // This posts block doesn't do categories
		{
			$categoryid = "0";
		}
		else // This posts block requires specific category, ignore URL
		{
			$categoryid = $this->CI->categories_model->get_by_urlname($this->block['values']['category']);
		}

		// Report error for invalid category
		if ($categoryid == NULL && $this->block['values']['category'] != "all" && $this->block['values']['category'] != "none") // Check to ensure category specified exists
		{
			$this->error = "There is no category with the name '".$this->block['values']['category']."' to pull posts from.";
		}

		// Get data and process
		$this->offset = 0;
		if ($this->pagination)
		{
			$this->count = $this->CI->posts_model->get_published_count($this->block['values']['page'], $featured, $categoryid, $this->block['values']['user']);
			$this->offset = generate_pagination($this->count, $this->block['values']['limit']);
		}
		$posts = $this->CI->posts_model->get_published($this->block['values']['limit'], $this->offset, $this->block['values']['page'], $featured, $categoryid, $this->block['values']['user'], $sort);
		if ($posts->num_rows() == 0 && $this->block['feedback'])
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "There are no posts at the moment.")));
		}
		else
		{
			$count = $posts->num_rows();
			$current = 0;
			foreach ($posts->result_array() as $post)
			{
				$current++;
				$object = instantiate_library('post', $post['postid']);
				if (!isset($this->last_modified) || $this->last_modified < $object->post['datemodified'])
				{
					$this->last_modified = $object->post['datemodified'];
				}

				$object->post['canedit'] = FALSE;
				if ($this->CI->acl->allow('post', 'edit', FALSE, 'post', $object->post['postid']))
				{
					$object->post['canedit'] = TRUE;
				}
				if ($object->post['userid'] != NULL)
				{
					$user = instantiate_library('user', $object->post['userid']);
					$object->post['user'] = $user->user;
				}

				// Build contributors list
				$object->post['contributorslist'] = array();
				foreach ($object->post['contributors'] as $userid)
				{
					 $user = instantiate_library('user', $userid);
					 array_push($object->post['contributorslist'], $user->user);
				}

				// Get parent if different than current page
				$page = instantiate_library('page', $object->post['pageid']);
				if (isset($page->page['pageid']) && isset($this->CI->page_urlname) && $page->page['urlname'] != $this->CI->page_urlname)
				{
					$object->post['parent'] = $page->page;

				}

				// Get categories
				$object->post['categories'] = $this->CI->categories_model->get_by_post_published($object->post['postid']);

				// Get comments
				$object->post['commentcount'] = $this->CI->comments_model->get_post_comments_count($object->post['postid']);

				// Get featured image
				$object->post['image'] = NULL;
				$file = instantiate_library('file', $object->post['imageid']);
				if (isset($file->file['fileid']))
				{
					$object->post['image'] = $file->file;
				}
				else
				{
					// Stock image code
					$stockimage = stock_image($object->post['postid']);
					if ($stockimage != NULL)
					{
						$object->post['image'] = $stockimage;
					}
				}

				if ($this->block['values']['detail'] != "template")
				{
					array_push($this->contents, array("view" => "block_".$this->block['function']."_".$this->block['values']['detail'], "data" => array("currentpage" => $this->CI->page_urlname, "post" => $object->post, "count" => $count, "current" => $current)));
				}
				else // We are rendering the posts from the template, this is a more taxing process, but necessary for post listings that use custom fields
				{
					// Grab parent page tree if the post is attached to a page
					$page = instantiate_library('page', $object->post['pageid']);
					if (isset($page->page['pageid']))
					{
						$page->initialize_page_tree();
					}

					// Grab post template and values
					if (isset($page->page['post_templateid']))
					{
						$templateid = $page->page['post_templateid'];
					}
					else
					{
						$this->CI->load->helper('template');
						$templateid = template_to_use('template', 'page', $page->page_tree);
					}

					$this->template = instantiate_library('template', $templateid);
					$this->template->initialize_values($object->post['postid']);

					// If there's a page post template, load it up and use it
					if (isset($this->template->template['templateid']))
					{
						// Get post images
						$object->post['images'] = array();
						$fileids = $this->CI->files_model->get_attached_images('post', $object->post['postid']);
						foreach ($fileids->result_array() as $fileid)
						{
							$file = instantiate_library('file', $fileid['fileid']);
							if (isset($file->file['fileid']))
							{
								array_push($object->post['images'], $file->file);
							}
						}

						// Enable moderating tool bar
						$admin_toolbar = NULL;
						if ($object->post['published'] == '1' && $this->CI->acl->allow('post', 'edit', FALSE, 'post', $object->post['postid']))
						{
							$admin_toolbar = $this->CI->load->view('post_admin_toolbar', array('post' => $object->post, 'author' => NULL), TRUE);
						}

						// Render the post
						$post_section = $this->CI->load->view('post_post', array('post' => $object->post, 'next_post' => NULL, 'previous_post' => NULL, 'contributors' => NULL, 'parentpage' => NULL, 'author' => NULL, 'admin_toolbar' => $admin_toolbar), TRUE);
						$featuredimage_section = $this->CI->load->view('post_image', array('postid' => $object->post['postid'], 'image' => $object->post['image']), TRUE);
						$listedimages_section = $this->CI->load->view('post_images', array('postid' => $object->post['postid'], 'image' => $object->post['image'], 'images' => $object->post['images']), TRUE);

						// Load up blocks as necessary
						$post_contents = "";
						$template_contents = preg_split('/(%block_\S+%)/', $this->template->template['content'], -1, PREG_SPLIT_DELIM_CAPTURE);
						foreach ($template_contents as $template_content)
						{
							if (preg_match('/^%block_\S+%$/', $template_content))
							{
								$block = instantiate_library('block', preg_replace('/^%block_(\S+)%$/', '$1', $template_content), 'title');
								$post_contents .= $block->output($page->page_tree);
							}
							else
							{
								$post_contents .= $template_content;
							}
						}

						// Parse for fields and insert values
						foreach ($this->template->fields as $field)
						{
							if (isset($this->template->values[$field['htmlcode']]) && $this->template->values[$field['htmlcode']] != NULL)
							{
								$value = $this->template->values[$field['htmlcode']];
								$label = '\1';
							}
							else
							{
								$value = "";
								$label = ' ';
							}
							$post_contents = str_replace('%'.$field['htmlcode'].'%', $value, $post_contents);
							$post_contents = preg_replace('/%'.$field['htmlcode'].':([^%]*)%/', $label, $post_contents); // Insert any labels dependent on if value exists or not
						}
						$post_contents = str_replace('%contenthere%', $post_section, $post_contents);
						$post_contents = str_replace('%titlehere%', $object->post['title'], $post_contents);
						$post_contents = str_replace('%commentshere%', "", $post_contents);
						$post_contents = str_replace('%fileshere%', "", $post_contents);
						$post_contents = str_replace('%referenceshere%', "", $post_contents);
						$post_contents = str_replace('%pingbackshere%', "", $post_contents);
						$post_contents = str_replace('%featuredimage%', $featuredimage_section, $post_contents);
						$post_contents = str_replace('%listedimages%', $listedimages_section, $post_contents);

						array_push($this->contents, array("view" => 'post_wrapper_dynamic', "data" => array('content' => $post_contents)));
					}
					else
					{
						array_push($this->contents, array("view" => "block_".$this->block['function']."_full", "data" => array("currentpage" => $this->CI->page_urlname, "post" => $object->post, "count" => $count, "current" => $current)));
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Scrape block
	 *
	 * Scrapes paragraphs from another page
	 * Expects:
	 * tag - optional value, '\d+'
	 * start - formatted '\d+'
	 * limit - formatted '\d+'
	 * page - formatted '\S+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_scrape()
	{
		// Set defaults
		if (!isset($this->block['values']['tag']))
		{
			$this->block['values']['tag'] = 'p';
		}
		if (!isset($this->block['values']['start']) || !isset($this->block['values']['limit']) || !isset($this->block['values']['page']))
		{
			$this->error = "Missing values required.";
		}
		else
		{
			$object = instantiate_library('page', $this->block['values']['page'], 'urlname');
			if (!isset($object->page['pageid']))
			{
				$this->error = "Page specified does not exist.";
			}
			else
			{
				if ($this->block['values']['tag'] == "br")
				{
					$this->CI->load->helper('url');
					preg_match_all("/(<br \/>)+(.*?)<br \/>/", auto_link(strip_tags($object->page['content'], '<br>')), $matches);
				}
				else
				{
					preg_match_all("/<".$this->block['values']['tag']."(\s.*)?>(.*)<\/".$this->block['values']['tag'].">/", $object->page['content'], $matches);
				}
				$count = ($this->block['values']['limit']-1)+$this->block['values']['start'];
				if (sizeof($matches[0]) < $count)
				{
					$count = sizeof($matches[0]);
				}
				$current = $this->block['values']['start']-1;
				for ($current; $current<$count; $current++)
				{
					if (isset($matches[0][$current]) && $matches[0][$current] != NULL)
					{
						array_push($this->contents, array("view" => "block_".$this->block['function'], "data" => array("content" => $matches[0][$current], "count" => $count, "current" => $current+1)));
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Twitter block
	 *
	 * Twitter block generates tweets from a feed
	 * Expects:
	 * Query - formatted '&from=\S+&tag=\S+|&from=\S+|&tag=\S+'
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_twitter()
	{
		// Set defaults
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 5;
		}
		if (isset($this->block['values']['query']))
		{
			$hasuser = preg_match('/&from=\S+/', $this->block['values']['query']);
			$hastag = preg_match('/&tag=\S+/', $this->block['values']['query']);
			if (!$hasuser && !$hastag)
			{
				$this->error = "Incorrectly formed query.";
			}
			else
			{
				$this->CI->load->library('simplepie');
				$this->CI->simplepie->set_feed_url("http://search.twitter.com/search.atom?q=".$this->block['values']['query']);
				$this->CI->simplepie->init();
				$tweet_count = 0;
				$tweets = $this->CI->simplepie;
				foreach ($tweets->get_items(0, $this->block['values']['limit']) as $tweet)
				{
					$tweet_count++;
					if (!$hasuser && $hastag)
					{
						$namepieces = explode(" ",$tweet->get_author()->get_name());
						$object = instantiate_library('user', $namepieces[0], 'twitter');
						$user = $object->user;
						array_push($this->contents, array("view" => "block_".$this->block['function']."_tag", "data" => array("tweet" => $tweet, "author" => $namepieces[0], "user" => $user)));
					}
					else
					{
						array_push($this->contents, array("view" => "block_".$this->block['function']."_user", "data" => array("tweet" => $tweet)));
					}
				}
				if (!$tweet_count)
				{
					$this->error = "No recent twitter activity.";
				}
			}
		}
		else
		{
			$this->error = "No query specified for twitter results.";
		}
	}

	// --------------------------------------------------------------------

	/**
	 * User_displayname block
	 *
	 * User_displayname block generates email address from signed on user
	 * Expects:
	 * User to be signed on
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_user_displayname()
	{
		if ($this->CI->session->userdata('signedon'))
		{
			$object = instantiate_library('user', $this->CI->session->userdata('userid'));
			array_push($this->contents, array("view" => "content", "data" => array("content" => $object->user['displayname'])));
		}
		else
		{
			$this->error = "Cannot show email address when user isn't signed on. This block should only appear on pages that require a sign on.";
		}
	}

	// --------------------------------------------------------------------

	/**
	 * User_email block
	 *
	 * User_email block generates email address from signed on user
	 * Expects:
	 * User to be signed on
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_user_email()
	{
		if ($this->CI->session->userdata('signedon'))
		{
			$object = instantiate_library('user', $this->CI->session->userdata('userid'));
			array_push($this->contents, array("view" => "content", "data" => array("content" => $object->user['email'])));
		}
		else
		{
			$this->error = "Cannot show email address when user isn't signed on. This block should only appear on pages that require a sign on.";
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Wall block
	 *
	 * Wall block generates list of anonymous wall postings
	 * Expects:
	 * limit - optional value, formatted '\d+'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_wall()
	{
		$this->CI->load->helper('pagination');
		$this->CI->load->model('walls_model');
		// Set defaults
		if (!isset($this->block['values']['limit']))
		{
			$this->block['values']['limit'] = 3;
		}
		// Get data and process
		$offset = generate_pagination($this->CI->walls_model->get_count(), $this->block['values']['limit']);
		$walls = $this->CI->walls_model->get($this->block['values']['limit'], $offset);
		if ($walls->num_rows() == 0)
		{
			array_push($this->contents, array("view" => "content", "data" => array("content" => "No wall posts have been made.")));
		}
		else
		{
			$count = $walls->num_rows();
			$current = 0;
			foreach ($walls->result_array() as $wall)
			{
				$current++;
				array_push($this->contents, array("view" => "block_".$this->block['function'], "data" => array("wall" => $wall, "count" => $count, "current" => $current)));
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Wrapper block
	 *
	 * Wrapper block wraps code up and processes visibility of code
	 * Expects:
	 * content - formatted '*'
	 * on_category - optional value, formatted 'yes|no|only'
	 * on_pagination - optional value, formatted 'yes|no|only'
	 * on_signedon - optional value, formatted 'yes|no|only'
	 *
	 * @access	private
	 * @return	void
	 */
	function _block_wrapper()
	{
		$this->CI->load->helper('pagination');
		// Set defaults
		if (!isset($this->block['values']['on_categorization']))
		{
			$this->block['values']['on_categorization'] = "yes";
		}
		if (!isset($this->block['values']['on_pagination']))
		{
			$this->block['values']['on_pagination'] = "yes";
		}
		if (!isset($this->block['values']['on_signedon']))
		{
			$this->block['values']['on_signedon'] = "yes";
		}

		if (
			((($this->block['values']['on_categorization'] == "yes" || $this->block['values']['on_categorization'] == "only") && (preg_match('/\/category\/\S+$/', $this->CI->uri->uri_string()))) ||
			(($this->block['values']['on_categorization'] == "yes" || $this->block['values']['on_categorization'] == "no") && (!preg_match('/\/category\/\S+$/', $this->CI->uri->uri_string())))) &&
			((($this->block['values']['on_pagination'] == "yes" || $this->block['values']['on_pagination'] == "only") && (get_pagination_uri() != NULL)) ||
			(($this->block['values']['on_pagination'] == "yes" || $this->block['values']['on_pagination'] == "no") && (get_pagination_uri() == NULL))) &&
			((($this->block['values']['on_signedon'] == "yes" || $this->block['values']['on_signedon'] == "only") && ($this->CI->session->userdata('signedon'))) ||
			(($this->block['values']['on_signedon'] == "yes" || $this->block['values']['on_signedon'] == "no") && (!$this->CI->session->userdata('signedon'))))
		)
		{
			$content = "";
			$pagecontents = preg_split('/(%block_\S+%)/', $this->block['values']['content'], -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($pagecontents as $pagecontent)
			{
				if (preg_match('/^%block_\S+%$/', $pagecontent))
				{
					if (preg_replace('/^%block_(\S+)%$/', '$1', $pagecontent) == $this->block['title'])
					{
						$this->error = "Block cannot reference itself.";
					}
					else
					{
						$page = $this->CI->config->item('dmcb_default_page');
						if (isset($this->CI->page_urlname)) // Grab the current page the block is on, if it exists
						{
							$page = $this->CI->page_urlname;
						}

						$page_object = instantiate_library('page', $page, 'urlname');
						$page_object->initialize_page_tree();
						$object = instantiate_library('block', preg_replace('/^%block_(\S+)%$/', '$1', $pagecontent), 'title');

						// If we have a block on the page that is paginated AND we are using it, make sure to focus to it
						if (isset($object->block['pagination']) && $object->block['pagination'])
						{
							if (get_pagination_uri() != NULL)
							{
								$this->CI->focus = "pagination_block";
							}
						}

						$content .= $object->output($page_object->page_tree);
					}
				}
				else
				{
					$content .= $pagecontent;
				}
			}
			array_push($this->contents, array("view" => "content", "data" => array("content" => $content)));
		}
	}
}