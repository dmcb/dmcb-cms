<?php
if ($this->config->item('csrf_protection')) $csrf = "parameters: '".$this->security->get_csrf_token_name()."=".$this->security->get_csrf_hash()."',";
$this->packages[4]['javascript'][] = "
Event.observe('waiver', 'submit', function(event) {
	$('waiver').request({
		parameters: { ajax:true },
		onCreate: function() {
			$('waiver_error').update(null);
			$('waiver_loading').update('<img src=\"".base_url()."assets/images/ajax-loader.gif\" alt=\"Please wait...\" />');
		},
		onComplete: function() {
			$('waiver_loading').update(null);
		},
		onFailure: function() {
			$('waiver').submit();
		},
		onSuccess: function(t) {
			if (t.responseText == \"TRUE\") {
				$('waiver_container').setStyle({ background: 'none' });
				Effect.Shrink('waiver_container');
			}
			else {
				window.location.replace('".base_url()."');
			}
		}
	});
	Event.stop(event);
});";
?>

<div class="waiver" id="waiver_container">
	<div class="waiver_body">
		<h2><?php echo $waiver['title'];?></h2>
		<p><?php echo $waiver['content'];?></p>

		<form id="waiver" action="<?php echo base_url();?><?php echo $page['urlname'];?>/waiver" method="post" onsubmit="return dmcb.submit(this);">
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<input type="submit" value="<?php echo $waiver['accept_message'];?>" name="accept" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>

			<?php if ($waiver['is_mandatory'])
			{
			?>
				<input type="submit" value="I don't agree" name="cancel" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			<?php
			}
			?>

			<div id="waiver_loading"></div>
			<div id="waiver_error"></div>
		</form>
	</div>
</div>