<?php

class Search extends MY_Controller {

	function Search()
	{
		parent::__construct();
		
		$this->load->helper('pagination');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model(array('users_model', 'pages_model', 'posts_model'));
	}
	
	function _remap()
	{
		if ($this->acl->allow('site', 'search', TRUE) || $this->_access_denied())
		{
			$data['search_text'] = $this->session->flashdata('text');
			$data['search_type'] = $this->session->flashdata('type');
			$data['search_page'] = $this->session->flashdata('page');
			
			// Preserve search parameters
			$this->session->keep_flashdata('text');
			$this->session->keep_flashdata('type');
			$this->session->keep_flashdata('page');
		
			// Get search fields and rules
			$this->form_validation->set_rules('searchtext', 'search term', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[100]');
			$this->form_validation->set_rules('searchtype', 'search type', 'xss_clean|strip_tags|trim');
			$this->form_validation->set_rules('searchpage', 'search page', 'xss_clean|strip_tags|trim');
			
			if ($this->form_validation->run())
			{
				// Set new search parameter
				$data['search_text'] = set_value('searchtext');
				$data['search_type'] = set_value('searchtype');
				$data['search_page'] = set_value('searchpage');
				$this->session->set_flashdata('text', $data['search_text']);
				$this->session->set_flashdata('type', $data['search_type']);
				$this->session->set_flashdata('page', $data['search_page']);
			}
			else
			{
				// Ensure other search values carry over in the event of a form submitted properly
				if (set_value('searchtype') != NULL)
				{
					$data['search_type'] = set_value('searchtype');
					$this->session->set_flashdata('type', $data['search_type']);
				}
				if (set_value('searchpage') != NULL)
				{
					$data['search_page'] = set_value('searchpage');
					$this->session->set_flashdata('page', $data['search_page']);
				}
			}

			$data['usermatches'] = array();
			$data['pagematches'] = array();
			$data['postmatches'] = array();
			$data['filematches'] = array();			

			// If a specific page is specified, return only post results from that page and it's children
			$pages = NULL;
			if ($data['search_page'] != NULL)
			{
				$data['search_type'] = "posts";
				
				// Grab all pages
				$search_pages = explode(";",$data['search_page']);
				for ($i=0; $i<sizeof($search_pages); $i++)
				{
					$object = instantiate_library('page', $search_pages[$i]);
					if (isset($object->page['pageid']) && $i == 0)
					{
						$data['search_page_details'] = $object->page;
					}
					
					$children = array();
					$this->pages_model->get_children_tree_pageids($search_pages[$i], $children);
					foreach ($children as $child)
					{
						$pages .= ";".$child;
					}
				}
			}
			
			// If a specific search type is specified, return more results
			$data['cap'] = 100;
			
			if ($data['search_text'] != NULL)
			{
				if ($data['search_type'] == "users")
				{
					$offset = generate_pagination($this->users_model->search_count($data['search_text']), $data['cap']);
					$usermatches = $this->users_model->search($data['search_text'], $data['cap'], $offset);
				}
				else if ($data['search_type'] == "pages")
				{
					$offset = generate_pagination($this->pages_model->search_count($data['search_text']), $data['cap']);
					$pagematches = $this->pages_model->search($data['search_text'], $data['cap'], $offset);
				}
				else if ($data['search_type'] == "posts")
				{
					$offset = generate_pagination($this->posts_model->search_count($data['search_text'], $pages), $data['cap']);
					$postmatches = $this->posts_model->search($data['search_text'], $data['cap'], $offset, $pages);
				}
				else if ($data['search_type'] == "files")
				{
					$cap = $data['cap'];
					$this->_search_files($data['filematches'], $cap, 'files', $data['search_text']);
					$this->_search_files($data['filematches'], $cap, 'files_managed', $data['search_text']);
				}
				else
				{
					$data['cap'] = 6;
					$usermatches = $this->users_model->search($data['search_text'], $data['cap'], 0);
					$pagematches = $this->pages_model->search($data['search_text'], $data['cap'], 0);
					$postmatches = $this->posts_model->search($data['search_text'], $data['cap'], 0);
					$cap = $data['cap'];
					$this->_search_files($data['filematches'], $cap, 'files', $data['search_text']);
					$this->_search_files($data['filematches'], $cap, 'files_managed', $data['search_text']);
				}
			
				// Grab user matches
				if (isset($usermatches) && $this->acl->enabled('profile', 'view'))
				{
					foreach($usermatches->result_array() as $usermatch)
					{
						$object = instantiate_library('user', $usermatch['userid']);
						array_push($data['usermatches'], $object->user);
					}
				}
				// Grab page matches
				if (isset($pagematches))
				{
					foreach($pagematches->result_array() as $pagematch)
					{
						$object = instantiate_library('page', $pagematch['pageid']);
						array_push($data['pagematches'], $object->page);
					}
				}
				// Grab post matches
				if (isset($postmatches))
				{
					foreach($postmatches->result_array() as $postmatch)
					{
						$object = instantiate_library('post', $postmatch['postid']);
						array_push($data['postmatches'], $object->post);
					}
				}
			
				if (sizeof($data['usermatches']) == 0 && sizeof($data['pagematches']) == 0 && sizeof($data['postmatches']) == 0 && sizeof($data['filematches']) == 0)
				{
					$data['search_message'] = 'No results found.';
				}
			}
			
			$this->_initialize_page('search', 'Search', $data);
		}
	}
	
	function _search_files(&$filematches, &$cap, $directory, $searchtext)
	{
		if ($handle = opendir($directory))
		{
			while (false !== ($file = readdir($handle)) && $cap > 0)
			{
				if ($file != "." && $file != ".." && $file != ".htaccess")
				{
					$file = $directory.'/'.$file;
					if (is_dir($file))
					{
						$this->_search_files($filematches, $cap, $file, $searchtext);
					}
					else if (substr($file, strrpos($file, ".")+1, strlen($file)) == "searchmetadata")
					{
						$pathpieces = explode("/", $file);
						if (sizeof($pathpieces) == 4 && shell_exec('grep -i \''.$searchtext.'\' '.$file.' | wc -l') > 0)
						{
							$filepieces = explode(".", $pathpieces[3]);
							if (sizeof($filepieces == 3))
							{
								$this->load->model('files_model');
								
								// Get internal attachedid from named attachedid
								$this->load->helper('attachment_helper');
								$object = instantiate_library('file', array($filepieces[0], $filepieces[1], $pathpieces[1], attached_id($pathpieces[1], $pathpieces[2])), 'details');

								if (isset($object->file['fileid']))
								{
									array_push($filematches, $object->file);
									$cap--;
								}
							}
						}
					}
				}
			}
			closedir($handle);
		}
	}
}
?>