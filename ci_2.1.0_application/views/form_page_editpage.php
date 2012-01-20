<form class="collapsible" action="<?=base_url();?><?=$page['urlname'];?>/editpage" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editpage');">Edit this page</a></legend>
		
		<div id="editpage" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="forminput">
				<label>Title</label>
				<input name="title" type="text" class="text" maxlength="50" onkeypress="dmcb.toUrlname(this.form, this.value, 'urlname')" onkeyup="dmcb.toUrlname(this.form, this.value, 'urlname')" value="<?php echo set_value('title', $page['title']); ?>"/>
				<?php echo form_error('title'); ?>
			</div>
			
			<div class="forminput">
				<label>URL name</label>
				<input name="urlname" type="text" class="text" maxlength="50" value="<?php $default = $page['urlname']; if (strrpos($page['urlname'], '/') != 0) $default = substr($page['urlname'], strrpos($page['urlname'], '/')+1); echo set_value('urlname', $default); ?>"/>
				<?php echo form_error('urlname'); ?>
			</div>

			<?php
			if ($page['pageof'] != NULL)
			{
			?>
				
			<div class="formnotes full">
				<p>Choose to nest a URL if you want a URL like <?=base_url();?>parent/child instead of <?=base_url();?>child</p>
			</div>
			
			<div class="forminput">
				<label>Nested URL</label>
				<input name="nestedurl" id="nestedurl" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if (strpos($page['urlname'], '/') !== FALSE) $default = TRUE; echo set_checkbox('nestedurl', '1', $default); ?> />
			</div>
			<?php
			}

			if ($this->acl->enabled('site', 'subscribe'))
			{
			?>
			
			<div class="forminput">
				<label>Subscription required</label>
				<input name="pagesubscription" id="pagesubscription" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($page['needsubscription'] == 1) $default = TRUE; echo set_checkbox('pagesubscription', '1', $default); ?>/>
			</div>

			<?php
			}
			?>
			
			<br />

			<div class="forminput">
				<label for="content">Page content</label>
				<textarea name="content" class="<?php if ($simple_editor) echo 'simple_editor'; else echo 'page_editor'; ?>" rows="" cols=""><?php echo set_value('content', $page['content']); ?></textarea>
				<?php echo form_error('content'); ?>
			</div>
			
			<br />
			
			<?php
				foreach ($fields as $field)
				{
					if (!isset($values[$field['htmlcode']]))
					{
						$values[$field['htmlcode']] = NULL;
					}
				
					echo '<div class="forminput"><label for="'.$field['htmlcode'].'">'.ucfirst($field['name']).'</label>';
					if ($field['form_type'] == 1)
					{
						echo '<input name="'.$field['htmlcode'].'" type="text" class="text" maxlength="9999" value="'.set_value($field['htmlcode'], $values[$field['htmlcode']]).'"/>';
					}
					else if ($field['form_type'] == 2)
					{
						echo '<textarea name="'.$field['htmlcode'].'" class="simple_editor" rows="" cols="">'.set_value($field['htmlcode'], $values[$field['htmlcode']]).'</textarea>';
					}
					else
					{
						echo '<textarea name="'.$field['htmlcode'].'" class="page_editor" rows="" cols="">'.set_value($field['htmlcode'], $values[$field['htmlcode']]).'</textarea>';
					}
					echo form_error($field['htmlcode']);
					echo '</div>';
				}
				
				if (sizeof($fields) > 0) 
				{
					echo '<br />';
				}
			?>

			<div class="forminput">
				<input type="submit" value="Submit changes" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
