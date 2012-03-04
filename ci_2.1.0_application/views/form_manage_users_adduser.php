	<form class="collapsible" action="<?=base_url();?>manage_users/adduser" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('adduser');">Add a new user</a></legend>

			<div id="adduser" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Display name</label>
					<input name="displayname" type="text" class="text" maxlength="50" value="<?php echo set_value('displayname'); ?>"/>
					<?php echo form_error('displayname'); ?>
				</div>

				<div class="forminput">
					<label>Email address</label>
					<input name="email" type="text" class="text" maxlength="50" value="<?php echo set_value('email'); ?>"/>
					<?php echo form_error('email'); ?>
				</div>

				<div class="forminput">
					<label>Account role</label>
					<select name="role">
						<?php
						foreach ($userroles->result_array() as $role) {
							$default = FALSE;
							if (set_value('role') == $role['roleid'])
							{
								$default = TRUE;
							}
							echo '<option value="'.$role['roleid'].'" '.set_select('role', $role['roleid'], $default).' >'.$role['role'].'</option>';
						}
						?>
					</select>
				</div>

				<div class="forminput">
					<input type="submit" value="Add a new user" name="adduser" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>