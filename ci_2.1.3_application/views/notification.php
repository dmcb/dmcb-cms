<div class="fullcolumn">
	<h2>Add a note for the user</h2>
	<p>The user can be notified of the <?php echo $change;?> or you can choose not to send the user any notification at all.</p>
	<div class="spacer">&nbsp;</div>
	<form action="<?php echo current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<label>Optional note to user</label>
				<textarea name="note" rows="" cols=""></textarea>
				
				<div class="forminput">
					<input type="submit" value="Send notification (recommended)" name="send" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
				<div class="forminput">
					<input type="submit" value="Dont send user any notification" name="nosend" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>
