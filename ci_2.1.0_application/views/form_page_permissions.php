<form class="collapsible" action="<?=base_url();?><?=$page['urlname'];?>/permissions" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('permissions');">Manage permissions</a></legend>

		<div id="permissions" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<?php
				for ($i=0; $i<sizeof($userlist); $i++)
				{
					if (sizeof($userlist[$i]) > 0)
					{
						$userrole = $roles->row_array($i);
						echo '<div class="forminput"><label><td colspan="3">Page '.$userrole['role'].'s</label>';
						echo '<table>';
						for ($j=0; $j<sizeof($userlist[$i]); $j++)
						{
							echo '<tr class="data"><td>'.$userlist[$i][$j]['displayname'].'</td>';

							echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
							foreach ($roles->result_array() as $role)
							{
								if ($role['roleid'] == $userlist[$i][$j]['roleid'])
								{
									echo '<option value="'.base_url().$page['urlname'].'/permissions/set_role/'.$userlist[$i][$j]['userid'].'/'.$role['roleid'].'" selected="selected">'.$role['role'].'</option>';
								}
								else
								{
									echo '<option value="'.base_url().$page['urlname'].'/permissions/set_role/'.$userlist[$i][$j]['userid'].'/'.$role['roleid'].'">'.$role['role'].'</option>';
								}
							}
							echo '</select></td>';

							echo '<td><a href="'.base_url().$page['urlname'].'/permissions/delete/'.$userlist[$i][$j]['userid'].'">Remove permission</a></td>';
						}
						echo '</table></div><br/>';
					}
				}
			?>

			<div class="forminput">
				<label>User display name</label>
				<input name="displayname" id="displayname" type="text" class="text" maxlength="20" value="<?php echo set_value('displayname'); ?>"/>
				<div class="autocomplete" id="autocomplete" style="display: none; position:relative;"></div>
				<?php echo form_error('displayname'); ?>
			</div>

			<?php
			if ($this->config->item('csrf_protection')) $csrf = "parameters: '".$this->security->get_csrf_token_name()."=".$this->security->get_csrf_hash()."',";
			$this->javascript['autocomplete'] = array('weight' => 1, 'javascript' => "
				new Ajax.Autocompleter('displayname','autocomplete','".base_url()."autocomplete/user', {
					".$csrf."
					minChars: 2,
					frequency: 0.1
				});");
			?>

			<div class="formnotes">
				<p>If you are adding a new user to the site, in addition to their display name, enter their email address below. Otherwise ignore the email field and enter an existing user's display name.</p>
			</div>

			<div class="forminput">
				<label>Email address</label>
				<input name="email" type="text" class="text" maxlength="50" value="<?php echo set_value('email'); ?>"/>
				<?php echo form_error('email'); ?>
			</div>

			<div class="forminput">
				<label>Permission level</label>
				<select name="role">
				<?php
					foreach ($roles->result_array() as $role)
					{
						echo '<option value="'.$role['roleid'].'" '.set_select('role', $role['roleid']).' >'.$role['role'].'</option>';
					}
				?>
				</select>
			</div>

			<div class="forminput">
				<input type="submit" value="Set" name="set" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>