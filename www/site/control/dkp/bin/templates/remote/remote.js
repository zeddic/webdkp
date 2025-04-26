<?php
$url = $_SERVER["HTTP_HOST"];
$isHttps = isset($_SERVER["HTTPS"]);
$urlPrefix = $isHttps ? "https://" : "http://";
global $siteRoot;
$url = $urlPrefix . $url;
if($siteRoot != "")
  $url .= $siteRoot;
?>

/*=====================================================
Site Structure
======================================================*/
Site = new (function() {
  //The root path of the site, such as http://www.site.com/
  this.SiteRoot = "<?=$url?>";
})();
/*=====================================================
Util Library
======================================================*/
var Util = new (function() {
  /*==============================================
  Shows the element with the given id
  ===============================================*/
  this.Show = function(id) {
    $(id).show();
  }
  /*==============================================
  Hides the element with the given id
  ===============================================*/
  this.Hide = function(id) {
    $(id).hide();
  }
  /*=====================================================
  Adds a function pointer to the on page load chain
  of the current page.
  ======================================================*/
  this.AddOnLoad = function( functionPointer ) {
    var oldonload = window.onload;
    if (typeof oldonload == 'function') {
      window.onload = function () {
        oldonload();
        functionPointer();
      }
    }
    else {
      window.onload = functionPointer;
    }
  }
})();

