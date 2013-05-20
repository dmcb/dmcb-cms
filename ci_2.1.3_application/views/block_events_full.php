<div class="block event full">
	<span class="event-title"><?php echo $event['title'];?></span>

	<span class="admin-bar">
	<?php
		if ($event['canedit'])
		{
			echo '<a href="'.base_url().$event['urlname'].'/editpost">Edit event</a>';
		}
	?>
	</span>

	<ul class="event-meta">
	<?php
		echo '<li class="when">When: '.date("F jS, Y", strtotime($event['date']));
		if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
		if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
		if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
		else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
		echo '</li>';
		echo '<li class="where">Where: '.$event['location'];
		if (isset($event['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($event['address']).'">'.$event['address'].'</a>';
		echo '</li>';
	?>
	</ul>

	<?php

	$summary = explode("<!-- pagebreak -->",$event['content']);

	echo '<div class="event-content">';
	echo $summary[0].'</p>';

	$subscription = "";
	if ($event['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
	{
		$subscription = ', <span class="restricted">Subscription only</span>';
	}

	<span class="comment-bar">
	if ($event['commentcount'] == 1 && isset($summary[1])) echo '<a href="'.base_url().$event['urlname'].'">Continue reading ('.$event['commentcount'].' comment)</a>'.$subscription.' | ';
	else if ($event['commentcount'] > 0 && isset($summary[1])) echo '<a href="'.base_url().$event['urlname'].'">Continue reading ('.$event['commentcount'].' comments)</a>'.$subscription.' | ';
	else if ($event['commentcount'] > 0) echo '<a href="'.base_url().$event['urlname'].'">Read comments ('.$event['commentcount'].')</a>'.$subscription.' | ';
	else if (isset($summary[1])) echo '<a href="'.base_url().$event['urlname'].'">Continue reading</a>'.$subscription.' | ';

	?>
	<a href="<?php echo base_url();?><?php echo $event['urlname'];?>/addcomment">Add comment</a>
	</span>
</div>
<?php
	if ($current != $count)
	{
		echo '<hr/>';
	}
?>
