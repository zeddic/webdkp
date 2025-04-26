<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<?php if(isset($eventResult)){ ?>
<div style="margin-left:70px;width:375px" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>

<br />

<form action="<?=$baseurl?>Admin/UpdateAccount" method="post" name="updateAccount">
<input type="hidden" name="event" value="updateAccount">
<div style="float:left;padding-top:10px;"><img src="<?=$siteRoot?>images/dkp/account.gif"></div>
<div style="margin-left:70px">
<table class="dkpForm" >
<tr>
	<td colspan=2 class="title">Change Account Details</td>
</tr>
<tr>
	<td class="label" style="width:180px">Username:</td>
	<td><input name="username" type="text" value="<?=$siteUser->username?>" ></td>
</tr>
<tr>
	<td class="label">Email:</td>
	<td><input name="email" type="text" value="<?=$siteUser->email?>"></td>
</tr>
<tr>
	<td class="label">Current Password:</td>
	<td><input name="password" type="password" value=""></td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Save Changes"></td>
</tr>
</table>
</div>
</form>

<br />
<br />

<form action="<?=$baseurl?>Admin/UpdateAccount" method="post" name="updatePassword">
<input type="hidden" name="event" value="updatePassword">
<div style="float:left;padding-top:10px;"><img src="<?=$siteRoot?>images/dkp/password.gif"></div>
<div style="margin-left:70px">
<table class="dkpForm" >
<tr>
	<td colspan=2 class="title">Change Password</td>
</tr>
<tr>
	<td class="label" style="width:180px">New Password:</td>
	<td><input name="password1" type="password" value="" ></td>
</tr>
<tr>
	<td class="label">Retype Password:</td>
	<td><input name="password2" type="password" value="" ></td>
</tr>
<tr>
	<td class="label">Current Password:</td>
	<td><input name="currentPassword" type="password" value=""></td>
</tr>
<tr>
	<td colspan=2><input type="submit" value="Save Changes"></td>
</tr>
</table>
</div>
</form>

<br />
<br />


<?php if($canEditOfficers){ ?>
<form action="<?=$baseurl?>Admin/UpdateAccount" method="post" name="deleteAccount">
<input type="hidden" name="event" value="DeleteAccount">
<div style="float:left;padding-top:10px;"><img src="<?=$siteRoot?>images/dkp/trash_can.png"></div>
<div style="margin-left:70px">
<table class="dkpForm" >
<tr>
	<td colspan=2 class="title">Delete Account</td>
</tr>
<tr>
	<td colspan=2>Deleting your account will completely clear all data from webdkp.com. This is only recommended if you are no longer planning on using WebDKP, or you want to re-create your account.</td>
	
</tr>
<tr>
<a class="dkpbutton" href="<?=$baseurl?>Admin/OfficerAccounts?event=deleteAccount"
				onclick="return confirm('Are you sure that you want to delete this account?')">
	<td colspan=2><input type="submit" value="Delete!" onclick="return confirm('Are you sure you want to delete your account? All information will be lost.')"></td>
</tr>
</table>
</div>
</form>

<?php } ?>

</div>
