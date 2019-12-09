<?php
/*===========================================================
Controller
Shows a membershp form for the site
============================================================*/
include_once("bin/song.php");
class pageTestIndex extends page {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $layout = "Columns1";

	function area2(){

		$this->border = 1;
		$this->title = "Songs";


		//$song = new song();
		//$song->loadByName("Loisaida (Rumba)");

		return $this->fetch("song.tmpl.php");
	}

	function eventAddSong(){
		$artist = util::getData("artist");
		$album = util::getData("album");
		$title = util::getData("title");

		if(!song::exists($title)) {
			$song = new song();
			$song->artist = $artist;
			$song->album = $album;
			$song->title = $title;
			$song->count = 1;
			$song->saveNew();
			$song->timestamp();
		}
		else {
			$song = new song();
			$song->loadByName($title);
			$song->count = $song->count+1;
			$song->save();
			$song->timestamp();
		}
		die();
	}
}
?>