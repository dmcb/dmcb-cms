<?php
if (!$self_editing)
{
?>
<h2>Account information for <?php echo $user['displayname'];?></h2>

<p><a href="<?php echo base_url();?>manage_users/email"><?php echo $user['email'];?></a></p>

<br/>

<?php
	if ($user['lastsignon'] != "0000-00-00 00:00:00")
	{
		echo 'Last sign on: '.date("F jS, Y", strtotime($user['lastsignon'])).'<br/>';
	}

	if ($user['registered'] != "0000-00-00 00:00:00")
	{
		echo 'Registered: '.date("F jS, Y", strtotime($user['registered'])).'<br/>';
	}

	if (isset($subscription))
	{
		echo '<br/>User has '.strtolower($subscription['type']).' subscription that ';
		if (strtotime($subscription['date']) < time())
		{
			echo 'expired on '.date("F jS, Y", strtotime($subscription['date'])).'<br/>';
		}
		else
		{
			echo 'expires on '.date("F jS, Y", strtotime($subscription['date'])).'<br/>';
		}
	}

	if ($user['enabledprofile'])
	{
		echo '<a href="'.base_url().'profile/'.$user['urlname'].'">Visit profile</a>';
	}
}

if (sizeof($privileges) > 0)
{
	if (!$self_editing)
	{
		echo '<br/><br/>';
	}

	echo '<h2>Site privileges</h2><br/>';
	foreach ($privileges as $privilege)
	{
		echo '<li>';
		if ($privilege['on'] == "site")
		{
			echo 'Site-wide '.$privilege['role'];
		}
		else
		{
			echo ucfirst($privilege['role']).' on <a href="'.base_url().$privilege[$privilege['on']]['urlname'].'">'.$privilege[$privilege['on']]['title'].'</a>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

if (!$self_editing)
{
?>

<br/>
<br/>

<h2>Moderating report</h2>

<br/>

<?php
	if ($moderations->num_rows() == 0)
	{
		echo "No moderating activity against this user was found.<br/>";
	}
	else
	{
		if ($moderations->num_rows() == 30)
		{
			echo "Most recent entries listed...<br/><br/>";
		}

		foreach ($moderations->result_array() as $moderation)
		{
			if ($moderation['actionon'] == "user")
			{
				echo $moderation['date'].": User ".$moderation['action'];
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
				echo "<br/>";
			}
			else
			{
				echo $moderation['date'].": ".ucfirst($moderation['actionon'])." ".$moderation['action']."<br/>";
			}
		}
	}
}
?>