<span>
	<a href="<?=base_url();?><?=$file['urlpath'];?>"><?=$file['filename'];?>.<?=$file['extension'];?></a>, <?php echo number_format(($file['filesize']/1000000),2);?> mb, last modified on <?=date("F jS, Y", $file['filemodified']);?>.
</span>
<br/>
