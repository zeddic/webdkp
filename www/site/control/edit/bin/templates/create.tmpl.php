<br />

<div id="createWizard">

<form  action="<?=$PHP_SELF?>" method="post" name="newpage" id="newpage" style="display:inline">
<input type="hidden" name="ajax" value="createNewPage">

<input id="createPageButton" style="width:150px" class="mediumButton" type="button" value="Create Page" onclick="NewPage.CreatePage()" >
<input class="mediumButton"  type="button" value="Cancel" onclick="history.go(-1)" >
<br />
<div class="errorMessage" id="createPageError" style="width:470px;display:none">Error Message</div>
<br />

<div style="width:500px">
	<div class="editTabs">
		<ul>
			<li class="selected" id="TabName"><a onclick="NewPage.ShowTab('Name','section1')"><span>General</span></a></li>
			<li class="back" id="TabLayout"><a onclick="NewPage.ShowTab('Layout','section2')"><span>Layout</span></a></li>
			<li class="back" id="TabPermissions"><a onclick="NewPage.ShowTab('Permissions','section3')"><span>Permissions</span></a></li>
			<li class="back" id="TabTemplate"><a onclick="NewPage.ShowTab('Template','section4')"><span>Template</span></a></li>
		</ul>
		<div class="editTabsUnderline"></div>
	</div>

	<!-- PAGE NAME -->
	<div id="section1">
		<div class="noticeMessage">
			Enter a title and a path for the page.
		</div>

		<br />

		<table>
			<tr>
				<td class="formLabel">title</td>
			</tr>
			<tr>
				<td><input class="formInput" type="text" style="width:492px" name="pagetitle" value="<?=$pagetitle?>"></td>
			</tr>
			<tr>
				<td class="formLabel">path</td>
			</tr>
			<tr>
				<td><input class="formInput" id="path" type="text" style="width:492px" name="pagename" value="<?=$pagename?>" onkeypress="NewPage.OnPathKeyPress()"></td>
			</tr>
			<tr>
				<td><div class="message" id="pathAvailableMessage" style="display:none"></div></td>
			</tr>
		</table>
	</div>
	<!-- PAGE LAYOUT -->
	<div id="section2"  style="display:none">
		<div class="noticeMessage">
			 Select a layout for your page. The layout will determine how information is displayed.
		</div>
		<br />

		<input type="hidden" id="layoutid" name="layoutid" value="<?=$page->layout->id?>">
		<div id="layoutContainer">
			<?php foreach($layouts as $layout){ ?>
			<div class="<?=($layoutid==$layout->id?"layoutImageSelected":"layoutImage")?>" onclick="NewPage.SelectLayout(this,'<?=$layout->id?>')">
				<div class="layoutName"><span><?=$layout->name?></span></div>
			  	<img class="previewImage" id="layout_<?=$layout->id?>" src="<?=$layout->getLayoutSample()?>" >
			</div>
			<?php } ?>

			<div class="<?=($layoutid==""?"layoutImageSelected":"layoutImage")?>" onclick="NewPage.SelectLayout(this,'0')">
				<div class="layoutName"><span>Default</span></div>
			  	<img class="previewImage" id="layout_0" src="<?=$theme->getAbsCommonDirectory()?>layouts/samples/default.gif" >
			</div>
		</div>
		<div style="clear:both"></div>

	</div>

	<!-- PAGE PERMISSIONS -->
	<div id="section3" style="display:none">
		<div class="noticeMessage">
			Select the users that should be able to view this page.
			<br />
		</div>
		<br />

		<table class="left" width=300>
			<tr>
				<td>
					<input type="checkbox" name="permissions[]" id="permissions_everyone" value="everyone" style="border:none;vertical-align:middle" class="formInput"
					<?=(in_array("everyone",$permissions)?"checked":"")?> onclick="Util.Toggle('accessCheckboxes')">
					<label style="cursor:pointer;vertical-align:middle" for="permissions_everyone" class="formLabel">Everyone</label> <br />
					<div id="accessCheckboxes" style="display:<?=(in_array("everyone",$permissions)?"none":"block")?>">
					<?php foreach($userGroups as $userGroup){ ?>
					<input type="checkbox" name="permissions[]" value="<?=$userGroup->id?>" id="usergroup_<?=$userGroup->id?>" class="formInput" style="border:none;vertical-align:middle"
					 <?=(in_array($userGroup->id,$permissions) || in_array("everyone",$permissions)?"checked":"")?> >
					<label style="cursor:pointer;vertical-align:middle" for="usergroup_<?=$userGroup->id?>"  class="formLabel"><?=$userGroup->name?></label> <br />
					<?php } ?>
					</div>
				</td>
			</tr>
		</table>

	</div>

	<!-- PAGE TEMPLATES -->
	<div id="section4" style="display:none">
		<div class="noticeMessage">
			You can select a template for the page. All the contents from the template will be displayed
			on the new page as well.
			<br />
		</div>
		<br />

		<table class="left" width=210>
		<tr>
			<td>
				<label style="cursor:pointer;vertical-align:middle" class="formLabel" for="useTemplate">use template</label>
				<input type="checkbox" id="useTemplate" name="useTemplate" style="border:none;vertical-align:middle;" checked onclick="Util.Toggle('templateDropdownRow')"></td>
			</td>
		</tr>
		<tr id="templateDropdownRow" >
			<td colspan=2>
				<select  class="formInput" id="templatepage" name="template" style="width:200px" >
				<?php foreach($templates as $template){ ?>
				<option value="<?=$template->id?>" <?=($template->title=="Master Template"?"selected":"")?>><?=$template->title?></option>
				<?php } ?>
				</select>
			</td>
		</tr>
		</table>

	</div>

</div>
</form>
</div>

<div id="finish" style="display:none">
<div class="message">Success, a new page has been created at <span id="createdPage"></span></div>
<br />

<?php if($from == "webpages") { ?>
<input class="mediumButton"  type="button" value="Return to Webpages" onclick="document.location='<?=$returnTo?>'" >
<?php } ?>
<input class="mediumButton" id="viewPageNowButton" type="button" value="View Page Now" onclick="document.location='<?=$returntoUrl?>'" >


</div>


 <script type="text/javascript">
 NewPage.Init();
 </script>







