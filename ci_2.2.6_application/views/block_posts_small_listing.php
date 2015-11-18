<div class="listing">
	<?php
		echo date("F j, Y", strtotime($post['date'])).': <a href="'.base_url().$post['urlname'].'">'.$post['title'].'</a>';
		
		if ($post['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe')) 
		{
			echo '<span class="restricted">Subscription required</span>';
		}
		
		if ($post['enabledcomments'])
		{
			if ($post['commentcount'] == 1) echo ', '.$post['commentcount'].' comment';
			else if ($post['commentcount'] > 0) echo ', '.$post['commentcount'].' comments';
		}
	?>
</div>
