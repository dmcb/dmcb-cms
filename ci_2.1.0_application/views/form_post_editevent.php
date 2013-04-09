<form class="collapsible" action="<?php echo base_url();?><?php echo $post['urlname'];?>/editevent" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('editevent');">Attach an event to this post</a></legend>
		
		<div id="editevent" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />
			
			<div class="formnotes full">
				<p>All information about the event is optional except the date.</p>
			</div>
			
			<div class="forminput">
				<label>Date (YYYYMMDD)</label>
				<input name="eventdate" type="text" class="text" maxlength="8" value="<?php $default = NULL; if (isset($post['event']['date'])) $default = date('Ymd',strtotime($post['event']['date'])); echo set_value('eventdate', $default); ?>"/>
				<img alt="Calendar" onclick="new CalendarDateSelect( $(this).previous(), {year_range:10} );" src="<?php echo base_url();?>assets/images/calendar.gif" style="border:0px; cursor:pointer;" />
				<?php echo form_error('eventdate'); ?>
			</div>
			
			<div class="forminput">
				<label>Time (HH:MM, 24 hr clock)</label>
				<input name="eventtime" type="text" class="text" maxlength="5" value="<?php $default = NULL; if (isset($post['event']['time'])) $default = date('G:i',strtotime($post['event']['time'])); echo set_value('eventtime', $default); ?>"/>
				<?php echo form_error('eventtime'); ?>
			</div>
			
			<br/>
			
			<div class="forminput">
				<label>End date (YYYYMMDD)</label>
				<input name="eventenddate" type="text" class="text" maxlength="8" value="<?php $default = NULL; if (isset($post['event']['enddate'])) $default = date('Ymd',strtotime($post['event']['enddate'])); echo set_value('eventenddate', $default); ?>"/>
				<img alt="Calendar" onclick="new CalendarDateSelect( $(this).previous(), {year_range:10} );" src="<?php echo base_url();?>assets/images/calendar.gif" style="border:0px; cursor:pointer;" />
				<?php echo form_error('eventenddate'); ?>
			</div>
			
			<div class="forminput">
				<label>End time (HH:MM, 24 hr clock)</label>
				<input name="eventendtime" type="text" class="text" maxlength="5" value="<?php $default = NULL; if (isset($post['event']['endtime'])) $default = date('G:i',strtotime($post['event']['endtime'])); echo set_value('eventendtime', $default); ?>"/>
				<?php echo form_error('eventendtime'); ?>
			</div>
			
			<br/>
			
			<div class="forminput">
				<label>Location</label>
				<input name="eventlocation" type="text" class="text" maxlength="50" value="<?php echo set_value('eventlocation', $post['event']['location']); ?>"/>
				<?php echo form_error('eventlocation'); ?>
			</div>
			
			<div class="forminput">
				<label>Address</label>
				<input name="eventaddress" type="text" class="text" maxlength="150" value="<?php echo set_value('eventaddress', $post['event']['address']); ?>"/>
				<?php echo form_error('eventaddress'); ?>
			</div>

			<?php 
			if ($post['event'] == NULL) { 
			?>
			<div class="forminput">
				<input type="submit" value="Add event" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			<?php 
			} 
			else {
			?>
			<div class="forminput">
				<input type="submit" value="Update event" name="save" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
			<div class="forminput">
				<input type="button" value="Delete event" class="button" onclick="return dmcb.confirmationLink('Are you sure you wish to delete this event?','<?php echo base_url();?><?php echo $post['urlname'];?>/editevent/delete')"/>
			</div>
			<?php
			}
			?>
		</div></div>
	</fieldset>
</form>
