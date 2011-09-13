<?php
	$space = '';
	for ($i=0; $i<=$level; $i++)
	{
		$space .= '   ';
	}

	$selected_html = '';
	if ($selected)
	{
		$selected_html = ' class="selected"';
	}
	if ($title == NULL)
	{
		$selected_html = ' class="nohover"';
	}
	
	$link_html = '';
	if ($link != NULL)
	{
		$link_html = ' href="'.$link.'"';
	}
	
	echo $space.'<li><a'.$link_html.$selected_html.'>'.$title.'</a>';

	if ($children_html != NULL)
	{
		echo PHP_EOL.$space."<ul>".PHP_EOL.$children_html.$space."</ul>".PHP_EOL.$space;
	}
	
	echo '</li>';
?>

