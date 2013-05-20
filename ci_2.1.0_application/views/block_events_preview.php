<div class="block event preview">
<?php
	$subscription = "";
	if ($event['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe')) 
	{
		$subscription =  ' <span class="restricted">Subscription required</span>';
	}

	echo '<p class="event-title"><a href="'.base_url().$event['urlname'].'">'.$event['title'].'</a>'.$subscription.'</p>';

	echo '<ul class="event-meta"><li class="when">When: '.date("F jS, Y", strtotime($event['date']));
	if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
	if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
	if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
	else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
	echo '</li><li class="where">';
	echo 'Where: '.$event['location'];
	if (isset($event['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($event['address']).'">'.$event['address'].'</a>';
	echo '</li></ul>';
?>
</div>
