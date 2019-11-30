/*=====================================================
NewPost
Javascript code for the new post editor. The code
is mostly divided into the following areas:
- Image uploading
- File uploading
- Image/File ajax calls
- Image toolbar code

The bulk of the code revolves around image and file
uploading, which uses a plugin called swfu uploader. This
is a bit of flash that allows ajax calls to upload
images in the background. The flash calls the javascript
periodically when an upload is being performed so that
the javascript can update the ui.
======================================================*/
var NewPost = new (function() {
	//the id of the image part (differentiates this
	//part from others)
	this.postid = 0;
	//the last index where text was entered into the editor. Used to determine
	//where to insert image code
	this.lastFocusPoint = 0;

	this.bbcodeConverted = false;

	/*===================================
	Initializes the editor by providing it
	with the id of the nav part it is editing
	===================================*/
	this.Init = function(postid) {
		this.postid = postid;

		if(typeof(postid) == 'undefined') {
			postid = 0;
		}
		this.postid = postid;


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
		$("contentTab").className = (tab=="content"?"selected":"back");
		$("previewTab").className = (tab=="preview"?"selected":"back");
		$("imageTab").className = (tab=="image"?"selected":"back");
		$("uploadTab").className = (tab=="upload"?"selected":"back");

		(tab == "content" ? Util.Show("contentTabContent") : Util.Hide("contentTabContent"));
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
		NewPost.bbcodeConverted = false;

		$("saveChangesButton").enable();
		$("saveChangesButton").value = "Save Changes";
	}


	/*===================================
	Makes an ajax call to convert the given editor text (in bbcode) to
	an html string fit for display
	===================================*/
	this.ConvertBBCode = function() {
		//don't both checking the html if we've already done so
		if (NewPost.bbcodeConverted ) {
			return;
		}

		var content = Form.Element.serialize($("content"));

		var data = 'ajax=ConvertBBCode';
		data += "&" + content;

		this.ShowMessage("Rendering...");


		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method:'post',
			parameters:data ,
			onSuccess: NewPost.ConvertBBCodeCallback
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
		NewPost.bbcodeConverted = true;

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
		var data = 'ajax=deleteImage';
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
		this.bbcodeConverted = false;
		//alert(this.lastFocusPoint);
		this.lastFocusPoint = Util.InsertIntoTextbox("content", toInsert, this.lastFocusPoint);
	//	alert(this.lastFocusPoint);
	}

	/*===================================
	Triggered when html editor was clicked, remebers the last
	focus point.
	===================================*/
	this.OnContentClick = function(element)
	{
		this.lastFocusPoint = element.selectionStart;
		//alert(this.lastFocusPoint);
	}

	/*===================================
	Monitors the editor field for tab presses
	and converts them to a tab character (versus) tabbing
	outside of the editor.
	===================================*/
	this.ConvertTab = function(event,element) {
		this.lastFocusPoint = element.selectionStart;
		this.bbcodeConverted = false;
		//A function to capture a tab keypress in a textarea and insert a tab character and NOT change focus.
		//9 is the tab key, except maybe it's 25 in Safari? oh well for them ...
		if(event.keyCode==9){
			this.lastFocusPoint = Util.InsertIntoTextbox("content", "\t", this.lastFocusPoint, true);
		}
	}

	/*=====================================================
	Checks to make sure that this post already has an id
	in the database. If it doesn't, a new one is obtained
	via an ajax call.
	======================================================*/
	this.CheckId = function() {
		if(this.postid == 0 ) {
			//if this is the first time the user is attempting
			//to do something with the post (ie, it doesn't have a database
			//represetnation yet, we need to create one)
			//Since we can't update the swfu uploader to point to the new
			//partid, we set the editing flag to true, which will result
			//in the postid being stored in a session variable
			new Ajax.Request(Site.SiteRoot + Site.Url, {
				method: 'post',
				parameters: {ajax:'getId'},
				asynchronous: false,	//ack, need to do this to hold off everything else until we have an id
				onSuccess: function(transport) {
					//store the new id
					NewPost.postid = transport.responseText;
					$("postid").value = NewPost.postid;
					$("uploadpostid").value = NewPost.postid;
			    }
			});
		}
	}


	/*===================================
	Saves changes via an ajax call
	===================================*/
	this.SaveChanges = function() {
		$("saveChangesButton").disable();
		$("saveChangesButton").value = "Saving...";

		NewPost.CheckId();

		$("postevent").value = "saveChanges";
		$("postForm").request({
  			onComplete: NewPost.SaveChangesCallback
			});
	}

	/*===================================
	Saves changes via an ajax call
	===================================*/
	this.SaveChangesCallback = function(transport) {
		$("saveChangesButton").disable();
		$("saveChangesButton").value = "Saved";
	}

	/*=====================================================
	Ppublishes a news post using an ajax call
	======================================================*/
	this.Publish = function() {
		NewPost.CheckId();
		$("postevent").value = "publish";
		$("postForm").request({
  			onComplete: NewPost.PublishCallback
			});
	}

	/*=====================================================
	Unpublishes a news post via an ajax call
	======================================================*/
	this.Unpublish = function() {
		NewPost.CheckId();
		$("postevent").value = "unpublish";
		$("postForm").request({
  			onComplete: NewPost.UnpublishCallback
			});
	}

	/*=====================================================
	Callback for an unpublish call.
	======================================================*/
	this.UnpublishCallback = function() {
		//change the unpublish button back to
		//publish
		$("publishButton").value = "Publish";
		$("publishButton").onclick = NewPost.Publish;

	}

	/*=====================================================
	Callback for publish call.
	======================================================*/
	this.PublishCallback = function() {
		//switch the publish button back to unpublishc
		$("publishButton").value = "Unpublish";
		$("publishButton").onclick = NewPost.Unpublish;
	}
})();

