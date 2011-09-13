<form class="collapsible" action="<?=base_url();?>profile/<?=$this->uri->segment(2);?>/editname" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editname');">Rename your display name</a></legend>
				
		<div id="editname" class="panel"><div>
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