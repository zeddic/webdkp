DKP = new (function() {
  this.Server = "";
  this.Guild = "";
  this.ServerUrl = "";
  this.GuildUrl = "";
  this.BaseUrl = "";

  this.Init = function(server, guild) {
    this.Server = server;
    this.Guild = guild;
    this.ServerUrl = this.Server.replace(/ /g, "+");
    this.GuildUrl = this.Guild.replace(/ /g, "+");
    this.BaseUrl =
      Site.SiteRoot + "dkp/" + this.ServerUrl + "/" + this.GuildUrl + "/";
    window.addEventListener("load", DKP.SetupOnLoad);
  };

  this.SetupOnLoad = function() {
    DKP.SetupWowStats();
    DKP.SetupTooltips();
    DKP.SetupButtons();
    DKP.SetupSimpleTables();
  };

  this.SetupWowStats = function() {
    var links = $$("a.noitemdata");
    for (var i = 0; i < links.size(); i++) {
      DKP.SetupNoItemDataLink(links[i]);
    }
    var links = $$("a.itemnotfound");
    for (var i = 0; i < links.size(); i++) {
      DKP.SetupNoItemFoundLink(links[i]);
    }
  };

  this.SetupNoItemDataLink = function(link) {
    link.setAttribute("tooltip", "Click to download stats from Wowhead");
    link.setAttribute("icon", "INV_Misc_QuestionMark");
    link.addEventListener("click", DKP.DownloadData);
  };

  this.SetupNoItemFoundLink = function(link) {
    link.setAttribute(
      "tooltip",
      "Item data not available. Either Wowhead was busy or the item does not exist. Click to try loading data again."
    );
    link.setAttribute("icon", "INV_Misc_QuestionMark");
    link.addEventListener("click", DKP.DownloadData);
  };

  this.SetupTooltips = function() {
    var links = $$("a.tooltip");
    for (var i = 0; i < links.size(); i++) {
      DKP.SetupTooltip(links[i]);
    }
  };

  this.SetupTooltip = function(element) {
    element.addEventListener("mouseover", DKP.TooltipOver);
    element.addEventListener("mousemove", DKP.TooltipMove);
    element.addEventListener("mouseout", DKP.TooltipOut);
  };

  this.TooltipOver = function(event) {
    var element = event.element();
    if (element.getAttribute("tooltip") != null) {
      var tooltip = element.getAttribute("tooltip");
      var icon = "";
      if (element.getAttribute("icon") != null) {
        icon = element.getAttribute("icon");
        $WowheadPower.showTooltip(event, tooltip, icon);
      } else {
        $WowheadPower.showTooltip(event, tooltip);
      }
    }
  };

  this.TooltipOut = function(event) {
    $WowheadPower.hideTooltip(event);
  };

  this.TooltipMove = function(event) {
    $WowheadPower.moveTooltip(event);
  };

  this.DownloadData = function(event) {
    var link = event.element();

    var itemname = link.innerHTML;
    link.innerHTML = "Loading...";
    new Ajax.Request(Site.SiteRoot + "ajax/loaditem", {
      method: "post",
      parameters: "name=" + itemname,
      onSuccess: function(transport) {
        var temp = Builder.node("span");
        temp.innerHTML = transport.responseText;
        var newlink = temp.firstChild;
        if (newlink.hasClassName("noitemdata"))
          DKP.SetupNoItemDataLink(newlink);
        else if (newlink.hasClassName("itemnotfound"))
          DKP.SetupNoItemFoundLink(newlink);
        if (newlink.hasClassName("tooltip")) DKP.SetupTooltip(newlink);
        link.parentNode.insertBefore(newlink, link);
        link.parentNode.removeChild(link);
      }
    });
  };

  this.ButtonOver = function(event) {
    //var element = event.element();
    this.addClassName("dkpbuttonover");
  };

  this.ButtonOut = function(event) {
    //var element = event.element();
    this.removeClassName("dkpbuttonover");
  };

  this.SetupButtons = function() {
    var links = $$("a.dkpbutton");

    for (var i = 0; i < links.size(); i++) {
      links[i].addEventListener("mouseover", DKP.ButtonOver);
      links[i].addEventListener("mouseout", DKP.ButtonOut);
    }
  };

  this.SetupSimpleTables = function() {
    var tables = $$("table.simpletable");
    for (var i = 0; i < tables.size(); i++) {
      table = new DKPTable(tables[i].id);
      table.DrawSimple();
    }
  };
})();

