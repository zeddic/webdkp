<?php
include_once("htmlContent.php");
//include_once("plugins/fckeditor/fckeditor.php");

/*====================================================
A part that allows the user to enter arbitrary html
for an area on the site
=====================================================*/
class partHtml extends part {
	//the view that should be shown by default
	var $defaultView = "Content";
	//var $name = "content";
	//views that should be rendered alone, full screen
	var $renderAlone = array("edit");
	//the view that should be used for editing the part
	var $editView = "edit";

	/*===================================
	Views the html content. This is the view mode, and does
	not allow anything to be edited. it just echos the html
	to the page
	===================================*/
	function viewContent(){

		$htmlContent = & new htmlContent();
		$htmlContent->loadFromDatabaseByInstance($this->id);

		$this->set("html",$htmlContent);
		//$this->set("canEdit",security::userHasAccess("Edit HTML Pages"));
		$this->set("canEdit",true);
		return $this->fetch("content.tmpl.php");
	}

	/*===================================
	Views an editor where the content can be modified
	===================================*/
	function viewEdit() {

		$this->renderAlone = 1;
		$this->useBorder = 1;
		$this->border = 1;
		$this->title = "Editing HTML";

		//disable indenting the the templates while on this page (confuses the textarea)
		framework::useTemplateIndents(false);

		//use js code that supports tabbing, and saving content via an
		//ajax call
		$this->addJavascriptHeader($this->binDirectory."js/htmlpart.js");
		$this->addCSSHeader($this->binDirectory."css/edit.css");

		//get a list of images that are available to insert
		$images = image::getImagesWithContext("htmlpart$this->id");

		$htmlContent = & new htmlContent();
		$htmlContent->loadFromDatabaseByInstance($this->id);
		$this->set("html",$htmlContent);
		$this->set("images",$images);
		$this->set("uploadedImage",util::getData("event$this->id")=="uploadImage");
		//carrys over text that was present when an image was upload (even if it wasn't explicitly saved)
		$this->set("tempcontent",stripslashes(util::getData("tempcontent")));

		return $this->fetch("edit.tmpl.php");
	}

	/*===================================
	An ajax call issued by the editor to save
	content.
	===================================*/
	function ajaxEdit(){
		//stop the template system from indenting
		//(this screws up text area boxes)


		$content = $this->getData("content");
		$htmlContent = & new htmlContent();
		$htmlContent->loadFromDatabaseByInstance($this->id);
		$htmlContent->content = $content;
		if($htmlContent->id != "") {
			$htmlContent->save();
		}
		else {
			$htmlContent->iid = $this->id;
			$htmlContent->saveNew();
		}
		//$this->setEventResult(true,"Changes Saved");

		echo(util::json(array(true,"Changes Saved")));

	}

	/*===================================
	An ajax call to validate html - making
	sure that there are no unopened tags
	or tags that were not closed
	===================================*/
	function ajaxCheckHtml(){

		//get the content
		$content = $this->getData("content");
		$content = stripslashes($content);

		//correct it if it is bad. If something had to be fixed
		//problem will be set to true and unclosed tags will hold an array of
		//tags that were not closed correctly.
		$content = util::correctHtml($content, $problem, $unclosedTags, $unopenedTags);

		echo(util::json(array($content, $problem, implode($unclosedTags,"<br />"),implode($unopenedTags,"<br />"))));

	}

	/*==============================================
	EVENT
	Event from editor. Uploads a new image to the media
	directory that can be added to other pages
	==============================================*/
	function eventUploadImage(){
		$image = & new image();
		//upload image to is new home
		$image->uploadImage("site/media/images/html/part$this->id","file");
		$image->context = "htmlpart$this->id";
		$image->createLarge();
		$image->createMedium();
		$image->createSmall();
		$image->createThumbnail();
		$image->createSquare(100);
		$image->saveNew();

		$this->setEventResult(true,"Image Uploaded");
	}

	/*==============================================
	AJAX
	An ajax call from the editor - deletes the image background
	with the given id
	==============================================*/
	function ajaxDeleteImage(){
		$imageid = util::getData("image");
		$image = & new image();
		$image->loadFromDatabase($imageid);
		$image->delete();
	}

	/*===================================
	A utility method made available for the setup program. Lets this part
	have its html artibrarily set
	===================================*/
	function setContent($content){
		$htmlContent = & new htmlContent();
		$htmlContent->loadFromDatabaseByInstance($this->id);
		$htmlContent->content = $content;
		if($htmlContent->id != "") {
			$htmlContent->save();
		}
		else {
			$htmlContent->iid = $this->id;
			$htmlContent->saveNew();
		}
	}
	/*===================================
	Deletes part from the database
	===================================*/
	function delete(){
		//delete any images that are left over
		fileutil::rmdir("site/media/images/html/part$this->id");
		parent::delete();
	}
}
?>