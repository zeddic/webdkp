<?php


include_once("lib/dkp/dkpGuild.php");
include_once("lib/dkp/dkpUser.php");
include_once("lib/dkp/dkpUtil.php");
include_once("htmltophp.php");

class armory {


	// OLD const americanUrl = "http://www.wowarmory.com/";
	// OLD const euroUrl = "http://armory.wow-europe.com/";
	const americanUrl = "http://us.battle.net/wow/en/";
	const euroUrl = "http://eu.battle.net/wow/en/";

	const AMERICAN = 0;
	const EURO = 1;

	function GetPlayersInGuild($guild, $armorySite = armory::AMERICAN){

		if(!is_a($guild,"dkpGuild")) {

			$guildid = $guild;
			$guild = new dkpGuild();
			$guild->loadFromDatabase($guildid);
			if($guild->id == "")
				return array();
		}

		return armory::GetPlayersInGuildByName($guild->name, $guild->server, $armorySite);
	}

	function GetArmoryUrl($guild, $armorySite = armory::AMERICAN){
		if(!is_a($guild,"dkpGuild")) {

			$guildid = $guild;
			$guild = new dkpGuild();
			$guild->loadFromDatabase($guildid);
			if($guild->id == "")
				return "http://us.battle.net/wow/en/";
		}
		return armory::GetUrlByName($guild->name, $guild->server, $armorySite);
	}

	function GetArmoryUrlByName($guildname, $server, $armorySite = armory::AMERICAN){
		if($armorySite == armory::AMERICAN)
			$base = armory::americanUrl;
		else
			$base = armory::euroUrl;

		$server = stripslashes($server);
		$pos = strpos($server," EU");
		if($pos === false) {
			// string needle NOT found in haystack don't do anything
		}
		else {
 			// string needle found in haystack so replace and set to Euro URL
			$server = str_replace(" EU", "", $server);
			$base = armory::euroUrl;
		}
		$pos = strpos($server," (EU)");
		if($pos === false) {
			// string needle NOT found in haystack don't do anything
		}
		else {
 			// string needle found in haystack so replace and set to Euro URL
			$server = str_replace(" (EU)", "", $server);
			$base = armory::euroUrl;
		}
		
		$guildname = stripslashes($guildname);
		$url = $base . "guild/".$server."/".$guildname."/roster";
		$url = str_replace(' ', '%20', $url);


		return $url;
	}

	function GetPlayersInGuildByName($guildname, $server, $armorySite = armory::AMERICAN ) {

		$toReturn = array();
		// Setup a lookup array for the class id.
		$classarray = array(1 => 'Warrior', 
				    2 => 'Paladin', 
			            3 => 'Hunter',
			            4 => 'Rogue',
			            5 => 'Priest',
			            6 => 'Death Knight',
			            7 => 'Shaman',
			            8 => 'Mage',
			            9 => 'Warlock',
			            10 => 'Unknown',
			            11 => 'Druid');

		$url = armory::GetArmoryUrlByName($guildname, $server, $armorySite);
		$xml = armory::GetUrl($url);
		// Remarked the encode due to issues with proper names displaying
		//$xml = utf8_encode($xml);

		if($xml == "")
			return $toReturn;

		// Search the page to see if the armory located the guild, if it didn't don't continue
		$validguild = strpos( $xml, '<!-- guild : not-found -->');

		if ($validguild == False)
		{

			// Search the first page and determine how many pages of members there are total.
			$findme1   = '<strong class="results-total">';
			$findme2   = '</strong> results';
			$posstart = strpos($xml, $findme1);
			$posend = strpos($xml, $findme2);
			$posstart = $posstart + 30;
			$resultsize = $posend - $posstart;
			$totalmembers = substr($xml,$posstart,$resultsize);
			$totalmembers = floatval($totalmembers);
			$totalmembers = $totalmembers / 100;
			// This is the final result of the total number of pages to be loaded.
			$totalpagestoload = ceil($totalmembers);

			// Now that we know how many total pages there are we need to loop through
			// and load each page after appending the page number to the url
			
			//for ( $counter = 1; $counter <= $totalpagestoload; $counter = $counter + 1) {
			for ( $counter = 1; $counter <= 1; $counter = $counter + 1) {
				$url2 = $url;
				$stringadd1 = '?page=';
				$url2 = $url.$stringadd1.$counter;
				$xml = armory::GetUrl($url2);

				$returnedarray = armory::ProcessURL($xml);
			
				// combine the returned array into the variable toReturn
				// this combines all the users from every page into one variable to pass back for processing
				$toReturn = array_merge($returnedarray, $toReturn);
				
			}
			

		return $toReturn;	


		}

		
	}


	function GetUrl($url){
		//$url = "http://www.wowarmory.com/guild-info.xml?r=Stormscale&n=Totus+Solus";
		$ch = curl_init($url);

		$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT, $useragent);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

	function ProcessURL($xml){
	// This function processes the XML page.

		// Setup a lookup array for the class id.
		$classarray = array(1 => 'Warrior', 
				    2 => 'Paladin', 
			            3 => 'Hunter',
			            4 => 'Rogue',
			            5 => 'Priest',
			            6 => 'Death Knight',
			            7 => 'Shaman',
			            8 => 'Mage',
			            9 => 'Warlock',
			            10 => 'Unknown',
			            11 => 'Druid');

		// Search and modify the XML file so the Table Extractor can populate the Class data properly.
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Warrior">', "1", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Paladin">', "2", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Hunter">', "3", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Rogue">', "4", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Priest">', "5", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Death Knight">', "6", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Shaman">', "7", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Mage">', "8", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Warlock">', "9", $xml);
		$xml = str_replace('<span class="icon-frame frame-14 " data-tooltip="Druid">', "11", $xml);



		// Convert the HTML Roster Table into a PHP Array so we can pull the data out.
		$tbl = new tableExtractor; 
		$tbl->source = $xml; // Set the HTML Document 
		$tbl->anchor = 'row1'; // Set an anchor that is unique and occurs before the Table 
		$tbl->anchorWithin = true; // To use a unique anchor within the table to be retrieved 
		$convertedtable = $tbl->extractTable(); // The array
		//var_dump($convertedtable);
		// Keep this for testing, enable it to output an XML file to determine proper parsing of data.
		//$doc = new DOMDocument();
		//$doc->loadXML($xml);
		//$doc->save("site/lib/wow/file.xml");
		//$members = $doc->getElementsByTagName("character");

		$totalmembers = count($convertedtable);

		for ( $counter = 1; $counter <= $totalmembers; $counter = $counter + 1) {
			$name = $convertedtable[$counter]["Name"];

			$parse = explode('">', $name, 2);
       			$name = substr($parse[1], 0, stripos($parse[1], '</a>'));

			$class = $convertedtable[$counter]["Class"];
			$class = intval($class);
			

			$level = $convertedtable[$counter]["Level"];

			$class = $classarray[$class];

			$user = new dkpUser();
			$user->name = $name;
			$user->class = $class;
			$user->level = $level;
			$user->guild = $guildname;

			$toReturn[] = $user;

		}

	return $toReturn;
	}


}

?>