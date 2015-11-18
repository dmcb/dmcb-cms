<div class="leftcolumn">
	<?php
		if (isset($account_report)) echo $account_report;
	?>
</div>

<div class="centercolumnlarge">
	<?php
		if (isset($edit_name)) echo $edit_name;
		if (isset($change_password)) echo $change_password;
		if (isset($update_email)) echo $update_email;
		if (isset($manage_page_permissions)) echo $manage_page_permissions;
		if (isset($facebook)) echo $facebook;
		if (isset($message_settings)) echo $message_settings;
	?>
</div>
