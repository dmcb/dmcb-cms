<div class="post preview">
	<h3><?php echo $post['title'];?></h3>

	<h5>
	<?php
		echo date("F jS, Y", strtotime($post['date']));
		if (isset($post['parent']))
		{
			echo ' from <a href="'.base_url().$post['parent']['urlname'].'">'.$post['parent']['title'].'</a>';
		}
		$j=0;
		foreach ($post['categories']->result_array() as $category)
		{
			if ($j==0)
			{
				echo ' in ';
			}
			$j++;
			echo '<a href="'.base_url().$currentpage.'/category/'.$category['urlname'].'">'.$category['name'].'</a>';
			if ($j!=$post['categories']->num_rows())
			{
				echo ', ';
			}
		}
		if (sizeof($post['contributorslist']) > 0)
		{
			echo ' featuring ';
			$i = 0;
			foreach ($post['contributorslist'] as $contributor)
			{
				if ($i < sizeof($post['contributorslist']) && $i != 0)
				{
					echo ' & ';
				}

				if ($contributor['enabledprofile'])
				{
					echo '<a href="'.base_url().'profile/'.$contributor['urlname'].'">'.$contributor['displayname'].'</a>';
				}
				else
				{
					echo $contributor['displayname'];
				}

				$i++;
			}
		}
		if ($post['canedit'])
		{
			echo ' | <a href="'.base_url().$post['urlname'].'/editpost">Edit post</a>';
		}
	?>
	</h5>

	<br/>

	<?php
	if (isset($post['image']['urlpath']))
	{
		echo '<a href="'.base_url().$post['urlname'].'"><img src="'.base_url().size_image($post['image']['urlpath'],280,160).'" alt="'.$post['title'].'" /></a>';
	}

	$summary = explode("<!-- pagebreak -->",$post['content']);

	echo character_limiter(strip_tags(preg_replace("/<img[^>]+\>/i", "", $summary[0])),300);
	echo '</p>';

	$subscription = "";
	if ($post['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
	{
		$subscription = ' <span class="restricted">subscription required</span>';
	}

	if ($post['commentcount'] == 1 && $post['enabledcomments']) echo '<a href="'.base_url().$post['urlname'].'">Continue reading ('.$post['commentcount'].' comment)</a>'.$subscription.' > <br/>';
	else if ($post['commentcount'] > 0 && $post['enabledcomments']) echo '<a href="'.base_url().$post['urlname'].'">Continue reading ('.$post['commentcount'].' comments)</a>'.$subscription.' > <br/>';
	else echo '<p><a href="'.base_url().$post['urlname'].'">Continue reading</a>'.$subscription.' > <br/>';

	if ($post['enabledcomments'])
	{
		echo '<a href="'.base_url().$post['urlname'].'/addcomment">Add comment ></a>';
	}
	?>

	</p>
</div>
<?php
	if ($current != $count)
	{
		echo '<hr/>';
	}
?>
