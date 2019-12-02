<?=$tabs?>
<?=$sidebar?>
<br />
<div class="adminContents">

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/info.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Instructions</div>
	Here you can download a  backup file of your accounts' DKP information.
	This backup contains a snapshot of all the dkp tables, awards,
	and player history. You can use generated backup files to restore your DKP table in
	the future, or move your information to a new account.
	<br />
	<br />
	<i>Please Note</i> - The backup file does not contain your loot table. You
	must backup your loot table independently.
</div>
<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/backup.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Download Backup</div>
	To download a backup, click the download button below.
	<br />
	<br />
	<input type="button" class="largeButton" value="Download Backup" onclick="document.location='<?=$baseurl?>Admin/Backup?event=backup'">
</div>
<br />
<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/restore.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Restore Backup</div>
	Here you can upload a DKP Backup file. This will restore all information within
	the backup file to the site. You have two options when performing a restore: a
	<u>merge</u> or a <u>full restore</u>.
	<br />
	<br />
	A <u>merge</u> will combine any information in your backup file with information that
	is currently on the site. Use this if you accidently deleted a history or an award
	and you want to restore it from a backup. You will not lose any information when using
	a merge.
	<br />
	<br />
	A <u>full restore</u> will completly <span style="color:red">erase all your current data</span> and
	replace it with whatever is contained within your backup file. This restores your online
	site to the exact same state it was in when you downloaded the backup.
	<br />
	<br />
	<div class="noticeMessage">If you are doing a full restore, it is <b>highly</b> recommended that you create
	an extra backup first. WebDKP takes no responsibility for data that you lose by performing a restore.</div>
	<br />

	<form name="uploadLog" enctype="multipart/form-data"  action="<?=$baseurl?>Admin/Backup" method="post">
	<input type='hidden' name='event' value='upload'>

	<select name="restoreType" class="largeInput" style="width:175px;margin-bottom:5px;">
		<option value="merge">Merge</option>
		<option value="fullrestore">Full Restore</option>
	</select>
	<br />
	<input type="file" name="userfile" class="formInput" >
	<input type="submit" value="Upload" class="mediumButton" onclick="this.value='Uploading...'">
	</form>
	<br />

	<?php if(isset($eventResult) && !$eventResult){ ?>
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>

	<?php if(isset($log)) { ?>
	<div class="message">
	<b><?=$eventMessage?></b>
	<br />
	<br />
	<b>Restore Log:</b> <br />
	<?=$log?>
	</div>
	<?php } ?>

</div>
<br />
<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/restore.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">LUA Restore</div>
	A LUA restore is a special restore operation that reads your DKP values from your
	WebDKP.lua log file. This will set everyones DKP values to exactly the values that
	you see in game. Unfortuantly, .lua files are not intended to be used as
	backup files and therefore do not contain complete player history. <u>Only</u> use this
	option if you need to make a restore and you do not have a regular backup file.
	<br />
	<br />
	<form name="uploadmini" enctype="multipart/form-data"  action="<?=$baseurl?>Admin/Backup" method="post">
	<input type='hidden' name='event' value='miniUpload'>
	<input type="file" name="userfile" class="formInput" >
	<input type="submit" value="Upload" class="mediumButton" onclick="this.value='Uploading...'">
	</form>
	<br />

	<?php if(isset($eventResult) && !$eventResult){ ?>
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>

	<?php if(isset($minilog)) { ?>
	<div class="message">
	<b><?=$eventMessage?></b>
	<br />
	<br />
	<b>Restore Log:</b> <br />
	<?=$minilog?>
	</div>
	<?php } ?>
</div>


<br />
<br />

</div>
