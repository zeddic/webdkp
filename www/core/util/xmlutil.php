<?php
/*===========================================================
xmlutil()
A utility class that provides various static methods
related to xml.

These methods are mostly related to the php xml dom
functions: http://us2.php.net/dom
============================================================*/

class xmlutil {

	/*===========================================================
	Given a xml dom node and a path, this will return the value
	of the node at the given path.
	For example:
	<root>
	   <header>
	      <name>Zedd</name>
	   </header>
	</root>

	$name = xmlutil::getXmlElementValue($node, "root/header/name");

	Returns empty string on failure
	============================================================*/
	function getXmlElementValue($node, $path){
		$element = xmlutil::GetXmlElement($node, $path);
		if($element === false )
			return "";
		return $element->nodeValue;
	}

	/*===========================================================
	Given a xml dom node and a path, and the name of a desired attribute,
	this will return the value of the attribute
	For example:
	<root>
	   <header>
	      <name id="55">Zedd</name>
	   </header>
	</root>

	$id = xmlutil::getXmlElementValue($node, "root/header/name","id");

	Returns empty string on failure
	============================================================*/
	function getXmlElementAttribute($node, $path, $attribute){
		$element = xmlutil::GetXmlElement($node, $path);
		if($element === false )
			return "";
		if(!$element->hasAttribute($attribute))
			return "";
		return $element->getAttribute($attribute);
	}

	/*===========================================================
	Given a xml dom node and a path, this will return the node at
	the given path
	For example:
	<root>
	   <header>
	      <name>Zedd</name>
	   </header>
	</root>

	$node = xmlutil::getXmlElementValue($node, "root/header/name");

	Returns false if the node could not be found
	============================================================*/
	function getXmlElement($node, $path){
		$parts = explode("/",$path);
		$toFind = $parts[0];

		if(!$node->hasChildNodes())
			return false;

		$children = $node->childNodes;

		foreach($children as $child) {
			if($child->nodeName == $toFind) {
				if(sizeof($parts) == 1) {
					return $child;
				}
				else {
					$newPath = implode("/",array_slice($parts,1));
					return xmlutil::GetXmlElement($child, $newPath);
				}

			}
		}

		return false;
	}

	/*===========================================================
	Downloads and returns the xml data at the given url
	============================================================*/
	function fetchUrl($url){
		$ch = curl_init($url);
		$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT, $useragent);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}
}