<form action="<?php echo base_url();?>profile/<?php echo $user['urlname'];?>/message" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend>Send <?php echo $user['displayname'];?> a message</legend>
		
		<div id="message" class="panel alwaysopen"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="forminput">
				<label>Message</label>
				<textarea name="content" rows="" cols=""><?php echo set_value('content'); ?></textarea>
				<?php echo form_error('content'); ?>
			</div>
			
			<div class="forminput">
				<input type="submit" value="Send message" name="send" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
