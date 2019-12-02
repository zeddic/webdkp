<table class="controltable" style="width:100%;border:0px;padding-top:10px"  cellspacing=0>

	<tbody>
		<?php foreach($posts as $post) { $count++; ?>
		<tr <?=($count%2==0?"class='odd'":"")?> id="post_<?=$post->id?>">
			<td class="left" style="padding-left:2px">
				<a href="<?=$PHP_SELF?>?view=edit&postid=<?=$post->id?>"><?=($post->title!=""?$post->title:"Untitled Post")?></a>
			</td>
			<td><?=$post->createdDate?></td>
			<td class="right" style="padding-right:2px">
				<a href="<?=$PHP_SELF?>?view=edit&postid=<?=$post->id?>"><img class="iconButton" src="<?=$directory?>images/edit.png" title="Edit Post"></a>
				<a href="javascript:NewsAdmin.DeletePost(<?=$post->id?>)"><img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete Post"></a>
			</td>
		</tr>
		<?php } ?>
		<?php if(sizeof($posts)==0){ ?>
		<tr>
			<td colspan=2 style="padding-left:2px">There are no published posts.</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

