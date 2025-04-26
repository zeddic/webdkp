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
    // DKP.SetupTooltips();
    DKP.SetupButtons();
    DKP.SetupSimpleTables();
  };

  this.SetupWowStats = function() {
    var links = document.querySelectorAll("a.noitemdata");
    for (let link of links) {
      DKP.SetupNoItemDataLink(link);
    }
    var links = document.querySelectorAll("a.itemnotfound");
    for (let link of links) {
      DKP.SetupNoItemFoundLink(link);
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
    var links = document.querySelectorAll("a.tooltip");
    for (let link of links) {
      DKP.SetupTooltip(link);
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
    $.ajax(Site.SiteRoot + "ajax/loaditem", {
      method: "post",
      data: {
        name: itemname
      },
      success: function(transport) {
        const temp = $("<span>").html(transport.responseText);
        const newlink = temp[0].firstChild;
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
    $(event.currentTarget).addClass("dkpbuttonover");
  };

  this.ButtonOut = function(event) {
    $(event.currentTarget).removeClass("dkpbuttonover");
  };

  this.SetupButtons = function() {
    var links = document.querySelectorAll("a.dkpbutton");
    for (let link of links) {
      link.addEventListener("mouseover", DKP.ButtonOver);
      link.addEventListener("mouseout", DKP.ButtonOut);
    }
  };

  this.SetupSimpleTables = function() {
    var tables = document.querySelectorAll("table.simpletable");
    for (let tableEl of tables) {
      table = new DKPTable(tableEl.id);
      table.DrawSimple();
    }
  };
})();

class DKPTable {
  constructor(name) {
    this.tableName = name;
    this.table = $(`#${name}`);
    this.tableBody = this.table.find("tbody");
    this.tableHead = this.table.find("thead");
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
  }

  EnablePaging(rowsPerPage) {
    this.usePaging = true;
    this.rowsPerPage = rowsPerPage;
    this.CalculatePagingInfo();
  }

  CalculatePagingInfo() {
    this.page = 1;
    this.maxpage = Math.floor(this.items.length / this.rowsPerPage) + 1;
    if (this.maxpage == 0) this.maxpage = 1;
  }

  CheckPageBarVisibility() {
    var els = document.querySelectorAll(`div.${this.tableName}_pagebar`);
    for (let el of els) {
      el.style.display = this.maxpage === 1 ? "none" : "";
    }
  }

  Add(item) {
    this.items.push(item);
  }

  DrawSimple() {
    for (var i = 0; i < this.tableBody.rows.length; i++) {
      var row = this.tableBody.rows[i];
      row.addEventListener("mouseover", this.OnRowOver);
      row.addEventListener("mouseout", this.OnRowOut);
    }
  }

  Draw() {
    this.CalculatePagingInfo();
    window.addEventListener("load", () => this.DrawOnLoad());
  }

  Redraw() {
    this.Sort(this.activeSortedCol, true);
  }

  Clear() {
    //clear all rows other than the first row and the last row
    const first = this.firstRow && this.firstRow[0];
    const last = this.lastRow && this.lastRow[0];

    for (let i = 0; i < this.tableBody[0].rows.length; i++) {
      if (
        this.tableBody[0].rows[i] !== first &&
        this.tableBody[0].rows[i] !== last
      ) {
        // Note: We use detach() instead of remove() so the
        // jquery event listeners are not lost.
        $(this.tableBody[0].rows[i]).detach();
        i--;
      }
    }
  }

  Erase() {
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
    this.rowObjects.forEach(r => r.remove());
    this.rowObjects = [];
  }

  DrawOnLoad() {
    this.HookHeaders();
    this.DrawPageButtons();
    this.CheckPageBarVisibility();
    this.firstRow = this.GetFirstRow();
    if (this.firstRow != null) this.tableBody.append(this.firstRow);
    for (var i = 0; i < this.items.length; i++) {
      const row = this.GetRow(i);
      if (!row) continue;
      this.items[i].deleted = false;
      this.rowObjects[i] = row;
      if (this.usePaging && i >= this.rowsPerPage) continue;
      this.tableBody.append(row);
    }
    this.lastRow = this.GetLastRow();
    if (this.lastRow != null) this.tableBody.append(this.lastRow);

    DKP.SetupWowStats();
    DKP.SetupTooltips();
    DKP.SetupButtons();
    Util.Hide("TableLoading");
  }

  GetPageButton(name) {
    var content;
    if (name == "first") content = "&laquo; First";
    else if (name == "last") content = "Last &raquo;";
    else if (name == "left") content = "< Prev";
    else if (name == "right") content = "Next >";

    const button = $("<a>", { href: "javascript:;" })
      .addClass("pagebutton")
      .html(content);

    return button;
  }

  UpdatePageText() {
    var els = document.querySelectorAll("div.pagedata");
    for (let el of els) {
      el.innerHTML = `Page <b>${this.page}</b> of ${this.maxpage}`;
    }
  }

  GeneratePageBar() {
    const container = $("<div>")
      .addClass(`${this.tableName}_pagebar`)
      .css("padding", "2px");

    const pageButtons = $("<div>").css("float", "right");

    const extraButtons = this.GetExtraPageButtons();
    if (!!extraButtons) {
      pageButtons.append(extraButtons);
    }

    // First page
    const first = this.GetPageButton("first");
    pageButtons.append(first);
    $(first).on("click", () => this.FirstPage());
    pageButtons.append($("<span>").text(" "));

    // Prev page
    const prev = this.GetPageButton("left");
    pageButtons.append(prev);
    $(prev).on("click", () => this.PrevPage());
    pageButtons.append($("<span>").text(" "));

    // Next page
    const next = this.GetPageButton("right");
    pageButtons.append(next);
    $(next).on("click", () => this.NextPage());
    pageButtons.append($("<span>").text(" "));

    // Last page
    const last = this.GetPageButton("last");
    pageButtons.append(last);
    $(last).on("click", () => this.LastPage());

    container.append(pageButtons);

    const count = $("<div>")
      .addClass("pagedata")
      .html(`Page <b>${this.page}</b> of ${this.maxpage}`);

    container.append(count);
    return container;
  }

  GetExtraPageButtons() {
    return "";
  }

  DrawPageButtons() {
    if (!this.usePaging || this.pageLinksCreated) return;
    this.pageLinksCreated = true;
    var top = this.GeneratePageBar();
    var bottom = this.GeneratePageBar();

    top.insertBefore(this.table);
    bottom.insertAfter(this.table);
  }

  OnPageIconOver(event) {
    var el = event.element();
    var pathParts = el.src.split("/");
    var file = pathParts[pathParts.length - 1];
    file = file.replace(".gif", "");
    el.src = Site.SiteRoot + "images/page/" + file + "-over.gif";
  }

  OnPageIconOut(event) {
    var el = event.element();
    var pathParts = el.src.split("/");
    var file = pathParts[pathParts.length - 1];
    file = file.replace("-over.gif", "");
    el.src = Site.SiteRoot + "images/page/" + file + ".gif";
  }

  NextPage() {
    if (this.page >= this.maxpage) return;
    this.page++;
    this.Redraw();
    this.UpdatePageText();
  }

  PrevPage() {
    if (this.page <= 1) return;
    this.page--;
    this.Redraw();
    this.UpdatePageText();
  }

  FirstPage() {
    this.page = 1;
    this.Redraw();
    this.UpdatePageText();
  }

  LastPage() {
    this.page = this.maxpage;
    this.Redraw();
    this.UpdatePageText();
  }

  GetStartPageIndex() {
    if (!this.usePaging) return 0;
    return (this.page - 1) * this.rowsPerPage;
  }

  GetEndPageIndex() {
    if (!this.usePaging) return this.items.length;
    return this.page * this.rowsPerPage - 1;
  }

  OnHeaderClick(col) {
    this.Sort(col);
  }

  HookHeaders() {
    if (this.headersHooked) return;
    this.headersHooked = true;
    var row = this.tableHead[0].rows[0];
    for (let i = 0; i < row.cells.length; i++) {
      if (!$(row.cells[i]).hasClass("nosort")) {
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
  }

  GetSortFunc(name) {
    switch (name) {
      case "number":
        return this.SortNumber;
      case "string":
        return this.SortAlpha;
    }
    return this.SortAlpha;
  }

  OnRowOver(event) {
    $(event.currentTarget).addClass("over");
  }

  OnRowOut(event) {
    $(event.currentTarget).removeClass("over");
  }

  GetRow(i) {
    const row = $("<tr>");
    row.append($("<td>", { text: "..." }));
    row.addEventListener("mouseover", this.OnRowOver);
    row.addEventListener("mouseout", this.OnRowOut);
    return row;
  }

  GetLastRow() {
    return null;
  }

  GetFirstRow() {
    return null;
  }

  RecreateRow(i) {
    var row = this.GetRow(i);
    this.rowObjects[i] = row;
  }

  OnRowClick(i) {
    var data = $A(arguments);
    data.shift();
    var url = data[0];
    document.location = url;
  }

  AddUpChar(col) {
    const char = "&nbsp;&#x25B4;";
    this.AddCharToCol(col, char);
  }

  AddDownChar(col) {
    const char = "&nbsp;&#x25BE;";
    this.AddCharToCol(col, char);
  }

  AddCharToCol(col, char) {
    this.RemoveCharFromCol(col);

    var toadd = $("<span>", { id: `sortchar_${this.tableName}` });
    toadd.html(char);
    var cell = this.tableHead[0].rows[0].cells[col];
    var el = cell.firstChild;
    el.appendChild(toadd[0]);
  }

  RemoveCharFromCol(col) {
    var cell = this.tableHead[0].rows[0].cells[col];
    var toDelete = document.getElementById("sortchar_" + this.tableName);
    if (toDelete) {
      toDelete.parentNode.removeChild(toDelete);
    }
  }

  OnSort() {}

  Sort(col, redo) {
    this.OnSort();
    this.activeSortedCol = col;
    if (typeof redo != "undefined") redo = true;
    else redo = false;
    if (
      (this.sortedCol == col && !redo) ||
      (this.sortedReverseCol == col && redo)
    ) {
      return this.SortReverse(col, redo);
    }

    if (col != this.sortedCol && !redo) {
      this.page = 1;
    }

    this.AddDownChar(col);
    this.sortedCol = col;
    this.sortedReverseCol = -1;
    let row_array = [];
    let rows = this.rowObjects;
    for (let i = 0; i < rows.length; i++) {
      if (this.ShouldShowRow(i) && !this.items[i].deleted) {
        row_array.push([
          this.GetInnerText(rows[i][0].childNodes[col]),
          rows[i]
        ]);
      }
    }
    var sortFunc = this.sortTypes[col];
    row_array.sort(sortFunc);
    this.Clear();
    var start = this.GetStartPageIndex();
    var end = this.GetEndPageIndex();
    for (let j = start; j < row_array.length && j <= end; j++) {
      this.tableBody.append(row_array[j][1]);
    }
  }

  ShouldShowRow(i) {
    return true;
  }

  SortReverse(col, redo) {
    if (col != this.sortedReverseCol && !redo) {
      this.page = 1;
    }

    this.AddUpChar(col);
    this.sortedReverseCol = col;
    this.sortedCol = -1;
    let row_array = [];
    let rows = this.rowObjects;
    for (let i = 0; i < rows.length; i++) {
      if (this.ShouldShowRow(i) && !this.items[i].deleted) {
        row_array[row_array.length] = [
          this.GetInnerText(rows[i][0].childNodes[col]),
          rows[i]
        ];
      }
    }
    var sortFunc = this.sortTypes[col];
    this.shaker_sort(row_array, sortFunc);
    this.Clear();
    var start = this.items.length - this.GetStartPageIndex() - 1;
    var end = this.items.length - this.GetEndPageIndex() - 1;

    for (let j = start; j >= 0 && j >= end; j--) {
      if (j < row_array.length) {
        this.tableBody.append(row_array[j][1]);
      }
    }
  }

  shaker_sort(list, comp_func) {
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
  }

  GetCellValue(row, col) {}

  SortAlpha(a, b) {
    if (a[0] == b[0]) return 0;
    if (a[0] < b[0]) return -1;
    return 1;
  }

  SortNumber(a, b) {
    const aa = parseFloat(a[0].replace(/[^0-9.-]/g, ""));
    if (isNaN(aa)) aa = 0;
    const bb = parseFloat(b[0].replace(/[^0-9.-]/g, ""));
    if (isNaN(bb)) bb = 0;
    return aa - bb;
  }

  GetInnerText(node) {
    // gets the text we want to use for sorting for a cell.
    // strips leading and trailing whitespace.
    // this is *not* a generic getInnerText function; it's special to sorttable.
    // for example, you can override the cell text with a customkey attribute.
    // it also gets .value for <input> fields.
    const hasInputs =
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
            innerText += this.GetInnerText(node.childNodes[i]);
          }
          return innerText.replace(/^\s+|\s+$/g, "");
          break;
        default:
          return "";
      }
    }
  }
}

/*================================================
The manual page table is a special type of javascript table
that does not do all of its sorting or paging locally.
Instead it changes the page, using urls to specify the
current page.
=================================================*/
class ManualPageTable extends DKPTable {
  /*================================================
  Override the ussual paging settings since we will
  be doing things manually. This will allow the table
  to specify the current and max page, since we can
  assume we havn't been provided with all the data
  upfront
  =================================================*/
  SetPageData(page, maxpage) {
    this.page = page;
    this.maxpage = maxpage;
    this.usePaging = true;
    this.rowsPerPage = 1000;
    this.url = this.GetUrl();
  }

  /*================================================
  Sets the url that should be used when switching
  between pages and sort orders
  =================================================*/
  GetUrl() {
    return DKP.BaseUrl + "Awards/";
  }

  /*================================================
  Sets sort information - the name of the column
  already sorted and if it is sorted asc or desc.
  =================================================*/
  SetSortData(sorted, order) {
    this.sortString = sorted;
    this.orderString = order;
    for (var i = 0; i < this.tableHead[0].rows[0].cells.length; i++) {
      var header = this.tableHead[0].rows[0].cells[i];
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
  }

  /*================================================
  Override automatted paging calculations
  =================================================*/
  CalculatePagingInfo() {}

  /*================================================
  Called when user hits the next page button
  =================================================*/
  NextPage() {
    if (this.page >= this.maxpage) return;
    this.page++;
    this.ChangePage();
  }

  /*================================================
  Called when user hits the prev page button
  =================================================*/
  PrevPage() {
    if (this.page <= 1) return;
    this.page--;
    this.ChangePage();
  }

  /*================================================
  Called when clicks ont he first page
  =================================================*/
  FirstPage() {
    this.page = 1;
    this.ChangePage();
  }

  /*================================================
  Called when user clicks on the last page button
  =================================================*/
  LastPage() {
    this.page = this.maxpage;
    this.ChangePage();
  }

  /*================================================
  Called when user clicks header. Detects what
  column they want to sort by, if the column is already
  sorted and must be sorted in the oppsoite direction.
  =================================================*/
  OnHeaderClick(col) {
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
  }

  /*================================================
  Orders a column to be sorted in reverse order
  =================================================*/
  SortReverse(col) {
    if (col != this.sortedReverseCol) {
      this.page = 1;
    }
    this.AddUpChar(col);
    this.sortedReverseCol = col;
    this.sortedCol = -1;
    //SEND AJAX REQUEST HERE
    this.ChangePage();
  }

  /*================================================
  Fades the table to signal the user that the page
  will be reloading soon
  =================================================*/
  Fade() {
    for (let i = 0; i < this.tableBody[0].rows.length; i++) {
      const el = this.tableBody[0].rows[i];
      $(el).css({ opacity: 0.5 });
    }
  }

  /*================================================
  Changes the page / sort order by causing a page
  refresh
  =================================================*/
  ChangePage() {
    this.Fade();
    var sort = "date";
    var order = "desc";
    var header = null;
    if (this.sortedCol != -1) {
      header = this.tableHead[0].rows[0].cells[this.sortedCol];
      order = "asc";
    } else if (this.sortedReverseCol != -1) {
      header = this.tableHead[0].rows[0].cells[this.sortedReverseCol];
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
}

class ServerTable extends DKPTable {
  GetRow(i) {
    const row = $("<tr>");
    const url = Site.SiteRoot + "dkp/" + this.items[i].urlname;

    row.append(
      $("<td>").append(
        $("<a>")
          .attr({ href: url })
          .text(this.items[i].name)
      )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].total)
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class GuildsTable extends DKPTable {
  GetRow(i) {
    const row = $("<tr>");
    row.append(
      $("<td>").append(
        $("<a>")
          .attr({ href: this.items[i].url })
          .text(this.items[i].name)
      )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].faction)
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class PointsTable extends ManualPageTable {
  GetUrl() {
    return DKP.BaseUrl;
  }

  SetShowData(showLifetime, showTiers) {
    this.showLifetime = showLifetime;
    this.showTiers = showTiers;
  }

  GetRow(i) {
    const row = $("<tr>");

    var playerUrlName = this.items[i].player.replace(/ /g, "+");

    row.append(
      $("<td>").append(
        $("<a>")
          .attr({ href: DKP.BaseUrl + "Player/" + playerUrlName })
          .text(this.items[i].player)
      )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].playerguild)
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortKey: this.items[i].playerclass })
        .append(
          $("<img>", {
            src:
              Site.SiteRoot +
              "images/classes/small/" +
              this.items[i].playerclass +
              ".gif"
          })
        )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].dkp + "")
    );

    if (this.showLifetime) {
      row.append(
        $("<td>")
          .addClass("center")
          .text(this.items[i].lifetime + "")
      );
    }

    if (this.showTiers) {
      row.append(
        $("<td>")
          .addClass("center")
          .text(this.items[i].tier)
      );
    }

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class RemotePointsTable extends DKPTable {
  SetShowData(showLifetime, showTiers) {
    this.showLifetime = showLifetime;
    this.showTiers = showTiers;
  }

  GetRow(i) {
    const row = $("<tr>");

    row.append(
      $("<td>").append(
        $("<a>", {
          href:
            Site.SiteRoot + WebDKP.BaseUrl + "Player/" + this.items[i].player,
          target: "WebDKP"
        }).text(this.items[i].player)
      )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortkey: this.items[i].playerclass })
        .append(
          $("<img>", {
            src:
              Site.SiteRoot +
              "images/classes/small/" +
              this.items[i].playerclass +
              ".gif"
          })
        )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].dkp + "")
    );

    if (this.showLifetime) {
      row.append(
        $("<td>")
          .addClass("center")
          .text(this.items[i].lifetime)
      );
    }

    if (this.showTiers) {
      row.append(
        $("<td>")
          .addClass("center")
          .text(this.items[i].tier)
      );
    }

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class PlayerLootTable extends DKPTable {
  SetCanEdit(canedit) {
    this.canedit = canedit;
  }

  GetRow(i) {
    const row = $("<tr>");

    row.append(
      $("<td>")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    row.append($("<td>").html(this.items[i].name));

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].points + "")
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class PlayerHistoryTable extends ManualPageTable {
  SetCanEdit(canedit) {
    this.canedit = canedit;
  }

  SetPlayerInfo(playername, playerid, dkp) {
    this.playername = playername;
    this.playerid = playerid;
    this.dkp = dkp;
    this.runningTotal = parseFloat(dkp).toFixed(2);
  }

  GetUrl() {
    return DKP.BaseUrl + "Player/" + this.playername + "/";
  }

  GetRow(i) {
    const row = $("<tr>");
    row.append(
      $("<td>")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    const name = $("<td>");
    name.append(
      $("<a>")
        .attr({ href: DKP.BaseUrl + "Award/" + this.items[i].id })
        .text(this.items[i].name)
    );
    row.append(name);

    if (this.items[i].points > 0) {
      row.append(
        $("<td>")
          .addClass("center")
          .text(`+${this.items[i].points}`)
      );
      row.append($("<td>"));
    } else {
      row.append($("<td>"));
      row.append(
        $("<td>")
          .addClass("center")
          .text(this.items[i].points + "")
      );
    }

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.runningTotal)
    );

    var num = parseFloat(this.items[i].points).toFixed(2);
    this.runningTotal -= num;
    this.runningTotal = this.runningTotal.toFixed(2);
    if (this.canedit) {
      const actions = $("<td>").addClass("center");
      //edit link
      const url =
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

      const editLink = $("<a>")
        .addClass("dkpbutton")
        .attr({ href: url })
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/edit.png"
            })
            .css("vertical-align", "text-bottom")
        );

      actions.append(editLink);

      //delete link
      const deleteUrl =
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

      const deleteLink = $("<a>")
        .attr({ href: deleteUrl })
        .addClass("dkpbutton")
        .on("click", () => confirm("Delete Award?"))
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/delete.png"
            })
            .css("vertical-align", "text-bottom")
        );

      //append
      actions.append(deleteLink);
      row.append(actions);
    }

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class AwardTable extends ManualPageTable {
  SetCanEdit(canedit) {
    this.canedit = canedit;
  }

  GetRow(i) {
    const row = $("<tr>");

    row.append(
      $("<td>").append(
        $("<a>")
          .attr({
            href: DKP.BaseUrl + "Award/" + this.items[i].id
          })
          .html(this.items[i].name)
      )
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].points + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].players + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    if (this.canedit) {
      const actions = $("<td>").addClass("center");

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

      const editLink = $("<a>")
        .attr({ href: url })
        .addClass("dkpbutton")
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/edit.png"
            })
            .css("vertical-align", "text-bottom")
        );

      actions.append(editLink);

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

      const deleteLink = $("<a>")
        .attr({ href: url })
        .addClass("dkpbutton")
        .on("click", () => confirm("Delete Award?"))
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/delete.png"
            })
            .css("vertical-align", "text-bottom")
        );
      //append
      actions.append(deleteLink);
      row.append(actions);
    }
    //row highligting
    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class RemoteAwardTable extends DKPTable {
  GetRow(i) {
    const row = $("<tr>");

    row.append(
      $("<td>")
        .append("<a>")
        .attr({
          href: Site.SiteRoot + WebDKP.BaseUrl + "Award/" + this.items[i].id
        })
        .html(this.items[i].name)
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].points + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].players + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class LootTable extends ManualPageTable {
  SetCanEdit(canedit) {
    this.canedit = canedit;
  }

  GetUrl() {
    return DKP.BaseUrl + "Loot/";
  }

  GetRow(i) {
    const row = $("<tr>");
    row.append($("<td>").text(this.items[i].name));

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].points + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].player + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    if (this.canedit) {
      const actions = $("<td>").addClass("center");

      //edit link
      const url =
        DKP.BaseUrl +
        "Admin/EditAward/" +
        this.items[i].id +
        "?b=l&p=" +
        this.page +
        "&s=" +
        this.sortString +
        "&o=" +
        this.orderString;

      const editLink = $("<a>")
        .attr({ href: url })
        .addClass("dkpbutton")
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/edit.png"
            })
            .css("vertical-align", "text-bottom")
        );
      actions.append(editLink);

      //delete link
      const deleteUrl =
        DKP.BaseUrl +
        "Loot/" +
        this.page +
        "/" +
        this.sortString +
        "/" +
        this.orderString +
        "?event=deleteAward&awardid=" +
        this.items[i].id;

      const deleteLink = $("<a>")
        .attr({ href: deleteUrl })
        .addClass("dkpbutton")
        .on("click", () => confirm("Delete Award?"))
        .append(
          $("<img>")
            .attr({
              src: Site.SiteRoot + "images/buttons/delete.png"
            })
            .css("vertical-align", "text-bottom")
        );

      //append
      actions.append(deleteLink);
      row.append(actions);
    }
    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class RemoteLootTable extends DKPTable {
  GetRow(i) {
    const row = $("<tr>");
    row.append($("<td>").html(this.items[i].name));

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].points + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].player + "")
    );

    row.append(
      $("<td>")
        .addClass("center")
        .attr({ sortkey: this.items[i].date })
        .text(this.items[i].datestring)
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class ViewLootTable extends DKPTable {
  /*================================================
  Generates a single row for the table
  =================================================*/
  GetRow(i) {
    const row = $("<tr>");

    // Name
    row.append($("<td>").text(this.items[i].name));

    // Cost
    row.append(
      $("<td>")
        .addClass("center")
        .text(this.items[i].cost)
    );

    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    return row;
  }
}

class CheckPlayerTable extends DKPTable {
  /*================================================
  Generates a single row for the table
  =================================================*/
  GetRow(i) {
    if (i % 5 !== 0) return null;

    const row = $("<tr>");
    let classname = "";

    for (let j = i; j < i + 5; j++) {
      let cell;
      classname = "playerSelectCell ";
      if (j < this.items.length) {
        if (this.items[j].checked) {
          classname += "selected";
        }

        const input = $("<input>")
          .attr({
            name: "users[]",
            value: this.items[j].id,
            type: "checkbox"
          })
          .css("vertical-align", "bottom");

        if (this.items[j].checked) input[0].checked = true;
        this.items[j].input = input;

        cell = $("<td>")
          .addClass(classname)
          .append(input)
          .append(document.createTextNode(" "))
          .append(document.createTextNode(this.items[j].name));

        this.items[j].cell = cell;
        cell.on("click", e => this.OnItemClick(e, cell, j));
      } else {
        cell = $("<td>");
      }
      row.append(cell);
    }
    return row;
  }

  OnItemClick(event, element, i) {
    const input = this.items[i].input[0];
    const cell = this.items[i].cell;

    if (event.target.tagName === "INPUT") {
      if (input.checked) cell.addClass("selected");
      else cell.removeClass("selected");
    } else {
      if (input.checked) {
        input.checked = false;
        cell.removeClass("selected");
      } else {
        input.checked = true;
        cell.addClass("selected");
      }
    }
  }
}
