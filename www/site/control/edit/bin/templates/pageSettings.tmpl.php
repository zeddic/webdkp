<?php if($page->isControlFile ){ ?>
<div class="noticeMessage">This page is a code behind page. Some settings may be hard coded and cannot be changed here.</div>
<?php }  ?>
<br />

<input id="saveChangesButton" class="mediumButton" type="button" value="Save Changes" style="width:130px" onclick="PageSettings.SaveChanges()">
<input class="mediumButton"  type="button" value="Back" onclick="document.location='<?=$returntoUrl?>'" >

<br />

<form  action="<?=$PHP_SELFDIR?><?=$page->id?>" method="post" id="pageSettingsForm" style="display:inline">
<input type="hidden" name="ajax" value="updatePageSettings">
<br />

<div class="editTabs">
	<ul >
		<li class="selected" id="generalTab"><a href="javascript:PageSettings.ShowGeneralTab()"><span>General</span></a></li>
		<li class="back" id="layoutTab"><a href="javascript:PageSettings.ShowLayoutTab()"><span>Layout</span></a></li>
		<li class="back" id="permissionsTab"><a href="javascript:PageSettings.ShowPermissionsTab()"><span>Permissions</span></a></li>
		<li class="back" id="templateTab"><a href="javascript:PageSettings.ShowTemplateTab()"><span>Template</span></a></li>
	</ul>
	<div class="editTabsUnderline"></div>
</div>
<div id="editorContent">

	<!-- General Input -->
	<div id="generalTabContent" >
		The title of the page is the title that users will see for their browser window.
		<table>
			<tr>
				<td class="formLabel">title</td>
			</tr>
			<tr>
				<td>
					<input class="formInput" type="text" style="width:492px" name="pagetitle" value="<?=$page->title?>" <?=($page->hardcodedProperty("title")?"disabled":"")?> onclick="PageSettings.OnChange()">
					<?php if($page->hardcodedProperty("title") ){ ?><img src="<?=$directory?>images/lock.png"><?php }?>
				</td>
			</tr>
		</table>

	</div>

	<!-- Layout Input -->
	<div id="layoutTabContent" style="display:none">
		The layout of a page determines how information is displayed.
		<input type="hidden" id="layoutid" name="layoutid" value="<?=$page->layout->id?>">
		<div id="layoutContainer">
			<?php foreach($layouts as $layout){ ?>
			<div class="<?=($page->layout->id==$layout->id && $page->layout->useTemplate==false?"layoutImageSelected":"layoutImage")?>" <?php if(!$page->hardcodedProperty("layout")){?>onclick="PageSettings.SelectLayout(this,'<?=$layout->id?>')"<?php } ?>>
				<div class="layoutName"><span><?=$layout->name?><?php if($page->hardcodedProperty("layout") && $page->layout->id==$layout->id && $page->layout->useTemplate==false){ ?> <img src="<?=$directory?>images/lock.png"><?php }?></span></div>
			  	<img class="previewImage" id="layout_<?=$layout->id?>" src="<?=$layout->getLayoutSample()?>" >
			</div>
			<?php } ?>

			<div class="<?=($page->layout->useTemplate==true?"layoutImageSelected":"layoutImage")?>" <?php if(!$page->hardcodedProperty("layout")){?>onclick="PageSettings.SelectLayout(this,'0')"<?php } ?>>
				<div class="layoutName"><span>Default<?php if($page->hardcodedProperty("layout") && $page->layout->useTemplate==true){ ?> <img src="<?=$directory?>images/lock.png"><?php }?></span></div>
			  	<img class="previewImage" id="layout_0" src="<?=$theme->getAbsCommonDirectory()?>layouts/samples/default.gif" >
			</div>
		</div>
		<div style="clear:both"></div>
	</div>

	<!-- Permissions Input -->
	<div id="permissionsTabContent" style="display:none">
		Who should be able to view this page?
		<table>
			<tr>
				<td>
					<input type="checkbox" name="permissions_everyone" id="permissions_everyone" value="everyone" style="border:none;vertical-align:middle"
					 <?=(!$securePage->valid()?"checked":"")?> onclick="Util.Toggle('accessCheckboxes');PageSettings.OnChange()">
					<label style="cursor:pointer;vertical-align:middle" for="permissions_everyone" class="formLabel">Everyone</label> <br />
					<div id="accessCheckboxes"  style="display:<?=(!$securePage->valid()?"none":"block")?>">
					<?php foreach($userGroups as $userGroup){ ?>
					<input type="checkbox" name="permissions[]" value="<?=$userGroup->id?>" id="usergroup_<?=$userGroup->id?>"  style="border:none;vertical-align:middle"
					<?=($securePage->groupHasAccess($userGroup->id)?"checked":"")?>  onclick="PageSettings.OnChange()" >
					<label style="cursor:pointer;vertical-align:middle" for="usergroup_<?=$userGroup->id?>"  class="formLabel"><?=$userGroup->name?></label> <br />
					<?php } ?>
					</div>
				</td>
			</tr>
		</table>

	</div>

	<!-- Templates Input -->
	<div id="templateTabContent" style="display:none">
		Should this page inherit content from another page?
		<table class="left" width=210>
		<tr>
			<td>
				<label style="cursor:pointer;vertical-align:middle" class="formLabel" for="useTemplate">use template</label>
				<input type="checkbox" id="useTemplate" name="useTemplate" style="border:none;vertical-align:middle;" <?=($page->useTemplate?"checked":"")?>
				<?=($page->hardcodedProperty("useTemplate")?"disabled":"")?> onclick="Util.Toggle('templateDropdownRow');PageSettings.OnChange()">
			</td>
		</tr>
		<tr id="templateDropdownRow" <?=($page->useTemplate?"":"style='display:none'")?>>
			<td colspan=2>
				<select  class="formInput" id="templatepage" name="templatepage" style="width:200px"  <?=($page->hardcodedProperty("template")?"disabled":"")?>>
				<?php foreach($templates as $template){ ?>
				<option value="<?=$template->id?>" <?=((($page->useTemplate && $page->template->id==$template->id)||($page->template==$template->id))?"selected":"")?>><?=$template->title?></option>
				<?php } ?>
				</select>
				<?php if($page->hardcodedProperty("template") ){ ?><img src="<?=$directory?>images/lock.png"><?php }?>
			</td>
		</tr>
		</table>
	</div>
</div>


<div class="clear"></div>

</form>
<br />
<br />
