<div class="fullcolumn">
	<table>

	<?php
		echo 'Sort users by: ';
		if ($sort == "by_role")
		{
			echo '<b>role</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_role">role</a>';
		}
		echo ' | ';
		if ($sort == "by_status")
		{
			echo '<b>status</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_status">status</a>';
		}
		echo ' | ';
		if ($sort == "by_signon")
		{
			echo '<b>last sign on</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_signon">last sign on</a>';
		}
		echo ' | ';
		if ($sort == "by_registration")
		{
			echo '<b>registration date</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_registration">registration date</a>';
		}
		echo ' | ';
		if ($sort == "by_name")
		{
			echo '<b>name</b>';
		}
		else
		{
			echo '<a href="'.base_url().'manage_users/by_name">name</a>';
		}
		if ($this->acl->enabled('site', 'subscribe'))
		{
			echo ' | ';
			if ($sort == "by_subscription")
			{
				echo '<b>subscription</b>';
			}
			else
			{
				echo '<a href="'.base_url().'manage_users/by_subscription">subscription</a>';
			}
		}
		echo '<br/><br/>';

		foreach ($userlist as $key => $value)
		{
			echo '<tr><td colspan="6"><h2>'.ucfirst($key).'</h2></td></tr>';

			foreach ($value as $user)
			{
				echo '<tr class="data"><td>';
				if ($set_password)
				{
					echo '<a href="'.base_url().'account/'.$user['urlname'].'">'.$user['displayname'].'</a>';
				}
				else
				{
					echo '<a href="'.base_url().'manage_users/report/'.$user['userid'].'">'.$user['displayname'].'</a>';
				}

				if (substr($user['plusminus'], 0, 1) != '0')
				{
					if (substr($user['plusminus'], 0, 1) == '-')
					{
						echo ' <span class="red">'.$user['plusminus'].'</span>';
					}
					else
					{
						echo ' <span class="green">+'.$user['plusminus'].'</span>';
					}
				}

				echo '</td>';
				if ($sort == "by_signon") // Sorting by last sign on shows date of last sign on
				{
					if ($user['lastsignon'] == "0000-00-00 00:00:00")
					{
						echo '<td>&nbsp;</td>';
					}
					else
					{
						echo '<td>Last sign on: '.date("F jS, Y", strtotime($user['lastsignon'])).'</td>';
					}
				}
				else if ($sort == "by_registration") // Sorting by registration date shows registration date
				{
					if ($user['registered'] == "0000-00-00 00:00:00")
					{
						echo '<td>&nbsp;</td>';
					}
					else
					{
						echo '<td>Registered: '.date("F jS, Y", strtotime($user['registered'])).'</td>';
					}
				}
				else if ($sort == "by_subscription") // Sorting by subscription shows expiry date if applicable
				{
					echo '<td>';
					if (isset($user['subscriptiondate']))
					{
						if (strtotime($user['subscriptiondate']) < time())
						{
							echo 'Expired on '.date("F jS, Y", strtotime($user['subscriptiondate']));
						}
						else
						{
							echo 'Expires on '.date("F jS, Y", strtotime($user['subscriptiondate']));
						}
					}
					echo '</td>';
				}
				else
				{
					echo '<td>'.$user['email'].'</td>';
				}

				if ($change_role)
				{
					echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
					foreach ($userroles->result_array() as $role)
					{
						if ($role['roleid'] == $user['roleid'])
						{
							echo '<option value="'.base_url().'manage_users/set_role/'.$user['userid'].'/'.$role['roleid'].'" selected="selected">'.ucfirst($role['role']).'</option>';
						}
						else
						{
							echo '<option value="'.base_url().'manage_users/set_role/'.$user['userid'].'/'.$role['roleid'].'">'.ucfirst($role['role']).'</option>';
						}
					}
					echo '</select></td>';
				}

				if ($change_status)
				{
					echo '<td><select onchange="dmcb.goto(this)" class="narrow">';
					foreach ($userstatus->result_array() as $status)
					{
						if ($status['statusid'] == $user['statusid'])
						{
							echo '<option value="'.base_url().'manage_users/set_status/'.$user['userid'].'/'.$status['statusid'].'" selected="selected">'.$status['status'].'</option>';
						}
						else
						{
							echo '<option value="'.base_url().'manage_users/set_status/'.$user['userid'].'/'.$status['statusid'].'">'.$status['status'].'</option>';
						}
					}
					echo '</select></td>';
				}

				if ($this->acl->enabled('site', 'subscribe') && $set_subscription)
				{
					echo '<td><a href="'.base_url().'manage_users/subscription/'.$user['userid'].'">Set subscription</a></td>';
				}

				if (isset($this->session->userdata['signedon']) && $this->session->userdata('userid') == 1)
				{
					echo '<td><a href="'.base_url().'manage_users/delete/'.$user['userid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this user?\')">Delete</a></td>';
				}
			}
			echo '</tr><tr><td colspan="7"><br/></td></tr>';
		}
	?>

	</table>

	<?php echo $this->pagination->create_links();?>

	<div class="spacer">&nbsp;</div>

	<?php
		if (isset($add_user)) echo $add_user;
		if (isset($mailing_list)) echo $mailing_list;
	?>

</div>
