<span class="event listing">
	<?php
		echo date("F jS, Y", strtotime($event['date']));
		if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
		if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
		if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
		else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
		echo ': <a href="'.base_url().$event['urlname'].'">'.$event['title'].'</a>';

		if ($event['needsubscription'] == "1" && $this->acl->enabled('site', 'subscribe'))
		{
			echo ' <span class="restricted">Subscription required</span>';
		}
	?>
</span>
<br/>
