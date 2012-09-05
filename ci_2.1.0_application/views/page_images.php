<?php
foreach ($images as $attached_image)
{
	if ($attached_image['urlpath'] != $image['urlpath'])
	{
?>
		<a href="<?php echo base_url().$attached_image['urlpath'];?>" rel="lightbox[<?php echo $pageid;?>]"><img src="<?php echo base_url().size_image($attached_image['urlpath'],40);?>" alt="" class="listed_image"/></a>
<?php
	}
}
?>
