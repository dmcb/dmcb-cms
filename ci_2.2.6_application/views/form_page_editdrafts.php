<form class="collapsible important">	<fieldset>		<legend><a href="javascript:Effect.Combo('editdrafts');">Edit drafts</a></legend>				<div id="editdrafts" class="panel"><div>		<?php			foreach ($drafts as $draft) {				echo '<a href="'.base_url().$draft['urlname'].'/editpost">'.$draft['title'].'</a> - '.date("F jS, Y", strtotime($draft['date'])).'<br/>';			}		?>				</div></div>	</fieldset></form>	
