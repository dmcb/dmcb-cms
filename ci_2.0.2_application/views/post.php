<?php 
	if (isset($postcontent)) echo $postcontent;

	if (isset($edit_post) || isset($edit_css) || isset($edit_event) || isset($tag_user) || isset($attachments) || isset($permissions))
	{
		echo '<div class="spacer">&nbsp;</div>';
		echo '<div class="admin">';
		
		if (isset($edit_post)) echo $edit_post;
		if (isset($edit_css)) echo $edit_css;
		if (isset($edit_event)) echo $edit_event;
		if (isset($tag_user)) echo $tag_user;
		if (isset($attachments)) echo $attachments;
		if (isset($permissions)) echo $permissions;
	
		echo '</div>';
	}
?>
