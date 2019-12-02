<?php
global $ShowAds;
global $siteUser;
$browser = $_SERVER['HTTP_USER_AGENT'];
if(stripos($browser, "MSIE 6.0") !== false ) {
	if(stripos($browser, "compatible; MSIE 6.0;") === false)
		$ie6 = true;
}

global $baseurl;

?>

<div id="container" class="Columns1">
	<?=$header?>
	<?php if($ShowAds && !$ie6) { ?>


	<?php } ?>
	<div id="contents" <?=($ShowAds?"style='margin-left:185px'":"")?>>
		<?=implode($area2)?>
		<div style="clear:both"></div>
		
	</div>
	<?=$footer?>
</div>
<?=$links?>
