<?php
include_once("adminmain.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
Page allows users to set various options and settings
for their account
=================================================*/
class pageSettings extends pageAdminMain {

	/*=================================================
	Main Page Content
	=================================================*/
	function area2()
	{
		global $siteRoot;
		$this->title = "DKP Settings";
		$this->border = 1;

		//call the template
		return $this->fetch("settings/settings.tmpl.php");
	}

	/*=================================================
	EVENT - Sets lifetime option
	=================================================*/
	function eventSetLifetime(){
		$state = util::getData("state");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		$this->settings->SetLifetimeEnabled($state);
	}
	/*=================================================
	EVENT - Sets tier option
	=================================================*/
	function eventSetTiers(){
		$state = util::getData("state");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		$this->settings->SetTiersEnabled($state);
	}
	/*=================================================
	EVENT - Sets size used for tier
	=================================================*/
	function eventSetTierSize(){
		$size = util::getData("size");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		if(!is_numeric($size))
			return $this->setEventResult(false, "The tier size must be a number.");

		if($size == 0)
			return $this->setEventResult(false, "Your tier size can't be zero.");

		$this->settings->SetTierSize($size);

		$this->setEventResult(true,"Tier Size Updated");

	}
	/*=================================================
	EVENT - Sets zerosum option
	=================================================*/
	function eventSetZerosum(){
		$state = util::getData("state");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		$this->settings->SetZerosumEnabled($state);

	}
	/*=================================================
	EVENT - Sets alt & main dkp sharing
	=================================================*/
	function eventSetAltMain(){
		$state = util::getData("state");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		$this->settings->SetCombineAltsEnabled($state);

	}
	/*=================================================
	EVENT - Sets loot table option
	=================================================*/
	function eventSetLootTable(){
		$state = util::getData("state");

		if(!$this->HasPermission("ChangeSettings"))
			return $this->setEventResult(false, "You do not have permission to change this setting");

		$this->settings->SetLootTableEnabled($state);

	}
}
?>