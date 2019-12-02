<div style="font-family:verdana;font-size:10pt">
<a href="<?=$PHP_SELF?>?editpage=1">Back</a>
<br />
The navigation part allows you to create navigational links to different areas
of the site. This editor allows you to set the links and order of links that should
appear. Arrange the link order by dragging the links in the preview.
Setting a value for either "Permission" or "User Groups" allows you to limit the visibility of
a link to either users with given permission, or users in a given group.
</div>
<br />

<div id="navpartlist" style="width:230px;float:left;" >
	<div id="navEditPreview" style="font-family:verdana;font-size:12pt;font-weight:bold;padding-top:4px">Preview</div>
	<div class="navLine"></div>
		<!-- <ul id="navPreviewList">
	<?php foreach($nav->list as $link){ ?>
		<li id="link_<?=$link->id?>">
			<div class="delete" title="Delete" onclick="NavEdit.DeleteLink(<?=$link->id?>)"></div>
			<div class="edit" title="Edit" onclick="NavEdit.EditLink(<?=$link->id?>)"></div>
			<div id="linkTitle<?=$link->id?>" class="handle"><?=$link->name?></div>
		</li>
		<?php } ?>
	</ul> -->


	<div class="navigationContainer">
	<ul class="navigationList" id="navPreviewList">
		<?php foreach($nav->list as $link){ ?>
		<li id="link_<?=$link->id?>" onmouseover="NavEdit.ShowButtons(<?=$link->id?>)" onmouseout="NavEdit.HideButtons(<?=$link->id?>)">
			<a href="#" >
			<div style="display:none" id="buttons_<?=$link->id?>">
			<div class="delete" title="Delete" onclick="NavEdit.DeleteLink(<?=$link->id?>)"></div>
			<div class="edit" title="Edit" onclick="NavEdit.EditLink(<?=$link->id?>)"></div>
			</div>
			<div id="linkTitle<?=$link->id?>" class="handle"><?=$link->name?></div>
			</a>
		</li>
		<?php } ?>
	</ul>
	</div>

</div>

