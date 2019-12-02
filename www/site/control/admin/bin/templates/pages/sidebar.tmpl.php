<?php
$view = util::getData("view");
if ($view == "template")
	$view = "templates";
if($view == "webpage" || $view == "")
	$view = "webpages";
?>

<div class="navigationContainer">
	<ul class="navigationList">
		<li class="<?=($view=="webpages"?"active":"")?>"><a href="<?=$SiteRoot?>admin/webpages">Pages</a></li>
		<li class="<?=($view=="templates"?"active":"")?>"><a href="<?=$SiteRoot?>admin/templates">Templates</a></li>
	</ul>
</div>