<form class="collapsible important" action="<?php echo base_url();?>profile/<?php echo $this->uri->segment(2);?>/heldcomments" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('heldcomments');">Held back comments</a></legend>
		
		<div id="heldcomments" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<?php
			foreach ($heldcomments->result_array() as $hold) {
				$field = 'comment'.$hold['commentid'];
				echo '<div class="forminput">';
				echo '<label>Comment on '.date('F jS, Y \a\t g:i a', strtotime($hold['date']));
				if ($hold['reviewed'] == 0) 
				{
					echo ' (Currently pending approval from moderators)';		
				}					
				echo '</label>';
				echo '<textarea name="'.$field.'" rows="" cols="">'.set_value($field, str_replace("<br/>","\n",$hold['content'])).'</textarea>';
				echo form_error($field);
				echo '</div>';
				
				echo '<div class="forminput"><input type="submit" value="Edit comment" class="button" name="'.$field.'edit" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/></div>';
				echo '<div class="forminput"><input type="submit" value="Delete comment" class="button" name="'.$field.'delete" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/></div>';
				echo '<br/>';
			}
			?>
		
		</div></div>
	</fieldset>
</form>
