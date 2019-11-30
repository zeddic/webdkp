/*=====================================================
ImageNote
Javascript code for editing page settings
======================================================*/
var PageSettings = new (function() {
	//the id of the image part (differentiates this
	//nav part from others)
	this.pageid = 0;

	/*===================================
	Initializes the editor by providing it
	with the id of the nav part it is editing
	===================================*/
	this.Init = function(pageid) {
		this.pageid = pageid;
	}

	/*===================================
	Displays the text tab
	===================================*/
	this.ShowGeneralTab = function() {
		$("layoutTab").className = "back";
		$("generalTab").className = "selected";
		$("permissionsTab").className = "back";
		$("templateTab").className = "back";

		Util.Hide("layoutTabContent");
		Util.Show("generalTabContent");
		Util.Hide("permissionsTabContent");
		Util.Hide("templateTabContent");
	}

	/*===================================
	Displays the background tab
	===================================*/
	this.ShowLayoutTab = function() {
		$("layoutTab").className = "selected";
		$("generalTab").className = "back";
		$("permissionsTab").className = "back";
		$("templateTab").className = "back";

		Util.Show("layoutTabContent");
		Util.Hide("generalTabContent");
		Util.Hide("permissionsTabContent");
		Util.Hide("templateTabContent");
	}

	/*===================================
	Displays the background tab
	===================================*/
	this.ShowPermissionsTab = function() {
		$("layoutTab").className = "back";
		$("generalTab").className = "back";
		$("permissionsTab").className = "selected";
		$("templateTab").className = "back";

		Util.Hide("layoutTabContent");
		Util.Hide("generalTabContent");
		Util.Show("permissionsTabContent");
		Util.Hide("templateTabContent");
	}

	/*===================================
	Displays the background tab
	===================================*/
	this.ShowTemplateTab = function() {
		$("layoutTab").className = "back";
		$("generalTab").className = "back";
		$("permissionsTab").className = "back";
		$("templateTab").className = "selected";

		Util.Hide("layoutTabContent");
		Util.Hide("generalTabContent");
		Util.Hide("permissionsTabContent");
		Util.Show("templateTabContent");
	}

	/*===================================
	Selects a given image for the background
	===================================*/
	this.SelectLayout = function(element, id) {

		$("layoutid").value = id;
		this.OnChange();

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
	Sets a dirty bit when something has been changed,
	enabling the 'save changes' button
	===================================*/
	this.OnChange = function() {
		$("saveChangesButton").enable();
		$("saveChangesButton").value = "Save Changes";
	}

	/*===================================
	Saves changes via an ajax call
	===================================*/
	this.SaveChanges = function() {
		$("pageSettingsForm").request({
  			onComplete: PageSettings.SaveChangesCallback
			});

	}
	/*===================================
	Callback for save changes
	===================================*/
	this.SaveChangesCallback = function() {
		$("saveChangesButton").value = "Saved";
		$("saveChangesButton").disable();
	}
})();