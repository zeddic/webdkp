/*=====================================================

======================================================*/
Site = new (function() {
  //The root path of the site, such as http://www.site.com/
  this.SiteRoot = "http://webdkp.com/";
})();

var Util = new (function() {
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
})();

WebDKP = new (function() {
  //this.Server = "";

  this.MainUrl = "http://www.webdkp.com/";
  this.LoadCount = 0;
  this.Table = null;
  this.Tables = [];
  this.LoadQueue = [];
  this.Loading = false;

  this.Init = function(guild, server) {
    //

    /*this.Server = server;
		this.Guild = guild;
		this.ServerUrl = this.Server.replace(/ /g,"+");
		this.GuildUrl = this.Guild.replace(/ /g,"+");
		this.BaseUrl = Site.SiteRoot+"dkp/"+this.ServerUrl+"/"+this.GuildUrl+"/";*/

    this.Setup();
  };

  this.AddOnLoad = function(functionPointer) {
    var oldonload = window.onload;
    if (typeof oldonload == "function") {
      window.onload = function() {
        oldonload();
        functionPointer();
      };
    } else {
      window.onload = functionPointer;
    }
  };

  this.ClearSpaces = function(data) {
    return data.replace(/ /g, "+");
  };

  this.Setup = function() {
    WebDKP.AddOnLoad(WebDKP.SetupOnLoad);
  };

  this.SetupOnLoad = function() {
    WebDKP.Table = document.getElementById("webdkp");
    WebDKP.LoadJavascript("js/scriptaculous/lib/prototype.js", true);
    WebDKP.LoadJavascript("js/scriptaculous/src/builder.js", true);
    WebDKP.LoadJavascript("js/dkp.js", true);
    WebDKP.LoadJavascript("js/test.js", true);
    /*DKP.SetupWowStats();
		DKP.SetupTooltips();
		DKP.SetupButtons();
		DKP.SetupSimpleTables();*/
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
    Event.observe(link, "click", DKP.DownloadData);
  };

  this.SetupNoItemFoundLink = function(link) {
    link.setAttribute(
      "tooltip",
      "Item data not available. Either Wowhead was busy or the item does not exist. Click to try loading data again."
    );
    link.setAttribute("icon", "INV_Misc_QuestionMark");
    Event.observe(link, "click", DKP.DownloadData);
  };

  this.SetupTooltips = function() {
    var links = $$("a.tooltip");
    for (var i = 0; i < links.size(); i++) {
      DKP.SetupTooltip(links[i]);
    }
  };

  this.SetupTooltip = function(element) {
    Event.observe(element, "mouseover", DKP.TooltipOver);
    Event.observe(element, "mousemove", DKP.TooltipMove);
    Event.observe(element, "mouseout", DKP.TooltipOut);
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
      Event.observe(links[i], "mouseover", DKP.ButtonOver);
      Event.observe(links[i], "mouseout", DKP.ButtonOut);
    }
  };

  this.SetupSimpleTables = function() {
    var tables = $$("table.simpletable");
    for (var i = 0; i < tables.size(); i++) {
      table = new DKPTable(tables[i].id);
      table.DrawSimple();
    }
  };

  this.LoadCSS = function(toload) {
    var url = WebDKP.MainUrl + toload;
    var headTag = document.getElementsByTagName("head")[0];
    var style = document.createElement("style");
    style.type = "text/css";
    style.href = url;
    headTag.appendChild(style);
  };

  this.LoadJavascript = function(toload, wait) {
    var waitForLoad = false;
    if (typeof wait != "undefined") waitForLoad = wait;

    WebDKP.LoadCount++;
    var url = WebDKP.MainUrl + toload;
    var headTag = document.getElementsByTagName("head")[0];
    var script = document.createElement("script");
    script.id = WebDKP.LoadCount;
    script.type = "text/javascript";
    script.src = url;

    if (!waitForLoad) {
      headTag.appendChild(script);
    } else {
      WebDKP.LoadQueue.push(script);
      if (WebDKP.LoadQueue.length == 1 && !this.Loading) {
        WebDKP.ProcessQueue();
      }
    }
  };

  this.ProcessQueue = function() {
    if (this.LoadQueue.length == 0) {
      this.Loading = false;
      return;
    }

    this.Loading = true;

    var script = this.LoadQueue[0];
    this.LoadQueue.splice(0, 1);

    var headTag = document.getElementsByTagName("head")[0];
    headTag.appendChild(script);

    if (
      /msie/i.test(navigator.userAgent) &&
      !/AppleWebKit\/([^ ]*)/.test(navigator.userAgent) &&
      !/opera/i.test(navigator.userAgent)
    ) {
      // If this is IE, watch the last script's ready state.
      script.onreadystatechange = function() {
        if (this.readyState === "loaded" || this.readyState === "complete") {
          WebDKP.LoadComplete();
        }
      };
    } else {
      script = document.createElement("script");
      script.appendChild(document.createTextNode("WebDKP.LoadComplete()"));
      document.body.appendChild(script);
    }
  };

  this.LoadComplete = function() {
    WebDKP.ProcessQueue();
  };

  this.AddTable = function(table) {
    this.Tables.push(table);
  };

  this.SetupTable = function() {
    Builder.node("span");

    var table = Builder.node("table", {
      className: "dkp",
      cellpadding: 0,
      cellspacing: 0,
      id: "webdkptable"
    });
    var thead = Builder.node("thead");
    var header = Builder.node("tr", { className: "header" });
    header.appendChild(
      Builder.node("th", { className: "link" }, Builder.node("a", {}, "name"))
    );
    header.appendChild(
      Builder.node(
        "th",
        { className: "link", style: "width:100px" },
        Builder.node("a", {}, "class")
      )
    );
    header.appendChild(
      Builder.node(
        "th",
        { className: "link", style: "width:100px" },
        Builder.node("a", {}, "dkp")
      )
    );
    header.appendChild(
      Builder.node(
        "th",
        { className: "link", style: "width:100px" },
        Builder.node("a", {}, "lifetime")
      )
    );

    thead.appendChild(header);

    var tbody = Builder.node("tbody");

    table.appendChild(thead);
    table.appendChild(tbody);

    var dropdown = WebDKP.GetTableDropdown();

    $("webdkp").appendChild(dropdown);
    $("webdkp").appendChild(table);

    table = new RemotePointsTable("webdkptable");
    //table.EnablePaging(100);
    //table.Add({"dkp":"70","lifetime":"166.67","player":"Abstinence3","playerguild":"Moonlight Dancers","playerclass":"Druid"});
    //table.Add({"dkp":"33.29","lifetime":"46.67","player":"Accendere","playerguild":"Blades of Lordaeron","playerclass":"Druid"});
    //table.DrawOnLoad();
  };

  this.GetTableDropdown = function() {
    var select = Builder.node("select", {}, "");
    select.style.width = "200px";
    for (var i = 0; i < WebDKP.Tables.length; i++) {
      select.appendChild(
        Builder.node(
          "option",
          { value: WebDKP.Tables[i].tableid },
          WebDKP.Tables[i].name
        )
      );
    }

    //select.appendChild(Builder.node('option',{},"Death Knight"));
    return select;
  };
})();

WebDKP.Init("<?=serverUrlName?>", "<?=guildUrlName?>");
