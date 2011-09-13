		<item>
			<title><?=$user['displayname'];?></title>
			<link><?=base_url();?>profile/<?=$user['urlname'];?></link>
			<guid><?=base_url();?>profile/<?=$user['urlname'];?></guid>
			<pubDate><?=date('r',strtotime($user['registered']));?></pubDate>
			<author><?=$user['email'];?> (<?=$user['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode(character_limiter(preg_replace("/<img[^>]+\>/i", "", $user['profile']), 400));?>]]></description>
		</item>
