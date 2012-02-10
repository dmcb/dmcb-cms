<form action="<?=base_url();?>signon/authenticate<?=$redirection;?>" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend>Sign on</legend>

		<div id="signon" class="panel alwaysopen"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			<div class="forminput">
				<label id="emaillabel">Email address</label>
				<input name="email" id="email" type="text" class="text" value="<?php echo set_value('email'); ?>"/>
				<?php echo form_error('email'); ?>
			</div>

			<div class="forminput">
				<label id="passwordlabel">Password</label>
				<input name="password" id="password" type="password" class="text" />
			</div>

			<div class="forminput">
				<label id="remembermelabel">Remember me on this computer</label>
				<input name="rememberme" id="rememberme" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('rememberme', '1'); ?>/>
			</div>

			<br/>

			<div class="forminput">
				<input type="submit" value="Sign on" name="signon" id="signon" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				<?php echo form_error('password'); ?>
			</div>
		</div></div>
	</fieldset>
</form>