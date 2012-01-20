<div class="fullcolumn">
	<table>

	<?php
		foreach ($functions as $key => $value)
		{
			echo '<tr><td colspan="'.(sizeof($roles)+2).'"><h2>'.$key.' functions</h2></td>';
			echo '<tr class="data"><td>&nbsp;</td>';
			foreach ($roles as $role)
			{
				echo '<td>'.$role['role'].'</td>';
			}
			echo '<td>&nbsp;</td></tr>';
			foreach ($value as $function)
			{
				echo '<tr class="data"><td>';
				for ($level=0; $level<$function['level']; $level++)
				{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo $function['name'].'</td>';
				$i=0;
				foreach ($function['priveleges'] as $privelege)
				{
					echo '<td>';
					if (($function['guestpossible'] && $roles[$i]['role'] == "guest") || ($function['ownerpossible'] && $roles[$i]['role'] == "owner") || ($roles[$i]['role'] != "guest" && $roles[$i]['role'] != "owner"))
					{
						$functionfield = $function['function'].'_'.$roles[$i]['roleid'];
						echo '<input name="'.$functionfield.'" id="'.$functionfield.'" type="checkbox" class="checkbox" onclick="dmcb.gotovalue(this)" value="'.base_url().'manage_security/setprivelege/'.$function['functionid'].'/'.$roles[$i]['roleid'].'" ';
						if ($privelege)
						{
							echo 'checked="checked"';
						}
						echo '/>';
					}
					else
					{
						echo '<input type="checkbox" class="checkbox" disabled />';
					}
					echo '</td>';
					$i++;
				}
				echo '<td><a href="'.base_url().'manage_security/disablefunction/'.$function['functionid'].'">Disable</td>';
				echo '</tr>';
			}
			echo '</tr><tr><td colspan="'.(sizeof($roles)+2).'">&nbsp;</td></tr>';
		}
	?>
	</table>
	
	<div class="spacer">&nbsp;</div>

	<form class="collapsible" action="<?=base_url();?>manage_security/enablefunction" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('enablefunction');">Enable a function</a></legend>
			
			<div id="enablefunction" class="panel"><div>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="formnotes">
					<p>Any functions developed for this site that aren't already enabled can be done so here.</p>
				</div>
				
				<div class="forminput">
					<label>Function name</label>
					<select onchange="dmcb.goto(this)">
						<option value="">Choose a function</option>
						<?php
							foreach ($availablefunctions as $key => $value)
							{
								foreach ($value as $function)
								{
									echo '<option value="'.base_url().'manage_security/enablefunction/'.$function['functionid'].'">'.$function['controller'].': '.$function['name'].'</option>';
								}
							}
						?>
					</select>
				</div>

			</div></div>
		</fieldset>
	</form>

	<form class="collapsible" action="<?=base_url();?>manage_security/addrole" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('addrole');">Manage roles</a></legend>
			
			<div id="addrole" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="forminput">
					<label>Custom roles</label>
					<table>
						<?php
						if (sizeof($customroles) == 0)
						{
							echo '<tr><td>No custom roles have been added.</td></tr>';
						}
						else
						{
							foreach ($customroles as $customrole) 
							{
								echo '<tr class="data"><td>'.ucwords($customrole['role']).'</td><td><a href="'.base_url().'manage_security/deleterole/'.$customrole['roleid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this role?\')">Delete</a></td></tr>';
							}
						}
						?>
					</table>
				</div>
				
				<br />
				
				<div class="forminput">
					<label>Role title</label>
					<input name="role" type="text" class="text" maxlength="50" value="<?php echo set_value('role'); ?>"/>
					<?php echo form_error('role'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Add a new role" name="addrole" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>