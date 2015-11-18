<div class="fullcolumn">
	<p>Please configure the <?php echo $template['type'];?> template, <?php echo $template['title'];?>.</p>
	
	<div class="seperator">&nbsp;</div>
	
	<form action="<?php echo base_url().'template/'.$template['templateid']?>/edit" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div id="template" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes full">
					<p>You can choose what fields are where in the template by inserting it's respective HTML into the template.</p>
					<p>
						Required fields:
						<?php
							foreach ($required as $field)
							{
								echo '<br/>';
								if ($field['custom'])
								{
									echo '<a href="'.base_url().'template/'.$template['templateid'].'/field/'.$field['htmlcode'].'" style="font-weight: bold;">'.ucfirst($field['name']).'</a>';
								}
								else
								{
									echo ucfirst($field['name']);
								}
								echo ': %'.$field['htmlcode'].'%';
							}
						?>
					</p>
					<p>
						Optional fields:
						<?php
							foreach ($optional as $field)
							{
								echo '<br/>';
								if ($field['custom'])
								{
									echo '<a href="'.base_url().'template/'.$template['templateid'].'/field/'.$field['htmlcode'].'" style="font-weight: bold;">'.ucfirst($field['name']).'</a>';
								}
								else
								{
									echo ucfirst($field['name']);
								}
								echo ': %'.$field['htmlcode'].'%';
							}
						?>
					</p>
				</div>
				
				<div class="forminput">
					<label>Title</label>
					<input name="title" type="text" class="text" maxlength="100" value="<?php echo set_value('title', $template['title']); ?>"/>
					<?php echo form_error('title'); ?>
				</div>
				
				<div class="forminput">
					<label>Template </label>
					<textarea name="content" class="template_editor" rows="" cols=""><?php echo set_value('content', $template['content']); ?></textarea>
					<?php echo form_error('content'); ?>
				</div>
				
				<div class="forminput">
					<label>Simple editor for <?php echo $template['type'];?> content</label>
					<input name="simple" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($template['simple'] == 1) $default = TRUE; echo set_checkbox('simple', '1', $default); ?>/>
				</div>
				
				<?php
				if ($template['type'] == "page")
				{
				?>
				
				<br/>
				
				<div class="formnotes full">
					<p>By default posts follow YYYYMMDD/postname format, but you can set posts to follow pagename/post/postname.</p>
				</div>
				
				<div class="forminput">
					<label>Posts use page URL name</label>
					<input name="pagepostname" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($template['pagepostname'] == 1) $default = TRUE; echo set_checkbox('pagepostname', '1', $default); ?>/>
				</div>
				<?php
				}
				?>
				
				<br/>
				
				<div class="forminput">
					<input type="submit" value="Save" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
				<div class="forminput">
					<input type="submit" value="Delete" name="delete" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
	
	<div class="seperator">&nbsp;</div>
	
	<form action="<?php echo base_url().'template/'.$template['templateid']?>/fields" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div id="fields" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="formnotes full">
					<p>In addition to the default fields, you may add additional fields to the template that will be required by anyone editing <?php echo $template['type'];?>s using this template.</p>
				</div>

				<div class="forminput">
					<label>Field name</label>
					<input name="fieldname" type="text" class="text" maxlength="20" value="<?php echo set_value('fieldname'); ?>"/>
					<?php echo form_error('fieldname'); ?>
				</div>
				
				<div class="forminput">
					<label>HTML code for embed</label>
					<input name="fieldcode" type="text" class="text" maxlength="10" value="<?php echo set_value('fieldcode'); ?>"/>
					<?php echo form_error('fieldcode'); ?>
				</div>
				
				<div class="forminput">
					<label>Form type</label>
					<select name="fieldtype">
						<option value="1" <?php echo set_select('fieldtype', '1');?>>One-line text input</option>
						<option value="2" <?php echo set_select('fieldtype', '2');?>>Large text box, basic</option>
						<option value="3" <?php echo set_select('fieldtype', '3');?>>Large text box, advanced</option>
					</select>
				</div>
				
				<div class="forminput">
					<label>User must fill out</label>
					<input name="fieldrequired" id="fieldrequired" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('fieldrequired', '1'); ?> />
				</div>
				
				<br/>
				
				<div class="forminput">
					<input type="submit" value="Add field" name="add" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>
