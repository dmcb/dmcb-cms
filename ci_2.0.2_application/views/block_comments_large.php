<blockquote>
	<?php
		echo character_limiter(preg_replace('/<br\/>/',' ',$comment['content']), 200).'<br/>-';
		echo $comment['displayname'];
		echo ', <a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.$comment['post']['title'].'</a>';
	?>
</blockquote>