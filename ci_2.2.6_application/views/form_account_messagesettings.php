	<form action="<?php echo base_url();?>account/<?php echo $user['urlname'];?>/messagesettings" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><?php echo ucfirst($person_edited);?> message settings</legend>

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

				<div class="formnotes">
					<p>You can allow other members of the site to message you. This will make your profile visible to registered members of the site even if one is not set.</p>
				</div>

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
