	<form action="<?=base_url();?>account/<?php echo $user['urlname'];?>/changepassword" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Change <?php echo $person_edited;?> password</legend>

			<div id="changepassword" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Old password</label>
					<input name="oldpassword" type="password" maxlength="15" class="text"/>
					<?php echo form_error('oldpassword'); ?>
				</div>

				<div class="forminput">
					<label>New password</label>
					<input name="newpassword" type="password" maxlength="15" class="text"/>
					<?php echo form_error('newpassword'); ?>
				</div>

				<div class="forminput">
					<label>Confirm password</label>
					<input name="confirmpassword" type="password" maxlength="15" class="text"/>
					<?php echo form_error('confirmpassword'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Change" name="change" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
