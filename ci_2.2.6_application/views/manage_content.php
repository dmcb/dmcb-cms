<div class="fullcolumn">
	<h2>Site-wide content</h2>
	
	<div class="spacer">&nbsp;</div>
	
	<form class="collapsible" action="<?php echo base_url();?>upload/file/site" method="post" enctype="multipart/form-data" onsubmit="return dmcb.submit(this);" id="uploadform">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('attachments');">Stock photos and attachments</a></legend>
			
			<div id="attachments" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="sessionid" value="<?php echo $this->session->userdata('session_id'); ?>" class="hidden" />
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="forminput">
					<label>Attachments</label>
					<table>
						<?php
						if (sizeof($files) == 0)
							echo '<tr><td>No site-wide files been uploaded.</td></tr>';
						else
							foreach ($files as $row) 
							{
								echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" ';
								if ($row['isimage'] == 1)
									echo 'rel="lightbox">';
								else
									echo '>';
								echo '/'.$row['urlpath'].'</a></td>';
								echo '<td>';
								if ($row['isimage'] == 1)
								{
									echo '<a href="'.base_url().'manage_content/attachments/';
									if (isset($stockimages[$row['fileid']]))
										echo 'removestock/'.$row['fileid'].'">Remove as stock image</a>';
									else
										echo 'setstock/'.$row['fileid'].'">Set as stock image</a>';
								}
								echo '</td><td><a href="'.base_url().'manage_content/attachments/rename/'.$row['fileid'].'">Rename</a></td><td><a href="'.base_url().'manage_content/attachments/delete/'.$row['fileid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this file?\')">Delete</a></td></tr>';
							}
						?>
					</table>
				</div>
				
				<br/>
				
				<div class="forminput">
					<label>Upload a file</label>
				
					<noscript>
					<input type="file" name="Filedata" class="file" />
					</noscript>
					
					<script type="text/javascript">
					document.writeln('<input type="text" class="text" id="txtFileName" disabled="disabled" />');
					document.writeln('<span id="spanButtonPlaceholder"></span>');
					
					document.writeln('<div class="flash" id="fsUploadProgress">');
					document.writeln('</div>');
					document.writeln('<input type="hidden" name="hidFileID" id="hidFileID" value="" />');
					</script>
				</div>
				
				<div class="forminput">
					<label>Replace</label>
					<select name="replace" id="replace">
						<option value="">Nothing, it's a new file</option>
						<?php
						foreach ($files as $file) {
							echo '<option value="'.$file['fileid'].'">'.$file['filename'].'.'.$file['extension'].'</option>';
						}
						?>
					</select>
				</div>
				
				<div class="forminput">
					<input type="submit" value="Upload" name="uploadfile" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);" id="btnSubmit"/>
				</div>
			</div></div>
		</fieldset>
	</form>
			
	<form class="collapsible" action="<?php echo base_url();?>manage_content/blocks" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('blocks');">Add and edit blocks</a></legend>
			
			<div id="blocks" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Blocks</label>
					<table>
						<?php
							foreach ($blocks as $block) 
							{
								echo '<tr class="data"><td><a href="'.base_url().'block/'.$block['blockinstanceid'].'/edit">'.$block['title'].'</a></td>';
								echo '<td>';
								if ($block['parent']['paginationpossible'] == 1 && isset($default_blocks[$block['blockinstanceid'].'pagination']))
								{
									echo '<a href="'.base_url().'manage_content/blocks/removepagination/'.$block['blockinstanceid'].'">Remove as default site pagination</a>';
								}
								else if ($block['parent']['paginationpossible'] == 1)
								{
									echo '<a href="'.base_url().'manage_content/blocks/setpagination/'.$block['blockinstanceid'].'">Set as default site pagination</a>';
								}
								echo '</td><td>';
								if ($block['parent']['rsspossible'] == 1 && isset($default_blocks[$block['blockinstanceid'].'rss']))
								{
									echo '<a href="'.base_url().'manage_content/blocks/removerss/'.$block['blockinstanceid'].'">Remove as default site rss</a>';
								}
								else if ($block['parent']['rsspossible'] == 1)
								{
									echo '<a href="'.base_url().'manage_content/blocks/setrss/'.$block['blockinstanceid'].'">Set as default site rss</a>';
								}
								echo '</td>';								
							}
						?>
					</table>
				</div>
				
				<br/>
				
				<div class="formnotes">
					<p>Add a block to embed dynamic elements in the page like news post listings, twitter feeds, etc.</p>
				</div>
				
				<div class="forminput">
					<label>Title</label>
					<input name="blocktitle" type="text" class="text" maxlength="20" value="<?php echo set_value('blocktitle'); ?>"/>
					<?php echo form_error('blocktitle'); ?>
				</div>
				
				<div class="forminput">
					<label>Function</label>
					<select name="blockfunction">
					<?php
						foreach ($functions->result_array() as $function) 
						{
							echo '<option value="'.$function['function'].'" '.set_select('blockfunction', $function['function']).' >'.$function['name'].'</option>';
						}
					?>
					</select>
				</div>

				<div class="forminput">
					<input type="submit" value="Add block" name="addblock" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			
			</div></div>
		</fieldset>
	</form>
	
	<form class="collapsible" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('manageblocks');">Enable a block</a></legend>
			
			<div id="manageblocks" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="forminput">
					<label>Enabled blocks</label>
					<table>
						<?php
						if (sizeof($functions) == 0)
						{
							echo '<tr><td>No blocks are enabled.</td></tr>';
						}
						else
						{
							foreach ($functions->result_array() as $function) 
							{
								echo '<tr class="data"><td>'.$function['name'].'</td>';
								echo '<td><a href="'.base_url().'manage_content/manageblocks/disable/'.$function['function'].'">Disable</a></td></tr>';
							}
						}
						?>
					</table>
				</div>
				
				<div class="formnotes">
					<p>Any blocks developed for this site that aren't already enabled can be done so here.</p>
				</div>
				
				<div class="forminput">
					<label>Block name</label>
					<select onchange="dmcb.goto(this)">
						<option value="">Choose a block</option>
						<?php
							foreach ($availablefunctions->result_array() as $block)
							{
								echo '<option value="'.base_url().'manage_content/manageblocks/enable/'.$block['function'].'">'.$block['name'].'</option>';
							}
						?>
					</select>
				</div>

			</div></div>
		</fieldset>
	</form>
	
	<form class="collapsible" action="<?php echo base_url();?>manage_content/templates" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('templates');">Add and edit templates</a></legend>
			
			<div id="templates" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<div class="forminput">
					<label>Templates</label>
					<table>
						<?php
						if (sizeof($templates) == 0)
						{
							echo '<tr><td>No site-wide templates exist.</td></tr>';
						}
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
							
								echo '<tr class="data"><td>';
								echo '<a href="'.base_url().'template/'.$row['templateid'].'" title="'.$row['title'].'">'.$row['title'].'</a></td>';
								echo '<td>';

								echo '<a href="'.base_url().'manage_content/templates/';
								if (isset($default_templates[$row['templateid']]))
								{
									echo 'removedefault/'.$row['templateid'].'">Remove as default site '.$row['type'].' template</a>';
								}
								else
								{
									echo 'setdefault/'.$row['templateid'].'">Set as default site '.$row['type'].' template</a>';
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
</div>
