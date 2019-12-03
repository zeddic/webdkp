<?php
include_once("bbcodeContent.php");

/*====================================================
A part that allows the user to enter arbitrary html
for an area on the site
=====================================================*/
class partBbcode extends part {
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

		$content = new bbcodeContent();
		$content->loadFromDatabaseByInstance($this->id);

		//get the content
		$html = $content->content;
		$html = bbcode::parseBBCode($html);

		//make sure any bad html is closed
		$html = util::correctHtml($html);


		$this->set("content",$content);
		$this->set("html",$html);

		return $this->fetch("content.tmpl.php");
	}

	/*===================================
	Views an editor where the content can be modified
	===================================*/
	function viewEdit() {

		$this->renderAlone = 1;
		$this->useBorder = 1;
		$this->border = 1;
		$this->title = "Editing Content";

		//disable indenting the the templates while on this page (confuses the textarea)
		framework::useTemplateIndents(false);

		//use js code that supports tabbing, and saving content via an
		//ajax call
		$this->addJavascriptHeader($this->binDirectory."js/part.js");
		$this->addCSSHeader($this->binDirectory."css/edit.css");

		//get a list of images that are available to insert
		$images = image::getImagesWithContext("bbcodepart/$this->id");

		$content = new bbcodeContent();
		$content->loadFromDatabaseByInstance($this->id);
		$this->set("content",$content);
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


		$data = $this->getData("content");
		$content = new bbcodeContent();
		$content->loadFromDatabaseByInstance($this->id);
		$content->content = $data;
		if($content->id != "") {
			$content->save();
		}
		else {
			$content->iid = $this->id;
			$content->saveNew();
		}
		//$this->setEventResult(true,"Changes Saved");

		echo(util::json(array(true,"Changes Saved")));

	}
	/*==============================================
	AJAX
	Converts the passed bbcode data into html fit for display
	==============================================*/
	function ajaxConvertBBCode() {
		//get the content
		$content = $this->getData("content");
		$content = stripslashes($content);

		$parser = new bbcode();
		$content = $parser->parseBBCode($content);

		//make sure any bad html is closed
		$content = util::correctHtml($content);

		echo($content);
	}


	/*==============================================
	EVENT
	Event from editor. Uploads a new image to the media
	directory that can be added to other pages
	==============================================*/
	function eventUploadImage(){
		$image = new image();
		//upload image to is new home
		$image->uploadImage("site/media/images/bbcode/part$this->id","file");
		$image->context = "bbcodepart/$this->id";
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
		$image = new image();
		$image->loadFromDatabase($imageid);
		$image->delete();
	}

	/*===================================
	A utility method made available for the setup program. Lets this part
	have its html artibrarily set
	===================================*/
	function setContent($content){
		$bbcodeContent = new bbcodeContent();
		$bbcodeContent->loadFromDatabaseByInstance($this->id);
		$bbcodeContent->content = $content;
		if($bbcodeContent->id != "") {
			$bbcodeContent->save();
		}
		else {
			$bbcodeContent->iid = $this->id;
			$bbcodeContent->saveNew();
		}
	}
	/*===================================
	Deletes part from the database
	===================================*/
	function delete(){
		//delete any images that are left over
		fileutil::rmdir("site/media/images/bbcode/part$this->id");
		parent::delete();
	}
}
?>