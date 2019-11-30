/*=====================================================
NavEdit
Javascript code for the navigation part editor. Handles
the ajax calls for creating, editing, and deleting links
as well as displaying a preview to the user.
======================================================*/

var NavEdit = new (function() {
	//the id of the navpart (differentiates this
	//nav part from others)
	this.partid = 0;
	//a count of the number of js messages that have been
	//displayed to the user. Used to generate unique ids for
	//new messages
	this.messageCount = 0;
	//The id of the link that is currently available in the
	//edit tab. If not link is available this is 0
	this.editingLink = 0;

	/*===================================
	Initializes the editor by providing it
	with the id of the nav part it is editing
	===================================*/
	this.Init = function(partid) {
		this.partid = partid;
	}

	/*===================================
	Displays the edit link tab
	===================================*/
	this.ShowEditLink = function() {
		if($("link_"+NavEdit.editingLink))  {
			$("link_"+NavEdit.editingLink).className = "selected";
		}
		$("newLinkTab").className = "back";
		$("editLinkTab").className = "selected";
		Util.Hide("newLink");
		Util.Show("editLink");
	}

	/*===================================
	Displays the new link tab
	===================================*/
	this.ShowNewLink = function() {
		if($("link_"+NavEdit.editingLink))  {
			$("link_"+NavEdit.editingLink).className = "";
		}
		$("newLinkTab").className = "selected";
		$("editLinkTab").className = "back";
		Util.Show("newLink");
		Util.Hide("editLink");
	}

	/*===================================
	Creates a new link by submitting the
	new link form.
	===================================*/
	this.CreateNewLink = function() {
		var title = $("linkTitle");
		var url = $("linkUrl");
		var type = $("linkType");
		Site.ShowLoading();
		$("editform").request({
  		onComplete: NavEdit.CreateNewLinkCallback
		});
	}

	/*===================================
	Invoked when the new link form has been
	submitted. Interprets the results and takes
	needed action (display error message or
	load the new part on the screen)
	===================================*/
	this.CreateNewLinkCallback = function(transport) {
		//hide loading box
		Site.HideLoading();

		//get our response and split it.
		//It will be broken up like: #|extradata
		//where # is 0 for failure and 1 for succcess
		var response = transport.responseText

		var split = response.split("|");
		if (split.length < 2 ) {
			NavEdit.ShowError("Could not send update to server... please try reloading the page.");
			return;
		}
		var result = split[0];
		var data = split[1];

		//failure (extra data is the error)
		if(result == "0") {
			NavEdit.ShowError(data);
		}
		//success
		else {
			NavEdit.ShowMessage("New Link Created");
			//add the new link to the preview
			NavEdit.AddLinkToPreview(data);
			//clear the current form content and move the cursor
			//back to the top, making it easier to add another link
			$("linkTitle").value = "";
			$("linkUrl").value = "";
			$("linkTitle").focus();
		}
	}

	/*===================================
	Adds a link with the given database id
	to the preview
	===================================*/
	this.AddLinkToPreview = function(linkid) {
		//first get the link data
		var data = 'a'+NavEdit.partid+'=getLink&linkid='+linkid;
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){
							//get the link data
							var json = transport.responseText.evalJSON();
							NavEdit.AddLinkToPreviewCallback(json);
						}
			});
	}

	/*===================================
	Callback for adding a link to the preview. Interprets the
	returned data and adds the link into the DOM
	===================================*/
	this.AddLinkToPreviewCallback = function(json) {
		var linkId = "link_"+json.id;

		//build the list item
		var overstring = "NavEdit.ShowButtons("+json.id+")";
		var outstring = "NavEdit.HideButtons("+json.id+")";
		var listItem = Builder.node('li', {id:linkId,onmouseover:overstring,onmouseout:outstring});

		//build the link tag
		var link = Builder.node('a', {href:"#"} );

		//build the button container
		var buttonContainer = Builder.node('div', {style:"display:none", id:"buttons_"+json.id} );

		//build the buttons
		var onEditClick = "NavEdit.EditLink("+json.id+")";
		var onDeleteClick = "NavEdit.DeleteLink("+json.id+")";
		var editImg = Builder.node('div', {className:"edit",title:"Edit",onClick:onEditClick});
		var deleteImg = Builder.node('div', {className:"delete",title:"Delete",onClick:onDeleteClick});

		//build the title
		var title = Builder.node('div', {id:"linkTitle"+json.id,className:"handle"}, json.name);

		//place the buttons into their container
		buttonContainer.appendChild(deleteImg);
		buttonContainer.appendChild(editImg);

		//place everything into the link
		link.appendChild(buttonContainer);
		link.appendChild(title);

		//place the link into the list item
		listItem.appendChild(link);

		//append it to the page
		$("navPreviewList").appendChild(listItem);

		//make the list draggable again
		initNav();

	}

	/*===================================
	Invoked when the edit link button is clicked next to a link
	Loads that links content into the edit link tab.
	===================================*/
	this.EditLink = function(linkid) {
		NavEdit.ShowEditLink();
		$("editHelp").hide();

		if(NavEdit.editingLink == linkid )
			return;

		if($("link_"+NavEdit.editingLink))
			$("link_"+NavEdit.editingLink).className = "";
		$("link_"+linkid).className = "selected";

		$("editLoading").show();
		$("editContent").hide();

		var data = 'a'+NavEdit.partid+'=getLink&linkid='+linkid;
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){
							var json = transport.responseText.evalJSON();
							//NavEdit.EditLinkCallback(json);
							$("editLoading").hide();
							NavEdit.editingLink = json.id;
							$("editLinkId").value = json.id;
							$("editLinkTitle").value = json.name;
							$("editLinkUrl").value = json.url;
							$("editLinkType").selectedIndex = json.type-1;
							//$("editLinkPermission").value = json.permission;

							//make sure the given option is selected.
							var permission = $("editLinkPermission");
							for ( var i = 0 ; i < permission.options.length ; i++ ) {
								if (permission.options[i].value == json.permission) {
									permission.options[i].selected = true;
									break;
								}
							}
							//make sure the correct groups are selected
							var groups = document.forms['editLinkForm'].elements['editLinkUserGroups[]'];
							//iterate through each of the available checkboxes
							var foundCount = 0;
							for ( var i = 0 ; i < groups.length ; i++ ) {
								var foundMatch = false;
								//check to see if this checkbox has an entry in the
								//set of groups this link is visible too
								for ( var j = 0 ; j < json.userGroups.length ; j++ ) {
									if (json.userGroups[j] == groups[i].value) {
										//yes, found a match - check it now
										groups[i].checked = true;
										//if 'everyone' wasn't the only group selected, make sure
										//we unhide the other checkboxes
										if (groups[i].value != "everyone") {
											Util.Show("EditAccessCheckboxes");
										}
										//iterate our count
										foundCount++;
										foundMatch = true;
										break;
									}
								}
								//if we found no match for this checkbox, make sure it is unchecked
								if (!foundMatch) {
									groups[i].checked = false;
								}
							}
							//if we found no matches for any of the checkboxes, assume
							//'everyone' is checked and hide the other options.
							if(foundCount == 0 ) {
								Util.Hide("EditAccessCheckboxes");
								groups[0].checked = true;
							}

							//show our new loaded data
							$("editContent").show();

						}
			});
	}

	/*===================================
	Saves any changes that were made to a link in
	the edit tab.
	===================================*/
	this.SaveEditChanges = function() {
		//submit the save changes form
		$("editLinkForm").request({
  			onComplete: NavEdit.SaveEditChangesCallback
			});
	}

	/*===================================
	Called when the edit link tab form has
	been submitted. Interprets results and displays
	messages to user
	===================================*/
	this.SaveEditChangesCallback = function(transport) {
		var linkid = NavEdit.editingLink;

		//get the result
		var split = transport.responseText.split("|");
		if (split.length < 2 ) {
			NavEdit.ShowError("The server did not response to the save request.");
			return;
		}
		var result = split[0];
		var data = split[1];

		//display message
		if(result == "0") {
			NavEdit.ShowError(data);
		}
		else {
			NavEdit.ShowMessage("Changes Saved");
			//update the link in the dom in case the user
			//changed the link name
			NavEdit.ReloadLink(linkid);
		}
	}

	/*===================================
	Reloads a link in the preview, making
	sure it has the latests correct name
	===================================*/
	this.ReloadLink = function(linkid) {
		//get the link
		var data = 'a'+NavEdit.partid+'=getLink&linkid='+linkid;
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){
							//put the latest name into the preview panel
							var json = transport.responseText.evalJSON();
							if( !$("linkTitle"+json.id))
								return;
							//write the new text to the dom
							//there is a bug in firefox here that prvents universaly
							//using innerHTML. Firefox would randomly wrap the content
							//set with innerHTML with a new <a></a> tag. Work around
							//is to use textContent
							if(typeof($("linkTitle"+json.id).textContent) != "undefined" )
								$("linkTitle"+json.id).textContent = json.name;
							else
								$("linkTitle"+json.id).innerHTML = json.name;


						}
			});
	}

	/*===================================
	Deletes a link with the given id from
	the database
	===================================*/
	this.DeleteLink = function(linkid) {

		if( !confirm('Delete link?') )
			return;

		if(NavEdit.editingLink == linkid ) {
			NavEdit.editingLink = 0;
			$("editHelp").show();
			$("editLoading").hide();
			$("editContent").hide();
			//ShowNewLink();
		}

		var data = "event"+NavEdit.partid+"=deleteLink&linkid="+linkid+"&ajaxpost=1";
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){
							var ok = transport.responseText;
							if( ok == "1" ) {
								NavEdit.ShowMessage("Link Deleted");
								Effect.Fade("link_"+linkid,{duration:.5});
								setTimeout(function(){NavEdit.RemoveLinkFromDOM(linkid);},1000);

							}
							else
							{
								NavEdit.ShowError("Error - link not deleted");
							}
						}
			});
	}

	/*===================================
	Removes the link with the given id from
	the DOM
	===================================*/
	this.RemoveLinkFromDOM = function(linkid) {
		if(!$("link_"+linkid))
			return;
		$("navPreviewList").removeChild($("link_"+linkid));
	}

	/*===================================
	Submits the create new link form if the
	given event is an enter event
	===================================*/
	this.NewIfEnter = function(event) {
		if( Util.IsEnterEvent(event) )
			NavEdit.CreateNewLink();
	}
	/*===================================
	Submits the create edit link form if the
	given event is an enter event
	===================================*/
	this.SaveIfEnter = function(event) {
		if( Util.IsEnterEvent(event) )
			NavEdit.SaveEditChanges();
	}

	this.SendOrderUpdate = function() {
		//create the data to post
		var data = Sortable.serialize("navPreviewList");
		data = "event"+NavEdit.partid+"=editOrder&ajaxpost=1&" + data;

		//send the new order
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){
							var ok = transport.responseText;
							if( ok =! "1" ) {
								NavEdit.ShowError("Error sending new order to server...");
							}
						}
			});

		//this.SendUpdate("updateAreaOrder","page="+page+"&area="+area+"&"+data,0);
	}

	/*===================================
	Shows an error message to the user. Note
	that a new div is created for every message
	shown
	===================================*/
	this.ShowError = function(error) {
		var idString = "navMessage"+this.messageCount++;
		var el = Builder.node('div', {className:'navEditError',id:idString,style:'display:none'}, error);
		$("navMessages").insertBefore(el, $("navMessages").firstChild);
		Util.Flash(idString,5000,true);
	}

	/*===================================
	Shows an message to the user. Note
	that a new div is created for every message
	shown
	===================================*/
	this.ShowMessage = function(message) {
		var idString = "navMessage"+this.messageCount++;
		var el = Builder.node('div', {className:'navEditMessage',id:idString,style:'display:none'}, message);
		$("navMessages").insertBefore(el, $("navMessages").firstChild);
		Util.Flash(idString,5000,true);
	}

	/*===================================
	Shows the edit buttons for a particular
	item
	===================================*/
	this.ShowButtons = function(linkid) {
		Util.Show('buttons_'+linkid);
	}

	/*===================================
	Hides the edit buttons for a particular
	item
	===================================*/
	this.HideButtons = function(linkid) {
		Util.Hide('buttons_'+linkid);
	}

})();