<?php

/*===========================================================

CLASS DESCRIPTION

=============================================================



*/

include_once("core/part/partDefinition.php");

include_once("core/security/security.php");

class part {

	/*===========================================================

	MEMBER VARIABLES

	============================================================*/

	var $id;				//the unique id of this part

	var $definition;		//a part definition that tells what type of part this is

	var $name;				//the name of this particular part instance

	var $title;				//The title of this part when it has a border

	var $border;			//The id of the border to use. 0 is ussually reserved for 'no border'. Other numbers depend on the theme used

	var $fromTemplate = 0;	//Set at run time. 1 (true) if this part is being rendered on the current page as a result of being inherited from a template. 0 (false) otherwise.

	var $options = array();	//custom options to be attached with the part

	const tablename = "site_parts";





	var $template;						//holds a reference to the template

	var $templateDir;					//template directory. Will automattically be set to the modules template folder for you

	var $templateFile;					//the template file to call. You need to set this just before a view renders

	var $defaultView = "Default";		//The view handler to be called by default when one wasn't explicitly requested



	var $useBorder = true;				//Whether borders should be used for this module. True allows the user to decide what they want. False forces borders to be disabled ALWAYS.

	var $extraHeaders = array();		//An array of extra html values that should be placed in the final pages <head> tag. Module sections may add

										//to this if they need to include extra js or css files.

	var $editPageMode = false;			//Set at run time (During render). True if the page is currently being edited.

	var $eventResult;					//Boolean. Set by a event callback to signal whether it worked correctly or not. True = success.

	var $eventMessage;					//String. Set by a event callback to tell what the event failed or succedded.

	/*===========================================================

	MEMBER VARIABLES DEFINED BY EXTENDING CLASSES

	============================================================*/

	var $renderAlone = false;			//When set to true, the part contents will be rendered alone and in the center of the screen.

	var $useAjaxEdit = false;			//Set in extending parts. Went set to true the edit link for the part while in edit page mode will

										//load the parts editer via an ajax call and display it on the same page.

	var $editView; 						//The view that will be used for editing this part while in edit page mode.

										//Can be left blank if the part doesn't have an editor

	var $defaultTitle;					//If set, allows a part to define its default title. If not set the default title will be the part name

	var $defaultBorder;					//If set, allows a part to define its default border. This expects an int. If not set, it assums border 0.

	/*===========================================================

	DEFAULT CONSTRUCTOR

	============================================================*/

	function part()

	{

		$this->tablename = part::tablename;

		$this->template = & new template();

	}

	/*===========================================================

	loadFromDatabase($id)

	Loads the information for this class from the backend database

	using the passed string.

	============================================================*/

	function loadFromDatabase($id)

	{

		$this->tablename = part::tablename;

		global $sql;

		$row = $sql->QueryRow("SELECT * FROM $this->tablename WHERE id='$id'");

		$this->loadFromRow($row);

	}

	/*===========================================================

	loadFromRow($row)

	Loads the information for this class from the passed database row.

	============================================================*/

	function loadFromRow($row)

	{

		$this->id=$row["id"];

		$this->instanceName = $row["instanceName"];

		$this->title = $row["title"];

		$this->border = $row["border"];

		if($this->border=="")

			$this->border=1;

		if($defaultView != "")

			$defaultView = $row["defaultView"];

		//only load a new default view / section if it has been overriden

		//by this instance. If not, just go with what the part defined

		if($defaultView != "")

			$this->defaultView = $defaultView;



		$definitionId = $row["definition"];

		$this->definition = & new partDefinition();

		$this->definition->loadFromDatabase($definitionId);

		$this->options = util::explodeWithKey($row["options"],">&>","$|$");





		$this->directory = $this->definition->directory;

		$this->templateDir = $this->directory."bin/templates/";

		$this->iid = $this->module->id;

		$this->binDirectory = $this->definition->getBinDirectory();



	}

	/*===========================================================

	save()

	Saves data into the backend database using the supplied id

	============================================================*/

	function save()

