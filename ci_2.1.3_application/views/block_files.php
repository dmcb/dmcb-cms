<span class="block file">
	<a href="<?php echo base_url();?><?php echo $file['urlpath'];?>"><?php echo $file['filename'];?>.<?php echo $file['extension'];?></a>, <?php echo number_format(($file['filesize']/1000000),2);?> mb, last modified on <?php echo date("F jS, Y", $file['filemodified']);?>.
</span>