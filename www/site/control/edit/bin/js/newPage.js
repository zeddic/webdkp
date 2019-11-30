/*=====================================================
NewPage
Javascript code for the new page creator / wizard
======================================================*/

var NewPage = new (function() {

	this.typingPath = false;	//true if the user is currently typing a path
	this.waitingForCheck = false;	//true if js is waiting for the user to stop typing the path so it can do a check
	/*=====================================================
	Shows the tab with the given name
	======================================================*/
	this.Init = function() {
		this.CheckIfPathTaken();
	}

	/*=====================================================
	Sends an ajax request to see if the given path is vailable
	======================================================*/
	this.CheckIfPathTaken = function () {
		var path = escape($("path").value);

		//create the data to post
		//var data = Sortable.serialize("navPreviewList");
		data = "ajax=pathInUse&";
		data += "path="+path;


		//Util.Hide("pathAvailableMessage");

		//send the new order
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: NewPage.CheckIfPathTakenCallback
			});

		//this.SendUpdate("updateAreaOrder","page="+page+"&area="+area+"&"+data,0);
	}

	/*=====================================================
	Callback for path check. Will display a box telling the
	user if the path they entered is valid or not
	======================================================*/
	this.CheckIfPathTakenCallback = function(transport) {

		var ok = transport.responseText;
		if ( ok == "1") {
			$("pathAvailableMessage").innerHTML = "This path is already taken";
			$("pathAvailableMessage").className = "errorMessage";
		}
		else {
			$("pathAvailableMessage").innerHTML = "This path is available";
			$("pathAvailableMessage").className = "message";
		}

		Util.Show("pathAvailableMessage");
	}
	/*=====================================================
	Monitor the user typing a new path. If they don't type
	for more than 2 seconds after starting to type, we
	can go ahead and do a path check for them
	======================================================*/
	this.OnPathKeyPress = function()
	{
		this.typingPath = true;
		Util.Hide("pathAvailableMessage");
		if(!this.waitingForCheck) {
			this.waitingForCheck = true;
			this.typingPath = false;
			setTimeout("NewPage.CheckIfTyping()",1000);
		}
	}
	/*=====================================================
	Monitor the user typing a new path. Have they finished
	typing now?
	======================================================*/
	this.CheckIfTyping = function()
	{
		if(NewPage.typingPath ) {
			//still typing... wait again
			NewPage.typingPath = false;
			setTimeout("NewPage.CheckIfTyping()",1000);
		}
		else {
			//they are done typing, lets check the path
			NewPage.typingPath = false;
			NewPage.waitingForCheck = false;
			NewPage.CheckIfPathTaken();
		}
	}

	/*=====================================================
	Shows the tab with the given name
	======================================================*/
	this.ShowTab = function(tabName, toShow) {
		$("TabName").className = ( tabName == "Name" ? "selected" : "back" );
		$("TabLayout").className = ( tabName == "Layout" ? "selected" : "back" );
		$("TabPermissions").className = ( tabName == "Permissions" ? "selected" : "back" );
		$("TabTemplate").className = ( tabName == "Template" ? "selected" : "back" );

		for( var i = 1 ; i <= 5 ; i++ ) {
			var contentBlock = "section" + i;
			if($(contentBlock) ){
				Util.Hide(contentBlock);
			}
		}
		if($(toShow)){
			Util.Show(toShow);
		}
	}

	/*===================================
	Selects a given image for the background
	===================================*/
	this.SelectLayout = function(element, id) {

		$("layoutid").value = id;

		var container = $("layoutContainer");
		for ( var i = 0 ; i < container.childNodes.length ; i++ ) {
			var node = container.childNodes[i];
			if ( typeof(node.className) != "undefined" && node.className == "layoutImageSelected") {
				node.className = "layoutImage";
			}
		}

		element.className = "layoutImageSelected";
		//alert($("layoutContainer").children);
	}

	/*===================================
	Attempts to create the page via an ajax call
	===================================*/
	this.CreatePage = function() {
		$("createPageButton").value = "Creating Page...";
		$("createPageButton").disable();

		$("newpage").request({
  			onComplete: NewPage.CreatePageCallback
			});
	}


	/*===================================
	Callback for the create page call
	===================================*/
	this.CreatePageCallback = function(transport) {
		$("createPageButton").enable();
		$("createPageButton").value = "Create Page";


		var result = transport.responseText;


		var json = result.evalJSON();

		if (json[0] == 1) {
			var path = json[1];
			var fullPath = Site.SiteRoot + path;

			var link = "<a href='"+fullPath+"'>"+path+"</a>";
			$("createdPage").innerHTML = link;

			$("viewPageNowButton").onclick = function() {	document.location=fullPath; };

			Util.Hide("createWizard");
			Util.Show("finish");

		}
		else
		{
			$("createPageError").innerHTML = json[1];
			//Util.Show("createPageError");

			//right now the only error is the path error. We'll just take
			//them to the name tab and point out the error there
			NewPage.ShowTab("Name","section1");
			$("pathAvailableMessage").style.fontWeight = "bold";
			//make sure they get the picture...
			Effect.Pulsate("pathAvailableMessage");
		}

	}

})();