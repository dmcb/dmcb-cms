<?php

class Activate extends MY_Controller {

	function Activate()
	{
		parent::__construct();
	}
	
	function _remap()
	{
		$user = instantiate_library('user', $this->uri->segment(2));
		if (isset($user->user['code']) && $user->user['code'] == $this->uri->segment(3))
		{
			$user->new_user['code'] = "";
			$user->save();
			$data['subject'] = 'Success!';
			$data['message'] = 'You have successfully activated your account at '.$this->config->item('dmcb_friendly_server').'.  Click <a href="'.base_url().'signon/">here</a> to sign on.';
			// Add subscription trial if subscriptions are enabled on the site and a trial duration greater than zero is specified
			if ($this->acl->enabled('site', 'subscribe') && $this->config->item('dmcb_post_subscriptions_trial_duration') > "0")
			{
				$this->load->model('subscriptions_model');
				$typeid = $this->subscriptions_model->get_type_free();
				$this->subscriptions_model->set($user->user['userid'], date("Ymd",mktime(0,0,0,date("m"),date("d")+$this->config->item('dmcb_post_subscriptions_trial_duration'),date("Y"))),$typeid);
				$data['message'] .= "<br/>Your free ".$this->config->item('dmcb_post_subscriptions_trial_duration')." day trial subscription starts now.";
			}
		}
		else
		{
			$data['subject'] = 'Error';
			$data['message'] = 'Incorrect code.';
		}
		$this->_message("Activate account", $data['message'], $data['subject']);
	}
}
?> 
