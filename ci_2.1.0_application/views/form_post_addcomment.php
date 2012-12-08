<form action="<?php echo base_url();?><?php echo $post['urlname'];?>/addcomment" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend>Add a comment</legend>

		<div id="addcomment" class="panel alwaysopen"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<?php
			if (!$this->session->userdata('signedon'))
			{
			?>

			<div class="formnotes full">
				<p>You are not signed on, but you can comment. Your comment will need to be approved. To skip the wait for approval <a href="<?php echo base_url();?>signon/<?php echo $this->uri->segment(2);?>/addcomment/">sign on</a>.

				<?php if ($this->config->item('dmcb_signon_facebook') == "true") { ?>
				<span id="fb-root"></span>
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

				<br/><br/>
				<fb:login-button length="long" scope="email"></fb:login-button>

				<?php } ?>

				</p>

			</div>

			<input name="information" type="text" class="hidden" />

			<?php
				if (form_error('email') != "<p class=\"error\">This email address is registered. Enter your account password to add your comment.</p>" && form_error('email') != "<p class=\"error\">Your password is incorrect.</p>")
				{
			?>

			<div class="forminput">
				<label>Display name</label>
				<input name="displayname" type="text" class="text" value="<?php echo set_value('displayname'); ?>"/>
				<?php echo form_error('displayname'); ?>
			</div>

			<?php
				}
			?>

			<div class="forminput">
				<label>Email address</label>
				<input name="email" type="text" class="text" value="<?php echo set_value('email'); ?>"/>
				<?php echo form_error('email'); ?>
			</div>

			<?php
				if (form_error('email') == "<p class=\"error\">This email address is registered. Enter your account password to add your comment.</p>" || form_error('email') == "<p class=\"error\">Your password is incorrect.</p>")
				{
			?>

			<div class="forminput">
				<label>Password</label>
				<input name="password" type="password" class="text" />
				<?php echo form_error('password'); ?>
			</div>

			<?php
				}
			}
			
			if ($captcha)
			{
			?>
			<div class="forminput">
				<label>Captcha code</label>
				<img src="<?php echo base_url();?><?php echo $post['urlname'];?>/captcha" alt="Captcha" id="captcha">
			</div>
			
			<div class="forminput">
				<label>Enter code</label>
				<input name="captcha" type="text" class="text"/>
				<?php echo form_error('captcha'); ?>
			</div>
			<?php
			}
			?>
			
			<div class="forminput">
				<label><?php if (!$this->session->userdata('signedon')) echo 'Comment'; else echo '&nbsp;'; ?></label>
				<textarea name="comment" rows="" cols=""><?php echo set_value('comment'); ?></textarea>
				<?php echo form_error('comment'); ?>
			</div>

			<div class="forminput">
				<input type="submit" value="Add comment" name="addcomment" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
