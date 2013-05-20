<span class="comment-author"><?php echo $comment['displayname'].' writes:';?></span>
<span class="comment-content"><?php echo '<a href="'.base_url().$comment['post']['urlname'].'/comment/'.$comment['commentid'].'">'.character_limiter(preg_replace('/<br\/>/',' ',$comment['content']), 60).'"</a></span>';?>
