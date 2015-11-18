<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $this->config->item('dmcb_title');?><?php echo $this->config->item('dmcb_title_seperator');?><?php if ($this->config->item('dmcb_titles_lowercase') == "true") echo strtolower($title); else echo $title;?></title>

<?php if (isset($metadata)) echo $metadata; ?>
<?php if (isset($rss)) echo $rss; ?>
<?php if (isset($packages)) echo $packages; ?>

	<!-- google analytics -->
	<script type="text/javascript">	
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo $this->config->item('dmcb_google_analytics_id');?>']);
		_gaq.push(['_trackPageview']);
		
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>

</head>
<body>

<?php if (isset($waiver)) echo $waiver; ?>
<?php echo $site_content; ?>

</body>
</html>
