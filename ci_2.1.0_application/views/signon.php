<div class="leftcolumn">
	<?php if (isset($facebook)) echo $facebook; ?>
</div>

<div class="centercolumnlarge">
	<?php
		if ($signon_message != NULL) echo '<h4>'.$signon_message.'</h4><br/>';
		if ($signoff_message != NULL) echo '<h4>'.$signoff_message.'</h4><br/>';

		if (isset($authenticate)) echo $authenticate;
		if (isset($signup)) echo $signup;
		if (isset($recover)) echo $recover;
	?>
</div>