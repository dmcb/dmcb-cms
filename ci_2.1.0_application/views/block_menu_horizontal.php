<?php
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
	
	if ($level == 0 )
	{
		$menu_item = '<a'.$link_html.$selected_html.'>'.$title.'</a><br/>';
	}
	else
	{
		$menu_item = '<a'.$link_html.$selected_html.' class="small">'.$title.'</a>';
	}
	
	if ($children_html != NULL)
	{
		$menu_item .= $children_html;
	}
	
	if ($level == 0 )
	{
		echo '<td>'.$menu_item.'</td>';
	}
	else
	{
		echo '<br/>'.$menu_item;
	}
?>

