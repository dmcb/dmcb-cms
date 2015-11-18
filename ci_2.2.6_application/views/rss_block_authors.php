		<item>
			<title><?php echo $user['displayname'];?></title>
			<link><?php echo base_url();?>profile/<?php echo $user['urlname'];?></link>
			<guid><?php echo base_url();?>profile/<?php echo $user['urlname'];?></guid>
			<pubDate><?php echo date('r',strtotime($user['registered']));?></pubDate>
			<author><?php echo $user['email'];?> (<?php echo $user['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode(character_limiter(preg_replace("/<img[^>]+\>/i", "", $user['profile']), 400));?>]]></description>
		</item>
