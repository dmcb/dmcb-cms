		<item>
			<title><?php echo $event['title'];?></title>
			<link><?php echo base_url();?><?php echo $event['urlname'];?></link>
			<guid><?php echo base_url();?><?php echo $event['urlname'];?></guid>
			<pubDate><?php echo date('r',strtotime($event['publisheddate']));?></pubDate>
			<?php
			if ($event['userid'] != NULL)
			{
				echo '<author>'.$event['user']['email'].' ('.$event['user']['displayname'].')</author>';
			}
			?>
			<description><![CDATA[<?php
			echo date("F jS, Y", strtotime($event['date']));
			if (isset($event['time'])) echo ' @ '.date("g:ia", strtotime($event['time']));
			if (isset($event['enddate'])) echo ' to '.date("F jS, Y", strtotime($event['enddate']));
			if (isset($event['enddate']) && isset($event['endtime'])) echo ' @ '.date("g:ia", strtotime($event['endtime']));
			else if (isset($event['endtime'])) echo ' to '.date("g:ia", strtotime($event['endtime']));
			echo ' - '.$event['location'];
			if (isset($event['address'])) echo ', <a href="http://maps.google.ca/maps?q='.urlencode($event['address']).'">'.$event['address'].'</a>';
			$summary = explode("<!-- pagebreak -->",$event['content']); echo htmlspecialchars_decode(preg_replace("/<img[^>]+\>/i", "", $summary[0]));
			?>]]></description>
		</item>
