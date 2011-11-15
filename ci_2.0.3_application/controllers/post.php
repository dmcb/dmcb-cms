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
class Post extends MY_Controller {

	function Post()
	{
		parent::__construct();

		$this->load->helper('pagination');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model(array('categories_model', 'comments_model', 'events_model', 'files_model', 'pingbacks_model', 'posts_model', 'users_model', 'views_model'));
	}

	function _remap()
	{
		// If subscriptions enabled, load model
		if ($this->acl->enabled('site', 'subscribe'))
		{
			$this->load->model('subscriptions_model');
		}
		
		// Determine post URL name
		$urlname = "";
		if ($this->uri->total_segments() <= 1) // If no post is specified, go to default post
		{
			$default_post_criteria = $this->config->item('dmcb_default_post');
			$default_post = $this->posts_model->get_published(1, 0,
				$default_post_criteria[0],
				$default_post_criteria[1],
				$default_post_criteria[2],
				$default_post_criteria[3]
			);
			if ($default_post->num_rows() != 0) // Post found with default post criteria
			{
				$postid = $default_post->row_array();
				$this->post = instantiate_library('post', $postid['postid']);
				// This next part doesn't really matter, deep linking to /post/addcomment will make it no longer match to default post setting
				$this->base_segment = substr_count($this->post->post['urlname'], '/')+1; 
			}
		}
		else
		{
			if (preg_match('/^[0-9]{8}$/', $this->uri->segment(1))) // Post URL name can be of format /YYYYMMDD/POSTNAME
			{
				$urlname = $this->uri->segment(1).'/'.$this->uri->segment(2);
				$this->base_segment = 2;
			}
			else // Or of format /PAGENAME/post/POSTNAME
			{
				$segments = $this->uri->segment_array();
				$i = 1;
				while ($segments[$i] != "post" && isset($segments[$i+1]))
				{
					$urlname .= $segments[$i].'/';
					$i++;
				}
				$urlname .= $segments[$i].'/'.$segments[$i+1];
				$this->base_segment = $i+1;
			}
			$this->post = instantiate_library('post', $urlname, 'urlname');
		}
	
		if (isset($this->post->post['postid']))
		{
			// Get potential parent author and parent page of post
			$this->author = instantiate_library('user', $this->post->post['userid']);
			$this->page = instantiate_library('page', $this->post->post['pageid']);
			// If there is a parent page, set it in the system so any blocks referenced in the template will use the parent page as the current page
			if (isset($this->page->page['pageid']))
			{
				$this->page_urlname = $this->page->page['urlname'];
			}
			// Like wise, set the post urlname in the system
			$this->post_urlname = $this->post->post['urlname'];
		}
		
		// No post specified
		if (!isset($this->post->post['postid']))
		{
			// If the link fails but there is a placeholder for it, point them to the new URL
			$this->load->model('placeholders_model');
			$placeholder = $this->placeholders_model->get('post', $urlname);
			if ($placeholder != NULL)
			{
				$this->_redirect(base_url().$placeholder['newname'], $placeholder['redirect']);
			}
			else
			{
				$this->_page_not_found();
			}
		}
		else if (($this->post->post['featured'] == -1 || $this->post->post['published'] == 0 || (isset($this->page->page['pageid']) && !$this->page->page['published'])) && !$this->acl->allow('post', 'edit', TRUE, 'post', $this->post->post['postid']))
		{
			// Post has been heldback or not published, or the parent page is not published, and the user isn't allowed to edit the post, deny them
			$this->_access_denied();
		}
		else if (isset($this->page->page['pageid']) && $this->page->page['published'] && $this->page->page['protected'] && !$this->acl->allow('post', 'edit', TRUE, 'post', $this->post->post['postid']) && !$this->acl->access($this->page->page['protection'], $this->page, NULL, TRUE))
		{
			// Post has a parent page, it is published, but the page is protected and the user isn't allowed to view the page or edit the post, so deny them
			$this->_access_denied();
		}
		else if ($this->acl->enabled('site', 'subscribe') && ($this->post->post['needsubscription'] || (isset($this->page->page['pageid']) && $this->page->page['needsubscription'])) && !$this->subscriptions_model->check($this->session->userdata('userid')) && !$this->subscriptions_model->check_view($_SERVER['REMOTE_ADDR'],$this->post->post['postid']) && !$this->acl->allow('post', 'edit', TRUE, 'post', $this->post->post['postid']))
		{
			// Subscriptions are enabled, and either this post or the post's parent page requires one, and the user isn't allowed to edit the page and doesn't have a subscription, and is out of free views, so deny them
			if ($this->config->item('dmcb_post_subscriptions_free_views') == 0)
			{
				$this->message = "'".$this->post->post['title']."' requires a subscription to view.";
			}
			else if ($this->config->item('dmcb_post_subscriptions_free_views') == 1)
			{
				$this->message = "You have used your ".$this->config->item('dmcb_post_subscriptions_free_views')." free ".$this->config->item('dmcb_post_subscriptions_free_views_range')." view on posts that require a subscription and won't be able to read '".$this->post->post['title']."'.";
			}
			else
			{
				$this->message = "You have used your ".$this->config->item('dmcb_post_subscriptions_free_views')." free ".$this->config->item('dmcb_post_subscriptions_free_views_range')." views on posts that require a subscription and won't be able to read '".$this->post->post['title']."'.";		
			}
			
			if ($this->session->userdata('signedon'))
			{
				$this->message .= "<br/><br/>Your subscription has ended, you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
			}
			else
			{
				$this->message .= "<br/><br/>If you do have a subscription, please <a href=\"".base_url()."signon".uri_string()."\">sign on</a>.<br/>If you don't have a subscription, you can start a free trial by creating an account <a href=\"".base_url()."signon\">here</a>, or you can <a href=\"".base_url()."subscription\">subscribe for a full account</a> for unlimited access.";
			}
			$this->_message('Subscription required', $this->message);
		}
		else
		{
			// We're in, let's load up everything
			$this->post->post['event'] = $this->events_model->get($this->post->post['postid']);
			
			// Get post references and categories
			$this->postreferences = array();
			$postreferences = $this->posts_model->get_references($this->post->post['postid']);	
			$this->post->post['postcategories'] = $this->categories_model->get_by_post_published($this->post->post['postid']);
			$postcategories = $this->categories_model->get_by_post($this->post->post['postid']);
			
			// Construct category values for edit page form
			$this->post->post['categorynames'] = "";
			$this->post->post['categoryvalues'] = "";
			$this->post->post['categories'] = $this->categories_model->get_list();
			
			// Use posted values if available
			if (isset($_POST['categoryvalues']))
			{
				$this->post->post['categoryvalues'] = $this->security->xss_clean($_POST['categoryvalues']);	
				$this->post->post['categorynames'] = $this->security->xss_clean($_POST['categorynames']);
			}
			else if ($postcategories->num_rows != 0) // If category values weren't posted (i.e. editing was attempted), load up the originals
			{
				foreach($postcategories->result_array() as $postcategory)
				{
					if ($postcategory['heldback'])
					{
						$this->post->post['categorynames'] = $this->post->post['categorynames'].$postcategory['name'].' (pending approval);';
					}
					else
					{
						$this->post->post['categorynames'] = $this->post->post['categorynames'].$postcategory['name'].";";
					}
					$this->post->post['categoryvalues'] = $this->post->post['categoryvalues'].$postcategory['categoryid'].";";
				}						
			}
			// Construct previous post values for edit page form
			$this->post->post['previouspostnames'] = "";
			$this->post->post['previouspostvalues'] = "";
			$this->post->post['previousposts'] = array();
			
			// Determine potential references the editor can use by drawing on previous posts by page or by author
			if (isset($this->page->page['pageid']))
			{
				$previousposts = $this->posts_model->get_potential_references_by_page($this->page->page['pageid'],$this->post->post['postid']);
				foreach ($previousposts->result_array() as $previouspost)
				{
					$object = instantiate_library('post', $previouspost['postid']);
					array_push($this->post->post['previousposts'], $object->post);
				}
			}
			else if (isset($this->author->user['userid']))
			{
				$previousposts = $this->posts_model->get_potential_references_by_user($this->author->user['userid'],$this->post->post['postid']);
				foreach ($previousposts->result_array() as $previouspost)
				{
					$object = instantiate_library('post', $previouspost['postid']);
					array_push($this->post->post['previousposts'], $object->post);
				}
			}
			
			// Use posted values if available
			if (isset($_POST['previouspostvalues']))
			{
				$this->post->post['previouspostvalues'] = $this->security->xss_clean($_POST['previouspostvalues']);	
				$this->post->post['previouspostnames'] = $this->security->xss_clean($_POST['previouspostnames']);	
			}
			foreach ($postreferences->result_array() as $postreference)
			{
				$object = instantiate_library('post', $postreference['postid']);
				// If previous post values weren't posted (i.e. editing was attempted), load up the originals
				if ($postreferences->num_rows != 0 && !isset($_POST['previouspostvalues']))
				{
					$this->post->post['previouspostnames'] = $this->post->post['previouspostnames'].$object->post['title'].";";
					$this->post->post['previouspostvalues'] = $this->post->post['previouspostvalues'].$object->post['postid'].";";
				}
				// Regardless, always ensure post references are built
				array_push($this->postreferences, $object->post);
			}						
			
			// Build files list
			$this->listedfiles = array();
			$files = $this->files_model->get_attached_listed("post",$this->post->post['postid']);
			foreach ($files->result_array() as $file)
			{
				$object = instantiate_library('file', $file['fileid']);
				array_push($this->listedfiles, $object->file);
			}
			
			// Build contributors list
			$this->contributors = array();
			foreach ($this->post->post['contributors'] as $userid)
			{
				$object = instantiate_library('user', $userid);
				array_push($this->contributors, $object->user);
			}
			
			// Get user roles for permissions			
			if ($this->acl->allow('post', 'permissions', FALSE, 'post'))
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
					$items = $this->acls_model->get_userlist_by_role($role['roleid'], 'post', $this->post->post['postid']);
					foreach($items->result_array() as $item)
					{
						$object = instantiate_library('user', $item['userid']);
						$object->user['roleid'] = $role['roleid'];
						array_push($users, $object->user);
					}
					array_push($this->userlist, $users);
				}
			}
			
