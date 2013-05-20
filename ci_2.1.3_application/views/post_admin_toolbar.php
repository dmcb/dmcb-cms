<?php
	if ($post['featured'] == '1')
	{
		echo '<a href="'.base_url().'manage_activity/postreturn/unfeature/'.$post['postid'].'">Unfeature this post</a>';
	}
	else
	{
		echo '<a href="'.base_url().'manage_activity/postreturn/feature/'.$post['postid'].'">Feature this post</a>';
	}
	if (isset($author['userid']))
	{
		if ($post['featured'] == '-1')
		{
			echo ' | <a href="'.base_url().'manage_activity/postreturn/approve/'.$post['postid'].'">Approve</a>';
		}
		else
		{
			echo ' | <a href="'.base_url().'manage_activity/postreturn/holdback/'.$post['postid'].'">Hold back</a>';
		}
	}
	echo ' | <a href="'.base_url().'manage_activity/postreturn/delete/'.$post['postid'].'">Delete</a>';
?>
