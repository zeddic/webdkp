/*=====================================================
ImageNote
Javascript code for the imagenote part editor. Handles
the ajax calls.
======================================================*/

var BBCodePart = new (function() {
	//the id of the image part (differentiates this
	//part from others)
	this.partid = 0;
	//the last index where text was entered into the editor. Used to determine
	//where to insert image code
	this.lastFocusPoint = 0;
	this.htmlChecked = false;
	//this.useValidation = true;
	/*===================================
	Initializes the editor by providing it
	with the id of the nav part it is editing
	===================================*/
	this.Init = function(partid) {
		this.partid = partid;

		//image toolbar init

		//add event handlers to the regular icons so that they will
		//be highlighted when the user mouse overs
		var buttons = document.getElementsByClassName("imageToolbarIcon");
		for(var i = 0 ; i < buttons.length ; i++ ) {
			buttons[i].onmouseover = function(){this.className="imageToolbarIconHover";};
			buttons[i].onmouseout = function(){this.className="imageToolbarIcon"};
		}

		//default last focus point to the end of the editor
		this.lastFocusPoint = $("content").value.length;

	}

	/*===================================
	Switches to the given tab. Available names are
	"html" "preview" "image" "upload"
	===================================*/
	this.ShowTab = function(tab) {
		$("htmlTab").className = (tab=="html"?"selected":"back");
		$("previewTab").className = (tab=="preview"?"selected":"back");
		$("imageTab").className = (tab=="image"?"selected":"back");
		$("uploadTab").className = (tab=="upload"?"selected":"back");

		(tab == "html" ? Util.Show("htmlTabContent") : Util.Hide("htmlTabContent"));
		(tab == "preview" ? Util.Show("previewTabContent") : Util.Hide("previewTabContent"));
		(tab == "image" ? Util.Show("imageTabContent") : Util.Hide("imageTabContent"));
		(tab == "upload" ? Util.Show("uploadTabContent") : Util.Hide("uploadTabContent"));


		if(tab == "preview") {
			this.ConvertBBCode();
		}
		else if(tab == "upload" ) {
			//if we are on the upload tab, stuff the current content data into
			//a hidden form input. This allows us to carry over their changes if
			//they decide to upload an image
			$("tempcontent").value = $("content").value;
		}
	}

	/*===================================
	Sets a dirty bit when something has been changed,
	enabling the 'save changes' button
	===================================*/
	this.OnChange = function() {
		//set a dirty bit that will signal that html needs to be revalidated
		BBCodePart.htmlChecked = false;

		$("saveEditChanges").enable();
		$("saveEditChanges").value = "Save Changes";
	}

	/*===================================
	Saves changes via an ajax call
	===================================*/
	this.SaveChanges = function() {

		$("saveEditChanges").disable();
		$("saveEditChanges").value = "Saving...";
		$("editform").request({
  			onComplete: BBCodePart.SaveChangesCallback
		});
	}

	/*===================================
	Saves changes via an ajax call
	===================================*/
	this.SaveChangesCallback = function(transport)
	{
		$("saveEditChanges").disable();
		$("saveEditChanges").value = "Saved";
	}
	/*===================================
	Makes an ajax call to convert the given editor text (in bbcode) to
	an html string fit for display
	===================================*/
	this.ConvertBBCode = function() {
		//don't both checking the html if we've already done so
		if (BBCodePart.htmlChecked ) {
			return;
		}

		var content = Form.Element.serialize($("content"));

		var data = 'ajax'+this.partid+'=ConvertBBCode';
		data += "&" + content;

		this.ShowMessage("Rendering...");


		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: BBCodePart.ConvertBBCodeCallback
			});

	}

	/*===================================
	Recieves results of convertbbcode call and
	displays rendered html in the preview tab
	===================================*/
	this.ConvertBBCodeCallback = function(transport) {

		var html = transport.responseText;
		Util.Hide("message");

		$("preview").innerHTML =html;
		if(typeof initLightbox == 'function' ) {
				initLightbox();
		}
		BBCodePart.htmlChecked = true;

	}

	/*===================================
	Shows a generic message to the user
	===================================*/
	this.ShowMessage = function(message) {
		$("message").className = "noticeMessage";
		$("message").innerHTML = message;
		Util.Show("message");
	}

	/*===================================
	Shows an error message to the user
	===================================*/
	this.ShowError = function(message) {
		$("message").className = "errorMessage";
		$("message").innerHTML = message;
		Util.Show("message");
	}

	/*===================================
	Shows a good message to the user
	===================================*/
	this.ShowGood = function(message) {
		$("message").className = "message";
		$("message").innerHTML = message;
		Util.Show("message");
	}

	/*===================================
	Deletes a given image from the database collection
	===================================*/
	this.DeleteImage = function(element, id) {
		//confirm the delete
		var result = confirm("Delete Image?");
		if(!result)
			return;

		//remove it from the dom
		var toDelete = element.up().up();
		var container = toDelete.up();
		container.removeChild(toDelete);

		//send the ajax request to the backend
		var data = 'a'+this.partid+'=deleteImage';
		data += "&image="+id;

		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: function(transport){

						}
			});
	}

	/*===================================
	Inserts code to display an image into the html editor.
	Accepts 2 parameters:
	path of the image to show.
	and a path to a larger / fullscreen image.

	If use lightbox is checked off, the larger path will be used
	for the lightbox code. Otherwise it is discarded.
	===================================*/
	this.InsertImage = function(id, size, title) {
		//create the image code
		var img = "[img id='"+id+"' size='"+size+"' alt='"+title+"'";
		//if use lightbox is checked add the extra code
		if( $("uselightbox").checked ) {
			img += " lightbox='true'";
		}
		img += "][/img]\r\n";
		//insert it all into the editor
		this.InsertCode(img);
	}

	/*===================================
	Inserts the given text into the html text editor.
	Focus is placed immediatly after the text inserted.
	Text is inserted at the last place text was typed,
	or if the text box hasn't been edited yet, at the end
	of the text box.
	===================================*/
	this.InsertCode = function(toInsert) {
		this.htmlChecked = false;
		var element = $("content");

		var oldscroll = element.scrollTop; //So the scroll won't move after a tabbing
		//Check if we're in a firefox deal
		if (element.setSelectionRange) {
			var pos_to_leave_caret=this.lastFocusPoint+toInsert.length;
			//Put in the tab
			element.value = element.value.substring(0,this.lastFocusPoint) + toInsert + element.value.substring(this.lastFocusPoint,element.value.length);
			//There's no easy way to have the focus stay in the textarea, below seems to work though
			//setTimeout("var t=$('content'); t.focus(); t.setSelectionRange(" + pos_to_leave_caret + ", " + pos_to_leave_caret + ");", 0);
		}
		//Handle IE
		else if (document.selection) {
			//element.focus();
			// IE code, pretty simple really
			var range = document.selection.createRange();
			//if we don't have focus to the element, we have to insert it the 'messy way'
			if ( range.parentElement() != element) {
				element.innerHTML += toInsert;
			}
			//we have focus, we can insert text the good way
			else {
				range.text = toInsert;
				range.select();
			}
		}

		this.lastFocusPoint += toInsert.length;

		element.scrollTop = oldscroll; //put back the scroll
	}

	/*===================================
	Triggered when html editor was clicked, remebers the last
	focus point.
	===================================*/
	this.OnContentClick = function(element)
	{
		this.lastFocusPoint = element.selectionStart;
	}

	/*===================================
	Monitors the editor field for tab presses
	and converts them to a tab character (versus) tabbing
	outside of the editor.
	===================================*/
	this.ConvertTab = function(event,element) {
		this.lastFocusPoint = element.selectionStart;

		//A function to capture a tab keypress in a textarea and insert a tab character and NOT change focus.
		//9 is the tab key, except maybe it's 25 in Safari? oh well for them ...
		if(event.keyCode==9){
			var oldscroll = element.scrollTop; //So the scroll won't move after a tabbing
			event.returnValue=false;  //This doesn't seem to help anything, maybe it helps for IE
			//Check if we're in a firefox deal
			if (element.setSelectionRange) {

				var pos_to_leave_caret=element.selectionStart+1;
				//Put in the tab
				element.value = element.value.substring(0,element.selectionStart) + '\t' + element.value.substring(element.selectionEnd,element.value.length);
				//There's no easy way to have the focus stay in the textarea, below seems to work though
				setTimeout("var t=$('content'); t.focus(); t.setSelectionRange(" + pos_to_leave_caret + ", " + pos_to_leave_caret + ");", 0);
			}
			//Handle IE
			else {
				// IE code, pretty simple really
				document.selection.createRange().text='\t';
			}
			element.scrollTop = oldscroll; //put back the scroll
		}
	}
})();