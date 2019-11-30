<?=$tabs?>

<div class="adminArea">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/tasks.gif"> Tasks</div>
<div class="adminLinks">
	<a href="<?=$baseurl?>Admin/Manage">Edit DKP Tables</a> <br />
	<?php if($canUploadLog){ ?>
	<a href="<?=$baseurl?>Admin/Upload">Upload Log File</a> <br />
	<?php } ?>
	<a href="<?=$baseurl?>Admin/Download">Download Log File</a> <br />
	<?php if($canAddPlayer) { ?>
	<a href="<?=$baseurl?>Admin/Armory">Sync with Armory</a>
	<?php } ?>
</div>
</div>



<div class="adminArea">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/account.gif"> Account Settings</div>
<div class="adminLinks">
	<a href="<?=$baseurl?>Admin/UpdateAccount">Update User Account</a> <br />
	<?php if($canEditGuild){ ?>
	<a href="<?=$baseurl?>Admin/UpdateGuild">Update Guild</a> <br />
	<?php } ?>
	<?php if($canEditOfficers){ ?>
	<a href="<?=$baseurl?>Admin/OfficerAccounts">Officer Accounts</a> <br />
	<?php } ?>

</div>
</div>


<?php if($canChangeSettings || $canManageDKPTables || $canManageLootTable) { ?>
<div class="adminArea">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/settings.gif"> Settings</div>
<div class="adminLinks">
	<?php if($canChangeSettings) { ?>
	<a href="<?=$baseurl?>Admin/Settings">Settings</a> <br />
	<?php } ?>
	<?php if($canManageDKPTables) { ?>
	<a href="<?=$baseurl?>Admin/DKPTables">Create & Delete Tables</a> <br />
	<?php } ?>
	<?php if($canManageLootTable) { ?>
	<a href="<?=$baseurl?>Admin/LootTable">Loot Table</a> <br />
	<?php } ?>
	<br />
</div>
</div>
<?php } ?>

<?php if($canBackup || $canRepair){ ?>
<div class="adminArea">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/maintain.gif"> Maintenance</div>
<div class="adminLinks">
	<?php if($canRepair){ ?>
	<a href="<?=$baseurl?>Admin/Repair">Repairs</a> <br />
	<?php } ?>
	<?php if($canBackup){ ?>
	<a href="<?=$baseurl?>Admin/Backup">Backups</a> <br />
	<?php } ?>
</div>
</div>
<?php } ?>

<div class="adminArea" style="padding-right:90px">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/world.gif"> Remote WebDKP</div>
<div class="adminLinks">
	<a href="<?=$baseurl?>Admin/Remote">WebDKP on your Website</a> <br />
	<a href="<?=$baseurl?>Admin/RemoteStyle">Change Table Style</a> <br />
</div>
</div>

<div class="adminArea" style="padding-right:0px">
<div class="adminTitle"><img src="<?=$siteRoot?>images/dkp/maintain.gif"> Disable Ads</div>
<div class="adminLinks" style="width:220px">
	<?php if( !$settings->GetProaccount() || $settings->GetProstatus() == "Active Until End of Term") { ?>
	<a href="<?=$baseurl?>Admin/Ads">Subscribe Now</a> <br />
	You can disable the ads for only <b>$1.50</b> per month.
	<?php } else { ?>
	<a href="<?=$baseurl?>Admin/Ads">Cancel Subscription</a>
	<?php if(!$settings->GetNewProaccount()) { ?>
	<br />
	<div class="message" style="background:#BFFFBA;padding-left:4px;">
	Fees have been reduced since you first subscribed!
	<a href="<?=$baseurl?>Admin/Ads">Update your account</a> and save now!
	</div>
	<?php } ?>
	<?php } ?>
</div>
</div>


<div style="clear:both"></div>