If you forgot your account's passowrd, you can request it to be reset here.
To reset your account, please enter your username below. An email will be
sent to you with more instructions. If you did not register an email address with your
account, you will need to submit a reset request on the
<a href="<?=$SiteRoot?>phpBB2/">Forums</a>.
<br />
<br />

<div class="roundedcornr_box">
<div class="roundedcornr_top"><div></div></div>
<div class="roundedcornr_content">


<form action="<?=$PHP_SELF?>" method="post" name="requestserver">
<input type="hidden" name="event" value="RequestReset">

<table class="signup">
<tr>
	<td class="label" style="width:150px">Username:</td>
	<td><input name="username" type="text" value=""></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Reset Password"></td>
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