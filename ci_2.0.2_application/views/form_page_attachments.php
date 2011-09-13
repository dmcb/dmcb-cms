<form class="collapsible" action="<?=base_url();?>upload/file/page/<?=$page['urlname'];?>" method="post" enctype="multipart/form-data" onsubmit="return dmcb.submit(this);" id="uploadform">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('attachments');">Manage attachments</a></legend>
		
		<div id="attachments" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="sessionid" value="<?php echo $this->session->userdata('session_id'); ?>" class="hidden" />
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="forminput">
				<label>Attachments</label>
				<table>
					<?php
					if (sizeof($files) == 0)
						echo '<tr><td>No files been uploaded for this page.</td></tr>';
					else
						foreach ($files as $row) {
							echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" ';
							if ($row['isimage'] == 1)
								echo 'rel="lightbox">';
							else
								echo '>';
							echo '/'.$row['urlpath'].'</a></td>';
							if ($row['isimage'] == 1)
							{
								if ($row['fileid'] == $page['imageid']) echo '<td><a href="'.base_url().$page['urlname'].'/attachments/removeimage/'.$row['fileid'].'">Remove as page picture</a></td>';
								else echo '<td><a href="'.base_url().$page['urlname'].'/attachments/setimage/'.$row['fileid'].'">Set as page picture</a></td>';
							}
							else
							{
								echo '<td></td>';
							}
							echo '<td><a href="'.base_url().$page['urlname'].'/attachments/';
							if ($row['listed'] == 1)
								echo 'unlist/'.$row['fileid'].'">Unlist</a>';
							else
								echo 'list/'.$row['fileid'].'">List</a>';
							echo '</td><td><a href="'.base_url().$page['urlname'].'/attachments/rename/'.$row['fileid'].'">Rename</a></td><td><a href="'.base_url().$page['urlname'].'/attachments/delete/'.$row['fileid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this file?\')">Delete</a></td></tr>';
						}
					?>
				</table>
			</div>
			
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
							if ($row['fileid'] == $page['imageid'])
							{
								echo '<td><a href="'.base_url().$page['urlname'].'/attachments/removeimage/'.$row['fileid'].'">Remove as page picture</a></td>';
							}
							else
							{
								echo '<td><a href="'.base_url().$page['urlname'].'/attachments/setimage/'.$row['fileid'].'">Set as page picture</a></td>';
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
