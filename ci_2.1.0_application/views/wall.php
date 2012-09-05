<div class="leftcolumn">
	<h2>We are engaging students around the world to make sustainable choices.</h2><br/>
	<h2>Share a choice you made for the environment:</h2>
	<form action="<?php echo base_url();?>wall" method="post" onsubmit="return dmcb.submit(this);">
		<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
		<label id="namelabel">Name</label><label id="citylabel">City</label>
		<input type="text" class="text" name="name" id="name" value="<?php echo set_value('name'); ?>" maxlength="50" /><input type="text" class="text" name="city" id="city" value="<?php echo set_value('city'); ?>" maxlength="50" />
		<label id="choicelabel">My Choice:</label>
		<input type="text" class="text" name="content" id="content" value="<?php echo set_value('content'); ?>" maxlength="140" /><input type="submit" value="post" name="post" class="button" id="post" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
	</form>
	<?php echo validation_errors(); ?>
	<br/>
	<iframe src="http://www.facebook.com/widgets/like.php?href=<?php echo urlencode(base_url());?>wall" scrolling="no" frameborder="0" style="border:none; width:288px; height:80px"></iframe>
</div>

<div class="centercolumnlarge">
	<h2>Making the world a better place has never been so simple</h2>
	<?php
		foreach ($walls->result_array() as $wall)
		{
			if (strlen($wall['content']) > 0)
			{
				echo '<br/><blockquote>'.$wall['content'].'<br/>-'.$wall['name'].', '.$wall['city'].'</blockquote>';
			}
		}
	?>
	
	<?php echo $this->pagination->create_links();?>
</div>
