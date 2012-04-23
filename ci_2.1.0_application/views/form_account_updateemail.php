	<form action="<?=base_url();?>account/<?php echo $user['urlname'];?>/updateemail" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Update <?php echo $person_edited;?> email address</legend>

			<div id="updateemail" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>You must use a valid email address you have access to. An activation email will be sent to your new address. This is the email address which you may be contacted by this site.</p>
				</div>

				<div class="forminput">
					<label>Email address</label>
					<input name="email" type="text" maxlength="50" class="text" value="<?php echo set_value('email', $user['email']); ?>"/>
					<?php echo form_error('email'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Update" name="update" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
