<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:869px"><b><?=$eventMessage?></b></div>
<?php } ?>

<table class="controltable" style="width:900px" cellspacing=0>
<caption>Part Library</caption>
<thead>
	<tr>
		<th><b>Name</b></th>
		<th><b>Created By</b></th>
		<th><b>Description</b></th>
		<th class="center"><b>Preview</b></th>
	</tr>
</thead>
<tbody>
<?php foreach($parts as $part) { $count++; ?>
<tr class="<?=($count%2==0?"odd":"")?>">
	<td class="leftmost top"  style="width:180px">
		<span style="font-weight:bold;font-size:150%"><?=$part->name?></span>
		<br />
		<br />
		<img src="<?=$directory?>images/refresh.png" style="vertical-align:middle"> <a href="<?=$PHP_SELF?>?event<?=$iid?>=reload&part<?=$iid?>=<?=$part->id?>">Reload</a>
	</td>
	<td class="left top" style="width:110px"><?=$part->createdBy?></td>
	<td class="left top"><?=$part->description?></td>
	<td class="center top">
		<?php if($part->screenshotExists()){ ?>
		<div class="controlImageBorder" style="float:right">
		<img src="<?=$part->getScreenshot()?>" ></div>
		<?php } else {?>
		None Available
		<?php } ?>
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
		<th><b>Part Name</b></th>
		<th><b>Error</b></th>
		<th class="center"><b>Preview</b></th>
	</tr>
</thead>
<tbody>
<tbody>
<?php foreach($scanErrors as $error) { $count++; ?>
<tr class="<?=($count%2==0?"odd":"")?>">
	<td class="leftmost top"  style="width:180px"><span style="font-weight:bold;font-size:150%"><?=$error->partname?></span></td>
	<td class="left top">
		<?php if($error->error == partScanError::ERR_PART_FILE_NOT_FOUND) { ?>
		The part's folder is either missing an <b>info.php</b> or <b>part<?=ucfirst($error->partname)?>.php</b> file.
		<?php } else if($error->error == partScanError::ERR_PART_CLASS_NOT_FOUND) { ?>
		The parts main class is incorrect. The class name in <?=$error->partfile?> must be named
		part<?=ucfirst($error->partname)?>.
		<?php } else if($error->error == partScanError::ERR_PART_PARSE_ERROR){ ?>
		There was a parsing error in the file <?=$error->partfile?>
		<?php } ?>
	</td>
	<td class="center top">
		None Available
	</td>
</tr>
<?php } ?>
</tbody>
</table>
<?php } ?>
