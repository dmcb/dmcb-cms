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
class Manage_activity extends MY_Controller {

	function Manage_activity()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model(array('users_model', 'posts_model', 'comments_model', 'categories_model', 'notifications_model', 'pingbacks_model'));
	}
	
	function _remap()
	{
		if ($this->acl->allow('site', 'manage_activity', TRUE)|| $this->_access_denied())
		{
			$data['action'] = $this->uri->uri_string();

			$data['users'] = array();
			$data['postsreview'] = array();
			$data['postspending'] = array();
			$data['commentsnew'] = array();
			$data['commentsreported'] = array();
			$data['commentspending'] = array();
			$data['commentsanonymous'] = array();
			$data['categories'] = array();
			$data['pingbacks'] = array();
			
			// Get list of users registered to the site, but that haven't confirmed their email
			$users = $this->users_model->get_list_purgatory();
			foreach ($users->result_array() as $user)
			{
				$object = instantiate_library('user', $user['userid']);
				array_push($data['users'], $object->user);				
			}
			
			// Get list of posts to review
			if ($this->acl->enabled('profile', 'addpost'))
			{
				$postsreview = $this->posts_model->get_list_review();
				foreach ($postsreview->result_array() as $postreview)
				{
					$object = instantiate_library('post', $postreview['postid']);
					array_push($data['postsreview'], $object->post);
				}
				
				// Get list of posts that have been heldback
				$postspending = $this->posts_model->get_list_heldback();
				foreach ($postspending->result_array() as $postpending)
				{
					$object = instantiate_library('post', $postpending['postid']);
					array_push($data['postspending'], $object->post);
				}
			}
			
			// Get list of new comments from members to review
			$commentsnew = $this->comments_model->get_list_new();
			foreach ($commentsnew->result_array() as $commentnew)
			{
				$user = instantiate_library('user', $commentnew['userid']);
				$post = instantiate_library('post', $commentnew['postid']);
				$commentnew['user'] = $user->user;
				$commentnew['post'] = $post->post;
				array_push($data['commentsnew'], $commentnew);
			}
			
			// Get list of reported comments
			$commentsreported = $this->comments_model->get_list_reported();
			foreach ($commentsreported->result_array() as $commentreported)
			{
				$user = instantiate_library('user', $commentreported['userid']);
				$post = instantiate_library('post', $commentreported['postid']);
				if (isset($user->user['userid']))
				{
					$commentreported['user'] = $user->user;
				}
				$commentreported['post'] = $post->post;			
				array_push($data['commentsreported'], $commentreported);
			}
			
			// Get list of heldback comments
			$commentspending = $this->comments_model->get_list_heldback();
			foreach ($commentspending->result_array() as $commentpending)
			{
				$user = instantiate_library('user', $commentpending['userid']);
				$post = instantiate_library('post', $commentpending['postid']);
				$commentpending['user'] = $user->user;
				$commentpending['post'] = $post->post;
				array_push($data['commentspending'], $commentpending);
			}
			
			// Get list of anonymously posted comments to review
			$commentsanonymous = $this->comments_model->get_list_anonymous();
			foreach ($commentsanonymous->result_array() as $commentanonymous)
			{
				$post = instantiate_library('post', $commentanonymous['postid']);
				$commentanonymous['post'] = $post->post;
				array_push($data['commentsanonymous'], $commentanonymous);
			}
			
			// Get list of suggested new post categories
			$categories = $this->categories_model->get_list_heldback();
			foreach ($categories->result_array() as $category)
			{
				array_push($data['categories'], $category);
			}
			$data['publishedcategories'] = $this->categories_model->get_list();
			
			// Get list of new pingbacks
			$pingbacks = $this->pingbacks_model->get_new();
			foreach ($pingbacks->result_array() as $pingback)
			{
				$post = instantiate_library('post', $pingback['postid']);
				$pingback['post'] = $post->post;
				array_push($data['pingbacks'], $pingback);
			}			
			
			// Handle activity managing
			// If there was a post, check to see if 'send' was specified
			$alert_user = FALSE;
			if (isset($_POST['buttonchoice']) && $_POST['buttonchoice'] == "send")
			{
				$alert_user = TRUE;
			}
			
			if ($this->uri->segment(2) == "user")
			{
				$user = instantiate_library('user', $this->uri->segment(4));
			
				if ($this->uri->segment(3) == "email")
				{
					$message = "You have created an account at ".$this->config->item('dmcb_friendly_server').".\n\nBefore you can log in you must activate your account by going to the following URL, ".base_url()."activate/".$user->user['userid'].'/'.$user->user['code'];
					if ($this->notifications_model->send($user->user['email'], $this->config->item('dmcb_friendly_server').' account', $message))
					{
						$data['subject'] = "Success!";
						$data['message'] = 'You have sent a reminder email containing '.$user->user['displayname'].'\'s account information. Click <a href="'.base_url().'manage_activity">here</a> to return to moderating.';
					}	
					else 
					{	
						$data['subject'] = "Error";
						$data['message'] = "The account was created but an email could not be sent for ".$user->user['displayname'].", please contact support at <a href=\"mailto:support@".$this->config->item('dmcb_server')."\">support@".$this->config->item('dmcb_server')."</a>.";
					}
					$this->_message("Resend activation email", $data['message'], $data['subject']);
				}
				else if ($this->uri->segment(3) == "delete")
				{
					$user->delete();
					redirect('manage_activity');
				}
			}
			else if ($this->uri->segment(2) == "post" || $this->uri->segment(2) == "postreturn")
			{
				$post = instantiate_library('post', $this->uri->segment(4));
				$user = instantiate_library('user', $post->post['userid']);
				
				if ($this->uri->segment(3) == "approve")
				{
					$post->new_post['featured'] = 0;
					$post->new_post['reviewed'] = 1;
					$post->save();
					$this->load->helper('pingback');
					ping();
					pingback($post->post['postid']);

					// Do notification
					if (isset($post->post['userid']))
					{
						$this->session->set_flashdata('change', 'approval');
						$this->session->set_flashdata('action', 'approved');
						$this->session->set_flashdata('actionon', 'post');
						$this->session->set_flashdata('actiononid', $post->post['postid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $post->post['title']);
						if ($this->uri->segment(2) == "postreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "postreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "delete")
				{
					$post->delete();
					
					// Do notification
					if (isset($post->post['userid']))
					{
						$this->session->set_flashdata('change', 'deletion');
						$this->session->set_flashdata('action', 'deleted');
						$this->session->set_flashdata('actionon', 'post');
						$this->session->set_flashdata('actiononid', $post->post['postid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $post->post['title']);
						if ($this->uri->segment(2) == "postreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "postreturn")
						{
							$page = instantiate_library('page', $post->post['pageid']);
							redirect($page->page['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "feature")
				{
					$post->new_post['featured'] = 1;
					$post->new_post['reviewed'] = 1;
					$post->save();
					
					// Do notification
					if (isset($post->post['userid']))
					{
						$this->session->set_flashdata('change', 'featuring');
						$this->session->set_flashdata('action', 'featured');
						$this->session->set_flashdata('actionon', 'post');
						$this->session->set_flashdata('actiononid', $post->post['postid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $post->post['title']);
						if ($this->uri->segment(2) == "postreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "postreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "holdback")
				{
					$post->new_post['featured'] = -1;
					$post->new_post['reviewed'] = 1;
					$post->save();
					
					// Do notification
					if (isset($post->post['userid']))
					{
						$this->session->set_flashdata('change', 'hold back');
						$this->session->set_flashdata('action', 'held back');
						$this->session->set_flashdata('actionon', 'post');
						$this->session->set_flashdata('actiononid', $post->post['postid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $post->post['title']);
						if ($this->uri->segment(2) == "postreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "postreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "review")
				{
					$post->new_post['reviewed'] = 1;
					$post->save();
					redirect('manage_activity');
				}
				else if ($this->uri->segment(3) == "unfeature")
				{
					$post->new_post['featured'] = 0;
					$post->save();
					
					// Do notification
					if (isset($post->post['userid']))
					{
						$this->session->set_flashdata('change', 'unfeaturing');
						$this->session->set_flashdata('action', 'unfeatured');
						$this->session->set_flashdata('actionon', 'post');
						$this->session->set_flashdata('actiononid', $post->post['postid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $post->post['title']);
						if ($this->uri->segment(2) == "postreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "postreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
			}
			else if ($this->uri->segment(2) == "comment" || $this->uri->segment(2) == "commentreturn")
			{
				$comment = $this->comments_model->get($this->uri->segment(4));
				$post = instantiate_library('post', $comment['postid']);
				$user = instantiate_library('user', $comment['userid']);
				
				if ($this->uri->segment(3) == "approve")
				{
					$this->comments_model->set_featured($this->uri->segment(4), 0);
					
					// Do notification
					if (isset($comment['userid']))
					{
						$this->session->set_flashdata('change', 'approval');
						$this->session->set_flashdata('action', 'approved');
						$this->session->set_flashdata('actionon', 'comment');
						$this->session->set_flashdata('actiononid', $comment['commentid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $comment['content']);
						if ($this->uri->segment(2) == "commentreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "commentreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "delete")
				{
					$this->comments_model->delete($this->uri->segment(4));
		
					// Do notification
					if (isset($comment['userid']))
					{
						$this->session->set_flashdata('change', 'deletion');
						$this->session->set_flashdata('action', 'deleted');
						$this->session->set_flashdata('actionon', 'comment');
						$this->session->set_flashdata('actiononid', $comment['commentid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $comment['content']);
						if ($this->uri->segment(2) == "commentreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "commentreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "holdback")
				{
					$this->comments_model->set_featured($this->uri->segment(4), -1);
					
					// Do notification
					if (isset($comment['userid']))
					{
						$this->session->set_flashdata('change', 'hold back');
						$this->session->set_flashdata('action', 'held back');
						$this->session->set_flashdata('actionon', 'comment');
						$this->session->set_flashdata('actiononid', $comment['commentid']);
						$this->session->set_flashdata('parentid', $user->user['userid']);
						$this->session->set_flashdata('content', $comment['content']);
						if ($this->uri->segment(2) == "commentreturn")
						{
							$this->session->set_flashdata('return', $post->post['urlname']);
						}
						else
						{
							$this->session->set_flashdata('return', 'manage_activity');
						}
						redirect('notify');
					}
					else
					{
						if ($this->uri->segment(2) == "commentreturn")
						{
							redirect($post->post['urlname']);
						}
						else
						{
							redirect('manage_activity');
						}
					}
				}
				else if ($this->uri->segment(3) == "review")
				{
					$this->comments_model->set_reviewed($this->uri->segment(4));
					redirect('manage_activity');
				}
				else if ($this->uri->segment(3) == "ban")
				{
					$this->comments_model->set_ban($comment['ip']);
					$this->comments_model->delete_by_ip($comment['ip']);
					redirect('manage_activity');
				}
			}
			else if ($this->uri->segment(2) == "category")
			{
				if ($this->uri->segment(3) == "delete")
				{
					$this->categories_model->delete($this->uri->segment(4));
					redirect('manage_activity');
				}
				else if ($this->uri->segment(3) == "merge")
				{
					$this->categories_model->merge($this->uri->segment(4), $this->uri->segment(5));
					redirect('manage_activity');
				}
				else
				{
					$this->form_validation->set_rules('categoryname', 'category name', 'xss_clean|strip_tags|trim|required|min_length[3]|max_length[50]');

					if ($this->form_validation->run())
					{
						$categoryname = set_value('categoryname');
						$categoryid = $this->categories_model->get_by_name($categoryname);
						if ($categoryid != NULL)
						{
							if ($categoryid == $this->uri->segment(3))
							{
								$this->categories_model->approve($this->uri->segment(3), $categoryname);
							}
							else
							{
								$this->categories_model->merge($this->uri->segment(3), $categoryid);
							}
						}
						else
						{
							$this->categories_model->approve($this->uri->segment(3), $categoryname);
						}
						redirect('manage_activity');
					}
					else
					{
						$this->_initialize_page('manage_activity', 'Manage activity', $data);
					}
				}
			}
			else if ($this->uri->segment(2) == "pingback")
			{
				if ($this->uri->segment(3) == "approve")
				{
					$this->pingbacks_model->update($this->uri->segment(4), '1');
					redirect('manage_activity');
				}
				else if ($this->uri->segment(3) == "deny")
				{
					$this->pingbacks_model->update($this->uri->segment(4), '-1');
					redirect('manage_activity');
				}
			}
			else
			{			
				$this->_initialize_page('manage_activity', 'Manage activity', $data);
			}
		}
	}
}