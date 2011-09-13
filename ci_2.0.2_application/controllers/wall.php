<?php

class Wall extends MY_Controller {

	function Wall()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model('walls_model');
	}
	
	function _remap()
	{
		$this->form_validation->set_rules('content', 'content', 'xss_clean|strip_tags|trim|required|max_length[140]|callback_content_check');
		$this->form_validation->set_rules('name', 'name', 'xss_clean|strip_tags|trim|required|max_length[50]');
		$this->form_validation->set_rules('city', 'city', 'xss_clean|strip_tags|trim|required|max_length[50]');
	
		if ($this->form_validation->run())
		{
			$this->load->model('notifications_model');
			$this->notifications_model->send("4037144828@fido.ca", $this->config->item('dmcb_default_page')." alert", set_value('content'));
			$this->walls_model->add(set_value('content'), set_value('name'), set_value('city'), $_SERVER['REMOTE_ADDR']);
		}
	
		$this->load->helper('pagination');
		$wall_limit = 10;
		$offset = generate_pagination($this->walls_model->get_count(), $wall_limit);
		$data['walls'] = $this->walls_model->get($wall_limit, $offset);
		
		$this->_initialize_page('wall', 'Wall', $data);
	}
}
?>