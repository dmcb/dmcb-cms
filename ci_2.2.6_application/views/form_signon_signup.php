<form class="collapsible" action="<?php echo base_url();?>signon/signup" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('signup');">Sign up for <?php echo $this->config->item('dmcb_title');?></a></legend>

		<div id="signup" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="formnotes">
				<p>Choose an email account you have access to, an email will be sent to complete your registration.<br/>
				Your email address is kept private.</p>
			</div>

			<div class="forminput">
				<label>Email address</label>
				<input name="signup_email" type="text" class="text" value="<?php echo set_value('signup_email'); ?>"/>
				<?php echo form_error('signup_email'); ?>
			</div>

			<div class="forminput">
				<label>Display name</label>
				<input name="signup_display" type="text" class="text" value="<?php echo set_value('signup_display'); ?>"/>
				<?php echo form_error('signup_display'); ?>
			</div>

			<div class="forminput">
				<label>Password</label>
				<input name="signup_password" type="password" class="text" />
				<?php echo form_error('signup_password'); ?>
			</div>

			<div class="forminput">
				<label>Confirm password</label>
				<input name="signup_password_confirm" type="password" class="text" />
				<?php echo form_error('signup_password_confirm'); ?>
			</div>

			<div class="forminput">
				<input type="submit" value="Sign up" name="signup" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
