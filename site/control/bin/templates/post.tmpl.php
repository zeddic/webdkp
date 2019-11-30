<div class="Border1">
	<div class="BorderHeader postTitle">
		<a href="<?=$PHP_SELFDIR?>post/<?=$post->id?>"><?=$post->title?></a>
	</div>
	<div class="newsSubtitle">
		<div style="float:right">
		<?php if(security::hasAccess("Edit News")){ ?><a href="<?=$SiteRoot?>admin/content/news/index?view=edit&postid=<?=$post->id?>&returnto=<?=($post->isPreview?"posts":"post")?>">Edit</a><?=(isset($backLink)?" | ":"")?><?php } ?>
		<?php if(isset($backLink)){ ?> <a href="<?=$backLink?>">Back</a><?php } ?>
		</div>
		<div><i><?=$post->createdDate?></i></div>
	</div>
	<?php /* if ($post->isPreview) { ?>
	<?=util::trimString($post->content,200,"... <br /><br /><a href='".$PHP_SELFDIR."post/$post->id'>Read More</a>",true)?>
	<?php } else { */ ?>
	<?=$post->content?>
	<?php /* } */ ?>
</div>



<?php /*<div>
	<div class="postDate">
		<span class="postMonth"><?=$post->createdMonth?></span>
		<span class="postDay"><?=$post->createdDay?></span>
	</div>
	<div class="postTitle">
		<div class="postTitleText"><a href="<?=$PHP_SELFDIR?>post/<?=$post->id?>"><?=$post->title?></a></div>
		<div class="postTitleLinks" style="float:right">

			<?php if(security::hasAccess("Edit News")){ ?><a href="<?=$SiteRoot?>admin/content/index?view=edit&postid=<?=$post->id?>&returnto=<?=($post->isPreview?"posts":"post")?>">Edit</a><?=(isset($backLink)?" | ":"")?><?php } ?>
			<?php if(isset($backLink)){ ?> <a href="<?=$backLink?>">Back</a><?php } ?>
		</div>
		<div class="postTitleTags">
			<?php if(sizeof($post->tags)>0){ ?>
			<?php $i=0; foreach($post->tags as $tag){ $i++; ?>
			<a href="<?=$PHP_SELFDIR?>tags/1/<?=$tag->name?>"><?=$tag->name?></a><?=($i==sizeof($post->tags)?"":",")?>
			<?php } ?>
			<?php } else { ?><a href="<?=$PHP_SELFDIR?>tags/1/untagged">untagged</a><?php } ?>
		</div>
	</div>
	<div class="postContent">

		<?php if(sizeof($post->imagesInGallery)>0){ ?>
		<div class="postPhotoGallery"  >
			<?php foreach($post->images as $image) {
					if(in_array($image->id,$post->imagesInGallery)) { ?>
				<a href="<?=$image->getLarge()?>" rel="lightbox" title="<a href='<?=$image->getOriginal()?>'>Original Image</a>"><img src="<?=$image->getSquare()?>"></a>
			<?php }} ?>
		</div>
		<?php } ?>


		<?php if ($post->isPreview) { ?>
		<?=util::trimString($post->content,200,"... ( <a href='".$PHP_SELFDIR."post/$post->id'>Read More</a> )",true)?>
		<?php } else { ?>
		<?=$post->content?>
		<?php } ?>


		<?php if($post->address != ""){ ?>
			<br />
			Map of <?=$post->address?>
			<script type="text/javascript">
			News.ShowMapOnLoad(<?=$post->id?>,"<?=$post->address?>");
	    	</script>
			<br />
			<div id="map<?=$post->id?>" style="height: 300px"></div>
		<?php } ?>


		<?php if(sizeof($post->files) > 0 ) { ?>
		<div class="postFiles">
		<b>Attached Files</b>
		<br />
			<?php foreach($post->files as $file) { ?>
				<img class="fileIcon" src="<?=$directory?>images/icons/<?=$file->getExtType()?>.png">
				<a href="<?=$file->getPath()?>" target="EditorFile"><?=$file->originalname?></a>
				<br />
			<?php } ?>
		</div>
		<?php  }?>
	</div>
</div> */ ?>