	<form action="<?php echo base_url();?>account/<?php echo $user['urlname'];?>/resetpassword" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Reset <?php echo $person_edited;?> password</legend>

			<div id="changepassword" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>This will reset the user's password and send you and the user a copy. If the user hasn't activated their account, it will be activated.</p>
				</div>

				<div class="forminput">
					<input type="submit" value="Reset" name="reset" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
