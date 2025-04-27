<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">
<br />
Here you can edit a custom Remote DKP table. This allows you to style your
remote table so it fits in with the rest of your site. To style you're table
you'll need to know <a href="http://www.w3schools.com/web/web_css.asp">CSS</a>.
<br />
<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />
<form action="<?=$baseurl?>Admin/EditRemoteStyle" method="post" name="updateGuild">
<input type="submit" class="largeButton" value="Save Changes" style="width:125px" onclick="this.value='Saving...'">
<input type="button" class="largeButton" value="Preview" onclick="window.open('<?=$baseurl?>RemotePreview','WebDKPPreview','width=600,toolbar=yes,scrollbars=yes,resizable=yes')">
<input type="button" class="largeButton" value="Back" onclick="document.location='<?=$baseurl?>Admin/RemoteStyle'">

<br /><br />

<input type="hidden" name="event" value="saveChanges">
<textarea name="content" style="width:100%;height:500px;padding:2px;"><?=$custom->content?></textarea>
</form>

<br />
<br />
<br />


</div>
