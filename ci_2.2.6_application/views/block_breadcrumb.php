<span class="breadcrumb">
<?php
	$first = TRUE;
	foreach ($links as $link)
	{
		if (!$first)
		{
			echo ' > ';
		}
		echo '<a href="'.base_url().$link['url'].'">'.$link['title'].'</a>';
		$first = FALSE;
	}
?>
</span>
