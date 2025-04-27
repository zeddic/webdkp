<?php
include_once("site/control/dkp/dkpmain.php");
include_once("lib/dkp/dkpUpdater.php");
/*=================================================
The news page displays news to the user.
=================================================*/
class pageAdminMain extends pageDkpMain {

	var $layout = "Columns1";

	var $canBackup;
	var $canBackupRestore;
	var $canRepairRecalc;
	var $canManageDKPTables;
	var $canEditGuild;
	var $canEditOfficers;
	var $canChangeSettings;
	var $canAccessAllTables;
	var $canAddPlayer;
	var $canDeletePlayer;
	var $canAddPoints;
	var $canEditHistory;
	var $canEditPlayers;
	var $canUploadLog;
	var $canRepair;
	var $canManageLootTable;

	function __construct()
	{
		parent::__construct();
		global $siteUser;
		if($siteUser->guild != $this->guild->id && $siteUser->usergroup->name != "Admin") {
			util::forward($this->baseurl);
			die();
		}
		$this->LoadPermissions();

		$GLOBALS["ShowAds"] = false;

	}

	/*=================================================
	Shows a list of posts to the user. The user has
	links to skip to any page of the posts
	=================================================*/
	function area1()
	{
		global $sql;

		//$this->title = "Control Center";
		$this->title = "Admin";
		$this->border = 1;

		//$this->set("tabs",$this->GetTabs("admin"));
		//return $this->fetch("sidebar.tmpl.php");
		return "";
	}

	//todo - remove
	function LoadPermissions(){
		$this->canBackup = dkpUserPermissions::currentUserHasPermission("BackupCreate",$this->guild->id);
		$this->canBackupRestore = dkpUserPermissions::currentUserHasPermission("BackupRestore",$this->guild->id);
		$this->canRepairRecalc = dkpUserPermissions::currentUserHasPermission("RecalculateDKP",$this->guild->id);
		$this->canManageDKPTables =  dkpUserPermissions::currentUserHasPermission("DKPTables",$this->guild->id);
		$this->canEditGuild = dkpUserPermissions::currentUserHasPermission("AccountEditGuild",$this->guild->id);
		$this->canEditOfficers = dkpUserPermissions::currentUserHasPermission("AccountSecondaryUsers",$this->guild->id);
		$this->canChangeSettings = dkpUserPermissions::currentUserHasPermission("ChangeSettings",$this->guild->id);
		$this->canAccessAllTables = dkpUserPermissions::currentUserHasPermission("AllTableAccess",$this->guild->id);
		$this->canAddPlayer = dkpUserPermissions::currentUserHasPermission("TableAddPlayer",$this->guild->id);
		$this->canDeletePlayer = dkpUserPermissions::currentUserHasPermission("TableDeletePlayer",$this->guild->id);
		$this->canAddPoints = dkpUserPermissions::currentUserHasPermission("TableAddPoints",$this->guild->id);
		$this->canEditHistory = dkpUserPermissions::currentUserHasPermission("TableEditHistory",$this->guild->id);
		$this->canEditPlayers = dkpUserPermissions::currentUserHasPermission("TableEditPlayers",$this->guild->id);
		$this->canUploadLog = dkpUserPermissions::currentUserHasPermissionAnyTable("TableUploadLog",$this->guild->id);
		$this->canRepair = dkpUserPermissions::currentUserHasPermission("Repair",$this->guild->id);
		$this->canManageLootTable = dkpUserPermissions::currentUserHasPermission("LootTable",$this->guild->id);
	//	dkpUserPermissions::currentUserHasPermission("",$this->guild->id)
	}

	function fetch($file){

		$self = fileutil::getFile($_SERVER["PHP_SELF"]);

		$template = new template("site/control/dkp/admin/bin/templates/sidebar.tmpl.php");
		$template->set("baseurl",$this->baseurl);
		$template->set("directory",$this->binDirectory);
		$template->set("self",$self);
		$template->set("settings",$this->settings);
		$template->set("guild",$this->guild);
		$template->set("canBackup",$this->canBackup);
		$template->set("canBackupRestore",$this->canBackupRestore);
		$template->set("canRepairRecalc",$this->canRepairRecalc);
		$template->set("canManageDKPTables",$this->canManageDKPTables);
		$template->set("canEditGuild",$this->canEditGuild);
		$template->set("canEditOfficers",$this->canEditOfficers);
		$template->set("canChangeSettings",$this->canChangeSettings);
		$template->set("canAccessAllTables",$this->canAccessAllTables);
		$template->set("canAddPlayer",$this->canAddPlayer);
		$template->set("canDeletePlayer",$this->canDeletePlayer);
		$template->set("canAddPoints",$this->canAddPoints);
		$template->set("canEditHistory",$this->canEditHistory);
		$template->set("canEditPlayers",$this->canEditPlayers);
		$template->set("canUploadLog",$this->canUploadLog);
		$template->set("canRepair",$this->canRepair);
		$template->set("canManageLootTable",$this->canManageLootTable);

		$sidebar = $template->fetch();

		$this->set("tabs",$this->GetTabs("admin"));
		$this->set("sidebar",$sidebar);
		$this->set("canBackup",$this->canBackup);
		$this->set("canBackupRestore",$this->canBackupRestore);
		$this->set("canRepairRecalc",$this->canRepairRecalc);
		$this->set("canManageDKPTables",$this->canManageDKPTables);
		$this->set("canEditGuild",$this->canEditGuild);
		$this->set("canEditOfficers",$this->canEditOfficers);
		$this->set("canChangeSettings",$this->canChangeSettings);
		$this->set("canAccessAllTables",$this->canAccessAllTables);
		$this->set("canAddPlayer",$this->canAddPlayer);
		$this->set("canDeletePlayer",$this->canDeletePlayer);
		$this->set("canAddPoints",$this->canAddPoints);
		$this->set("canEditHistory",$this->canEditHistory);
		$this->set("canEditPlayers",$this->canEditPlayers);
		$this->set("canUploadLog",$this->canUploadLog);
		$this->set("canRepair",$this->canRepair);
		$this->set("canManageLootTable",$this->canManageLootTable);

		return parent::fetch($file);
	}
}
?>