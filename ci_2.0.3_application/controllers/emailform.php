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
class Emailform extends MY_Controller {

	function Emailform()
	{
		parent::__construct();
	}
	
	function _remap()
	{
		$destination = NULL;
		$email = NULL;
		$submission = "A submission form was sent from ".$_SERVER['REMOTE_ADDR']."\n\n";
		
		foreach ($_POST as $key=>$value)
		{
			$key = $this->security->xss_clean($key);
			$value = $this->security->xss_clean($value);
			
			if ($key != "buttonchoice")
			{
				$submission .= "$key: $value\n";
				if ($key == "email")
				{
					$email = $value;
				}
				else if ($key == "destination")
				{
					$destination = $value;
				}
			}
		}
		
		$this->load->helper('email');
		if (valid_email($email) && isset($_POST['form']))
		{
			$this->load->model('forms_model');
			$recent_form = $this->forms_model->get_recent($email);
			if ($recent_form == NULL)
			{
				$this->load->model('notifications_model');
				$this->forms_model->add($submission, $email, $_SERVER['REMOTE_ADDR']);
				if (isset($destination) && valid_email($destination."@".$this->config->item('dmcb_server')))
				{
					$this->notifications_model->send_to_server($email, $_POST['form']." received", $submission, array(), $destination);
					$this->notifications_model->send($email, $_POST['form']." received", "Your ".strtolower($_POST['form'])." submission has been received. We will respond as quickly as we can, thanks for getting involved with ".$this->config->item('dmcb_title')."\n\n".base_url(), array(), $destination);
				}
				else
				{
					$this->notifications_model->send_to_server($email, $_POST['form']." received", $submission);
					$this->notifications_model->send($email, $_POST['form']." received", "Your ".strtolower($_POST['form'])." submission has been received. We will respond as quickly as we can, thanks for getting involved with ".$this->config->item('dmcb_title')."\n\n".base_url());
				}
				
				$title = "Submission received";
				$data['subject'] = 'Thanks!';
				$data['message'] = 'Your '.strtolower($_POST['form']).' has been received, you will receive a confirmation email of your submission.  We will respond as quickly as we can, thanks for getting involved with '.$this->config->item('dmcb_title');
			}
			else
			{
				$title = "Submission failed";
				$data['subject'] = 'Form already sent';
				$data['message'] = 'You have already submitted a form within the past day, please wait until tomorrow to send another.  Here was the form you submitted:</p><br/><p class="small">'.str_replace("\n","<br/>",$recent_form['form']);
			}
		}
		else
		{
			$title = "Submission failed";
			$data['subject'] = 'There appears to be a mistake';
			
			if (isset($_POST['form']))
			{
				$data['message'] = 'A valid email address was not submitted, so we will have no way to respond to you! Please try again.';			
			}
			else
			{
				$data['message'] = "There was a problem with the submitted form.  If you feel this is in error, please contact support at <a href=\"mailto:support@".$this->config->item('dmcb_server')."\">support@".$this->config->item('dmcb_server')."</a>.";			
			}
		}
		$this->_message($title, $data['message'], $data['subject']);
	}
}
?> 
