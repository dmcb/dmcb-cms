<div class="author">	<?php	echo generate_profile_picture("small", $user);	?>	<p>		<?php 		if ($user['enabledprofile'])		{			echo '<a href="'.base_url().'profile/'.$user['urlname'].'">'.$user['displayname'].'</a>';		}		else		{			echo $user['displayname'];		}		?>		<br/>		Started writing for us on <?=date("F jS, Y", strtotime($author['date']));?>		<br/>		First post:		<a href="<?=base_url();?><?=$author['posturlname'];?>"><?=$author['title'];?></a>		<br/>		<?=$author['commentcount'];?>		<?php			if ($author['commentcount'] == 1)			{				echo ' comment';			}			else			{				echo ' comments';			}		?>	</p></div>