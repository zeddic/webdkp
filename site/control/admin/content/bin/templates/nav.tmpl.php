<?php
$view = util::getData("view");
if(strpos($PHP_SELF,"news") && $view=="edit" )
	$view = "write";
else if(strpos($PHP_SELF,"news"))
	$view = "news";
else
	$view = "default";
?>

<div class="navigationContainer">
	<ul class="navigationList">
		<li class="<?=($view=="default"?"active":"")?>"><a href="<?=$SiteRoot?>admin/content/">Content Home</a></li>
		<li class="<?=($view=="news"?"active":"")?>"><a href="<?=$SiteRoot?>admin/content/news/">News</a></li>
	</ul>
</div>