	<form class="collapsible" action="<?=base_url();?>manage_users/mailinglist" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<legend><a href="javascript:Effect.Combo('mailinglist');">Send email to mailing list</a></legend>

			<div id="mailinglist" class="panel"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />

				<div class="forminput">
					<label>Send to</label>
					<input name="sendto_all" id="sendto_all" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('sendto_all', '1'); ?>/>
					All users on the mailing list
				</div>

				<br/>

				<?php
				foreach ($userroles->result_array() as $role)
				{
					if ($memberrole == $role['roleid'])
					{
						foreach ($userstatus->result_array() as $status)
						{
							$id = 'sendto_'.$role['roleid'].'_'.$status['status'];
							echo '
								<div class="forminput">
									<label>&nbsp;</label>

									<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
									'.$status['status'].' '.$role['role'].'s
								</div>';
						}
					}
					else
					{
						$id = 'sendto_'.$role['roleid'];
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								'.ucfirst($role['role']).'s
							</div>';
					}
				}

				if (isset($subscription_types))
				{
					echo '<br/>';

					foreach ($subscription_types->result_array() as $subscription_type)
					{
						$id = 'sendto_subscribers_'.$subscription_type['typeid'];
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								'.ucfirst($subscription_type['type']).' subscribers
							</div>';
						$id = 'sendto_subscribers_'.$subscription_type['typeid'].'_expired';
						echo '
							<div class="forminput">
								<label>&nbsp;</label>

								<input name="'.$id.'" id="'.$id.'" type="checkbox" class="checkbox" value="1" '.set_checkbox($id, '1').' />
								Expired '.strtolower($subscription_type['type']).' subscribers
							</div>';

					}
				?>

				<div class="forminput">
					<label>&nbsp;</label>
					<input name="sendto_subscribers_none" id="sendto_subscribers_none" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('sendto_subscribers_none', '1'); ?>/>
					Non subscribers
				</div>

				<?php
				}
				?>

				<br/>

				<div class="forminput">
					<input type="submit" value="Compose email" name="sendmail" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			</div></div>
		</fieldset>
	</form>