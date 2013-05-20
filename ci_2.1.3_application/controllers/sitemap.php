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
class Sitemap extends MY_Controller {

	function Sitemap()
	{
		parent::__construct();
	}

	function _remap()
	{
		$this->load->model(array('pages_model', 'posts_model', 'users_model'));
		$results = array();
		
		// Get all pages
		// Less deep pages get higher priority
		$pages = $this->pages_model->get_published_all();
		foreach ($pages->result_array() as $page)
		{
			$object = instantiate_library('page', $page['pageid']);
			$result = array();
			$result['lastmod'] = $object->page['datemodified'];
			$result['changefreq'] = $this->_frequency($object->page['datemodified']);

			if ($object->page['needsubscription'])
			{
				$result['priority'] = "0.4";
			}
			else if ($object->page['pageof'] == NULL)
			{
				$result['priority'] = "0.8";
			}
			else 
			{
				$result['priority'] = "0.6";
			}
			
			$ignore = FALSE;
			if (!$object->page['protected']) // If it's protected we don't want google attempting it
			{
				if ($object->page['link'] != null)
				{
					if (substr($object->page['link'],0,1) == "/") // If the page is a link to a controller page, record it
					{
						$link = substr($object->page['link'],1,strlen($object->page['link']));
						if ($link == $this->config->item('dmcb_default_location')) // If it's the default controller, lose the page url name and bump priority and frequency
						{
							$result['loc'] = base_url();
							$result['priority'] = "1.0";
							$result['changefreq'] = "daily";
						}
						else
						{
							$result['loc'] = base_url().$link;
						}
					}
					else
					{
						$ignore = TRUE; // Otherwise it's a menu link to an off-server site, let's ignore it
					}
				}
				else
				{
					if ($object->page['content'] != NULL)
					{
						$result['loc'] = base_url().$object->page['urlname'];
					}
					else
					{
						$ignore = TRUE; // If it's an empty page, ignore it
					}
				}
				
				if (!$ignore)
				{
					array_push($results, $result);
				}
			}
		}
		
		// Get all posts that aren't associated with a page
		// Featured posts get higher priority
		$posts = $this->posts_model->get_published_all();
		foreach ($posts->result_array() as $post)
		{
			$object = instantiate_library('post', $post['postid']);
			$result = array();
			$result['loc'] = base_url().$object->post['urlname'];
			$result['lastmod'] = $object->post['datemodified'];
			$result['changefreq'] = $this->_frequency($object->post['datemodified']);
			
			if ($object->post['needsubscription'])
			{
				$result['priority'] = "0.3";
			}
			if ($object->post['featured'] == 1)
			{
				$result['priority'] = "0.7";
			}
			else 
			{
				$result['priority'] = "0.5";
			}
			
			$ignore = FALSE;
			if ($object->post['pageid'] != NULL)
			{
				$parent = instantiate_library('page', $object->post['pageid']);
				if (!isset($parent->page['pageid']) || $parent->page['protected'])
				{
					$ignore = TRUE; // If it's protected we don't want google attempting it
				}
				else if ($parent->page['needsubscription'])
				{
					$result['priority'] = "0.3";
				}
			}
			
			if (!$ignore)
			{
				array_push($results, $result);
			}
		}
		
		// Get all users
		// Featured users get higher priority
		$users = $this->users_model->get_published_all();
		$featured = $this->users_model->get_status_highest();
		foreach ($users->result_array() as $user)
		{
			$object = instantiate_library('user', $user['userid']);
			if ($object->user['enabledprofile']) // Only add profiles to site map if profile viewing is enabled
			{
				$result = array();
				$result['loc'] = base_url().'profile/'.$object->user['urlname'];
				$result['lastmod'] = $object->user['datemodified'];
				$result['changefreq'] = $this->_frequency($object->user['datemodified']);
				
				if ($object->user['statusid'] == $featured)
				{
					$result['priority'] = "0.6";
				}
				else 
				{
					$result['priority'] = "0.4";
				}
				array_push($results, $result);
			}
		}
		
		$data['sitemaps'] = "";
		foreach ($results as $result)
		{
			$data['sitemaps'] .= $this->load->view('sitemap_url', $result, TRUE);
		}
		$this->load->view('sitemap', $data);
	}
	
	function _frequency($modified) // Generate a rather brute force estimate on frequency by comparing how long ago it was last updated to now
	{
		list($date, $time) = explode(' ', $modified);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		$elapsedtime = (time() - $timestamp)/86400;
		if ($elapsedtime < 7)
		{
			return "daily";
		}
		else if ($elapsedtime < 28)
		{
			return "weekly";
		}
		else if ($elapsedtime < 365)
		{
			return "monthly";
		}
		else 
		{
			return "yearly";
		}
	}
}