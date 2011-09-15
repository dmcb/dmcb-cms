<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb pingback helper
 *
 * Pings blog publishing engines, and parses a post for URLs and initiates a pingback
 *
 * @package		CodeIgniter
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney
 * @link		http://dmcbdesign.com
 */
 
// ------------------------------------------------------------------------

/**
 * Ping
 *
 * Ping blog publishing engines that the website has updated
 *
 * @access	public
 * @param	int     post id
 * @return	void
 */
if ( ! function_exists('ping'))
{
	function ping()
	{
		$CI =& get_instance();
		$CI->load->library('xmlrpc');

		$message = "";
		
		$services = array (
		    'Google'         => 'http://blogsearch.google.com/ping/RPC2',
		    'Weblogs.com'    => 'http://rpc.weblogs.com/RPC2',
		    'Syndic8'        => 'http://ping.syndic8.com/xmlrpc.php' ,
		    'Ping-o-Matic!'  => 'http://rpc.pingomatic.com/',
		    'NewsGator'      => 'http://services.newsgator.com/ngws/xmlrpcping.aspx',
		    'Blog People'    => 'http://www.blogpeople.net/servlet/weblogUpdates',
		    'FeedSky'        => 'http://www.feedsky.com/api/RPC2'
		);
		
		foreach ($services as $service=>$url)
		{
			$CI->xmlrpc->server($url, 80);
			$CI->xmlrpc->method('weblogUpdates.ping');

			$request = array($CI->config->item('dmcb_title'), base_url());
			$CI->xmlrpc->request($request);

			if (!$CI->xmlrpc->send_request())
			{
			    $message .= 'Pinging '.$service.' failed: '.$CI->xmlrpc->display_error()."\n";
			}
			else
			{
				$result = $CI->xmlrpc->display_response();
				$message .= 'Pinging '.$service.' returned: '.$result['message']."\n";
			}
		}
		
		log_to_file('pings', $message);
	}
}

// ------------------------------------------------------------------------

/**
 * Pingback
 *
 * Parse through a post and initiate pingback for any sites found
 *
 * @access	public
 * @return	void
 */
if ( ! function_exists('pingback'))
{
	function pingback($postid)
	{
		$CI =& get_instance();
		$CI->load->library('xmlrpc');
		$CI->load->model('posts_model');
		
		$object = instantiate_library('post', $postid);
		$message = "Sending pingbacks to URLs referenced in post: ".base_url().$object->post['urlname']."\n\n";
		
		$urls = array();
		preg_match_all('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $object->new_post['content'], $urls);
		$urls = array_unique($urls[0]);
		foreach($urls as $url)
		{
			if (substr($url,0,strlen(base_url())) != base_url())
			{
				$message .= "URL found: $url.  Checking for a pingback URL...\n";
				
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_USERAGENT, $CI->config->item('dmcb_title')."/1.0 +http://".$CI->config->item('dmcb_server'));
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_HEADER, 1);
				$page = curl_exec($curl);
				$errno = curl_errno($curl);
				$curl_info = curl_getinfo($curl);
				curl_close($curl);
				$header_size = $curl_info['header_size'];
				$header = substr($page, 0, $header_size);
				
				if($errno != 0)
				{
					$message .= "URL could not be read.\n";
				}
				else
				{
					preg_match("/X-Pingback: (\S+)/i", $header, $pingbackurls);
					if (isset($pingbackurls[1]))
					{
						$pingbackserver = $pingbackurls[1];
						$message .= "Ping back URL of $pingbackserver found in HTTP header.\n";
					}
					else
					{
						preg_match("/<link rel=\"pingback\" href=\"([^\"]+)\" ?\/?>/i", $page, $pingbackurls);
						if (isset($pingbackurls[1]))
						{
							$pingbackserver = $pingbackurls[1];
							$message .= "Ping back URL of $pingbackserver found in HTML header.\n";
						}
						else
						{
							$pingbackserver = FALSE;
						}
					}
					
					if(!$pingbackserver)
					{
						$message .= "Could not find pingback URL.\n";
					}
					else
					{
						$CI->xmlrpc->server($pingbackserver, 80);
						$CI->xmlrpc->method('pingback.ping');

						$request = array(base_url().$object->post['urlname'], $url);
						$CI->xmlrpc->request($request);

						if (!$CI->xmlrpc->send_request())
						{
						    $message .= 'Sending pingback failed: '.$CI->xmlrpc->display_error()."\n";
						}
						else
						{
							$result = $CI->xmlrpc->display_response();
							$message .= 'Sending pingback returned: '.$result['message']."\n";
						}
					}
				}
			}
		}
		
		log_to_file('pingbacks_sent', $message);
	}
}