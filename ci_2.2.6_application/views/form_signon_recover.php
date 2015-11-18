<form class="collapsible" action="<?php echo base_url();?>signon/recover" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('recover');">Recover your password</a></legend>

		<div id="recover" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="formnotes">
				<p>A new temporary password will be sent to your email account</p>
			</div>

			<div class="forminput">
				<label>Email address</label>
				<input name="emailforgot" type="text" class="text" value="<?php echo set_value('email_forgot'); ?>"/>
				<?php echo form_error('emailforgot'); ?>
			</div>

			<div class="forminput">
				<input type="submit" value="Recover" name="recover" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
