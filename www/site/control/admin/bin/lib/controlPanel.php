<?php

include_once("controlPanelItem.php");
/*===========================================================
Wraps functionality for the control panel for the site. This
provides methods to load the list of links on that should
be displayed on the control panel as well as add new links
or categories to the control panel.

The control panel is composed of categories, subcategories,
and items (links). Individual links can be placed under either
the category of the subcategory level. For example:

Site Settings			(Category)
   Security				(Subcategory)
      Users				(Link)
      User Groups		(Link)
      Permissions		(Link)
   Themes				(Link)

Subcategories can only be of depth 1. Ie, you could not
have a subcategory within a subcategory.
============================================================*/
class controlPanel  {

	//Associative array that will contain all the categories of the
	//the control panel. Each categoriy will then be made up of
	//a list of links or subcategories that make it up. These can
	//accessed via the member variable $items. The key of the associative
	//array is the id of the category.
	var $categories = array();

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	function __construct(){

	}

	/*===========================================================
	Loads the contents of the control panel categories, subcategories
	and links into the $catetories internal member variable.
	============================================================*/
	function load(){
		global $sql;
		//load the categories
		$type = controlPanelItem::TYPE_CATEGORY;
		$table = controlPanelItem::tablename;
		$result = $sql->Query("SELECT * FROM $table WHERE type='$type' ORDER BY name ASC");
		while($row = mysqli_fetch_array($result)){
			$category = new controlPanelItem();
			$category->loadFromRow($row);
			$category->items = array();
			$this->categories[$category->id] = $category;
		}

		//load subcategories
		$type = controlPanelItem::TYPE_SUBCATEGORY;
		$result = $sql->Query("SELECT * FROM $table WHERE type='$type' ORDER BY name ASC");
		while($row = mysqli_fetch_array($result)){
			$subcategory = new controlPanelItem();
			$subcategory->loadFromRow($row);
			$subcategory->items = array();
			if (isset($this->categories[$subcategory->parent])) {
				$this->categories[$subcategory->parent]->items[$subcategory->id] = $subcategory;
			}
		}
		//load the items that will either belong to a
		//category or a subcategory
		$type = controlPanelItem::TYPE_ITEM;
		$result = $sql->Query("SELECT * FROM $table WHERE type='$type' ORDER BY name ASC");
		while($row = mysqli_fetch_array($result)){
			$item = new controlPanelItem();
			$item->loadFromRow($row);
			$this->addItemToParent($item);
		}
	}
	/*===========================================================
	Interal method to help load step. Adds the given item (control panel
	link) to the the given parents list of links. The parent id is the
	id of either a category or a subcategory that the link should appear
	under.
	============================================================*/
	function addItemToParent(& $item) {
		$parentid = $item->parent;
		if (isset($this->categories[$parentid])) {
			$this->categories[$parentid]->items[$item->id]=$item;
			return true;
		}
		else {
			foreach($this->categories as $category){
				//print_r($category->items);
				if(isset($category->items[$parentid])) {
					$category->items[$parentid]->items[$item->id] = $item;
					return true;
				}
			}
		}
		return false;
	}

	/*===========================================================
	Adds a new link to the control panel. The link is given the given
	name, and the given url. It is placed under the category / subcategory
	named by parentName.
	For example:
	addLink("Site Settings", "Themes", "setupThemes.php");
	============================================================*/
	function addLink($parentName, $linkName, $link){
		$item = new controlPanelItem();
		$item->type = controlPanelItem::TYPE_ITEM;
		$item->name = $linkName;
		$item->link = $link;
		return controlPanel::addItem($parentName, $item);
	}
	/*===========================================================
	Adds a new category to the control panel. The given image
	will appear next to the category
	============================================================*/
	function addCategory($categoryName, $imageUrl=""){
		$item = new controlPanelItem();
		$item->type = controlPanelItem::TYPE_CATEGORY;
		$item->name = $categoryName;

		if(controlPanelItem::exists($categoryName)) {
			return false;
		}

		//TODO: SPECAL TASKS FOR COPYING IMAGE URL?
		if (file_exists($imageUrl)) {
			$filenameStart = strrpos($imageUrl,"/");
			$filename = substr($imageUrl,$filenameStart+1,strlen($imageUrl)-$filenameStart);
			copy($imageUrl,"modules/system/controlPanel/images/".$filename);
			$item->image = $filename;
		}



		$item->saveNew();

		return true;
	}
	/*===========================================================
	Adds a new subcategory to the control panel
	============================================================*/
	function addSubcategory(){
		$item = new controlPanelItem();
		$item->type = controlPanelItem::TYPE_SUBCATEGORY;
		$item->name = $subcategoryName;
		return controlPanel::addItem($parentName, $item);
	}
	/*===========================================================
	Adds a new 'item' to the control panel under the given parent.
	The item is of type controlPanelItem and can be of type category,
	subcategory, or link. If it is a category type, parentName can
	be left blank.

	Returns true if the item has been added to the control panel
	correctly.
	============================================================*/
	function addItem($parentName, & $item){
		//get the parent (needed for subcategories or items)
		$parent = controlPanelItem::getItemByName($parentName);
		if($parent == NULL && $item->type != controlPanelItem::TYPE_CATEGORY) {
			return false;
		}

		//make sure the parent is valid for the type of item being added
		if($item->type == controlPanelItem::TYPE_SUBCATEGORY &&
		   $parent->type != controlPanelItem::TYPE_CATEGORY) {
			return false;
		}

		if($item->type == controlPanelItem::TYPE_ITEM &&
		 	$parent->type == controlPanelItem::TYPE_ITEM) {
			return false;
		}

		//add the item
		$item->parent = $parent->id;
		$item->saveNew();

		return true;
	}

}
?>