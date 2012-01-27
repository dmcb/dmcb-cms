<div class="post featured">
	<h2><?=$post['title'];?></h2>

	<?php
	if (isset($post['image']['urlpath']))
	{
		echo '<a href="'.base_url().$post['urlname'].'"><img src="'.base_url().size_image($post['image']['urlpath'],640).'" alt="'.$post['title'].'" /></a>';
	}

	$subscription = "";
	if ($post['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
	{
		$subscription = '<span class="restricted">(Subscription only)</span> ';
	}

	$summary = explode("<!-- pagebreak -->", $post['content']);
	echo '<h5>'.date("F jS, Y", strtotime($post['date'])).'</h5>';
	echo '<p>'.character_limiter(strip_tags(preg_replace("/<img[^>]+\>/i", "", $summary[0])),300);
	echo '<a href="'.base_url().$post['urlname'].'"> Continue reading this article '.$subscription.'></a></p>';
	?>
</div>