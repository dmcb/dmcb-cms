<div class="leftcolumn">
	<?php
	if (sizeof($priveleges) > 0)
	{
		echo '<h2>Your privileges</h2><ul>';
		foreach ($priveleges as $privelege)
		{
			echo '<li>';
			if ($privelege['on'] == "site")
			{
				echo 'Site-wide '.$privelege['role'];
			}
			else
			{
				echo ucfirst($privelege['role']).' on <a href="'.base_url().$privelege[$privelege['on']]['urlname'].'">'.$privelege[$privelege['on']]['title'].'</a>';
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
