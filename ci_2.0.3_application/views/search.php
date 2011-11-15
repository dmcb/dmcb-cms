<div class="fullcolumn">

	<form action="<?=base_url();?>search" method="post" onsubmit="return dmcb.submit(this);">
		<fieldset>
			<?php
			if (isset($search_page_details))
			{
				echo '<legend>'.ucfirst(strtolower($search_page_details['title'])).' search</legend>';
			}
			else
			{
				echo '<legend>Search '.$this->config->item('dmcb_title').'</legend>';
			}
			?>
			
			<div id="search" class="panel alwaysopen"><div>
				<?php if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';?>
				<input type="hidden" name="buttonchoice" value="" class="hidden" />
				
				<input type="hidden" name="searchpage" value="<?php if (isset($search_page)) echo $search_page; else echo set_value('searchpage'); ?>" class="hidden" />
				
				<div class="forminput">
					<label>Search for</label>
					<input name="searchtext" type="text" class="text" value="<?php if (isset($search_text)) echo $search_text; else echo set_value('searchtext'); ?>"/>
					<?php echo form_error('searchtext'); ?>
				</div>
				
				<?php
				if ($search_page == NULL)
				{
				?>
				
				<div class="forminput">
					<label>Search type</label>
					<select name="searchtype">
						<option value="all" <option value="users" <?php echo set_select('searchtype', 'all', TRUE); ?>>All</option>
						<?php if ($this->acl->enabled('profile', 'view')) {?><option value="users" <?php echo set_select('searchtype', 'users'); ?>>Users</option><?php } ?>
						<option value="pages" <?php if (isset($search_type) && $search_type == "pages") echo 'selected="selected"'; echo set_select('searchtype', 'pages'); ?>>Pages</option>
						<option value="posts" <?php if (isset($search_type) && $search_type == "posts") echo 'selected="selected"'; echo set_select('searchtype', 'posts'); ?>>Posts</option>
						<option value="files" <?php if (isset($search_type) && $search_type == "files") echo 'selected="selected"'; echo set_select('searchtype', 'files'); ?>>Files</option>
					</select>
				</div>
				
				<?php
				}
				?>
				
				<div class="forminput">
					<input type="submit" value="Search" name="search" class="button" onclick="dmcb.submitSetValue(this);" onfocus="dmcb.submitSetValue(this);" onblur="dmcb.submitRemoveValue(this);"/>
				</div>
			
			</div></div>
		</fieldset>
	</form>
	
	<div class="spacer">&nbsp;</div>
	
	<?php
	if (sizeof($usermatches) > 0)
	{
		echo '<table><tr class="data"><td><h3>User results</h3></td><td>';
		
		if (sizeof($pagematches) > 5 && $search_type != "users")
		{
			echo '<form action="'.base_url().'search" method="post" id="searchform">';
			if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
			echo '<input type="hidden" name="searchtext" value="'.set_value('searchtext').'" class="hidden" />
				<a href="javascript:dmcb.searchsubmit(\'searchform\',\'users\');">Show more user results...</a>
				</form>';		
		}
		echo '</td></tr>';

		$i=0;
		foreach ($usermatches as $user)
		{
			if ($i < 5 || $search_type == "users")
			{
				echo '<tr class="data"><td colspan="2"><a href="'.base_url().'profile/'.$user['urlname'].'">'.$user['displayname'].'</a></td></tr>';
			}
			$i++;
		}
		echo '</table><br/>';
	}
	
	if (sizeof($pagematches) > 0)
	{
		echo '<table><tr class="data"><td><h3>Page results</h3></td><td>';
		
		if (sizeof($pagematches) > 5 && $search_type != "pages")
		{
			echo '<form action="'.base_url().'search" method="post" id="searchform">';
			if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
			echo '<input type="hidden" name="searchtext" value="'.set_value('searchtext').'" class="hidden" />
				<a href="javascript:dmcb.searchsubmit(\'searchform\',\'pages\');">Show more page results...</a>
				</form>';		
		}
		echo '</td></tr>';

		$i=0;
		foreach ($pagematches as $page)
		{
			if ($i < 5 || $search_type == "pages")
			{
				echo '<tr class="data"><td colspan="2">';
				if ($page['link'] == NULL)
				{
					echo '<a href="'.base_url().$page['urlname'].'">'.$page['title'].'</a>';
				}
				else
				{
					echo '<a href="'.$page['link'].'">'.$page['title'].'</a>';
				}
				echo '</td></tr>';
			}
			$i++;
		}
		echo '</table><br/>';
	}
	
	if (sizeof($postmatches) > 0)
	{
		echo '<table><tr class="data"><td><h3>Post results</h3></td><td>';
		
		if (sizeof($postmatches) > 5 && $search_type != "posts")
		{
			echo '<form action="'.base_url().'search" method="post" id="searchform">';
			if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
			echo '<input type="hidden" name="searchtext" value="'.set_value('searchtext').'" class="hidden" />
				<a href="javascript:dmcb.searchsubmit(\'searchform\',\'posts\');">Show more post results...</a>
				</form>';		
		}
		echo '</td></tr>';

		$i=0;
		foreach ($postmatches as $post)
		{
			if ($i < 5 || $search_type == "posts")
			{
				echo '<tr class="data"><td colspan="2"><a href="'.base_url().$post['urlname'].'">'.$post['title'].'</a></td></tr>';
			}
			$i++;
		}
		echo '</table><br/>';
	}
	
	if (sizeof($filematches) > 0)
	{
		echo '<table><tr class="data"><td><h3>File results</h3></td><td>';
		
		if (sizeof($filematches) > 5 && $search_type != "files")
		{
			echo '<form action="'.base_url().'search" method="post" id="searchform">';
			if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
			echo '<input type="hidden" name="searchtext" value="'.set_value('searchtext').'" class="hidden" />
				<a href="javascript:dmcb.searchsubmit(\'searchform\',\'files\');">Show more file results...</a>
				</form>';		
		}
		echo '</td></tr>';

		$i=0;
		foreach ($filematches as $file)
		{
			if ($i < 5 || $search_type == "files")
			{
				echo '<tr class="data"><td colspan="2"><a href="'.base_url().$file['urlpath'].'?search='.urlencode('"'.set_value('searchtext').'"').'">'.$file['filename'].'.'.$file['extension'].'</a> ('.number_format(($file['filesize']/1000000),2).' mb)</td></tr>';
			}
			$i++;
		}
		echo '</table><br/>';
	}
	
	if (isset($this->pagination))
	{
		echo $this->pagination->create_links();
	}
	
	if (isset($search_message)) 
	{
		echo '<p>'.$search_message.'</p>';
	}

	?>
</div>