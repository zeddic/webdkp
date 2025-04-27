<?php
/*===========================================================
PAGING METHODS
The pager class is a utility class that makes it easier to
browse database by content by pages.

This class will help create a clause to add at the end of
mysql queries to limit returned rows based on the current page number.
If also provides links that can be rendered to the final page that
will allow the user to move between pages.
============================================================*/
include_once("core/util/util.php");
class pager {
	/*===========================================================
	LOCAL VARIABLES
	============================================================*/
	//The name of the variable to used when moving between pages
	//This will be placed in a query string in the form of page=1, page=2, etc.
	var $pageVariable = "page";
	//The number of database rows that make up a single page. Set w/
	//constructor. If changed at runtime via property, user must recall
	//'handlePages'
	var $rowsPerPage = 10;
	//If set to true the pager will generate page links using directories
	//relative to the current path.
	//Example: page 1 would be represented as site\news\1\
	var $useDirectoryLinks = false;
	//If true the pager will carry over the current set of page query string
	//paramters on each of the page links
	var $carryQueryStrings = true;
	//A string to append after the page number in the page link
	//Example, if the page link were going to turn out as http://site.com/New/1
	//and $appendAfterPage was set to "/ascuser"
	//the final link would appear as "http://site.com/New/1/ascuser"
	var $appendAfterPage = "";
	//holds the current page as determined via looking at get/post/or session
	//set at run time
	var $page;
	//The url that the pager will append in front of every page link. Excludes
	//query strings.
	var $pageUrl;
	//if set to true the current page number will be remembered and restroed from
	//a session variable
	var $savePageInSession = true;
	/*===========================================================
	DEFAULT CONSTRUCTOR
	$pageVariable -  Sets the variable that will be used between
					 page loads to specify what page is currently
					 being viewed. Will be stored in a session.
					 Should be made specific to the place using
					 paging. Example: page5 or pageNews
	$rowsPerPage - 	 The number of rows that should be on each page.
	============================================================*/
	function __construct($pageVariable = null, $rowsPerPage = null, $savePageInSession = -1){
		if( $pageVariable != null )
			$this->pageVariable = $pageVariable;
		if( $rowsPerPage != null )
			$this->rowsPerPage = $rowsPerPage;
		if( $savePageInSession != -1 ) {
			$this->savePageInSession = $savePageInSession;
		}
		$this->handlePages();
	}

	/*===========================================================
	handlePages
	Generates a string to be used at the end of the an sql query.
	When added to the end of a query the returned contents will be
	limited to the current page

	Will return the query string as well as store it in the class
	variable $pageString

	Can optionally be passed $page will tell it the current page number,
	bypassing its checks into get/post/or session.
	============================================================*/
	function handlePages($page = null){
		if(!$this->savePageInSession)
			util::clearFromSession($this->pageVariable);
		if ($page == null) {
			$this->page = util::getData($this->pageVariable);
		}

		if(empty($this->page) || $this->page==0) {
			$this->page = 1;
		}

		if($this->savePageInSession) {
			util::saveInSession($this->pageVariable,$this->page);
		}

		$pageOffset = ($this->page - 1 ) * $this->rowsPerPage;

		$this->pageQueryString = " LIMIT $pageOffset, $this->rowsPerPage";
	}

	/*===========================================================
	getMaxPage
	Returns the maximum page number given the total number of rows
	============================================================*/
	function getMaxPage($totalRows) {
		$maxPage = ceil($totalRows/$this->rowsPerPage);
		return $maxPage;
	}

	/*===========================================================
	getPageLinks
	Generates an array of different links that represents forward and back buttons
	for browsing the different pages of a database table.
	It must be passed the total number of rows in the given table so that it knows
	the maximum page. It uses $this->rowsPerPage and $this->page for additional information.
	Please see 'Returns:' for additional information on the array data structure that this
	uses.

	Parameters:		The total maximum number of rows in the database / table being browsed
	Returns:		An associative array representing links to navigate the different pages.
					Array is structured as follows:
					[0] = [type]	//The type of link. "first", "prev" "this"
									//"link" "next" "last" (describes what the link is linking to)
						  [link]	//The actual url of the link
						  [name]	//The name for the link
					[1] = [type]
						  [link]
						  [name]
					...
	============================================================*/
	function getPageLinks($totalRows){
		//determine the max page
		$maxPage = ceil($totalRows/$this->rowsPerPage);



		//construct the array
		$links = array();

		//create the quick links for 'first' and 'prev'
		if ($this->page > 1) {
		   $page  = $this->page - 1;

		   $links[] = array ("type"=>"first" , "link"=>$this->createPageLink(1) , "name"=>"First");
		   $links[] = array ("type"=>"prev" , "link"=>$this->createPageLink($page) , "name"=>"Prev");
		}

		//Create all the page numbers + the current page number
		for($page = max($this->page-2,1); $page <= min($maxPage,$this->page+2); $page++) {
			if ($page == $this->page) {
				$links[] = array ("type"=>"this" , "link"=>"javascript:;" , "name"=>$page);
			}
			else {
				$links[] = array ("type"=>"link" , "link"=>$this->createPageLink($page) , "name"=>$page);
			}
		}

		if ($this->page < $maxPage) {
		   $page  = $this->page + 1;
		   $links[] = array ("type"=>"next" , "link"=>$this->createPageLink($page) , "name"=>"Next");
		   $links[] = array ("type"=>"last" , "link"=>$this->createPageLink($maxPage) , "name"=>"Last");
		}

		return $links;
	}

