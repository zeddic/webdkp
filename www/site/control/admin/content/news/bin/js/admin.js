/*=====================================================

======================================================*/

var NewsAdmin = new (function() {
	
	this.publishedLinks = Array(5);
	this.pCurrentPage = 1;
	this.pLastPage = 10;
	this.unpublishedLinks = Array(5);
	this.unpCurrentPage = 1;
	this.unpLastPage = 10;
	/*=====================================================
	Sets the current page and the max page for the published
	tab. Must be called before Init. 
	======================================================*/
	this.SetPublishedData = function(page, maxpage) {
		this.pCurrentPage = page;
		this.pLastPage = maxpage;
	}
	
	/*=====================================================
	Sets the current page and max page for the unpublished tab. 
	Must be called before init. 
	======================================================*/
	this.SetUnpublishedData = function(page, maxpage) {
		this.unpCurrentPage = page;
		this.unpLastPage = maxpage;
	}
	
	/*=====================================================
	Initializes the page features for the news admin 
	page. This will setup the page arrays, insert
	the page links's html dynamically, as well as load the 
	current pages via an ajax call.  
	======================================================*/
	this.Init = function() {
		Util.AddOnLoad(NewsAdmin.InitOnLoad);		
	}
	
	this.InitOnLoad = function()
	{
		//intiailize what pages each of the page links 
		//should link to for the published and unpublished
		//tabs
		for( var i = 0 ; i < NewsAdmin.publishedLinks.length ; i++ ) {
			NewsAdmin.publishedLinks[i] = i + 1;
		}
		for( var i = 0 ; i < NewsAdmin.unpublishedLinks.length ; i++ ) {
			NewsAdmin.unpublishedLinks[i] = i + 1;
		}
		
		//create the html for the published and unpublished
		//tabs
		NewsAdmin.CreatePageLinks("unpublishedPageLinks","unp");
		NewsAdmin.CreatePageLinks("publishedPageLinks","p");
		
		//update the html so it reflects the current 
		//data arrays
		NewsAdmin.UpdatePageLinks(NewsAdmin.publishedLinks,"p");
		NewsAdmin.UpdatePageLinks(NewsAdmin.unpublishedLinks,"unp");
		
		//update the ui to show the current max page. 
		//ie: page 5 of 10
		$("p_max").innerHTML = NewsAdmin.pLastPage; 
		$("unp_max").innerHTML = NewsAdmin.unpLastPage; 
		
		//$("p_0").style.display = "";
		//alert($("p_0").style.display);
		
		//navigate to the pages setup before init was called. 
		NewsAdmin.PublishedGotoPage(NewsAdmin.pCurrentPage);
		NewsAdmin.UnpublishedGotoPage(NewsAdmin.unpCurrentPage);
	}
	
	/*=====================================================
	Dynamically creates and inserts the html for the page links. 
	This will add the links to the div with the id specified by 
	container. Each of the links will be appended by a prefix 
	followed by an underscore. For example:
	p_first, p_prev, p_0, p_1, p_2, p_3, p_4, p_next, p_last
	
	The 0-4 links will be dynamically populated using the contents
	of an array (published links or unpublishedLinks). The index
	of each array will correspond to the link while the contents will
	be the page that that links corresponds to. 
	======================================================*/
	this.CreatePageLinks = function(container, prefix) {		
		var onclick = "NewsAdmin.PageLinkClick(this)";
		
		//create the links
		var first = Builder.node('a', {id:prefix+"_first",onclick:onclick,title:"first"}, "««" );
		var prev = Builder.node('a', {id:prefix+"_prev",onclick:onclick,title:"previous"}, "«");	
		var link0 = Builder.node('a', {id:prefix+"_0",onclick:onclick} );
		var link1 = Builder.node('a', {id:prefix+"_1",onclick:onclick} );	
		var link2 = Builder.node('a', {id:prefix+"_2",onclick:onclick} );	
		var link3 = Builder.node('a', {id:prefix+"_3",onclick:onclick} );	
		var link4 = Builder.node('a', {id:prefix+"_4",onclick:onclick} );	
		var next = Builder.node('a', {id:prefix+"_next",onclick:onclick,title:"next"}, "»" );	
		var last = Builder.node('a', {id:prefix+"_last",onclick:onclick,title:"last"}, "»»" );	
	
		//add the links to the container
		$(container).appendChild(first);
		$(container).appendChild(prev);
		$(container).appendChild(link0);
		$(container).appendChild(link1);
		$(container).appendChild(link2);
		$(container).appendChild(link3);
		$(container).appendChild(link4);
		$(container).appendChild(next);
		$(container).appendChild(last);
	}
	
	/*=====================================================
	Updates the links prefix_0 through prefix_4 so that
	their values represent the values stored in the given 
	array. Example, array[0] == 4, so prefix_4 will now
	show "4" (ie, linking to page 4)
	======================================================*/
	this.UpdatePageLinks = function(array, prefix) { 
		for( var i = 0 ; i < array.length ; i++ ) {
			var id = prefix+"_"+i;
			var value = array[i];
			if( $(id) ) { 
				$(id).innerHTML = value;
			}
		}
	}
	
	/*=====================================================
	Invoked when a user clicks on any of the page links. 
	This will look at the id of the page link, determing
	which of the links were pressed and what the prefix 
	was. The prefix will allow us to determine if we 
	are paging through published or unpublished data. 
	======================================================*/
	this.PageLinkClick = function(element) {
		//get the area (published or unpublished)
		var area = this.GetPageLinkArea(element);
		//get the number (0-4) or next,last,prev, first
		var number = this.GetPageLinkNumber(element);
		
		//attempt to convert number into its real page number
		//by looking at the arrays. Example, published_0 is clicked. 
		//In publishedLinks[0] there might be "4", meaning that 
		//the user clicked on a link to page 4. 
		var pageRequest = -1; 
		if ( area == "p" && number < this.publishedLinks.length) 
			pageRequest = this.publishedLinks[number];
		else if( area == "unp" && number < this.unpublishedLinks.length) 
			pageRequest = this.unpublishedLinks[number];
		
		//user clicked on a link in the published area
		if ( area == "p" ) {
			if ( number == "first" ) 
				this.PublishedGotoPage(1);
			else if(number == "prev" ) 
				this.PublishedGotoPage(this.pCurrentPage-1);
			else if(number == "next" ) 
				this.PublishedGotoPage(this.pCurrentPage+1);
			else if(number == "last" )
				this.PublishedGotoPage(this.pLastPage);
			else
				this.PublishedGotoPage(pageRequest);
		}
		//user clicked on a link in the unpublished area
		else if( area == "unp") { 
			if ( number == "first" ) 
				this.UnpublishedGotoPage(1);
			else if(number == "prev" ) 
				this.UnpublishedGotoPage(this.unpCurrentPage-1);
			else if(number == "next" ) 
				this.UnpublishedGotoPage(this.unpCurrentPage+1);
			else if(number == "last" )
				this.UnpublishedGotoPage(this.unpLastPage);
			else
				this.UnpublishedGotoPage(pageRequest);
		}
		return false;
	}
	
	/*=====================================================
	Determine the current maximum page for the published page. 
	This will obtain this information via an ajax call. If
	the retrieved number is less than the current max page
	number, this will update the ui as well as redownload
	the page data. Called in case the user deletes a file, 
	so they don't get stranded on empty pages. 
	======================================================*/
	this.GetPublishedMaxPage = function() {
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method: 'post',
			parameters: {ajax:'getPublishedMaxPage'},
			onSuccess: function(transport) {
				//determine the current last page
				var oldLastPage = NewsAdmin.pLastPage;
				NewsAdmin.pLastPage = transport.responseText;
				//update ui
				$("p_max").innerHTML = NewsAdmin.pLastPage; 
				//if the current page is beyond the new maximum page, 
				//back up. 
				while( NewsAdmin.pCurrentPage > NewsAdmin.pLastPage ) 
					NewsAdmin.pCurrentPage --;
				//if there was a change, update the ui
				if( oldLastPage != NewsAdmin.pLastPage ) 
					NewsAdmin.PublishedGotoPage(NewsAdmin.pCurrentPage);
		    }
		});		
	}
	
	/*=====================================================
	Determine the current maximum page for the unpublished page. 
	This will obtain this information via an ajax call. If
	the retrieved number is less than the current max page
	number, this will update the ui as well as redownload
	the page data. Called in case the user deletes a file, 
	so they don't get stranded on empty pages. 
	======================================================*/
	this.GetUnpublishedMaxPage = function() { 
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method: 'post',
			parameters: {ajax:'getUnpublishedMaxPage'},
			onSuccess: function(transport) {
				//remember the 'old' / current last page
				var oldLastPage = NewsAdmin.unpLastPage;
				//get the new last page
				NewsAdmin.unpLastPage = transport.responseText;
				//update the ui. 
				$("unp_max").innerHTML = NewsAdmin.unpLastPage; 
				//make sure the current page is within range. If it isn't, 
				//back up. 
				while( NewsAdmin.unpCurrentPage > NewsAdmin.unpLastPage ) 
					NewsAdmin.unpCurrentPage --;
				//if there was a change, redownload the current page
				if( oldLastPage != NewsAdmin.unpLastPage ) 
					NewsAdmin.PublishedGotoPage(NewsAdmin.unpCurrentPage);
		    }
		});		
	}
	
	/*=====================================================
	Deletes a post with the given id. This will be done 
	via an ajax call. When the call is complete, the 
	post will be deleted directly from the dom - no page 
	reload will be needed. This will also trigger off
	a check of the new max page. 
	======================================================*/
	this.DeletePost = function(postid) {
		
		//popup
		if( !confirm("Delete post?") ) 
			return;
		
		
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method: 'post',
			parameters: {ajax:'DeletePost',postid:postid},
			onSuccess: function(transport) {
				//get the response. It will be in the form:
				//0|message or 1|message
				//where 0 signals a failure/error
				var result = transport.responseText;
				var parts = result.split("|");
				if(parts.length < 2 )
					return;
				var ok = parts[0];
				var message = parts[1];
				
				//display the message
				$("postResult").innerHTML = message;
				Util.Show("postResult");
				
				//yay!			
				if(ok == 1 ) {
					//remove it from the dom
					if( $("post_"+postid) ) 
						$("post_"+postid).up().removeChild($("post_"+postid));
					
					//reget the current last page. We may have to go back
					//a page if we deleted the last post on a page.
					NewsAdmin.GetPublishedMaxPage();
					NewsAdmin.GetUnpublishedMaxPage();
				} 
		    }
		});		
	}	
	
	/*=====================================================
	Moves the published area to the given page number. 
	Saves the chnages into a session so the user will be 
	moved back to this page on a reload. 
	======================================================*/
	this.PublishedGotoPage = function(page) {
		 //check bounds
		if ( page > this.pLastPage || page < 1) 
			return;
		
		//save the current page
		this.pCurrentPage = page;
		$("p_current").innerHTML = this.pCurrentPage;
		
		//determine the range of pages that we will display as direct links
		
		//assume 2 to the left
		var left = page - 2;
		//if we will show links to pages > the last page, move
		//the left most link back some
		if ( left + 4 > this.pLastPage ) {
			left = this.pLastPage-4;
		}
		//make sure we can't show links to pages under page 1
		while ( left <= 0 ) 
			left++ ;
		
		//update the page link array
		for ( var i = 0 ; i < 5 ; i ++ ) {
			//if a page link is out of bounds, hide it
			if(left+i <= this.pLastPage ) {
				this.publishedLinks[i] = left+i;
				$("p_"+i).style.display="";
			}
			else {
				this.unpublishedLinks[i] = "";
				$("p_"+i).style.display="none";
			}
			//if the given page is selected, set its class
			if ( left + i == page )
				$("p_"+i).className = "selectedPage";
			else
				$("p_"+i).className = "";
		}
		
		//reflect the changes on the ui
		this.UpdatePageLinks(this.publishedLinks,"p"); 
		
		//send out the ajax request		
		this.GetPublishedPage(this.pCurrentPage);
	}
	
	/*=====================================================
	Moves the unpublished area to the given page. Remembers
	the selected page via a session call so that it can be 
	restored when the user visits the page again. 
	======================================================*/
	this.UnpublishedGotoPage = function(page) {
		//check bounds
		if ( page > this.unpLastPage || page < 1) 
			return;
		
		//save the current page
		this.unpCurrentPage = page;
		$("unp_current").innerHTML = this.unpCurrentPage;
		
		//determine the range of pages that we will display as direct links
		
		//assume 2 to the left
		var left = page - 2;
		//if we will show links to pages > the last page, move
		//the left most link back some
		if ( left + 4 > this.unpLastPage ) {
			left = this.unpLastPage-4;
		}
		//make sure we can't show links to pages under page 1
		while ( left <= 0 ) 
			left++ ;
		
		//update the page link array
		for ( var i = 0 ; i < 5 ; i ++ ) {
			//if a page link is out of bounds, hide it
			if(left+i <= this.unpLastPage ) {
				this.unpublishedLinks[i] = left+i;
				$("unp_"+i).style.display="";
			}
			else {
				this.unpublishedLinks[i] = "";
				$("unp_"+i).style.display="none";
			}
			//if the given page is selected, set its class
			if ( left + i == page )
				$("unp_"+i).className = "selectedPage";
			else
				$("unp_"+i).className = "";
		}
		
		//reflect the changes on the ui
		this.UpdatePageLinks(this.unpublishedLinks,"unp"); 
		
		//send out the ajax request		
		this.GetUnpublishedPage(this.unpCurrentPage);
		
	}
	
	/*=====================================================
	Given a link element it will return its 'area' or prefix
	that it was given when it was created. 
	======================================================*/
	this.GetPageLinkArea = function(element) {
		var id = element.id;
		var parts = id.split("_");
		if(parts.length < 2 )
			return 0;
		return parts[0];
	}
	
	/*=====================================================
	Given a link element it will return its 'number', or the
	its name after its prefix. Example:
	unp_next returns "next"
	unp_1 returns "1"
	======================================================*/
	this.GetPageLinkNumber = function(element) {
		var id = element.id;
		var parts = id.split("_");
		if(parts.length < 2 )
			return 0;
		return parts[1];
	}
	
	/*===================================
	Displays the edit link tab
	===================================*/
	this.ShowTab= function(area) {
		
		//Published Tab
		if ( area == "Published" ) {
			$("PublishedTab").className = "selected";
			Util.Show("PublishedArea");
			this.SaveActiveTab("Published");
		} else { 
			$("PublishedTab").className = ""; 
			Util.Hide("PublishedArea");
		}
		
		//Unpublished Tab
		if ( area == "Unpublished" ) {
			$("UnpublishedTab").className = "selected";
			Util.Show("UnpublishedArea");
			this.SaveActiveTab("Unpublished");
		} else { 
			$("UnpublishedTab").className = ""; 
			Util.Hide("UnpublishedArea");
		}
	}
	
	/*=====================================================
	Loads the given page number into the gui via an ajax 
	call. 
	======================================================*/
	this.GetPublishedPage = function(page) {
		if(typeof page == undefined ) {
			page = 1;
		}
		
		Util.Show("p_loading");
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method: 'post',
			parameters: {ajax:'getPublished',posts_published_page:page},
			onSuccess: function(transport) {
				//$("PublishedArea").innerHTML = transport.responseText;
				//alert("got published page");
				Util.Hide("p_loading");
				$("publishedContent").innerHTML = transport.responseText;
		    }
		});		
	}
	
	/*=====================================================
	Loads the given page number into the gui via an ajax call
	======================================================*/
	this.GetUnpublishedPage = function(page) {
		if(typeof page == undefined) {
			page = 1;		
		}	
		Util.Show("unp_loading");
		//alert("requesting unpublished page "+page);
		new Ajax.Request(Site.SiteRoot + Site.Url, {
			method: 'post',
			parameters: {ajax:'getUnpublished',posts_unpublished_page:page},
			onSuccess: function(transport) {
				//alert("recieved unpublished");
				//$("UnpublishedArea").innerHTML = transport.responseText;
				
				$("unpublishedContent").innerHTML = transport.responseText;
				Util.Hide("unp_loading");
		    }
		});		
	}
	
	/*=====================================================
	Saves the currently selected tab via an ajax call. This is
	stored into a session. This tab will automattically
	be restored when the user browse to the same page.
	======================================================*/
	this.SaveActiveTab = function(tab) {
		new Ajax.Request(Site.SiteRoot + Site.Url, {
				method: 'post',
				parameters: {ajax:'saveActiveTab',tab:tab}
			});
	}

})();


