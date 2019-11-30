<br />
<input id="saveEditChanges" class="mediumButton" style="width:150px" type="button" value="Save Changes" onclick="HtmlPart.SaveChanges()">
<input class="mediumButton" type="button" value="Back" onclick="document.location='<?=$PHP_SELF?>?editpage=1'" >
<br /><br />

<div class="editTabs">
	<ul>
		<li class="<?=(!$uploadedImage?"selected":"back")?>" id="htmlTab"><a href="javascript:HtmlPart.ShowTab('html')"><span>HTML</span></a></li>
		<li class="back" id="previewTab"><a href="javascript:HtmlPart.ShowTab('preview')"><span>Preview</span></a></li>
		<li class="back" id="imageTab"><a href="javascript:HtmlPart.ShowTab('image')"><span>Images</span></a></li>
		<li class="<?=($uploadedImage?"selected":"back")?>" id="uploadTab"><a href="javascript:HtmlPart.ShowTab('upload')"><span>Upload</span></a></li>
	</ul>
	<div class="editTabsUnderline"></div>
</div>

<div id="htmlTabContent" <?=($uploadedImage?"style='display:none'":"")?>>
	<form  action="<?=$PHP_SELF?>" method="post" id="editform" style="display:inline">
	<input type="hidden" name="ajax<?=$iid?>" value="edit">

	<textarea name="content"  id="content" style="width:100%;height:600px;padding:2px" onclick="HtmlPart.OnChange();HtmlPart.OnContentClick(this)"
		onkeydown="HtmlPart.ConvertTab(event,this)"
><?=($tempcontent!=""?$tempcontent:$html->content)?></textarea>
	</form>
</div>

<div id="previewTabContent" style="display:none">
	<div style="float:right;position:relative;z-index:2">
		<div class="message" id="message" style="display:none"></div>
	</div>
	<div id="preview"></div>
</div>

<div id="imageTabContent" style="display:none">

	<?php if(sizeof($images)==0){ ?>
	There are no uploaded images. To insert images, first upload them using the upload tab and inserted into your html.
	<?php } else { ?>
	To insert an image into your code, hover over an image and click on the small, medium, or large photo buttons on the toolbar. If you would like
	inserted images to use the lightbox effect, where a larger version appears when the image is clicked, please check 'use lightbox'.
	<br />
	<br />
	<label for="uselightbox" class="formLabel">Use Lightbox</label><input type="checkbox" name="uselightbox" id="uselightbox" class="formInput">
	<br />
	<br />
	<div id="imageContainer" class="imageContainer">
	<?php foreach($images as $image){?>
	<div class="photo" id="image_<?=$image->id?>" onmouseover="Util.Show('toolbar_<?=$image->id?>')" onmouseout="Util.Hide('toolbar_<?=$image->id?>')">
		<a href="<?=$image->getLarge()?>" rel="lightbox"><img class="photoThumbnail" src="<?=$image->getSquare()?>"></a>
		<div class="imageToolbar" id="toolbar_<?=$image->id?>" style="display:none">
			<span class="imageToolbarIcon" style="float:right" onclick="HtmlPart.DeleteImage(this,'<?=$image->id?>')"><img src="<?=$directory?>images/delete.png" title="Delete Image" ></span>
			<span class="imageToolbarIcon" style="float:left" onclick="HtmlPart.InsertImage('<?=$image->getMedium()?>','<?=$image->getLarge()?>')"><img src="<?=$directory?>images/photolarge.png" title="Insert Large Image" ></span>
			<span class="imageToolbarIcon" style="float:left" onclick="HtmlPart.InsertImage('<?=$image->getSmall()?>','<?=$image->getLarge()?>')"><img src="<?=$directory?>images/photomedium.png" title="Insert Medium Image"></span>
			<span class="imageToolbarIcon" style="float:left" onclick="HtmlPart.InsertImage('<?=$image->getThumbnail()?>','<?=$image->getLarge()?>')"><img src="<?=$directory?>images/photosmall.png" title="Insert Small Image"></span>
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
	<form enctype="multipart/form-data" action="<?=$PHP_SELF?>?editpage=1&v<?=$iid?>=edit" method="POST">
	<input type="hidden" name="event<?=$iid?>" value="uploadImage">
	<input type="hidden" id="tempcontent" name="tempcontent" value="">
	<div>
	<input name="file" type="file" style="height:30px;font-size:130%;" class="formInput"/>
	<input type="submit" value="Upload" style="height:30px;" class="mediumButton"/>
	</div>
	</form>
	<br />
	<?php if($eventResult){ ?>
	<div class="message"><?=$eventMessage?></div>
	<?php } ?>
</div>

 <script type="text/javascript">
 HtmlPart.Init(<?=$iid?>);
 </script>