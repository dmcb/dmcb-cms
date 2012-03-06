<form class="collapsible" action="<?=base_url();?><?=$post['urlname'];?>/taguser" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('taguser');">Tag a user to post</a></legend>

		<div id="taguser" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<?php
			for ($i=0; $i<=sizeof($contributors); $i++)
			{
				$contributor['displayname'] = "";
				if ($i != sizeof($contributors))
					$contributor = $contributors[$i];
				$field = 'contributor'.($i+1);
				$field_error = 'contributor'.($i+1).'_error';

				echo '<div class="forminput">';
				if ($i == sizeof($contributors) && $i != 0)
					echo '<label>Tag another user</label>';
				else if ($i == sizeof($contributors))
					echo '<label>Tag a user</label>';
				else
					echo '<label>Tagged user #'.($i+1).'</label>';
				echo '<input name="'.$field.'" id="tag_'.$i.'" type="text" maxlength="30" class="text" value="'.set_value($field, $contributor['displayname']).'"/>';
				echo '<div class="autocomplete" id="autocomplete_'.$i.'" style="display: none; position:relative;"></div>';

				if ($this->config->item('csrf_protection')) $csrf = "parameters: '".$this->security->get_csrf_token_name()."=".$this->security->get_csrf_hash()."',";
				$this->packages[4]['javascript'][] = "
new Ajax.Autocompleter('tag_".$i."','autocomplete_".$i."','".base_url()."autocomplete/user', {
	".$csrf."
	minChars: 2,
	frequency: 0.1
});";

				echo form_error($field);
				echo '</div>';
			}
			?>

			<div class="forminput">
				<input type="submit" value="Set" name="set" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>