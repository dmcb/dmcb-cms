<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="<?=$this->config->item('dmcb_description');?>" />
	<meta name="keywords" content="<?=$this->config->item('dmcb_keywords');?>" />
	<meta name="robots" content="<?=$this->config->item('dmcb_robots');?>" />
	<meta name="author" content="<?=$this->config->item('dmcb_author');?>" />
	<title><?=$this->config->item('dmcb_title');?><?=$this->config->item('dmcb_title_seperator');?><?php if ($this->config->item('dmcb_titles_lowercase') == "true") echo strtolower($title); else echo $title;?></title>

	<link rel="image_src" href="<?=base_url();?>includes/images/facebook.jpg" />
	<link rel="pingback" href="<?=base_url();?>pingback" />

<?php if (isset($rss)) echo $rss; ?>

<?php if (isset($prepackages)) echo $prepackages; ?>

	<!-- dmcb styles-->
	<style type="text/css">
		@import "<?=base_url();?>includes/styles/elements.css";
		@import "<?=base_url();?>includes/styles/layout.css";
		<?php if (isset($cssfiles))
		{
			foreach($cssfiles as $cssfile)
			{
				echo '@import "'.base_url().substr($cssfile,1).'";';
			}
		}
		?>
		<?php if (isset($css)) echo $css; ?>
	</style>

	<!-- dmcb scripts -->
	<script type="text/javascript" src="<?=base_url();?>includes/scripts/functions.js"></script>
	<script type="text/javascript" src="<?=base_url();?>includes/scripts/panels.js"></script>
	<?php if (isset($jsfiles))
	{
		foreach($jsfiles as $jsfile)
		{
			echo '<script type="text/javascript" src="'.base_url().substr($jsfile,1).'"></script>';
		}
	}
	?>
	<script type="text/javascript">
		<!--
		<?php if (isset($javascript)) echo $javascript;?>
		dmcb.addLoadEvent(function () {
				Effect.InitializePage('<?=$focus;?>');
			}
		);
		document.write('<style type="text/css">div.panel { width: 0; height: 0; }</style>');
		-->
	</script>

<?php if (isset($packages)) echo $packages; ?>

</head>
<body>

	<?php echo $site_content; ?>

	<!-- google analytics -->
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("<?=$this->config->item('dmcb_google_analytics_id');?>");
	pageTracker._trackPageview();
	} catch(err) {}
	</script>
</body>
</html>
