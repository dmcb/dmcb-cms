	<!-- tinymce blocks initialization -->
	<script type="text/javascript">
		<!--
	<?php
		echo "\t\tvar dmcbBlocksArray = new Array(".sizeof($blocks).');';
		for ($i=0; $i<sizeof($blocks); $i++)
		{
			echo "\n\t\t\tdmcbBlocksArray[".$i."] = '".$blocks[$i]['title']."';";
		}
	?>

		-->
	</script>
