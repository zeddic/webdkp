<?php if(!$SiteUser->visitor && security::hasAccess("Control Panel")) { ?>
<div id="SiteManageLinks">
	<a href="<?=$SiteRoot?>admin/login?siteUserEvent=logout">Logout</a> |
	<a href="<?=$SiteRoot?>">Back to Site</a>
</div>
<?php } ?>