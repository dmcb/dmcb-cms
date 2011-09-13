<div class="fullcolumn">
	<?php
		if (sizeof($users) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="6"><h2>Users waiting for activation</h2></td></tr>';
			foreach ($users as $user)
			{
				echo '<tr class="data"><td>'.$user['displayname'].'</td>';
				echo '<td>'.$user['email'].'</td>';
				echo '<td>Initially registered on '.date("F jS, Y", strtotime($user['registered'])).'</td>';
				echo '<td><a href="'.base_url().'manage_activity/user/email/'.$user['userid'].'">Resend activation email</a></td>';
				echo '<td><a href="'.base_url().'manage_users/password/'.$user['userid'].'">Activate user</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/user/delete/'.$user['userid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this user?\')">Delete</a></td></tr>';
			}
			echo '</table><br/>';
		}

		if (sizeof($postsreview) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Posts to review</h2></td></tr>';
			foreach ($postsreview as $post)
			{
				echo '<tr class="data"><td><a href="'.base_url().$post['urlname'].'">'.$post['title'].'</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/feature/'.$post['postid'].'">Feature this post</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/review/'.$post['postid'].'">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/holdback/'.$post['postid'].'">Hold back</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/delete/'.$post['postid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this post?\')">Delete</a></td></tr>';
			}
			echo '</table><br/>';
		}

		if (sizeof($postspending) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Held back posts that have been updated</h2></td></tr>';
			foreach ($postspending as $post)
			{
				echo '<tr class="data"><td><a href="'.base_url().$post['urlname'].'">'.$post['title'].'</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/feature/'.$post['postid'].'">Feature this post</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/approve/'.$post['postid'].'">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/holdback/'.$post['postid'].'">Hold back</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/post/delete/'.$post['postid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this post?\')">Delete</a></td></tr>';
			}
			echo '</table><br/>';
		}
		
		if (sizeof($categories) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="4"><h2>Category suggestions pending approval</h2></td></tr>';
			foreach ($categories as $category)
			{
				echo '<tr class="data"><td><form action="'.base_url().'manage_activity/category/'.$category['categoryid'].'" method="post" id="categoryform'.$category['categoryid'].'">';
				if ($this->config->item('csrf_protection')) echo '<input type="hidden" name="'.$this->security->get_csrf_token_name().'" value="'.$this->security->get_csrf_hash().'" />';
				echo '<input type="text" name="categoryname" value="'.set_value('categoryname', $category['name']).'" class="text"/>';
				if ($this->uri->segment(2) == "category" && $this->uri->segment(3) == $category['categoryid'])
				{
					echo form_error('categoryname');
				}
				echo '</form></td>';
				echo '<td><select onchange="dmcb.goto(this)" class="wide"><option>Merge into existing category</option>';
				foreach ($publishedcategories->result_array() as $publishedcategory)
				{
					echo '<option value="'.base_url().'manage_activity/category/merge/'.$category['categoryid'].'/'.$publishedcategory['categoryid'].'">'.$publishedcategory['name'].'</option>';
				}
				echo '</select></td>';
				echo '<td><a href="javascript:dmcb.linksubmit(\'categoryform'.$category['categoryid'].'\');">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/category/delete/'.$category['categoryid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this category?\')">Delete</a></td></tr>';
			}
			echo '</table><br/>';
		}
		
		if (sizeof($commentsnew) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Newest comments from members to review (currently live on the site)</h2></td></tr>';
			foreach ($commentsnew as $comment)
			{
				echo '<tr class="data"><td>Comment on '.date('F jS, Y \a\t g:i a', strtotime($comment['date'])).'</td>';
				echo '<td><a href="javascript:Effect.Combo(\'comment'.$comment['commentid'].'\');">Show comment</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/review/'.$comment['commentid'].'">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/holdback/'.$comment['commentid'].'">Hold back</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/delete/'.$comment['commentid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this comment?\')">Delete</a></td></tr>';
				echo '<tr><td colspan="5"><div class="notice" style="display: none;" id="comment'.$comment['commentid'].'"><h5>Posted in <a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.$comment['post']['title'].'</a> by ';
				
				if ($comment['user']['enabledprofile'])
				{
					echo '<a href="'.base_url().'profile/'.$comment['user']['urlname'].'">'.$comment['user']['displayname'].'</a>';
				}
				else
				{
					echo $comment['user']['displayname'];
				}

				echo '</h5><br/><p>'.$comment['content'].'</p></div></td></tr>';
			}
			echo '</table><br/>';
		}
		
		if (sizeof($commentsanonymous) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Newest comments from anonymous users to review (held from view until approved)</h2></td></tr>';
			foreach ($commentsanonymous as $comment)
			{
				echo '<tr class="data"><td>Comment on '.date('F jS, Y \a\t g:i a', strtotime($comment['date'])).'</td>';
				echo '<td><a href="javascript:Effect.Combo(\'comment'.$comment['commentid'].'\');">Show comment</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/approve/'.$comment['commentid'].'">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/delete/'.$comment['commentid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this comment?\')">Delete</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/ban/'.$comment['commentid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to ban this ip address?\')">Ban ip</a></td></tr>';
				echo '<tr><td colspan="5"><div class="notice" style="display: none;" id="comment'.$comment['commentid'].'"><h5>Posted in <a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.$comment['post']['title'].'</a> anonymously by '.$comment['displayname'].'</h5><br/><p>'.$comment['content'].'</p></div></td></tr>';
			}
			echo '</table><br/>';
		}

		if (sizeof($commentsreported) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Comments that were reported as abusive</h2></td></tr>';
			foreach ($commentsreported as $comment)
			{
				echo '<tr class="data"><td>Comment on '.date('F jS, Y \a\t g:i a', strtotime($comment['date'])).'</td>';
				echo '<td><a href="javascript:Effect.Combo(\'comment'.$comment['commentid'].'\');">Show comment</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/review/'.$comment['commentid'].'">Dismiss the reporting</a></td>';
				if ($comment['userid'] != NULL) 
				{
					echo '<td><a href="'.base_url().'manage_activity/comment/holdback/'.$comment['commentid'].'">Hold back</a></td>';
				}
				else
				{
					echo '<td></td>';
				}
				echo '<td><a href="'.base_url().'manage_activity/comment/delete/'.$comment['commentid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this comment?\')">Delete</a></td></tr>';
				echo '<tr><td colspan="5"><div class="notice" style="display: none;" id="comment'.$comment['commentid'].'"><h5>Posted in <a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.$comment['post']['title'].'</a> by ';
				if (isset($comment['user']))
				{
					if ($comment['user']['enabledprofile'])
					{
						echo '<a href="'.base_url().'profile/'.$comment['user']['urlname'].'">'.$comment['user']['displayname'].'</a>';
					}
					else
					{
						echo $comment['user']['displayname'];
					}
				}
				else
				{
					echo 'anonymously by '.$comment['displayname'];
				}
				echo '</h5><br/><p>'.$comment['content'].'</p></div></td></tr>';
			}
			echo '</table><br/>';
		}

		if (sizeof($commentspending) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Held back comments that have been updated</h2></td></tr>';
			foreach ($commentspending as $comment)
			{
				echo '<tr class="data"><td>Comment on '.date('F jS, Y \a\t g:i a', strtotime($comment['date'])).'</td>';
				echo '<td><a href="javascript:Effect.Combo(\'comment'.$comment['commentid'].'\');">Show comment</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/approve/'.$comment['commentid'].'">Approve</a></td>';
				if ($comment['userid'] != NULL) echo '<td><a href="'.base_url().'manage_activity/comment/holdback/'.$comment['commentid'].'">Hold back</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/comment/delete/'.$comment['commentid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to delete this comment?\')">Delete</a></td></tr>';
				echo '<tr><td colspan="5"><div class="notice" style="display: none;" id="comment'.$comment['commentid'].'"><h5>Posted in <a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.$comment['post']['title'].'</a> by <a href="'.base_url().'profile/'.$comment['user']['urlname'].'">'.$comment['user']['displayname'].'</a></h5><br/><p>'.$comment['content'].'</p></div></td></tr>';
			}
			echo '</table><br/>';
		}
		
		if (sizeof($pingbacks) > 0)
		{
			echo '<table>';
			echo '<tr><td colspan="5"><h2>Pingbacks to review</h2></td></tr>';
			foreach ($pingbacks as $pingback)
			{
				echo '<tr class="data"><td>Pingback for <a href="'.base_url().$pingback['post']['urlname'].'">'.$pingback['post']['title'].'</a> from <a href="'.$pingback['source'].'">'.$pingback['title'].'</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/pingback/approve/'.$pingback['pingbackid'].'">Approve</a></td>';
				echo '<td><a href="'.base_url().'manage_activity/pingback/deny/'.$pingback['pingbackid'].'" onclick="return dmcb.confirmation(\'Are you sure you wish to deny this pingback?\')">Deny</a></td></tr>';
			}
			echo '</table><br/>';
		}
		
		if (sizeof($users) == 0 && sizeof($postsreview) == 0 && sizeof($postspending) == 0 && sizeof($commentsnew) == 0 && sizeof($commentsanonymous) == 0 && sizeof($commentsreported) == 0  && sizeof($commentspending) == 0 && sizeof($categories) == 0 && sizeof($pingbacks) == 0)
		{
			echo '<p>There is no activity to report.</p>';
		}
	?>
</div>