<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<?php if(isset($eventResult)){ ?>
<div style="margin-left:70px;width:470px" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />


<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/guild.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Officer Accounts</div>
	Officer Accounts are other accounts that have access to manage your DKP tables.
	These accounts have a seperate user name and password. You can customize
	the access of Officer accounts or make them Administrators.
	<br />

	<?php if(sizeof($accounts)==0){ ?>
	<?php } else { ?>
	<br />
	<table class="dkp" style="width:500px" id="accountTable" cellspacing=0>
		<tr class="header">
			<th>Name</th>
			<th class="center">Email</th>
			<th class="center">Action</th>
		</tr>
		<?php foreach($accounts as $account) { ?>
		<tr>
			<td><?=$account->username?></td>
			<td class="center"><?=$account->email?></td>
			<td class="center middle">
				<a class="dkpbutton" href="<?=$baseurl?>Admin/EditOfficerAccount?id=<?=$account->id?>"><img title="Edit Account" src="<?=$siteRoot?>images/buttons/edit.png"></a>
				<a class="dkpbutton" href="<?=$baseurl?>Admin/OfficerAccounts?event=deleteAccount&id=<?=$account->id?>"
				onclick="return confirm('Are you sure that you want to delete this account?')">
				<img title="Delete Account" src="<?=$siteRoot?>images/buttons/delete.png"></a>
			</td>
		</tr>
		<?php } ?>
	</table>
	<script type="text/javascript">
	table = new DKPTable("accountTable");
	table.DrawSimple();
	</script>
	<?php } ?>


</div>


<br />
<br />
<form action="<?=$baseurl?>Admin/OfficerAccounts" method="post" name="newOfficer">
<input type="hidden" name="event" value="createAccount">
<div style="float:left;padding-top:10px;"><img src="<?=$siteRoot?>images/dkp/account.gif"></div>
<div style="margin-left:70px">
	<table class="dkpForm" >
	<tr>
		<td colspan=2 class="title">Create New Officer Account</td>
	</tr>
	<tr>
		<td class="label" style="width:180px">Username:</td>
		<td><input name="username" type="text" value="" ></td>
	</tr>
	<tr>
		<td class="label" style="width:180px">Email:</td>
		<td><input name="email" type="text" value="" ></td>
	</tr>
	<tr>
		<td class="label">Password:</td>
		<td><input name="password1" type="password" value="" ></td>
	</tr>
	<tr>
		<td class="label">Retype Password:</td>
		<td><input name="password2" type="password" value="" ></td>
	</tr>
	<tr>
		<td colspan=2><input type="submit" value="Create Account"></td>
	</tr>
	</table>
</div>
</form>



</div>
