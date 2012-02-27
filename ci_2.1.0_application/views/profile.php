<div class="leftcolumn">
	<?php echo generate_profile_picture("large", $user) ?>

	<?php if (isset($tweets))
		{
			echo '<br/><br/><h2>Latest tweets</h2>';
			echo $tweets;
			echo '<br/><a href="http://twitter.com/'.$user['twitter'].'">Follow '.$user['displayname'].' on twitter ></a>';
		}
	?>
</div>

<div class="centercolumnlarge">
	<h2><?=$title;?></h2>

	<?php if (isset($user['profile'])) echo $user['profile']; ?>

	<?php if (isset($posts) && !preg_match('/There are no posts at the moment./', $posts))
		{
			echo '<br/><br/><h2>Latest posts</h2>';
			echo $posts;
			echo '<br/><br/>';
		}
	?>

	<?php if (isset($messages))
		{
			echo $messages;
		}
	?>
</div>

<?php
	if (isset($edit_name) || isset($edit_profile) || isset($edit_settings) || isset($attachments) || isset($edit_heldbackposts) || isset($edit_heldbackcomments) || isset($edit_drafts) || isset($add_post))
	{
		echo '<div class="spacer">&nbsp</div>';
		echo '<div class="admin">';

		if (isset($edit_name)) echo $edit_name;
		if (isset($edit_profile)) echo $edit_profile;
		if (isset($edit_settings)) echo $edit_settings;
		if (isset($attachments)) echo $attachments;
		if (isset($edit_heldbackposts)) echo $edit_heldbackposts;
		if (isset($edit_heldbackcomments)) echo $edit_heldbackcomments;
		if (isset($edit_drafts)) echo $edit_drafts;
		if (isset($add_post)) echo $add_post;

		echo '</div>';
	}
?>