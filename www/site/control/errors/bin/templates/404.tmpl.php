Sorry, the file you were looking for could not be found.

<?php if($path != "Errors/404" && security::hasAccess("Create Page")) { ?>
<form action="<?=$SiteRoot?>Edit/NewPage" name="newpageform" method="post" style="display:inline">
<input type="hidden" name="newFileName" value="<?=$path?>">
<br />
<br />
<input type="Submit" class="mediumButton" value="Create Page">
</form>

<?php } ?>