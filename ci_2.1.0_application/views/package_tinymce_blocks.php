	<!-- tinymce blocks initialization -->
	<script type="text/javascript">
	<?php
		echo "	var dmcbBlocksArray = new Array(".sizeof($blocks).');';
		for ($i=0; $i<sizeof($blocks); $i++)
		{
			echo "\n		dmcbBlocksArray[".$i."] = '".$blocks[$i]['title']."';";
		}
	?>

	</script>