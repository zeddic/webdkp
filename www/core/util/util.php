<?php
/*===========================================================
util()
A utility class that provides various static methods
and helper functions.
============================================================*/
include_once("util/fileutil.php");
include_once("general/image.php");
include_once("ajax/json.php");

class util{
	/*===========================================================
	Attempts to retrieve data from a combination of get, post
	and a session (in that order).
	If passed an optional default value, that will be returned
	if nothing can be found. If passed an optional third parameter
	of true, whatever the data is that is retrieved will be stored
	in a session.
	============================================================*/
	static function getData($var, $defaultValue=false, $storeInSession = false){
		$toReturn = null;
		
		if (isset($_GET[$var])) {
			$toReturn =  $_GET[$var];
		} else if (isset($_POST[$var])) {
			$toReturn =  $_POST[$var];
		} else if(isset($_SESSION) && isset($_SESSION[$var])) {
			$toReturn =  $_SESSION[$var];
		} else {
			$toReturn = $defaultValue;
		}

		if($storeInSession){
			util::saveInSession($var,$toReturn);
		}

		return $toReturn;
	}

	/*===========================================================
	getDataNoSession()
	Attempts to retrieve data from a combination of get and post (in that order)
	If passed an optional default value, that will be returned
	if nothing can be found.
	============================================================*/
	static function getDataNoSession($var, $defaultValue=false){

		$toReturn = null;
		if($_GET[$var]!=""){
			$toReturn =  $_GET[$var];
		}
		else if($_POST[$var]!=""){
			$toReturn =  $_POST[$var];
		}
		else {
			$toReturn = $defaultValue;
		}

		return $toReturn;
	}

	/*===========================================================
	Saves a given key / value pair into the session
	============================================================*/
	static function saveInSession($var, $value){
		$_SESSION[$var]=$value;
	}

	/*===========================================================
	Retrieves a given value from the session
	============================================================*/
	static function getFromSession($var){
		return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
	}

	/*===========================================================
	Clears the given variable from the current session
	============================================================*/
	static function clearFromSession($var){
		unset ($_SESSION[$var] );
	}

	/*===========================================================
	Returns true if a given variable is saved in the session
	============================================================*/
	static function inSession($var){
		return (isset($_SESSION[$var]));
	}

	/*===========================================================
	Given a full file name, including directory path, this will
	strip out and return just the file name. Example:
	mysite/file.php  would only have file.php returned
	============================================================*/
	static function getFileName($fullname){
		//search for the last "/"
		$temp = explode("/",$fullname);
		return($temp[sizeof($temp)-1]);
	}

	/*===========================================================
	Implodes an associative array, keeping the key and the values
	together. $inglue = what to use to link the key and value together,
	$outglue = what seperates the the key/value pairs
	Example: name>Scott,age>Unknown
	============================================================*/
	static function implodeWithKey($assoc, $inglue = '>', $outglue = ',')
	{
		$return = '';
		foreach ($assoc as $tk => $tv)
		{
			$return .= $outglue . $tk . $inglue . $tv;
		}
		return substr($return,strlen($outglue));
	}

	/*===========================================================
	Undoes the operation performed by implodeWithKey, taking
	a string and splitting it back into an associative array with
	key / value pairs.
	Example input: name>Scott,age>Unknown
	Returned array: ["name"] = "Scott"
					["age"] = "Unknown"
	============================================================*/
	static function explodeWithKey($str, $inglue = ">", $outglue = ',')
	{
		$hash = array();
		foreach (explode($outglue, $str) as $pair) {
			 $k2v = explode($inglue, $pair);
			 if (isset($k2v[0]) && isset($k2v[1])) {
				$hash[$k2v[0]] = $k2v[1];
			 }
		}
		return $hash;
	}

	/*===========================================================

	============================================================*/
	static function addTooltip($message){
		$message = "<div class='tooltip'>".$message."</div>";
		$message = str_replace(array("\n", "\r"), '', $message);
		$message = addslashes($message);
		$message = 'onmouseover="return overlib(' . "'" . $message . "'" . ',FULLHTML);" onmouseout="return nd();"';
		return $message;
	}

