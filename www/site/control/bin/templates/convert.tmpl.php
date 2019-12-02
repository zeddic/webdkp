<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />
<br />

<input type="button" class="largeButton" value="Convert Users" onclick="document.location='<?=$baseurl?>Convert?event=convertUsers'">
<br />
<br />
<input type="button" class="largeButton" value="Create Awards" onclick="document.location='<?=$baseurl?>Convert?event=createAwards'">
<br />
<br />
<input type="button" class="largeButton" value="Convert History" onclick="document.location='<?=$baseurl?>Convert?event=convertHistory'">
<br />
<br />
<input type="button" class="largeButton" value="Convert Loot Tables" onclick="document.location='<?=$baseurl?>Convert?event=convertLoot'">
<br />
<br />
<input type="button" class="largeButton" value="Convert Options" onclick="document.location='<?=$baseurl?>Convert?event=convertOptions'">
<br />
<br />
<input type="button" class="largeButton" value="Convert Permissions" onclick="document.location='<?=$baseurl?>Convert?event=convertPermissions'">
<br />
<br />
<input type="button" class="largeButton" value="Check Users" onclick="document.location='<?=$baseurl?>Convert?event=checkUsers'">
<br />
<br />
<input type="button" class="largeButton" value="Check Guilds" onclick="document.location='<?=$baseurl?>Convert?event=checkGuilds'">
<br />
<br />
<input type="button" class="largeButton" value="Check Zerosum" onclick="document.location='<?=$baseurl?>Convert?event=checkZerosum'">
<br />
<br />
<input type="button" class="largeButton" value="Fix Guilds" onclick="document.location='<?=$baseurl?>Convert?event=fixGuilds'">
<br />
<br />
<input type="button" class="largeButton" value="Check User Spaces" onclick="document.location='<?=$baseurl?>Convert?event=checkUserSpaces'">
<br />
<br />

<?php if(isset($log)) { ?>
<div class="message">
<?=$log?>
</div>
<?php } ?>


<?php if($creatingAwards && $lastGuild != "") { ?>
<script type="text/javascript">
function nextAward(){
	document.location='<?=$baseurl?>Convert?event=createAwards';
}
setTimeout("nextAward()",500);
</script>
<?php } ?>

<?php if($convertingHistory && $lastGuild != "") { ?>
<script type="text/javascript">
function nextAward(){
	document.location='<?=$baseurl?>Convert?event=convertHistory';
}
setTimeout("nextAward()",500);
</script>
<?php } ?>

<?php if($convertingLoot && $lastGuild != "") { ?>
<script type="text/javascript">
function nextAward(){
	document.location='<?=$baseurl?>Convert?event=convertLoot';
}
setTimeout("nextAward()",500);
</script>
<?php } ?>