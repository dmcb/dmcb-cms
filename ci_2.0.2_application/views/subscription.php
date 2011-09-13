<div class="leftcolumn">
	<h2>Your subscription</h2>
	<p>
		Check on your existing subscription, or order or renew a new one.
	</p>
</div>

<div class="centercolumnlarge">
	<form>
		<fieldset>
			<legend>Your current subscription</legend>
			
			<div id="order" class="panel alwaysopen"><div>
				<?php
				if ($subscription == NULL)
				{
					echo '<div class="forminput"><label>Subscription type:</label>You have no subscription</div>';
				}
				else
				{
					echo '<div class="forminput"><label>Subscription type:</label>'.$subscription['type']['type'].'</div>';
					if (strtotime($subscription['date']) < time())
					{
						echo '<div class="forminput"><label>Expires:</label><span class="restricted">Expired on '.date("F jS, Y", strtotime($subscription['date'])).'</span></div>';
					}
					else
					{
						echo '<div class="forminput"><label>Expires:</label>'.date("F jS, Y", strtotime($subscription['date'])).'</div>';
					}
				}
				?>
				
			</div></div>
		</fieldset>
	</form>

	<?php if (isset($order_form)) echo $order_form; ?>
</div>