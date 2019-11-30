This editor accepts <a href="http://en.wikipedia.org/wiki/BBCode" target="_blank">BBCode</a> to format text.
<br />

<input id="saveChangesButton" class="mediumButton" style="width:150px" type="button" value="Save Changes" onclick="NewPost.SaveChanges()">
<?php if($post->status != 1 ){ ?>
<input id="publishButton" class="mediumButton" type="button" value="Publish" onclick="NewPost.Publish()" />
<?php } else { ?>
<input id="publishButton" class="mediumButton" type="button" value="Unpublish" onclick="NewPost.Unpublish()" />
<?php } ?>
<input class="mediumButton" type="button" value="Back" onclick="document.location='<?=$returnUrl?>'" >
<br /><br />

<div class="editTabs">
	<ul>
		<li class="<?=(!$uploadedImage?"selected":"back")?>" id="contentTab"><a href="javascript:NewPost.ShowTab('content')"><span>Editor</span></a></li>
		<li class="back" id="previewTab"><a href="javascript:NewPost.ShowTab('preview')"><span>Preview</span></a></li>
		<li class="back" id="imageTab"><a href="javascript:NewPost.ShowTab('image')"><span>Images</span></a></li>
		<li class="<?=($uploadedImage?"selected":"back")?>" id="uploadTab"><a href="javascript:NewPost.ShowTab('upload')"><span>Upload</span></a></li>
	</ul>
	<div class="editTabsUnderline"></div>
</div>

<div id="contentTabContent" <?=($uploadedImage?"style='display:none'":"")?>>
	<form  action="<?=$PHP_SELF?>" method="post" id="postForm" style="display:inline">
	<input type="hidden" name="ajax" id="postevent" value="savechanges">
	<input type="hidden" name="postid" id="postid" value="<?=$post->id?>">
	<input type="text" name="title" value="<?=($post->title==""?"Post Title":$post->title)?>" style="width:100%" onclick="NewPost.OnChange()" onchange="NewPost.OnChange()">
	<textarea name="content"  id="content" style="width:100%;height:600px;padding:2px" onclick="NewPost.OnChange();NewPost.OnContentClick(this)"
		onkeydown="NewPost.ConvertTab(event,this)"
><?=($tempcontent!=""?$tempcontent:$post->content)?></textarea>
	</form>
</div>

<div id="previewTabContent" style="display:none">
	<div style="float:right;position:relative;z-index:2">
		<div class="message" id="message" style="display:none"></div>
	</div>
	<div id="preview" style="line-height:1.6em"></div>
</div>

<div id="imageTabContent" style="display:none">

	<?php if(sizeof($post->images)==0){ ?>
	There are no uploaded images. To insert images, first upload them using the upload tab.
	<?php } else { ?>
	To insert an image into your code, hover over an image and click on the small, medium, or large photo buttons on the toolbar. If you would like
	inserted images to use the lightbox effect, where a larger version appears when the image is clicked, please check 'use lightbox'.
	<br />
	<br />
	<label for="uselightbox" class="formLabel" style="cursor:pointer">Use Lightbox</label> <input type="checkbox" name="uselightbox" id="uselightbox" class="formInput">
	<br />
	<br />
	<div id="imageContainer" class="imageContainer">
	<?php foreach($post->images as $image){?>
	<div class="photo" id="image_<?=$image->id?>" onmouseover="Util.Show('toolbar_<?=$image->id?>')" onmouseout="Util.Hide('toolbar_<?=$image->id?>')">
		<a href="<?=$image->getLarge()?>" rel="lightbox"><img class="photoThumbnail" src="<?=$image->getSquare()?>"></a>
		<div class="imageToolbar" id="toolbar_<?=$image->id?>" style="display:none">
			<span class="imageToolbarIcon" style="float:right" onclick="NewPost.DeleteImage(this,'<?=$image->id?>')"><img src="<?=$directory?>images/delete.png" title="Delete Image" ></span>
			<span class="imageToolbarIcon" style="float:left" onclick="NewPost.InsertImage('<?=$image->id?>','l','<?=$image->originalname?>')"><img src="<?=$directory?>images/photolarge.png" title="Insert Large Image" ></span>
			<span class="imageToolbarIcon" style="float:left" onclick="NewPost.InsertImage('<?=$image->id?>','m','<?=$image->originalname?>')"><img src="<?=$directory?>images/photomedium.png" title="Insert Medium Image"></span>
			<span class="imageToolbarIcon" style="float:left" onclick="NewPost.InsertImage('<?=$image->id?>','s','<?=$image->originalname?>')"><img src="<?=$directory?>images/photosmall.png" title="Insert Small Image"></span>
		</div>
	</div>
	<?php }} ?>
	</div>
</div>

<div id="uploadTabContent" style="display:<?=($uploadedImage?"block":"none")?>">
	You can upload new images here. Uploaded images will be available in the
	images tab.
	<br />
	<br />
	<form enctype="multipart/form-data" action="<?=$PHP_SELF?>?view=edit" method="POST">
	<input type="hidden" name="event" value="uploadImage">
	<input type="hidden" id="tempcontent" name="tempcontent" value="">
	<input type="hidden" id="uploadpostid" name="postid" value="<?=$post->id?>">
	<div>
	<input name="file" type="file" style="height:30px;font-size:130%;" class="formInput"/>
	<input type="submit" value="Upload" style="height:30px;" class="mediumButton" onclick="NewPost.CheckId()"/>
	</div>
	</form>
	<br />
	<?php if($eventResult){ ?>
	<div class="message"><?=$eventMessage?></div>
	<?php } ?>
</div>

<script type="text/javascript">
	NewPost.Init(<?=$post->id?>);
</script>
