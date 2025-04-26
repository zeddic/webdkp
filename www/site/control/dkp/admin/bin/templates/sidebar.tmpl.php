
<div id="adminlist">
<img src="<?=$siteRoot?>images/sidebar_corner.gif" id="admincorner">
<ul>
	<li><a href="#">Tasks</a>
		<ul id="subnavlist">
			<li <?=($self=="manage"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Manage">Edit DKP Tables</a></li>
			<?php if($canUploadLog){ ?>
			<li <?=($self=="upload"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Upload">Upload Log File</a></li>
			<?php } ?>
			<li <?=($self=="download"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Download">Download Log File</a></li>
		</ul>
	</li>
	<li><a href="#">Account Settings</a>
		<ul id="subnavlist">
			<li <?=($self=="updateaccount"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/UpdateAccount">Update User Account</a></li>
			<?php if($canEditGuild){ ?>
			<li <?=($self=="updateguild"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/UpdateGuild">Update Guild</a></li>
			<?php } ?>
			<?php if($canEditOfficers){ ?>
			<li <?=($self=="officeraccounts"||$self=="editofficeraccount"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/OfficerAccounts">Officer Accounts</a></li>
			<?php } ?>
		</ul>
	</li>
	<?php if($canChangeSettings || $canManageDKPTables || $canManageLootTable) { ?>
	<li><a href="#">Settings</a>
		<ul id="subnavlist">
			<?php if($canChangeSettings) { ?>
			<li <?=($self=="settings"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Settings">Settings</a></li>
			<?php } ?>
			<?php if($canManageDKPTables) { ?>
			<li <?=($self=="dkptables" || $self=="editdkptable"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/DKPTables">Create & Delete Tables</a></li>
			<?php } ?>
			<?php if($canManageLootTable) { ?>
			<li <?=($self=="loottable" || $self=="editloottable"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/LootTable">Loot Table</a></li>
			<?php } ?>
		</ul>
	</li>
	<?php } ?>
	<?php if($canBackup || $canRepair) { ?>
	<li><a href="#">Maintenance</a>
		<ul id="subnavlist">
			<?php if($canRepair) { ?>
			<li <?=($self=="repair"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Repair">Repairs</a></li>
			<?php } ?>
			<?php if($canBackup){ ?>
			<li <?=($self=="backup"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Backup">Backups</a></li>
			<?php } ?>
		</ul>
	</li>
	<?php } ?>
	<li><a href="#">Remote DKP</a>
		<ul id="subnavlist">
			<li <?=($self=="remote"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/Remote">WebDKP on Your Site</a></li>
			<li <?=($self=="remotestyle"||$self=="editremotestyle"?"class='active'":"")?>><a href="<?=$baseurl?>Admin/RemoteStyle">Table Style</a></li>
		</ul>
	</li>
</ul>
<br />
<br />
</div>

