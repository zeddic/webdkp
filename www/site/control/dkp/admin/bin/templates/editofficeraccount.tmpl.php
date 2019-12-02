<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />
<script type='text/javascript'>
function UpdatePermissionVisibility(){
	var admin = $("adminAccount").checked;
	if(admin)
		Util.Hide('customPermissionsDiv');
	else
		Util.Show('customPermissionsDiv');
}

function UpdateTableVisibility(){
	var show = $("selectedTables").checked;
	if(show)
		Util.Show('selectedTablesDiv');
	else
		Util.Hide('selectedTablesDiv');
}
</script>

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/guild.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Editing Officer Account <?=$account->username?></div>

	Here you can setup custom permissions for this account. You can limit what features
	of the site the account can use as well as limit what tables they can work with.
	<br />
	<br />

	<input type="button" class="largeButton" value="Save Changes" style="width:150px" onclick="this.value='Saving...';Util.Submit('editPermissions')">
	<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$baseurl?>Admin/OfficerAccounts'">

	<br />
	<?php if(isset($eventResult)){ ?>
	<br />
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>
	<br />

	<a href="javascript:Util.Toggle('resetPassword')"><img src="<?=$siteRoot?>images/buttons/lock.png" style="vertical-align:text-bottom"> Reset Password</a>
	<div id="resetPassword" style="display:none">
		<form action="<?=$baseurl?>Admin/EditOfficerAccount?id=<?=$account->id?>" method="post" name="newOfficer">
		<input type="hidden" name="event" value="updatePassword">
		<table class="dkpForm" >
		<tr>
			<td class="label" style="width:150px">New Password:</td>
			<td><input name="password1" type="password" value="" ></td>
		</tr>
		<tr>
			<td class="label">Retype:</td>
			<td><input name="password2" type="password" value="" ></td>
		</tr>
		<tr>
			<td colspan=2><input type="submit" class="largeButton" value="Reset"></td>
		</tr>
		</table>
		</form>
	</div>
	<br />
	<br />


	<form name="editPermissions" action="<?=$baseurl?>Admin/EditOfficerAccount?id=<?=$account->id?>" method="post">
	<input type='hidden' name='event' value='updateAccount'>

	<!-- ACCOUNT TYPE -->
	<table width="300px" class="stripped">
	<tr class="clear">
		<td colspan=2 class="underline"><b>Account Type:</b></td>
	</tr>
	<tr>
		<td width="275"><a href="javascript:;" class="tooltip" tooltip="Admin accounts have unrestricted access to the all DKP tables and Settings." onclick="$('adminAccount').checked=true;UpdatePermissionVisibility()">Admin Account:</a></td>
		<td width="25"><input type="radio" name="accountType" id="adminAccount" value="adminAccount" style="border:0px" <?=($permissions->isAdmin==1?"CHECKED":"")?> onclick="UpdatePermissionVisibility()"></td>
	</tr>
	<tr class="odd">
		<td><a href="javascript:;" class="tooltip" tooltip="Custom accounts let you spepcify what permissions the user should have." onclick="$('customAccount').checked=true;UpdatePermissionVisibility()">Custom Account:</a></td>
		<td><input type="radio" name="accountType" id="customAccount" value="customAccount" style="border:0px" <?=($permissions->isAdmin==0?"CHECKED":"")?> onclick="UpdatePermissionVisibility()"></td>
	</tr>
	</table>
	<br />
	<div id="customPermissionsDiv" style="display:<?=($permissions->isAdmin?"none":"block")?>">


	<!-- TABLE ACCESS -->
	<table width="300px" class="stripped">
	<tr class="clear">
		<td colspan=2 class="underline"><b>Table Access:</b></td>
	</tr>
	<tr>
		<td width=275><a href="javascript:;" class="tooltip" tooltip="User has access to all DKP tables. Note: custom permissions will still apply." onclick="$('allTables').checked=true;UpdateTableVisibility()">All Tables:</a></td>
		<td width=25><input type="radio" id="allTables" name="tableAccess" value="allTables" style="border:0px" <?=($permissions->userHasPermission("AllTableAccess")?"checked":"")?> onclick="UpdateTableVisibility()"></td>
	</tr>
	<tr class="odd">
		<td><a href="javascript:;" class="tooltip" tooltip="User will only have access to the tables you select below. Custom permissions still apply." onclick="$('selectedTables').checked=true;UpdateTableVisibility()">Selected Tables:</a></td>
		<td><input type="radio" id="selectedTables" name="tableAccess" value="selectedTables" style="border:0px" <?=(!$permissions->userHasPermission("AllTableAccess")?"checked":"")?> onclick="UpdateTableVisibility()"></td>
	</tr>
	</table>


	<!-- TABLE LIST-->
	<div id="selectedTablesDiv" style="display:<?=($permissions->userHasPermission("AllTableAccess")?"none":"block")?>">
	<br />
	<table width="300px" id="selectedTablesList" class="stripped">
	<tr class="clear">
		<td colspan=2 class="underline"><b>Selected Tables:</b></td>
	</tr>
	<?php $i=0; foreach($tables as $table){ $i++;?>
	<tr <?=($i%2==1?"class='odd'":"")?>>
		<td width=275><?=$table->name?></td>
		<td width=25><input type="checkbox" name="selectedTables[]" value="<?=$table->tableid?>" style="border:0px" <?=($permissions->userHasAccessToTable($table->tableid)?"CHECKED":"")?>></td>
	</tr>
	<?php } ?>
	</table>
	</div>
	<br />

	<!-- CUSTOM PERMISSIONS -->
	<table width="300px" id="customPermissions" class="stripped">
	<tr class="clear">
		<td colspan=2><b>Custom Permissions:</b></td>
	</tr>
	<tr>
		<td width="275">
			<a href="javascript:;" class="tooltip" tooltip="Allows a player to create / edit / and delete multiple DKP tables. Note: a user with this right has the ability to delete your table!">
			Table Management:</a><img src="modules/moduleDkp/images/icons/warning.gif" style="vertical-align:middle">
		</td>
		<td width="25"><input type="checkbox" name="customPermissions[]" value="DKPTables" style="border:0px" <?=($permissions->userHasPermission("DKPTables")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>
			<a href="javascript:;" class="tooltip" tooltip="Allows a user to create and edit officer accounts. This permission should only be given to an admin!">
			Edit Officer Accounts:</a><img src="modules/moduleDkp/images/icons/warning.gif" style="vertical-align:middle">
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="AccountSecondaryUsers" style="border:0px" <?=($permissions->userHasPermission("AccountSecondaryUsers")?"CHECKED":"")?>></td>
	</tr>
	<tr>
		<td>
			<a href="javascript:;" class="tooltip" tooltip="Allows a player to upload a log to the website. Note that this will still be affected by what tables they have access to. Any additions in the log to tables that they don't have access to will be ignored.">
			Upload Logs:</a>
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableUploadLog" style="border:0px" <?=($permissions->userHasPermission("TableUploadLog")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>Create Backups:</td>
		<td><input type="checkbox" name="customPermissions[]" value="BackupCreate" style="border:0px" <?=($permissions->userHasPermission("BackupCreate")?"CHECKED":"")?>></td>
	</tr>
	<tr >
		<td>Restore Backups:</td>
		<td><input type="checkbox" name="customPermissions[]" value="BackupRestore" style="border:0px" <?=($permissions->userHasPermission("BackupRestore")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>Table Repairs:</td>
		<td><input type="checkbox" name="customPermissions[]" value="Repair" style="border:0px" <?=($permissions->userHasPermission("Repair")?"CHECKED":"")?>></td>
	</tr>
	<tr>
		<td>
			Edit Guild Details:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="AccountEditGuild" style="border:0px" <?=($permissions->userHasPermission("AccountEditGuild")?"CHECKED":"")?>></td>
	</tr>

	<tr class="odd">
		<td>
			Change Settings:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="ChangeSettings" style="border:0px" <?=($permissions->userHasPermission("ChangeSettings")?"CHECKED":"")?>></td>
	</tr>
	<tr>
		<td>
			Edit Loot Tables:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="LootTable" style="border:0px" <?=($permissions->userHasPermission("LootTable")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>
			Add Awards:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableAddPoints" style="border:0px" <?=($permissions->userHasPermission("TableAddPoints")?"CHECKED":"")?>></td>
	</tr>
	<tr class="">
		<td>
			Add Players:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableAddPlayer" style="border:0px" <?=($permissions->userHasPermission("TableAddPlayer")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>
			Delete Players:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableDeletePlayer" style="border:0px" <?=($permissions->userHasPermission("TableDeletePlayer")?"CHECKED":"")?>></td>
	</tr>
	<tr class="">
		<td>
			Edit Players:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableEditPlayers" style="border:0px" <?=($permissions->userHasPermission("TableEditPlayers")?"CHECKED":"")?>></td>
	</tr>
	<tr class="odd">
		<td>
			Edit History:
		</td>
		<td><input type="checkbox" name="customPermissions[]" value="TableEditHistory" style="border:0px" <?=($permissions->userHasPermission("TableEditHistory")?"CHECKED":"")?>></td>
	</tr>
	</table>

	</div>
	</form>

</div>


<br />
<br />





</div>
