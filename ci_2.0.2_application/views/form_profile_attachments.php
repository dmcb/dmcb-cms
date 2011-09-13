<form class="collapsible" action="<?=base_url();?>upload/file/<?=$upload_url;?>" method="post" enctype="multipart/form-data" onsubmit="return dmcb.submit(this);" id="uploadform">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('attachments');">Manage profile pictures</a></legend>
		
		<div id="attachments" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="sessionid" value="<?php echo $this->session->userdata('session_id'); ?>" class="hidden" />
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="formnotes full">
				<p>You can upload photos to use as an avatar and profile picture on the site (your profile picture will only show if you've added a profile).</p>
				<p>If you don't set a photo, <?=$this->config->item('dmcb_title');?> will try to use any <a href="http://www.gravatar.com/">Gravatars</a> you may have.</p>
			</div>
			
			<div class="forminput">
				<label>Pictures</label>
				<table>
					<?php
					if (sizeof($files) == 0)
						echo '<tr><td>No pictures been uploaded.</td></tr>';
					else
						foreach ($files as $row) {
							echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" ';
							if ($row['isimage'] == 1)
								echo 'rel="lightbox">';
							else
								echo '>';
							echo $row['filename'].'.'.$row['extension'].'</a></td>';
							if ($row['isimage'] == 1)
							{
								if ($row['fileid'] == $user['profilepicture']) echo '<td><a href="'.base_url().'profile/'.$user['urlname'].'/attachments/removeimage/'.$row['fileid'].'">Remove as profile picture</a></td>';
								else echo '<td><a href="'.base_url().'profile/'.$user['urlname'].'/attachments/setimage/'.$row['fileid'].'">Set as profile picture</a></td>';
							}
							else
							{
								echo '<td></td>';
							}
							echo '<td><a href="'.base_url().'profile/'.$user['urlname'].'/attachments/rename/'.$row['fileid'].'">Rename</a></td><td><a href="'.base_url().'profile/'.$user['urlname'].'/attachments/delete/'.$row['fileid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this file?\')">Delete</a></td></tr>';
						}
					?>
				</table>
			</div>
			
			<br/>
			
			<div class="forminput">
				<label>Upload a picture</label>
			
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