			// Build theme files list			
			if ($this->acl->enabled('post', 'theme'))
			{
				$this->cssfiles = array();
				$this->jsfiles = array();
				$theme_files = $this->post->get_theme_files();
				foreach ($theme_files->result_array() as $theme_file)
				{
					if ($theme_file['type'] == "css")
					{
						array_push($this->cssfiles, $theme_file['file']);
					}
					else if ($theme_file['type'] == "js")
					{
						array_push($this->jsfiles, $theme_file['file']);
					}
				}
			}
		
			// Grab parent page tree if the post is attached to a page
			if (isset($this->page->page['pageid']))
			{
				$this->page->initialize_page_tree();
			}
			
			// Grab default post template and values
			if (isset($this->page->page['post_templateid']))
			{
				$templateid = $this->page->page['post_templateid'];
			}
			else
			{
				$this->load->helper('template');
				$templateid = template_to_use('template', 'post', $this->page->page_tree);
			}
			$this->template = instantiate_library('template', $templateid);
			$this->template->initialize_values($this->post->post['postid']);
			
			
			// Grab attachments & images
			$filetypes = array();
			$filetypes[0] = array();
			$fileids = $this->files_model->get_attached("post",$this->post->post['postid']);
			foreach ($fileids->result_array() as $fileid)
			{
				$object = instantiate_library('file', $fileid['fileid']);
				if ($object->file['filetypeid'] == NULL)
				{
					array_push($filetypes[0], $object->file);
				}
				else
				{
					if (!isset($filetypes[$object->file['filetypeid']]))
					{
						$filetypes[$object->file['filetypeid']] = array();
					}
					array_push($filetypes[$object->file['filetypeid']], $object->file);
				}
			}
		
			// Grab attachment quotas
			$this->files = array();
			$this->filegroups = array();
			$quota_required = TRUE;
			$no_quota_in_use = TRUE;
			
