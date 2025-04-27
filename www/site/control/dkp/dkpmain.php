<?php
include_once("lib/dkp/dkpServer.php");
include_once("lib/dkp/dkpGuild.php");
include_once("lib/dkp/dkpUpdater.php");

class pageDkpMain extends page {
	var $server;
	var $guild;
	var $serverUrlName;
	var $guildUrlName;

	/**
	 * The base url of the current guild.
	 * Example: /dkp/Stormscale/Totus+Solus/
	 */
	var $baseurl;

	/**
	 * A url for the current dkp page, relative to baseurl.
	 * Example: admin/manage
	 * This is set by classes that extend from this one.
	 */
	var $pageurl;

	var $settings;
	var $tables;
	var $updater;
	var $table;
	var $tableid;
	var $filterClause;
	var $classClause;
	var $dkpClause;

	var $page;
	var $sort;
	var $order;
	var $maxpage;

	function __construct(){
		parent::__construct();
		$this->LoadPageDetails();
		$GLOBALS["ShowAds"] = false;
	}

	function LoadPageDetails(){
		$this->guild = $this->GetGuild();
		$this->settings = $this->guild->loadSettings();
		$this->server = $this->GetServer();

		$this->pagetitle = $this->guild->name;

		$this->serverUrlName = str_replace(" ","+",$this->server->name ?? "");
		$this->guildUrlName = str_replace(" ","+",$this->guild->name ?? "");

		$this->baseurl = dkpUtil::GetGuildUrl($this->guild->id);
		$GLOBALS["baseurl"] = $this->baseurl;

		// Stop now if not on a page for a specific Guild.
		if (!$this->guild->id) {
			$this->tables = [];
			return;
		}

		$this->updater = new dkpUpdater($this->guild->id);
		$this->tables = $this->updater->GetTables(true);

		if(isset($_GET["t"])) {
			util::saveInSession($this->guild->id."_tableid", $_GET["t"]);
		}

		//get the tableid
		$tableid = util::getFromSession($this->guild->id."_tableid");

		//choose a default one if none selected
		if($tableid=="")
			$tableid = $this->tables[0]->tableid;

		//given the tableid, get the table
		foreach($this->tables as $table) {
			if($table->tableid == $tableid) {
				$this->table = $table;
				break;
			}
		}

		//if no table found with id, go to the first one
		if(!isset($this->table))
			$this->table = $this->tables[0];

		$this->tableid = $this->table->tableid;
	}

	function IsServerError(){
		return empty($this->server->id);
	}

	function ShowServerError(){
		$this->title = "Server Not Found :(";
		$this->border = 1;
		return "The server ".$this->GetServerName()." does not exist. ";
	}

	function GetTabs($active="dkp"){
		$template = new template("site/control/dkp/bin/templates/tabs.tmpl.php");
		$template->set("active",$active);
		$template->set("serverUrlName",$this->serverUrlName);
		$template->set("guildUrlName",$this->guildUrlName);
		$template->set("baseurl",$this->baseurl);
		$template->set("settings",$this->settings);
		$template->set("guild", $this->guild);
		$content = $template->fetch();
		return $content;
	}

	function GetServer(){
		$server = new dkpServer();
		$server->loadFromDatabaseByName($this->GetServerName());
		return $server;
	}

	function GetGuild(){
		$guild = new dkpGuild();
		$guild->loadFromDatabaseByName($this->GetGuildName(),$this->GetServerName());
		return $guild;
	}

	function GetGuildName(){
		return util::getData("pguild");
	}

	function GetServerName(){
		return util::getData("pserver");
	}

	function HasPermission($name, $tableid = -1){
		return (dkpUserPermissions::currentUserHasPermission($name,$this->guild->id,$tableid));
	}

	function PermissionError(){
		$this->title = "Security!";
		return "You're not allowed to be here.";
	}

