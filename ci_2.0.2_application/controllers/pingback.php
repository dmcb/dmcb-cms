<?php

class Pingback extends MY_Controller {

	function Pingback()
	{
		parent::__construct();
		
		$this->load->library(array('xmlrpc', 'xmlrpcs')); 
		$this->load->model('pingbacks_model');
	}

	function _remap()
	{
		$config['functions']['pingback.ping'] = array('function' => 'pingback._process');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
	}
	
	function _process($request)
	{
		$this->message = "Pingback received.\n";
		$parameters = $request->output_parameters();
        if (sizeof($parameters) != 2)
        {
			$this->message .= "Invalid parameter count of ".sizeof($parameters)."\n";
			log_to_file('pingbacks_served', $this->message);
            return $this->xmlrpc->send_error_message('0', 'Invalid number of parameters sent');
        }
		else
		{
			$source = $parameters[0];
			$target = $parameters[1];
			$this->message .= "Source parameter: $source.\nTarget parameter: $target.\n";
			
			$title = "";
			$sourcepage = "";
			$errno = 1;
			if (substr($source,0,strlen(base_url())) != base_url())
			{
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $source);
				curl_setopt($curl, CURLOPT_USERAGENT, $this->config->item('dmcb_title')."/1.0 +http://".$this->config->item('dmcb_server'));
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				$sourcepage = curl_exec($curl);
				$errno = curl_errno($curl);
				curl_close($curl);
				
				preg_match("/<title>(.*)<\/title>/i", $sourcepage, $titles);
				if (isset($titles[1]))
				{
					$title = $titles[1];
				}
			}
			
			if ($errno != 0)
			{
				$this->message .= "Source URI does not exist\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('16', 'Source URI does not exist');
			}

			$urls = array();
			$match = FALSE;
			preg_match_all('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $sourcepage, $urls);
			$urls = array_unique($urls[0]);
			foreach($urls as $url)
			{
				if ($url == $target) 
				{
					$match = TRUE;
				}
			}
			if (!$match)
			{
				$this->message .= "Source URI does not have a link to target URI\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('17', 'Source URI does not have a link to target URI');
			}
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $target);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->config->item('dmcb_title')."/1.0 +http://".$this->config->item('dmcb_server'));
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$targetpage = curl_exec($curl);
			$errno = curl_errno($curl);
			curl_close($curl);
			if ($errno != 0)
			{
				$this->message .= "Target URI does not exist\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('32', 'Target URI does not exist');
			}
			
			if (substr($target,0,strlen(base_url())) != base_url())
			{
				$this->message .= "Target URI cannot be used as target\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('33', 'Target URI cannot be used as target');
			}
			
			$postname = substr($target,strlen(base_url()));
			
			$object = instantiate_library('post', $postname, 'urlname');
			if (!isset($object->post['postid']) || $object->post['published'] != 1 || $object->post['featured'] == -1)
			{
				$this->message .= "Access denied\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('49', 'Access denied');
			}
			
			if ($this->pingbacks_model->check($object->post['postid'], $source)) 
			{
				$this->message .= "Pingback already registered\n";
				log_to_file('pingbacks_served', $this->message);
				return $this->xmlrpc->send_error_message('48', 'Pingback already registered');
			}
			
			$summary = "";
			$paragraphs = array();
			preg_match_all('/<p>(.*)<\/p>/i', $sourcepage, $paragraphs);
			foreach($paragraphs[0] as $paragraph)
			{
				preg_match_all('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $paragraph, $urls);
				$urls = array_unique($urls[0]);
				foreach($urls as $url)
				{
					if ($url == $target) 
					{
						$summary = $paragraph;
					}
				}
			}

			$ip = $_SERVER['REMOTE_ADDR'];
			
			$this->pingbacks_model->add($object->post['postid'], $source, $title, $summary, $ip);
			$this->message .= "Pingback from $source to $target recorded.\n";
	        $response = array(
							array(
								'message' => array("Pingback from $source to $target recorded. Thanks!",'string')
	                        ),
	                    'struct');
			log_to_file('pingbacks_served', $this->message);
			return $this->xmlrpc->send_response($response);
		}
	}
}