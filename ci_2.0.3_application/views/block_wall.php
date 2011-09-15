<?php
	if (strlen($wall['content']) > 0)
	{
		echo '<blockquote>'.$wall['content'].'<br/>-'.$wall['name'].', '.$wall['city'].'</blockquote><br/>';
	}
?>