var DKPTable = Class.create({
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
    this.firstRow = null;
    this.lastRow = null;
    this.headersHooked = false;
    this.pageLinksCreated = false;
    this.rowObjects = [];
  },

  EnablePaging: function(rowsPerPage) {
    this.usePaging = true;
    this.rowsPerPage = rowsPerPage;
    this.CalculatePagingInfo();
  },

  CalculatePagingInfo: function() {
    this.page = 1;
    this.maxpage = Math.floor(this.items.length / this.rowsPerPage) + 1;
    if (this.maxpage == 0) this.maxpage = 1;
  },

  CheckPageBarVisibility: function() {
    var els = $$("div." + this.tableName + "_pagebar");
    for (var i = 0; i < els.size(); i++) {
      if (this.maxpage == 1) els[i].hide();
      else els[i].show();
    }
  },

  Add: function(item) {
    this.items.push(item);
  },

  DrawSimple: function() {
    for (var i = 0; i < this.tableBody.rows.length; i++) {
      var row = this.tableBody.rows[i];
      row.addEventListener("mouseover", this.OnRowOver);
      row.addEventListener("mouseout", this.OnRowOut);
    }
  },

  Draw: function() {
    this.CalculatePagingInfo();
    window.addEventListener("load", () => this.DrawOnLoad());
  },

  Redraw: function() {
    this.Sort(this.activeSortedCol, true);
  },

  Clear: function() {
    //clear all rows other than the first row and the last row
    for (var i = 0; i < this.tableBody.rows.length; i++) {
      if (
        this.tableBody.rows[i] != this.firstRow &&
        this.tableBody.rows[i] != this.lastRow
      ) {
        this.tableBody.removeChild(this.tableBody.rows[i]);
        i--;
      }
    }
  },

  Erase: function() {
    this.Clear();
    this.items = [];
    //this.sortTypes = [];
    this.sortedCol = -1;
    this.sortedReverseCol = -1;
    this.activeSortedCol = 0;
    //this.usePaging = false;
    //this.rowsPerPage = 25;
    this.page = 1;
    this.maxpage = 1;
    this.firstRow = null;
    this.lastRow = null;
    this.rowObjects = [];
  },

  DrawOnLoad: function() {
    this.HookHeaders();
    this.DrawPageButtons();
    this.CheckPageBarVisibility();
    this.firstRow = this.GetFirstRow();
    if (this.firstRow != null) this.tableBody.appendChild(this.firstRow);
    for (var i = 0; i < this.items.length; i++) {
      row = this.GetRow(i);
      if (!row) continue;
      this.items[i].deleted = false;
      this.rowObjects[i] = row;
      if (this.usePaging && i >= this.rowsPerPage) continue;
      //row_array[row_array.length] = [this.GetInnerText(rows[i].cells[col]), rows[i]]
      this.tableBody.appendChild(row);
    }
    this.lastRow = this.GetLastRow();
    if (this.lastRow != null) this.tableBody.appendChild(this.lastRow);

    DKP.SetupWowStats();
    DKP.SetupTooltips();
    DKP.SetupButtons();
    Util.Hide("TableLoading");
  },

  GetPageButton: function(name) {
    var content;
    if (name == "first") content = "&laquo; First";
    else if (name == "last") content = "Last &raquo;";
    else if (name == "left") content = "< Prev";
    else if (name == "right") content = "Next >";
    var button = Builder.node("a", {
      href: "javascript:;",
      className: "pagebutton"
    });
    button.innerHTML = content;
    return button;
  },

  UpdatePageText: function() {
    var els = $$("div.pagedata");
    for (var i = 0; i < els.size(); i++) {
      els[i].innerHTML = "Page <b>" + this.page + "</b> of " + this.maxpage;
    }
  },

  GeneratePageBar: function() {
    var container = Builder.node("div", {
      style: "padding:2px",
      className: this.tableName + "_pagebar"
    });
    var pageButtons = Builder.node("div", { style: "float:right" });
    var extraButtons = this.GetExtraPageButtons();
    if (extraButtons != "") {
      pageButtons.appendChild(extraButtons);
    }

    // First page
    var first = this.GetPageButton("first");
    pageButtons.appendChild(first);
    first.addEventListener("click", () => this.FirstPage());
    pageButtons.appendChild(Builder.node("span", {}, " "));

    // Prev page
    var prev = this.GetPageButton("left");
    pageButtons.appendChild(prev);
    prev.addEventListener("click", () => this.PrevPage());
    pageButtons.appendChild(Builder.node("span", {}, " "));

    // Next page
    var next = this.GetPageButton("right");
    pageButtons.appendChild(next);
    next.addEventListener("click", () => this.NextPage());
    pageButtons.appendChild(Builder.node("span", {}, " "));

    // Last page
    var last = this.GetPageButton("last");
    pageButtons.appendChild(last);
    last.addEventListener("click", () => this.LastPage());

    container.appendChild(pageButtons);
    var count = Builder.node("div", { className: "pagedata" });
    count.innerHTML = "Page <b>" + this.page + "</b> of " + this.maxpage;
    container.appendChild(count);
    return container;
  },

  GetExtraPageButtons: function() {
    return "";
  },

  DrawPageButtons: function() {
    if (!this.usePaging || this.pageLinksCreated) return;
    this.pageLinksCreated = true;
    var top = this.GeneratePageBar();
    var bottom = this.GeneratePageBar();
    this.table.parentNode.insertBefore(top, this.table);
    this.table.parentNode.insertBefore(bottom, this.table.nextSibling);
  },

  OnPageIconOver: function(event) {
    var el = event.element();
    var pathParts = el.src.split("/");
    var file = pathParts[pathParts.length - 1];
    file = file.replace(".gif", "");
    el.src = Site.SiteRoot + "images/page/" + file + "-over.gif";
  },

  OnPageIconOut: function(event) {
    var el = event.element();
    var pathParts = el.src.split("/");
    var file = pathParts[pathParts.length - 1];
    file = file.replace("-over.gif", "");
    el.src = Site.SiteRoot + "images/page/" + file + ".gif";
  },

  NextPage: function() {
    if (this.page >= this.maxpage) return;
    this.page++;
    this.Redraw();
    this.UpdatePageText();
  },

  PrevPage: function() {
    if (this.page <= 1) return;
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
    if (!this.usePaging) return 0;
    return (this.page - 1) * this.rowsPerPage;
  },

  GetEndPageIndex: function() {
    if (!this.usePaging) return this.items.length;
    return this.page * this.rowsPerPage - 1;
  },

  OnHeaderClick: function(col) {
    this.Sort(col);
  },

  HookHeaders: function() {
    if (this.headersHooked) return;
    this.headersHooked = true;
    var row = this.tableHead.rows[0];
    for (let i = 0; i < row.cells.length; i++) {
      if (!Element.hasClassName(row.cells[i], "nosort")) {
        var sortby = this.SortAlpha;
        if (row.cells[i].getAttribute("sort") != null) {
          var sortName = row.cells[i].getAttribute("sort");
          sortby = this.GetSortFunc(sortName);
        }
        this.sortTypes[i] = sortby;

        row.cells[i].onselectstart = function() {
          return false;
        };
        row.cells[i].unselectable = "on";
        row.cells[i].style.MozUserSelect = "none";
        row.cells[i].style.cursor = "default";
        row.cells[i].addEventListener("click", () => this.OnHeaderClick(i));
      }
    }
  },

  GetSortFunc: function(name) {
    switch (name) {
      case "number":
        return this.SortNumber;
      case "string":
        return this.SortAlpha;
    }
    return this.SortAlpha;
  },

  OnRowOver: function(event) {
    this.addClassName("over");
  },

  OnRowOut: function(event) {
    this.removeClassName("over");
  },

  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(Builder.node("td", {}, "..."));
    row.appendChild(Builder.node("td", { className: "center" }, "..."));
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  },

  GetLastRow: function() {
    return null;
  },

  GetFirstRow: function() {
    return null;
  },

  RecreateRow: function(i) {
    var row = this.GetRow(i);
    this.rowObjects[i] = row;
  },

  OnRowClick: function(i) {
    var data = $A(arguments);
    data.shift();
    var url = data[0];
    document.location = url;
  },

  AddUpChar: function(col) {
    var char = Prototype.Browser.IE
      ? '&nbsp<font face="webdings">5</font>'
      : "&nbsp;&#x25B4;";
    this.AddCharToCol(col, char);
  },

  AddDownChar: function(col) {
    var char = Prototype.Browser.IE
      ? '&nbsp<font face="webdings">6</font>'
      : "&nbsp;&#x25BE;";
    this.AddCharToCol(col, char);
  },

  AddCharToCol: function(col, char) {
    this.RemoveCharFromCol(col);

    var toadd = Builder.node("span", { id: "sortchar_" + this.tableName });
    toadd.innerHTML = char;
    var cell = this.tableHead.rows[0].cells[col];
    var el = cell.firstChild;
    el.appendChild(toadd);
  },

  RemoveCharFromCol: function(col) {
    var cell = this.tableHead.rows[0].cells[col];
    var toDelete = document.getElementById("sortchar_" + this.tableName);
    if (toDelete) {
      toDelete.parentNode.removeChild(toDelete);
    }
  },

  OnSort: function() {},

  Sort: function(col, redo) {
    this.OnSort();
    this.activeSortedCol = col;
    if (typeof redo != "undefined") redo = true;
    else redo = false;
    if (
      (this.sortedCol == col && !redo) ||
      (this.sortedReverseCol == col && redo)
    )
      return this.SortReverse(col, redo);

    if (col != this.sortedCol && !redo) {
      this.page = 1;
    }

    this.AddDownChar(col);
    this.sortedCol = col;
    this.sortedReverseCol = -1;
    row_array = [];
    rows = this.rowObjects;
    for (var i = 0; i < rows.length; i++) {
      if (this.ShouldShowRow(i) && !this.items[i].deleted) {
        row_array[row_array.length] = [
          this.GetInnerText(rows[i].childNodes[col]),
          rows[i]
        ];
      }
    }
    var sortFunc = this.sortTypes[col];
    //this.shaker_sort(row_array, sortFunc);
    row_array.sort(sortFunc);
    this.Clear();
    var start = this.GetStartPageIndex();
    var end = this.GetEndPageIndex();
    //if(this.firstRow != null )
    //  this.tableBody.appendChild(this.firstRow);
    for (var j = start; j < row_array.length && j <= end; j++) {
      //if(this.firstRow != null ) {
      //  this.tableBody.insertBefore(row_array[j][1], this.firstRow.nextSibling);
      //}
      //else {
      this.tableBody.appendChild(row_array[j][1]);
      //}
    }
    //if(this.lastRow != null )
    //  this.tableBody.appendChild(this.lastRow);
    delete row_array;
  },

  ShouldShowRow: function(i) {
    return true;
  },

  SortReverse: function(col, redo) {
    if (col != this.sortedReverseCol && !redo) {
      this.page = 1;
    }

    this.AddUpChar(col);
    this.sortedReverseCol = col;
    this.sortedCol = -1;
    row_array = [];
    rows = this.rowObjects;
    for (var i = 0; i < rows.length; i++) {
      if (this.ShouldShowRow(i) && !this.items[i].deleted) {
        row_array[row_array.length] = [
          this.GetInnerText(rows[i].childNodes[col]),
          rows[i]
        ];
      }
    }
    var sortFunc = this.sortTypes[col];
    this.shaker_sort(row_array, sortFunc);
    //       row_array.sort(sortFunc);
    this.Clear();
    var start = this.items.length - this.GetStartPageIndex() - 1;
    var end = this.items.length - this.GetEndPageIndex() - 1;
    //if(this.firstRow != null )
    //  this.tableBody.appendChild(this.firstRow);
    for (var j = start; j >= 0 && j >= end; j--) {
      if (j < row_array.length) {
        //if(this.firstRow != null ) {
        //  this.tableBody.insertBefore(row_array[j][1], this.firstRow.nextSibling);
        //}
        //else {
        this.tableBody.appendChild(row_array[j][1]);
        //}
      }
    }
    //if(this.lastRow != null )
    //  this.tableBody.appendChild(this.lastRow);
    delete row_array;
  },

  shaker_sort: function(list, comp_func) {
    // A stable sort function to allow multi-level sorting of data
    // see: http://en.wikipedia.org/wiki/Cocktail_sort
    // thanks to Joseph Nahmias
    var b = 0;
    var t = list.length - 1;
    var swap = true;
    while (swap) {
      swap = false;
      for (var i = b; i < t; ++i) {
        if (comp_func(list[i], list[i + 1]) > 0) {
          var q = list[i];
          list[i] = list[i + 1];
          list[i + 1] = q;
          swap = true;
        }
      } // for
      t--;
      if (!swap) break;
      for (var i = t; i > b; --i) {
        if (comp_func(list[i], list[i - 1]) < 0) {
          var q = list[i];
          list[i] = list[i - 1];
          list[i - 1] = q;
          swap = true;
        }
      } // for
      b++;
    } // while(swap)
  },

  GetCellValue: function(row, col) {},

  SortAlpha: function(a, b) {
    if (a[0] == b[0]) return 0;
    if (a[0] < b[0]) return -1;
    return 1;
  },

  SortNumber: function(a, b) {
    aa = parseFloat(a[0].replace(/[^0-9.-]/g, ""));
    if (isNaN(aa)) aa = 0;
    bb = parseFloat(b[0].replace(/[^0-9.-]/g, ""));
    if (isNaN(bb)) bb = 0;
    return aa - bb;
  },

  GetInnerText: function(node) {
    // gets the text we want to use for sorting for a cell.
    // strips leading and trailing whitespace.
    // this is *not* a generic getInnerText function; it's special to sorttable.
    // for example, you can override the cell text with a customkey attribute.
    // it also gets .value for <input> fields.
    hasInputs =
      typeof node.getElementsByTagName == "function" &&
      node.getElementsByTagName("input").length;
    if (node.getAttribute("sortkey") != null) {
      return node.getAttribute("sortkey");
    } else if (typeof node.textContent != "undefined" && !hasInputs) {
      return node.textContent.replace(/^\s+|\s+$/g, "");
    } else if (typeof node.innerText != "undefined" && !hasInputs) {
      return node.innerText.replace(/^\s+|\s+$/g, "");
    } else if (typeof node.text != "undefined" && !hasInputs) {
      return node.text.replace(/^\s+|\s+$/g, "");
    } else {
      switch (node.nodeType) {
        case 3:
          if (node.nodeName.toLowerCase() == "input") {
            return node.value.replace(/^\s+|\s+$/g, "");
          }
        case 4:
          return node.nodeValue.replace(/^\s+|\s+$/g, "");
          break;
        case 1:
        case 11:
          var innerText = "";
          for (var i = 0; i < node.childNodes.length; i++) {
            innerText += sorttable.getInnerText(node.childNodes[i]);
          }
          return innerText.replace(/^\s+|\s+$/g, "");
          break;
        default:
          return "";
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
var ManualPageTable = Class.create(DKPTable, {
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
    this.url = this.GetUrl();
  },

  /*================================================
  Sets the url that should be used when switching
  between pages and sort orders
  =================================================*/
  GetUrl: function() {
    return DKP.BaseUrl + "Awards/";
  },

  /*================================================
  Sets sort information - the name of the column
  already sorted and if it is sorted asc or desc.
  =================================================*/
  SetSortData: function(sorted, order) {
    this.sortString = sorted;
    this.orderString = order;
    for (var i = 0; i < this.tableHead.rows[0].cells.length; i++) {
      var header = this.tableHead.rows[0].cells[i];
      if (typeof header.getAttribute("sorttype") != "undefined") {
        var sorttype = header.getAttribute("sorttype");
        if (sorttype == sorted) {
          if (order == "asc") {
            this.sortedCol = i;
            this.AddDownChar(i);
          } else {
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
  CalculatePagingInfo: function() {},

  /*================================================
  Called when user hits the next page button
  =================================================*/
  NextPage: function() {
    if (this.page >= this.maxpage) return;
    this.page++;
    this.ChangePage();
  },

  /*================================================
  Called when user hits the prev page button
  =================================================*/
  PrevPage: function() {
    if (this.page <= 1) return;
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
  OnHeaderClick: function(col) {
    this.activeSortedCol = col;
    if (this.sortedCol == col /*|| this.sortedReverseCol == col */)
      return this.SortReverse(col);
    if (col != this.sortedCol) {
      this.page = 1;
    }
    this.AddDownChar(col);
    this.sortedCol = col;
    this.sortedReverseCol = -1;
    this.ChangePage();
  },
  /*================================================
  Orders a column to be sorted in reverse order
  =================================================*/
  SortReverse: function(col) {
    if (col != this.sortedReverseCol) {
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
    for (var i = 0; i < this.tableBody.rows.length; i++) {
      if (this.tableBody.rows[i].setOpacity)
        this.tableBody.rows[i].setOpacity(0.5);
    }
  },
  /*================================================
  Changes the page / sort order by causing a page
  refresh
  =================================================*/
  ChangePage: function() {
    this.Fade();
    var sort = "date";
    var order = "desc";
    var header = null;
    if (this.sortedCol != -1) {
      header = this.tableHead.rows[0].cells[this.sortedCol];
      order = "asc";
    } else if (this.sortedReverseCol != -1) {
      header = this.tableHead.rows[0].cells[this.sortedReverseCol];
      order = "desc";
    }
    if (
      header != null &&
      typeof header.getAttribute("sorttype") != "undefined"
    ) {
      sort = header.getAttribute("sorttype");
    }
    document.location = this.url + this.page + "/" + sort + "/" + order;
    //document.location = Site.SiteRoot + Site.Url +
  }
});
var ServerTable = Class.create(DKPTable, {
  GetRow: function(i) {
    var row = Builder.node("tr");
    var url = Site.SiteRoot + "dkp/" + this.items[i].urlname;
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node("a", { href: url }, this.items[i].name)
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].total)
    );

    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var GuildsTable = Class.create(DKPTable, {
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node("a", { href: this.items[i].url }, this.items[i].name)
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].faction)
    );
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var PointsTable = Class.create(ManualPageTable, {
  GetUrl: function() {
    return DKP.BaseUrl;
  },
  SetShowData: function(showLifetime, showTiers) {
    this.showLifetime = showLifetime;
    this.showTiers = showTiers;
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    //var link = ;
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node(
          "a",
          { href: DKP.BaseUrl + "Player/" + this.items[i].player },
          this.items[i].player
        )
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].playerguild)
    );
    var img = Builder.node("img", {
      src:
        Site.SiteRoot +
        "images/classes/small/" +
        this.items[i].playerclass +
        ".gif"
    });
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].playerclass },
        img
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].dkp + "")
    );
    if (this.showLifetime) {
      row.appendChild(
        Builder.node("td", { className: "center" }, this.items[i].lifetime)
      );
    }
    if (this.showTiers) {
      row.appendChild(
        Builder.node("td", { className: "center" }, this.items[i].tier)
      );
    }

    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var RemotePointsTable = Class.create(DKPTable, {
  SetShowData: function(showLifetime, showTiers) {
    this.showLifetime = showLifetime;
    this.showTiers = showTiers;
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node(
          "a",
          {
            href:
              Site.SiteRoot + WebDKP.BaseUrl + "Player/" + this.items[i].player,
            target: "WebDKP"
          },
          this.items[i].player
        )
      )
    );
    var img = Builder.node("img", {
      src:
        Site.SiteRoot +
        "images/classes/small/" +
        this.items[i].playerclass +
        ".gif"
    });
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].playerclass },
        img
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].dkp + "")
    );
    if (this.showLifetime)
      row.appendChild(
        Builder.node("td", { className: "center" }, this.items[i].lifetime)
      );
    if (this.showTiers)
      row.appendChild(
        Builder.node("td", { className: "center" }, this.items[i].tier)
      );

    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var PlayerLootTable = Class.create(DKPTable, {
  SetCanEdit: function(canedit) {
    this.canedit = canedit;
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        { sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    var cell = Builder.node("td");
    cell.innerHTML = this.items[i].name;
    row.appendChild(cell);
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].points + "")
    );
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var PlayerHistoryTable = Class.create(ManualPageTable, {
  SetCanEdit: function(canedit) {
    this.canedit = canedit;
  },
  SetPlayerInfo: function(playername, playerid, dkp) {
    this.playername = playername;
    this.playerid = playerid;
    this.dkp = dkp;
    this.runningTotal = parseFloat(dkp).toFixed(2);
  },
  GetUrl: function() {
    return DKP.BaseUrl + "Player/" + this.playername + "/";
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        { sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    var name = Builder.node("td", {}, "");
    if (this.items[i].foritem == 1) name.innerHTML = this.items[i].name;
    else
      name.appendChild(
        Builder.node(
          "a",
          { href: DKP.BaseUrl + "Award/" + this.items[i].id },
          this.items[i].name
        )
      );
    row.appendChild(name);
    //var cell = Builder.node('td');
    //cell.innerHTML = this.items[i].name;
    //row.appendChild(cell);
    if (this.items[i].points > 0) {
      row.appendChild(
        Builder.node(
          "td",
          { className: "center" },
          "+" + this.items[i].points + ""
        )
      );
      row.appendChild(Builder.node("td", {}, ""));
    } else {
      row.appendChild(Builder.node("td", {}, ""));
      row.appendChild(
        Builder.node("td", { className: "center" }, this.items[i].points + "")
      );
    }
    row.appendChild(
      Builder.node("td", { className: "center" }, this.runningTotal)
    );
    var num = parseFloat(this.items[i].points).toFixed(2);
    this.runningTotal -= num;
    this.runningTotal = this.runningTotal.toFixed(2);
    if (this.canedit) {
      var actions = Builder.node("td", { className: "center" });
      //edit link
      var url =
        DKP.BaseUrl +
        "Admin/EditAward/" +
        this.items[i].id +
        "?b=p&pid=" +
        this.playerid +
        "&p=" +
        this.page +
        "&s=" +
        this.sortString +
        "&o=" +
        this.orderString;
      var editImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/edit.png",
        style: "vertical-align:text-bottom"
      });
      var editLink = Builder.node(
        "a",
        { href: url, className: "dkpbutton" },
        editImg
      );
      actions.appendChild(editLink);
      //delete link
      url =
        DKP.BaseUrl +
        "Player/" +
        this.playername +
        "/" +
        this.page +
        "/" +
        this.sortString +
        "/" +
        this.orderString +
        "?event=deleteHistory&historyid=" +
        this.items[i].historyid;
      var deleteImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/delete.png",
        style: "vertical-align:text-bottom"
      });
      var deleteLink = Builder.node(
        "a",
        {
          href: url,
          className: "dkpbutton",
          onclick: "return confirm('Delete Award?')"
        },
        deleteImg
      );
      //append
      actions.appendChild(deleteLink);
      row.appendChild(actions);
    }
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var AwardTable = Class.create(ManualPageTable, {
  SetCanEdit: function(canedit) {
    this.canedit = canedit;
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node(
          "a",
          { href: DKP.BaseUrl + "Award/" + this.items[i].id },
          this.items[i].name
        )
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].points + "")
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].players + "")
    );
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    if (this.canedit) {
      var actions = Builder.node("td", { className: "center" });
      //edit link
      var url =
        DKP.BaseUrl +
        "Admin/EditAward/" +
        this.items[i].id +
        "?b=a&p=" +
        this.page +
        "&s=" +
        this.sortString +
        "&o=" +
        this.orderString;
      var editImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/edit.png",
        style: "vertical-align:text-bottom"
      });
      var editLink = Builder.node(
        "a",
        { href: url, className: "dkpbutton" },
        editImg
      );
      actions.appendChild(editLink);

      //delete link
      url =
        DKP.BaseUrl +
        "Awards/" +
        this.page +
        "/" +
        this.sortString +
        "/" +
        this.orderString +
        "?event=deleteAward&awardid=" +
        this.items[i].id;
      var deleteImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/delete.png",
        style: "vertical-align:text-bottom"
      });
      var deleteLink = Builder.node(
        "a",
        {
          href: url,
          className: "dkpbutton",
          onclick: "return confirm('Delete Award?')"
        },
        deleteImg
      );
      //append
      actions.appendChild(deleteLink);
      row.appendChild(actions);
    }
    //row highligting
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});

