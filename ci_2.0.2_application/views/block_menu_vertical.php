<?php
	$space = '';
	for ($i=0; $i<=$level; $i++)
	{
		$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	}

	$selected_html = '';
	if ($selected)
	{
		$selected_html = ' class="selected"';
	}
	
	$link_html = '';
	if ($link != NULL)
	{
		$link_html = ' href="'.$link.'"';
	}
	
	echo $space.'<a'.$link_html.$selected_html.'>'.$title.'</a><br/>';
	
	if ($children_html != NULL)
	{
		echo $children_html;
	}
?>

