<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
The wowstats class provides a set of static methods for
retrieving statistics for items from World of Warcraft.
It provides function to create item tooltip links or to just
retrieve php class instances that contains information about a
specific item.

Item data is retrieved from wowhead and is then cached locally
in a mysql database. This improves perforamnce for future
lookups.

Created by Scott Bailey, 2008

Modified by Protevis, 2009
- added class item_data
- rewrited some code for wowhead xml

*/
include_once("itemcache.php");

class wowstats {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	//the url where item xml data can be downloaded from for items.
	//{ITEM_NAME} will be replaced with a url encoded name
	const itemUrl = "http://www.wowhead.com/item={ITEM_NAME}&xml";

	/*===========================================================

	============================================================*/
	static function GetItem($name, $itemid, $candownload = false){
		//first, attempt to download it from the database
		$item = new itemcache();
		if ($itemid != 0) {
			$item->loadFromDatabaseByItemID($itemid);
		} else if ($name != "") {
      $item->loadItemFromDatabaseByName($name);
		}
		
		if (empty($item->id) && $candownload) {
		  $item = wowstats::DownloadItem($name,$itemid);
		}
		
		return $item;
	}

	/*===========================================================
	Checks to see if the item exists
	============================================================*/
	function ItemExists($name){
		return itemcache::exists($name);
	}

	/*===========================================================
	Called by Loot.php
	============================================================*/
	static function GetTextLink($name, $itemid = 0, $candownload = false){
		$item = wowstats::GetItem($name, $itemid, $candownload);
		
		if($item->itemid == 0 ) {
			//construct the 'click to load tooltip' link
			return "<a href='javascript:;' class='q0 tooltip itemnotfound'>$name</a>";
		}

		$link = $item->link;
		$name = $item->name;
		$quality = $item->quality;

		return "<a href='$link' class='q$quality'>$name</a>";
	}

	function DownloadItem($name, $itemid){

		//get the url that we will download from
		$url = wowstats::itemUrl;
		if ($itemid != "0") {
			$url = str_replace("{ITEM_NAME}", urlencode($itemid), $url);
		}
		else {
			$url = str_replace("{ITEM_NAME}", urlencode($name), $url);
		}
	
		//get and parser the data
    $doc =self::fetchXml($url);

		//create a new item
		$item = new itemcache();

		//grab the needed data out of the xml document
		$item->name = $doc->item->name;
		$item->itemid = $doc->item['id'];
		$item->quality = $doc->item->quality['id'];
		$item->link = $doc->item->link;
		$item->icon = $doc->item->icon;

		if(empty($item->itemid)) {
			$item->name = $name;
			$item->itemid = 0;
			return $item;
		}

		//save the results into the database
		$item->saveNew();

		return $item;
	}
	
  private function fetchXml($url) {
    $ch = curl_init();
    $header[] = 'Accept-Language: en-gb';
    $browser = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)';
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header); 
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt ($ch, CURLOPT_USERAGENT, $browser);
    $url_string = curl_exec($ch);
    curl_close($ch);
    return simplexml_load_string($url_string);
  }
}

?>