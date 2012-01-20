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

		$this->lang->load('emailform', 'english', FALSE, TRUE, APPPATH.'site_specific_');
	}

	function _remap()
	{
		$destination = NULL;
		$email = NULL;
		$submission = sprintf($this->lang->line('submission'), $_SERVER['REMOTE_ADDR'])."\n\n";

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
					$this->notifications_model->send_to_server($email, sprintf($this->lang->line('submission_received'), $_POST['form']), $submission, array(), $destination);
					$this->notifications_model->send($email, $_POST['form']." received", sprintf($this->lang->line('submission_sent'), strtolower($_POST['form']), $this->config->item('dmcb_title'))."\n\n".base_url(), array(), $destination);
				}
				else
				{
					$this->notifications_model->send_to_server($email, sprintf($this->lang->line('submission_received'), $_POST['form']), $submission);
					$this->notifications_model->send($email, $_POST['form']." received", sprintf($this->lang->line('submission_sent'), strtolower($_POST['form']), $this->config->item('dmcb_title'))."\n\n".base_url());
				}

				$title = $this->lang->line('submission_sent_header');
				$data['subject'] = $this->lang->line('submission_sent_subject');
				$data['message'] = sprintf($this->lang->line('submission_sent_confirmation'), strtolower($_POST['form']), $this->config->item('dmcb_title'));
			}
			else
			{
				$title = $this->lang->line('error_form_already_sent_header');
				$data['subject'] = $this->lang->line('error_form_already_sent_subject');
				$data['message'] = sprintf($this->lang->line('error_form_already_sent'), '</p><br/><p class="small">'.str_replace("\n","<br/>",$recent_form['form']));
			}
		}
		else
		{
			$title = $this->lang->line('error_submission_failed_header');
			$data['subject'] = $this->lang->line('error_submission_failed_subject');

			if (isset($_POST['form']))
			{
				$data['message'] = $this->lang->line('error_submission_failed_invalid_email');
			}
			else
			{
				$data['message'] = sprintf($this->lang->line('error_submission_failed_bad_form'), "<a href=\"mailto:support@".$this->config->item('dmcb_server')."\">support@".$this->config->item('dmcb_server')."</a>");
			}
		}
		$this->_message($title, $data['message'], $data['subject']);
	}
}