	/*=================================================
	Gets the curent page, sort column, and sort order for a paginated
	patent table with the given prefix. Each table uses a different
	set of variables set by the .htaccess file. The prefix is
	used to specify these. To find out what the correct
	prefix should be, check webroot\.htaccess's mod
	rewrite rules
	=================================================*/
	function LoadPageVars($prefix){
		$this->page = $this->getPage($prefix."_page");
		$this->sort = $this->getSort($prefix."_sort");
		$this->order = $this->getOrder($prefix."_order");
	}

	/*=================================================
	Gets the current page of data requested via query parameters
	=================================================*/
	function GetPage($name){
		$page = util::getData($name);
		if(empty($page))
			$page = 1;
		return $page;
	}
	/*=================================================
	Gets the current sort column requested via query parameters
	=================================================*/
	function GetSort($name){
		$sort = util::getData($name);
		if(empty($sort))
			$sort = "date";
		return $sort;
	}
	/*=================================================
	Gets the current sort order requested via query parameters
	=================================================*/
	function GetOrder($name){
		$order = util::getData($name);
		if(empty($order))
			$order = "desc";
		return $order;
	}

	function GetTableSelect(){
		if(sizeof($this->tables) == 1)
			return "";

		$data = "<select name=\"tableid\" onchange=\"document.location='".$this->baseurl.$this->pageurl."?t='+options[selectedIndex].value\">\r\n";
		foreach($this->tables as $table){
			$data .= "<option value=\"".$table->tableid."\" ";
			if($this->tableid == $table->tableid)
				$data.=" selected ";
			$data.= ">".$table->name."</option>\r\n";
		}
		$data.="</select><br />";
		return $data;
	}

	function fetch($file){
		$this->set("serverUrlName",$this->serverUrlName);
		$this->set("guildUrlName",$this->guildUrlName);
		$this->set("baseurl",$this->baseurl);
		$this->set("guild",$this->guild);
		$this->set("settings",$this->settings);
		$this->set("page", $this->page);
		$this->set("order", $this->order);
		$this->set("sort", $this->sort);
		$this->set("maxpage", $this->maxpage);
		$this->set("dkptables", $this->tables);
		$this->set("tableid", $this->tableid);
		$this->set("tableselect", $this->GetTableSelect());
		return parent::fetch($file);
	}


	function GetClassFilters(){
		$filters = array();

		$filters["Druid"] = 	array("Druid");
		$filters["Hunter"] = 	array("Hunter");
		$filters["Mage"] = 		array("Mage");
		$filters["Paladin"] = 	array("Paladin");
		$filters["Priest"] =	array("Priest");
		$filters["Rogue"] = 	array("Rogue");
		$filters["Shaman"] = 	array("Shaman");
		$filters["Warlock"] = 	array("Warlock");
		$filters["Warrior"] =	array("Warrior");
		$filters["Death Knight"] = array("Death Knight");
		$filters["Casters"] = 	array("Paladin","Shaman","Mage","Warlock","Priest","Druid");
		$filters["Melee"] = 	array("Paladin","Shaman","Warrior","Rogue","Druid","Hunter","Death Knight");
		$filters["Healer"] = 	array("Shaman","Paladin","Priest","Druid");
		$filters["Mail"]  = 	array("Shaman","Hunter");
		$filters["Cloth"] = 	array("Warlock","Mage","Priest");
		$filters["Leather"] = 	array("Rogue","Druid");
		$filters["Plate"] = 	array("Warrior","Paladin","Death Knight");

		return $filters;
	}

