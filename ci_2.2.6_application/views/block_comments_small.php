<span>
	<?php
		echo $comment['displayname'].' writes:<br/>';
		echo '"<a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.character_limiter(preg_replace('/<br\/>/',' ',$comment['content']), 60).'"</a><br/>';
	?>
</span>
<br/>
