<div class="event full">
	<h2><?=$event['title'];?></h2>
	
	<h6>
	<?php
		if ($event['canedit'])
		{
			echo '<a href="'.base_url().$event['urlname'].'/editpost">Edit event</a>';
		}
	?>
	</h6>
	
	<?php
		echo '<div class="notice">';
		echo 'When: '.date("F jS, Y", strtotime($event['date']));
		if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
		if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
		if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
		else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
		echo '<br/>';
		echo 'Where: '.$event['location'];
		if (isset($event['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($event['address']).'">'.$event['address'].'</a>';
		echo '</div>';
	?>

	<?php

	$summary = split("<!-- pagebreak -->",$event['content']);

	echo '<br/>'.$summary[0];
	echo '</p>';
	
	$subscription = "";
	if ($event['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe')) 
	{
		$subscription = ', <span class="restricted">Subscription only</span>';
	}
	
	if ($event['commentcount'] == 1 && isset($summary[1])) echo '<a href="'.base_url().$event['urlname'].'">Continue reading ('.$event['commentcount'].' comment)</a>'.$subscription.' | ';
	else if ($event['commentcount'] > 0 && isset($summary[1])) echo '<a href="'.base_url().$event['urlname'].'">Continue reading ('.$event['commentcount'].' comments)</a>'.$subscription.' | ';
	else if ($event['commentcount'] > 0) echo '<a href="'.base_url().$event['urlname'].'">Read comments ('.$event['commentcount'].')</a>'.$subscription.' | ';
	else if (isset($summary[1])) echo '<p><a href="'.base_url().$event['urlname'].'">Continue reading</a>'.$subscription.' | ';

	?>

	<a href="<?=base_url();?><?=$event['urlname'];?>/addcomment">Add comment</a>
	</p>
</div>
<?php
	if ($current != $count)
	{
		echo '<hr/>';
	}
?>