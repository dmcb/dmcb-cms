<form class="collapsible" action="<?php echo base_url();?>profile/<?php echo $this->uri->segment(2);?>/editgoogle" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editgoogle');">Google settings</a></legend>
												
		<div id="editgoogle" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="formnotes">
				<p><a href="https://plus.google.com">Using Google Plus?</a> Enter your numerical ID so that Google search results for your contributed content will connect to your Google Plus account.</p>
			</div>
							
			<div class="forminput">
				<label>Google Plus account</label>
				<input name="google" type="text" maxlength="25" class="text" value="<?php echo set_value('google', $user['google']); ?>"/>
				<?php echo form_error('google'); ?>
			</div>
							
			<div class="forminput">
				<input type="submit" value="Save settings" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
