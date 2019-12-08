<?php //CASE 1 - USER CAN EDIT PAGES OR GO TO CONTROL PANEL - SHOW MORE ADVANCED LINKS
if(!$SiteUser->visitor && (security::hasAccess("Edit Page") || security::hasAccess("Control Panel"))) {
?>


<?php if($editPageMode && $isTemplate) { ?>
<div id="TemplateBar">
	<img src="<?=$theme->getAbsCommonDirectory()?>images/editpage/star.png"> <span><?=$title?></span>
	<?php if($isFromPage) { ?>
		<span> | <a href="<?=$fromPage->url?>"><b>Go Back</b></a></span>
	<?php } ?>
</div>
<?php } else if($editPageMode && $useTemplate) { ?>
<div id="TemplateBar">
	<img src="<?=$theme->getAbsCommonDirectory()?>images/editpage/info.png"> <span>Uses template <a href="<?=$templateUrl?>"><b><?=$templateTitle?></b></a></span>
</div>
<?php } ?>

<?php if($editPageMode && !$isTemplate){ ?>
<div id="SiteManageLinks">
	<a href="javascript:PageEditor.ShowPartLibrary()">Add</a> |
	<a href="<?=$SiteRoot?>edit/<?=$pageid?>">Page Settings</a> |
	<a href="<?=$PHP_SELF?>?editpage=0">Stop Edit</a>
</div>
<?php }else if($editPageMode && $isTemplate){ ?>
<div id="SiteManageLinks">
	<a href="javascript:PageEditor.ShowPartLibrary()">Add</a> |
	<a href="<?=$PHP_SELF?>?editpage=0">Preview</a> |
	<?php if($isFromPage){ ?>
		<a href="<?=$SiteRoot?>edit/<?=$pageid?>">Template Settings</a>
	<?php } else { ?>
		<a href="<?=$SiteRoot?>admin/templates/<?=$pageid?>?editpage=0">Return to Templates</a>
	<?php } ?>
</div>
<?php }else if($isTemplate){ ?>
<div id="SiteManageLinks">
	<a href="<?=$PHP_SELF?>?editpage=1">Exit Preview</a>
</div>
<?php }else if($system!=1){ ?>
<div id="SiteManageLinks">
	<?php if(security::hasAccess("Control Panel")){ ?>
	<a href="<?=$SiteRoot?>admin">Control Panel</a>
	<?php } ?>
</div>
<?php }else if($system==1 && !$isEditor){ ?>
<div id="SiteManageLinks">
	<a href="<?=$SiteRoot?>">Back to Site</a>
</div>
<?php } ?>

<?php } ?>