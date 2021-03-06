<form action="<?php echo base_url();?>account/<?php echo $user['urlname'];?>/pagepermissions" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend>Set <?php echo $person_edited;?> page permissions</legend>

		<div id="pagepermissions" class="panel alwaysopen"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<?php
				if (sizeof($privileges['page']) > 0)
				{
					echo '<div class="forminput"><label><td colspan="3">Page permissions</label>';
					echo '<table>';
					foreach ($privileges['page'] as $privilege)
					{
						echo '<tr class="data"><td>'.$privilege['page']['title'].'</td>';

						echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
						foreach ($roles->result_array() as $role)
						{
							if ($role['roleid'] == $privilege['role'])
							{
								echo '<option value="'.base_url().'account/'.$user['urlname'].'/pagepermissions/set_role/'.$privilege['page']['pageid'].'/'.$role['roleid'].'" selected="selected">'.$role['role'].'</option>';
							}
							else
							{
								echo '<option value="'.base_url().'account/'.$user['urlname'].'/pagepermissions/set_role/'.$privilege['page']['pageid'].'/'.$role['roleid'].'">'.$role['role'].'</option>';
							}
						}
						echo '</select></td>';

						echo '<td><a href="'.base_url().'account/'.$user['urlname'].'/pagepermissions/delete/'.$privilege['page']['pageid'].'">Remove</a></td>';
					}
					echo '</table></div><br/>';
				}
			?>

			<div class="forminput">
				<label>Pages</label>
				<div class="list">
					<?php
					foreach ($pages as $menu => $menu_section)
					{
						foreach($menu_section as $page) 
						{
							echo '<label>';
							for ($i=0; $i<$page['level']; $i++)
							{
								echo '&nbsp;&nbsp;&nbsp;';	
							}
							echo '<input type="checkbox" name="pages[]" value="'.$page['pageid'].'"> '.$page['title'].'</label>';
						}
					}
					?>
				</div>
				<?php echo form_error('pages[]'); ?>
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
