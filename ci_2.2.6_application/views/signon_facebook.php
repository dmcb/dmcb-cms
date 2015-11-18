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
