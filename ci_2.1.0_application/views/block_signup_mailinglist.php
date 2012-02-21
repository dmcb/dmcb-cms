<?php
if ($this->config->item('csrf_protection')) $csrf = "parameters: '".$this->security->get_csrf_token_name()."=".$this->security->get_csrf_hash()."',";
$this->javascript['maillist_signup'] = array('weight' => 1, 'javascript' => "
				Event.observe('maillist_signup', 'submit', function(event) {
					$('maillist_signup').request({
						parameters: { ajax:true },
						onCreate: function() {
							$('maillist_error').update(null);
							$('maillist_loading').update('<img src=\"".base_url()."includes/images/ajax-loader.gif\" alt=\"Please wait...\" />');
						},
						onComplete: function() {
							$('maillist_loading').update(null);
						},
						onFailure: function() {
							$('maillist_signup').submit();
						},
						onSuccess: function(t) {
							if (t.responseText.indexOf('error') != -1) {
								$('maillist_error').update(t.responseText);
							}
							else {
								$('maillist_signup').update(t.responseText);
							}
						}
					});
					Event.stop(event);
				});");
?>

<form id="maillist_signup" action="<?php echo base_url();?>signon/mailinglist" method="post">
	<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
	<input type="hidden" name="buttonchoice" value="" class="hidden" />
	<input name="signup_email" type="text" class="text" maxlength="50" value=""/>
	<input type="submit" value="Subscribe" name="subscribe" class="button"/>

	<div id="maillist_loading"></div>
	<div id="maillist_error"></div>
</form>