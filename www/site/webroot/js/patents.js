/*=====================================================
Global patent class that provides some utility functions
======================================================*/
Patents = new (function() {

	this.UserGroup = "Visitor";
	this.UserID = 0;

	this.Init = function(usergroup, userid) {
		this.UserGroup = usergroup;
		this.UserID = userid;
		this.Setup();
	}

	this.ClearSpaces = function(data)
	{
		return data.replace(/ /g,"+");
	}

	this.Setup = function() {
		Event.observe(window,'load', Patents.SetupOnLoad);
	}

	this.SetupOnLoad = function() {
		Patents.SetupButtons();
		Patents.SetupSimpleTables();
		Patents.SetupTooltips();
	}

	this.SetupTooltips = function() {
		var links = $$('a.tooltip');
		for ( var i = 0 ; i < links.size() ; i++ ) {
			Patents.SetupTooltip(links[i]);
		}
	}

	this.SetupTooltip = function(element) {
		Event.observe(element,'mouseover', Patents.TooltipOver );
		Event.observe(element,'mousemove', Patents.TooltipMove);
		Event.observe(element,'mouseout', Patents.TooltipOut);
	}


	this.TooltipOver = function(event) {
		var element = event.element();
		if (element.getAttribute("tooltip") != null) {
			var tooltip = element.getAttribute("tooltip");
			var icon = '';
			if(element.getAttribute("icon") != null) {
				icon = element.getAttribute("icon");
				$WowheadPower.showTooltip(event, tooltip, icon);
			}
			else {
				$WowheadPower.showTooltip(event, tooltip);
			}
		}
	}

	this.TooltipOut = function(event) {
		$WowheadPower.hideTooltip(event);
	}

	this.TooltipMove = function(event) {
		$WowheadPower.moveTooltip(event);
	}


	this.ButtonOver = function(event) {
		this.addClassName("dkpbuttonover");
	}

	this.ButtonOut = function(event) {
		this.removeClassName("dkpbuttonover");
	}

	this.SetupButtons = function() {
		var links = $$('a.dkpbutton');
		for ( var i = 0 ; i < links.size() ; i++ ) {
			Event.observe(links[i],'mouseover', Patents.ButtonOver);
			Event.observe(links[i],'mouseout', Patents.ButtonOut);
		}
	}

	this.SetupSimpleTables = function() {
		var tables = $$('table.simpletable');
		for ( var i = 0 ; i < tables.size() ; i++ ) {
			table = new PatentTable(tables[i].id);
			table.DrawSimple();
		}
	}
})();

