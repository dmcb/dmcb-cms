<div class="fullcolumn">
	<table>

	<?php
		for ($i=0; $i<sizeof($menusections); $i++) {
			$menutype = $menutypes->row_array($i);
			if (sizeof($menusections[$i]) > 0)
			{
				echo '<tr><td colspan="7"><h2>'.$menutype['name'].'</h2></td></tr>';
				
				for ($j=0; $j<sizeof($menusections[$i]); $j++) {
					echo '<tr class="data"><td>';
					// Menu padding hierarchy
					for ($k=0; $k<$menusections[$i][$j]['level']; $k++)
					{
						echo '&nbsp;&nbsp;&nbsp;';
					}
					
					// If page has a URL name instead of it being an external URL or menu place holder, show editing link
					if (isset($menusections[$i][$j]['urlname']))
					{
						echo '<a href="'.base_url().$menusections[$i][$j]['urlname'].'/editpage">'.$menusections[$i][$j]['title'].'</a>';
					}
					else if (isset($menusections[$i][$j]['link']) && substr($menusections[$i][$j]['link'], 0, 1) == "/")
					{
						echo 'Internal link: '.$menusections[$i][$j]['title'];
					}
					else if (isset($menusections[$i][$j]['link']))
					{
						echo 'External link: '.$menusections[$i][$j]['title'];
					}
					else
					{
						echo 'Menu placeholder: '.$menusections[$i][$j]['title'];
					}
					
					echo '</td>';
					echo '<td><a href="'.base_url().'manage_pages/move_up/'.$menusections[$i][$j]['pageid'].'">Up</a></td>';
					echo '<td><a href="'.base_url().'manage_pages/move_down/'.$menusections[$i][$j]['pageid'].'">Down</a></td>';

					echo '<td><select onchange="dmcb.goto(this)" class="wide">';
					for ($k=0; $k<sizeof($menusections); $k++) 
					{
						$menutype = $menutypes->row_array($k);
						if ($menusections[$i][$j]['pageof'] == NULL && $menusections[$i][$j]['menu'] == $menutype['menu'])
						{
							echo '<option value="'.base_url().'manage_pages/move/'.$menusections[$i][$j]['pageid'].'/'.$menutype['menu'].'" selected="selected">Under Menu - '.$menutype['name'].'</option>';
						}
						else echo '<option value="'.base_url().'manage_pages/move/'.$menusections[$i][$j]['pageid'].'/'.$menutype['menu'].'">Under Menu - '.$menutype['name'].'</option>';
						
						foreach ($menusections[$k] as $menupage) 
						{
							if ($menusections[$i][$j]['pageid'] != $menupage['pageid'])
							{
								if ($menusections[$i][$j]['pageof'] == $menupage['pageid'])
								{
									echo '<option value="'.base_url().'manage_pages/move/'.$menusections[$i][$j]['pageid'].'/'.$menupage['pageid'].'" selected="selected">';
								}
								else echo '<option value="'.base_url().'manage_pages/move/'.$menusections[$i][$j]['pageid'].'/'.$menupage['pageid'].'">';
								echo 'Under '.$menupage['title'].'</option>';
							}
						}
					}
					echo '</select></td>';
					
					if ($menusections[$i][$j]['link'] != NULL && substr($menusections[$i][$j]['link'], 0, 1) == "/")
					{
						echo '<td>-</td>';
					}
					else if ($menusections[$i][$j]['protected']) 
					{
						echo '<td><a href="'.base_url().'manage_pages/permissions/'.$menusections[$i][$j]['pageid'].'">Edit protection</a></td>';
					}
					else
					{
						echo '<td><a href="'.base_url().'manage_pages/permissions/'.$menusections[$i][$j]['pageid'].'">Protect</a></td>';
					}
					if ($menusections[$i][$j]['published']) 
					{
						echo '<td><a href="'.base_url().'manage_pages/unpublish/'.$menusections[$i][$j]['pageid'].'">Unpublish</a></td>';
					}
					else
					{
						echo '<td><a href="'.base_url().'manage_pages/publish/'.$menusections[$i][$j]['pageid'].'">Publish</a></td>';
					}
					
					if ($j+1<sizeof($menusections[$i])) 
					{
						if ($menusections[$i][$j+1]['level'] <= $menusections[$i][$j]['level'])
							echo '<td><a href="'.base_url().'manage_pages/delete/'.$menusections[$i][$j]['pageid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this page?\')">Delete</a></td>';
						else
							echo '<td></td>';
					}
					else
					{
						echo '<td><a href="'.base_url().'manage_pages/delete/'.$menusections[$i][$j]['pageid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this page?\')">Delete</a></td>';
					}
					echo '</tr>';
				}
				echo '<tr><td colspan="8"><br/></td></tr>';
			}
		}
	?>

	</table>

	<div class="spacer">&nbsp;</div>

	<form class="collapsible" action="<?php echo base_url();?>manage_pages/addpage" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('addpage');">Add a new page / menu item</a></legend>
			
			<div id="addpage" class="panel" ><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="forminput">
					<label>Title</label>
					<input name="title" type="text" class="text" maxlength="50" onkeypress="dmcb.toUrlname(this.form, this.value, 'urlname')" onkeyup="dmcb.toUrlname(this.form, this.value, 'urlname')" value="<?php echo set_value('title'); ?>"/>
					<?php echo form_error('title'); ?>
				</div>
				
				<div class="formnotes full">
					<p>The URL name is how your page will be addressed on the web.<br/>If you fill in a URL to link to (either a link on the site or an external link), the menu item will not be it's own page but a link to another.<br/>If you leave both fields empty, the menu item will be a place holder ideal for child pages.</p>
				</div>
				
				<div class="forminput">
					<label>URL name</label>
					<input name="urlname" type="text" class="text" maxlength="50" value="<?php echo set_value('urlname'); ?>"/>
					or URL to link to
					<?php echo form_error('urlname'); ?>
					<input name="link" type="text" class="text sameline" maxlength="150" value="<?php echo set_value('link'); ?>"/>
					<?php echo form_error('link'); ?>
				</div>
				
				<div class="forminput">
					<label>Appears under</label>
					<select name="pageof">
						<?php
							for ($i=0; $i<sizeof($menusections); $i++) 
							{
								$menutype = $menutypes->row_array($i);
								echo '<option value="'.$menutype['menu'].'" '.set_select('pageof', $menutype['menu']).' >Menu - '.$menutype['name'].'</option>';
								
								foreach ($menusections[$i] as $menupage) 
								{
									echo '<option value="'.$menupage['pageid'].'" '.set_select('pageof', $menupage['pageid']).' >';
									for ($j=0; $j<$menupage['level']; $j++) echo '&nbsp;&nbsp;&nbsp;';
									echo $menupage['title'].'</option>';
								}
							}
						?>
					</select>
				</div>
				
				<div class="formnotes full">
					<p>Choose to nest a URL if the page appears under another page and you want a URL like <?php echo base_url();?>parent/child instead of <?php echo base_url();?>child</p>
				</div>
				
				<div class="forminput">
					<label>Nested URL</label>
					<input name="nestedurl" id="nestedurl" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('nestedurl', '1');?> />
				</div>

				<div class="forminput">
					<input type="submit" value="Add page" name="addpage" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>							
	</form>
</div>
