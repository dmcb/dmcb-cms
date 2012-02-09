<div class="leftcolumn">
	<?php if ($this->config->item('dmcb_signon_facebook') == "true") { ?>
	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			FB.init({
				appId   : '<?php echo $this->config->item('dmcb_facebook_app_id'); ?>',
				oauth   : true,
				authResponse : <?php echo json_encode($session); ?>, // don't refetch the session when PHP already has it
				status  : true, // check login status
				cookie  : true, // enable cookies to allow the server to access the session
				xfbml   : true // parse XFBML
			});

			// whenever the user logs in, we refresh the page
			FB.Event.subscribe('auth.login', function() {
				window.location.reload();
			});
			FB.Event.subscribe('auth.logout', function(response) {
				window.location.reload();
			});
		};

		(function() {
			var e = document.createElement('script');
			e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
			e.async = true;
			document.getElementById('fb-root').appendChild(e);
		}());
	</script>

	<h2>Facebook users</h2>
	<p>Ignore the sign up and log in with Facebook!</p>
    <div>
		<fb:login-button size="large" length="long" scope="email"></fb:login-button>
    </div>
	<?php } ?>
</div>

<div class="centercolumnlarge">
	<?php
	if ($redirection != NULL) echo '<h4>This page requires that you sign on.</h4><br/>';
	if ($signoff_message != NULL) echo '<h4>'.$signoff_message.'</h4><br/>';
	?>

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

	<?php
	if ($this->config->item('dmcb_guest_signup'))
	{
	?>
	<form class="collapsible" action="<?=base_url();?>signon/signup" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('signup');">Sign up for <?=$this->config->item('dmcb_title');?></a></legend>

			<div id="signup" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>Choose an email account you have access to, an email will be sent to complete your registration.<br/>
					Your email address is kept private.</p>
				</div>

				<div class="forminput">
					<label>Email address</label>
					<input name="signup_email" type="text" class="text" value="<?php echo set_value('signup_email'); ?>"/>
					<?php echo form_error('signup_email'); ?>
				</div>

				<div class="forminput">
					<label>Display name</label>
					<input name="signup_display" type="text" class="text" value="<?php echo set_value('signup_display'); ?>"/>
					<?php echo form_error('signup_display'); ?>
				</div>

				<div class="forminput">
					<label>Password</label>
					<input name="signup_password" type="password" class="text" />
					<?php echo form_error('signup_password'); ?>
				</div>

				<div class="forminput">
					<label>Confirm password</label>
					<input name="signup_password_confirm" type="password" class="text" />
					<?php echo form_error('signup_password_confirm'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Sign up" name="signup" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
	<?php
	}
	?>

	<form class="collapsible" action="<?=base_url();?>signon/recover" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('recover');">Recover your password</a></legend>

			<div id="recover" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>A new temporary password will be sent to your email account</p>
				</div>

				<div class="forminput">
					<label>Email address</label>
					<input name="emailforgot" type="text" class="text" value="<?php echo set_value('email_forgot'); ?>"/>
					<?php echo form_error('emailforgot'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Recover" name="recover" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>