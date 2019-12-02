<form id="postForm" class="wufoo" enctype="multipart/form-data" method="post" action="<?=$PHP_SELF?>?view=edit&postid=<?=$post->id?>&ajaxpost=1">
<input type="hidden" name="postid" id="postid" value="<?=$post->id?>" />
<input type="hidden" name="event" id="postevent" value="saveChanges" />
<ul style="overflow:hidden">
<li class="buttons">
	<?php if(isset($eventResult)){?>
	<div class="message" id="FormSaved">
		<b><?=$eventMessage?></b>
	</div>
	<?php } ?>

	<input id="saveChangesButton" class="mediumButton" type="button" value="Save Changes" onclick="NewPost.SaveChanges()" style="width:140px" />
	<?php if($post->status != 1 ){ ?>
	<input id="publishButton" class="mediumButton" type="button" value="Publish" onclick="NewPost.Publish()" />
	<?php } else { ?>
	<input id="publishButton" class="mediumButton" type="button" value="Unpublish" onclick="NewPost.Unpublish()" />
	<?php } ?>
	<input class="mediumButton" type="button" value="Back" onclick="document.location='<?=$returnUrl?>'"/>
	<br />
</li>
<li >
	<label class="desc" id="title0" for="Field0">
	Post Title
	</label>
	<div>
		<input id="Field0" onclick="NewPost.OnChange()"
		   name="title"
		   class="field text large"
		   type="text" maxlength="255" value="<?=$post->title?>"/>
	</div>
	<p class="instruct " id="instruct0"><small>Enter the title for your news post.</small></p>
</li>

<li  style="width:100%;padding:0px;">
	<div style="padding: 5px 5px 2px 0px" >
		<div>
		<?php
		/*$oFCKeditor = new FCKeditor("postContent") ;
		$oFCKeditor->Value	= $post->content;
		$oFCKeditor->BasePath = $SiteRoot.'plugins/fckeditor/';
		$oFCKeditor->ToolbarSet = "Advanced";
		$oFCKeditor->Height= $size;
		$oFCKeditor->Config['FormatSource'] = false;
		$oFCKeditor->Config['FormatOutput'] = false;
		$oFCKeditor->Config['EditorAreaCSS'] = $directory.'css/editor.css';
		//$oFCKeditor->Config['StartupFocus'] = true;
		$oFCKeditor->Create() ;*/
		?>
		<div class="resizeContainer">
			<img src="<?=$directory?>images/resizeup.png" class="editIcon" onclick="NewPost.ResizeUp(this)">
			<img src="<?=$directory?>images/resizedown.png" class="editIcon" onclick="NewPost.ResizeDown(this)" >
		</div>
		</div>

	</div>

</li>

<li>
	<img src="<?=$directory?>images/pictures.png" style="vertical-align:middle"> <a href="javascript:Util.Toggle('Images');">Images</a>
</li>
<li id="Images" style="display:none">
	<p class="instruct " id="instruct4"><small>
		To attach photos to your post, first click on upload photos then select the
		photos that you would like to attach. You can upload many photos at once by
		holding down the shift key.
		<br />
		<br />
		Uploaded photos will automatically be added to a gallery at the end of your  post.
		If you would instead like the photo to appear inline with your text click on the
		<img src="<?=$directory?>images/insertlink.png" style="vertical-alignment:middle"> icon. The
		<img src="<?=$directory?>images/gallery.png" style="vertical-alignment:middle"> icon can be used to toggle whether
		an image should be included in the gallery.
	</small></p>

	<div id="imageContainer" class="imageContainer">
	<?php if(sizeof($post->images) == 0 ) { ?>
	<span id="noImagesMessage">There are no images attached to this post.</span>
	<?php } else { ?>
	 <?php foreach($post->images as $image ) { ?>
		<div class="photo" id="image_<?=$image->id?>" onmouseover="Util.Show('toolbar_<?=$image->id?>')" onmouseout="Util.Hide('toolbar_<?=$image->id?>')">
			<a href="<?=$image->getLarge()?>" rel="lightbox"><img class="photoThumbnail" src="<?=$image->getSquare()?>"></a>
			<div class="imageToolbar" id="toolbar_<?=$image->id?>" style="display:none">
				<span class="imageToolbarIcon" onclick="NewPost.ImageDeleteClick(this)" style="float:right"><img src="<?=$directory?>images/delete.png" ></span>
				<span class="<?=($image->inGallery?"imageToolbarIconOn":"imageToolbarIcon")?>" onclick="NewPost.ImageToggleInGallery(this)" id="attachIcon<?=$image->id?>"><img src="<?=$directory?>images/gallery.png"></span>
				<span class="imageToolbarIcon" onclick="NewPost.ImageInsertLink(this)" ><img src="<?=$directory?>images/insertlink.png" ></span>
			</div>
		</div>
		<?php } ?>
	<?php } ?>
	<div id="imageClear" class="clear"></div>
	</div>

	<div id="SWFUploadTarget" style="clear:both">
		In order to upload files you must have flash installed.
	</div>


	<div id="uploadProgress" style="display:none;font-size:10pt">
		<b>Total Progress</b> <div id="totalProgressPercent" style="display:inline"></div><div id="totalProgressText" style="display:inline"></div>
		<div class="progressbarContainer"><div class="progressbar" id="overallProgress"></div></div>
		<div style="display:inline" id="fileProgressContainer">
			<b>File Progress</b> <div id="imagePercent" style="display:inline"></div>
			<div class="progressbarContainer"><div class="progressbar" id="fileProgress"></div></div>
		</div>
	</div>
