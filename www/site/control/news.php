<?php
include_once("util/pager.php");
include_once("lib/news/post.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageIndex extends page {

	//var $googleMapsKey = "ABQIAAAAvQTQ4gRdXYYWRzKabBC54RQMosuk0c39nKIN_HkF2tnoQZeiLxTAd8ViUdw2P8XzJYkSittb2e367w"; //ironclaw
	//var $googleMapsKey = "ABQIAAAARFjnHHSwNMqfUGO4WdA5WBSS1pNjusDlc2Um3mbr_Jews9TOAxTdYbYzAbBQYhR79jvZunH9g-9v7A"; //ocas
	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area2()
	{
		global $sql;
		//create a pager to help us split our data into pages
		$pager = new pager("posts_page",5,false);		//page variable name, # of posts per page, don't save page in session
		$pager->pageUrl = $_SERVER["PHP_SELFDIR"];	//url base for every page
		$pager->useDirectoryLinks = true;			//signals we want to use directories as page numbers: site\path\1, site\path\2, etc.
		$pager->carryQueryStrings = false;			//don't carry query strings between pages

		//get the current page of posts. Put this in a loop in case we need to backtrack
		//to a previous page because the current requested page is empty.
		$table = post::tablename;
		do {
			$result = $sql->Query("SELECT * FROM $table WHERE status=1 ORDER BY created DESC $pager->pageQueryString");
			if($sql->a_rows > 0 || $pager->page == 1)
				break;
			$pager->page--;
			$pager->handlePages($pager->page);
		}while(true);

		if($sql->a_rows == 0 ) {
			$this->title = "Welcome";
			$this->border = 1;
			return "Welcome. There is currently no published posts on this site.";
		}


		//we have post data now, parse it into an array
		//each entry will be prerended html
		$needGoogleMaps = false;
		$posts = array();
		while($row = mysqli_fetch_array($result)) {
			$post = new post();
			$post->loadFromRow($row);
			$post->isPreview = true;
			$post->content = bbcode::parseBBCode($post->content);
			if($post->address != "")
				$needGoogleMaps = true;

			//call the template for a single postm
			//note that a single template is used for a post, regardless
			//of whether it is in the main page or the single view. The
			//variable isPreview will allow the template to differentiate as needed
			$template = new template($this->templateDirectory,"post.tmpl.php");
			$template->set("post",$post);
			$template->set("directory",$this->binDirectory);
			$content = $template->fetch();

			//save the rendered html
			$posts[] = $content;
		}

		//only include the googe maps reference if we need it (its veerrry slow)
		//if($needGoogleMaps)
		//	$this->addJavascriptHeader("http://maps.google.com/maps?file=api&amp;v=2&amp;key=$this->googleMapsKey");

		//get some final data for the template
		$numRows = $sql->QueryItem("SELECT count(id) FROM $table");
		$maxPage = $pager->getMaxPage($numRows);

		//determine if we have next or previous buttons on the page
		$page = $pager->page;
		if($page < $maxPage )
			$hasNextPage = true;
		if($page > 1 )
			$hasPrevPage = true;

		//add a few things to the session that will allow the user to browse
		//back to the relevant page if they click on a detailed view of a
		//news post
		util::saveInSession("news_lastView","date");
		util::saveInSession("news_lastViewedPage",$page);


		$this->addCSSHeader($this->binDirectory."css/posts.css");
		$this->addJavascriptHeader($this->binDirectory."js/posts.js");

		//call the template now
		//$this->title = "News";
		$this->set("page",$page);
		$this->set("hasNextPage",$hasNextPage);
		$this->set("hasPrevPage",$hasPrevPage);
		//$this->set("pageLinks",$pageLinks);
		$this->set("posts",$posts);

		return $this->fetch("posts.tmpl.php");
	}

	/*=================================================
	View - Shows a single news post to the user
	=================================================*/
	function area2Post()
	{

		$postid = util::getData("postid");

		$page = util::getData("posts_page");
		$post = new post();
		$post->loadFromDatabase($postid);
		$post->content = bbcode::parseBBCode($post->content);
		if($post->id == "")
			return $this->area2BadPost();


		//determine how the back button should work
		$lastView = util::getData("news_lastView");
		$lastViewedPage = util::getData("news_lastViewedPage");
		$lastViewedTag = util::getData("news_lastViewedTagString");

		$backLink = $_SERVER["PHP_SELFDIR"];
		if($lastView == "" || $lastView== "date") {
			$backLink .= "page/$lastViewedPage";
		}
		else if($lastView == "tags") {
			$backLink .= "tags/$lastViewedPage/$lastViewedTag";
		}

		if($post->address != "")
			$this->addJavascriptHeader("http://maps.google.com/maps?file=api&amp;v=2&amp;key=$this->googleMapsKey");

		$this->addCSSHeader($this->binDirectory."css/posts.css");
		$this->addJavascriptHeader($this->binDirectory."js/posts.js");

		$this->set("post",$post);
		$this->set("page",$page);
		$this->set("backLink",$backLink);

		return $this->fetch("post.tmpl.php");
	}

	/*=================================================
	View - Shows the users pages of posts with a given tag
	=================================================*/
	function area2Tags()
	{

		//create a pager to help us split our data into pages
		$pager = new pager("poststags_page",5, false);		//page variable name, # of posts per page, don't save page var in a session
		$pager->pageUrl = $_SERVER["PHP_SELFDIR"];	//url base for every page
		$pager->useDirectoryLinks = true;			//signals we want to use directories as page numbers: site\path\1, site\path\2, etc.
		$pager->carryQueryStrings = false;			//don't carry query strings between pages

		global $sql;

		//get the requested tag
		$tagString = util::getData("tag");
		if($tagString == "")
			$tagString = "untagged";

		//try to load the tag
		$tag = new tag();
		if($tagString != "untagged")
			$tag->loadFromDatabaseByName($tagString);

		$mapTable = tag::maptablename;
		$table = post::tablename;

		//if the tag existed, grab all posts that match it
		if($tag->id != "") {
			//find the ids of all the posts with the given tag
			$result = $sql->Query("SELECT * FROM $mapTable WHERE tag='$tag->id'");
			$postids = array();
			//create a compound where clause
			while($row = mysqli_fetch_array($result)){
				$postid = $row["post"];
				$temp = "id = $postid";
				$postids[] = $temp;
			}
			//get the posts
			$clause = implode(" OR ", $postids);

			do {
				$result = $sql->Query("SELECT * FROM $table WHERE $clause ORDER BY created DESC $pager->pageQueryString");
				if($sql->a_rows > 0 || $pager->page == 1)
					break;
				$pager->page--;
				$pager->handlePages($pager->page);
			}while(true);


			$numRows = $sql->QueryItem("SELECT count(id) FROM $table WHERE $clause ");
		}
		//if tag dosn't exist assume we want all posts that have no tags
		else if($tagString == "untagged"){
			//query is done this way to improve database performance
			do {
				$result = $sql->Query("SELECT * FROM $table WHERE id NOT IN (
									SELECT post FROM (( SELECT post FROM $mapTable ) as temp )
								  ) ORDER BY created DESC $pager->pageQueryString");
				if($sql->a_rows > 0 || $pager->page == 1)
					break;
				$pager->page--;
				$pager->handlePages($pager->page);
			}while(true);


			$numRows = $sql->QueryItem("SELECT count(id) FROM $table WHERE id NOT IN (
									SELECT post FROM ( ( SELECT post FROM $mapTable ) as temp)
								  ) ");
		}

		//load the posts
		$posts = array();
		$needGoogleMaps = false;
		if($result) {
			while($row = mysqli_fetch_array($result)) {
				$post = new post();
				$post->loadFromRow($row);
				$post->isPreview = true;

				if($post->address != "")
					$needGoogleMaps = true;

				//call the template for a single postm
				//note that a single template is used for a post, regardless
				//of whether it is in the main page or the single view. The
				//variable isPreview will allow the template to differentiate as needed
				$template = new template($this->templateDirectory,"post.tmpl.php");
				$template->set("post",$post);
				$template->set("directory",$this->binDirectory);
				$content = $template->fetch();

				$posts[] = $content;
			}
		}

		//only load google maps if needed (its very slow)
		if($needGoogleMaps)
			$this->addJavascriptHeader("http://maps.google.com/maps?file=api&amp;v=2&amp;key=$this->googleMapsKey");

		//get some final data for the template
		//echo($numRows);
		$maxPage = $pager->getMaxPage($numRows);
		$page = $pager->page;

		if($page < $maxPage )
			$hasNextPage = true;
		if($page > 1 )
			$hasPrevPage = true;




		//save some data in a session taht will allow a user to go back
		//to the current page
		if(count($posts) > 0 ) {
			util::saveInSession("news_lastView","tags");
			util::saveInSession("news_lastViewedPage",$page);
			util::saveInSession("news_lastViewedTagString",$tagString);
		}

		$this->addCSSHeader($this->binDirectory."css/posts.css");
		$this->addJavascriptHeader($this->binDirectory."js/view.js");

		//call the template now
		$this->title = "News - $tagString";
		$this->set("page",$page);
		$this->set("hasNextPage",$hasNextPage);
		$this->set("hasPrevPage",$hasPrevPage);
		$this->set("posts",$posts);
		$this->set("tagString",$tagString);




		return $this->fetch("tags.tmpl.php");
	}



	/*=================================================
	Shows an error that the user requested a bad post.
	=================================================*/
	/*function area2BadPost()
	{
		$this->title = "Bad Post";
		return $this->fetch("badpost.tmpl.php");
	}*/
}

?>