<?php
include_once("core/general/file.php");
include_once("core/general/image.php");
include_once("lib/news/post.php");
include_once("lib/news/tag.php");
include_once("util/pager.php");
/*===========================================================
Controller
Allows a user to administer a mailing list. They can:
- view the mailing list
- edit entries
- delete entries
- upload files
============================================================*/
class pageNewsIndex extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//Use another static page as a template
	//It will set our layout
	var $useTemplate = true;
	var $template = "admin/content/template";
	var $system = 1;

	var $postsPerPage = 15;

	/*===========================================================
	Center page. Default to managing news
	============================================================*/
	function area2(){
		$this->addJavascriptHeader($this->binDirectory."js/admin.js");
		$this->addCSSHeader($this->binDirectory."css/admin.css");
		$this->border = 1;

		global $sql;
		$table = post::tablename;

		$publishedPage = util::getData("posts_published_page",1);
		$publishedMaxPage = floor($sql->QueryItem("SELECT count(id) FROM $table WHERE status = '1'")/$this->postsPerPage)+1;
		$unpublishedPage = util::getData("posts_unpublished_page",1);
		$unpublishedMaxPage = floor($sql->QueryItem("SELECT count(id) FROM $table WHERE status != '1'")/$this->postsPerPage)+1;

		$this->title = "Manage News";
		//$this->set("posts",$posts);
		$this->set("activeTab",util::getData("active_posts_tab","Unpublished"));
		$this->setVars(compact("publishedPage","publishedMaxPage","unpublishedPage","unpublishedMaxPage"));
		return $this->fetch("admin.tmpl.php");
	}

	/*===========================================================
	VIEW
	Breadcrumbs for the top of the page
	============================================================*/
	function area3(){
		global $SiteRoot;
		if($this->breadcrumbs == "") {
			$breadcrumbs = array();
			$breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
			$breadcrumbs[] = array("Content",$SiteRoot."admin/content");
			$breadcrumbs[] = array("News");
		}
		else
			$breadcrumbs = $this->breadcrumbs;
		$this->set("breadcrumbs",$breadcrumbs);
		return $this->fetch("breadcrumbs.tmpl.php");
	}

	/*=================================================
	Ajax call for the main admin page. Returns a html table
	of published posts.
	=================================================*/
	function ajaxGetPublished()
	{
		//create a pager to help us split our data into pages
		$pager = new pager("posts_published_page",$this->postsPerPage);		//page variable name, # of posts per page
		$pager->pageUrl = $_SERVER["PHP_SELF"];	//url base for every page

		//get the pages
		global $sql;
		$table = post::tablename;
		$result = $sql->Query("SELECT * FROM $table WHERE status = '1' ORDER BY created DESC $pager->pageQueryString");
		$posts = sql::LoadObjects($result, "post");

		//pass the data to the template
		$this->set("posts",$posts);
		$this->set("page",$pager->page);
		$this->set("pageLinks",$pageLinks);
		echo $this->fetch("adminPublished.tmpl.php");
	}

	/*=================================================
	Ajax call for the main admin page. Returns a html table
	of unpublished posts.
	=================================================*/
	function ajaxGetUnpublished()
	{
		//create a pager to help us split our data into pages
		$pager = new pager("posts_unpublished_page",$this->postsPerPage);		//page variable name, # of posts per page
		$pager->pageUrl = $_SERVER["PHP_SELF"];	//url base for every page

		global $sql;
		$table = post::tablename;
		$result = $sql->Query("SELECT * FROM $table WHERE status != '1' ORDER BY created DESC $pager->pageQueryString");
		$posts = sql::LoadObjects($result, "post");


		$this->set("posts",$posts);
		$this->set("page",$pager->page);
		echo $this->fetch("adminUnpublished.tmpl.php");
	}

	/*=================================================
	Ajax call for the main admin page. Saves the currently
	selected tab in a session.
	=================================================*/
	function ajaxSaveActiveTab() {
		$tab = util::getData("tab");
		util::saveInSession("active_posts_tab",$tab);
	}

	/*=================================================
	Ajax call for the main admin page. Returns the maximum
	possible page for unpublished parts
	=================================================*/
	function ajaxGetUnpublishedMaxPage() {
		global $sql;
		$table = post::tablename;
		$numRows = $sql->QueryItem("SELECT count(id) FROM $table WHERE status != '1'");
		$numRows = floor($numRows/$this->postsPerPage)+1;
		echo $numRows;
		return;

	}
	/*=================================================
	Ajax call for the main admin page. Returns the maximum
	possible page for unpublished parts
	=================================================*/
	function ajaxGetPublishedMaxPage() {
		global $sql;
		$table = post::tablename;
		$numRows = $sql->QueryItem("SELECT count(id) FROM $table WHERE status = '1'");
		$numRows = floor($numRows/$this->postsPerPage)+1;
		echo $numRows;
		return;
	}

	/*=================================================
	Ajax call for the main admin page. Deletes the given
	post specified by "postid". Returns a string in the form
	of
	#|message
	# may be 0 for failure or 1 for success.
	=================================================*/
	function ajaxDeletePost() {
		$postid = util::getData("postid");
		if($postid == 0 ) {
			echo("0|Post <b>not</b> deleted. Invalid id passed.");
			return;
		}

		$post = new post();
		$post->loadFromDatabase($postid);
		if($post->id == "") {
			echo("0|Post <b>not</b> deleted. Invalid id passed.");
			return;
		}

		$post->delete();
		echo("1|Post $post->id Deleted");

	}


	/*=================================================
	Edit view for administration. Displays a single
	news post and allows the user to edit it.
	=================================================*/
	function area2Edit()
	{
		global $SiteRoot;

		$this->breadcrumbs = array();
		$this->breadcrumbs[] = array("Control Panel",$SiteRoot."admin");
		$this->breadcrumbs[] = array("Content",$SiteRoot."admin/content");
		$this->breadcrumbs[] = array("News",$SiteRoot."admin/content");
		$this->breadcrumbs[] = array("News Post");

		//change the layout to be a single column
		//$this->changeLayout("Columns1");

		//get the post
		$postid = util::getData("postid");
		$post = new post();
		$post->loadFromDatabase($postid);

		//add some extra headers
		global $SiteRoot;
		$this->addJavascriptHeader($this->binDirectory."js/edit.js");
		$this->addCSSHeader($this->binDirectory."css/edit.css");
		$this->border = 1;
		if($postid!= "")
			$this->title = "Edit Post";
		else
			$this->title = "Create New Post";

		//determine where the back link will point to
		$returnto = util::getData("returnto","admin");
		//back to a list of all posts. This is a content
		if($returnto == "posts") {
			//check to see what content view the user was in when they hit the
			//edit button. ie, tag view or a normal date listing. Also determine the
			//page they were on so we can return them to their correct page.
			$lastView = util::getData("news_lastView");
			$lastViewedPage = util::getData("news_lastViewedPage");
			$lastViewedTag = util::getData("news_lastViewedTagString");
			$returnUrl = $SiteRoot."page/";

			if($lastView == "" || $lastView== "date") {
				$returnUrl .= "$lastViewedPage";
			}
			else if($lastView == "tags") {
				$returnUrl .= "tags/$lastViewedPage/$lastViewedTag";
			}
		}
		//user was looking at a single post in detail
		else if($returnto == "post")
			$returnUrl = $SiteRoot."post/".$post->id;
		//user was on the management page
		else
			$returnUrl = $_SERVER["PHP_SELF"];


		//render everything with the template
		$this->set("SiteRoot", $GLOBALS["SiteRoot"]);
		$this->set("post",$post);
		$this->set("returnUrl",$returnUrl);
		$this->set("uploadedImage",util::getData("event")=="uploadImage");

		return $this->fetch("new.tmpl.php");
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
		//determine the post we are adding the image to
		$postid = util::getData("postid");
		if($postid == "")
			return;

		//load it from the database
		$post = new post();
		$post->loadFromDatabase($postid);
		if($post->id == "")
			die();

		$image = new image();
		//upload image to is new home
		$image->uploadImage("site/media/images/news/$postid","file");
		$image->context = "news/$postid";
		$image->createLarge();
		$image->createMedium();
		$image->createSmall();
		$image->createThumbnail();
		$image->createSquare(100);
		$image->saveNew();


		$post->images[] = $image;
		$post->save();

		$this->setEventResult(true,"Image Uploaded");
	}

	/*==============================================
	AJAX
	An ajax call from the editor - deletes the image background
	with the given id
	==============================================*/
	function ajaxDeleteImage(){

		$imageid = util::getData("imageid");
		$postid = util::getData("postid");

		$post = new post();
		$post->loadFromDatabase($postid);
		$ok = $post->removeImage($imageid);
		if(!$ok)  {
			echo("0");
			return;
		}
		$post->save();
		echo("1");
	}

	/*=================================================
	AJAX - Returns the id of a newly created post
	for the user.
	=================================================*/
	function ajaxGetId(){
		$post = new post();
		$post->saveNew();
		$post->timestamp();
		echo($post->id);
	}

	/*=================================================
	EVENT - Saves changes to a news post. Does not change
			its published / unpublished status
	=================================================*/
	function ajaxSaveChanges(){
		$this->saveChanges();
		echo(util::json(array(true,"Changes Saved")));
	}

	/*=================================================
	EVENT - Saves changes to a news post and sets its
	status to published (viewable to the outside world)
	=================================================*/
	function ajaxPublish(){
		$this->saveChanges(1);
		echo(util::json(array(true,"Post Published")));
	}
	/*=================================================
	EVENT - Saves changes to a news post and unpublishes
	it.
	=================================================*/
	function ajaxUnpublish(){
		$this->saveChanges(0);
		echo(util::json(array(true,"Post Unpublished")));
	}

	/*=================================================
	Saves changes to a news post. Callback to handle
	the form submit. Accepts 1 parameter $newStatus
	which is optional.
	If $newStatus is not passed, the post will be upadted
	but its published flah will not be changed. If set
	to 0, the post will be unpublished. If set to 1, the
	post will be published.
	=================================================*/
	function saveChanges($newStatus = -1){
		//get submitted data
		$postid = util::getData("postid");
		$title = util::getData("title");
		$postContent = util::getData("content");
		$tags = util::getData("tags");

		//put it into a post instance
		$post = new post();
		$post->loadFromDatabase($postid);

		$post->title = $title;
		$post->content = $postContent;

		if($newStatus != -1)
			$post->status = $newStatus;

		//if its a new post, add it to the database,
		//otherwise, update the current entry
		if($post->id == "") {
			$post->saveNew();
			$_GET["postid"] = $post->id;
		}
		else {
			$post->save();
		}

		if($newStatus == 1 && $post->created == "0000-00-00 00:00:00")
			$post->timestamp();

		$this->updateTags($post,$tags);
	}

	/*=================================================
	Updates the tags that a given post is labeled with.
	Accepts:
	$post - reference to the post to update the tags for
	$tags - a string of tags to add. In comma seperated form
			such as "Home, Work, Conference"
	=================================================*/
	function updateTags(&$post, $tags){
		//first, clean out any old posts
		$post->clearTags();

		//add all of the tags. The post class will handle
		//splitting, formatted, adding things to the correct
		//database etc.
		$post->addtags($tags);

		//remove any tags that are no longer being used.
		tag::cleanUnusedTags();
	}
}
?>