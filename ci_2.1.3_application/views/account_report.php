<?php if (!$self_editing) { ?>
<div class="account-information">
	<h2>Account information for <?php echo $user['displayname'];?></h2>
	<span class="email"><a href="<?php echo base_url();?>manage_users/email"><?php echo $user['email'];?></a></span>
	<ul class="stats">
	<?php
		if ($user['lastsignon'] != "0000-00-00 00:00:00")
		{
			echo '<li>Last sign on: '.date("F jS, Y", strtotime($user['lastsignon'])).'</li>';
		}

		if ($user['registered'] != "0000-00-00 00:00:00")
		{
			echo '<li>Registered: '.date("F jS, Y", strtotime($user['registered'])).'</li>';
		}

		if (isset($subscription))
		{
			echo '<li>User has '.strtolower($subscription['type']).' subscription that ';
			if (strtotime($subscription['date']) < time())
			{
				echo 'expired on '.date("F jS, Y", strtotime($subscription['date'])).'</li>';
			}
			else
			{
				echo 'expires on '.date("F jS, Y", strtotime($subscription['date'])).'</li>';
			}
		}

		if ($user['enabledprofile'])
		{
			echo '<li><a href="'.base_url().'profile/'.$user['urlname'].'">Visit profile</a></li>';
		}
	?>
	</ul>
</div>

<?php } if (sizeof($privileges) > 0) { ?>
<div class="account-privileges">
	<?php
	if (!$change_permissions)
	{
		echo '<h2>Site privileges</h2>';
		echo '<ul class="privileges">';
		foreach ($privileges as $domain => $privilege)
		{
			if ($domain == "site")
			{
				echo '<li>Site-wide '.$roles_table[$privilege].'</li>';
			}
			else
			{
				foreach ($privilege as $privilege_on)
				{
					echo '<li>'.ucfirst($roles_table[$privilege_on['role']]).' on <a href="'.base_url().$privilege_on[$domain]['urlname'].'">'.$privilege_on[$domain]['title'].'</a></li>';
				}
			}
		}
		echo '</ul>';
	} ?>
<div>

<php } if (!$self_editing) { ?>
<div class="moderating-report">
	<h2>Moderating report</h2>
	<?php
		if ($moderations->num_rows() == 0)
		{
			echo '<span class="no-activity">No moderating activity against this user was found.</span>';
		}
		else
		{
			if ($moderations->num_rows() >= 10)
			{
				echo '<span class="activity">Most recent entries listed</span>';
			}
			echo '<ul class="moderations">';
			foreach ($moderations->result_array() as $moderation)
			{
				if ($moderation['actionon'] == "user")
				{
					echo '<li><span class="date">'.$moderation['date'].'</span>: User '.$moderation['action'];
					if ($moderation['content'] != NULL)
					{
						echo " to ".$moderation['content'];
					}
					if ($moderation['scope'] != NULL)
					{
						$object = instantiate_library($moderation['scope'], $moderation['scopeid']);
						$scopedata = $object->$moderation['scope'];
						echo " for ".$moderation['scope']." '".$scopedata['title']."'";
					}
					echo '</li>';
				}
				else
				{
					echo '<li><span class="date">'.$moderation['date'].'</span>: '.ucfirst($moderation['actionon'])." ".$moderation['action']."</li>";
				}
			}
			echo '</ul>';
		}
	?>
</div>
<?php } ?>
