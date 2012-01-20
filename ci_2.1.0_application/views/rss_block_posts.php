		<item>
			<title><?=$post['title'];?></title>
			<link><?=base_url();?><?=$post['urlname'];?></link>
			<guid><?=base_url();?><?=$post['urlname'];?></guid>
			<pubDate><?=date('r',strtotime($post['date']));?></pubDate>
<?php
			if ($post['userid'] != NULL)
			{
				echo '			<author>'.$post['user']['email'].' ('.$post['user']['displayname'].')</author>'.PHP_EOL;
			}
			?>
			<description><![CDATA[<?php $summary = explode("<!-- pagebreak -->",$post['content']); echo htmlspecialchars_decode(preg_replace("/<img[^>]+\>/i", "", $summary[0])); ?>]]></description>
<?php
			if (isset($post['image']))
			{
				echo '			<enclosure url="'.base_url().$post['image']['urlpath'].'" length="'.$post['image']['filesize'].'" type="'.$post['image']['mimetype'].'" />'.PHP_EOL;
			}
			?>
		</item>
