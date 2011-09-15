<div class="fullcolumn">
	<p>Please configure the settings of the block '<?php echo $block['title'];?>'.</p>
	
	<div class="seperator">&nbsp;</div>
	
	<form action="<?php echo base_url().'block/'.$block['blockinstanceid']?>/edit" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Title</label>
					<input name="blocktitle" type="text" class="text" maxlength="20" value="<?php echo set_value('blocktitle', $block['title']); ?>"/>
					<?php echo form_error('blocktitle'); ?>
				</div>
				
				<div class="forminput">
					<label>Show error feedback</label>
					<input name="feedback" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($block['feedback'] == 1) $default = TRUE; echo set_checkbox('feedback', '1', $default); ?>/>
				</div>
				
				<?php
					if ($block['variables']->num_rows() > 0)
					{
						echo '<div class="formnotes">';
						foreach ($block['variables']->result_array() as $variable)
						{
							echo '<strong>'.ucfirst(str_replace('_', ' ', $variable['variablename'])).'</strong>: '.$variable['variabledescription'].'<br/>';
						}
						echo '</div>';
					}
					
					foreach ($block['variables']->result_array() as $variable)
					{
						$variablename = $variable['variablename'];
						$variablenamespecify = $variable['variablename'].'_specify';
						echo '<div class="forminput">';
						echo '<label>'.ucfirst(str_replace('_', ' ', $variablename)).'</label>';
						
						// If variable choices don't start with * or +, create a select drop down box
						$chosen = FALSE;
						if ($variable['pattern'] != "*" && $variable['pattern'] != "+")
						{
							echo '<select name="'.$variablename.'">';
							$options = split('\|', $variable['pattern']);
							foreach ($options as $option)
							{
								$default = FALSE;
								if ($option != "*")
								{
									if (isset($block['values'][$variablename]) && $option ==  $block['values'][$variablename])
									{
										$chosen = TRUE;
										$default = TRUE;
									}
									echo '<option value="'.$option.'" '.set_select($variablename, $option, $default).' >'.$option.'</option>';
								}
							}
							echo '</select>';
							if (strstr($variable['pattern'], '*')) // If variable choices has a *, add a specify field
							{
								echo ' or specify ';
								echo '<input name="'.$variablenamespecify.'" type="text" class="text sameline" maxlength="100" value="';
								if (!$chosen && isset($block['values'][$variablename]))
								{
									echo set_value($variablenamespecify, $block['values'][$variablename]);
								}
								else
								{
									echo set_value($variablenamespecify);
								}
								echo '"/>';
								echo form_error($variablenamespecify);
							}
						}
						else if ($variable['pattern'] == "+") // Variable choice is designed for a text box
						{
							echo '<textarea name="'.$variablename.'" class="page_editor" rows="" cols="">';
							if (!$chosen && isset($block['values'][$variablename]))
							{
								echo set_value($variablename, $block['values'][$variablename]);
							}
							else
							{
								echo set_value($variablename);
							}
							echo '</textarea>';
							echo form_error($variablename);
						}
						else // Variable choice is a *, so it's only a specify field
						{
							echo '<input name="'.$variablename.'" type="text" class="text" maxlength="100" value="';
							if (!$chosen && isset($block['values'][$variablename]))
							{
								echo set_value($variablename, $block['values'][$variablename]);
							}
							else
							{
								echo set_value($variablename);
							}
							echo '"/>';
							echo form_error($variablename);
						}
						echo '</div>';
					}
				?>
				
				<div class="forminput">
					<input type="submit" value="Save settings" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
				<div class="forminput">
					<input type="submit" value="Delete" name="delete" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>