	/*===========================================================
	Generates a link to the given page
	============================================================*/
	function createPageLink($pageNumber){
		//get the url base
		if(empty($this->pageUrl))
			$this->pageUrl = $_SERVER["PHP_SELF"];

		//get the current query strings in case we need to carry them
		//from page to page
		if(empty($this->queryString)) {
			$this->queryString = $this->rebuildQueryString();
		}


		if($this->useDirectoryLinks ) {
			$link = $this->pageUrl."/".$pageNumber;
		}
		else {
			$link =	"$this->pageUrl?$this->pageVariable=$pageNumber";
		}

		$link.=$this->appendAfterPage;

		if($this->carryQueryStrings && $this->queryString != "") {
			if(strpos($link,"?")===false)
				$link.="?";
			else
				$link.="&";
			$link.=$this->queryString;
		}

		return $link;
	}

	/*===========================================================
	getHtmlPageLinks
	Generates an array of links that allows users to browse the different pages of a data
	set. The links call the current page over again, but puts an appropriate $page variable
	in the query string. Note - the link is raw html and can be echoed directly to the page.
	Parameters:		TotalRows - The total / maixum number of sql rows in the data being browsed
	Returns:		An array of strings, each string represents a link to an appropriate page
	============================================================*/
	function getHtmlPageLinks($totalRows)
	{
		//get the current links
		$links = $this->getPageLinks($totalRows);

		//iterate through them and build an array of html links
		$toReturn = array();
		foreach($links as $link) {
			$type = $link["type"];
			$url = $link["link"];
			$name = $link["name"];
			if($link["type"]=="this")
				$toReturn[]="<b>$name</b>";
			else if($link["type"]=="first")
				$toReturn[]="<a href=\"$url\">«« first</a>&nbsp;";
			else if($link["type"]=="prev")
				$toReturn[]="<a href=\"$url\">« prev</a>&nbsp;";
			else if($link["type"]=="next")
				$toReturn[]="<a href=\"$url\">&nbsp;next »</a>&nbsp;";  //» «
			else if($link["type"]=="last")
				$toReturn[]="<a href=\"$url\">last »»</a>";
			else if($link["type"]=="link")
				$toReturn[]="<a href=\"$url\">$name</a>";
		}
		return $toReturn;
	}

	/*===========================================================
	getRawHtmlPageLinks
	Returns an html string that allows a user to page between values. This
	string could be echoed straight to the template.
	Parameters:		TotalRows - The total / maixum number of sql rows in the data being browsed
	Returns:		A straight string that represents links to different pages
	============================================================*/
	function getRawHtmlPageLinks($totalRows){
		//get the html links in array form
		$htmlLinks = $this->getHtmlPageLinks($totalRows);
		//combine them into a single string
		$toReturn = implode(" ",$htmlLinks);
		return $toReturn;
	}

	/*===========================================================
	getPageOf
	Helper method for modules using pages. Returns a simple string that tells what the current
	and max pages are. Example: Page 1 of 2
	Requires the total number of rows in the given
	database table that is being paged (so it can determine the max page)
	============================================================*/
	function getPageOf($totalRows){
		$maxPage = ceil($totalRows/$this->rowsPerPage);
		return "Page <b>$this->page</b> of <b>$maxPage</b> <br />";
	}

	/*===========================================================
	setRowsPerPage
	Sets the number of rows of database items that should be used on
	a single page. Use this instead of changing property directly.
	============================================================*/
	function setRowsPerPage($rowsPerPage){
		$this->rowsPerPage = $rowsPerPage;
		$this->handlePages();
	}
	/*===========================================================
	setRowsPerPage
	Sets the variable to use when creating page strings and
	looking for the current page in get/post/session.
	============================================================*/
	function setPageVariable($pageVariable){
		$this->pageVariable = $pageVariable;
		$this->handlePages();
	}

	/*===========================================================
	rebuildQueryString
	Returns the url to the current page (including
	all current query strings)
	============================================================*/
	function rebuildQueryString(){
		$queryString = $_SERVER["QUERY_STRING"];

		//while rebuilding the query string remove any old page parameters
		//(so we don't end up pathing them twice and ending up with page=1&page=1
		$this->removeQueryStringVar($queryString,$this->pageVariable);

		//remove the url= query string too
		$this->removeQueryStringVar($queryString,"url");

		return $queryString;
	}

	/*===========================================================
	Removes the given variable name from a query string.
	For example, if the query string were "?name=bob&age=5" and
	this method were called to remove the varible "name",
	the query string would be modified to
	"?age=5"
	$queryString - the query string to modify. Passed by reference
	$value - the name of the variable to prune
	============================================================*/
	function removeQueryStringVar(&$queryString, $value){
		//remove the url= query string too
		$value .= "=";
		$badParamStart = strpos($queryString,$value);
		if($badParamStart!==false)	{
			//find the end of the bad parameters
			$badParamEnd = strpos($queryString,"&",$badParamStart+1);
			if($badParamEnd === false)
				$badParamEnd = strlen($queryString);
			else
				$badParamEnd++;
			//cut out the bad parameters
			$start = substr($queryString,0,$badParamStart);
			$end = substr($queryString,$badParamEnd,strlen($queryString));
			$queryString = $start.$end;
		}
	}
}

?>