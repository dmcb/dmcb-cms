		<item>
			<title><?php echo $comment['displayname'];?> on <?php echo date("F jS, Y", strtotime($comment['date']));?> in <?php echo $comment['post']['title'];?></title>
			<link><?php echo base_url();?><?php echo $comment['post']['urlname'];?></link>
			<guid><?php echo base_url();?><?php echo $comment['post']['urlname'];?></guid>
			<pubDate><?php echo date('r',strtotime($comment['date']));?></pubDate>
			<author><?php echo $comment['email'];?> (<?php echo $comment['displayname'];?>)</author>
			<description><![CDATA[<?php echo htmlspecialchars_decode($comment['content']);?>]]></description>
		</item>
