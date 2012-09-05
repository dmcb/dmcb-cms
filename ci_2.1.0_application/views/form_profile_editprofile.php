<form class="collapsible" action="<?php echo base_url();?>profile/<?php echo $this->uri->segment(2);?>/editprofile" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editprofile');">Edit your profile</a></legend>

		<div id="editprofile" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="formnotes full">
				<p>Here's where you can write whatever you want about yourself.  You have complete freedom!  You can give as much or as little information about yourself as you'd like.</p>
				<p>If you don't write up a profile, any profile picture you set for yourself won't be seen.</p>
			</div>

			<div class="forminput">
				<label>Profile</label>
				<textarea name="profile" rows="" cols=""><?php echo html_entity_decode(set_value('profile', str_replace("<br/>","\n",$user['profile']))); ?></textarea>
				<?php echo form_error('profile'); ?>
			</div>

			<div class="forminput">
				<input type="submit" value="Submit changes" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
