<?php if(sizeof($posts)>0){ ?>
<?php foreach($posts as $post ) { ?>
	<?=$post?>
<?php } ?>
<?php } else { ?>
There are currently no published posts.
<?php } ?>
<br />

<?php if($hasNextPage) {  ?>
<a style="float:right" href="<?=$PHP_SELFDIR?>page/<?=$page+1?>" class="newsPageLink">Older Posts ></a>
<?php } ?>

<?php if($hasPrevPage) { ?>
<a href="<?=$PHP_SELFDIR?>page/<?=$page-1?>" class="newsPageLink">< Newer Posts</a>
<?php } ?>

