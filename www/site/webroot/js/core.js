/*==============================================
Main site class. Provides access to common tasks
related to the site.
===============================================*/

Site = new (function() {
  //The root path of the site, such as http://www.site.com/
  this.SiteRoot = "";
  //The url of the current page. Includes site root
  this.Url = "";
  /*==============================================
	Accessors
	===============================================*/
  this.SetSiteRoot = function(siteRoot) {
    this.SiteRoot = siteRoot;
  };

  this.SetUrl = function(url) {
    this.url = url;
  };

  this.Init = function(siteRoot, url) {
    this.SiteRoot = siteRoot;
    this.Url = url;
  };
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
	Focuses on the given element
	===============================================*/
  this.Focus = function(id) {
    if (!$(id)) {
      return;
    }
    $(id).focus();
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
})();
