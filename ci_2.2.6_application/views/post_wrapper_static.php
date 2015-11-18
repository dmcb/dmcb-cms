<div class="leftcolumn">
	<h2>Share this post</h2>
	<p>
		<img src="/includes/images/icon_facebook.gif" alt="" /> <a href="http://www.facebook.com/share.php?u=<?php echo urlencode(base_url().$post['urlname']);?>">Share on Facebook</a><br/>
		<img src="/includes/images/icon_twitter.gif" alt="" /> <a href="http://twitter.com/home?status=<?php echo urlencode('Currently at '.$this->config->item('dmcb_title').' reading "'.$post['title'].'" '.shorten_url(base_url().$post['urlname']));?>">Send to Twitter</a><br/>
	</p>
	
	<?php if (isset($files_section)) echo $files_section; ?>
	<?php if (isset($pingbacks_section)) echo $pingbacks_section; ?>
	
</div>
<div class="centercolumnlarge">
	<div class="post">
	
		<?php if (isset($post_section)) echo $post_section; ?>
		<?php if (isset($references_section)) echo $references_section; ?>
		
		<div class="spacer">&nbsp;</div>
		
		<iframe src="http://www.facebook.com/widgets/like.php?href=<?php echo urlencode(base_url().$post['urlname']);?>" scrolling="no" frameborder="0" style="border:none; width:450px; height:80px"></iframe>
		
	</div>
		
	<?php if (isset($comments_section)) echo $comments_section; ?>
		
</div>
