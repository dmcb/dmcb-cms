		<item>
			<title><?=$comment['displayname'];?> on <?=date("F jS, Y", strtotime($comment['date']));?> in <?=$comment['post']['title'];?></title>
			<link><?=base_url();?><?=$comment['post']['urlname'];?></link>
			<guid><?=base_url();?><?=$comment['post']['urlname'];?></guid>
			<pubDate><?=date('r',strtotime($comment['date']));?></pubDate>
			<author><?=$comment['email'];?> (<?=$comment['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode($comment['content']);?>]]></description>
		</item>
