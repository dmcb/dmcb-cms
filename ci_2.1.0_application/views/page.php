<?php 
	if (isset($pagecontent)) echo $pagecontent;

	if (isset($edit_page) || isset($edit_theme) || isset($set_template) || isset($add_templates) || isset($edit_drafts) || isset($add_post) || isset($add_page) || isset($attachments) || isset($edit_blocks) || isset($permissions))
	{
		echo '<div class="spacer">&nbsp</div>';
		echo '<div class="admin">';
		
		if (isset($edit_page)) echo $edit_page;
		if (isset($edit_theme)) echo $edit_theme;
		if (isset($set_template)) echo $set_template;
		if (isset($add_templates)) echo $add_templates;
		if (isset($edit_drafts)) echo $edit_drafts;
		if (isset($add_post)) echo $add_post;
		if (isset($add_page)) echo $add_page;
		if (isset($attachments)) echo $attachments;
		if (isset($edit_blocks)) echo $edit_blocks;
		if (isset($permissions)) echo $permissions;
		
		echo '</div>';
	}
?>
