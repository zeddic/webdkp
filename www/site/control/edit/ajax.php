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

}

?>