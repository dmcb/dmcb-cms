<div class="post listing">
	<?php
		if (isset($post['image']['urlpath']))
		{
			echo '<a href="'.base_url().$post['urlname'].'"><img src="'.base_url().size_image($post['image']['urlpath'],120,80).'" alt="'.$post['title'].'" /></a>';
		}

		echo '<h3>'.date("F j, Y", strtotime($post['date'])).'</h3>';
		echo '<p><a href="'.base_url().$post['urlname'].'">'.$post['title'].'</a>';

		if ($post['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
		{
			echo '<span class="restricted">Subscription required</span>';
		}

		if ($post['enabledcomments'])
		{
			if ($post['commentcount'] == 1) echo '<br/>'.$post['commentcount'].' comment';
			else if ($post['commentcount'] > 0) echo '<br/>'.$post['commentcount'].' comments';
		}
	?>
	</p>
</div>