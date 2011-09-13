<?php
	if (sizeof($files) > 0)
	{
		echo '<h2>Download</h2>';
		echo '<ul>';
		foreach ($files as $file)
		{
			echo '<li><a href="'.base_url().$file['urlpath'].'">'.$file['filename'].'.'.$file['extension'].'</a>, '.number_format(($file['filesize']/1000000),2).' mb</li>';
		}
		echo '</ul>';
	}
?>