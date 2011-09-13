<h6>Update on <?=$tweet->get_date("F jS, Y"); ?></h6>
<p class="twitter">
<?php
echo preg_replace('/\@([a-zA-Z0-9_]+)/','<a href="http://twitter.com/'.strtolower('\1').'">@\1</a>',preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', '<a href="\0">\0</a>', $tweet->get_title())).'</a>';
?>
</p>
<br/>
