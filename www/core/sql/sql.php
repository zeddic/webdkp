<?php
/*===========================================================
Class sql.
The sql class provides mysql connectivity. This method allows
a persistent connection to be made and repeated queries to be
made over the connection.

It is suggested to make this class a global variable for ease
of use.
============================================================*/
class sql
{
    var $databaseName;		//The name of the database that is currently connected to
	var $id;				//The unique resource id for this connection
	var $result;			//The result from the last query
	var $rows;				//The numer of rows as a result of the last query
	var $data;				//The last row of data obtained for a queryRow statement
	var $a_rows;			//The number of affected rows from the last query
    var $user;				//The username to connect with
	var $pass;				//The password to connect with
	var $host;				//The host to connect to
	var $hideErrors = false;	//If true, mysql errors are not echoed.
	var $queryCount = 0;	//Keeps track of how many queries have been process
	/*===========================================================
	Setup
	Sets up a new connection to a given mysql database and
	connects to the specified table.
	============================================================*/
    function Setup($username, $password, $databaseName, $createDatabase = false)
	{
        $this->user = $username;
        $this->pass = $password;
        $this->connect($databaseName, $createDatabase);
    }
	/*===========================================================
	Connect
	Connects to the mysql database and selects the specified database
	name to work with
	============================================================*/
    function Connect($databaseName, $createDatabase = false)
	{
        $this->databaseName = $databaseName;
        $this->id = mysql_connect($this->host, $this->user, $this->pass) or
            $this->ShowError("Unable to connect to MySQL server: $this->host : '$SERVER_NAME'");
        $this->selectDatabase($databaseName, $createDatabase);
        //$this->Query("SET Names 'utf8'");
    }
	/*===========================================================
	SelectDatabase
	Selects the current database that queries and commands should
	be sent to.
	============================================================*/
    function SelectDatabase($databaseName, $createDatabase = false)
	{

		if (!$createDatabase) {
			@mysql_select_db($databaseName, $this->id) or
            	$this->ShowError("Unable to select database: $databaseName");
		}
		else {

			if(!@mysql_select_db($databaseName, $this->id)){

				$dbName = sql::Escape($databaseName);

				$this->Query("CREATE DATABASE `$dbName`;");
				@mysql_select_db($databaseName, $this->id) or
            	$this->ShowError("Unable to select database: $databaseName");
			}
		}
    }
	/*===========================================================
	Query
	Sends a mysql query to the database. Returns a result resource.
	You can use a while loop to iterate through results.
	(while($row = mysql_fetch_array($result))
	============================================================*/
    function Query($query)
	{
		$this->queryCount++;
        $this->result = @mysql_query($query, $this->id) or
            $this->ShowError("Unable to perform query: $query");
        $this->rows = @mysql_num_rows($this->result);
        $this->a_rows = @mysql_affected_rows($this->id);
		return($this->result);
    }

 	/*===========================================================
	QueryItem
	Returns a single element of data from the query. This will
	return the first element of the first row from the query.
	============================================================*/
    function QueryItem($query)
	{
		$this->queryCount++;
        $this->result = @mysql_query($query, $this->id) or
            $this->ShowError("Unable to perform query: $query");
        $this->rows = @mysql_num_rows($this->result);
        $this->a_rows = @mysql_affected_rows($this->id);
        $this->data = @mysql_fetch_array($this->result);
        return($this->data[0]);
    }

	/*===========================================================
	QueryRow
	Returns the first row of data from a query only.
	============================================================*/
    function QueryRow($query)
	{
		$this->queryCount++;
        $this->result = @mysql_query($query, $this->id) or
            $this->ShowError("Unable to perform query: $query");
        $this->rows = @mysql_num_rows($this->result);
        $this->a_rows = @mysql_affected_rows($this->id);
        $this->data = @mysql_fetch_array($this->result);
        return($this->data);
    }

