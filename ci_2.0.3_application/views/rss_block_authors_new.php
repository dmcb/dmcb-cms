		<item>
			<title><?=$author['displayname'];?></title>
			<link><?=base_url();?>profile/<?=$author['urlname'];?></link>
			<guid><?=base_url();?>profile/<?=$author['urlname'];?></guid>
			<pubDate><?=date('r',strtotime($author['registered']));?></pubDate>
			<author><?=$author['email'];?> (<?=$author['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode(character_limiter(preg_replace("/<img[^>]+\>/i", "", $author['profile']), 400));?>]]></description>
		</item>
