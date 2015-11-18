<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2012, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Unlist extends MY_Controller {

	function Unlist()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	function _remap()
	{
		$user = instantiate_library('user', $this->uri->segment(2));
		if (isset($user->user['mailinglist_code']) && $user->user['mailinglist_code'] == $this->uri->segment(3))
		{
			$this->form_validation->set_rules('unsubscribe', 'unsubscribe', 'xss_clean|required');

			if ($this->form_validation->run())
			{
				$user->new_user['mailinglist'] = "0";
				$user->save();
				$data['subject'] = 'Success!';
				$data['message'] = 'You have successfully unsubscribed from the '.$this->config->item('dmcb_friendly_server').' mailing list.';

				$message = "You have successfully unsubscribed from the ".$this->config->item('dmcb_friendly_server')." mailing list.\n\nIf you feel this message is in error, please contact support at ".$this->config->item('dmcb_email_support')."\n".
					'If you would like to resubscribe, please visit the following link to set your mailing list settings: '.base_url().'account/messagesettings.';

				$this->load->model('notifications_model');
				$this->notifications_model->send($user->user['email'], 'Unsubscribed from '.$this->config->item('dmcb_friendly_server').' mailing list', $message);

				$this->_message("Unlist account", $data['message'], $data['subject']);
			}
			else
			{
				$this->_initialize_page('unlist',"Unlist", NULL);
			}
		}
		else
		{
			$data['subject'] = 'Error';
			$data['message'] = 'Incorrect code. There was likely a problem with your unsubscribe link. Please visit the following link to set your mailing list settings: <a href="'.base_url().'account/messagesettings">'.base_url().'account/messagesettings</a>.';
			$this->_message("Unlist account", $data['message'], $data['subject']);
		}
	}
}