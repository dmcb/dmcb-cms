<form class="collapsible" action="<?=base_url();?><?=$post['urlname'];?>/theme" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('theme');">Edit post's theme</a></legend> 
		
		<div id="theme" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="forminput">
				<label for="template">Post CSS</label>
				<textarea name="css" rows="" cols=""><?php echo set_value('css', $post['css']); ?></textarea>
				<?php echo form_error('css'); ?>
			</div>
			
			<?php
				for ($i=0; $i<=sizeof($cssfiles); $i++)
				{
					$cssfile = "";
					if ($i != sizeof($cssfiles))
					{
						$cssfile = $cssfiles[$i];
					}
					$field = 'css'.($i+1);
					$field_error = 'css'.($i+1).'_error';
					
					echo '<div class="forminput">';
					if ($i == sizeof($cssfiles) && $i != 0)
						echo '<label>Add another CSS file</label>';
					else if ($i == sizeof($cssfiles))
						echo '<label>Add CSS file</label>';
					else
						echo '<label>CSS file #'.($i+1).'</label>';
					echo '<input name="'.$field.'" type="text" maxlength="250" class="text" value="'.set_value($field, $cssfile).'"/>';
					echo form_error($field);
					echo '</div>';
				}
			?>
			
			<div class="forminput">
				<label for="template">Post Javascript</label>
				<textarea name="javascript" rows="" cols=""><?php echo set_value('javascript', $post['javascript']); ?></textarea>
				<?php echo form_error('javascript'); ?>
			</div>
			
			<?php
				for ($i=0; $i<=sizeof($jsfiles); $i++)
				{
					$jsfile = "";
					if ($i != sizeof($jsfiles))
					{
						$jsfile = $jsfiles[$i];
					}
					$field = 'js'.($i+1);
					$field_error = 'js'.($i+1).'_error';
					
					echo '<div class="forminput">';
					if ($i == sizeof($jsfiles) && $i != 0)
						echo '<label>Add another JS file</label>';
					else if ($i == sizeof($jsfiles))
						echo '<label>Add Javascript file</label>';
					else
						echo '<label>Javascript file #'.($i+1).'</label>';
					echo '<input name="'.$field.'" type="text" maxlength="250" class="text" value="'.set_value($field, $jsfile).'"/>';
					echo form_error($field);
					echo '</div>';
				}
			?>

			<div class="forminput">
				<input type="submit" value="Save theme" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			
		</div></div>
	</fieldset>
</form>