	/*=================================================
	Handles any filters that are currently set. This will
	look for filters in the current session and generate
	a series of clauses that should be added to any patent
	sql query that will limit the results based on the
	current set of filters.
	Results are stored in:
	$this->filterClause
	$this->statusClause
	$this->assignedClause
	$this->ratingClause
	=================================================*/
	function HandleDKPFilters($prefix = ""){
		if($prefix != "")
			$prefix .= "_";

		//handle the filter string
		$filter = sql::Escape(util::getData($prefix."filter"));
		if($filter != "")
			$this->filterClause = " dkp_users.name LIKE '%$filter%' ";
		else
			$this->filterClause = null;

		//handle the class option
		$class = sql::Escape(util::getData($prefix."filterclass"));
		$this->classClause = null;
		$classFilters = $this->GetClassFilters();
		foreach($classFilters as $key => $classFilter) {
			if($class == $key) {
				$temp = array();
				foreach($classFilter as $className)
					$temp[] = " dkp_users.class = '".sql::Escape($className)."' ";
				$this->classClause = "(".implode("OR",$temp).")";
			}
		}

		//min dkp
		$mindkp = util::getData($prefix."mindkp");
		$mindkp = sql::Escape($mindkp);
		$this->dkpClause = null;
		if(is_numeric($mindkp))
			$this->dkpClause = " dkp_points.points >= '$mindkp' ";
	}

	/*=================================================
	Returns a string of all the combined filter limits.
	This string can be appended to any sql query to limit the
	results. It optinonlly accepts an array which allows
	you to limit the type of filters you want to pay attention to.
	Default is all filters.

	Choices for array are: filter, status, assigned, rating
	=================================================*/
	function CombineDKPFilters($prefix, $filterlimits = array("filter","class","dkp")){

		$this->HandleDKPFilters($prefix);

		$filters = array();
		if(isset($this->filterClause) && in_array("filter", $filterlimits))
			$filters[] = $this->filterClause;
		if(isset($this->classClause) && in_array("class", $filterlimits))
			$filters[] = $this->classClause;
		if(isset($this->dkpClause) && in_array("dkp", $filterlimits))
			$filters[] = $this->dkpClause;

		$temp = implode(" AND ", $filters);
		return $temp;
	}

	/*=================================================
	Event Handler - sets the filters used. These are
	stored in the user session
	=================================================*/
	function SetDKPFilter($prefix = ""){
		if($prefix != "")
			$prefix.="_";
		$filter = util::getDataNoSession($prefix."filter");
		util::saveInSession($prefix."filter", $filter);
		util::getData($prefix."filterclass","nofilter",true);
		util::getData($prefix."mindkp","nofilter",true);
		util::saveInSession($prefix."filteron", true);
	}
	/*=================================================
	Clears the current set filter
	=================================================*/
	function ClearDKPFilter($prefix){
		if($prefix != "")
			$prefix.="_";
		util::clearFromSession($prefix."filter");
		util::clearFromSession($prefix."filterclass");
		util::clearFromSession($prefix."mindkp");
		util::clearFromSession($prefix."filteron");
	}

	function GetDKPFilterUI($prefix = ""){
		$template = new template("site/control/dkp/bin/templates/dkpfilter.tmpl.php");

		if($prefix != "")
			$prefix .= "_";

		$template->set("filteron", util::getData($prefix."filteron"));
		$template->set("filter", util::getData($prefix."filter",""));
		$template->set("filterclass", util::getData($prefix."filterclass","all"));
		$template->set("mindkp", util::getData($prefix."mindkp","0"));
		$template->set("prefix",$prefix);
		$template->set("page", $this->page);
		$template->set("sort", $this->sort);
		$template->set("order", $this->order);
		$template->set("baseurl",$this->baseurl.$this->pageurl);
		$template->set("classfilters", $this->GetClassFilters());

		$content = $template->fetch();
		return $content;
	}

	function eventDeleteAward(){
		if(!$this->HasPermission("TableEditHistory", $this->tableid)) {
			$this->setEventResult(false, "You do not have permission to delete this award.");
		}

		$awardid = util::getData("awardid");

		$this->updater->DeleteAward($awardid);
	}

	function eventDeleteHistory(){
		if(!$this->HasPermission("TableEditHistory", $this->tableid)) {
			$this->setEventResult(false, "You do not have permission to delete this award.");
		}
		$historyid = util::getData("historyid");
		$this->updater->DeleteHistory($historyid);
	}
}
?>