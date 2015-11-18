<form class="collapsible" action="<?php echo base_url();?>profile/<?php echo $this->uri->segment(2);?>/addpost" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('addpost');">Add a post</a></legend>
		
		<div id="addpost" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
										
			<div class="forminput">
				<label>Title</label>
				<input name="posttitle" type="text" class="text" maxlength="50" onkeypress="dmcb.toUrlname(this.form, this.value, 'posturlname')" onkeyup="dmcb.toUrlname(this.form, this.value, 'posturlname')" value="<?php echo set_value('posttitle'); ?>"/>
				<?php echo form_error('posttitle'); ?>
			</div>
			
			<div class="forminput">
				<label>URL name</label>
				<input name="posturlname" type="text" class="text" maxlength="50" value="<?php echo set_value('posturlname'); ?>"/>
				<?php echo form_error('posturlname'); ?>
			</div>
			
			<div class="forminput">
				<input type="submit" value="Continue" name="continue" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
