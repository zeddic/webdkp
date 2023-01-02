Here you can request a new server to be added to the WebDKP System. New Server requests
ussually take about a day to complete. You can always create an account now using one
of the current servers, then easily transfer your account to the new server once it becomes
available.
<br />
<br />
<b>Only <u>real</u> Blizzard server names will be created.</b> Requests for
other server names, for example, "WoW Brasil" will be ignored.
<br />
<br />

<div class="roundedcornr_box">
<div class="roundedcornr_top"><div></div></div>
<div class="roundedcornr_content">


<form action="<?=$PHP_SELF?>" method="post" name="requestserver">
<input type="hidden" name="event" value="submitServer">

<table class="signup">
<tr>
	<td class="label" style="width:150px">Server Name:</td>
	<td><input name="server" type="text"></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Request Server"></td>
</tr>
</table>

</form>

</div>
<div class="roundedcornr_bottom"><div></div></div>
</div>
<br />
<br />
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>