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
	}

	function _remap()
	{
		$user = instantiate_library('user', $this->uri->segment(2));
		if (isset($user->user['mailinglist_code']) && $user->user['mailinglist_code'] == $this->uri->segment(3))
		{
			$user->new_user['mailinglist'] = "0";
			$user->save();
			$data['subject'] = 'Success!';
			$data['message'] = 'You have successfully unsubscribed from the '.$this->config->item('dmcb_friendly_server').' mailing list.';

			$message = "You have successfully unsubscribed from the ".$this->config->item('dmcb_friendly_server')." mailing list.\n\nIf you feel this message is in error, please contact support at ".$this->config->item('dmcb_email_support');

			$this->load->model('notifications_model');
			$this->notifications_model->send($user->user['email'], 'Unsubscribed from '.$this->config->item('dmcb_friendly_server').' mailing list', $message);
		}
		else
		{
			$data['subject'] = 'Error';
			$data['message'] = 'Incorrect code.';
		}
		$this->_message("Unlist account", $data['message'], $data['subject']);
	}
}