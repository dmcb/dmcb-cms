<form class="collapsible" action="<?php echo base_url();?><?php echo $page['urlname'];?>/addpage" method="post" onsubmit="return dmcb.submit(this);">
	<fieldset>
		<legend><a href="javascript:Effect.Combo('addpage');">Add a child page</a></legend>

		<div id="addpage" class="panel"><div>
			<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
			<input type="hidden" name="buttonchoice" value="" class="hidden" />

			<div class="forminput">
				<label>Child pages</label>
				<table>
					<?php
					if (sizeof($childpages) == 0)
					{
						echo '<tr><td>This page has no child pages.</td></tr>';
					}
					else
					{
						foreach ($childpages as $row)
						{
							echo '<tr class="data"><td><a href="'.base_url().$row['urlname'].'/editpage" title="'.$row['title'].'">'.$row['title'].'</a></td><td>';
							if ($row['published'])
							{
								echo '<a href="'.base_url().$page['urlname'].'/addpage/unpublish/'.$row['pageid'].'">Unpublish</a>';
							}
							else
							{
								echo '<a href="'.base_url().$page['urlname'].'/addpage/publish/'.$row['pageid'].'">Publish</a>';
							}
							echo '</td></tr>';
						}
					}
					?>
				</table>
			</div>

			<br/>
			<br/>

			<div class="forminput">
				<label>Title</label>
				<input name="pagetitle" type="text" class="text" maxlength="100" onkeypress="dmcb.toUrlname(this.form, this.value, 'pageurlname')" onkeyup="dmcb.toUrlname(this.form, this.value, 'pageurlname')" value="<?php echo set_value('pagetitle'); ?>"/>
				<?php echo form_error('pagetitle'); ?>
			</div>

			<div class="forminput">
				<label>URL name</label>
				<input name="pageurlname" type="text" class="text" maxlength="30" value="<?php echo set_value('pageurlname'); ?>"/>
				<?php echo form_error('pageurlname'); ?>
			</div>

			<div class="formnotes full">
				<p>Choose to nest a URL if the page appears under another page and you want a URL like <?php echo base_url();?>parent/child instead of <?php echo base_url();?>child</p>
			</div>

			<div class="forminput">
				<label>Nested URL</label>
				<input name="nestedurl" id="nestedurl" type="checkbox" class="checkbox" value="1" <?php echo set_checkbox('nestedurl', '1');?> />
			</div>

			<div class="forminput">
				<input type="submit" value="Continue" name="continue" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
			</div>
		</div></div>
	</fieldset>
</form>
