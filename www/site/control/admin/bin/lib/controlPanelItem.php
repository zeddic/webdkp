<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Defines the information for a single 'item' on the control panel.
The item could be either:
- A general category
- A link that appears within that category to a specific module.
*/

class controlPanelItem {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/
	var $id;
	var $name;
	var $type;
	var $link;
	var $image;
	var $parent;
	var $tablename;

	const TYPE_CATEGORY = 1;
	const TYPE_SUBCATEGORY = 2;
	const TYPE_ITEM = 3;
	const tablename = "site_controlpanel_items";
	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct()
	{
		$this->tablename = controlPanelItem::tablename;
	}
	/*===========================================================
	loadFromDatabase($id)
	Loads the information for this class from the backend database
	using the passed string.
	============================================================*/
	function loadFromDatabase($id)
	{
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
		$this->id=$row["id"] ?? null;
		$this->name = $row["name"] ?? null;
		$this->type = $row["type"] ?? null;
		$this->link = $row["link"] ?? null;
		$this->image = $row["image"] ?? null;
		$this->parent = $row["parent"] ?? null;

		if ($this->type == controlPanelItem::TYPE_CATEGORY && $this->image == "") {
			$this->image = "default.gif";
		}
	}
	/*===========================================================
	save()
	Saves data into the backend database using the supplied id
	============================================================*/
	function save()
	{
		global $sql;
		$name = sql::Escape($this->name);
		$link = sql::Escape($this->link);
		$image = sql::Escape($this->image);
		$sql->Query("UPDATE $this->tablename SET
					name = '$name',
					type = '$this->type',
					link = '$link',
					image = '$image',
					parent = '$this->parent'
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
		global $sql;
		$name = sql::Escape($this->name);
		$link = sql::Escape($this->link);
		$image = sql::Escape($this->image);
		$sql->Query("INSERT INTO $this->tablename SET
					name = '$name',
					type = '$this->type',
					link = '$link',
					image = '$image',
					parent = '$this->parent'
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
	static function exists($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$tablename = controlPanelItem::tablename;
		$exists = $sql->QueryItem("SELECT id FROM $tablename WHERE name='$name'");
		return ($exists != "");
	}
	/*===========================================================
	getItemByName()
	STATIC METHOD
	Returns an controlPanelItem instance of an item that has the
	same given name. Returns NULL if no items in the database match.
	============================================================*/
	static function getItemByName($name)
	{
		global $sql;
		$name = sql::Escape($name);
		$tablename = controlPanelItem::tablename;
		$row = $sql->QueryRow("SELECT * FROM $tablename WHERE name='$name'");
		if($sql->a_rows == 0) {
			return NULL;
		}
		$item = new controlPanelItem();
		$item->loadFromRow($row);
		return $item;
	}
	/*===========================================================
	setupTable()
	Checks to see if the classes database table exists. If it does not
	the table is created.
	============================================================*/
	static function setupTable()
	{
		if(!sql::TableExists(controlPanelItem::tablename)) {
			$tablename = controlPanelItem::tablename;
			global $sql;
			$sql->Query("CREATE TABLE `$tablename` (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR (256) NOT NULL,
						`type` INT NOT NULL,
						`link` VARCHAR (256) NOT NULL,
						`image` VARCHAR (256) NOT NULL,
						`parent` INT NOT NULL,
						PRIMARY KEY ( `id` )
						) TYPE = innodb;");
		}
	}


}
controlPanelItem::setupTable()
?>
