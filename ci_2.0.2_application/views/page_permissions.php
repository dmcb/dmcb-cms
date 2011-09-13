<div class="fullcolumn">
	<p>Please set the permissions for <?php echo $item['title'];?>.</p>
	<form action="<?=current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="formnotes full">
					<p>Restrict the page being viewed only by the user roles you choose.<br/>If none are selected, all users may view the page.</p>
					<p>For pages that link externally and menu place holders, this setting only affects the visibility of the page within the menu.</p>
				</div>
				
				<?php
					$i=0;
					foreach ($roles as $role)
					{
						if ($i == 0)
						{
							echo '<div class="forminput"><label>Limit accessibility to</label>';
						}
						else
						{
							echo '<div class="forminput"><label>&nbsp;</label>';
						}
						$default = FALSE; 
						if (isset($item['protection'][$role['roleid']]) && $item['protection'][$role['roleid']] == 1) 
						{
							$default = TRUE;
						}
						echo '<input name="'.$role['rolefield'].'" id="'.$role['rolefield'].'" type="checkbox" class="checkbox" value="1" '.set_checkbox($role['rolefield'], '1', $default).'/>';
						echo $role['role'].'s';
						echo '</div>';
						$i++;
					}
				?>
				
				<br />
				
				<div class="forminput">
					<input type="submit" value="Save permissions" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>