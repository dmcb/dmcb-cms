<form action="<?php echo base_url();?>account/<?php echo $user['urlname'];?>/editname" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend>Rename <?php echo $person_edited;?> display name</legend>
				
		<div id="editname" class="panel alwaysopen"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="forminput">
				<label>Display name</label>
				<input name="displayname" type="text" maxlength="30" class="text" value="<?php echo set_value('displayname', $user['displayname']); ?>"/>
				<?php echo form_error('displayname'); ?>
			</div>
			
			<div class="forminput">
				<input type="submit" value="Rename" name="rename" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			
		</div></div>
	</fieldset>
</form>