	/*===========================================================
	Helper function - used to time function use.
	Returns current time stamp. Call at the start of a set of
	code that you wish to time. At the end of the code, call
	timerEnd, passing the time returned by this function.
	============================================================*/
	static function timerStart()
	{
		$starttime = time()+microtime();
		return $starttime;
	}
	/*===========================================================
	Helper function - used to time function use.
	Returns the time between now and the passed start time.
	Optional parameter $round specifies how many digits
	to round the result to.
	============================================================*/
	static function timerEnd($starttime, $round = 4)
	{
		$stoptime = time()+microtime();
		$totaltime = round($stoptime-$starttime,$round);
		return $totaltime;
	}

	/*===========================================================
	Helper function
	Given an array and value, will return an array with
	all occurances of value in the original array removed.
	Does NOT modify the given array, but instead returns
	the a modified array with the given value removed.
	============================================================*/
	static function removeFromArray($value, &$array){
		$array_remval = $array;
		for($x=0;$x<count($array_remval);$x++) {
			$i=array_search($value,$array_remval);
			if (is_numeric($i)) {
		  		$array_temp  = array_slice($array_remval, 0, $i );
				$array_temp2 = array_slice($array_remval, $i+1, count($array_remval)-1 );
				$array_remval = array_merge($array_temp, $array_temp2);
			}
		}
		return $array_remval;
	}

	/*===========================================================
	wordTrim
	Returns the given string trimmed to the passed word count.
	$string - the string to trim
	$count - the number of words to trim to
	$ellipsis - if set to true, or any form of a string, the given string will
	* 			be appended to the end of the string if (and only if) it were trimmed.
	* 			For example, setting this to "..." would append those characters to the end
	* 			of the trimmed string
	$corretHtml - if set to true the html will be 'corrected' after the trim. That is, any
	* 			  tags that are now unclosed as a result of the trim will be closed at
	* 			  the end of the new substring.
	returns the trimmed string.
	============================================================*/
	static function trimString($string, $count, $ellipsis = false, $correctHtml = false)
	{
		$words = explode(' ',$string);
		if(count($words) > $count) {
			array_splice($words, $count);
		    $string = implode(' ', $words);
		    if (is_string($ellipsis)){
		      	$string .= $ellipsis;
		    }
		    elseif ($ellipsis){
		      	$string .= '&hellip;';
		    }
		}

		if($correctHtml)
			$string = util::correctHtml($string);

		return $string;
	}

