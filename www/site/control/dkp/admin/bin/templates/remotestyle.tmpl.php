<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/styleinfo.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Instructions</div>
	Here you can select the style for your <a href="<?=$baseurl?>admin/remote">Remote DKP table</a>. Different styles
	will cause your table to appear differently. You can choose a premade style that fits
	in with the rest of your stie or create your own. To create you own you'll
	need to know some CSS. These styles <b>only</b> work for Remote DKP tables. You can not change the style
	of your table on WebDKP.com
	<?php if(isset($eventResult)){ ?>
	<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
	<?php } ?>
</div>

<br />
<br />

<div class="adminSectionImage" ><img src="<?=$siteRoot?>images/dkp/style<?=($selected->file=="custom"?"custom":"")?>.gif"></div>
<div class="adminSection" style="padding-left:2px;min-height:230px">
	<img style="float:right;border:1px solid #E3E3E3; padding:4px; background: #F0F0F0" src="<?=$selected->getScreenshot()?>">
	<div class="title"><?=$selected->name?> - <span style="color:green">Active</span></div>
	<table style="padding-top:5px;padding-right: 10px;">
	<tr>
		<td style="width:100px"><b>Created By: </b></td>
		<td><?=$selected->createdby?></td>
	</tr>
	<tr>
		<td><b>Description: </b></td>
		<td><?=$selected->description?></td>
	</tr>
	<tr>
		<td colspan=2>
		<br />
		<?php if($selected->file=="custom"){ ?>
		<input style="width:100px" type="button" class="mediumButton" value="Edit Style" onclick="document.location='<?=$baseurl?>admin/EditRemoteStyle'">
		<?php } ?>
		<input style="width:100px" type="button" class="mediumButton" value="Preview" onclick="window.open('<?=$baseurl?>RemotePreview','WebDKPPreview','width=600,height=400,toolbar=yes,scrollbars=yes,resizable=yes')">
		</td>
	</tr>
	</table>
</div>
<?php foreach($styles as $style) { ?>
<?php if($style->id == $selectedid){ continue; } ?>
<div class="adminSectionImage" ><img src="<?=$siteRoot?>images/dkp/style<?=($style->file=="custom"?"custom":"")?>.gif"></div>
<div class="adminSection" style="padding-left:2px;min-height:230px">
	<img style="float:right;border:1px solid #E3E3E3; padding:4px; background: #F0F0F0" src="<?=$style->getScreenshot()?>">
	<div class="title"><?=$style->name?></div>
	<table style="padding-top:5px">
	<tr>
		<td style="width:100px"><b>Created By: </b></td>
		<td><?=$style->createdby?></td>
	</tr>
	<tr>
		<td><b>Description: </b></td>
		<td><?=$style->description?></td>
	</tr>
	<tr>
		<td colspan=2>
		<br />
		<input style="width:100px" type="button" class="mediumButton" value="Select Style" onclick="document.location='<?=$baseurl?>admin/RemoteStyle?event=SelectStyle&id=<?=$style->id?>'">
		<input style="width:100px" type="button" class="mediumButton" value="Preview" onclick="window.open('<?=$baseurl?>RemotePreview?styleid=<?=$style->id?>','WebDKPPreview','width=600,height=400,toolbar=yes,scrollbars=yes,resizable=yes')">
		</td>
	</tr>
	</table>
	<br />
</div>
<?php } ?>
<br />
<br />
<br />


</div>
