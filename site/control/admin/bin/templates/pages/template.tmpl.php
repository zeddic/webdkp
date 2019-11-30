



<form action="<?=$PHP_SELFDIR?>templates/<?=$template->id?>" method="post" name="templateform" style="display:inline">
<input type="hidden" name="event" value="updateTemplate">
<table class="controltable" style="width:450px">
<caption>Edit Template</caption>
<tbody>
<tr>
	<td class="leftmost" style="width:100px"><b>title</b></td>
	<td style="vertical-align:middle"><input type="text" name="title" value="<?=$template->title?>" style="width:205px"></td>
</tr>
<tr>
	<td class="leftmost"><b>content</b></td>
	<td style="vertical-align:middle">
		<a href="<?=$SiteRoot?><?=$template->url?>?editpage=1">edit template content</a>
	</td>
</tr>
<tr>
	<td class="leftmost"><b>use template</b></td>
	<td style="vertical-align:middle"><input type="checkbox" id="usetemplate" name="usetemplate" style="border:none" <?=($template->useTemplate?"checked":"")?> onclick="PageEditor.PageSettingsUpdateTemplateDropdown()"></td>
</tr>
<tr id="templateDropdownRow" style="display:<?=($template->useTemplate?"":"none")?>">
	<td class="left"><b>template</b></td>
	<td align=left style="vertical-align:middle">
		<select id="templatepage" name="templatepage" style="width:215px">
		<?php foreach($templatenames as $templatename){ ?>
		<option value="<?=$templatename->id?>" <?=(($template->useTemplate && $template->template->id==$templatename->id)?"selected":"")?>><?=$templatename->title?></option>
		<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td class="leftmost" style="vertical-align:top"><b>layout</b></td>
	<td >
		<select name="layout" style="width:215px" onchange="PageEditor.PageSettingsUpdateLayout(this.options[this.selectedIndex].value)">
		<?php foreach($layouts as $layout){ ?>
		<option value="<?=$layout->id?>|<?=$layout->getLayoutSample()?>" <?=($template->layout->id==$layout->id && $template->layout->useTemplate==false?"selected":"")?>><?=$layout->name?></option>
		<?php } ?>
		<?php if($template->useTemplate){ ?>
		<option value="0|<?=$theme->getAbsCommonDirectory()?>layouts/samples/default.gif" <?=($template->layout->inherited==true?"selected":"")?>>Template's Layout</option>
		<?php } ?>
		</select>
		<br />
		<?php if($template->layout->useTemplate) {?>
		<img id="PageSettingsTemplate" src="<?=$theme->getAbsCommonDirectory()?>layouts/samples/default.gif">
		<?php } else { ?>
		<img id="PageSettingsTemplate" src="<?=$template->layout->getLayoutSample()?>.">
		<?php } ?>
	</td>
</tr>
<tr>
	<td colspan=2">
		<input type="submit" value="Save Changes" class="mediumButton">
		<input type="button" value="Back" onclick="document.location='<?=$returnToUrl?><?=$SiteRoot?>admin/templates'" class="mediumButton">
	</td>
</tr>
</tbody>
</table>

</form>


<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:419px"><b><?=$eventMessage?></b></div>
<?php } ?>