/*=====================================================
WebDKP class - the main static class responsible
for all RemoteDKP Communication.
Thi class allows a DKP Table to be inserted into any
HTML page on any domain. It will communicate with WebDKP
to get table contents and add it to the page. Content
is loaded by adding script tags to the header - this
gets around the AJAX restriction to the same domain.

Process Steps:
1: Contact WebDKP to get a list of tables
2: Construct tabs & tables (a tab for dkp, awards, loot)
3: Contact WebDKP to get content of first DKP Table
4: Load content returned from WebDKP
5: Display

Users can switch between tabs by clicking on links.
Loaded tables are cached for quick switching between
the tabs once they have been loaded
======================================================*/
WebDKP = new (function() {
  //count used to generate a unique id for each script tag we generate
  this.LoadCount = 0;
  //a list of all the available tables
  this.Tables = [];
  //A queue of all the javascript pages to load. Only
  //one is loaded at a time to handle dependences
  this.LoadQueue = [];
  //True if a javascript file is currently being loaded
  this.Loading = false;

  /*================================================
  Initializes the class with needed data: the server
  and guild that this js file is being used by
  =================================================*/
  this.Init = function(server, guild) {
    this.Server = server;
    this.Guild = guild;
    this.ServerUrl = this.Server.replace(/ /g,"+");
    this.GuildUrl = this.Guild.replace(/ /g,"+");
    this.BaseUrl = "dkp/"+this.ServerUrl+"/"+this.GuildUrl+"/";
    this.Setup();
  }
  /*================================================
  Util, replaces + with a " " in the given string
  =================================================*/
  this.ClearSpaces = function(data)
  {
    return data.replace(/ /g,"+");
  }
  /*================================================
  Performs basic setup - this will wait for page load
  =================================================*/
  this.Setup = function() {
    Util.AddOnLoad(WebDKP.SetupOnLoad);
  }
  this.SetupOnLoad = function() {

    WebDKP.Table = document.getElementById("webdkp");

    //load the css to style our table
    WebDKP.LoadCSS(WebDKP.BaseUrl+"remote.css?view=Style<?=(isset($styleid)?"&styleid=$styleid":"")?>");

    //load needed javascript libs
    WebDKP.LoadJavascript('js/jquery-3.4.1.min.js', true);
    WebDKP.LoadJavascript('js/dkp.js', true);

    //load the list of tables. This js file will queue off our next
    //step...
    WebDKP.LoadJavascript(WebDKP.BaseUrl+"remote.js?view=Tables",true);
  }

  /*================================================
  Loads the given css file into the current page
  =================================================*/
  this.LoadCSS = function(toload) {

    var url = Site.SiteRoot + toload;
    var headTag = document.getElementsByTagName('head')[0];
    var style = document.createElement('link');
    style.type = 'text/css';
    style.href = url;
    style.rel = "stylesheet";
    headTag.appendChild(style);
  }

  /*================================================
  Loads the given js file into the current page.
  The page to load is assumed to be located on the
  WebDKP.com server
  =================================================*/
  this.LoadJavascript = function(toload, wait) {

    var waitForLoad = false;
    if( typeof(wait) != "undefined" )
      waitForLoad = wait;

    WebDKP.LoadCount++;
    var url = Site.SiteRoot + toload;

    //construct the script tag
    const headTag = document.getElementsByTagName('head')[0];
    const script = document.createElement('script');
    script.id = WebDKP.LoadCount;
    script.type = 'text/javascript';
    script.src = url;

    //if we can load now, add it to the header
    if (!waitForLoad) {
      headTag.appendChild(script);
    }
    else {
      //otherwise, add it to the process queue
      WebDKP.LoadQueue.push(script);
      //if its the only item in the queue, process it now
      if (WebDKP.LoadQueue.length == 1 && !this.Loading) {
        WebDKP.ProcessQueue();
      }
    }
  }

  /*================================================
  Processes a single javascript file to load
  =================================================*/
  this.ProcessQueue = function() {

    //make sure there is something to process
    if (this.LoadQueue.length == 0) {
      this.Loading = false;
      return;
    }

    //get the item to process
    this.Loading = true;
    var script = this.LoadQueue[0];
    this.LoadQueue.splice(0,1);

    //add it to the head tag
    var headTag = document.getElementsByTagName('head')[0];
    headTag.appendChild(script);

    //add a hoook so we now when the js file finishes loading,
    //so we can process the next item. This is different
    //between Firefox and ie.
    if ((/msie/i).test(navigator.userAgent) &&
      !(/AppleWebKit\/([^ ]*)/).test(navigator.userAgent) &&
      !(/opera/i).test(navigator.userAgent)) {
        // If this is IE, watch the last script's ready state.
        script.onreadystatechange = function () {
          if (this.readyState === 'loaded' || this.readyState == 'complete') {
            //alert(this.readyState);
            WebDKP.LoadComplete();
          }
        };
    }
    else {
      script = document.createElement('script');
      script.appendChild(document.createTextNode('WebDKP.LoadComplete()'));
      document.body.appendChild(script);
    }
  }
  /*================================================
  Called when JS load complete. Process next item in
  queue.
  =================================================*/
  this.LoadComplete = function() {
    WebDKP.ProcessQueue();
  }

  /*================================================
  Performs basic setup - this will construct the tab
  links, create the different tables, and cause
  the dkp list for the first table to be loaded.
  This load command is triggered after we recieve
  the list of available tables.
  =================================================*/
  this.SetupBasic = function() {

    //create links

    const links = $('<div>').css('padding', '5px')
      .append(
        $('<a>')
          .attr({href: '#', id: 'webdkp_dkplink'})
          .on('click', () => WebDKP.LoadDKP())
          .text('DKP')
      )
      .append(document.createTextNode(' | '))
      .append(
        $('<a>')
          .attr({href: '#', id: 'webdkp_lootlink'})
          .on('click', () => WebDKP.LoadLoot())
          .text('Loot')
      )
      .append(document.createTextNode(' | '))
      .append(
        $('<a>')
          .attr({href: '#', id: 'webdkp_awardslink'})
          .on('click', () => WebDKP.LoadAwards())
          .text('Awards')
      );

    //create dropdown (if needed)
    const dropdown = WebDKP.GetTableDropdown();
    if(dropdown[0].options.length > 1 ) {
      $("#webdkp").append(dropdown);
      $("#webdkp").append($('<br>'));
      $("#webdkp").append($('<br>'));
    }

    $("#webdkp").append(links);

    //create containers to hold the different types of information
    const dkp = $('<div>').attr({'id': 'webdkp_dkp'});
    const loot = $('<div>').attr({'id': 'webdkp_loot'}).css('display', 'none');
    const awards = $('<div>').attr({'id': 'webdkp_awards'}).css('display', 'none');
    $("#webdkp").append(dkp);
    $("#webdkp").append(loot);
    $("#webdkp").append(awards);

    //construct the tables for the different tabs
    WebDKP.SetupDKPTable();
    WebDKP.SetupLootTable();
    WebDKP.SetupAwardTable();

    //default to showing the dkp table
    dropdown.selectedIndex = 0;
    WebDKP.SetActiveTable("dkp");
  }

  /*================================================
  Displays the loot tab
  =================================================*/
  this.LoadLoot = function() {
    WebDKP.SetActiveTable("loot");
  }
  /*================================================
  Displays the dkp tab
  =================================================*/
  this.LoadDKP = function() {
    WebDKP.SetActiveTable("dkp");
  }
  /*================================================
  Displays the awards tab
  =================================================*/
  this.LoadAwards = function() {
    WebDKP.SetActiveTable("awards");
  }

  /*================================================
  Construct the dkp table
  =================================================*/
  this.SetupDKPTable = function() {
    const table = $('<table>')
        .addClass('dkp')
        .attr({
          cellpadding: 0,
          cellspacing: 0,
          id: 'webdkp_dkptable',
        });

    const thead = $('<thead>');
    const header = $('<tr>').addClass('header');
   
    // name
    $('<th>')
        .addClass('link')
        .append($('<a>').text('name'))
        .appendTo(header);

    // class
    $('<th>')
        .addClass('link center')
        .append($('<a>').text('class'))
        .appendTo(header);

    // dkp
    $('<th>')
        .addClass('link center')
        .attr({sort: 'number'})
        .css('width', '100px')
        .append($('<a>').text('dkp'))
        .appendTo(header);

    // lifetime dkp
    <?php if($settings->GetLifetimeEnabled()) { ?>
      $('<th>')
          .addClass('link center')
          .attr({sort: 'number'})
          .css('width', '100px')
          .append($('<a>').text('lifetime'))
          .appendTo(header);
    <?php } ?>

    // tiers
    <?php if($settings->GetTiersEnabled()) { ?>
      $('<th>')
          .addClass('link center')
          .attr({sort: 'number'})
          .css('width', '100px')
          .append($('<a>').text('tiers'))
          .appendTo(header);
    <?php } ?>

    thead.append(header);

    const tbody = $('<tbody>');
    table.append(thead);
    table.append(tbody);
    $("#webdkp_dkp").append(table);

    const url = "<a href='http://www.webdkp.com<?=$baseurl?>'>WebDKP.com</a>";
    const warning = $('<div>')
        .css({
            'text-align': 'center', 
            'padding': '5px',
        })
        .html(`Only the top 100 players are displayed in this table.
            For the full table, please visit ${url}.`
        );
    $("#webdkp_dkp").append(warning);

    const dkpTable = new RemotePointsTable("webdkp_dkptable");
    dkpTable.SetShowData(
        <?=($settings->GetLifetimeEnabled()?"true":"false")?>,
        <?=($settings->GetTiersEnabled()?"true":"false")?>,
    );
    dkpTable.EnablePaging(101);

    WebDKP.DKPTable = dkpTable;
    WebDKP.LoadedDKP = 0;
  }

  /*================================================
  Construct the loot table
  =================================================*/
  this.SetupLootTable = function() {
    const table = $('<table>')
        .addClass('dkp')
        .attr({
          cellpadding: 0,
          cellspacing: 0,
          id: 'webdkp_loottable',
        });
    const thead = $('<thead>');
    const header = $('<tr>').addClass('header');

    // name
    $('<th>')
        .addClass('link')
        .append($('<a>').text('loot'))
        .appendTo(header);

    // dkp
    $('<th>')
        .addClass('link center')
        .css('width', '100px')
        .append($('<a>').text('dkp'))
        .appendTo(header);

    // player
    $('<th>')
        .addClass('link center')
        .attr({sort: 'number'})
        .css('width', '100px')
        .append($('<a>').text('player'))
        .appendTo(header);

    // date
    $('<th>')
        .addClass('link center')
        .attr({sort: 'number'})
        .css('width', '200px')
        .append($('<a>').text('date'))
        .appendTo(header);

    thead.append(header);
    const tbody = $('<tbody>');
    table.append(thead);
    table.append(tbody);

    $("#webdkp_loot").append(table);

    const url = "<a href='<?=$baseurl?>Loot'>WebDKP.com</a>";
    const warning = $('<div>')
        .css({
            'text-align': 'center', 
            'padding': '5px',
        })
        .html(`Only the 50 latest awards are displayed here.
            For the full table, please visit ${url}`);
    $("#webdkp_loot").append(warning);

    const dkpTable = new RemoteLootTable("webdkp_loottable");
    dkpTable.EnablePaging(101);

    WebDKP.LootTable = dkpTable;
    WebDKP.LoadedLoot = 0;
  }

  /*================================================
  Construct the award table
  =================================================*/
  this.SetupAwardTable = function() {
    const table = $('<table>')
        .addClass('dkp')
        .attr({
          cellpadding: 0,
          cellspacing: 0,
          id: 'webdkp_awardstable',
        });

    const thead = $('<thead>');
    const header = $('<tr>').addClass('header');

    // award name
    $('<th>')
        .addClass('link')
        .append($('<a>').text('award'))
        .appendTo(header);

    // dkp
    $('<th>')
        .addClass('link center')
        .css('width', '100px')
        .append($('<a>').text('dkp'))
        .appendTo(header);

    // player
    $('<th>')
        .addClass('link center')
        .attr({sort: 'number'})
        .css('width', '100px')
        .append($('<a>').text('players'))
        .appendTo(header);

    // date
    $('<th>')
        .addClass('link center')
        .attr({sort: 'number'})
        .css('width', '200px')
        .append($('<a>').text('date'))
        .appendTo(header);

    thead.append(header);
    const tbody = $('<tbody>');
    table.append(thead);
    table.append(tbody);

    $("#webdkp_awards").append(table);

    const url = "<a href='<?=$baseurl?>Awards'>WebDKP.com</a>";
    const warning = $('<div>')
        .css({
            'text-align': 'center', 
            'padding': '5px',
        })
        .html(`Only the 50 latest awards are displayed here.
            For the full table, please visit ${url}`);
    $("#webdkp_awards").append(warning);

    const dkpTable = new RemoteAwardTable("webdkp_awardstable");
    dkpTable.EnablePaging(100);

    WebDKP.AwardsTable = dkpTable;
    WebDKP.LoadedAwards = 0;
  }

  /*================================================
  Gets the id of the currently selected table from
  the table dropdown
  =================================================*/
  this.GetTableid = function() {
    const select = WebDKP.TableSelect[0];
    const id = select.options[select.selectedIndex].value;
    return id;
  }

  /*================================================
  Sets the ative tab / table. Available options are
  "loot" "awards" or "dkp". If a cached version is
  available, it is loaded. If no cache is available,
  a request is sent to WebDKP.com
  =================================================*/
  this.SetActiveTable = function(table)
  {
    var tableid = WebDKP.GetTableid();
    var mustload = false;

    if(table == "loot" ) {
      Util.Hide("#webdkp_dkp");
      Util.Hide("#webdkp_awards");
      Util.Show("#webdkp_loot");
      $("#webdkp_dkplink").removeClass("selected");
      $("#webdkp_awardslink").removeClass("selected");
      $("#webdkp_lootlink").addClass("selected");
      WebDKP.activeTable = "loot";
      if ( WebDKP.LoadedLoot != tableid ) {
        WebDKP.LoadedLoot = tableid;
        mustload = true;
      }
    }
    else if(table == "awards" ) {
      Util.Hide("#webdkp_dkp");
      Util.Show("#webdkp_awards");
      Util.Hide("#webdkp_loot");
      $("#webdkp_dkplink").removeClass("selected");
      $("#webdkp_awardslink").addClass("selected");
      $("#webdkp_lootlink").removeClass("selected");
      WebDKP.activeTable = "awards";
      if ( WebDKP.LoadedAwards != tableid ) {
        WebDKP.LoadedAwards = tableid;
        mustload = true;
      }
    }
    else {
      Util.Show("#webdkp_dkp");
      Util.Hide("#webdkp_awards");
      Util.Hide("#webdkp_loot");
      $("#webdkp_dkplink").addClass("selected");
      $("#webdkp_awardslink").removeClass("selected");
      $("#webdkp_lootlink").removeClass("selected");
      WebDKP.activeTable = "dkp";
      if ( WebDKP.LoadedDKP != tableid ) {
        WebDKP.LoadedDKP = tableid;
        mustload = true;
      }
    }
    if ( mustload ) {
      WebDKP.LoadData(tableid, WebDKP.activeTable);
    }
  }

  /*================================================
  Adds a table to our list of tables. Used to construct
  dropdown of available table
  =================================================*/
  this.AddTable = function(table) {
    WebDKP.Tables.push(table);
  }
  /*================================================
  Cosntructs a dropdown / selection box of the available
  tables
  =================================================*/
  this.GetTableDropdown = function() {
    const select = $('<select>')
        .attr({id: 'webdkp_tables'})
        .css('width', '200px');

    for (let i = 0 ; i < WebDKP.Tables.length ; i++ ) {
      select.append(
          $('<option>')
              .val(WebDKP.Tables[i].tableid)
              .text(WebDKP.Tables[i].name)
      );
    }

    select.on('change', () => WebDKP.TableChange)
    WebDKP.TableSelect = select;
    return select;
  }
  /*================================================
  Triggered when a table is selected from the dropdown
  Updates the currently selected tab with the select
  dkp table
  =================================================*/
  this.TableChange = function() {
    WebDKP.SetActiveTable(WebDKP.activeTable);
  }

  /*================================================
  Sends a request to webdkp to load information
  for the currently selected table. This will
  only load information for the currently visible tab.
  =================================================*/
  this.LoadData = function(id) {
    if(WebDKP.activeTable == "loot" )
      WebDKP.LootTable.Erase();
    else if( WebDKP.activeTable == "awards" )
      WebDKP.AwardsTable.Erase();
    else
      WebDKP.DKPTable.Erase();

    WebDKP.LoadJavascript(WebDKP.BaseUrl+"remote.js?view=TableData&t="+id+"&type="+WebDKP.activeTable,true);
  }
})();

//Kicks of processing
WebDKP.Init("<?=$guild->server?>", "<?=$guild->name?>");

