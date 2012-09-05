<?php
	if (sizeof($references) > 0)
	{
		echo '<h2>See also</h2>';
		echo '<ul>';
		foreach ($references as $reference)
		{
			echo '<li><a href="'.base_url().$reference['urlname'].'">'.$reference['title'].'</a></li>';
		}
		echo '</ul>';
	}
?>
