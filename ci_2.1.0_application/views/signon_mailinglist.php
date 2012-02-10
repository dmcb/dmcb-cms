<div class="fullcolumn">
	<p>Please enter your email address.</p>
	<div class="seperator">&nbsp;</div>
	<form action="<?=current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Email address</label>
					<input name="signup_email" type="text" class="text" maxlength="50" value="<?php echo set_value('signup_email'); ?>"/>
					<?php echo form_error('signup_email'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Subscribe" name="subscribe" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>