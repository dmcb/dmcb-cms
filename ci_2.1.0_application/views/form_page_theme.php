<form class="collapsible" action="<?php echo base_url();?><?php echo $page['urlname'];?>/theme" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('templates');">Edit page's CSS</a></legend> 
		
		<div id="templates" class="panel"><div>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="forminput">
				<label for="template">Page CSS</label>
				<textarea name="css" rows="" cols=""><?php if ($this->form_validation->css != NULL || !isset($page['css'])) echo $this->form_validation->css; else echo $page['css']; ?></textarea>
				<?php echo $this->form_validation->css_error; ?>	
			</div>

			<div class="forminput">
				<input type="submit" value="Save CSS" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			
		</div></div>
	</fieldset>
</form>
