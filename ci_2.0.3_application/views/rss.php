<?php
echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?=$feed;?>" rel="self" type="application/rss+xml" />
		<title><![CDATA[<?php echo htmlspecialchars_decode($this->config->item('dmcb_title').$this->config->item('dmcb_title_seperator')); if ($this->config->item('dmcb_titles_lowercase') == "true") echo strtolower(htmlspecialchars_decode($title)); else echo htmlspecialchars_decode($title);?>]]></title>
		<link><?=$feed;?></link>
		<description><![CDATA[<?php echo htmlspecialchars_decode($this->config->item('dmcb_description'));?>]]></description>
		<lastBuildDate><?php echo date('r',strtotime($date));?></lastBuildDate>
		<language>en</language>	
<?php if (isset($rsscontent)) echo $rsscontent; ?>
	</channel>
</rss>