	/*===========================================================
	Corrects a given html string by making sure that all strings
	have been property closed.
	Accepts:
	$text - the text to check
	$problem - [reference, optional] set to true if there was a problem with the html that was corrected
	$unclosedTags - [reference, optional] set to an array of strings when there was a problem. Each entry is the name
				    of the tag that gave problems
	$unopendTags - [reference, optional] set to an array of strings of tags that the html closes but never opened
	============================================================*/
	static function correctHtml($text , &$problem = false, &$unclosedTags = array(), &$unopendTags = array()) {

	  $problem = false;
	  $unclosedTags = array();
	  $unopendTags = array();

	  // Tags which cannot be nested but are typically left unclosed.
	  $nonesting = array('li', 'p');

	  // Single use tags in HTML4
	  $singleuse = array('base', 'meta', 'link', 'hr', 'br', 'param', 'img', 'area', 'input', 'col', 'frame');

	  // Properly entify angles
	  $text = preg_replace('!<([^a-zA-Z/])!', '&lt;\1', $text);

	  // Splits tags from text
	  $split = preg_split('/<([^>]+?)>/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	  // Note: PHP ensures the array consists of alternating delimiters and literals
	  // and begins and ends with a literal (inserting $null as required).

	  $tag = false; // Odd/even counter. Tag or no tag.
	  $stack = array();
	  $output = '';
	  foreach ($split as $value) {
	    // HTML tag
	    if ($tag) {
	      list($tagname) = explode(' ', strtolower($value), 2);
	      // Closing tag
	      if ($tagname{0} == '/') {
	        $tagname = substr($tagname, 1);
	        if (!in_array($tagname, $singleuse)) {
	          // See if we have other tags lingering first, and close them
	          while (($stack[0] != $tagname) && count($stack)) {
	            $output .= '</'. array_shift($stack) .'>';
	          }
	          // If the tag was not found, just leave it out;
	          if (count($stack)) {
	            $output .= '</'. array_shift($stack) .'>';
	          }
	          else {
	          	if(!in_array($tagname, $nonesting)) {
	           	   $problem = true;
			  	   $unopendTags[] = $tagname;
			  	}
			  }
	        }
	      }
	      // Opening tag
	      else {
	        // See if we have an identical tag already open and close it if desired.
	        if (count($stack) && ($stack[0] == $tagname) && in_array($stack[0], $nonesting)) {
	          $output .= '</'. array_shift($stack) .'>';
	        }
	        // Push non-single-use tags onto the stack
	        if (!in_array($tagname, $singleuse)) {
	          array_unshift($stack, $tagname);
	        }
	        // Add trailing slash to single-use tags as per X(HT)ML.
	        else {
	          $value = rtrim($value, ' /') . ' /';
	        }
	        $output .= '<'. $value .'>';
	      }
	    }
	    else {
	      // Passthrough
	      $output .= $value;
	    }
	    $tag = !$tag;
	  }

      //check to see if there were any problems

  	  foreach($stack as $unclosedTag) {
	  	if(!in_array($unclosedTag, $nonesting)) {
       	   $problem = true;
	  	   $unclosedTags[] = $stack;
	  	}
	  }


	  // Close remaining tags
	  while (count($stack) > 0) {
	    $output .= '</'. array_shift($stack) .'>';
	  }
	  return $output;
	}

	/*===========================================================
	A utility method that will encode the given object
	into a json string.
	$obj - The object to encode into json. This may be an array,
		   object, or basic data type

	Note - there is a json object that can be used that is found
	in ajax/json.
	Also note that this function will cache a json object internally
	that will be reused.
	============================================================*/
	static function json($obj, $utf8encode = false)
	{
		$json = util::getJson();
		return $json->encode($obj, $utf8encode);
	}

	/*===========================================================
	A utility method that will encode the given object
	into a json string.
	$obj - The object to encode into json. This may be an array,
		   object, or basic data type

	Note - there is a json object that can be used that is found
	in ajax/json.
	Also note that this function will cache a json object internally
	that will be reused.
	============================================================*/
	static function jsonEncode($obj, $utf8encode = false)
	{
		$json = util::getJson();
		return $json->encode($obj, $utf8encode);
	}

	/*===========================================================
	A utility method that will decode the given json string back
	to set of objects
	$string - the json string to decode.
	============================================================*/
	static function jsonDecode($jsonString) {
		$json = util::getJson();
		return $json->decode($jsonString);
	}

	/*===========================================================
	Returns a json object that can be used to encode or decode objects.
	Note that this function will cache internally, such that if this
	method is called a second time it will return the same
	object that was returned the first time.
	============================================================*/
	static function getJson() {
		$json = util::ifset($GLOBALS["FrameworkJson"], null);
		if($json == null ) {
			$json = new json();
			$GLOBALS["FrameworkJson"] = $json;
		}
		return $json;
	}

	/**
	 * Returns a value if set and a default value if not.
	 * Convenience wrapper to avoid php noticies when inspecting optional
	 * properties w/o needing to use ternary operators everywhere.
	 */
	static function ifset(&$obj, $default = null) {
		return isset($obj) ? $obj : $default;
	}

	/*===========================================================
	Forwards the user to a given url using http headers. This method
	will not return. It forwards immediatly then kills the script
	NOTE: I don't believe this function is being called properly.
	============================================================*/
	static function forward($url) {
		if (!headers_sent()) {
			header("Location: http://www.webdkp.com/$url");
		}
	}
	/*===========================================================
	Makes sure the given string is in utf8 form. If not, it is
	converted
	============================================================*/
	static function ensureUTF8($string)
	{
		/*echo($string." - ".utf8_decode($string)." - ".utf8_encode($string)."<br />");*/
		$type = mb_detect_encoding($string);
		if($type != "UTF-8" && $type != "UTF-32") {
			return utf8_encode($string);
		}
		return $string;
	}
}