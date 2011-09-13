<form class="collapsible important">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('heldposts');">Held back posts</a></legend>
		
		<div id="heldposts" class="panel"><div>

		<?php
			foreach ($heldposts as $heldpost) {
				echo '<a href="'.base_url().$heldpost['urlname'].'/editpost">'.$heldpost['title'].'</a> - '.date("F jS, Y", strtotime($heldpost['date']));
				if ($heldpost['reviewed'] == 0)
				{
					echo ' (Currently pending approval from moderators)';
				}
				echo '<br/>';
			}
		?>
		
		</div></div>
	</fieldset>
</form>
 