/*================================================
Patent table is a javascript table that causes
in place sorting and paging. It also has
other extending pages that provide specific
cases.
=================================================*/
var PatentTable = Class.create({

	initialize: function(name) {
		this.tableName = name;
		this.table = $(name);
		this.tableBody = this.table.getElementsByTagName("tbody")[0];
		this.tableHead = this.table.getElementsByTagName("thead")[0];
		this.items = [];
		this.sortTypes = [];
		this.sortedCol = -1;
		this.sortedReverseCol = -1;
		this.activeSortedCol = 0;
		this.usePaging = false;
		this.rowsPerPage = 25;
		this.page = 1;
		this.maxpage = 1;

		this.rowObjects = [];

		this.selectedRow = -1;
		this.users = [];
		this.selectedCell = -1;
	},

	EnablePaging: function(rowsPerPage) {
		this.usePaging = true;
		this.rowsPerPage = rowsPerPage;
		this.CalculatePagingInfo();
	},

	CalculatePagingInfo: function() {
		this.page = 1;
		this.maxpage = Math.floor(this.items.length / this.rowsPerPage)+1;
		if(this.maxpage == 0 )
			this.maxpage = 1;
	},

	Add: function(item) {
		this.items.push(item);
	},

	DrawSimple: function() {

		for ( var i = 0 ; i < this.tableBody.rows.length ; i++ ) {
			var row = this.tableBody.rows[i];
			Event.observe(row,'mouseover', this.OnRowOver);
			Event.observe(row,'mouseout', this.OnRowOut);
		}
	},

	Draw: function() {
		this.CalculatePagingInfo();
		Event.observe(window,'load', this.DrawOnLoad.bindAsEventListener(this));
	},


	Redraw: function() {
		this.Sort(this.activeSortedCol,true);
	},

	Clear: function() {
		while(this.tableBody.rows.length > 0 )
			this.tableBody.removeChild(this.tableBody.firstChild);
	},

	DrawOnLoad: function() {
		this.HookHeaders();
		this.DrawPageButtons();

		for(var i = 0 ; i < this.items.length; i++ ) {

			row = this.GetRow(i);

			this.rowObjects[i] = row;

			if(this.usePaging && i >= this.rowsPerPage )
				continue;
			//row_array[row_array.length] = [this.GetInnerText(rows[i].cells[col]), rows[i]]
			this.tableBody.appendChild(row);
		}
		Patents.SetupTooltips();

	},

	GetPageButton: function(name) {
		var button = Builder.node('img',{src:Site.SiteRoot+'images/page/'+name+'.gif',className:'pagebutton'});
		Event.observe(button,'mouseover', this.OnPageIconOver);
		Event.observe(button,'mouseout', this.OnPageIconOut);

		return button;
	},

	UpdatePageText: function() {
		var els = $$('div.pagedata');
		for ( var i = 0 ; i < els.size() ; i++ ) {
			els[i].innerHTML = "Page <b>"+this.page+"</b> of "+this.maxpage;
		}
	},

	GeneratePageBar: function() {
		var container = Builder.node('div');

		var pageButtons = Builder.node('div',{style:"float:right"});

		var first = this.GetPageButton('first');
		pageButtons.appendChild(first);
		Event.observe(first,'click', this.FirstPage.bindAsEventListener(this));

		var prev = this.GetPageButton('left');
		pageButtons.appendChild(prev);
		Event.observe(prev,'click', this.PrevPage.bindAsEventListener(this));

		var next = this.GetPageButton('right');
		pageButtons.appendChild(next);
		Event.observe(next,'click', this.NextPage.bindAsEventListener(this));

		var last = this.GetPageButton('last');
		pageButtons.appendChild(last);
		Event.observe(last,'click', this.LastPage.bindAsEventListener(this));

		container.appendChild(pageButtons);

		var count = Builder.node('div',{className:'pagedata'});
		count.innerHTML = "Page <b>"+this.page+"</b> of "+this.maxpage;

		container.appendChild(count);

		return container;
	},

	DrawPageButtons: function() {
		if(!this.usePaging)
			return;

		var top = this.GeneratePageBar();
		var bottom = this.GeneratePageBar();

		this.table.parentNode.insertBefore(top, this.table);
		this.table.parentNode.insertBefore(bottom, this.table.nextSibling);

	},

	OnPageIconOver: function(event) {
		var el = event.element();

		var pathParts = el.src.split("/");
		var file = pathParts[pathParts.length-1];
		file = file.replace(".gif","");
		el.src = Site.SiteRoot+'images/page/'+file+'-over.gif';
	},

	OnPageIconOut: function(event) {
		var el = event.element();
		var pathParts = el.src.split("/");
		var file = pathParts[pathParts.length-1];
		file = file.replace("-over.gif","");
		el.src = Site.SiteRoot+'images/page/'+file+'.gif';
	},

	NextPage: function() {
		if(this.page >= this.maxpage )
			return;
		this.page++;
		this.Redraw();
		this.UpdatePageText();
	},

	PrevPage: function() {
		if(this.page <= 1 )
			return;
		this.page--;
		this.Redraw();
		this.UpdatePageText();
	},

	FirstPage: function() {
		this.page = 1;
		this.Redraw();
		this.UpdatePageText();
	},

	LastPage: function() {
		this.page = this.maxpage;
		this.Redraw();
		this.UpdatePageText();
	},

	GetStartPageIndex: function() {
		if(!this.usePaging)
			return 0;

		return (this.page-1)*this.rowsPerPage;
	},

	GetEndPageIndex: function() {
		if(!this.usePaging)
			return this.items.length;

		return (this.page)*this.rowsPerPage - 1;
	},


	OnHeaderClick: function(event) {
		var data = $A(arguments);
		data.shift();

		var col = data[0];

		this.Sort(col);
		var z = 5;
	},

	HookHeaders: function(){
		var row = this.tableHead.rows[0];
		for(var i = 0 ; i < row.cells.length ; i++ ) {
			if(!Element.hasClassName(row.cells[i],"nosort")) {

				var sortby = this.SortAlpha;
				if (row.cells[i].getAttribute("sort") != null) {
			    	var sortName =  row.cells[i].getAttribute("sort");
			    	sortby = this.GetSortFunc(sortName);
			    }
			    this.sortTypes[i] = sortby;

			    Util.DisableSelection(row.cells[i]);

				Event.observe(row.cells[i],'click', this.OnHeaderClick.bindAsEventListener(this,i));
			}
		}
		var z = 5;
	},

	GetSortFunc: function(name) {
		switch(name)
		{
			case "number":
				return this.SortNumber;
			case "string":
				return this.SortAlpha;
		}
		return this.SortAlpha;
	},

	OnRowOver: function(event) {
		//var el = event.element()
		this.addClassName("over");
	},

	OnRowOut: function(event) {
		var el = event.element()
		this.removeClassName("over");
	},

	GetRow: function(i) {
		//var url = Site.SiteRoot + "dkp/" + this.items[i].name;

		var row = Builder.node('tr');
		row.appendChild(Builder.node('td',{},"..."));
		row.appendChild(Builder.node('td',{className:"center"},"..."));

		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);
		//Event.observe(row,'click', this.OnRowClick.bindAsEventListener(this, url));

		return row;
	},

	OnRowClick: function(i) {
		var data = $A(arguments);
		data.shift();
		var url = data[0];
		document.location = url;
	},

	AddUpChar: function(col) {
		var char = Prototype.Browser.IE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
		this.AddCharToCol(col,char);
	},

	AddDownChar: function(col) {
		var char = Prototype.Browser.IE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
		this.AddCharToCol(col,char);
	},

	AddCharToCol: function(col, char) {

		this.RemoveCharFromCol(col);


		var toadd = Builder.node('span',{id:'sortchar_'+this.tableName});
		toadd.innerHTML = char;

		var cell = this.tableHead.rows[0].cells[col];
		var el = cell.firstChild;

		el.appendChild(toadd);

	},

	RemoveCharFromCol: function(col) {

		var cell = this.tableHead.rows[0].cells[col];
		var toDelete = document.getElementById('sortchar_'+this.tableName);
		if(toDelete) {
			toDelete.parentNode.removeChild(toDelete);
		}

	},

	Sort: function(col, redo) {

		this.activeSortedCol = col;

		if (typeof redo != "undefined")
			redo = true;
		else
			redo = false;

		if(this.sortedCol == col && !redo || this.sortedReverseCol == col && redo  )
			return this.SortReverse(col, redo);


		if( col != this.sortedCol && !redo) {
			this.page = 1;
		}


		this.AddDownChar(col);

		this.sortedCol = col;
		this.sortedReverseCol = -1;
		row_array = [];
        /*rows = this.tableBody.rows;
        for (var i=0; i<rows.length; i++) {
          row_array[row_array.length] = [this.GetInnerText(rows[i].cells[col]), rows[i]];
        }*/
        rows = this.rowObjects;
        for (var i=0; i<rows.length; i++) {
          row_array[row_array.length] = [this.GetInnerText(rows[i].childNodes[col]), rows[i]];
        }


        /* If you want a stable sort, uncomment the following line */
        //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
        /* and comment out this one */

        var sortFunc = this.sortTypes[col];

		//this.shaker_sort(row_array, sortFunc);

        row_array.sort(sortFunc);

		this.Clear();
		var start = this.GetStartPageIndex();
		var end = this.GetEndPageIndex();


        for (var j=start; j<row_array.length && j<=end; j++) {
          this.tableBody.appendChild(row_array[j][1]);
        }

        delete row_array;
	},

	SortReverse: function(col, redo) {

		if( col != this.sortedReverseCol && !redo) {
			this.page = 1;
		}


		this.AddUpChar(col);

		this.sortedReverseCol = col;
		this.sortedCol = -1;

		row_array = [];
        rows = this.rowObjects;
        for (var i=0; i<rows.length; i++) {
          row_array[row_array.length] = [this.GetInnerText(rows[i].childNodes[col]), rows[i]];
        }

        var sortFunc = this.sortTypes[col];

		this.shaker_sort(row_array, sortFunc);
		//       row_array.sort(sortFunc);

		this.Clear();

		var start = this.items.length - this.GetStartPageIndex() - 1;
		var end = this.items.length - this.GetEndPageIndex() - 1;


        for (var j=start; j>=0 && j>=end; j--) {
          this.tableBody.appendChild(row_array[j][1]);
        }

        delete row_array;

	},


	shaker_sort: function(list, comp_func) {
		// A stable sort function to allow multi-level sorting of data
		// see: http://en.wikipedia.org/wiki/Cocktail_sort
		// thanks to Joseph Nahmias
		var b = 0;
		var t = list.length - 1;
		var swap = true;

		while(swap) {
		    swap = false;
		    for(var i = b; i < t; ++i) {
		        if ( comp_func(list[i], list[i+1]) > 0 ) {
		            var q = list[i]; list[i] = list[i+1]; list[i+1] = q;
		            swap = true;
		        }
		    } // for
		    t--;

		    if (!swap) break;

		    for(var i = t; i > b; --i) {
		        if ( comp_func(list[i], list[i-1]) < 0 ) {
		            var q = list[i]; list[i] = list[i-1]; list[i-1] = q;
		            swap = true;
		        }
		    } // for
		    b++;

		} // while(swap)
	},

	GetCellValue: function(row, col) {

	},

	SortAlpha: function(a,b) {
		if (a[0]==b[0]) return 0;
		if (a[0]<b[0]) return -1;
		return 1;
	},

	SortNumber: function(a,b) {
		aa = parseFloat(a[0].replace(/[^0-9.-]/g,''));
	    if (isNaN(aa)) aa = 0;
	    bb = parseFloat(b[0].replace(/[^0-9.-]/g,''));
	    if (isNaN(bb)) bb = 0;
	    return aa-bb;
	},

	GetInnerText: function(node) {
	    // gets the text we want to use for sorting for a cell.
	    // strips leading and trailing whitespace.
	    // this is *not* a generic getInnerText function; it's special to sorttable.
	    // for example, you can override the cell text with a customkey attribute.
	    // it also gets .value for <input> fields.

	    hasInputs = (typeof node.getElementsByTagName == 'function') &&
	                 node.getElementsByTagName('input').length;

	    if (node.getAttribute("sortkey") != null) {
	      return node.getAttribute("sortkey");
	    }
	    else if (typeof node.textContent != 'undefined' && !hasInputs) {
	      return node.textContent.replace(/^\s+|\s+$/g, '');
	    }
	    else if (typeof node.innerText != 'undefined' && !hasInputs) {
	      return node.innerText.replace(/^\s+|\s+$/g, '');
	    }
	    else if (typeof node.text != 'undefined' && !hasInputs) {
	      return node.text.replace(/^\s+|\s+$/g, '');
	    }
	    else {
	      switch (node.nodeType) {
	        case 3:
	          if (node.nodeName.toLowerCase() == 'input') {
	            return node.value.replace(/^\s+|\s+$/g, '');
	          }
	        case 4:
	          return node.nodeValue.replace(/^\s+|\s+$/g, '');
	          break;
	        case 1:
	        case 11:
	          var innerText = '';
	          for (var i = 0; i < node.childNodes.length; i++) {
	            innerText += sorttable.getInnerText(node.childNodes[i]);
	          }
	          return innerText.replace(/^\s+|\s+$/g, '');
	          break;
	        default:
	          return '';
	      }
	    }
	  }

});

