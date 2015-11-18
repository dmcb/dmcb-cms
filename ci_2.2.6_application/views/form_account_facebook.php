	<form>
		<fieldset>
			<legend>Facebook connect</legend>

			<div class="panel alwaysopen"><div>
				<div class="formnotes">
					<p>
					<?php
					if ($user['facebook_uid'] == NULL)
					{
						echo 'You do not have Facebook associated with this account, to do so, set your email address above to your email address for Facebook, and sign on to the site using Facebook.';
					}
					else
					{
						echo 'You have associated Facebook with your account. <a href="'.base_url().'account/'.$user['urlname'].'/removefacebook" onclick="return dmcb.confirmation(\'Are you sure you wish to remove Facebook association with your account?\')">Remove association</a>.';
					}
					?>
					</p>
				</div>
			</div></div>
		</fieldset>
	</form>
