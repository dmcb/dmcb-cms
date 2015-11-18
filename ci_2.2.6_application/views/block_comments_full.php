<div class="comment full">
<?php
	echo generate_profile_picture("small", $comment['user'], $comment['displayname'], $comment['email']);
	echo '<blockquote class="comment" id="comment'.$comment['commentid'].'">'.$comment['content'].'</blockquote>';
	echo '<h6>Comment made on '.date('F jS, Y \a\t g:i a', strtotime($comment['date'])).' by ';
	if ($comment['user'] != NULL && $comment['user']['enabledprofile'])
	{
		echo '<a href="'.base_url().'profile/'.$comment['user']['urlname'].'">'.$comment['user']['displayname'].'</a>';
	}
	else
	{
		echo $comment['displayname'];
	}
	echo '</h6>';

	// Admin options
	if ((isset($this->can_report_comment) && $this->can_report_comment) || (isset($this->can_delete_comment) && $this->can_delete_comment) || (isset($this->can_holdback_comment) && $this->can_holdback_comment))
	{
		echo '<h6>';
		if (isset($this->can_report_comment) && $this->can_report_comment)
		{
			echo '<a href="javascript:dmcb.linksubmit(\'commentform'.$comment['commentid'].'\');">Report abusive comment</a>';
		}
		if ((isset($this->can_report_comment) && $this->can_report_comment) && (isset($this->can_holdback_comment) && $this->can_holdback_comment))
		{
			echo ' | ';
		}
		if (isset($this->can_holdback_comment) && $this->can_holdback_comment)
		{
			echo '<a href="'.base_url().'manage_activity/commentreturn/holdback/'.$comment['commentid'].'">Hold back</a>';
		}
		if (((isset($this->can_report_comment) && $this->can_report_comment) || (isset($this->can_holdback_comment) && $this->can_holdback_comment)) && (isset($this->can_delete_comment) && $this->can_delete_comment))	
		{
			echo ' | ';
		}
		if (isset($this->can_delete_comment) && $this->can_delete_comment)
		{
			echo '<a href="'.base_url().$comment['post']['urlname'].'/deletecomment/'.$comment['commentid'].'">Delete</a>';
		}
		echo '</h6>';
	}
?>

	<form action="<?php echo base_url().$comment['post']['urlname'].'/reportcomment/'.$comment['commentid'];?>" method="post" id="commentform<?php echo $comment['commentid'];?>">
		<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
		<input name="information" type="text" class="hidden" />
	</form>
</div>
