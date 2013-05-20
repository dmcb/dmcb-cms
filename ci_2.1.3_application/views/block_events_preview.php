<div class="event preview">
	<p>
	<?php
		$subscription = "";
		if ($event['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe')) 
		{
			$subscription =  ' <span class="restricted">Subscription required</span>';
		}
	
		echo '<h4><a href="'.base_url().$event['urlname'].'">'.$event['title'].'</a>'.$subscription.'</h4>';
		echo 'When: '.date("F jS, Y", strtotime($event['date']));
		if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
		if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
		if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
		else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
		echo '<br/>';
		echo 'Where: '.$event['location'];
		if (isset($event['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($event['address']).'">'.$event['address'].'</a>';
		echo '<br/>';
	?>
	</p>
</div>
