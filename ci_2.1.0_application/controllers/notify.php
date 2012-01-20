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
class Notify extends MY_Controller {

	function Notify()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		$this->load->model('notifications_model');
	}
	
	function _remap()
	{
		$this->form_validation->set_rules('note', 'note', 'xss_clean|strip_tags|trim');
		
		// Keep session data from manage users page intact
		$this->session->keep_flashdata('sort');
		$this->session->keep_flashdata('page');
		
		// Load session data about the notification and keep all the data alive
		$this->data['change'] = $this->session->flashdata('change');
		
		$parentid = $this->session->flashdata('parentid');
		$return = $this->session->flashdata('return');
		
		$this->session->keep_flashdata('action');
		$this->session->keep_flashdata('actionon');
		$this->session->keep_flashdata('actiononid');
		$this->session->keep_flashdata('parentid');
		$this->session->keep_flashdata('scope');
		$this->session->keep_flashdata('scopeid');
		$this->session->keep_flashdata('content');
		$this->session->keep_flashdata('return');
		
		// If the user that would be notified is yourself, ignore the notification and move on
		if ($this->session->userdata('userid') == $parentid)
		{
			redirect($return);
		}
		
		if ($this->form_validation->run())
		{
			// Burn up session data
			$action = $this->session->flashdata('action');
			$actionon = $this->session->flashdata('actionon');
			$actiononid = $this->session->flashdata('actiononid');
			$parentid = $this->session->flashdata('parentid');
			$scope = $this->session->flashdata('scope');
			$scopeid = $this->session->flashdata('scopeid');
			$content = $this->session->flashdata('content');
			$return = $this->session->flashdata('return');
			
			// Determine if the user is supposed to be alerted with a notification or not
			$alert_user = FALSE;
			if (isset($_POST['buttonchoice']) && $_POST['buttonchoice'] == "send")
			{
				$alert_user = TRUE;
			}
			
			// Grab user information
			$user = instantiate_library('user', $parentid);
			
			// Add notification
			$this->notifications_model->add($this->session->userdata('userid'), $action, $actionon, $actiononid, $user->user, $scope, $scopeid, $content, set_value('note'), $alert_user);	
			redirect($return);
		}
		else
		{
			$this->_initialize_page('notification', 'Notification for '.$this->data['change'], $this->data);
		}
	}
}