 <?php

class Subscription extends MY_Controller {

	function Subscription()
	{
		parent::__construct();
		
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	}
	
	function _remap()
	{	
		if (($this->uri->segment(2) == "order" && $this->uri->segment(3) == "validation") || $this->acl->allow('site', 'subscribe', TRUE) || $this->_access_denied())
		{
			// This doesn't actually work, need a better work around:
			// $this->config->set_item('csrf_protection', FALSE); // PayPal and CSRF don't mix since I can't set CSRF values in PayPal response
		
			$this->load->model('subscriptions_model');
			$this->user = instantiate_library('user', $this->session->userdata('userid'));
			$this->subscription_types = $this->subscriptions_model->get_types();
			
			$method = $this->uri->segment(2);
			if ($method == "order")
			{
				$this->focus = $method;
				$this->$method();
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function index()
	{
		$data['subscription'] = $this->subscriptions_model->get($this->user->user['userid']);
		if ($data['subscription'] != NULL)
		{
			$data['subscription']['type'] = $this->subscriptions_model->get_type($data['subscription']['typeid']);
		}
		if ($data['subscription'] == NULL || strtotime($data['subscription']['date']) < time() || $data['subscription']['typeid'] == $this->subscriptions_model->get_type_free())
		{
			$data['order_form'] = $this->load->view('form_subscription_order', array('user' => $this->user->user, 'subscription_types' => $this->subscription_types), TRUE);
		}
		else
		{
			$data['order_form'] = $this->load->view('form_subscription_noorder', NULL, TRUE);
		}
		$this->_initialize_page('subscription', 'Subscription', $data);
	}
	
	function order()
	{
		$this->load->model('orders_model');
		if ($this->uri->segment(3) == "confirmation") // This URL is visited when the user completes an already successful PayPal order
		{
			$order = $this->orders_model->get($this->uri->segment(4));
			$user = instantiate_library('user', $this->session->userdata('userid'));
			if ($order != NULL && $user->user['userid'] == $order['userid'])
			{
				$items = $this->orders_model->get_items($order['orderid']);
				
				$data['subject'] = "Thank you!";
				$data['message'] = "Your order number is #".$order['orderid'].". A confirmation email has been sent to ".$user->user['email'].".<br/><br/>You have ordered:<hr/>";
				foreach ($items->result_array() as $item)
				{
					$data['message'] .= $item['quantity']."x ".$item['description']." @ ";
					if ($item['tax'] != 0)
					{
						$data['message'] .= "$".$item['price']." + $".round($item['price']*$item['tax'],2)." tax";
					}
					else
					{
						$data['message'] .= "$".$item['price'];
					}
					$data['message'] .= ": <strong>$".round($item['total'],2)."</strong><br/>";
				}
				$data['message'] .= "Total: <strong>$".round($order['total'],2)."</strong><br/>";
				$data['message'] .= 
					"<br/><br/>The order is for:<hr/>".
					$order['firstname']." ".$order['lastname']."<br/>".
					$order['address']."<br/>".
					$order['city'].", ".$order['province']."<br/>".
					$order['postalcode']."<br/>".
					$order['country']."<br/>";
				$this->_message("Order confirmation", $data['message'], $data['subject']);
			}
			else
			{
				$this->_access_denied();
			}
		}
		else if ($this->uri->segment(3) == "validation") // This URL is hit in the event a PayPal order has been submitted by the user
		{
			// Assign PayPal posted variables to local variables
			$payment_status = $this->input->post('payment_status', TRUE);
			$payment_amount = $this->input->post('mc_gross', TRUE);
			$payment_currency = $this->input->post('mc_currency', TRUE);
			$txn_id = $this->input->post('txn_id', TRUE);
			$receiver_email = $this->input->post('receiver_email', TRUE);
			$payer_email = $this->input->post('payer_email', TRUE);
			$invoice = $this->input->post('invoice', TRUE);
			
			if ($invoice != NULL)
			{
				$order = $this->orders_model->get($invoice);
			}
			
			if (isset($order['orderid']))
			{
				$user = instantiate_library('user', $order['userid']);
				$message = "PayPal attempting validation against order #".$order['orderid']." from ".$_SERVER['REMOTE_ADDR']."\n\n";

				// Read the post from PayPal system and add 'cmd'
				$req = 'cmd=_notify-validate';

				foreach ($_POST as $key => $value) 
				{
					$value = urlencode(stripslashes($value));
					$req .= "&$key=$value";
					$message .= "$key=$value\n";
				}
				$message .="\n";
				
				// Post back to PayPal system to validate
				$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
				$fp = fsockopen ('ssl://'.$this->config->item('dmcb_paypal_url'), 443, $errno, $errstr, 30);

				if (!$fp)
				{
					$message .= "Order failed. Error posting validation response to PayPal.\n";
				} 
				else 
				{		
					fputs ($fp, $header . $req);
					while (!feof($fp))
					{
						$res = fgets ($fp, 1024);
						if (strcmp ($res, "VERIFIED") == 0) 
						{
							if ($payment_status != "Completed") // check the payment_status is Completed
							{
								$message .= "Order failed. The PayPal payment was never complete.\n";
							}
							else if ($order['completed']) // check that txn_id has not been previously processed
							{
								$message .= "Order failed. The order has been previously processed.\n";
							}
							else if ($receiver_email != $this->config->item('dmcb_paypal_email')) // check that receiver_email is your Primary PayPal email
							{
								$message .= "Order failed. An incorrect PayPal account of $receiver_email was specified.\n";
							}
							//else if ($payer_email != $user->user['email']) // check that payer_email is correct account
							//{
							//	$message .= "Order failed. The PayPal email address of $payer_email does not match the order email address of ".$user['email']."\n";
							//}
							else if ($payment_amount != round($order['total'],2) || $payment_currency != "CAD") // check that payment_amount/payment_currency are correct
							{
								$message .= "Order failed. The payment amount/currency of $payment_amount $payment_currency was incorrect.\n";
								// Send an email to the web master if a user has paid for a subscription, but an incorrect price.
								$this->notifications_model->send_to_server($user->user['email'], "Order failed",
									"Order #".$order['orderid']." failed.\n\n".
									"The user ".$user->user['displayname'].", ".$user->user['email']." paid via PayPal $".$payment_amount." instead of the order total of $".round($order['total'],2));
							}
							else // Process payment!
							{
								$this->orders_model->set_completed($order['orderid'], $txn_id);
								$message .= "Order succeeded. PayPal payment validated.\n";
								
								// Send confirmation email
								$items = $this->orders_model->get_items($order['orderid']);
								$summary = "";
								foreach ($items->result_array() as $item)
								{
									$summary .= $item['quantity']."x ".$item['description']." @ ";
									if ($item['tax'] != 0)
									{
										$summary .= "$".$item['price']." + $".round($item['price']*$item['tax'],2)." tax";
									}
									else
									{
										$summary .= "$".$item['price'];
									}
									$summary .= ": $".round($item['total'],2)."\n";
								}
								$summary .= "Total: $".round($order['total'],2)."\n";
								$summary .= 
									"\n\nThe order is for:\n".
									$order['firstname']." ".$order['lastname']."\n".
									$order['address']."\n".
									$order['city'].", ".$order['province']."\n".
									$order['postalcode']."\n".
									$order['country']."\n";
									
									$internalsummary = $summary."\n\nDO NOT RESPOND TO THIS EMAIL. The customer has been informed of the order. Please ensure the order is filled.";
									$externalsummary = $summary;
									if ($this->acl->enabled('site', 'subscribe'))
									{
										$internalsummary .= "\n\nIMPORTANT: If the user ordered a subscription to the site, click the following link to set it in the system: ".base_url()."manage_users/subscription/".$order['userid'];
										$externalsummary .= "\n\nIf you have ordered a subscription, it may take a few business days to be processed.";
									}
								$this->notifications_model->send_to_server($user->user['email'], "Order received", "The customer's order number is #".$order['orderid']."\n\nThey have ordered:\n\n".$internalsummary);
								$this->notifications_model->send($user->user['email'], "Order confirmed", "Your order number is #".$order['orderid']."\n\nYou have ordered:\n\n".$externalsummary);
							}
						}
						else if (strcmp ($res, "INVALID") == 0)  // Log for manual investigation
						{
							$message .= "Order failed. PayPal reports an invalid transaction, possible hack attempt.\n";
						}
					}
					fclose ($fp);
				}		
			}
			else
			{
				$message = "PayPal attempted validation against non-existent order #".$invoice." from ".$_SERVER['REMOTE_ADDR']."\n";
			}
			log_to_file('orders', $message);
		}
		else if ($this->uri->segment(3) == "retraction") // URL used by PayPal in the event the user cancels the order
		{
			$order = $this->orders_model->get($this->uri->segment(4));
			if ($order != NULL && $this->session->userdata('userid') == $order['userid'])
			{
				$this->orders_model->delete($order['orderid']);
				$this->_message(
					'Order retraction', 
					'Your order number #'.$order['orderid'].' has been cancelled. <a href="'.base_url().'subscription">Return to managing your subscription.</a>'
				);
			}
			else
			{
				$this->_access_denied();
			}
		}
		else
		{
			$this->form_validation->set_rules('subscription', 'subscription type', 'xss_clean|strip_tags|trim|required');
			$this->form_validation->set_rules('firstname', 'first name', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('lastname', 'last name', 'xss_clean|strip_tags|trim|required|min_length[2]|max_length[50]');
			$this->form_validation->set_rules('address', 'address', 'xss_clean|strip_tags|trim|required|max_length[200]');
			$this->form_validation->set_rules('city', 'city', 'xss_clean|strip_tags|trim|required|max_length[50]');
			$this->form_validation->set_rules('province', 'province/state', 'xss_clean|strip_tags|trim|required');
			$this->form_validation->set_rules('postalcode', 'postal/zip code', 'xss_clean|strip_tags|trim|required|max_length[30]');
			$this->form_validation->set_rules('country', 'country', 'xss_clean|strip_tags|trim|required');
			$this->form_validation->set_rules('phone', 'phone number', 'xss_clean|strip_tags|trim|required|callback_phone_check');
			$this->form_validation->set_rules('comment', 'comment', 'xss_clean|strip_tags|trim|max_length[500]');
			
			# https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables
			if ($this->form_validation->run())
			{
				// Clear out any opened orders that were never confirmed by PayPal
				$this->orders_model->delete_open($this->user->user['userid']);
				$subscription = $this->subscriptions_model->get_type(set_value('subscription'));
				// Create unique order ID that isn't just an auto incremented integer
				$orderid = strtoupper(random_string());
				$order = $this->orders_model->get($orderid);
				while ($order['orderid'] != NULL)
				{
					$orderid = strtoupper(random_string());
					$order = $this->orders_model->get($orderid);
				}
				$this->orders_model->add(
					$orderid, 
					$this->user->user['userid'], 
					set_value('firstname'), 
					set_value('lastname'), 
					set_value('address'), 
					set_value('city'), 
					set_value('province'), 
					set_value('postalcode'), 
					set_value('country'), 
					set_value('phone'), 
					set_value('comment')
				);
				$this->orders_model->add_item($orderid, $subscription['type']." subscription", 1, $subscription['price'], $subscription['tax']);
				redirect("https://".$this->config->item('dmcb_paypal_url')."/cgi-bin/webscr?".
					"cmd=_xclick".
					"&business=".urlencode($this->config->item('dmcb_paypal_email')).
					"&return=".urlencode(base_url()."subscription/order/confirmation/".$orderid).
					"&notify_url=".urlencode(base_url()."subscription/order/validation").
					"&cancel_return=".urlencode(base_url()."subscription/order/retraction/".$orderid).
					"&item_name=".urlencode($subscription['type'].' subscription').
					"&shipping=0.0".
					"&no_shipping=1".
					"&no_note=1".
					"&tax=".urlencode(round(($subscription['price']*$subscription['tax']),2)).
					"&amount=".urlencode(round(($subscription['price']+($subscription['price']*$subscription['tax'])),2)).
					"&invoice=".$orderid.
					"&email=".urlencode($this->user->user['email']).
					"&first_name=".urlencode(set_value('firstname')).
					"&last_name=".urlencode(set_value('lastname')).
					"&address1=".urlencode(set_value('address')).
					"&city=".urlencode(set_value('city')).
					"&state=".urlencode(set_value('province')).
					"&zip=".urlencode(set_value('postalcode')).
					"&country=".urlencode(set_value('country')).
					"&night_phone_a=".urlencode(set_value('phone')).
					"&lc=en".
					"&currency_code=CAD");
			}
			else
			{
				$this->index();
			}
		}
	}
	
	function phone_check($str)
	{
		$this->form_validation->set_message('phone_check', "The phone number must be a valid format.");
		$formats = array('###-###-####', '####-###-###', '(###) ###-###', '####-####-####',
			'##-###-####-####', '####-####', '###-###-###', '#####-###-###', '##########');
		$format = trim(ereg_replace("[0-9]", "#", $str));
		return (in_array($format, $formats)) ? true : false;
	}
}
?> 