/*================================================
The manual page table is a special type of javascript table
that does not do all of its sorting or paging locally.
Instead it changes the page, using urls to specify the
current page.
=================================================*/
var ManualPageTable = Class.create(PatentTable, {

	/*================================================
	Override the ussual paging settings since we will
	be doing things manually. This will allow the table
	to specify the current and max page, since we can
	assume we havn't been provided with all the data
	upfront
	=================================================*/
	SetPageData: function(page, maxpage) {
		this.page = page;
		this.maxpage = maxpage;
		this.usePaging = true;
		this.rowsPerPage = 1000;
	},

	/*================================================
	Sets sort information - the name of the column
	already sorted and if it is sorted asc or desc.
	=================================================*/
	SetSortData: function(sorted, order) {

		for ( var i = 0 ; i < this.tableHead.rows[0].cells.length ; i++ ) {
			var header = this.tableHead.rows[0].cells[i];
			if (typeof(header.getAttribute("sorttype")) != "undefined") {
	    		var sorttype = header.getAttribute("sorttype");
				if(sorttype == sorted ) {
					if(order == "asc" ) {
						this.sortedCol = i;
						this.AddDownChar(i);
					}
					else {
						this.sortedReverseCol = i;
						this.AddUpChar(i);
					}
					break;
				}
			}
		}
	},

	/*================================================
	Override automatted paging calculations
	=================================================*/
	CalculatePagingInfo: function() {

	},
	/*================================================
	Called when user hits the next page button
	=================================================*/
	NextPage: function() {
		if(this.page >= this.maxpage )
			return;
		this.page++;
		this.ChangePage();
	},
	/*================================================
	Called when user hits the prev page button
	=================================================*/
	PrevPage: function() {
		if(this.page <= 1 )
			return;
		this.page--;
		this.ChangePage();

	},
	/*================================================
	Called when clicks ont he first page
	=================================================*/
	FirstPage: function() {
		this.page = 1;
		this.ChangePage();
	},
	/*================================================
	Called when user clicks on the last page button
	=================================================*/
	LastPage: function() {
		this.page = this.maxpage;
		this.ChangePage();
	},
	/*================================================
	Called when user clicks header. Detects what
	column they want to sort by, if the column is already
	sorted and must be sorted in the oppsoite direction.
	=================================================*/
	OnHeaderClick: function(event) {
		var data = $A(arguments);
		data.shift();

		var col = data[0];

		this.activeSortedCol = col;

		if(this.sortedCol == col /*|| this.sortedReverseCol == col */)
			return this.SortReverse(col);
		if( col != this.sortedCol ) {
			this.page = 1;
		}

		this.AddDownChar(col);

		this.sortedCol = col;
		this.sortedReverseCol = -1;

		//SEND AJAX REQUEST HERE
		this.ChangePage();
	},
	/*================================================
	Orders a column to be sorted in reverse order
	=================================================*/
	SortReverse: function(col) {
		if( col != this.sortedReverseCol) {
			this.page = 1;
		}

		this.AddUpChar(col);

		this.sortedReverseCol = col;
		this.sortedCol = -1;

		//SEND AJAX REQUEST HERE
		this.ChangePage();
	},
	/*================================================
	Fads the table to signal the user that the page
	will be reloading soon
	=================================================*/
	Fade: function() {
		for ( var i = 0 ; i < this.tableBody.rows.length ; i++ ) {
			this.tableBody.rows[i].setOpacity(.5);
		}
	},
	/*================================================
	Changes the page / sort order by causing a page
	refresh
	=================================================*/
	ChangePage: function() {

		this.Fade();

		var sort = "number";
		var order = "asc";

		var header = null;
		if( this.sortedCol != -1 ) {
			header =  this.tableHead.rows[0].cells[this.sortedCol];
			order = "asc";
		}
		else if ( this.sortedReverseCol != -1) {
			header =  this.tableHead.rows[0].cells[this.sortedReverseCol];
			order = "desc";
		}
		if (header != null && typeof(header.getAttribute("sorttype")) != "undefined") {
	    	sort = header.getAttribute("sorttype");
	    }

		document.location = Site.SiteRoot + Site.Url + "/"+this.page+"/"+sort+"/"+order;
	}
});

