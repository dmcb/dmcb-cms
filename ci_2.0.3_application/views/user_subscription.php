<div class="fullcolumn">
	<p>Please set the subscription for <?php echo $user['displayname'];?>.</p>
	<form action="<?=current_url();?>" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<div class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Subscription type</label>
					<select name="subscribetype">
						<option value="">None</option>
						<?php
							foreach ($subscriptiontypes->result_array() as $subscriptiontype)
							{
								echo '<option value="'.$subscriptiontype['typeid'].'" ';
								if ($subscription['typeid'] == $subscriptiontype['typeid']) echo 'selected="selected"';
								echo '>'.$subscriptiontype['type'].'</option>';
							}
						?>
					</select>
				</div>
				
				<div class="forminput">
					<label>End Date (YYYYMMDD)</label>
					<input name="subscribedate" type="text" class="text" maxlength="8" value="<?php $default = NULL; if (isset($subscription['date'])) $default = date('Ymd',strtotime($subscription['date'])); echo set_value('subscribedate', $default); ?>"/>
					<img alt="Calendar" onclick="new CalendarDateSelect( $(this).previous(), {year_range:10} );" src="<?=base_url();?>includes/images/calendar.gif" style="border:0px; cursor:pointer;" />
					<?php echo form_error('subscribedate'); ?>
				</div>
				
				<div class="forminput">
					<input type="submit" value="Set subscription" name="set" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>
</div>