	{

		$this->tablename = part::tablename;

		global $sql;

		$instanceName = sql::Escape($this->instanceName);

		$defaultView = sql::Escape($this->defaultView);

		$title = sql::Escape($this->title);

		if(is_a($this->definition,"partDefinition"))

			$definition = $this->definition->id;

		else

			$definition = $this->definition;

		$options = sql::Escape(util::implodeWithKey($this->options,">&>","$|$"));

		$sql->Query("UPDATE $this->tablename SET

					definition = '$definition',

					instanceName = '$instanceName',

					defaultView = '$defaultView',

					border = '$this->border',

					title = '$title',

					options = '$options'

					WHERE id='$this->id'");

	}

	/*===========================================================

	saveNew()

	Saves data into the backend database as a new row entry. After

	calling this method $id will be filled with a new value

	matching the new row for the data

	============================================================*/

	function saveNew()

	{

		$this->tablename = part::tablename;

		global $sql;

		$instanceName = sql::Escape($this->instanceName);

		$defaultView = sql::Escape($this->defaultView);

		$title = sql::Escape($this->title);

		if(is_a($this->definition,"partDefinition"))

			$definition = $this->definition->id;

		else

			$definition = $this->definition;

		$options = sql::Escape(util::implodeWithKey($this->options,">&>","$|$"));

		$sql->Query("INSERT INTO $this->tablename SET

					definition = '$definition',

					instanceName = '$instanceName',

					defaultView = '$defaultView',

					border = '$this->border',

					title = '$title',

					options = '$options'

					");

		$this->id=$sql->GetLastId();

	}

	/*===========================================================

	delete()

	Deletes the row with the current id of this instance from the

	database

	============================================================*/

	function delete()

	{

		global $sql;

		$sql->Query("DELETE FROM $this->tablename WHERE id = '$this->id'");

	}

	/*===========================================================

	exists()

	STATIC METHOD

	Returns true if the given entry exists in the database

	database

	============================================================*/

	function exists($name)

	{

		global $sql;

		$name = sql::escape($name);

		$tablename = part::tablename;

		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE id='$name'"); //MODIFY THIS LINE

		return ($exists != "");

	}





	/*===========================================================

	getEditView()

	Returns the name of the view that the edit link on the editing

	page display should point to when clicked

	============================================================*/

	function getEditView(){

		return $this->editView;

	}



	/*===========================================================

	getTitle()

	Returns an appropriate title for the current part

	============================================================*/

	function getTitle(){

		$title = $this->title;

		if($this->module->title!="" && $this->module->title!=$this->module->name || $title=="")

			$title = $this->module->title;

		return $title;

	}



	/*===========================================================

	fetchEditPageBorder()

	Returns true if the current view is an 'edit view' - one of the

	views specified in $this->renderAlone.

	============================================================*/

	function inEditView(){

		return ($this->getEditView()!="" && $this->getIData("v")==$this->getEditView());

	}



	/*===========================================================

	handleEvents()

	Handles any events for this section. This is done by looking

	for any event values in post / get. This value will cause a matching

	method of eventEVENTNAME within this class to be executed.

	============================================================*/

	function handleEvents(){

		$event = $this->getIData("event");

		if($event=="") {

			$event = $this->getData("gevent");

		}

		if($event == "")

			return;



		$eventHandler = "event" . $event;



		//check to see if proper event handler is defined

		if(!method_exists($this, $eventHandler))

			return;	//no event handler for this event



		$this->$eventHandler();

	}



	/*===========================================================

	handleAjax()

	Handles any ajax requests for this part.

	============================================================*/

	function handleAjax(){

		$ajax = $this->getIData("a");



		if($ajax == "") {

			$ajax = $this->getIData("ajax");

			if ($ajax == "") {

				return;

			}

		}



		$ajaxHandler = "ajax" . $ajax;



		//check to see if proper event handler is defined

		if(!method_exists($this, $ajaxHandler))

			return;	//no event handler for this event



		$result = $this->$ajaxHandler();

		echo($result);

		die();

	}



	/*===========================================================

	render()

	Renders this section. The section is rendered by looking for

	its currently set view, which would be found in get / post

	in the variable name v (with the modules instance id appended).

	A method is then invoked of viewVIEWNAME. If no view variable

	can be found in get / post, a default one is used which is defined

	by the classes $this->defaultView member variable.



	$editpageMode is used to signal if the page is currently being edited,

	in which case the module needs to be wrapped in a border.

	============================================================*/

	function render($editPageMode = false){

		$this->editPageMode = $editPageMode;



		//get the requested view (get / post / session - in that priority)

		$view = $this->getIData("v");



		//if set to any value, the requested view will not be saved in

		//in a session

		$noSession = $this->getIData("ns");



		//fall back to the default view if the given view isn't valid

		if($view == "" || !method_exists($this,"view".$view))

			$view = $this->defaultView;





		if ($this->inEditView()) {

			//util::clearFromSession("v".$this->id);

			//if(!$editPageMode) {

			//	$view = $this->defaultView;

			//	util::saveInSession("v".$this->id,$view);

			//}

			$noSession = 1;

			util::clearFromSession("v".$this->id);

		}



		//save the view in a session so it will not

		//always need to be passed via parameters (allows multiple parts

		//to function on the same page without interfering with each other)

		if($noSession == "") {

			util::saveInSession("v".$this->id,$view);

		}





		//render the view

		$viewHandler = "view".$view;

		$content = $this->$viewHandler();



		//wrap the content in borders as needed

		if($this->useBorder && (!$this->inEditView() || $this->renderAlone)){

			$this->fetchBorder($content);

		}



		//if we are in the edit page mode we need to

		//call the module template. This will provide an interactive way

		//for the user to edit and modify the module color, border, title, position

		if($this->editPageMode && !$this->inEditView()) {

			$this->fetchEditPageBorder($content);

		}





		//check to see if this is a special ajax callback that

		//only wants the content of this particular module

		//This is signaled by putting get$moduleid in the query string

		//and setting it to any value

		if($this->getData("getPart") != "" &&

		   ($this->getData("getPart")==$this->id || $this->getData("getPart")==$this->name) ){

			echo($content);

			die();

		}



		return $content;

	}



	/*===========================================================

	set()

	Sets a variable in the template file. This variable can then be

	accesses like a regular php variable in the template.

	============================================================*/

	function set($name, $value){

		$this->template->set($name,$value);

	}



	/*===========================================================

	setVars()

	Sends an array of variables to the template file at once. The

	arrays must be in an associative array, with the variable name

	as a key and the value as the array entry or that key.

	An easy way to do this is using the compact() method in php.

	Optioanl second variable will clear all currently set variables

	in the template

	============================================================*/

	function setVars($vars, $clear = false){

		$this->template->setVars($vars,$clear);

	}



	/*===========================================================

	fetch()

	Fetches / renders the content for the current template. This

	will reunder the rendered content as a string.

	============================================================*/

	function fetch($templateFile = ""){

		global $siteUser;



		if($templateFile=="")

			$templateFile = $this->templateFile;



		$this->template->setDirectory($this->templateDir);

		$this->template->setFile($templateFile);

		$this->template->set("directory",$this->definition->getBinDirectory());

		$this->template->set("id",$this->id);

		$this->template->set("iid",$this->id);

		$this->template->set("siteUser",$siteUser);

		$this->template->set("eventResult",$this->eventResult);

		$this->template->set("eventMessage",$this->eventMessage);

		$this->template->set("eventResultString",($this->eventResult?"1":"0"));

		$this->template->set("defaultView",$this->defaultView);

		$this->template->set("title",$this->getTitle());

		$this->template->depth = 2;

		$content =  $this->template->fetch();





		return $content;

	}



	/*===========================================================

	fetchBorder()

	Wraps the given content in a border

	============================================================*/

	function fetchBorder(&$content){





		global $theme;

		$title = $this->getTitle();



		$template = & new template();

		$template->set("content",$content);

		$template->set("title",$title);

		$template->set("iid",$this->id);

		$template->set("border",$this->border);

		$template->directory.=$theme->getDirectory()."borders/";

		$template->setFile("border".$this->border.".tmpl.php");

		$template->depth = 1;

		if(!$template->exists()) {

			$template->setFile("border1.tmpl.php");

		}



		$content = $template->fetch();

	}



	/*===========================================================

	fetchEditPageBorder()

	Wraps the given module content in a 'edit page' border.

	This border, unlike the regular border, provides methods for

	moving the modules as well as changing title / border color / etc.

	============================================================*/

	function fetchEditPageBorder(&$content){

		$template = & new template();

		$title = $this->getTitle();



		//we only display an 'edit' link on the border if the module specifies

		//a edit section / view AND there is not already another module

		//that is being rendered alone (ie, being edited on)

		$displayEditLink = ($this->getEditView()!="");

		//determine if we are already in edit view (so we can add links to the border to leave it)

		$isEditView = $this->inEditView();

		$template->set("id",$this->id);

		$template->set("iid",$this->id);

		$template->set("content",$content);

		$template->set("displayEditLink",$displayEditLink);

		$template->set("editView",$this->getEditView());

		$template->set("useAjaxEdit",$this->useAjaxEdit);

		$template->set("isEditView",$isEditView);

		$template->set("defaultView",$this->defaultView);

		$template->set("options",$this->fetchEditPageOptions());  //any custom options that they want to appear in the options drop down

		$template->set("title",$title);

		$template->set("border",$this->border);

		$template->set("fromTemplate",$this->fromTemplate);

		$template->set("icon",$this->definition->getIcon());

		$template->directory = theme::getCommonDirectory();

		$content = $template->fetch("editpage/module.tmpl.php");

	}



	/*===========================================================

	fetchEditPageOptions()

	Returns an html string of options for a given module. These

	are the options that will be shown for the module when a

	user clicks on the options link. It is composed for 3

	different components:

	- Standard options (title + border )

	- Custom options defined by module (drop down, text file, etc.)

	- Custom options defined by module in a options.tmpl.php template

	============================================================*/

	function fetchEditPageOptions(){

		global $theme;

		if(sizeof($this->definition->options)==0){

			$this->definition->loadOptions();

		}



		$availableOptions = $this->definition->options;

		$currentOptions = $this->options;



		$template = & new template();

		$template->set("customOptions",$this->fetchEditPageCustomOptions());

		$template->set("id",$this->id);

		$template->set("iid",$this->id);

		$template->set("title",$this->getTitle());

		$template->set("numberOfBorders",$theme->numberOfBorderTypes());

		$template->set("border",$this->border);

		$template->set("currentOptions",$currentOptions);

		$template->set("availableOptions",$availableOptions);

		$template->directory = theme::getCommonDirectory();

		return $template->fetch("editpage/options.tmpl.php");

	}



	/*===========================================================

	getCustomOptions()

	Returns an html string of custom options for the current

	module. This is determined by checking for a custom options.tmpl.php

	located in the given modules template directory

	============================================================*/

	function fetchEditPageCustomOptions(){

		$template = & new template();

		$options = "";

		$template->directory = $this->definition->directory."templates/";

		if($template->exists("options.tmpl.php")){

			$options = $template->fetch("options.tmpl.php");

		}

		return $options;

	}



	/*===========================================================

	getExtraHeaderIncludes()

	Returns an array of html strings that specify extra header values

	that should be in the final html page.

	For example, this module may require that an extra css page

	or javascript file be placed in the pages header.

	============================================================*/

	function getExtraHeaders()

	{

		return $this->extraHeaders;

	}



	/*===========================================================

	addHeader()

	Adds a given string of html to the head of the page.

	Use this to import another javascript or css file that might

	be needed for the current module

	============================================================*/

	function addHeader($htmlString){

		$this->extraHeaders[]=$htmlString;

	}

	/*===========================================================

	addJavascriptHeader()

	Adds a reference to the given javascript file to the <head>

	tag of the current page.

	============================================================*/

	function addJavascriptHeader($path){

		$this->addHeader("<script src=\"$path\" type=\"text/javascript\"></script>");

	}

	/*===========================================================

	addCSSToHeader()

	Adds a reference to the given css file to the <head>

	tag of the current page.

	============================================================*/

	function addCSSHeader($path){

		$this->addHeader("<link rel=\"stylesheet\" type=\"text/css\" href=\"$path\" />");

	}

	/*===========================================================

	Sets the result of a event callback. These values will be available

	in any template that is invoked on the same page rendering as the

	event.

	$ok =  Whether the event task succedded

	$message = Any message to go along with the result

	============================================================*/

	function setEventResult($ok = true, $message = ""){

		$this->eventResult = $ok;

		$this->eventMessage = $message;

	}



	/*===========================================================

	getData()

	Attempts to retrieve data from a combination of get, post

	and a session (in that order). Please see definition

	in module class for complete description

	============================================================*/

	function getData($var, $default=false, $storeInSession=false){

		return util::getData($var, $default, $storeInSession);

	}

	/*===========================================================

	getIData()

	Same as getData, except it always appends the modules current

	instance id to the passed parameter, garanteeing that it will

	be unique within a page.

	============================================================*/

	function getIData($var, $default=false, $storeInSession=false){

		//echo("looking for : ".$var.$this->id);

		return util::getData($var.$this->id, $default, $storeInSession);

	}



	/*===========================================================

	getDataNoSession()

	Attempts to retrieve data from a combination of get, post

	in that order). It does NOT check the SESSION Please see definition

	in module class for complete description

	============================================================*/

	function getDataNoSession($var, $default=false, $storeInSession=false){

		return util::getDataNoSesssion($var, $default, $storeInSession);

	}

	/*===========================================================

	getIDataNoSession()

	Same as getIDataNoSession, except it always appends the modules current

	instance id to the passed parameter, garanteeing that it will

	be unique within a page.

	============================================================*/

	function getIDataNoSession($var, $default=false){

		return util::getDataNoSession($var.$this->id, $default, $storeInSession);

	}



	/*===========================================================

	getOption()

	Returns the value of a custom option for the part.

	Accepts a key which represents the name of the option. Returns

	the value of the option.

	Note, these are custom options and can be used or implemented

	by extending parts.

	============================================================*/

	function getOption($key){



		if (!$this->optionSet($key)) {

			if (is_a($this->definition->options[$key],"partOption")) {

				$defaultValue = $this->definition->options[$key]->default;

			}

			$this->setOption($key,$defaultValue);

		}

		else {

			$value = $this->options[$key];

			return $this->options[$key];

		}

	}

	/*===========================================================

	optionSet()

	Returns true if a given custom option is set.

	============================================================*/

	function optionSet($key){

		return isset($this->options[$key]);

	}

	/*===========================================================

	setOption()

	Sets a custom value for the part. This option is specific

	to a given part instance. Save or savenew must still be called

	afterwards.

	============================================================*/

	function setOption($key, $value){

		$this->options[$key]=$value;

		//echo("setting option $key with value $value <br />");

	}



	/*===========================================================

	loadDefaultOptions()

	Loads all the default options for this part.

	============================================================*/

	function loadDefaultOptions(){

		if ($this->definition && is_array($this->definition->options)) {

			//load options if they havn't yet (they are only loaded on demand, saves

			//loading them when not needed to save database queries)

			if (sizeof($this->definition->options)==0) {

				$this->definition->loadOptions();

			}

			foreach($this->definition->options as $availableOption){

				$this->setOption($availableOption->name, $availableOption->default);

			}

		}

	}



	/*===========================================================

	saveOptions()

	Saves any changes made by setOption

	============================================================*/

	function saveOptions(){

		$this->tablename = part::tablename;

		global $sql;

		$options = sql::Escape(util::implodeWithKey($this->options,">&>","$|$"));

		$sql->Query("UPDATE $this->tablename SET

					 options = '$options'

					 WHERE id='$this->id'");

	}



	/*===========================================================

	viewDefault()

	Default view in case the user forgets to implement their own.

	============================================================*/

	function viewDefault(){

		return "This is the default view. No content is available.";

	}





	/*===========================================================

	setupTable()

	Checks to see if the classes database table exists. If it does not

	the table is created.

	============================================================*/

	function setupTable()

	{

		if(!sql::TableExists(part::tablename)) {

			$tablename = part::tablename;

			global $sql;

			$sql->Query("CREATE TABLE `$tablename` (

						`id` INT NOT NULL AUTO_INCREMENT ,

						`definition` INT NOT NULL,

						`instanceName` VARCHAR (256) NOT NULL,

						`defaultView` VARCHAR (256) NOT NULL,

						`title` VARCHAR (256) NOT NULL,

						`border` INT DEFAULT '1' NOT NULL,

						`options` VARCHAR (1024) NOT NULL,

						PRIMARY KEY ( `id` )

						) TYPE = innodb;");

		}

	}

}

part::setupTable()

?>