			if (isset($this->template->template['templateid']))
			{
				$this->template->initialize_quotas();
				foreach ($this->template->quotas as $filegroup)
				{
					$filegroup['editable'] = $this->acl->access($filegroup['protection'], $this->page, $this->post->post['postid']);

					if ($filegroup['editable'])
					{
						$no_quota_in_use = FALSE;
					}
					if (!$filegroup['editable'] && $filegroup['other_roles_allowed'])
					{
						$filegroup['editable'] = TRUE;
						$quota_required = FALSE;
					}
					$filegroup['total'] = 0;
					foreach ($filegroup['filetypes'] as $filetypeid => $filetype)
					{
						$filegroup['filetypes'][$filetypeid]['files'] = array();
						if (isset($filetypes[$filetype['filetypeid']]))
						{
							foreach ($filetypes[$filetype['filetypeid']] as $file)
							{
								array_push($filegroup['filetypes'][$filetypeid]['files'], $file);
								$file['editable'] = $filegroup['editable'];
								$this->files[$file['fileid']] = $file;
								$filegroup['total']++;
							}
						}
					}
					$this->filegroups[$filegroup['quotaid']] = $filegroup;
				}
			}
						
			if (!$quota_required || $no_quota_in_use) // If the user isn't assigned to a quota, or is assigned to one but not required to use it, allow for regular post attachments
			{
				array_unshift($this->filegroups, array('name' => 'Attachments', 'editable' => TRUE, 'filetypes' => array(array('filetypeid' => NULL, 'name' => 'Attachments', 'files' => $filetypes[0]))));
				foreach ($filetypes[0] as $file)
				{
					$file['editable'] = TRUE;
					$this->files[$file['fileid']] = $file;
				}
			}
			
