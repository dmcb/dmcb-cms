<?php

class Signoff extends MY_Controller {

	function Signoff()
	{
		parent::__construct();
	}
	
	function _remap()
	{
		// If Facebook connect is enabled, load it up
		if ($this->config->item('dmcb_signon_facebook') == "true")
		{
			$this->load->library('facebook_connect');
			// If we're logged on, log off
			if ($this->facebook_connect->me)
			{
				// Redirect will come back to this sign off page
				redirect($this->facebook_connect->logoutUrl);
			}
		}
		
		$this->session->set_userdata('signedon',FALSE);
		$this->session->unset_userdata('userid');
		$this->session->unset_userdata('displayname');
		$this->session->unset_userdata('urlname');
		$this->session->unset_userdata('rememberme');
		$this->session->unset_userdata('facebook');
		$this->session->unset_userdata('signedon');
		$this->session->set_flashdata('signoff_message', 'You have successfully signed off of '.$this->config->item('dmcb_friendly_server'));
		redirect('signon');
	}
}
?> 
