<div class="leftcolumn">
	<?php
	if (sizeof($priveleges) > 0)
	{
		echo '<h2>Your privileges</h2><ul>';
		foreach ($priveleges as $privelege)
		{
			echo '<li>';
			if ($privelege['on'] == "site")
			{
				echo 'Site-wide '.$privelege['role'];
			}
			else
			{
				echo ucfirst($privelege['role']).' on <a href="'.base_url().$privelege[$privelege['on']]['urlname'].'">'.$privelege[$privelege['on']]['title'].'</a>';
			}
			echo '</li>';
		}
		echo '</ul>';
	}
	?>
	&nbsp;
</div>

<div class="centercolumnlarge">
	<form action="<?=base_url();?>account/changepassword" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Change your password</legend>

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

	<form action="<?=base_url();?>account/updateemail" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Update your email address</legend>

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

	<?php
		if ($this->config->item('dmcb_signon_facebook') == "true")
		{
	?>

	<form>
		<fieldset>
			<legend>Facebook connect</legend>

			<div class="panel alwaysopen"><div>
				<div class="formnotes">
					<p>
					<?php
					if ($user['facebook_uid'] == NULL)
					{
						echo 'You do not have Facebook associated with this account, to do so, set your email address above to your email address for Facebook, and sign on to the site using Facebook.';
					}
					else
					{
						echo 'You have associated Facebook with your account. <a href="'.base_url().'account/removefacebook/" onclick="return dmcb.confirmation(\'Are you sure you wish to remove Facebook association with your account?\')">Remove association</a>.';
					}
					?>
					</p>
				</div>
			</div></div>
		</fieldset>
	</form>

	<?php
		}
	?>

	<form action="<?=base_url();?>account/messagesettings" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Message settings</legend>

			<div id="messagesettings" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>Occasionally there may be updates sent out if you're on the mailing list. You can opt out of this to stop receiving these updates.</p>
				</div>

				<div class="forminput">
					<label>On mailing list?</label>
					<input name="mailinglist" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($user['mailinglist'] == 1) $default = TRUE; echo set_checkbox('mailinglist', '1', $default); ?>/>
				</div>

				<?php
				if ($this->acl->enabled('profile', 'message'))
				{
				?>

				<div class="forminput">
					<label>Allow messages?</label>
					<input name="getmessages" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($user['getmessages'] == 1) $default = TRUE; echo set_checkbox('getmessages', '1', $default); ?>/>
				</div>

				<div class="formnotes">
					<p>You can block specific users from sending you messages by typing in their display name.</p>
				</div>

				<?php
					for ($i=0; $i<=sizeof($blocked); $i++)
					{
						$block['displayname'] = "";
						if ($i != sizeof($blocked))
							$block = $blocked[$i];
						$field = 'block'.($i+1);

						echo '<div class="forminput">';
						if ($i == sizeof($blocked) && $i != 0)
							echo '<label>Block another user</label>';
						else if ($i == sizeof($blocked))
							echo '<label>Block a user</label>';
						else
							echo '<label>Blocked user #'.($i+1).'</label>';
						echo '<input name="'.$field.'" type="text" maxlength="30" class="text" value="'.set_value($field, $block['displayname']).'"/>';
						echo form_error($field);
						echo '</div>';
					}
				}
				?>

				<div class="forminput">
					<input type="submit" value="Set" name="set" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>
