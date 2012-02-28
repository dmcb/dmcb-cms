<div class="leftcolumn">
	<?php
	if (sizeof($privileges) > 0)
	{
		echo '<h2>Your privileges</h2><ul>';
		foreach ($privileges as $privilege)
		{
			echo '<li>';
			if ($privilege['on'] == "site")
			{
				echo 'Site-wide '.$privilege['role'];
			}
			else
			{
				echo ucfirst($privilege['role']).' on <a href="'.base_url().$privilege[$privilege['on']]['urlname'].'">'.$privilege[$privilege['on']]['title'].'</a>';
			}
			echo '</li>';
		}
		echo '</ul>';
	}
	?>
	&nbsp;
</div>

<div class="centercolumnlarge">
	<?php
		if (isset($change_password)) echo $change_password;
		if (isset($update_email)) echo $update_email;
		if (isset($facebook)) echo $facebook;
		if (isset($message_settings)) echo $message_settings;
	?>
</div>
