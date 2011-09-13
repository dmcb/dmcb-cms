<form class="collapsible" action="<?=base_url();?><?=$post['urlname'];?>/editpost" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editpost');">Edit this post</a></legend>
		
		<div id="editpost" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<input type="hidden" name="categoryvalues" id="categoryvalues" value="<?=$post['categoryvalues'];?>" class="hidden"/>
			<input type="hidden" name="categorynames" id="categorynames" value="<?=$post['categorynames'];?>" class="hidden"/>
			<?php
			if (isset($post['previousposts']))
			{
			?>
			<input type="hidden" name="previouspostvalues" id="previouspostvalues" value="<?=$post['previouspostvalues'];?>" class="hidden"/>
			<input type="hidden" name="previouspostnames" id="previouspostnames" value="<?=$post['previouspostnames'];?>" class="hidden"/>
			<?php
			}
			?>
			
			<div class="formnotes">
				<p>Categories are keywords that help describe your post and help connect people to your post.</p>
				<p>Multiple categories may apply to your post, and you can suggest new ones.</p>
			</div>
			
			<div class="forminput">
				<label>Title</label>
				<input name="posttitle" type="text" class="text" maxlength="100" onkeypress="dmcb.toUrlname(this.form, this.value, 'posturlname')" onkeyup="dmcb.toUrlname(this.form, this.value, 'posturlname')" value="<?php echo set_value('posttitle', $post['title']); ?>"/>
				<?php echo form_error('posttitle'); ?>
			</div>
			
			<div class="forminput">
				<label>URL name</label>
				<input name="posturlname" type="text" class="text" maxlength="30" value="<?php $default = $post['urlname']; if (strrpos($post['urlname'], '/') != 0) $default = substr($post['urlname'], strrpos($post['urlname'], '/')+1); echo set_value('posturlname', $default); ?>"/>
				<?php echo form_error('posturlname'); ?>
			</div>
			
			<?php
			if ($this->acl->enabled('site', 'subscribe'))
			{
			?>
			<div class="forminput">
				<label>Needs subscription?</label>
				<input name="postsubscription" id="postsubscription" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($post['needsubscription'] == 1) $default = TRUE; echo set_checkbox('postsubscription', '1', $default); ?>/>
			</div>
			<?php
			}
			?>
			
			<div class="forminput">
				<label>Add categories</label>
				<select onchange="dmcb.categorylist.addItem(this)">
					<option value="">Choose categories to add</option>
				<?php
				foreach ($post['categories']->result_array() as $category) {
						echo '<option value="'.$category['categoryid'].'">'.$category['name'].'</option>';
				}
				?>
				</select>
			</div>
			
			<div class="forminput">
				<label>Add custom categories</label>
				<input name="customcategory" type="text" class="text" maxlength="50" value=""/>
			</div>
			
			<div class="forminput">
				<input type="button" value="Add new category" class="button" onclick="dmcb.categorylist.addCustomItem(customcategory)"/>
			</div>
			
			<br />
			
			<div class="forminput">
				<label>Categories</label>
				<div class="items" id="categorys"></div>
			</div>
			
			<br />
			
			<div class="forminput">
				<label>Post content</label>
				<textarea name="postcontent" class="<?php if ($simple_editor) echo 'simple_editor'; else echo 'post_editor'; ?>" rows="" cols=""><?php echo set_value('postcontent', $post['content']); ?></textarea>
				<?php echo form_error('postcontent'); ?>
			</div>

			<br />
			
			<?php
			if (sizeof($post['previousposts']) > 0)
			{
			?>
			<div class="forminput">
				<label>Refer to previous posts</label>
				<select onchange="dmcb.previouspostlist.addItem(this)">
					<option value="">Choose posts to add</option>
				<?php
				foreach ($post['previousposts'] as $previouspost) {
						echo '<option value="'.$previouspost['postid'].'">'.$previouspost['title'].'</option>';
				}
				?>
				</select>
			</div>
			
			<p>&nbsp;</p>
			
			<div class="forminput">
				<label>Posts</label>
				<div class="items" id="previousposts"></div>
			</div>
			
			<br />
			<?php
			}
			?>
			
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
				<input type="submit" value="Save changes" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			<?php if ($post['published'] == '0') { ?>
			<div class="forminput">
				<input type="submit" value="Save changes and publish" name="publish" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			<?php } ?>
			<div class="forminput">
				<input type="button" value="Delete post" class="button" onclick="return dmcb.confirmationLink('Are you sure you wish to delete this post?','<?=base_url();?><?=$post['urlname'];?>/delete')"/>
			</div>
		</div></div>
	</fieldset>
</form>
