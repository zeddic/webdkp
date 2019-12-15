/*==============================================
Main site class. Provides access to common tasks
related to the site.
===============================================*/

Site = new (function() {
  //The root path of the site, such as http://www.site.com/
  this.SiteRoot = "";
  //The url of the current page. Includes site root
  this.Url = "";
  //the id of the current page (if applicable)
  this.PageId = "";
  /*==============================================
	Accessors
	===============================================*/
  this.SetSiteRoot = function(siteRoot) {
    this.SiteRoot = siteRoot;
  };
  this.SetUrl = function(url) {
    this.url = url;
  };
  this.SetPageId = function(pageid) {
    this.PageId = pageid;
  };
  this.Init = function(siteRoot, url, pageid) {
    this.SiteRoot = siteRoot;
    this.Url = url;
    this.PageId = pageid;
  };

  /*==============================================
	Show the loading box in the upper right hand
	corner of the page.
	===============================================*/
  this.ShowLoading = function() {
    //if($("system_loading_place")){
    //	var pos = FindPos($("system_loading_place"));
    //	$("system_loading").styl e.top = pos[1]+"px";
    //	$("system_loading").style.left = pos[0]+"px";
    //}
    $("SiteLoading").show();
  };
  /*==============================================
	Hides the loading box in the upper right hand
	corner of the page
	===============================================*/
  this.HideLoading = function() {
    $("SiteLoading").hide();
  };
  /*==============================================
	Shows a message notification in the same form as
	loading text. Accepts the name of the message to
	display
	===============================================*/
  this.ShowMessage = function(message) {
    if (typeof message != "undefined") {
      $("SiteMessage").innerHTML = message;
    }
    $("SiteMessage").show();
  };
  /*==============================================
	Hides the message displayed by Site.ShowMessage
	===============================================*/
  this.HideMessage = function() {
    $("SiteMessage").hide();
  };

  /*==============================================
	Shows a message using Site.ShowMessage but will
	auto hide the message after the given time.
	Time measured in milliseconds. If no time
	specified, will hide the message in 5 seconds.
	===============================================*/
  this.FlashMessage = function(message, time) {
    this.ShowMessage(message);
    if (typeof time == "undefined") time = 5000;
    setTimeout(function() {
      Site.HideMessage();
    }, time);
  };
  /*==============================================
	Reloads the content of a given part on the current page.
	The returned data will be the part in complete rendered
	html. This will only load a part on the CURRENT PAGE
	User attentication still applies. If you don't have access
	to the parts page, the part will not be returned.
	Accepts 4 parameters.
	Partid - the id of the part on the page to get. Can also be
			 the parts title
	callback - a javascript function to invoke once the parts
			   data has been recieved. Must accept two parameters:
			   transport and partid
	customdata - (optional) a query string with data to send to
				 the part.
	editpage   - (optional) if set to 1, the part will be returned
				 as if it is in edit page mode
	===============================================*/
  this.GetPart = function(partid, callback, customdata, editpage) {
    if (typeof customdata == "undefined") {
      customdata = "";
    }

    if (typeof editpage == "undefined") {
      editpage = 0;
    }
    var data = "getPart=" + partid + "&editpage=" + editpage + "&" + customdata;

    Site.Debug.Out("Sending request to " + Site.SiteRoot + Site.Url);

    new Ajax.Request(Site.SiteRoot + Site.Url, {
      method: "post",
      parameters: data,
      onSuccess: function(transport) {
        Site.Debug.Out("Callback");
        callback(transport, partid);
      }
    });
  };

  /*==============================================
	Inner debug class handles sending debug output
	===============================================*/
  this.Debug = new (function() {
    /*==============================================
		Puts the message in a debug output page
		===============================================*/
    this.Out = function(message) {
      //alert('here');
      if ($("DebugOut")) $("DebugOut").innerHTML += message + "<br />";
    };
  })();
})();

