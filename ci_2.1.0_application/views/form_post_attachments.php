<form class="collapsible" action="<?=base_url();?>upload/file/post/<?=$post['urlname'];?>" method="post" enctype="multipart/form-data" onsubmit="return dmcb.submit(this);" id="uploadform">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('attachments');">Manage attachments</a></legend>
		
		<div id="attachments" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="sessionid" value="<?php echo $this->session->userdata('session_id'); ?>" class="hidden" />
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<?php 
			foreach ($filegroups as $filegroup)
			{
				if ($filegroup['editable'])
				{
			?>
			
			<div class="forminput">
				<label><?php echo $filegroup['name']; ?></label>
				<table>
					<?php
						foreach ($filegroup['filetypes'] as $filetype) 
						{
							if (!sizeof($filetype['files']) && $filetype['filetypeid'] == NULL)
							{
								echo '<tr><td>No files have been uploaded for this post.</td></tr>';
							}
							else if ($filetype['filetypeid'] != 0 && $filetype['cap'] != '*')
							{	
								echo '<tr><td>'.$filetype['name'].' ('.sizeof($filetype['files']).'/'.$filetype['cap'].')</td></tr>';
							}
						
							foreach ($filetype['files'] as $row) 
							{
								echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" ';
								if ($row['isimage'] == 1)
									echo 'rel="lightbox">';
								else
									echo '>';
								echo '/'.$row['urlpath'].'</a></td>';
								
								if ($filetype['filetypeid'] == NULL)
								{
									if ($row['isimage'] == 1)
									{
										if ($row['fileid'] == $post['imageid']) echo '<td><a href="'.base_url().$post['urlname'].'/attachments/removeimage/'.$row['fileid'].'">Remove as post picture</a></td>';
										else echo '<td><a href="'.base_url().$post['urlname'].'/attachments/setimage/'.$row['fileid'].'">Set as post picture</a></td>';
									}
									else
									{
										echo '<td></td>';
									}
									echo '<td><a href="'.base_url().$post['urlname'].'/attachments/';
									if ($row['listed'] == 1)
										echo 'unlist/'.$row['fileid'].'">Unlist</a>';
									else
										echo 'list/'.$row['fileid'].'">List</a>';
								}
								echo '</td><td><a href="'.base_url().$post['urlname'].'/attachments/rename/'.$row['fileid'].'">Rename</a></td><td><a href="'.base_url().$post['urlname'].'/attachments/delete/'.$row['fileid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this file?\')">Delete</a></td></tr>';
							}
							echo '<tr><td>&nbsp;</td></tr>';
						}
					?>
					<tr><td>&nbsp;</td></tr>
				</table>
			</div>
			
			<?php
				}
			}
			?>
			
			<br/>
			
			<?php
			if (sizeof($stockimages) > 1)
			{
			?>
			<div class="forminput">
				<label>Choose stock image</label>
				<table>
					<?php
						foreach ($stockimages as $row)
						{
							echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" rel="lightbox">/'.$row['urlpath'].'</a></td>';	
							if ($row['fileid'] == $post['imageid'])
							{
								echo '<td><a href="'.base_url().$post['urlname'].'/attachments/removeimage/'.$row['fileid'].'">Remove as post picture</a></td>';
							}
							else
							{
								echo '<td><a href="'.base_url().$post['urlname'].'/attachments/setimage/'.$row['fileid'].'">Set as post picture</a></td>';
							}
							echo '</tr>';
						}
					?>
				</table>
			</div>
			
			<br/>
			<?php
			}
			?>
			
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
				<select name="replace" id="replace" onchange="dmcb.disableUploadAs(this, 'filetype');">
					<option value="">Nothing, it's a new file</option>
					<?php
					foreach ($files as $file) 
					{
						echo '<option value="'.$file['fileid'].'">'.$file['filename'].'.'.$file['extension'].'</option>';
					}
					?>
				</select>
			</div>
			
			<?php
			if (sizeof($filegroups) != 1 || !isset($filegroups[0]))
			{
			?>
			<div class="forminput">
				<label>Upload as</label>
				<select name="filetype" id="filetype">
					<?php
					foreach ($filegroups as $filegroup)
					{
						if ($filegroup['editable'])
						{
							foreach ($filegroup['filetypes'] as $filetype)
							{
								echo '<option value="'.$filetype['filetypeid'].'">';
								if (sizeof($filegroup['filetypes']) == 1)
								{
									echo $filetype['name'];
								}
								else
								{
									echo $filegroup['name'].': '.$filetype['name'].' ('.sizeof($filetype['files']).'/'.$filetype['cap'].')';
								}
								echo '</option>';
							}
						}
					}
					?>
				</select>
			</div>
			<?php
			}
			?>
			
			<div class="forminput">
				<input type="submit" value="Upload" name="uploadfile" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);" id="btnSubmit"/>
			</div>
		</div></div>
	</fieldset>
</form>
