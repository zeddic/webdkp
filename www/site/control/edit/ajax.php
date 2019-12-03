<?php
/*===========================================================
EDIT PAGE AJAX HANDLER
Handles AJAX requests sent by the page editor
============================================================*/
//TODO: Add Security Check using global security
if(!security::hasAccess("EditPage")){
	//die();
	echo("");
}

new editpageHandler();
class editpageHandler extends actionHandler {
	/*===========================================================
	Updates the color of the border around the part instance
	============================================================*/
	function actionUpdateBorder(){
		if(!security::hasAccess("Edit Page"))
			return;
		$border = util::getData("border");
		$iid = util::getData("iid");
		$part = new part();
		$part->loadFromDatabase($iid);
		$part->border = $border;
		$part->save();
		echo("upading border for part $iid to $border ");
	}

	/*===========================================================
	Updates the title of a part. Called while in edit page mode.
	============================================================*/
	function actionUpdateTitle(){
		if(!security::hasAccess("Edit Page"))
			return;
		$title = util::getData("title");
		$iid = util::getData("iid");
		$part = new part();
		$part->loadFromDatabase($iid);
		$part->title = $title;
		$part->save();
	}

	/*===========================================================
	Updates the title of a part. Called while in edit page mode.
	============================================================*/
	function actionUpdateAreaOrder(){
		if(!security::hasAccess("Edit Page"))
			return;

		$area = sql::Escape(util::getData("area"));
		$page = sql::Escape(util::getData("page"));
		$data = util::getData($area."_container");

		if($data!="")
			$data = implode(",",$data);
		$data = sql::Escape($data);
		if($data=="")
			$data = "0,-1";
		//remove \' . This is caused by a bug with scriptaculous when an item has 0 for an index
		$data = str_replace("\\'","",$data);

		global $sql;
		echo("updating $page 's $area with data $data <br />");
		$tablename = virtualPage::tablename;
		$sql->Query("UPDATE $tablename SET $area='$data' WHERE id='$page'");
	}
	/*===========================================================
	Deletes a part from a page
	============================================================*/
	function actionDeletePart(){
		if(!security::hasAccess("Edit Page"))
			return;

		$pageid = sql::Escape(util::getData("pageid"));
		$partid = sql::Escape(util::getData("partid"));

		$page = new virtualPage();
		$page->loadFromDatabase($pageid,false);

		//echo("searching $pageid for $partid to delete <br />");
		$update = $update || $this->DeletePartFromArea($page,"area1",$partid);
		$update = $update || $this->DeletePartFromArea($page,"area2",$partid);
		$update = $update || $this->DeletePartFromArea($page,"area3",$partid);
		$update = $update || $this->DeletePartFromArea($page,"area4",$partid);
		$update = $update || $this->DeletePartFromArea($page,"area5",$partid);

		if($update) {
			$page->save();
		}

		//now delete the pat from the database
		$part = partLibrary::getPartInstance($partid);
		/*$part = new part();
		$part->loadFromDatabase($partid);*/
		$part->delete();

	}
	/*===========================================================
	Helper method for deletePart(). Deletes  / removes a part
	from a given array if it exists. Returns true if the part was
	found to be deleted, returns false otherwise.
	Parameters:
	$page - class page reference of the page that contains the area to remove the part from
	$area - name of the area to check "area1", "area2", etc
	$partid - the part id of the part to remove from the area
	============================================================*/
	function DeletePartFromArea($page, $area, $partid){
		$key = array_search($partid,$page->$area);
		if( $key !== FALSE) {
			$temp = &$page->$area;
			unset($temp[$key]);
			return true;
		}
		return false;
	}

	/*===========================================================
	Updates a custom  option that was set for a given part.
	============================================================*/
	function actionUpdateCustomOption(){
		if(!security::hasAccess("Edit Page"))
			return;

		//get data
		$partid = util::getData("partid");
		$optionid = util::getData("optionid");
		$value = util::getData("value");

		//determine the option that they are setting
		$partOption = new partOption();
		$partOption->loadFromDatabase($optionid);
		if($partOption->type == partOption::TYPE_CHECKBOX && $value=="null"){
			$value = "0";
		}

		//update the part to contain the new value
		$part = new part();
		$part->loadFromDatabase($partid);
		$part->setOption($partOption->name,$value);
		$part->save();

	}

	/*===========================================================
	Adds a new part to a given page
	============================================================*/
	function actionAddPart(){
		if(!security::hasAccess("Edit Page"))
			return;

		//get data
		$pageid = util::getData("pageid");
		$partid = util::getData("part");

		//create a new instance of the requested part
		$partDefinition = new partDefinition();
		$partDefinition->loadFromDatabase($partid);

		if($partDefinition->id == "")
			return;

		$newPart = $partDefinition->createInstance();

		//add it to the page
		$page = new virtualPage();
		$page->loadFromDatabase($pageid);
		$page->area2= array_merge(array($newPart),$page->area2);
		$page->save();

		//return the new parts id
		echo($newPart->id);
	}

}

?>