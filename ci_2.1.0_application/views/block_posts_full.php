<div class="post full">
	<h2><?php echo $post['title'];?></h2>

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

	<?php

	$summary = explode("<!-- pagebreak -->",$post['content']);

	echo '<br/>'.$summary[0];
	echo '</p>';

	$subscription = "";
	if ($post['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
	{
		$subscription = ' <span class="restricted">Subscription required</span>';
	}

	if ($post['commentcount'] == 1 && isset($summary[1]) && $post['enabledcomments']) echo '<a href="'.base_url().$post['urlname'].'">Continue reading ('.$post['commentcount'].' comment)</a>'.$subscription.' > <br/>';
	else if ($post['commentcount'] > 0 && isset($summary[1]) && $post['enabledcomments']) echo '<a href="'.base_url().$post['urlname'].'">Continue reading ('.$post['commentcount'].' comments)</a>'.$subscription.' > <br/>';
	else if ($post['commentcount'] > 0 && $post['enabledcomments']) echo '<a href="'.base_url().$post['urlname'].'">Read comments ('.$post['commentcount'].')</a>'.$subscription.' > <br/>';
	else if (isset($summary[1])) echo '<p><a href="'.base_url().$post['urlname'].'">Continue reading</a>'.$subscription.' > <br/>';

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
		echo '<br/><br/>';
	}
?>
