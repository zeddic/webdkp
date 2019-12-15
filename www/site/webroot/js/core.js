/*==============================================
Main site utilities. Provides access to common tasks
related to the site.

TODO(scott):
 * Modernize all site js.
 * Remove prototype deps
 * Remove scriptaculous
===============================================*/

const Site = new (function() {
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
Common utility methods.
===============================================*/
const Util = new (function() {
  /*==============================================
	Shows the element with the given id
	===============================================*/
  this.Show = function(id) {
    const el = document.getElementById(id);
    if (el) {
      el.style.display = "block";
    }
  };
  /*==============================================
	Hides the element with the given id
	===============================================*/
  this.Hide = function(id) {
    const el = document.getElementById(id);
    if (el) {
      el.style.display = "none";
    }
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
    const el = document.getElementById(id);
    if (el && el.focus) {
      el.focus();
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
})();
