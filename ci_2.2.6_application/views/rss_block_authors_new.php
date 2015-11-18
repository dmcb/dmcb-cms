		<item>
			<title><?php echo $author['displayname'];?></title>
			<link><?php echo base_url();?>profile/<?php echo $author['urlname'];?></link>
			<guid><?php echo base_url();?>profile/<?php echo $author['urlname'];?></guid>
			<pubDate><?php echo date('r',strtotime($author['registered']));?></pubDate>
			<author><?php echo $author['email'];?> (<?php echo $author['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode(character_limiter(preg_replace("/<img[^>]+\>/i", "", $author['profile']), 400));?>]]></description>
		</item>
