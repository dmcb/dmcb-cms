<form class="collapsible" action="<?php echo base_url();?><?php echo $page['urlname'];?>/settemplate" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('settemplate');">Set page template</a></legend> 
		
		<div id="settemplate" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="formnotes full">
				<p>Select a page and post template to use for this page, if you don't select one to use, or there aren't any available, whatever the specified default templates are for this page will be used.</p>
			</div>

			<div class="forminput">
				<label>Templates</label>
				<table>
					<?php
					if (sizeof($templates) == 0)
						echo '<tr><td>No templates available for use.</td></tr>';
					else
					{
						$post_templates = TRUE;
						$first_row = TRUE;
						foreach ($templates as $row) 
						{
							if ($row['type'] == "page" && $first_row)
							{
								echo '<tr><td colspan="3">Page templates:<br/><br/></td>';
								$first_row = FALSE;
							}
							if ($row['type'] == "post" && $post_templates)
							{
								if (!$first_row)
								{
									echo '<tr><td colspan="3"><br/><br/></td></tr>';
								}
								echo '<tr><td colspan="3">Post templates:<br/><br/></td></tr>';
								$post_templates = FALSE;
							}
						
							echo '<tr class="data"><td>'.$row['title'].'</td><td>';

							echo '<a href="'.base_url().$page['urlname'].'/settemplate/';
							if ($row['templateid'] == $page[$row['type'].'_templateid'])
							{
								echo 'remove_page/'.$row['templateid'].'">Remove as '.$row['type'].' template</a>';
							}
							else
							{
								echo 'set_page/'.$row['templateid'].'">Set as '.$row['type'].' template</a>';
							}
							echo '</td><td>';
							echo '<a href="'.base_url().$page['urlname'].'/settemplate/';
							if (isset($default_templates[$row['templateid']]))
							{
								echo 'remove_child/'.$row['templateid'].'">Remove as '.$row['type'].' template for child pages</a>';
							}
							else
							{
								echo 'set_child/'.$row['templateid'].'">Set as '.$row['type'].' template for child pages</a>';
							}
							echo '</td></tr>';
						}
					}
					?>
				</table>
			</div>
			
			<br/>
			<br/>
			
			<div class="forminput">
				<label>Posts use page URL name</label>
				<input name="pagepostname" <?php if (isset($template_in_use->template['templateid'])) echo "disabled";?> id="pagepostname" type="checkbox" class="checkbox" value="1" <?php $default = FALSE; if ($page['pagepostname']) $default = TRUE; echo set_checkbox('pagepostname', '1', $default); ?> />
			</div>

			<?php if (isset($template_in_use->template['templateid'])) echo '<div class="forminput"><span class="error">(can\'t change - setting controlled by page template in use)</span></div>';?>
			
			<br/>
			
			<div class="forminput">
				<input type="submit" value="Save post URL name setting" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			
		</div></div>
	</fieldset>
</form>
