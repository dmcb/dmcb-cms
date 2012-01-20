<form class="collapsible" action="<?=base_url();?><?=$page['urlname'];?>/addtemplates" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('addtemplates');">Add and edit templates</a></legend> 
		
		<div id="addtemplates" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="forminput">
				<label>Templates</label>
				<table>
					<?php
					if (sizeof($templates) == 0)
					{
						echo '<tr><td>No templates exist.</td></tr>';
					}
					else
					{
						$page_templates = FALSE;
						foreach ($templates as $row) 
						{
							if ($row['type'] == "page")
							{
								$page_templates = TRUE;
							}
							else if ($row['type'] == "post" && $page_templates)
							{
								$page_templates = FALSE;
								echo '<tr><td colspan="3">&nbsp;</td></tr>';
							}
						
							echo '<tr class="data"><td>'.ucfirst($row['type']).' template: ';
							echo '<a href="'.base_url().'template/'.$row['templateid'].'" title="'.$row['title'].'">'.$row['title'].'</a></td>';
							echo '</tr>';
						}
					}
					?>
				</table>
			</div>
			
			<br/>
			<br/>
			
			<div class="forminput">
				<label>Title</label>
				<input name="templatetitle" type="text" class="text" maxlength="100" value="<?php echo set_value('templatetitle'); ?>"/>
				<?php echo form_error('templatetitle'); ?>
			</div>
			
			<div class="forminput">
				<label>Type</label>
				<select name="templatetype">
					<option value="page" <?php echo set_select('templatetype', 'page');?>>Page template</option>
					<option value="post" <?php echo set_select('templatetype', 'post');?>>Post template</option>
				</select>
			</div>

			<div class="forminput">
				<input type="submit" value="Add template" name="addtemplate" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			
		</div></div>
	</fieldset>
</form>