	/*===========================================================
	GetArray
	Given a mysql result object (from a Query), it will return an associative
	array from the result. The associative array is setup as an array,
	with each entry in the array being another array which contains information
	for that specific row.
	Example:
	{["id"]=1,["name"]="Bob"},
	{["id"]=2,["name"]="Frank"},
	{["id"]=3,["name"]="Sue"}
	============================================================*/
	function GetArray($result_object)
	{
		$ret_data = array();
		while( $row = mysql_fetch_array( $result_object ) )
		{
			$ret_data[] = $row;
		}
		return $ret_data;
	}

	/*===========================================================
	GetArrayKey
	Given a mysql result object (from a Query),and a key which is a column
	name in the result, it will return an associative array of the result
	with the given column as a key in that array. Example:
	Example: ("name" passed as the key)
	["Bob"] => {["id"]=1,["name"]="Bob"},
	["Frank"] => {["id"]=2,["name"]="Frank"},
	["Sue"] => {["id"]=3,["name"]="Sue"}
	============================================================*/
	function GetArrayKey($result_object, $key)
	{
		$ret_data = array();
		while( $row = mysql_fetch_array( $result_object ) )
		{
			$ret_data[$row[$key]] = $row;
		}
		return $ret_data;
	}

	/*===========================================================
	ShowError
	Displays a mysql error. Haults all future execution.
	============================================================*/
	function ShowError($msg) {
		if($this->hideErrors)
			return;
	    // Close out a bunch of HTML constructs which might prevent
	    // the HTML page from displaying the error text.
	    echo("</ul></dl></ol>\n");
	    echo("</table></script>\n");

	    // Display the error message
	    $text  = "<font color=\"#ff0000\" size=-1><p>Error: $msg :";
	    $text .= mysql_error();
	    $text .= "</font>\n";
	    die($text);
	}

	/*===========================================================
	Escape : STATIC METHOD
	Returns an escapped version the passed text so that it is
	safe to insert into the database. Can be called statically.
	============================================================*/
	function Escape($value){
		if ( get_magic_quotes_gpc() ) {
			$value = stripslashes($value);
		}
		if ( !is_numeric($value) ) {
			$value = mysql_real_escape_string($value);
		}
		return $value;
	}

	/*===========================================================
	GetLastId : STATIC METHOD
	Returns the id generated for the previous insert as a result
	of the mysql autoincrement tag.
	============================================================*/
	function GetLastId(){
		return mysql_insert_id();
	}

	/*===========================================================
	TableExists : STATIC METHOD
	Returns true if a given table exists in the database
	============================================================*/
	function TableExists($tableName){
		global $sql;
		$tableName = sql::escape($tableName);
		$exists = $sql->QueryItem("SHOW TABLES LIKE '$tableName'");
		//don't count these towards the query count (near 0 footprint)
		$sql->queryCount--;
		return ($exists != "");
	}

	/*===========================================================
	DatabaseExists : STATIC METHOD
	Returns true if a given table exists in the database
	============================================================*/
	function DatabaseExists($databaseName){
		//global $sql;
		//$tableName = sql::escape($tableName);
		//$exists = $sql->QueryItem("SHOW TABLES LIKE '$tableName'");
		//return ($exists != "");
		return true;
	}

	/*===========================================================
	Helper Function
	LoadObjects(&$results, $className);

	Loads an sql array result into an array of objects. This will
	take the result set, then iterate through each row, instanitating
	a class of type $className. It will then call $className->loadFromRow
	on the classname passing the row. The given class represented
	by classname must exists. The class definition must
	also define the method loadFromRow. Will return an array
	of all objects.
	============================================================*/
	function LoadObjects(&$result, $className){

		$toReturn = array();

		//make sure the class exists
		if(!class_exists($className))
			return $toReturn;

		//iterate through the result set, creating
		//a new class instance for each	then passing
		//its loadFromRow method with the given row
		$methodExists = false;
		while($row = mysql_fetch_array($result)) {
			//create the object
			$object = new $className;

			//make sure its load from row call exists
			if($methodExists || method_exists($object, "loadFromRow")) {
				$object->loadFromRow($row);
				$toReturn[] = $object;
				//set a helper variable so we can skip calling
				//method_exists next time (will save us time)
				$methodExists = true;
			}
			else {
				return $result;
			}
		}

		//return the reuslt set
		return $toReturn;
	}
}
?>