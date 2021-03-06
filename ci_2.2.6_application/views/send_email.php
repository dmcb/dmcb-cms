<div class="fullcolumn">
	<h3>Your email will be sent to the following users</h3>
	<p>
	<?php
		$userids = "";
		foreach ($maillist as $user)
		{
			$userids .= $user['userid'].';';
			if ($user['mailinglist'])
			{
				echo '<span>'.$user['displayname'].' ('.$user['email'].')</span><br/>';
			}
			else
			{
				echo '<span class="restricted">'.$user['displayname'].' ('.$user['email'].') opted out of the mailing list, no email will be sent</span><br/>';
			}
		}
	?>
	</p>
	<br/>
	<p><a href="<?php echo base_url().'manage_users/mailinglist';?>">Assemble a different list</a></p>

	<div class="spacer">&nbsp;</div>

	<form action="<?php echo base_url();?>upload/file/<?php echo $upload_url;?>" method="post" enctype="multipart/form-data" onsubmit="return dmcb.submit(this);" id="uploadform">
		<fieldset>
			<legend>Add attachments to email</legend>

			<div id="attachments" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="sessionid" value="<?php echo $this->session->userdata('session_id'); ?>" class="hidden" />
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Attachments</label>
					<table>
						<?php
						if (sizeof($files) == 0)
							echo '<tr><td>No files have been attached to this email.</td></tr>';
						else
							foreach ($files as $file) {
								echo '<tr class="data"><td>'.$file.'</td>';
								echo '<td><a href="'.base_url().'manage_users/email/delete/'.$file.'" onclick="return dmcb.confirmation(\'Are you sure you wish to remove this attachment?\')">Remove attachment from email</a></td></tr>';
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
					<input type="submit" value="Upload" name="uploadfile" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);" id="btnSubmit"/>
				</div>
			</div></div>
		</fieldset>
	</form>

	<form action="<?php echo base_url();?>manage_users/email" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Email body</legend>

			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				<input type="hidden" name="maillist" value="<?php echo $userids;?>" class="hidden" />

				<div class="forminput">
					<label>Send yourself a copy</label>
					<input name="personalcopy" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('personalcopy', '1', true); ?> />
					<?php echo form_error('personalcopy'); ?>
				</div>

				<div class="forminput">
					<label>Include signature</label>
					<input name="usesignature" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('usesignature', '1', true); ?> />
					<?php echo form_error('personalcopy'); ?>
				</div>

				<div class="forminput">
					<label>Subject</label>
					<input name="emailsubject" type="text" class="text" maxlength="50" value="<?php echo set_value('emailsubject'); ?>"/>
					<?php echo form_error('emailsubject'); ?>
				</div>

				<div class="forminput">
					<label>Message</label>
					<textarea name="emailmessage" rows="" cols=""><?php echo set_value('emailmessage'); ?></textarea>
					<?php echo form_error('emailmessage'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Send email" name="sendmail" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>

	<form action="<?php echo base_url();?>manage_users/email/signature" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend>Email signature</legend>

			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="formnotes">
					<p>If you'd like a signature to appear on your emails, please write one below. This signature will persist for subsequent emails.</p>
				</div>

				<div class="forminput">
					<label>Signature</label>
					<textarea name="signature" rows="" cols=""><?php echo set_value('signature', $signature); ?></textarea>
					<?php echo form_error('signature'); ?>
				</div>

				<div class="forminput">
					<input type="submit" value="Update signature" name="updatesignature" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>

</div>
