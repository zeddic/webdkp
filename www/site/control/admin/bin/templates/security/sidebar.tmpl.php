<?php
$view = util::getData("view");
if ($view == "user" || $view == "")
	$view = "users";
if($view == "usergroup" )
	$view = "groups";
?>

<div class="navigationContainer">
	<ul class="navigationList">
		<?php if(security::hasAccess("Edit Users")){ ?><li class="<?=($view=="users"?"active":"")?>"><a href="<?=$SiteRoot?>admin/users">Users</a></li><?php } ?>
		<?php if(security::hasAccess("Edit User Groups")){ ?><li class="<?=($view=="groups"?"active":"")?>"><a href="<?=$SiteRoot?>admin/usergroups">User Groups</a></li><?php } ?>
		<?php if(security::hasAccess("Edit Permissions")){ ?><li class="<?=($view=="permissions"?"active":"")?>"><a href="<?=$SiteRoot?>admin/permissions">Permissions</a></li><?php } ?>
	</ul>
</div>