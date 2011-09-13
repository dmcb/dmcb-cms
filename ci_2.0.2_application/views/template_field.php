<div class="fullcolumn">
	<p>Edit the <?php echo $field['name'];?> field.</p>
	
	<div class="seperator">&nbsp;</div>
	
	<form action="<?php echo base_url().'template/'.$template['templateid'].'/field/'.$field['htmlcode'];?>/edit" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div id="fields" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Field name</label>
					<input name="fieldname" type="text" class="text" maxlength="100" value="<?php echo set_value('fieldtitle', $field['name']); ?>"/>
					<?php echo form_error('fieldname'); ?>
				</div>
				
				<div class="forminput">
					<label>HTML code for embed</label>
					<input name="fieldcode" type="text" class="text" maxlength="20" value="<?php echo set_value('fieldcode', $field['htmlcode']); ?>"/>
					<?php echo form_error('fieldcode'); ?>
				</div>
				
				<div class="forminput">
					<label>Form type</label>
					<select name="fieldtype">
						<option value="1" <?php echo $default = FALSE; if ($field['form_type'] == 1) $default = TRUE; echo set_select('fieldtype', '1', $default);?>>One-line text input</option>
						<option value="2" <?php echo $default = FALSE; if ($field['form_type'] == 2) $default = TRUE; echo set_select('fieldtype', '2', $default);?>>Large text box, basic</option>
						<option value="3" <?php echo $default = FALSE; if ($field['form_type'] == 3) $default = TRUE; echo set_select('fieldtype', '3', $default);?>>Large text box, advanced</option>
					</select>
				</div>
				
				<div class="forminput">
					<label>User must fill out</label>
					<input name="fieldrequired" id="fieldrequired" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($field['required']) $default = TRUE; echo set_checkbox('fieldrequired', '1', $default); ?> />
				</div>
				
				<br/>
				
				<div class="forminput">
					<input type="submit" value="Save field" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
				<div class="forminput">
					<input type="submit" value="Delete field" name="delete" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>