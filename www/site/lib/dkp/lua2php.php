<?
/***********************************************************************************
 *
 *   lua2php_array - Converts an WoW-Lua File into a php-Array.
 *
 *   Author: PattyPur (Patty.Pur@web.de)
 *   Char : Shindara
 *   Guild: Ehrengarde von Theramore
 *   Realm: Kel'Thuzad (DE-PVP)
 *
 *   Date: 02.10.2005
 *
 **********************************************************************************
 */

// Helper-functions

/*
  function trimval(string)

  cuts the leading and tailing quotationmarks and the tailing comma from the value
  Example:
    Input: "Value",
    Output: Value
*/
function trimval($str)
{
  $str = trim($str);
  if (substr($str,0,1)=="\""){

    $str  = trim(substr($str,1,strlen($str)));
  }
  if (substr($str,-1,1)==","){
    $str  = trim(substr($str,0,strlen($str)-1));
  }

  if (substr($str,-1,1)=="\""){
    $str  = trim(substr($str,0,strlen($str)-1));
  }

  if ($str =='false')
  {
    $str = false;
  }
  if ($str =='true')
  {
    $str = true;
  }

  return $str;
}

/*
  function array_id(string)

  extracts the Key-Value for array indexing
  String-Example:
    Input: ["Key"]
    Output: Key
  Int-Example:
    Input: [0]
    Output: 0
*/
function array_id($str)
{
  $id1 = sscanf($str, "[%d]");
  if (!empty($id1) && strlen($id1[0] ?? "")>0){
    return $id1[0];
  }
  else
  {
    if (substr($str,0,1)=="[")
    {
      $str  = substr($str,1,strlen($str));
    }
    if (substr($str,0,1)=="\"")
    {
      $str  = substr($str,1,strlen($str));
    }
    if (substr($str,-1,1)=="]")
    {
      $str  = substr($str,0,strlen($str)-1);
    }
    if (substr($str,-1,1)=="\"")
    {
      $str  = substr($str,0,strlen($str)-1);
    }
    return $str;
  }
}

/*
  function luaparser(array, arrayStartIndex)

  recursive Function - it does the main work
*/
function luaparser($lua, &$pos)
{
  $parray = array();
  $stop = false;
  if ($pos < count($lua))
  {
    for ($i = $pos;$stop ==false;)
    {
      if ($i >= count($lua)) { $stop=true;}

		$line = $lua[$i] ?? "";
	  //$line = utf8_decode($lua[$i]);
	  //the preg match will find any '=' that is inside a literal and change it into a -.
	  //The = character causes problems with the rest of the parser..
	  //-Zedd
	  $line = preg_replace('/"([^]]*)[=]([^]]*)"/',"\"$1-$2\"",$line);

	  /*$matches = array();
	  preg_match_all('/"([^]]*)[=]([^]]*)"/',$lua[$i],$matches);
	  if (count($matches[0]) > 0 ) {
	  	//print_r($matches);
	  	echo("found a match in ".$lua[$i]."<br />");

	  	echo("changed to $temp <br />");

	  }*/
      $strs = explode("=",($line));

      //start of new block
      if (trim($strs[1] ?? "") == "{"){
        $i++;
        $parray[array_id(trim($strs[0]))]=luaparser($lua, $i);
      }
      //end of previous block
      else if (trim($strs[0] ?? "") == "}" || trim($strs[0] ?? "") == "},")
      {
        //$i--;
        $i++;
        $stop = true;
      }
      //most likely an etry into an array
      else
      {
		$i++;

		/*$count = 0;

		//check to see if the parser incorrectly picked up a literal with a '=' character in it
		if(sizeof($strs) > 2 ) {

			//while we still haven't picked up a correct assignment: ["something"] = "something"
			//keep moving the counter to the left, to find the next = break. Keep going until
			//we find a correct break, or reach the end
			do {
				if(sizeof($strs) <= $count + 1) {
					$count++;
					break;
				}
				$trimmedBefore = trim($strs[$count]);
				$trimmedAfter = trim($strs[$count+1]);
				$trimmedBeforeLastChar = $trimmedBefore[strlen($trimmedBefore)-1];
				$trimmedAfterFirstChar = $trimmedAfter[0];
				$count++;

			}while($trimmedBeforeLastChar != "]" && $trimmedAfterFirstChar != "\"");
			$count--;
		}

		for($j = 0 ; $j <= )*/

		if (strlen(array_id(trim($strs[0])))>0 && strlen($strs[1] ?? "")>0)
		{
			$parray[array_id(trim($strs[0]))]=trimval($strs[1]);
		}
      }
    }
  }
  $pos=$i;
  return $parray;
}

/*
  function makePhpArray($input)

  thst the thing to call :-)

  $input can be
    - an array with the lines of the LuaFile
    - a String with the whole LuaFile
    - a Filename

*/
function makePhpArray($input){
  $start = 0;
  if (is_array($input))
  {
    return luaparser($input,$start);
  }
  elseif (is_string($input))
  {
    if (@is_file ( $input ))
    {
      return luaparser(file($input),$start);
    }
    else
    {
      return luaparser(explode("\n",$input),$start);
    }
  }
}
?>