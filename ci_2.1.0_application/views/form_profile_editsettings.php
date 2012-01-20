<form class="collapsible" action="<?=base_url();?>profile/<?=$this->uri->segment(2);?>/editsettings" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editsettings');">Twitter settings</a></legend>
												
		<div id="editsettings" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="formnotes">
				<p><a href="http://www.twitter.com">Twittering?</a>  Enter your account name, and your tweets will be shown on your profile (unless updates are protected in your twitter settings).</p>
			</div>
							
			<div class="forminput">
				<label>Twitter account</label>
				<input name="twitter" type="text" maxlength="30" class="text" value="<?php echo set_value('posturlname', $user['twitter']); ?>"/>
				<?php echo form_error('twitter'); ?>
			</div>
							
			<div class="forminput">
				<input type="submit" value="Save settings" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>