</li>


<li>
	<img src="<?=$directory?>images/files.png" style="vertical-align:middle">  <a href="javascript:Util.Toggle('Files');">Files</a>
</li>
<li id="Files" style="display:none">
	<p class="instruct " id="instruct5"><small>Select files to attach to your post. You can select multiple files by holding down the shift key.</small></p>

	<div id="fileContainer" class="fileContainer">
	<?php if(sizeof($post->files) == 0 ) { ?>
	<div id="noFilesMessage">There are no files attached to this post.</div>
	<?php } else { ?>
	<?php foreach($post->files as $file ) { ?>
		<div id="file_<?=$file->id?>">
			<img class="fileIcon" src="<?=$directory?>images/icons/<?=$file->getExtType()?>.png">
			<a href="<?=$file->getPath()?>" target="EditorFile"><?=$file->originalname?></a>
			<a href="javascript:NewPost.DeleteFile(<?=$file->id?>)" ><img class="closeIcon" src="<?=$directory?>images/delete.png" title="Delete File"></a>
		</div>
	<?php } ?>
	<?php } ?>
	</div>

	<div id="FileSWFUploadTarget" style="clear:both">
		In order to upload files you must have flash installed.
	</div>


	<div id="fileUploadProgress" style="display:none;font-size:10pt">
		<b>Total Progress</b> <div id="fileTotalProgressPercent" style="display:inline"></div><div id="fileTotalProgressText" style="display:inline"></div>
		<div class="progressbarContainer"><div class="progressbar" id="fileOverallProgress"></div></div>
		<div style="display:inline" id="fileFileProgressContainer">
			<b>File Progress</b> <div id="filePercent" style="display:inline"></div>
			<div class="progressbarContainer"><div class="progressbar" id="fileFileProgress"></div></div>
		</div>
	</div>
</li>



<li>
	<img src="<?=$directory?>images/map.png" style="vertical-align:middle">  <a href="javascript:Util.Toggle('Address');Util.Focus('Field106')">Address and Map</a>
</li>
<li id="Address" style="display:none">
	<div style="padding-top:5px;">
	<input id="Field106" onclick="NewPost.OnChange()"
	   name="address"
	   class="field text large"
	   type="text" maxlength="255" value="<?=$post->address?>"
	 />
	</div>
	<p class="instruct " id="instruct6"><small>Enter an address that you would like to be attached to your post.
	This address will appear on an interactive map on your website. You can use this feature to provide directions
	to different events.
	</small></p>

</li>

<li>
	<img src="<?=$directory?>images/tags.png" style="vertical-align:middle">  <a href="javascript:Util.Toggle('Tags');Util.Focus('Field103')">Tags</a>
</li>
<li id="Tags" style="display:none">
	<div style="padding-top:5px;">
	<input id="Field103" onclick="NewPost.OnChange()"
	   name="tags"
	   class="field text large"
	   type="text" maxlength="255" value="<?=$post->getTagString()?>"
	 />
	</div>
	<p class="instruct " id="instruct103"><small>Enter any tags that you want to label this post with. Separate tags using commas. For example: "Family, Fun, Vacation".
	By adding tags you make it easier for you're visitors to find content.</small></p>
</li>

</ul>
</form>
<div style="clear:both"></div>

<script type="text/javascript">
	NewPost.SetSwfuPath("<?=$SiteRoot?>plugins/swfupload/SWFUpload.swf");
	NewPost.SetImageUploadScript("<?=$PHP_SELF?>?event=UploadImage&ajaxpost=1&PHPSESSID=<?=session_id()?>&postid=<?=$post->id?>");
	NewPost.SetFileUploadScript("<?=$PHP_SELF?>?event=UploadFile&ajaxpost=1&PHPSESSID=<?=session_id()?>&postid=<?=$post->id?>");
	NewPost.SetBinDir("<?=$directory?>");
	NewPost.Init(<?=$post->id?>);
	NewPost.SetupSwfu();
</script>

<?php
/*
Sample form element for testing when swfu uploader is not being used.
<!-- <form action="<?=$PHP_SELF?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="event" value="uploadImage" />
			<input type="hidden" name="postid" value="<?=$post->id?>" />
			<input type="file" name="Filedata" />
			<input type="submit" value="Upload" />
	</form>
*/

?>