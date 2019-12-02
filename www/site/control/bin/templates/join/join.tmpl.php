Already a member? <a href="<?=$SiteRoot?>/login">Login!</a>
<br />
<br />
Creating an account on WebDKP is quick, easy, and best of all, <b>free</b>!
<br />
<br />
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult==user::REGISTER_OK?"message":"errorMessage")?>"><?=$eventMessage?></div>
<br />
<?php } ?>


<div class="roundedcornr_box">
<div class="roundedcornr_top"><div></div></div>
<div class="roundedcornr_content">


<form action="<?=$PHP_SELF?>" method="post" name="signup">
<input type="hidden" name="event" value="register">

<table class="signup">
<tr>
	<td class="label">Username:</td>
	<td><input name="username" type="text" value="<?=$username?>"></td>
</tr>
<tr>
	<td class="label">Password:</td>
	<td><input name="password" type="password"></td>
</tr>
<tr>
	<td class="label">Confirm:</td>
	<td><input name="password2" type="password"> (Retype Password)</td>
</tr>
<tr>
	<td class="label">Guild:</td>
	<td><input name="guild" type="text"></td>
</tr>
<tr>
	<td class="label">Server:</td>
	<td>
		<select name="server">
			<? foreach($servers as $server){ ?>
			<option value="<?=$server->name?>" <?=($server->name==$servername?"selected='selected'":"")?>><?=$server->name?></option>
			<?php } ?>
		</select>
		<a href="<?=$siteRoot?>ServerMissing">Server Missing?</a>
	</td>
</tr>
<tr>
	<td class="label">Faction:</td>
	<td>
		<select name="faction">
			<option value="Alliance">Alliance</option>
			<option value="Horde" <?=($faction=="Horde"?"Selected":"")?>>Horde</option>
		</select>
	</td>
</tr>
<tr>
	<td class="label">Email:</td>
	<td><input name="email" type="text" value="<?=$email?>"></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Signup!"></td>
</tr>
</table>

</form>

</div>
<div class="roundedcornr_bottom"><div></div></div>
</div>