/*================================================
A simple patent table with a name, number, assnged to, and status column
=================================================*/
var NormalPatentTable = Class.create(ManualPageTable, {

	GetRow: function(i) {

		var item = this.items[i];

		var row = Builder.node('tr',{style:"cursor:pointer"});

		row.appendChild(Builder.node('td',{},item.name));
		row.appendChild(Builder.node('td',{className:"center"},item.patentnumber));
		row.appendChild(Builder.node('td',{className:"center"},item.assignedto));

		var image = Builder.node('img',{style:'vertical-align:middle'});
		if(item.complete == 1 )
			image.src = Site.SiteRoot + "images/closed.png";
		else
			image.src = Site.SiteRoot + "images/open.png";
		row.appendChild(Builder.node('td',{className:"center"},image));


		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);

		var url = Site.SiteRoot + "Patents/Patent/" + item.patentnumber;
		Event.observe(row,'click', this.OnRowClick.bindAsEventListener(this, url));

		return row;
	}
});

/*================================================
The rated patent table shows a special manually sorted
patent table with a name, number, assigned to, rating, and status
column.

It also has special ajax support for the assigned to, rating
and status columsn that allow them to be edited via ajax
at run time
=================================================*/
var RatedPatentTable = Class.create(ManualPageTable, {

	/*================================================
	Generates a single row for the table
	=================================================*/
	GetRow: function(i) {
		//get the item that we are putting into this row
		var item = this.items[i];

		//generate the row element
		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{});
		var link = Builder.node('a',{href:Site.SiteRoot+"Patents/View/"+item.patentnumber,className:"tooltip",tooltip:item.abstract});
		link.innerHTML = item.name;
		//name.innerHTML = "<a href='"+Site.SiteRoot+"Patents/View/"+item.patentnumber+"'>"+item.name+"</a>";
		name.appendChild(link);
		row.appendChild(name);

		//create the patent number cell
		var number = Builder.node('td',{className:"center"},item.patentnumber);
		number.innerHTML = "<a href='"+Site.SiteRoot+"Patents/View/"+item.patentnumber+"'>"+item.patentnumber+"</a>";
		row.appendChild(number);

		//create the assigned to cell
		//we make this one clickable. When a user clicks on it, we change the content
		//to show a drop down.
		var assigned = Builder.node('td',{className:"center"},item.assignedname)
		Event.observe(assigned,'click', this.OnAssignedClick.bindAsEventListener(this, i));
		row.appendChild(assigned);

		//create the rating cell.
		//We make this one clickable. When a user clicks on it, we change the content
		//to show a drop down
		if(item.rating == -1 )
			item.rating = "";
		var rating = Builder.node('td',{className:"center"},item.rating);
		Event.observe(rating,'click', this.OnRatingClick.bindAsEventListener(this, i));
		row.appendChild(rating);

		//generate the status cell. We place an image in here
		//based on the status
		var image = Builder.node('img',{style:'vertical-align:middle'});
		if(item.complete == 1 )
			image.src = Site.SiteRoot + "images/closed.png";
		else
			image.src = Site.SiteRoot + "images/open.png";
		var status = Builder.node('td',{className:"center"},image);
		Event.observe(status,'click', this.OnStatusClick.bindAsEventListener(this, i));
		row.appendChild(status);

		//add mouse over event handlers so we can highlight rows as the
		//mouse movers
		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);

		//var url = Site.SiteRoot + "Patents/Patent/" + item.patentnumber;
		//Event.observe(row,'click', this.OnRowClick.bindAsEventListener(this, url));

		//return the generated row
		return row;
	},

	HasAccess: function(item) {
		if( Patents.UserGroup == "Admin" )
			return true;

		if( Patents.UserID != 0 && item.assignedto == Patents.UserID )
			return true;

		return false;
	},

	/*================================================
	Called when a user clicks a status cell - toggles
	a patents status between open / closed
	=================================================*/
	OnStatusClick: function(e) {

		//figure out what row was clicked
		var data = $A(arguments);
		data.shift();
		var i = data[0];

		if(!this.HasAccess(this.items[i]))
			return;

		var newcomplete;
		var image;

		var cell = Event.element(e);
		if( cell.tagName == "IMG" ) {
			cell = cell.parentNode;
		}

		cell.innerHTML = "";

		if (this.items[i].complete == 0) {
			image = Builder.node('img',{style:'vertical-align:middle',src:Site.SiteRoot + "images/closed.png"});
			cell.appendChild(image);
			newcomplete = 1;
		}
		else if(this.items[i].complete == 1 ) {
			image = Builder.node('img',{style:'vertical-align:middle',src:Site.SiteRoot + "images/open.png"});
			cell.appendChild(image);
			newcomplete = 0;
		}

		var patent = this.items[i].id;
		this.items[i].complete = newcomplete;
		Util.AjaxRequest("SetStatus","patent="+patent+"&status="+newcomplete,this.OnStatusCallback);

	},

	/*================================================
	Called after ajax update processed for patent status
	=================================================*/
	OnStatusCallback: function() {

	},

	/*================================================
	Called when user clicks no a assigned to or rating
	cell. This remembers the cell, makes sure its a valid
	cell for editing, and removes any editing drop down
	from other cells. This keeps only 1 cell in 'edit'
	mode at a time. Returns 'true' if the click is valid
	and should be followed with an edit box being generated
	or false.
	=================================================*/
	HandleCellClick: function(e, row) {


		//get the element that was clicked.
		el = Event.element(e);

		//if a cell wasn't clicked, ignore the event
		if(el.tagName != "TD") {
			return false;
		}
		//if the cell clicked was the one we already are working
		//on, ignore this event
		if (this.selectedCell == el) {
			return false;
		}

		//if we already had another cell selected undo
		//any changes that were made to it
		if(this.selectedCell != -1 ) {
			this.selectedCell.innerHTML = this.oldcontent;
		}

		//remember our now highlighted row and cell
		this.selectedCell = el;
		this.selectedRow = row;
		this.oldcontent = el.innerHTML;

		//return true - this was a valid click
		return true;
	},

	/*================================================
	Called when user clicks an assigned cell. Replaces
	it with a drop down box
	=================================================*/
	OnAssignedClick: function(e) {

		//figure out what row was clicked
		var data = $A(arguments);
		data.shift();
		var row = data[0];

		if(Patents.UserGroup != "Admin")
			return;

		//make sure the click was valid
		var validClick = this.HandleCellClick(e, row);
		if(!validClick)
			return;

		var el = this.selectedCell;

		//now genreate a select box

		//this will be used to select the defualt selection for the drop down
		var defaultSelection = 0;

		//make select
		var select = Builder.node('select',{className:"center",style:"width:150px"});

		//make default option
		var option = Builder.node('option',{value:0},"Unassigned");
		select.appendChild(option);

		//add each user as an option
		for ( var i = 0 ; i < this.users.length ; i++ ) {
			if(this.items[this.selectedRow].assignedto == this.users[i].id) {
				defaultSelection = i+1;
			}
			var option = Builder.node('option',{value:this.users[i].id},this.users[i].lastname+", "+this.users[i].firstname);
			select.appendChild(option);
		}

		//observe when the user selects someone
		Event.observe(select,'change', this.OnAssignedSelect.bindAsEventListener(this, el));

		//set the new content
		el.innerHTML = "";
		el.appendChild(select);

		//push the select box to the default selection
		select.selectedIndex = defaultSelection;
	},

	/*================================================
	Called when user selects a user from an assigned drop down
	Remembers the selection and saves it via a ajax call
	=================================================*/
	OnAssignedSelect: function(e) {

		//get the selection box
		el = Event.element(e);

		//find out information - who was selected, what patent was selected
		var patentid = this.items[this.selectedRow].id;
		var userid = el.options[el.selectedIndex].value;
		var user = this.GetUser(userid);

		//generate new content
		if(userid == 0 )
			this.selectedCell.innerHTML = "-";
		else
			this.selectedCell.innerHTML = user.lastname+", "+user.firstname;
		this.oldcontent = this.selectedCell.innerHTML;

		//update our objects
		this.items[this.selectedRow].assignedto = user.id;

		//send the update via an ajax request
		Util.AjaxRequest("SetAssignedUser","patent="+patentid+"&user="+userid,this.OnAssignedCallback);

		this.selectedRow = -1;
		this.selectedCell = -1;
	},

	/*================================================
	Called after on assigned ajax request is processed
	=================================================*/
	OnAssignedCallback: function() {
	},

	/*================================================
	Called when a user clicks on a rating cell. Replaces
	it with a drop down
	=================================================*/
	OnRatingClick: function(e) {

		//figure out what row was clicked
		var data = $A(arguments);
		data.shift();
		var row = data[0];

		if(!this.HasAccess(this.items[row]))
			return;

		//make sure the click was valid
		var validClick = this.HandleCellClick(e, row);

		if(!validClick)
			return;

		var el = this.selectedCell;

		//now genreate a select box

		//this will be used to select the defualt selection for the drop down
		var defaultSelection = 0;

		//make select
		var select = Builder.node('select',{className:"center",style:"width:80px"});

		//make default option
		var option = Builder.node('option',{value:""},"-");
		select.appendChild(option);

		//add each user as an option
		for ( var i = 0 ; i < 6 ; i++ ) {
			if(this.items[this.selectedRow].rating == i && this.items[this.selectedRow].rating!="") {
				defaultSelection = i+1;
			}
			var option = Builder.node('option',{value:i},i+"");
			select.appendChild(option);
		}

		//observe when the user selects someone
		Event.observe(select,'change', this.OnRatingSelect.bindAsEventListener(this, el));

		//set the new content
		el.innerHTML = "";
		el.appendChild(select);

		//push the select box to the default selection
		select.selectedIndex = defaultSelection;
	},
	/*================================================
	Called when a rating is selected from a drop down. Makes
	an ajax call so it can be changed on the code behind page.
	=================================================*/
	OnRatingSelect: function(e) {

		//get the selection box
		el = Event.element(e);

		//find out information - who was selected, what patent was selected
		var patentid = this.items[this.selectedRow].id;
		var rating = el.options[el.selectedIndex].value;

		//generate new content
		this.selectedCell.innerHTML = rating;
		this.oldcontent = this.selectedCell.innerHTML;

		//update our objects
		this.items[this.selectedRow].rating = rating;

		//send the update via an ajax request
		Util.AjaxRequest("SetRating","patent="+patentid+"&rating="+rating,this.OnRatingCallback);

		this.selectedRow = -1;
		this.selectedCell = -1;
	},

	OnRatingCallback: function() {
	},

	/*================================================
	Utility method - given a user id returns the user instance
	from the user list stored internally.
	=================================================*/
	GetUser: function(userid) {
		for( var i = 0 ; i < this.users.length ; i ++ ) {
			if(this.users[i].id == userid )
				return this.users[i];
		}
		return this.users[0];
	},
	/*================================================
	Adds a user to the list of users that this table
	must know about for the assigned to drop downs
	=================================================*/
	AddUser: function(user) {
		this.users.push(user);
	}
});

