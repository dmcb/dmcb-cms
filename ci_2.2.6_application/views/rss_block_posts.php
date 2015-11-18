		<item>
			<title><?php echo $post['title'];?></title>
			<link><?php echo base_url();?><?php echo $post['urlname'];?></link>
			<guid><?php echo base_url();?><?php echo $post['urlname'];?></guid>
			<pubDate><?php echo date('r',strtotime($post['date']));?></pubDate>
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
