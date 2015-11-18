 <?php 
	if (isset($comments))
	{
		echo '<div class="comments" id="comment"><h2>Comments</h2>';
	/*
		if ($comment_total == 1)
		{
			echo '<h2>'.$comment_total.' comment';
		}
		else 
		{
			echo '<h2><a name="comments" id="comments"></a>'.$comment_total.' comments';
		}
		
		if ($this->pagination->create_links() != NULL) 
		{
			if ($offset+1 == $comment_max)
				echo ' (viewing the last one)';
			else
				echo ' (viewing '.($offset+1).' through '.$comment_max.')';
		}
		echo ':</h2>'; */
		echo $comments.'</div>';
	}
	if (isset($add_comment)) 
	{
		echo '<div class="addcomment">'.$add_comment.'</div>'; 
	}
?>