			$method = $this->uri->segment($this->base_segment+1);
			if ($method == "addcomment" || $method == "attachments" || $method == "comment" || $method == "delete" || $method == "deletecomment" || $method == "editevent" || $method == "editpost" || $method == "permissions" || $method == "reportcomment" || $method == "taguser" ||  $method == "theme")
			{
				$this->focus = $method;
				$this->$method();
			}
			else
			{
				// Record a view if the article is published, not held back, and we're doing a regular view of it
				if ($this->post->post['published'] == 1 && $this->post->post['featured'] != -1)
				{
					$this->views_model->add($this->session->userdata('userid'), $_SERVER['REMOTE_ADDR'], "post", $this->post->post['postid']);
				}
				$this->index();
			}
		}
	}
	
	function index()
	{
		// Add comment stuff only if the post is published and not a draft
		$add_comment = NULL;
		$comments = NULL;
		if ($this->post->post['published'] == 1 && $this->post->post['featured'] != -1)
		{
			// Add a comment
			if ($this->acl->allow('post', 'addcomment', FALSE, 'post', $this->post->post['postid']))
			{
				// If Facebook connect is enabled, load it up
				$session = NULL;
				if ($this->config->item('dmcb_signon_facebook') == "true" && !$this->session->userdata('signedon'))
				{
					$this->load->library('facebook_connect');
					$session = $this->facebook_connect->session;
				}
				$add_comment = $this->load->view('form_post_addcomment', array('post' => $this->post->post, 'session' => $session), TRUE);
			}
			else if ($this->acl->enabled('post', 'addcomment', 'member'))
			{
				$add_comment = $this->load->view('form_post_addcommentteaser', array('post' => $this->post->post), TRUE);
			}
			
			// Grab comments
			if ($this->acl->enabled('post', 'addcomment'))
			{
				$this->can_report_comment = $this->acl->allow('post', 'reportcomment', FALSE, 'post', $this->post->post['postid']);
				$this->can_delete_comment = $this->acl->allow('post', 'deletecomment', FALSE, 'post', $this->post->post['postid']);
				$this->can_holdback_comment = $this->acl->allow('site', 'manage_activity', FALSE, 'post', $this->post->post['postid']);
				$this->load->library('block_lib');
				$this->block_lib->block['function'] = "comments";
				$this->block_lib->block['feedback'] = "0";
				$this->block_lib->block['values'] = array(
					'detail' => 'full', 
					'post' => $this->post->post['urlname'], 
					'limit' => $this->config->item('dmcb_comments_per_post'),
					'sort' => 'asc'
				);
				$this->block_lib->block['pagination'] = TRUE;
				$comments = $this->block_lib->output();
			}
		}

		// Enable moderating tool bar
		$admin_toolbar = NULL;
		if ($this->post->post['published'] == '1' && $this->acl->allow('post', 'edit', FALSE, 'post', $this->post->post['postid']))
		{
			$admin_toolbar = $this->load->view('post_admin_toolbar', array('post' => $this->post->post, 'author' => $this->author->user), TRUE);
		}
		
		// Enable editing
		if ($this->acl->allow('post', 'edit', FALSE, 'post', $this->post->post['postid']))
		{
			$data['packages_editing'] = $this->load->view('packages_editing', NULL, TRUE);
				
			// Set editor type from template
			$simple_editor = FALSE;
			if (isset($this->template->template['simple']) && $this->template->template['simple'])
			{
				$simple_editor = TRUE;
			}
			$data['edit_post'] = $this->load->view('form_post_editpost', array('post' => $this->post->post, 'fields' => $this->template->fields, 'values' => $this->template->values, 'simple_editor' => $simple_editor), TRUE);
		}

		// Enable attachment editing			
		if ($this->acl->allow('post', 'attachments', FALSE, 'post', $this->post->post['postid']))
		{		
			$data['packages_upload'] = $this->load->view('packages_upload', 
				array(
					'upload_url' => 'post/'.$this->post->post['urlname'], 
					'upload_size' => $this->config->item('dmcb_site_upload_size'),
					'upload_types' => $this->config->item('dmcb_site_upload_types'), 
					'upload_description' => $this->config->item('dmcb_site_upload_description')
				), TRUE);
			
			// Grab stock images, if we have multiple, we will let the user choose if they want to set any as the post image
			$stockimages = array();
			$stockimageids = $this->files_model->get_stockimages();
			if (isset($this->filegroups[0])) // Stock images only allowed if user not required to use quota
			{
				foreach ($stockimageids->result_array() as $stockimage)
				{
					$object = instantiate_library('file', $stockimage['fileid']);
					array_push($stockimages, $object->file);			
				}
			}
			
			$data['attachments'] = $this->load->view('form_post_attachments', array('post' => $this->post->post, 'stockimages' => $stockimages, 'filegroups' => $this->filegroups, 'files' => $this->files), TRUE);
		}
		
		// Output any custom CSS and JS and enable theme editing if allowed
		if ($this->acl->enabled('post', 'theme'))
		{
			if ($this->post->post['css'] != NULL)
			{
				if (isset($this->css))
				{
					$this->css .= $this->post->post['css'];
				}
				else
				{
					$this->css = $this->post->post['css'];
				}
			}
			if ($this->post->post['javascript'] != NULL)
			{
				if (isset($this->javascript))
				{
					$this->javascript .= $this->post->post['javascript'];
				}
				else
				{
					$this->javascript = $this->post->post['javascript'];
				}
			}
			if ($this->acl->allow('post', 'theme', FALSE, 'post', $this->post->post['postid']))
			{
				$data['edit_css'] = $this->load->view('form_post_theme', array('post' => $this->post->post, 'cssfiles' => $this->cssfiles, 'jsfiles' => $this->jsfiles), TRUE);	
			}
		}
		
		// Enable events
		if ($this->acl->allow('post', 'event', FALSE, 'post', $this->post->post['postid']))
		{
			$data['edit_event'] = $this->load->view('form_post_editevent', array('post' => $this->post->post), TRUE);	
		}
		
		// Enable tagging useres
		if ($this->acl->allow('post', 'taguser', FALSE, 'post', $this->post->post['postid']))
		{
			$data['tag_user'] = $this->load->view('form_post_taguser', array('post' => $this->post->post, 'contributors' => $this->contributors), TRUE);	
		}
		
		// Get post neighbours
		$next_postid = $this->posts_model->get_neighbour_post("next", $this->post->post['date'], $this->post->post['pageid'], $this->post->post['userid']);
		$next_post = instantiate_library('post', $next_postid);
		$previous_postid = $this->posts_model->get_neighbour_post("previous", $this->post->post['date'], $this->post->post['pageid'], $this->post->post['userid']);
		$previous_post = instantiate_library('post', $previous_postid);
		
		// Get post images
		$this->post->post['image'] = NULL;
		$file = instantiate_library('file', $this->post->post['imageid']);
		if (isset($file->file['fileid']))
		{
			$this->post->post['image'] = $file->file;
		}
		else
		{
			// Stock image code
			$this->load->helper('picture');
			$stockimage = stock_image($this->post->post['postid']);
			if ($stockimage != NULL)
			{
				$this->post->post['image'] = $stockimage;
			}
		}
		$this->post->post['images'] = array();
		$fileids = $this->files_model->get_attached_images('post', $this->post->post['postid']);
		foreach ($fileids->result_array() as $fileid)
		{
			$file = instantiate_library('file', $fileid['fileid']);
			if (isset($file->file['fileid']))
			{
				array_push($this->post->post['images'], $file->file);
			}
		}

		// Grab permissions
		if ($this->acl->allow('post', 'permissions', FALSE, 'post', $this->post->post['postid']))
		{
			$data['permissions'] = $this->load->view('form_post_permissions', array('post' => $this->post->post, 'roles' => $this->roles, 'userlist' => $this->userlist), TRUE);
		}
		
		// If post was reached via search, highlight the searched word
		if ($this->session->flashdata('search_term'))
		{
			$this->post->post['content'] = preg_replace('/('.$this->session->flashdata('search_term').')(?![^<]*>)(?![\S]*%)/i', $this->load->view('content_highlight', array('content' => '$1'), TRUE), $this->post->post['content']);
		}
		
		// Render the post
		$post_section = $this->load->view('post_post', array('post' => $this->post->post, 'next_post' => $next_post->post, 'previous_post' => $previous_post->post, 'contributors' => $this->contributors, 'parentpage' => $this->page->page, 'author' => $this->author->user, 'admin_toolbar' => $admin_toolbar), TRUE);
		$comments_section = $this->load->view('post_comments', array('comments' => $comments, 'add_comment' => $add_comment), TRUE);
		$files_section = $this->load->view('post_files', array('files' => $this->listedfiles), TRUE); 
		$references_section = $this->load->view('post_references', array('references' => $this->postreferences), TRUE);
		$pingbacks_section = $this->load->view('post_pingbacks', array('pingbacks' => $this->pingbacks_model->get($this->post->post['postid'])), TRUE);
		$featuredimage_section = $this->load->view('post_image', array('postid' => $this->post->post['postid'], 'image' => $this->post->post['image']), TRUE);
		$listedimages_section = $this->load->view('post_images', array('postid' => $this->post->post['postid'], 'image' => $this->post->post['image'], 'images' => $this->post->post['images']), TRUE);
		
		// If there's a page post template, load it up and use it
		if (isset($this->template->template['templateid']))
		{
			// Load up blocks as necessary
			$contents = "";
			$template_contents = preg_split('/(%block_\S+%)/', $this->template->template['content'], -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($template_contents as $template_content)
			{
				if (preg_match('/^%block_\S+%$/', $template_content))
				{
					$object = instantiate_library('block', preg_replace('/^%block_(\S+)%$/', '$1', $template_content), 'title');
					
					// If we have a block on the page that is paginated AND we are using it, make sure to focus to it
					if (isset($object->block['pagination']) && $object->block['pagination'])
					{
						$this->load->helper('pagination_helper');
						if (get_pagination_uri() != NULL)
						{
							$this->focus = "pagination_block";
						}
					}
					
					$contents .= $object->output($this->page->page_tree);
				}
				else
				{
					$contents .= $template_content;
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
				$contents = str_replace('%'.$field['htmlcode'].'%', $value, $contents); // Insert value
				$contents = preg_replace('/%'.$field['htmlcode'].':([^%]*)%/', $label, $contents); // Insert any labels dependent on if value exists or not
			}
			$contents = str_replace('%contenthere%', $post_section, $contents);
			$contents = str_replace('%titlehere%', $this->post->post['title'], $contents);
			$contents = str_replace('%commentshere%', $comments_section, $contents);
				
			$matches = array();
			preg_match_all('/%fileshere(\[([a-z0-9,]+)\])?%/i', $contents, $matches);
			for ($i=0; $i<sizeof($matches[0]); $i++)
			{
				if (isset($matches[2][$i]) && isset($this->filegroups[$matches[2][$i]]))
				{
					$files_section = $this->load->view('post_files_group', array('filegroup' => $this->filegroups[$matches[2][$i]]), TRUE); 
				}
				else
				{
					$files_section = $this->load->view('post_files', array('files' => $this->listedfiles), TRUE); 
				}
				$contents = str_replace($matches[0][$i], $files_section, $contents);
			}
			
			$contents = str_replace('%referenceshere%', $references_section, $contents);
			$contents = str_replace('%pingbackshere%', $pingbacks_section, $contents);
			$contents = str_replace('%featuredimage%', $featuredimage_section, $contents);
			$contents = str_replace('%listedimages%', $listedimages_section, $contents);
			
			$data['postcontent'] = $this->load->view('post_wrapper_dynamic', array('content' => $contents), TRUE);
		}
		else // If no post template exists, load default hard-coded view
		{
			$data['postcontent'] = $this->load->view('post_wrapper_static', array('post' => $this->post->post, 'post_section' => $post_section, 'comments_section' => $comments_section, 'files_section' => $files_section, 'references_section' => $references_section, 'pingbacks_section' => $pingbacks_section), TRUE);	
		}
	
		$this->_initialize_page('post', $this->post->post['title'], $data, TRUE);
	}
	
	function addcomment()
	{
		if ($this->acl->allow('post', 'addcomment', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			if (!$this->session->userdata('signedon'))
			{
				$this->form_validation->set_rules('displayname', 'display name', 'xss_clean|strip_tags|trim|max_length[30]|callback_commentdisplayname_check');
				$this->form_validation->set_rules('password', 'password', 'xss_clean|trim|md5');
				$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|required|max_length[50]|valid_email|callback_commentlogin_check|callback_code_check|callback_banned_check');
			}
			
			// Anti-spam measure - if hidden 'information' field is filled, we know it's a bot
			$this->form_validation->set_rules('information', 'information', 'exact_length[0]');
			$this->form_validation->set_rules('comment', 'comment', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[15000]|callback_ip_check');
			
			if ($this->form_validation->run())
			{
				$reviewed = 1;
				$featured = -1;
				if ($this->session->userdata('signedon'))
				{
					$user = instantiate_library('user', $this->session->userdata('userid'));
					if (isset($user->user['userid']))
					{
						$featured = $user->user['statusid'];
					}
				}
				if ($featured == -1) 
				{
					$reviewed = 0;
				}
				
				$comment = str_replace("\n","<br/>",set_value('comment'));
				$comment = preg_replace("/<br\/>(<br\/>)+/","<br/><br/>",$comment);
				
				if ($this->session->userdata('signedon'))
				{
					$this->comments_model->add($this->post->post['postid'], $this->session->userdata('userid'), $comment, $featured, $reviewed);
				}
				else
				{
					$this->comments_model->add_anonymous($this->post->post['postid'], set_value('displayname'), set_value('email'), $_SERVER['REMOTE_ADDR'], $comment, $featured, $reviewed);
				}
				
				if ($featured == -1)
				{
					$message = 'Thanks for adding your two cents!  Your comment will be posted pending approval from the moderators. Click <a href="'.base_url().$this->post->post['urlname'].'">here</a> to return to \''.$this->post->post['title'].'\'';
				}
				else
				{
					$message = 'Thanks for adding your two cents! Click <a href="'.base_url().$this->post->post['urlname'].'">here</a> to return to \''.$this->post->post['title'].'\'';	
				}
					
				$subject = "Success!";
				$this->_message("Add comment", $message, $subject);
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function commentdisplayname_check($str)
	{
		$email_check = NULL;
		if (isset($_POST['email']))
		{
			$email_check = instantiate_library('user', $_POST['email'], 'email');
		}
		$displayname_check = instantiate_library('user', $str, 'displayname');
		if (isset($email_check->user['userid']))
		{
			return TRUE;
		}
		else if (strlen($str) < 3)
		{
			$this->form_validation->set_message('commentdisplayname_check', "The display name field must be at least 3 characters in length.");
			return FALSE;
		}
		if (isset($displayname_check->user['userid']))
		{
			$this->form_validation->set_message('commentdisplayname_check', "The display name $str is in use, please try a new display name.");
			return FALSE;
		}
		else if (!preg_match('/^[a-z0-9- ]+$/i', $str))
		{
			$this->form_validation->set_message('commentdisplayname_check', "The display name must be made of only letters, numbers, dashes, and spaces.");
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function commentlogin_check($str)
	{
		$object = instantiate_library('user', $str, 'email');
		if (isset($object->user['userid']))
		{
			if (set_value('password') == "")
			{
				$this->form_validation->set_message('commentlogin_check', "This email address is registered. Enter your account password to add your comment.");
				return FALSE;
			}
			else
			{
				if ($object->user['password'] == set_value('password'))
				{
					$update_user = instantiate_library('user', $str, 'email');
					$update_user->new_user['lastsignon'] = date('Y-m-d H:i:s');
					$update_user->save();
					
					$session = array(
						'userid' => $object->user['userid'],
						'displayname' => $object->user['displayname'],
						'urlname' => $object->user['urlname'],
						'signedon' => TRUE
					);
					$this->session->set_userdata($session);
					return TRUE;
				}
				else
				{
					$this->form_validation->set_message('commentlogin_check', "Your password is incorrect.");
					return FALSE;
				}
			}
		}
		else
		{
			if (set_value('displayname') == "")
			{
				$this->form_validation->set_message('commentlogin_check', "Please enter a display name.");
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
	}
	
	function code_check($str)
	{
		$object = instantiate_library('user', $str, 'email');
		if ($object->check_activated())
		{
			return TRUE;
		}
		else
		{
			$code = $object->user['userid']."/".$object->user['code'];
			$message = "You have created an account at ".$this->config->item('dmcb_friendly_server').".\n\nBefore you can log in you must activate your account by going to the following URL, ".base_url()."activate/".$code;
			$this->form_validation->set_message('code_check', "You have not activated your account.  An activation email is being resent.");
			return FALSE;
		}
	}
	
	function banned_check($str)
	{
		$this->form_validation->set_message('banned_check', "Your account has been banned.  Contact support@".$this->config->item('dmcb_server')." to be reinstated.");
		$object = instantiate_library('user', $str, 'email');
		return !$object->check_banned();
	}
	
	function ip_check($str)
	{
		$this->form_validation->set_message('ip_check', "Your ip address has been associated with spam and is temporarily blocked from anonymous commenting.  Please sign up for an account to comment.");
	
		if (set_value('password') == "")
		{
			$check = $this->comments_model->check_banned($_SERVER['REMOTE_ADDR']);
			return !$check;
		}
		else
		{
			return TRUE;
		}
	}

	function attachments()
	{
		if ($this->acl->allow('post', 'attachments', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->attachment = instantiate_library('file', $this->uri->segment($this->base_segment+3));
			if ($this->uri->segment($this->base_segment+2) == "setimage" && $this->attachment->file['filetypeid'] == NULL && ($this->files_model->check_stockimage($this->uri->segment($this->base_segment+3)) || ($this->attachment->file['attachedto'] == "post" && $this->attachment->file['attachedid'] == $this->post->post['postid'])))
			{
				$this->post->new_post['imageid'] = $this->uri->segment($this->base_segment+3);
				$this->post->save();
				// Post images can't also be listed as downloads
				$this->attachment->new_file['listed'] = "0";
				$this->attachment->save();
				redirect($this->post->post['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "removeimage" && $this->attachment->file['filetypeid'] == NULL && ($this->files_model->check_stockimage($this->uri->segment($this->base_segment+3)) || ($this->attachment->file['attachedto'] == "post" && $this->attachment->file['attachedid'] == $this->post->post['postid'])))
			{
				$this->post->new_post['imageid'] = NULL;
				$this->post->save();
				redirect($this->post->post['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) != "" && (!isset($this->files[$this->attachment->file['fileid']]) || !$this->files[$this->attachment->file['fileid']]['editable'] || $this->attachment->file['attachedto'] != "post" || $this->attachment->file['attachedid'] != $this->post->post['postid']))
			{
				$this->_access_denied();
			}
			else if ($this->uri->segment($this->base_segment+2) == "delete")
			{
				$this->attachment->delete();
				redirect($this->post->post['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "list" && !isset($this->attachment->file['filetypeid']))
			{
				$this->attachment->new_file['listed'] = "1";
				$this->attachment->save();
				// Listed images can't also be the image of the post
				if ($this->post->post['imageid'] == $this->attachment->file['fileid'])
				{
					$this->post->new_post['imageid'] = NULL;
					$this->post->save();
				}
				redirect($this->post->post['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "unlist" && !isset($this->attachment->file['filetypeid']))
			{
				$this->attachment->new_file['listed'] = "0";
				$this->attachment->save();
				redirect($this->post->post['urlname'].'/attachments');
			}
			else if ($this->uri->segment($this->base_segment+2) == "rename")
			{
				$this->form_validation->set_rules('filename', 'file name', 'xss_clean|strip_tags|trim|required|max_length[100]|callback_filename_check');
				
				if ($this->form_validation->run())
				{
					$this->attachment->new_file['filename'] = set_value('filename');
					$this->attachment->save();
					redirect($this->post->post['urlname'].'/attachments');
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
			return TRUE;
	}
	
	function comment()
	{
		// By default, no comment is specified, go to comments section generically
		$this->focus = "comment";
		// Find comment position if a comment is specified
		if ($this->acl->enabled('post', 'addcomment') && $this->uri->segment($this->base_segment+2) != NULL && ctype_digit($this->uri->segment($this->base_segment+2)))
		{
			$position = -1;
			$comments = $this->comments_model->get_post_comments_all($this->post->post['postid']);
			$comments_count = $this->comments_model->get_post_comments_count($this->post->post['postid']);
			for ($i=0; $i<$comments->num_rows(); $i++)
			{
				$comment = $comments->row_array($i);
				if ($comment['commentid'] == $this->uri->segment($this->base_segment+2))
				{
					$position = $i;
				}
			}

			$potential_pagination_index = $position - ($position%$this->config->item('dmcb_comments_per_post'));
			// Check to see pagination not set, and if required, redirect to pagination
			if ($potential_pagination_index >= $this->config->item('dmcb_comments_per_post') && strpos($this->uri->uri_string(), '/index/') === FALSE)
			{
				redirect(base_url().$this->post->post['urlname'].'/comment/'.$this->uri->segment($this->base_segment+2).'/index/'.$potential_pagination_index);
			}

			// Otherwise if the comment is on the current pagination, jump to comment
			$current_pagination_index = 0;
			$matches = array();
			preg_match('/\/index\/(\d+)$/', $this->uri->uri_string(), $matches);
			if (isset($matches[1]))
			{
				$current_pagination_index = $matches[1];
			}

			if ($position >= $current_pagination_index && $position < $current_pagination_index + $this->config->item('dmcb_comments_per_post'))
			{
				$this->focus = "comment".$this->uri->segment($this->base_segment+2);
			}
			else
			{
				redirect(base_url().$this->post->post['urlname'].'/comment/index/'.$current_pagination_index);
			}
		}
		$this->index();
	}
	
	function delete()
	{
		if ($this->acl->allow('post', 'edit', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->post->delete();
			if (isset($this->page->page['pageid'])) 
			{
				$this->message = 'You have successfully deleted '.$this->post->post['title'].'. Click <a href="'.base_url().$this->page->page['urlname'].'">here</a> to return to '.strtolower($this->page->page['title']).'.';
			}
			else
			{
				$this->message = 'You have successfully deleted '.$this->post->post['title'].'. Click <a href="'.base_url().'profile">here</a> to return to your profile.';
			}
			$this->_message('Delete', $this->message, 'Success');
		}
	}
	
	function deletecomment()
	{
		$comment = $this->comments_model->get($this->uri->segment($this->base_segment+2));
		if ($this->session->userdata('signedon') && $this->session->userdata('userid') == $comment['userid'])
		{
			$this->comments_model->delete($this->uri->segment($this->base_segment+2));
			redirect($this->post->post['urlname']);
		}
		else if ($this->session->userdata('signedon'))
		{
			$this->_access_denied();
		}
		else
		{
			redirect('signon'.uri_string());
		}
	}
	
	function editevent()
	{
		if ($this->acl->allow('post', 'event', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('eventdate', 'date', 'xss_clean|strip_tags|trim|required|numeric|exact_length[8]');
			$this->form_validation->set_rules('eventtime', 'time', 'xss_clean|strip_tags|trim|min_length[4]|max_length[5]|callback_time_check');
			$this->form_validation->set_rules('eventenddate', 'end date', 'xss_clean|strip_tags|trim|numeric|exact_length[8]');
			$this->form_validation->set_rules('eventendtime', 'end time', 'xss_clean|strip_tags|trim|min_length[4]|max_length[5]|callback_time_check');
			$this->form_validation->set_rules('eventlocation', 'location', 'xss_clean|strip_tags|trim|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('eventaddress', 'address', 'xss_clean|strip_tags|trim|min_length[2]|max_length[150]');
			
			if ($this->uri->segment($this->base_segment+2) == "delete")
			{
				$this->events_model->delete($this->post->post['postid']);
				redirect($this->post->post['urlname']);
			}
			else if ($this->form_validation->run())
			{
				if ($this->post->post['event'] == NULL)
				{
					$this->events_model->add($this->post->post['postid'], set_value('eventlocation'), set_value('eventaddress'), set_value('eventdate'), set_value('eventtime'), set_value('eventenddate'), set_value('eventendtime'));
					redirect($this->post->post['urlname']);
				}
				else
				{
					$this->events_model->delete($this->post->post['postid']);
					$this->events_model->add($this->post->post['postid'], set_value('eventlocation'), set_value('eventaddress'), set_value('eventdate'), set_value('eventtime'), set_value('eventenddate'), set_value('eventendtime'));
					redirect($this->post->post['urlname']);
				}
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function time_check($str)
	{
		if ($str == "")
		{
			return TRUE;
		}
		else if (!preg_match('/\d?\d:\d\d$/i', $str))
		{
			$this->form_validation->set_message('time_check', "The time must be of the format HH:MM.");
			return FALSE;
		}

		return TRUE;
	}

	function editpost()
	{
		if ($this->acl->allow('post', 'edit', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('posttitle', 'title', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]');
			$this->form_validation->set_rules('posturlname', 'url name', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[35]|callback_urlname_check');
			$this->form_validation->set_rules('postcontent', 'content', 'required|min_length[2]|max_length[65000]');
			$this->form_validation->set_rules('postsubscription', 'needs subscription', 'xss_clean|strip_tags');
			
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
				if (isset($_POST['categoryvalues']))
				{
					$this->categories_model->delete_by_post($this->post->post['postid']);
					$values = split(";",$this->security->xss_clean($_POST['categoryvalues']));
					$names = split(";",$this->security->xss_clean($_POST['categorynames']));
					for ($i=0; $i<sizeof($values); $i++)
					{
						if ($values[$i] != "")
						{
							if ($values[$i] != -1)
							{
								$this->categories_model->add($values[$i], $this->post->post['postid']);
							}
							else
							{
								$categoryid = $this->categories_model->add_custom($names[$i]);
								$this->categories_model->add($categoryid, $this->post->post['postid']);
							}
						}
					}
				}
				
				if (isset($_POST['previouspostvalues']))
				{
					$this->posts_model->remove_references($this->post->post['postid']);
					$values = split(";",$this->security->xss_clean($_POST['previouspostvalues']));
					for ($i=0; $i<sizeof($values); $i++)
					{
						if ($values[$i] != "")
						{
							$this->posts_model->set_reference($this->post->post['postid'], $values[$i]);
						}
					}
				}
				
				if ($this->acl->enabled('site', 'subscribe'))
				{
					$this->post->new_post['needsubscription'] = set_value('postsubscription');
				}
				if ($_POST['buttonchoice'] == "publish")
				{
					$this->post->new_post['published'] = 1;
				}
				$this->post->new_post['title'] = html_entity_decode(set_value('posttitle'), ENT_QUOTES);
				$this->post->new_post['urlname'] = set_value('posturlname');
				// If there's a parent page that specifies the post use it's name, use it, otherwise use the default date format
				if (isset($this->page->page['pagepostname']) && $this->page->page['pagepostname'])
				{
					$this->post->new_post['urlname'] = $this->page->page['urlname'].'/post/'.$this->post->new_post['urlname'];
				}
				else
				{
					$this->post->new_post['urlname'] = date("Ymd", strtotime($this->post->post['date'])).'/'.$this->post->new_post['urlname'];
				}
				//$this->load->helper('url');
				//$this->post->new_post['content'] = html_entity_decode(auto_link(set_value('postcontent'), 'url'), ENT_QUOTES);
				$this->post->new_post['content'] = html_entity_decode(set_value('postcontent'), ENT_QUOTES);
				$this->post->save();
				
				// Save additional template field values
				if (isset($this->template->fields))
				{
					$values = array();
					foreach ($this->template->fields as $field)
					{
						$values[$field['htmlcode']] = html_entity_decode(set_value($field['htmlcode']), ENT_QUOTES);
					}
					$this->template->set_values($values, $this->post->post['postid']);
				}
				
				if ($_POST['buttonchoice'] == "save")
				{
					redirect($this->post->new_post['urlname'].'/editpost');
				}
				else
				{
					if (isset($this->page->page['pageid'])) 
					{
						redirect($this->page->page['urlname']);
					}
					else 
					{
						redirect('profile/'.$this->author->user['urlname']);
					}
				}
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function urlname_check($str)
	{
		if (!preg_match('/^[a-z0-9-_]+$/i', $str))
		{
			$this->form_validation->set_message('urlname_check', "The url name must be made of only letters, numbers, dashes, and underscores.");
			return FALSE;
		}
		else if (preg_match('/^\_+|^\-+|\-+$|\_+$/i', $str))
		{
			$this->form_validation->set_message('urlname_check', "The url name cannot start or end with dashes or underscores.");
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
			$suggestion = $this->post->suggest($str);
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
				$this->form_validation->set_message('urlname_check', "The url name is in use.  We suggest $suggestion.");
				return FALSE;
			}
		}
	}
	
	function permissions()
	{
		if ($this->acl->allow('post', 'permissions', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('displayname', 'display name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[30]|callback_displayname_permissions_check|callback_acl_exists_check');
			$this->form_validation->set_rules('email', 'email address', 'xss_clean|strip_tags|trim|max_length[50]|valid_email|callback_email_check');
			$this->form_validation->set_rules('role', 'role', 'xss_clean');
		
			$this->load->model('acls_model');
			if ($this->uri->segment($this->base_segment+2) == "set_role")
			{
				$this->acls_model->delete($this->uri->segment($this->base_segment+3), 'post', $this->post->post['postid']);
				$this->acls_model->add($this->uri->segment($this->base_segment+3), $this->uri->segment($this->base_segment+4), 'post', $this->post->post['postid']);
								
				// Do notification
				$this->session->set_flashdata('change', 'role change');
				$this->session->set_flashdata('action', 'set');
				$this->session->set_flashdata('actionon', 'user');
				$this->session->set_flashdata('actiononid', $this->uri->segment($this->base_segment+3));				
				$this->session->set_flashdata('parentid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('scope', 'post');
				$this->session->set_flashdata('scopeid', $this->post->post['postid']);
				$this->session->set_flashdata('content', $this->roles_table[$this->uri->segment($this->base_segment+4)]);
				$this->session->set_flashdata('return', $this->post->post['urlname'].'/permissions');
				redirect('notify');
			}
			else if ($this->uri->segment($this->base_segment+2) == "delete")
			{
				$this->acls_model->delete($this->uri->segment($this->base_segment+3), 'post', $this->post->post['postid']);
				
				// Do notification
				$this->session->set_flashdata('change', 'role removal');
				$this->session->set_flashdata('action', 'removed');
				$this->session->set_flashdata('actionon', 'user');
				$this->session->set_flashdata('actiononid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('parentid', $this->uri->segment($this->base_segment+3));
				$this->session->set_flashdata('scope', 'post');
				$this->session->set_flashdata('scopeid', $this->post->post['postid']);
				$this->session->set_flashdata('return', $this->post->post['urlname'].'/permissions');
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
					
					$this->acls_model->add($result['userid'], set_value('role'), 'post', $this->post->post['postid']);
					
					redirect($this->post->post['urlname'].'/permissions');
				}
				else
				{
					$object = instantiate_library('user', set_value('displayname'), 'displayname');
					
					$this->acls_model->add($object->user['userid'], set_value('role'), 'post', $this->post->post['postid']);
					
					// Do notification
					$this->session->set_flashdata('change', 'added role');
					$this->session->set_flashdata('action', 'set');
					$this->session->set_flashdata('actionon', 'user');
					$this->session->set_flashdata('actiononid', $object->user['userid']);
					$this->session->set_flashdata('parentid', $object->user['userid']);
					$this->session->set_flashdata('scope', 'post');
					$this->session->set_flashdata('scopeid', $this->post->post['postid']);						
					$this->session->set_flashdata('content', $this->roles_table[set_value('role')]);
					$this->session->set_flashdata('return', $this->post->post['urlname'].'/permissions');
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

	function reportcomment()
	{
		// Anti-spam measure - if hidden 'information' field is filled, we know it's a bot
		if (isset($_POST['information']) && $_POST['information'] != NULL)
		{
			redirect(base_url().$this->post->post['urlname']);
		}
		else
		{
			$this->comments_model->set_reported($this->uri->segment($this->base_segment+2));
			$this->_message(
				'Report', 
				'Thank you for reporting the comment. Click <a href="'.base_url().$this->post->post['urlname'].'">here</a> to return to \''.$this->post->post['title'].'\'.',
				'Thanks'
			);
		}
	}
	
	function taguser()
	{
		if ($this->acl->allow('post', 'taguser', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			for ($i=0; $i<=sizeof($this->contributors); $i++)
			{
				$this->form_validation->set_rules('contributor'.($i+1), 'display name of the tagged user', 'xss_clean|strip_tags|trim|min_length[3]|max_length[30]|callback_displayname_exists_check');
			}
		
			if ($this->form_validation->run())
			{
				$contributors = array();
				for ($i=0; $i<=sizeof($this->contributors); $i++)
				{
					$field = 'contributor'.($i+1);
					$object = instantiate_library('user', set_value($field), 'displayname');
					if (isset($object->user['userid']))
					{
						array_push($contributors, $object->user['userid']);
					}
				}
				$this->post->new_post['contributors'] = $contributors;
				$this->post->save();
				redirect($this->post->post['urlname'].'/taguser');
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function displayname_exists_check($str)
	{	
		$checkuser = instantiate_library('user', $str, 'displayname');
		if ($str != "" && !isset($checkuser->user['userid']))
		{
			$this->form_validation->set_message('displayname_exists_check', "The display name $str doesn't exist, please try a new display name.");	
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function theme()
	{
		if ($this->acl->allow('post', 'theme', TRUE, 'post', $this->post->post['postid']) || $this->_access_denied())
		{
			$this->form_validation->set_rules('css', 'css', 'xss_clean|strip_tags|trim|max_length[65000]');
			for ($i=0; $i<=sizeof($this->cssfiles); $i++)
			{
				$this->form_validation->set_rules('css'.($i+1), 'css file location', 'xss_clean|strip_tags|trim|min_length[3]|max_length[250]|callback_theme_file_check');
			}
			$this->form_validation->set_rules('javascript', 'javascript', 'xss_clean|strip_tags|trim|max_length[65000]');
			for ($i=0; $i<=sizeof($this->jsfiles); $i++)
			{
				$this->form_validation->set_rules('js'.($i+1), 'js file location', 'xss_clean|strip_tags|trim|min_length[3]|max_length[250]|callback_theme_file_check');
			}
			
			if ($this->form_validation->run())
			{
				$this->post->remove_theme_files();
				$added = array();
				for ($i=0; $i <= sizeof($this->cssfiles); $i++)
				{
					$field = 'css'.($i+1);
					if (set_value($field) != "" && !isset($added[strtolower(set_value($field))]))
					{
						$this->post->add_theme_file(set_value($field), 'css');
						$added[strtolower(set_value($field))] = TRUE;
					}
				}
				$added = array();
				for ($i=0; $i <= sizeof($this->jsfiles); $i++)
				{
					$field = 'js'.($i+1);
					if (set_value($field) != "" && !isset($added[strtolower(set_value($field))]))
					{
						$this->post->add_theme_file(set_value($field), 'js');
						$added[strtolower(set_value($field))] = TRUE;
					}
				}
				
				$this->post->new_post['css'] = html_entity_decode(set_value('css'), ENT_QUOTES);
				$this->post->new_post['javascript'] = html_entity_decode(set_value('javascript'), ENT_QUOTES);
				$this->post->save();
				
				redirect($this->post->new_post['urlname'].'/theme');
			}	
			else
			{
				$this->index();
			}
		}
	}
	
	function theme_file_check($str)
	{	
		if ($str == "")
		{
			return TRUE;
		}
		else if (substr($str, 0, 1) != '/')
		{
			$this->form_validation->set_message('theme_file_check', "The file must be a relative path starting with '/'. You cannot link to files outside the website.");	
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}
?>