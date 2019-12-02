<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:869px"><b><?=$eventMessage?></b></div>
<?php } ?>

<table class="controltable" style="width:900px"  cellspacing=0>
<caption>Themes</caption>
<thead>
	<tr>
		<th><b>Name</b></th>
		<th><b>Created By</b></th>
		<th><b>Description</b></th>
		<th class="center"><b>Preview</b></th>
	</tr>
</thead>
<tbody>
<?php foreach($themes as $themeEntry) { $count++; ?>
<tr class="<?=($count%2==0?"odd":"")?> <?=($defaultTheme->id == $themeEntry->id?"highlight":"")?>">
	<td class="leftmost top"  style="width:180px">
		<span style="font-weight:bold;font-size:150%"><?=$themeEntry->name?></span>
		<?php if($defaultTheme->id == $themeEntry->id) { ?><img src="<?=$directory?>images/star.png" alt="Active Theme"><?php } ?>
		<br />
		<br />
		<?php if($defaultTheme->id == $themeEntry->id) { ?>
		<img src="<?=$directory?>images/refresh.png" style="vertical-align:middle"> <a href="<?=$PHP_SELF?>?event<?=$iid?>=reload">Reload</a>
		<?php } else { ?>
		<img src="<?=$directory?>images/accept.png" style="vertical-align:middle"> <a href="<?=$PHP_SELF?>?event<?=$iid?>=setTheme&themeid=<?=$themeEntry->id?>">Use Theme</a>
		<?php } ?>
	</td>
	<td class="left top" style="width:110px"><?=$themeEntry->createdby?></td>
	<td class="left top"><?=$themeEntry->description?></td>
	<td class="center top">
		<div class="controlImageBorder">
		<a href="<?=$themeEntry->getAbsDirectory()?>images/sample/sampleBig.gif" rel="lightbox">
		<img src="<?=$themeEntry->getAbsDirectory()?>images/sample/sample.gif" ></a></div>
	</td>
</tr>
<?php } ?>
</tbody>
</table>

<br />

<?php if(count($scanErrors)>0){ ?>
<table class="controltable" style="width:900px" cellspacing=0>
<caption>Scan Errors</caption>
<thead>
	<tr>
		<th><b>Theme Name</b></th>
		<th><b>Error</b></th>
		<th class="center"><b>Preview</b></th>
	</tr>
</thead>
<tbody>
<tbody>
<?php foreach($scanErrors as $error) { $count++; ?>
<tr class="<?=($count%2==0?"odd":"")?>">
	<td class="leftmost top"  style="width:180px"><span style="font-weight:bold;font-size:150%"><?=$error->themename?></span></td>
	<td class="left top">
		<?php if($error->error == themeScanError::ERR_THEME_FILE_NOT_FOUND) { ?>
		The theme does not have a <b>info.php</b> file.
		<?php } else if($error->error == themeScanError::ERR_THEME_CLASS_NOT_FOUND) { ?>
		The themes info.php is incorrect. The class name must be named
		theme<?=ucfirst($error->themename)?>.
		<?php } else if($error->error == themeScanError::ERR_THEME_PARSE_ERROR){ ?>
		There was a parsing error in the themes info.php file.
		<?php } ?>
	</td>
	<td class="center top">
		<?php if(file_exists("themes/$error->themename/images/sample.gif")){ ?>
		<div class="controlImageBorder" style="float:right">
		<a href="themes/<?=$error->themename?>/images/sampleBig.gif" rel="lightbox">
		<img src="themes/<?=$error->themename?>/images/sample.gif" ></a></div>
		<?php } else {?>None Available<? } ?>
	</td>
</tr>
<?php } ?>
</tbody>
</table>
<?php } ?>