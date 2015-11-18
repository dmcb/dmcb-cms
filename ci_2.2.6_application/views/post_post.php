<?php
	echo '<div class="title"><div class="innerwrapper"><h2>'.$post['title'].'</h2></div></div>';

	echo '<div class="information"><div class="innerwrapper">';
	if (!$post['published'])
	{
		echo '<h5>Draft started on '.date("F jS, Y", strtotime($post['date'])).'</h5>';
	}
	else
	{
		echo '<h5>Posted '.date("F jS, Y", strtotime($post['date']));

		if (isset($author['userid']))
		{
			echo ' by ';

			if ($author['enabledprofile'])
			{
				echo '<a href="'.base_url().'profile/'.$author['urlname'].'">'.$author['displayname'].'</a>';
			}
			else
			{
				echo $author['displayname'];
			}
		}

		if ((isset($parentpage['urlname']) && $parentpage['urlname'] != NULL) || ($this->config->item('dmcb_default_articles_page') != NULL))
		{
			$i=0;
			foreach ($post['postcategories']->result_array() as $category)
			{
				if ($i==0) echo ' in ';
				$i++;
				if (isset($parentpage['urlname']) && $parentpage['urlname'] != NULL)
				{
					echo '<a href="'.base_url().$parentpage['urlname'].'/category/'.$category['urlname'].'">'.$category['name'].'</a>';
				}
				else if ($this->config->item('dmcb_default_articles_page') != NULL)
				{
					echo '<a href="'.base_url().$this->config->item('dmcb_default_articles_page').'/category/'.$category['urlname'].'">'.$category['name'].'</a>';
				}
				if ($i!=$post['postcategories']->num_rows())
				{
					echo ', ';
				}
			}
		}

		if (sizeof($contributors) > 0)
		{
			echo ' featuring ';
			$i = 0;
			foreach ($contributors as $contributor)
			{
				if ($i < sizeof($contributors) && $i != 0)
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
		echo '</h5>';
	}

	if ($this->config->item('dmcb_show_neighbour_posts') == "true")
	{
		if (isset($previous_post['postid']))
		{
			echo '<h5>Previous article: <a href="'.base_url().$previous_post['urlname'].'">'.$previous_post['title'].'</a></h5>';
		}
		if (isset($next_post['postid']))
		{
			echo '<h5>Next article: <a href="'.base_url().$next_post['urlname'].'">'.$next_post['title'].'</a></h5>';
		}
	}

	if ($post['originalurl'] != NULL)
	{
		echo '<h5>Originally posted at <a href="'.$post['originalurl'].'">'.$item['originalurl'].'</a></h5>';
	}

	if (isset($admin_toolbar))
	{
		echo '<h6>';
		echo $admin_toolbar;
		echo '</h6>';
	}
	echo '</div></div>';

	if (isset($post['event']))
	{
		echo '<div class="notice"><div class="innerwrapper">';
		echo 'When: '.date("F jS, Y", strtotime($post['event']['date']));
		if (isset($post['event']['time'])) echo ' @ '.date("g:ia", strtotime($post['event']['time']));
		if (isset($post['event']['enddate'])) echo ' to '.date("F jS, Y", strtotime($post['event']['enddate']));
		if (isset($post['event']['enddate']) && isset($post['event']['endtime'])) echo ' @ '.date("g:ia", strtotime($post['event']['endtime']));
		else if (isset($post['event']['endtime'])) echo ' to '.date("g:ia", strtotime($post['event']['endtime']));
		if (isset($post['event']['location']))
		{
			echo '<br/>Where: '.$post['event']['location'];
			if (isset($post['event']['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($post['event']['address']).'">'.$post['event']['address'].'</a>';
		}
		echo '</div></div>';
	}

	echo '<div class="article"><div class="innerwrapper">';
	echo $post['content'];
	echo '</div></div>';
?>
