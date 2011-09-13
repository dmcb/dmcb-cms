<div class="fullcolumn">
	<p>Please enter a new file name for <?php echo $attachment['filename'].'.'.$attachment['extension'];?>.  You cannot change the extension.</p>
	<div class="seperator">&nbsp;</div>
	<form action="<?=current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>File name</label>
					<input name="filename" type="text" class="text" maxlength="200" value="<?php echo set_value('filename', $attachment['filename']); ?>"/>
					<?php echo form_error('filename'); ?>
				</div>
				
				<div class="forminput">
					<input type="submit" value="Rename" name="rename" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>