<div id="navEditContainer" style="width:300px;float:left;margin-left:50px;">
	<div class="editTabs">
		<ul>
			<li class="selected" id="newLinkTab"><a href="javascript:NavEdit.ShowNewLink()"><span>New Link</span></a></li>
			<li class="back" id="editLinkTab"><a href="javascript:NavEdit.ShowEditLink()"><span>Edit Link</span></a></li>
		</ul>
		<div class="editTabsUnderline"></div>
	</div>

	<div id="navEditerContent">

		<!-- New Link Panel -->
		<div id="newLink">
			<form  action="<?=$PHP_SELF?>?ajaxpost=1" method="post" id="editform" style="display:inline">
			<input type="hidden" name="event<?=$iid?>" value="newLink">

			<table>
			<tr>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td width=100>Title</td>
				<td><input type="text" id="linkTitle" name="linkTitle" style="width:200px" onkeypress="NavEdit.NewIfEnter(event)"></td>
			</tr>
			<tr>
				<td>Url</td>
				<td><input type="text" id="linkUrl" name="linkUrl" style="width:200px"  onkeypress="NavEdit.NewIfEnter(event)"></td>
			</tr>
			<tr>
				<td>Type</td>
				<td>
					<select id="linkType" name="linkType" style="width:208px"  onkeypress="NavEdit.NewIfEnter(event)">
						<option value="absolute">Absolute</option>
						<option value="relativeSite">Relative to Site</option>
						<option value="relativePage">Relative to Page</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Permission</td>
				<td>
					<select id="linkPermission" name="linkPermission" style="width:208px" onkeypress="NavEdit.NewIfEnter(event)">
						<option value="0">No Permission Needed</option>
						<?php foreach($permissions as $permission){ ?>
						<option value="<?=$permission->id?>"><?=$permission->name?></a>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top">User Groups</td>
				<td style="vertical-align:top">
					<input checked type="checkbox" name="linkUserGroups[]" id="usergroups_everyone" value="everyone" onclick="Util.Toggle('accessCheckboxes')" class="groupsCheckbox">
					<label style="cursor:pointer" for="usergroups_everyone" class="groupsLabel">Everyone</label> <br />
					<div id="accessCheckboxes" style="display:none">
					<?php foreach($userGroups as $userGroup){ ?>
					<input type="checkbox" name="linkUserGroups[]" value="<?=$userGroup->id?>" id="usergroup_<?=$userGroup->id?>" class="groupsCheckbox">
					<label style="cursor:pointer" for="usergroup_<?=$userGroup->id?>"  class="groupsLabel"><?=$userGroup->name?></label> <br />
					<?php } ?>
				</td>
			</tr>
			</table>
			<br />
			<input style="font-size:105%" type="button" value="Add Link" onclick="NavEdit.CreateNewLink()">
			</form>
		</div>

		<!-- Edit link panel -->
		<div id="editLink" style="display:none">
			<div id="editHelp">Select a link from the left to edit</div>
			<div style="text-align:center;display:none" id="editLoading" ><img src="<?=$directory?>images/edit/loading.gif" /></div>
			<div id="editContent" style="display:none">
				<form  action="<?=$PHP_SELF?>?ajaxpost=1" method="post" id="editLinkForm" style="display:inline">
				<input type="hidden" name="event<?=$iid?>" value="editLink">
				<input type="hidden" name="linkid" id="editLinkId" >

				<table>
				<tr>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td width=100>Title</td>
					<td><input type="text" id="editLinkTitle" name="linkTitle" style="width:200px"  onkeypress="NavEdit.SaveIfEnter(event)"></td>
				</tr>
				<tr>
					<td>Url</td>
					<td><input type="text" id="editLinkUrl" name="linkUrl" style="width:200px" onkeypress="NavEdit.SaveIfEnter(event)"></td>
				</tr>
				<tr>
					<td>Type</td>
					<td>
						<select id="editLinkType" name="linkType" style="width:208px" onkeypress="NavEdit.SaveIfEnter(event)">
							<option value="absolute">Absolute</option>
							<option value="relativeSite">Relative to Site</option>
							<option value="relativePage">Relative to Page</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Permission</td>
					<td>
						<select id="editLinkPermission" name="editLinkPermission" style="width:208px" onkeypress="NavEdit.SaveIfEnter(event)">
							<option value="0">No Permission Needed</option>
							<?php foreach($permissions as $permission){ ?>
							<option value="<?=$permission->id?>"><?=$permission->name?></a>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top">User Groups</td>
					<td style="vertical-align:top">
						<input checked type="checkbox" name="editLinkUserGroups[]" id="editusergroups_everyone" value="everyone" onclick="Util.Toggle('EditAccessCheckboxes')" class="groupsCheckbox">
						<label style="cursor:pointer" for="editusergroups_everyone" class="groupsLabel">Everyone</label> <br />
						<div id="EditAccessCheckboxes" style="display:none">
						<?php foreach($userGroups as $userGroup){ ?>
						<input type="checkbox" name="editLinkUserGroups[]" value="<?=$userGroup->id?>" id="editusergroups_<?=$userGroup->id?>" class="groupsCheckbox">
						<label style="cursor:pointer" for="editusergroups_<?=$userGroup->id?>"  class="groupsLabel"><?=$userGroup->name?></label> <br />
						<?php } ?>
					</td>
				</tr>
				</table>
				<br />
				<input style="font-size:105%" type="button" value="Save Changes" onclick="NavEdit.SaveEditChanges()">
				<input style="font-size:105%" type="button" value="Cancel" onclick="NavEdit.ShowNewLink()">
				</form>
			</div>
		</div>
		<br />
		<div id="navMessages"></div>
	</div>
</div>

<div class="clear"></div>


 <script type="text/javascript">
 NavEdit.Init(<?=$iid?>);
 function initNav() {
 // <![CDATA[
    Sortable.create("navPreviewList",{
	 	onUpdate:NavEdit.SendOrderUpdate,handle:'handle',dropOnEmpty:true,constraint:false});
 // ]]>
 }
 initNav();
 </script>