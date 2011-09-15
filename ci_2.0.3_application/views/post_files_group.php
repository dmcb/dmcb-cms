<?php
	if ($filegroup['total']	> 0)
	{
?>

	<br/>
	<form class="collapsible"/>
		<fieldset>
		<legend><?php echo $filegroup['name'];?></legend>
		
		<?php
		foreach ($filegroup['filetypes'] as $filetype) 
		{	
			if (sizeof($filetype['files']))
			{
		?>
				<div class="forminput">
					<label><?php echo $filetype['name']; ?></label>
					<table>
				
				<?php			
		
				foreach ($filetype['files'] as $row) 
				{
					echo '<tr class="data"><td><a href="'.base_url().$row['urlpath'].'" title="'.$row['filename'].'" ';
					if ($row['isimage'] == 1)
						echo 'rel="lightbox">';
					else
						echo '>';
					echo $row['filename'].'.'.$row['extension'].'</a>, '.number_format(($row['filesize']/1000000),2).' mb</a></td></tr>';
				}
				?>
				
					</table>
				</div>
		<?php
			}
		}
		?>

		</fieldset>
	</form>
	
<?php
	}
?>