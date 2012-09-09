<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="<?php echo $this->config->item('dmcb_description');?>" />
	<meta name="keywords" content="<?php echo $this->config->item('dmcb_keywords');?>" />
	<meta name="robots" content="<?php echo $this->config->item('dmcb_robots');?>" />
	<meta name="author" content="<?php echo $this->config->item('dmcb_author');?>" />
	<title><?php echo $this->config->item('dmcb_title');?><?php echo $this->config->item('dmcb_title_seperator');?><?php if ($this->config->item('dmcb_titles_lowercase') == "true") echo strtolower($title); else echo $title;?></title>

	<link rel="image_src" href="<?php echo base_url();?>includes/images/facebook.jpg" />
	<link rel="pingback" href="<?php echo base_url();?>pingback" />

<?php if (isset($rss)) echo $rss; ?>
<?php if (isset($packages)) echo $packages; ?>

</head>
<body>

<?php if (isset($waiver)) echo $waiver; ?>
<?php echo $site_content; ?>


	<!-- google analytics -->
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("<?php echo $this->config->item('dmcb_google_analytics_id');?>");
	pageTracker._trackPageview();
	} catch(err) {}
	</script>
</body>
</html>
