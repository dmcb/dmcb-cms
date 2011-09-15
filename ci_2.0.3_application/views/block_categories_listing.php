	<li><a href="<?=base_url().$category['url'];?>"><?=$category['name'];?> 
	<?php if (isset($category['count'])) echo '('.$category['count'].')';?>
	</a></li>
	