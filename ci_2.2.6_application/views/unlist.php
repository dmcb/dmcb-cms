<div class="fullcolumn">
	<p>Please confirm that you wish to unsubscribe to the mailing list</p>
	<div class="seperator">&nbsp;</div>
	<form action="<?php echo current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
<?php echo validation_errors(); ?>
				<div class="forminput">
					<input type="submit" value="Unsubscribe" name="unsubscribe" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>
