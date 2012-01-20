<form class="collapsible" action="<?=base_url();?><?=$page['urlname'];?>/blocks" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('blocks');">Manage blocks</a></legend>
		
		<div id="blocks" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="forminput">
				<label>Blocks</label>
				<table>
					<?php
						$editable = TRUE;
						$first_row = TRUE;
						foreach ($blocks as $block) {
						
							if ($block['pageid'] == $page['pageid'] && $first_row)
							{
								echo '<tr><td colspan="4">Blocks on this page:<br/><br/></td>';
								$first_row = FALSE;
							}
							if ($block['pageid'] != $page['pageid'] && $editable)
							{
								if (!$first_row)
								{
									echo '<tr><td colspan="4"><br/><br/></td></tr>';
								}
								echo '<tr><td colspan="4">Other available blocks:<br/><br/></td></tr>';
								$editable = FALSE;
							}
							
							echo '<tr class="data"><td>';
							if ($editable)
							{
								echo '<a href="'.base_url().'block/'.$block['blockinstanceid'].'/edit">'.$block['title'].'</a>';
							}
							else
							{
								echo $block['title'];
							}
							echo '</td>';
							
							echo '<td>';
							if ($block['parent']['paginationpossible'] == 1 && $page['pagination_blockid'] == $block['blockinstanceid'])
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/remove_pagination_page/'.$block['blockinstanceid'].'">Remove as pagination</a>';
							}
							else if ($block['parent']['paginationpossible'] == 1)
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/set_pagination_page/'.$block['blockinstanceid'].'">Set as pagination</a>';
							}
							echo '<br/>';
							if ($block['parent']['rsspossible'] == 1 && $page['rss_blockid'] == $block['blockinstanceid'])
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/remove_rss_page/'.$block['blockinstanceid'].'">Remove as rss</a>';
							}
							else if ($block['parent']['rsspossible'] == 1)
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/set_rss_page/'.$block['blockinstanceid'].'">Set as rss</a>';
							}
							echo '</td>';
							
							echo '<td>';
							if ($block['parent']['paginationpossible'] == 1 && isset($default_blocks[$block['blockinstanceid'].'pagination']))
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/remove_pagination_child/'.$block['blockinstanceid'].'">Remove as pagination for child pages</a>';
							}
							else if ($block['parent']['paginationpossible'] == 1)
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/set_pagination_child/'.$block['blockinstanceid'].'">Set as pagination for child pages</a>';
							}
							echo '<br/>';
							if ($block['parent']['rsspossible'] == 1 && isset($default_blocks[$block['blockinstanceid'].'rss']))
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/remove_rss_child/'.$block['blockinstanceid'].'">Remove as rss for child pages</a>';
							}
							else if ($block['parent']['rsspossible'] == 1)
							{
								echo '<a href="'.base_url().$page['urlname'].'/blocks/set_rss_child/'.$block['blockinstanceid'].'">Set as rss for child pages</a>';
							}
							echo '</td>';	
						}
					?>
				</table>
			</div>
			
			<br/>
			
			<div class="formnotes">
				<p>Add a block to embed dynamic elements in the page like news post listings, twitter feeds, etc.</p>
			</div>
			
			<div class="forminput">
				<label>Title</label>
				<input name="blocktitle" type="text" class="text" maxlength="20" value="<?php echo set_value('blocktitle'); ?>"/>
				<?php echo form_error('blocktitle'); ?>
			</div>
			
			<div class="forminput">
				<label>Function</label>
				<select name="blockfunction">
				<?php
					foreach ($functions->result_array() as $function) 
					{
						echo '<option value="'.$function['function'].'" '.set_select('blockfunction', $function['function']).' >'.$function['name'].'</option>';
					}
				?>
				</select>
			</div>

			<div class="forminput">
				<input type="submit" value="Add block" name="addblock" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		
		</div></div>
	</fieldset>
</form>	