var RemoteAwardTable = Class.create(DKPTable, {
  GetRow: function(i) {
    var row = Builder.node("tr");
    row.appendChild(
      Builder.node(
        "td",
        {},
        Builder.node(
          "a",
          {
            href: Site.SiteRoot + WebDKP.BaseUrl + "Award/" + this.items[i].id
          },
          this.items[i].name
        )
      )
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].points + "")
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].players + "")
    );
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});

var LootTable = Class.create(ManualPageTable, {
  SetCanEdit: function(canedit) {
    this.canedit = canedit;
  },
  GetUrl: function() {
    return DKP.BaseUrl + "Loot/";
  },
  GetRow: function(i) {
    var row = Builder.node("tr");
    var cell = Builder.node("td");
    cell.innerHTML = this.items[i].name;
    row.appendChild(cell);
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].points + "")
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].player + "")
    );
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    if (this.canedit) {
      var actions = Builder.node("td", { className: "center" });

      //edit link
      var url =
        DKP.BaseUrl +
        "Admin/EditAward/" +
        this.items[i].id +
        "?b=l&p=" +
        this.page +
        "&s=" +
        this.sortString +
        "&o=" +
        this.orderString;
      var editImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/edit.png",
        style: "vertical-align:text-bottom"
      });
      var editLink = Builder.node(
        "a",
        { href: url, className: "dkpbutton" },
        editImg
      );
      actions.appendChild(editLink);
      //delete link
      url =
        DKP.BaseUrl +
        "Loot/" +
        this.page +
        "/" +
        this.sortString +
        "/" +
        this.orderString +
        "?event=deleteAward&awardid=" +
        this.items[i].id;
      var deleteImg = Builder.node("img", {
        src: Site.SiteRoot + "images/buttons/delete.png",
        style: "vertical-align:text-bottom"
      });
      var deleteLink = Builder.node(
        "a",
        {
          href: url,
          className: "dkpbutton",
          onclick: "return confirm('Delete Award?')"
        },
        deleteImg
      );
      //append
      actions.appendChild(deleteLink);
      row.appendChild(actions);
    }
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var RemoteLootTable = Class.create(DKPTable, {
  GetRow: function(i) {
    var row = Builder.node("tr");
    var cell = Builder.node("td");
    cell.innerHTML = this.items[i].name;
    row.appendChild(cell);
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].points + "")
    );
    row.appendChild(
      Builder.node("td", { className: "center" }, this.items[i].player + "")
    );
    row.appendChild(
      Builder.node(
        "td",
        { className: "center", sortkey: this.items[i].date },
        this.items[i].datestring
      )
    );
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }
});
var ViewLootTable = Class.create(DKPTable, {
  /*================================================
  Generates a single row for the table
  =================================================*/
  GetRow: function(i) {
    //get the item that we are putting into this row
    var item = this.items[i];
    //generate the row element
    var row = Builder.node("tr", {}, "");
    //create the name cell
    var name = Builder.node("td");
    name.innerHTML = this.items[i].name;
    row.appendChild(name);
    //create the cost cell
    var cost = Builder.node("td", { className: "center" }, item.cost);
    row.appendChild(cost);
    //add mouse over event handlers so we can highlight rows as the
    //mouse movers
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    //return the generated row
    return row;
  }
});
var CheckPlayerTable = Class.create(DKPTable, {
  /*================================================
  Generates a single row for the table
  =================================================*/
  GetRow: function(i) {
    if (i % 5 != 0) return null;
    //generate the row element
    var row = Builder.node("tr", {}, "");
    var classname = "";
    for (let j = i; j < i + 5; j++) {
      var cell;
      classname = "playerSelectCell ";
      if (j < this.items.length) {
        if (this.items[j].checked) {
          classname += "selected";
        }
        var input = Builder.node(
          "input",
          {
            name: "users[]",
            value: this.items[j].id,
            type: "checkbox",
            style: "vertical-align:bottom"
          },
          ""
        );
        if (this.items[j].checked) input.checked = true;
        this.items[j].input = input;
        cell = Builder.node("td", { className: classname }, [
          input,
          " ",
          this.items[j].name
        ]);
        this.items[j].cell = cell;
        cell.addEventListener("click", () => this.OnItemClick(cell, j));
      } else {
        cell = Builder.node("td", {}, "");
      }
      row.addEventListener("mouseover", this.OnRowOver);
      row.addEventListener("mouseout", this.OnRowOut);
      row.appendChild(cell);
    }
    return row;
  },
  OnItemClick: function(element, i) {
    input = this.items[i].input;
    cell = this.items[i].cell;
    if (element.tagName == "INPUT") {
      if (input.checked) cell.addClassName("selected");
      else cell.removeClassName("selected");
    } else {
      if (input.checked) {
        input.checked = false;
        cell.removeClassName("selected");
      } else {
        input.checked = true;
        cell.addClassName("selected");
      }
    }
  }
});
