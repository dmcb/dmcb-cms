<p class="twitter">
<?php
echo '<span>Update on '.$tweet->get_date("F jS, Y").', by ';
if ($user != NULL) 
{
	echo 'our very own ';
	if ($user['enabledprofile'])
	{
		echo '<a href="'.base_url().'profile/'.$user['urlname'].'">'.$user['displayname'].'</a>';
	}
	else
	{
		echo $user['displayname'];
	}
}
else
{
	echo $author;
}
echo '</span><br/>';
echo '<a href="'.$tweet->get_author()->get_link().'">'.preg_replace('/\@([a-zA-Z0-9_]+)/','<a href="http://twitter.com/'.strtolower('\1').'">@\1</a><a href="'.$tweet->get_author()->get_link().'">',preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', '</a><a href="\0">\0</a><a href="'.$tweet->get_author()->get_link().'">', $tweet->get_title())).'</a><br/>';
?>
</p><br/>

