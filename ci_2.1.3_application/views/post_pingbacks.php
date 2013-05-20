<?php
	if ($pingbacks->num_rows() > 0)
	{
		echo '<h2>Post linked by</h2>';
		echo '<ul>';
		foreach ($pingbacks->result_array() as $pingback)
		{
			echo '<li><a href="'.$pingback['source'].'">'.$pingback['title'].'</a></li>';
		}
		echo '</ul>';
	}
?>