/*==============================================
STATIC Utility class provides access to common
tasks
===============================================*/
var Util = new (function() {
  this.Test = function(message) {
    alert(message);
  };

  /*==============================================
	Shows the element with the given id
	===============================================*/
  this.Show = function(id) {
    if ($(id)) {
      $(id).style.display = "block";
    }
  };
  /*==============================================
	Hides the element with the given id
	===============================================*/
  this.Hide = function(id) {
    if ($(id)) {
      $(id).style.display = "none";
    }
  };
  /*==============================================
	Toggles the element with the given id between Hide or Show
	===============================================*/
  this.Toggle = function(id) {
    document.getElementById(id).style.display =
      document.getElementById(id).style.display == "none" ? "block" : "none";
  };

  /*==============================================
	Toggles the element with the given id between Hide or Show
	===============================================*/
  this.Toggle = function(id, focusAfter) {
    document.getElementById(id).style.display =
      document.getElementById(id).style.display == "none" ? "block" : "none";
    Util.Focus(focusAfter);
  };

  /*==============================================
	Toggles the visiblity of the given id by using a slide animation
	Duration (in seconds) for the slide animation to complete
	FocusAfter - (optional)
				 The name of an element that should be focused on after
				 the animation is complete (only used when appearing)
	===============================================*/
  this.SlideToggle = function(id, duration, focusAfter) {
    if (duration == "undefined") {
      duration = 0.5;
    }
    if (document.getElementById(id).style.display == "none") {
      if ($(focusAfter)) {
        Effect.SlideDown(id, {
          duration: duration,
          afterFinish: function() {
            Util.Focus(focusAfter);
          }
        });
      } else {
        Effect.SlideDown(id, { duration: duration });
      }
    } else {
      Effect.SlideUp(id, { duration: duration });
    }
  };

  /*==============================================
	Focuses on the given element
	===============================================*/
  this.Focus = function(id) {
    if (!$(id)) {
      return;
    }
    $(id).focus();
  };

  /*==============================================
	Makes a specified element visible for a certain
	amount of time
	===============================================*/
  this.Flash = function(id, time, fadein) {
    if (typeof time == "undefined") {
      time = 1000;
    }
    if (typeof fadein == "undefined") {
      fadein = false;
    }
    if ($(id)) {
      if (fadein) Effect.Appear(id);
      else $(id).show();
      setTimeout("Util.Fade('" + id + "',1)", time);
    }
  };

  /*==============================================
	Fades a given element
	===============================================*/
  this.Fade = function(id, time) {
    if (typeof time == "undefined") {
      time = 1;
    }
    if ($(id)) {
      Effect.Fade(id, { duration: time });
    }
  };

  /*=========================================================================
	Converts a px string (such as "50px") to an int.
	==========================================================================*/
  this.ConvertPxToInt = function(string) {
    return parseInt(string.substring(0, string.indexOf("px")));
  };
  /*=========================================================================
	Converts a string to an int
	==========================================================================*/
  this.ConvertToInt = function(string) {
    return parseInt(string);
  };
  /*=========================================================================
	Submits the given form if the passed event is an enter (key return) event
	on the keyboard. Allows forms to be submitted when the user presses enter,
	without having to select the submit button manually.
	Use as follows: <input type='text' onkeypress="Util.SubmitIfEnter('form',event)">
	NOTE: Do not use for forms with only a single input box. This will cause them
	to be submitted twice
	==========================================================================*/
  this.SubmitIfEnter = function(formName, event) {
    if (this.IsEnterEvent(event)) {
      this.SubmitForm(formName);
    }
  };
  /*=========================================================================
	Submits the given form if the passed event is an enter (key return) event
	on the keyboard. Allows forms to be submitted when the user presses enter,
	without having to select the submit button manually.
	Use as follows: <input type='text' onkeypress="Util.SubmitIfEnter('form',event)">
	NOTE: Do not use for forms with only a single input box. This will cause them
	to be submitted twice
	==========================================================================*/
  this.SubmitIfEnter = function(formName, event, destination) {
    if (this.IsEnterEvent(event)) {
      this.SubmitForm(formName, destination);
    }
  };

  /*=========================================================================
	Returns true if the passed event represents an enter keypress event
	==========================================================================*/
  this.IsEnterEvent = function(event) {
    if (event.keyCode == 13) return true;
    if (window.event && window.event.keyCode == 13) return true;
  };
  /*=========================================================================
	Auto sets focus to the given input box on page load.
	==========================================================================*/
  this.SetAutoFocus = function(inputId) {
    if (document.getElementById(inputId)) {
      document.getElementById(inputId).focus();
    } else {
      var self = this;
      setTimeout(function() {
        self.SetAutoFocus(inputId);
      }, 100);
    }
  };
  /*=========================================================================
	Submits the given formname to the given url destination. Destination
	is optional. If not provided the form will just be submitted.
	==========================================================================*/
  this.SubmitForm = function(formName, destination) {
    //make sure form valid
    if (!document.forms[formName]) {
      return;
    }
    //change the destination if set
    if (destination) {
      document.forms[formName].action = destination;
    }
    //submit form
    document.forms[formName].submit();
  };
  /*=========================================================================
	Submits the given formname to the given url destination. Destination
	is optional. If not provided the form will just be submitted.
	==========================================================================*/
  this.Submit = function(formName, destination) {
    this.SubmitForm(formName, destination);
  };

  /*==============================================
	Checks to see if the given function string exists.
	If it does, it evaluates it. Used for running
	javascript that was dynamically loaded.
	Sample call: RunFunction("window.loadedFunction");
	===============================================*/
  this.RunFunction = function(funcString) {
    var temp = "if(" + funcString + "){" + funcString + "();}else{}";
    eval(temp);
  };

  /*==============================================
	Evaluates any javascript that is in the given html
	string. Use this to dynamically load javascript
	as needed. JS in html must be within the tags
	<script type="text/javascript">
	....
	...
	</script>
	===============================================*/
  this.RunJavascriptInHtml = function(htmlString) {
    var jsStartString = '<script type="text/javascript">';
    var jsEndString = "</script>";
    var jsStart = htmlString.indexOf(jsStartString);

    if (jsStart >= 0) {
      var jsEnd = htmlString.indexOf(jsEndString);

      if (jsEnd >= 0) {
        jsStart += jsStartString.length;
        var js = htmlString.slice(jsStart, jsEnd);
        eval(js);
      }
    }
  };

  /*==============================================
	Hooks a method to the windows onload event.
	This will NOT kill any other load events, but instead
	hook this one after any other events that are
	already loaded
	===============================================*/
  this.AddOnLoad = function(functionPointer) {
    var oldonload = window.onload;
    if (typeof oldonload == "function") {
      window.onload = function() {
        oldonload();
        functionPointer();
      };
    } else {
      functionPointer();
    }
  };

  /*==============================================
	Adds slashes to a string for special characters
	such as quotes (" ') - returns the slashed
	version of the string.
	===============================================*/
  this.AddSlashes = function(string) {
    string = string.replace(/\'/g, "\\'");
    string = string.replace(/\"/g, '\\"');
    string = string.replace(/\\/g, "\\\\");
    string = string.replace(/\0/g, "\\0");
    return string;
  };

  /*==============================================
	Strips slashes from a given string. Returns
	the form of the string with slashes removed.
	===============================================*/
  this.StripSlashes = function(string) {
    string = string.replace(/\\'/g, "'");
    string = string.replace(/\\"/g, '"');
    string = string.replace(/\\\\/g, "\\");
    string = string.replace(/\\0/g, "\0");
    return string;
  };

  /*===================================
	Inserts the given text into the given textbox element.
	Focus is placed immediatly after the text inserted.
	Text is inserted at the last place text was typed,
	or if the text box hasn't been edited yet, at the end
	of the text box.
	===================================*/
  this.InsertIntoTextbox = function(
    textBoxName,
    toInsert,
    insertPos,
    keepFocus
  ) {
    element = $(textBoxName);

    if (!element) return;

    var oldscroll = element.scrollTop; //So the scroll won't move after a tabbing

    if (typeof insertPos == "undefined") {
      insertPos = $("content").value.length;
    }

    if (typeof keepFocus == "undefined") {
      keepFocus = false;
    }

    //Check if we're in a firefox deal
    if (element.setSelectionRange) {
      var pos_to_leave_caret = insertPos + toInsert.length;
      //Put in the tab
      element.value =
        element.value.substring(0, insertPos) +
        toInsert +
        element.value.substring(insertPos, element.value.length);
      //There's no easy way to have the focus stay in the textarea, below seems to work though
      if (keepFocus) {
        setTimeout(
          "var t=$('" +
            textBoxName +
            "'); t.focus(); t.setSelectionRange(" +
            pos_to_leave_caret +
            ", " +
            pos_to_leave_caret +
            ");",
          0
        );
      }
    }
    //Handle IE
    else if (document.selection) {
      //element.focus();
      // IE code, pretty simple really
      var range = document.selection.createRange();
      //if we don't have focus to the element, we have to insert it the 'messy way'
      if (range.parentElement() != element) {
        element.innerHTML += toInsert;
      }
      //we have focus, we can insert text the good way
      else {
        range.text = toInsert;
        range.select();
      }
    }

    element.scrollTop = oldscroll; //put back the scroll

    return insertPos + toInsert.length;
  };
  /*==============================================
	Disables text selection within a given element
	===============================================*/
  this.DisableSelection = function(element) {
    element.onselectstart = function() {
      return false;
    };
    element.unselectable = "on";
    element.style.MozUserSelect = "none";
    element.style.cursor = "default";
  };

  /*==============================================
	Submits an Ajax request to the current page, invoking
	the given ajax handler and passing it the given data.
	OnSuccess and OnFailure are methods that will be invoked
	when the Ajax request works or fails.
	===============================================*/
  this.AjaxRequest = function(AjaxHandler, data, OnSuccess, OnFailure) {
    var parameters = "ajax=" + AjaxHandler + "&" + data;

    if (typeof OnFailure == "undefined") {
      new Ajax.Request(Site.SiteRoot + Site.Url, {
        method: "post",
        parameters: parameters,
        onSuccess: OnSuccess
      });
    } else {
      new Ajax.Request(Site.SiteRoot + Site.Url, {
        method: "post",
        parameters: parameters,
        onSuccess: OnSuccess,
        onFailure: OnFailure
      });
    }
  };
  /*==============================================
	Submits an Ajax request to the current page, invoking
	the given ajax handler and passing it the given data.
	OnSuccess and OnFailure are methods that will be invoked
	when the Ajax request works or fails.
	===============================================*/
  this.AjaxPartRequest = function(
    AjaxHandler,
    partid,
    data,
    OnSuccess,
    OnFailure
  ) {
    var parameters = "a" + partid + "=" + AjaxHandler + "&" + data;

    if (typeof OnFailure == "undefined") {
      new Ajax.Request(Site.SiteRoot + Site.Url, {
        method: "post",
        parameters: parameters,
        onSuccess: OnSuccess
      });
    } else {
      new Ajax.Request(Site.SiteRoot + Site.Url, {
        method: "post",
        parameters: parameters,
        onSuccess: OnSuccess,
        onFailure: OnFailure
      });
    }
  };
})();

/*==============================================
STATIC
PageEditor provides access to functions related
to editing a page. Used while in edit page mode.
All JS requests are verified on the receiving
end for appropriate permissions before any action
is taken
===============================================*/
var PageEditor = new (function() {
  this.AjaxHandler = "Edit/ajax";
  this.LibraryLoaded = false;
  /*==============================================
	Accessors
	===============================================*/

  /*==============================================
	Shows the Add part Page panel
	===============================================*/
  this.ShowPartLibrary = function() {
    //if we have already gotten the library once,
    //get the cached copy
    if (PageEditor.LibraryLoaded) {
      Effect.SlideDown("PartLibrary", { duration: 0.5 });
      return;
    }

    //Site.ShowLoading();
    new Ajax.Request(Site.SiteRoot + "edit/partlibrary", {
      method: "post",
      onSuccess: PageEditor.ShowPartLibraryCallback
    });
  };

  /*==============================================
	Callback for show part library method. Called
	once the library has been recieved via an ajax call.
	Displays the library.
	===============================================*/
  this.ShowPartLibraryCallback = function(transport) {
    $("PartLibrary").innerHTML = transport.responseText;
    //PageEditor.Setup();
    Effect.SlideDown("PartLibrary", { duration: 0.5 });
    PageEditor.LibraryLoaded = true;
  };

  /*==============================================
	Hides the part library
	===============================================*/
  this.HidePartLibrary = function(transport) {
    Effect.SlideUp("PartLibrary", { duration: 0.5 });
  };

  /*==============================================
	Shows a preview for a given part in the part library.
	This is the mouse over that appears to the right
	of the library as the user hovers over different
	options.
	Accepts two parameters:
	event - the mouse event sent as the user hovers over the choice
	content - the content to display in the hover
	===============================================*/
  this.ShowLibraryPartPreview = function(event, content) {
    var y = event.clientY - 10;
    var previewDiv = "PartLibraryDetail";
    $(previewDiv).style.top = y + "px";
    $(previewDiv).show();

    var previewDivContent = "PartLibraryDetailInner";
    $(previewDivContent).innerHTML = content;
  };
  /*==============================================
	Hides the preview for a part in the part library
	===============================================*/
  this.HideLibraryPartPreview = function(content) {
    var previewDiv = "PartLibraryDetail";
    $(previewDiv).hide();
  };

  /*==========================================================================
	Invoked when a specific page area is updated by a user dragging a part
	either to or from it. (Via scriptaculous). When it is called the area data
	is serialized into an array and sent back via ajax.
	===========================================================================*/
  this.AreaUpdate = function(area) {
    var page = Site.PageId;
    var areaListName = area + "_container";
    var data = Sortable.serialize(areaListName);
    this.SendUpdate(
      "updateAreaOrder",
      "page=" + page + "&area=" + area + "&" + data,
      0
    );
    //unhighlightAreas();
  };

  /*==============================================
	Sends a 1 way ajax update to the page editors
	ajax handler. Accepts the following parameters:
	Action - the name of the action hadler to invoke
	Data - data to send (var=value&var=value...)
	Partid - (optional) the part on the page that is
		     requesting the update. It will have its
		     update animation started while this
		     request is is process.
	===============================================*/
  this.SendUpdate = function(action, data, partid, callback) {
    var data = "action=" + action + "&" + data;
    var savingPart = "PartLoading" + partid;
    if ($(savingPart)) {
      $(savingPart).show();
    }

    new Ajax.Request(Site.SiteRoot + this.AjaxHandler, {
      method: "post",
      parameters: data,
      onSuccess: function(transport) {
        if ($(savingPart)) $(savingPart).hide();
        Site.Debug.Out(transport.responseText);
        if (callback != "undefined") callback(partid);
      }
    });
  };

  /*==========================================================================
	Updates the title of the part
	===========================================================================*/
  this.UpdateTitle = function(partid) {
    var title = $("EditPageBorderTitleInput" + partid).getValue();
    //Send via ajax
    PageEditor.SendUpdate(
      "updateTitle",
      "iid=" + partid + "&title=" + title,
      partid,
      PageEditor.ReloadPart
    );
    $("EditPageBorderTitle" + partid).innerHTML = title;
  };

  /*==========================================================================
	Updates the selected border for a part
	===========================================================================*/
  this.ChangeBorder = function(partid, borderid) {
    this.UnselectBorders(partid);
    this.SelectBorder(partid, borderid);
    //send ajax request
    PageEditor.SendUpdate(
      "updateBorder",
      "iid=" + partid + "&border=" + borderid,
      partid,
      PageEditor.ReloadPart
    );
  };

  /*==========================================================================
	Reloads the content for the given part via an ajax call
	===========================================================================*/
  this.ReloadPart = function(partid) {
    Site.GetPart(partid, PageEditor.DisplayPart, "", "0");
  };
  /*==========================================================================
	Callback for reload part. Echos the response to the screen
	===========================================================================*/
  this.DisplayPart = function(transport, partid) {
    var element = "EditPageSampleContent" + partid;
    if (!$(element)) return;

    $(element).innerHTML = transport.responseText;
  };

  /*==========================================================================
	Helper method - removes the border around and border sample that is choosen
	===========================================================================*/
  this.UnselectBorders = function(partid) {
    var borderSampleId;
    for (i = 0; i < 20; i++) {
      borderSampleId = "EditPageBorderSample" + partid + "_" + i;
      if ($(borderSampleId)) {
        $(borderSampleId).className = "borderSamplePadding";
      }
    }
  };
  /*==========================================================================
	Helper method - adds the dotted lines around the border style that
	was choosen for a part
	===========================================================================*/
  this.SelectBorder = function(partid, borderid) {
    var borderSampleId = "EditPageBorderSample" + partid + "_" + borderid;
    if ($(borderSampleId)) {
      $(borderSampleId).className = "borderSampleChoosen";
    }
  };

  /*==========================================================================
	Updates the value of a custom option
	===========================================================================*/
  this.UpdateCustomOption = function(partid, optionid) {
    var pageid = "EditPageCustomOption" + partid + "_" + optionid;
    var value = $(pageid).getValue();
    PageEditor.SendUpdate(
      "UpdateCustomOption",
      "partid=" + partid + "&optionid=" + optionid + "&value=" + value,
      partid
    );
  };

  /*==============================================
	Toggles displaying the options for a given part.
	Accepts the id of the part to show the options for.
	===============================================*/
  this.TogglePartOptions = function(partid) {
    var id = "EditPageOptions" + partid;
    if (!$(id)) {
      return;
    }
    /*if ($(id).visible()) {
			Effect.SlideUp(id,{duration:.3});
		}
		else {
			Effect.SlideDown(id,{duration:.3});
		}*/
    Util.Toggle(id);
  };

  /*==========================================================================
	Adds a new part / part to the page. Parametesr are:
	pageid - the id of the page to add to
	part - an id of the part _definition_ of the part to add
	===========================================================================*/
  this.AddPartToPage = function(part) {
    Site.ShowLoading();
    var data = "action=AddPart&pageid=" + Site.PageId + "&part=" + part;
    new Ajax.Request(Site.SiteRoot + PageEditor.AjaxHandler, {
      method: "post",
      parameters: data,
      onSuccess: PageEditor.AddPartCallback
    });
  };

  /*==========================================================================
	A callback method that is invoked when a part has been added to a page.
	This will then make another query to the page to retrieve the new part
	and call displayNewPart once that new content has been retrieved
	===========================================================================*/
  this.AddPartCallback = function(transport) {
    var partid = parseInt(transport.responseText);
    if (partid == NaN || partid == "NaN" || partid == "" || partid == 0) {
      alert(
        "An error was encountered while adding the part. The ajax request returned '0' as the part id"
      );
      Site.HideLoading();
      return;
    }
    //part was added.
    //Send off another request to get the the new part as html
    Site.GetPart(partid, PageEditor.DisplayNewPart, "", "1");
  };

  /*==========================================================================
	A callback method that is invoked once a new part that was just added
	to the page is retrieved via an ajax call. Adds it to the page via an
	annimation then reloads the page. We need to reload the page so that the drag
	and drop abilities still work
	===========================================================================*/
  this.DisplayNewPart = function(transport, partid) {
    Site.Debug.Out("Got new part");
    Site.HideLoading();
    var id = "part_" + partid;
    var newItem = document.createElement("li");
    newItem.setAttribute("id", id);
    newItem.setAttribute("style", "display:none");
    newItem.innerHTML = transport.responseText;

    $("area2_container").insertBefore(newItem, $("area2_container").firstChild);

    //$("area2_container").appendChild(newItem);
    $(id).hide();

    Effect.Appear(id, { duration: 0.5 });
    PageEditor.Setup(); //(makes objects sortable again)
  };

  /*==========================================================================
	Deletes a part from the page. Causes an animation, than triggers
	RemovePartFromDOM to occur afterwards
	===========================================================================*/
  this.DeletePart = function(partid) {
    var divId = "part_" + partid;
    Effect.Fade(divId);
    setTimeout(function() {
      PageEditor.RemovePartFromDOM(partid);
    }, 1000);
    //setTimeout(function(){RemovePartFromDOM('"+partid+"')",1000);
  };
  /*==========================================================================
	Removes the given part from the page. This includes making the
	div be removed from the DOM, and sending an ajax request
	===========================================================================*/
  this.RemovePartFromDOM = function(partid) {
    var divId = "part_" + partid;
    PageEditor.SendUpdate(
      "DeletePart",
      "pageid=" + Site.PageId + "&partid=" + partid
    );
    var container = $(divId).up();
    container.removeChild($(divId));
  };

  /*===========================================================
	Page Settings Helper
	Enables / disables the layout drop down
	based on whether the use template option is checked
	============================================================*/
  this.PageSettingsUpdateTemplateDropdown = function() {
    if (!$("usetemplate")) return; // :( firefox

    if ($("usetemplate").checked) {
      $("templateDropdownRow").show();
    } else {
      $("templateDropdownRow").hide();
    }
  };

  /*===========================================================
	Page Settings Helper
	Updates the layout image on the page settings page.
	Passed 'selectedItem' which is a string in the following form:
	layoutid|url_to_layout_image
	============================================================*/
  this.PageSettingsUpdateLayout = function(selectedItem) {
    var split = selectedItem.split("|");
    if (split.length < 2) {
      return;
    }
    var filename = split[1];
    $("PageSettingsTemplate").src = filename;
  };

  /*===========================================================
	ShowPartEditor
	Loads a part editor for a specific part. This will updates its
	preview pane with an editor specified by the part. The editor
	will allow the user to change the parts configuration or content.

	Accepts two parameters:
	partid - the name of the part to load the editor for
	view - the editor view. Optional. If not defined, "edit" is assumed
	============================================================*/
  this.ShowPartEditor = function(partid, view) {
    //show the new savechanges / cancel buttons
    Util.Show("EditPageBorderButtonsEdit" + partid);
    //hide the old buttons
    Util.Hide("EditPageBorderButtons" + partid);
    //make sure the options pane is closed
    Util.Hide("EditPageOptions" + partid);

    //make sure view is defined
    if (typeof view == "undefined") view = "edit";

    //get the parts content
    Site.GetPart(
      partid,
      PageEditor.LoadEditPartCallback,
      "v" + partid + "=" + view,
      "0"
    );
  };

  /*===========================================================
	HidePartEditor
	Hides the part edtior for a specific part
	============================================================*/
  this.HidePartEditor = function(partid) {
    Util.Hide("EditPageBorderButtonsEdit" + partid);
    PageEditor.ShowPartButtons(partid);
    PageEditor.ReloadPart(partid);
  };

  /*===========================================================
	SavePartEditor
	Submits the editor for a given part. This will look at
	the editor, then submit its form as an ajax request to the
	same page.
	============================================================*/
  this.SavePartEditor = function(partid) {
    //get the form.
    var form = "editform" + partid;
    if (!$(form)) return;

    //see if the editor specified some extra js
    //that we must execute
    var saveFunc = "window.saveChanges" + partid;
    Util.RunFunction(saveFunc);

    //submit the request
    Site.ShowLoading();
    $(form).request({
      onComplete: function() {
        //once request finished, exit the editor
        Site.HideLoading();
        Site.FlashMessage("Changes Saved");
        Util.Hide("EditPageBorderButtonsEdit" + partid);
        PageEditor.ShowPartButtons(partid);
        PageEditor.ReloadPart(partid);
      }
    });
  };

  /*===========================================================
	ShowPartButtons
	Shows the normal buttons for a part (options / edit / x)
	============================================================*/
  this.ShowPartButtons = function(partid) {
    //if the part is in edit mode, keep the buttons visible
    if ($("EditPageBorderButtonsEdit" + partid).visible()) return;
    Util.Show("EditPageBorderButtons" + partid);
    /*onmouseover="Util.Show('EditPageBorderButtons<?=$iid?>')"
		onmouseout="Util.Hide('EditPageBorderButtons<?=$iid?>')"*/
  };

  /*===========================================================
	HidePartButtons
	Hides the normal buttons for a part (options / edit / x)
	============================================================*/
  this.HidePartButtons = function(partid) {
    Util.Hide("EditPageBorderButtons" + partid);
  };

  /*===========================================================
	LoadEditPartCallback
	Callback after a part editor has been recieved.
	This will display it to the screen as well as import
	any javascript that it requires.
	============================================================*/
  this.LoadEditPartCallback = function(transport, partid) {
    var element = "EditPageSampleContent" + partid;
    if (!$(element)) return;

    //import any javascript
    Util.RunJavascriptInHtml(transport.responseText);

    //display it to the screen
    $(element).innerHTML = transport.responseText;
  };

  /*===========================================================
	Setup
	Sets up the edit page so that parts are sortable.
	============================================================*/
  this.Setup = function() {
    if ($("area1_container")) {
      Sortable.create("area1_container", {
        dropOnEmpty: true,
        handle: "handle",
        constraint: false,
        onUpdate: function() {
          if (PageEditor.AreaUpdate != "undefined") {
            PageEditor.AreaUpdate("area1");
          }
        },
        revert: true,
        containment: [
          "area1_container",
          "area2_container",
          "area3_container",
          "area4_container",
          "area5_container"
        ]
      });
    }

    if ($("area2_container")) {
      Sortable.create("area2_container", {
        dropOnEmpty: true,
        handle: "handle",
        constraint: false,
        onUpdate: function() {
          if (PageEditor.AreaUpdate != "undefined") {
            PageEditor.AreaUpdate("area2");
          }
        },
        revert: true,
        containment: [
          "area1_container",
          "area2_container",
          "area3_container",
          "area4_container",
          "area5_container"
        ]
      });
    }

    if ($("area3_container")) {
      Sortable.create("area3_container", {
        dropOnEmpty: true,
        handle: "handle",
        constraint: false,
        onUpdate: function() {
          if (PageEditor.AreaUpdate != "undefined") {
            PageEditor.AreaUpdate("area3");
          }
        },
        revert: true,
        containment: [
          "area1_container",
          "area2_container",
          "area3_container",
          "area4_container",
          "area5_container"
        ]
      });
    }

    if ($("area4_container")) {
      Sortable.create("area4_container", {
        dropOnEmpty: true,
        handle: "handle",
        constraint: false,
        onUpdate: function() {
          if (PageEditor.AreaUpdate != "undefined") {
            PageEditor.AreaUpdate("area4");
          }
        },
        revert: true,
        containment: [
          "area1_container",
          "area2_container",
          "area3_container",
          "area4_container",
          "area5_container"
        ]
      });
    }

    if ($("area5_container")) {
      Sortable.create("area5_container", {
        dropOnEmpty: true,
        handle: "handle",
        constraint: false,
        onUpdate: function() {
          if (PageEditor.AreaUpdate != "undefined") {
            PageEditor.AreaUpdate("area5");
          }
        },
        revert: true,
        containment: [
          "area1_container",
          "area2_container",
          "area3_container",
          "area4_container",
          "area5_container"
        ]
      });
    }
  };
})();
