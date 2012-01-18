<div class="fullcolumn">
	<table>

	<?php
		echo 'Sort users by: ';
		if ($sort == "by_role")
		{
			echo '<b>role</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_role">role</a>';
		}
		echo ' | ';
		if ($sort == "by_status")
		{
			echo '<b>status</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_status">status</a>';
		}
		echo ' | ';
		if ($sort == "by_signon")
		{
			echo '<b>last sign on</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_signon">last sign on</a>';
		}
		echo ' | ';
		if ($sort == "by_registration")
		{
			echo '<b>registration date</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_registration">registration date</a>';
		}
		echo ' | ';
		if ($sort == "by_name")
		{
			echo '<b>name</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_name">name</a>';
		}
		if ($this->acl->enabled('site', 'subscribe'))
		{
			echo ' | ';
			if ($sort == "by_subscription")
			{
				echo '<b>subscription</b>';
			}
			else
			{
				echo '<a href="'.base_url().'manage_users/by_subscription">subscription</a>';
			}
		}
		echo '<br/><br/>';

		foreach ($userlist as $key => $value)
		{
			echo '<tr><td colspan="7"><h2>'.ucfirst($key).'</h2></td></tr>';

			foreach ($value as $user)
			{
				echo '<tr class="data"><td>';
				echo '<a href="'.base_url().'manage_users/report/'.$user['userid'].'">'.$user['displayname'].'</a>';

				if (substr($user['plusminus'], 0, 1) != '0')
				{
					if (substr($user['plusminus'], 0, 1) == '-')
					{
						echo ' <span class="red">'.$user['plusminus'].'</span>';
					}
					else
					{
						echo ' <span class="green">+'.$user['plusminus'].'</span>';
					}
				}

				echo '</td>';
				if ($sort == "by_signon") // Sorting by last sign on shows date of last sign on
				{
					if ($user['lastsignon'] == "0000-00-00 00:00:00")
					{
						echo '<td>&nbsp;</td>';
					}
					else
					{
						echo '<td>Last sign on: '.date("F jS, Y", strtotime($user['lastsignon'])).'</td>';
					}
				}
				else if ($sort == "by_registration") // Sorting by registration date shows registration date
				{
					if ($user['registered'] == "0000-00-00 00:00:00")
					{
						echo '<td>&nbsp;</td>';
					}
					else
					{
						echo '<td>Registered: '.date("F jS, Y", strtotime($user['registered'])).'</td>';
					}
				}
				else if ($sort == "by_subscription") // Sorting by subscription shows expiry date if applicable
				{
					echo '<td>';
					if (isset($user['subscriptiondate']))
					{
						if (strtotime($user['subscriptiondate']) < time())
						{
							echo 'Expired on '.date("F jS, Y", strtotime($user['subscriptiondate']));
						}
						else
						{
							echo 'Expires on '.date("F jS, Y", strtotime($user['subscriptiondate']));
						}
					}
					echo '</td>';
				}
				else
				{
					echo '<td>'.$user['email'].'</td>';
				}

				echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
				foreach ($userroles->result_array() as $role)
				{
					if ($role['roleid'] == $user['roleid'])
					{
						echo '<option value="'.base_url().'manage_users/set_role/'.$user['userid'].'/'.$role['roleid'].'" selected="selected">'.ucfirst($role['role']).'</option>';
					}
					else
					{
						echo '<option value="'.base_url().'manage_users/set_role/'.$user['userid'].'/'.$role['roleid'].'">'.ucfirst($role['role']).'</option>';
					}
				}
				echo '</select></td>';

				echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
				foreach ($userstatus->result_array() as $status)
				{
					if ($status['statusid'] == $user['statusid'])
					{
						echo '<option value="'.base_url().'manage_users/set_status/'.$user['userid'].'/'.$status['statusid'].'" selected="selected">'.$status['status'].'</option>';
					}
					else
					{
						echo '<option value="'.base_url().'manage_users/set_status/'.$user['userid'].'/'.$status['statusid'].'">'.$status['status'].'</option>';
					}
				}
				echo '</select></td>';

				echo '<td><a href="'.base_url().'manage_users/password/'.$user['userid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to reset the password of this user?\')">Reset password</a></td>';

				if ($this->acl->enabled('site', 'subscribe'))
					echo '<td><a href="'.base_url().'manage_users/subscription/'.$user['userid'].'">Set subscription</a></td>';

				echo '<td><a href="'.base_url().'manage_users/delete/'.$user['userid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this user?\')">Delete</a></td>';
			}
			echo '</tr><tr><td colspan="7"><br/></td></tr>';
		}
	?>

	</table>

	<?php echo $this->pagination->create_links();?>

	<div class="spacer">&nbsp;</div>

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

	<form class="collapsible" action="<?=base_url();?>manage_users/mailinglist" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('mailinglist');">Send email to mailing list</a></legend>

			<div id="mailinglist" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Send to</label>
					<input name="sendto_all" id="sendto_all" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('sendto_all', '1'); ?>/>
					All users on the mailing list
				</div>

				<br/>

				<?php
				foreach ($userroles->result_array() as $role)
				{
					if ($memberrole == $role['roleid'])
					{
						foreach ($userstatus->result_array() as $status)
						{
							$id = 'sendto_'.$role['roleid'].'_'.$status['status'];
							echo '
								<div class="forminput">
									<label>&nbsp;</label>

									<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
									'.$status['status'].' '.$role['role'].'s
								</div>';
						}
					}
					else
					{
						$id = 'sendto_'.$role['roleid'];
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								'.ucfirst($role['role']).'s
							</div>';
					}
				}

				if (isset($subscription_types))
				{
					echo '<br/>';

					foreach ($subscription_types->result_array() as $subscription_type)
					{
						$id = 'sendto_subscribers_'.$subscription_type['typeid'];
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								'.ucfirst($subscription_type['type']).' subscribers
							</div>';
						$id = 'sendto_subscribers_'.$subscription_type['typeid'].'_expired';
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								Expired '.strtolower($subscription_type['type']).' subscribers
							</div>';

					}
				?>

				<div class="forminput">
					<label>&nbsp;</label>
					<input name="sendto_subscribers_none" id="sendto_subscribers_none" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('sendto_subscribers_none', '1'); ?>/>
					Non subscribers
				</div>

				<?php
				}
				?>

				<br/>

				<div class="forminput">
					<input type="submit" value="Compose email" name="sendmail" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>