DKPManage = new (function() {
  this.zerosum = false;
  this.awarditem = false;

  this.Init = function(zerosum) {
    DKPManage.CreatePlayerDropdown();
    DKPManage.zerosum = zerosum;
  };

  this.StartAward = function() {
    Util.Show("AwardContent1");
    Util.Hide("TableContent");
    Util.Hide("AwardButton");
    Util.Hide("AwardContent2Item");
    Util.Hide("AwardContent2");
  };

  this.AwardItem = function() {
    DKPManage.awarditem = true;
    Util.Hide("AwardContent1");
    Util.Show("AwardContent2Item");
    Util.Hide("AwardContent3");

    $("#item_name").value = "";
    $("#item_cost").value = "";
  };

  this.AwardItemContinue = function() {
    if (DKPManage.zerosum) {
      $("#selectPlayersContent").innerHTML =
        "You guild is using ZeroSum. You must select a set of players who will recieve positive DKP as a result of this item being awarded.";
      Util.Hide("AwardContent2Item");
      Util.Show("AwardContent3");
    } else {
      DKPManage.ProcessItemAward();
    }
  };

  this.AwardGeneral = function() {
    DKPManage.awarditem = false;
    Util.Hide("AwardContent1");
    Util.Show("AwardContent2");
    Util.Hide("AwardContent3");

    $("award_reason").value = "";
    $("#award_cost").value = "";
  };

  this.SelectPlayersBack = function() {
    if (DKPManage.awarditem) {
      DKPManage.AwardGeneral();
    } else {
      DKPManage.AwardItem();
    }
  };

  this.SelectPlayersForward = function() {
    if (DKPManage.awarditem) {
      DKPManage.ProcessItemAward();
    } else {
      DKPManage.ProcessAward();
    }
  };

  this.SelectRecipients = function() {
    Util.Hide("AwardContent2");
    Util.Show("AwardContent3");
  };

  this.ProcessItemAward = function() {
    var players = [];
    if (DKPManage.zerosum) players = playertable.GetSelectedItems();
    if (players.length == 0 && DKPManage.zerosum) {
      for (var i = 0; i < playertable.items.length; i++)
        players.push(playertable.items[i].userid);
    }

    var playerids = players.join(",");
    var item = $("#item_name").val();
    var cost = $("#item_cost").val();
    var location = $("#item_location").val();
    var awardedby = $("#item_awardedby").val();
    var playerid = $("#userdropdown").val();

    DKPManage.playerid = playerid;
    DKPManage.players = players;
    DKPManage.dkp = parseFloat(cost);
    if (DKPManage.dkp == NaN) DKPManage.dkp = 0;

    $.ajax(DKP.BaseUrl + "Admin/CreateAward/", {
      method: "post",
      dataType: "json",
      data: {
        ajax: "CreateItemAward",
        playerid: playerid,
        item: item,
        cost: DKPManage.dkp,
        location: location,
        awardedby: awardedby,
        zerosum: playerids
      },
      success: json => this.CreateItemAwardCallback(json)
    });
  };

  this.CreateItemAwardCallback = function(result) {
    Util.Hide("AwardContent2Item");
    Util.Hide("AwardContent3");
    Util.Show("AwardContentFinished");

    if (!result) {
      $("#awardFinishedTitle").html("Error!");
      $("#awardFinishedBad").html(
        "There was an error communicating with the server! <br />" +
        transport.responseText);
      Util.Hide("awardFinishedOk");
      Util.Show("awardFinishedBad");
      return;
    }

    //on error
    if (!result[0]) {
      $("#awardFinishedTitle").html("Error!");
      $("#awardFinishedBad").html(result[1]);
      Util.Hide("awardFinishedOk");
      Util.Show("awardFinishedBad");
    }
    //on success
    else {
      $("#awardFinishedTitle").html("Award Created!");
      $("#awardFinishedOk").html("Award Successfully Created");
      Util.Hide("awardFinishedBad");
      Util.Show("awardFinishedOk");
    }
  };

  this.ProcessAward = function() {
    var players = playertable.GetSelectedItems();

    var playerids = players.join(",");
    var reason = $("#award_reason").val();
    var cost = $("#award_cost").val();
    var location = $("#award_location").val();
    var awardedby = $("#award_awardedby").val();

    DKPManage.players = players;
    DKPManage.dkp = parseFloat(cost);
    if (DKPManage.dkp === NaN) DKPManage.dkp = 0;

    $.ajax(DKP.BaseUrl + "Admin/CreateAward/", {
      method: "post",
      dataType: "json",
      data: {
        ajax: "CreateAward",
        playerids: playerids,
        reason: reason,
        cost: DKPManage.dkp,
        location: location,
        awardedby: awardedby
      },
      success: json => DKPManage.CreateAwardCallback(json)
    });
  };

  this.CreateAwardCallback = function(result) {
    Util.Hide("AwardContent3");
    Util.Show("AwardContentFinished");

    if (!result) {
      $("#awardFinishedTitle").text("Error!");
      $("#awardFinishedBad").text(
        "There was an error communicating with the server! <br /> " +
          transport.responseText
      );
      Util.Hide("awardFinishedOk");
      Util.Show("awardFinishedBad");
      return;
    }

    //on error
    if (!result[0]) {
      $("#awardFinishedTitle").text("Error!");
      $("#awardFinishedBad").text(result[1]);
      Util.Hide("awardFinishedOk");
      Util.Show("awardFinishedBad");
    }
    //on success
    else {
      $("#awardFinishedTitle").text("Award Created!");
      $("#awardFinishedOk").text("Award Successfully Created");
      Util.Hide("awardFinishedBad");
      Util.Show("awardFinishedOk");
    }
  };

  this.CreatePlayerDropdown = function() {
    for (let i = 0; i < playertable.items.length; i++) {
      const user = playertable.items[i];
      const option = $("<option>")
        .attr({ value: user.userid })
        .text(user.player);

      $("#userdropdown").append(option);
    }
    $("#userdropdown").selectedIndex = 0;
  };
})();

class SimplePlayerSelectTable extends DKPTable {
  GetRow(i) {
    const row = $("<tr>");
    row.append($("<td>").text(this.items[i].player));
    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);
    row.on("click", () => this.OnRowClick(i));
    return row;
  }

  GetExtraPageButtons() {
    const selectall = $("<a>")
      .attr({ href: "javascript:;" })
      .text("Select All")
      .on("click", () => this.OnSelectAll());

    const deselect = $("<a>")
      .attr({ href: "javascript:;" })
      .text("Deselect All")
      .on("click", () => this.OnDeselectAll());

    const buttons = $("<span>")
      .css("vertical-align", "top")
      .append(document.createTextNode(" ( "))
      .append(selectall)
      .append(document.createTextNode(" | "))
      .append(deselect)
      .append(document.createTextNode(" ) "));

    return buttons;
  }

  OnSelectAll() {
    for (let i = 0; i < this.items.length; i++) {
      if (this.ShouldShowRow(i)) {
        this.items[i].selected = true;
        this.rowObjects[i].addClassName("selected");
      }
    }
    this.Redraw();
  }

  OnDeselectAll() {
    for (let i = 0; i < this.items.length; i++) {
      if (this.ShouldShowRow(i)) {
        this.items[i].selected = false;
        this.rowObjects[i].removeClassName("selected");
      }
    }
    this.Redraw();
  }

  OnRowClick(i) {
    let row = this.rowObjects[i];

    let current = false;
    if (typeof this.items[i].selected != "undefined") {
      current = this.items[i].selected;
    }

    if (current) {
      this.items[i].selected = false;
      row.removeClass("selected");
    } else {
      this.items[i].selected = true;
      row.addClass("selected");
    }
  }

  GetFirstRow() {
    const input = $("<input>")
      .attr({ type: "text" })
      .on("keyup", e => this.OnKeyPress(e));

    this.filter = "";
    this.filterInput = input;

    const row = $("<tr>");
    row.append($("<td>").append(input));
    return row;
  }

  OnKeyPress(event) {
    this.filter = this.filterInput.val().toLowerCase(); // + keychar;
    this.Redraw();
    this.filterInput.focus();
  }

  ShouldShowRow(i) {
    if (this.filter == "") {
      return true;
    }

    var index = this.items[i].player.toLowerCase().indexOf(this.filter);
    return index != -1;
  }

  GetSelectedItems() {
    var items = [];
    for (let i = 0; i < this.items.length; i++) {
      if (this.items[i].selected) {
        items.push(this.items[i].userid);
      }
    }
    return items;
  }
}

class EditLootTable extends DKPTable {
  SetDetails(loottable, section) {
    this.loottable = loottable;
    this.section = section;

    this.activeRow = -1;
  }

  OnSort() {
    this.SaveActiveChanges();
  }

  /*================================================
	Generates a single row for the table
	=================================================*/
  GetRow(i) {
    //get the item that we are putting into this row
    var item = this.items[i];

    //generate the row element
    const row = $("<tr>");

    //create the name cell
    row.append(
      $("<td>")
        .text(item.name)
        .on("click", () => this.OnRowClick(i, 1))
    );

    //create the cost cell
    row.append(
      $("<td>")
        .addClass("center")
        .text(item.cost)
        .on("click", () => this.OnRowClick(i, 2))
    );

    //create the actions cell
    const actions = $("<td>").addClass("center");
    const deleteLink = $("<a>")
      .attr({ href: "javascript:;" })
      .addClass("dkpbutton")
      .on("click", () => this.OnDeleteItem(i))
      .append(
        $("<img>")
          .attr({
            src: Site.SiteRoot + "images/buttons/delete.png"
          })
          .css("vertical-align", "text-bottom")
      );

    actions.append(deleteLink);
    row.append(actions);

    //add mouse over event handlers so we can highlight rows as the
    //mouse movers
    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);

    //return the generated row
    return row;
  }

  GetFirstRow() {
    //generate the row element
    const row = $("<tr>");

    //create the name cell
    const nameInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "360px")
      .on("keypress", e => this.OnKeyPress(e));

    this.nameInput = nameInput;
    row.append($("<td>").append(nameInput));

    //create the cost cell
    const costInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "100px")
      .on("keypress", e => this.OnKeyPress(e));

    this.costInput = costInput;
    row.append(
      $("<td>")
        .addClass("center")
        .append(costInput)
    );

    //create the action cell
    const action = $("<td>")
      .addClass("center")
      .append(
        $("<img>")
          .attr({
            src: Site.SiteRoot + "images/buttons/new.png"
          })
          .css("vertical-align", "text-bottom")
      )
      .append(document.createTextNode(" "))
      .append(
        $("<a>")
          .attr({ href: "javascript:;" })
          .text("Add Loot")
          .on("click", () => this.OnAddItem())
      );

    row.append(action);
    this.firstrow = row;
    return row;
  }

  OnRowClick(i, selected) {
    var row = this.rowObjects[i];
    var item = this.items[i];

    if (this.activeRow == i) return;

    if (this.activeRow != -1 && this.activeRow != i) {
      //undo any current selection
      this.SaveActiveChanges();
    }

    this.activeRow = i;

    // Add <input> for name
    this.activeNameInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "360px")
      .val(item.name)
      .on("keypress", e => this.OnEditKeyPress(e));

    row
      .find("td")
      .eq(0)
      .empty()
      .append(this.activeNameInput);

    // Add <input> for cost
    this.activeCostInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "100px")
      .val(item.cost)
      .on("keypress", e => this.OnEditKeyPress(e));

    row
      .find("td")
      .eq(1)
      .empty()
      .append(this.activeCostInput);

    if (selected == 1) {
      this.activeNameInput.focus();
    } else {
      this.activeCostInput.focus();
    }
  }

  SaveActiveChanges() {
    var i = this.activeRow;
    if (i == -1) return;
    var item = this.items[i];

    var newname = this.activeNameInput.val();
    var newcost = this.activeCostInput.val();

    //check to see if a change occured. If so, we need to process
    //an ajax request
    if (this.items[i].name != newname || this.items[i].cost != newcost) {
      $.ajax(DKP.BaseUrl + "Admin/EditLootTable/" + this.loottable, {
        method: "post",
        dataType: "json",
        json: {
          ajax: "EditItem",
          id: item.id,
          name: newname,
          cost: newcost
        },
        success: json => this.SaveActiveChangesCallback(json, i)
      });
    }

    //assume success - on failure it will undo anything and show an error
    this.items[i].name = newname;
    this.items[i].cost = newcost;
    this.RevertRowToNormal(i);
  }

  SaveActiveChangesCallback(result, i) {
    if (!result[0]) {
      alert("Error: " + result[1]);
      var item = result[2];
      this.items[i] = item;
      this.RevertRowToNormal(i);
    }
    //we already assumed success... so we don't need to update
    //the gui on succeed.
  }

  RevertRowToNormal(i) {
    var row = this.rowObjects[i];
    if (typeof row !== undefined) {
      row[0].cells[0].innerHTML = this.items[i].name;
      row[0].cells[1].innerHTML = this.items[i].cost;
    }
  }

  OnEditKeyPress(event) {
    if (Util.IsEnterEvent(event)) {
      this.SaveActiveChanges();
      this.activeRow = -1;
    }
  }

  OnKeyPress(event) {
    if (Util.IsEnterEvent(event)) this.OnAddItem();
  }

  OnDeleteItem(i) {
    if (i == this.activeRow) this.RevertRowToNormal(i);

    var item = this.items[i];
    var id = item.id;

    $.ajax(DKP.BaseUrl + "Admin/EditLootTable/" + this.loottable, {
      method: "post",
      dataType: "json",
      data: { ajax: "DeleteItem", id: id },
      success: json => this.OnDeleteItemCallback(json, i)
    });
  }

  OnDeleteItemCallback(result, i) {
    if (!result[0]) {
      alert("Error: " + result[1]);
    } else {
      const toDelete = this.rowObjects[i];
      toDelete.remove();
    }
  }

  OnAddItem() {
    if (this.activeRow != -1) {
      this.SaveActiveChanges(this.activeRow);
      this.activeRow = -1;
    }

    const cost = this.costInput.val();
    const name = this.nameInput.val();

    if (name == "") return;
    if (cost == "") cost = 0;

    $.ajax(DKP.BaseUrl + "Admin/EditLootTable/" + this.loottable, {
      method: "post",
      dataType: "json",
      data: {
        ajax: "AddItem",
        section: this.section,
        name: name,
        cost: cost
      },
      success: json => this.OnAddItemCallback(json)
    });

    this.costInput.val("");
    this.nameInput.val("");
    this.nameInput.focus();
  }

  OnAddItemCallback(result) {
    if (!result[0]) {
      alert("Error: " + result[1]);
    } else {
      var item = result[2];

      this.Add(item);
      var row = this.GetRow(this.items.length - 1);
      this.rowObjects[this.items.length - 1] = row;

      //add the row to the visable table
      row.insertAfter(this.firstRow);
    }
  }
}

/*================================================
Contains logic and creation code for the dkp management page
This is the page that allows admins to edit user dkp,
name, guild, and class. The table works by allowing admins
to click on rows they wish to edit. When a row is clicked on,
it replaces static content with form input. Whenever the user
presses enter or clicks a save button, an ajax request is made
save the changes to the database.
=================================================*/
class ManageDKPTable extends ManualPageTable {
  GetUrl() {
    return DKP.BaseUrl + "Admin/Manage/";
  }

  /*================================================
	A setup call that is used to set the permissions that
	the current user has. This will allow us to determine
	what the user can and can not do.
	=================================================*/
  SetDetails(guildName, canDelete, canEditPlayer, canAddPlayer, canAddPoints) {
    this.guildName = guildName;
    this.canDelete = canDelete;
    this.canEditPlayer = canEditPlayer;
    this.canAddPlayer = canAddPlayer;
    this.canAddPoints = canAddPoints;

    this.activeRow = -1;
  }

  /*================================================
	Callback triggered just before a sort is about to
	take place - allows us to save any changes before
	sorting the active row.
	=================================================*/
  OnSort() {
    this.SaveActiveChanges();
  }

  /*================================================
	Generates a single row for the table
	=================================================*/
  GetRow(i) {
    //get the item that we are putting into this row
    var item = this.items[i];
    this.items[i].deleted = false;

    //generate the row element
    const row = $("<tr>");

    //create the name cell
    const name = $("<td>").text(item.player);
    if (this.canEditPlayer) {
      name.on("click", () => this.OnRowClick(i, 1));
    }
    row.append(name);

    //create the guild cell
    const guild = $("<td>")
      .addClass("center")
      .text(item.playerguild);

    if (this.canEditPlayer) {
      guild.on("click", () => this.OnRowClick(i, 2));
    }
    row.append(guild);

    //class cell
    const classCell = $("<td>")
      .addClass("center")
      .attr({ sortkey: this.items[i].playerclass })
      .append(
        $("<img>")
          .attr({
            src:
              Site.SiteRoot +
              "images/classes/small/" +
              this.items[i].playerclass +
              ".gif"
          })
          .addClass("classIcon")
      );

    if (this.canEditPlayer) {
      classCell.on("click", () => this.OnRowClick(i, 3));
    }
    row.append(classCell);

    //create the dkp cell
    const cost = $("<td>")
      .addClass("center")
      .text(item.dkp);

    if (this.canEditPlayer) {
      cost.on("click", () => this.OnRowClick(i, 4));
    }
    row.append(cost);

    //create the actions cell
    const actions = $("<td>").addClass("center");
    const saveLink = $("<a>")
      .attr({ href: "javascript:;", id: `save_${i}` })
      .addClass("dkpbutton")
      .css("display", "none")
      .on("click", () => this.OnSaveItem(i))
      .append(
        $("<img>")
          .attr({ src: Site.SiteRoot + "images/buttons/save.png" })
          .css("vertical-align", "text-bottom")
      );
    actions.append(saveLink);

    if (this.canEditPlayer) {
      // Edit User Button
      $("<a>")
        .attr({ href: "javascript:;", title: "Edit User" })
        .addClass("dkpbutton")
        .on("click", () => this.OnRowClick(i, 1))
        .append(
          $("<img>")
            .attr({ src: Site.SiteRoot + "images/buttons/edit.png" })
            .css("vertical-align", "text-bottom")
        )
        .appendTo(actions);

      // Edit Alts Button
      $("<a>")
        .attr({ href: "javascript:;", title: "Edit Alts" })
        .addClass("dkpbutton")
        .on("click", () => this.OnAltClick(i))
        .append(
          $("<img>")
            .attr({ src: Site.SiteRoot + "images/buttons/alts.png" })
            .css("vertical-align", "text-bottom")
        )
        .appendTo(actions);
    }

    if (this.canDelete) {
      $("<a>")
        .attr({ href: "javascript:;", title: "Delete User" })
        .addClass("dkpbutton")
        .on("click", () => this.OnDeleteItem(i))
        .append(
          $("<img>")
            .attr({ src: Site.SiteRoot + "images/buttons/delete.png" })
            .css("vertical-align", "text-bottom")
        )
        .appendTo(actions);
    }
    row.append(actions);

    //add mouse over event handlers so we can highlight rows as the
    //mouse movers
    row.on("mouseover", this.OnRowOver);
    row.on("mouseout", this.OnRowOut);

    //return the generated row
    return row;
  }

  /*================================================
	Generates the very first row for the table. This is
	the row that will always appear at the top and will
	provide a way for new users to be created
	=================================================*/
  GetFirstRow() {
    //only show the add player row if the current user has permissions to do so
    if (!this.canAddPlayer) return;

    //generate the row element
    const row = $("<tr>");

    //create the name cell
    const name = $("<td>").appendTo(row);
    this.nameInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "150px")
      .on("keypress", e => this.OnKeyPress(e))
      .appendTo(name);

    //create the guild cell
    const guild = $("<td>")
      .addClass("center")
      .appendTo(row);

    this.guildInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "150px")
      .val(this.guildName)
      .on("keypress", e => this.OnKeyPress(e))
      .appendTo(guild);

    //creat the class selection cell
    const playerClass = $("<td>")
      .addClass("center")
      .appendTo(row);

    this.classInput = this.GetClassDropdown().appendTo(playerClass);

    //create the dkp cell
    const dkp = $("<td>")
      .addClass("center")
      .appendTo(row);

    this.dkpInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "75px")
      .on("keypress", e => this.OnKeyPress(e))
      .appendTo(dkp);

    //create the action cell
    const action = $("<td>")
      .addClass("center")
      .appendTo(row);

    action
      .append(
        $("<img>")
          .attr({ src: Site.SiteRoot + "images/buttons/new.png" })
          .css("vertical-align", "text-bottom")
      )
      .append(document.createTextNode(" "))
      .append(
        $("<a>")
          .attr({ href: "javascript:;" })
          .text("Add Player")
          .on("click", () => this.OnAddPlayer())
      );

    this.firstrow = row;
    return row;
  }

  /*================================================
	Generated when a user clicks on a row to be edited.
	This replaces the static content with a form input
	=================================================*/
  OnRowClick(i, selected) {
    var row = this.rowObjects[i];
    var item = this.items[i];

    if (this.activeRow === i) return;

    if (this.activeRow !== -1 && this.activeRow !== i) {
      // undo any current selection
      this.SaveActiveChanges();
    }

    this.activeRow = i;

    // name input
    this.activeNameInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "150px")
      .val(item.player)
      .on("keypress", e => this.OnEditKeyPress(e));

    row
      .find("td")
      .eq(0)
      .empty()
      .append(this.activeNameInput);

    //guild input
    if (item.playerguild == null) item.playerguild = "";

    this.activeGuildInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "150px")
      .val(item.playerguild)
      .on("keypress", e => this.OnEditKeyPress(e));

    row
      .find("td")
      .eq(1)
      .empty()
      .append(this.activeGuildInput);

    //class input
    this.activeClassInput = this.GetClassDropdown().on("change", e =>
      this.OnEditSelect(e)
    );

    row
      .find("td")
      .eq(2)
      .empty()
      .append(this.activeClassInput);

    //figure out what the class input should select as a default option
    let selectedIndex = 0;
    for (let i = 0; i < this.activeClassInput[0].options.length; i++) {
      if (this.activeClassInput[0].options[i].value == item.playerclass) {
        selectedIndex = i;
        break;
      }
    }
    this.activeClassInput[0].selectedIndex = selectedIndex;

    //dkp input
    this.activeDkpInput = $("<input>")
      .attr({ type: "text" })
      .css("width", "75px")
      .val(item.dkp)
      .on("keypress", e => this.OnEditKeyPress(e));

    row
      .find("td")
      .eq(3)
      .empty()
      .append(this.activeDkpInput);

    //show the save button
    $("#save_" + this.activeRow).css({ display: "inline" });

    //force focus to the cell that they clicked on
    if (selected == 1) this.activeNameInput.focus();
    else if (selected == 2) this.activeGuildInput.focus();
    else if (selected == 3) this.activeClassInput.focus();
    else if (selected == 4) this.activeDkpInput.focus();
  }

  /*================================================
	Determines the row that is currently being edited
	and saves changes to the database. Afterwards,
	the row is revereted back to normal
	=================================================*/
  SaveActiveChanges() {
    const i = this.activeRow;
    if (i == -1) return;

    const newname = this.activeNameInput.val();
    const newguild = this.activeGuildInput.val();
    const newclass = this.activeClassInput[0].options[
      this.activeClassInput[0].selectedIndex
    ].value;
    const newdkp = this.activeDkpInput.val();

    //check to see if a change occured. If so, we need to process
    //an ajax request
    if (
      this.items[i].playername != newname ||
      this.items[i].playerguild != newguild ||
      this.items[i].playerclass != newclass ||
      this.items[i].dkp != newdkp
    ) {
      $.ajax(DKP.BaseUrl + "Admin/Manage/", {
        method: "post",
        dataType: "json",
        data: {
          ajax: "EditPlayer",
          id: this.items[i].userid,
          name: newname,
          guild: newguild,
          playerclass: newclass,
          dkp: newdkp
        },
        onSuccess: json => this.SaveActiveChangesCallback(json, i)
      });
    }

    //assume success
    this.items[i].player = newname;
    this.items[i].playerguild = newguild;
    this.items[i].playerclass = newclass;
    this.items[i].dkp = newdkp;
    this.RevertRowToNormal(i);
  }

  /*================================================
	Issued after save has been sent and processed by the
	server. Checks for any error
	=================================================*/
  SaveActiveChangesCallback(result, i) {
    if (!result[0]) {
      alert("Error: " + result[1]);
      if (result[2]) {
        this.items[i].player = result[2].player;
        this.items[i].playerguild = result[2].playerguild;
        this.items[i].playerclass = result[2].playerclass;
        this.items[i].dkp = result[2].dkp;
        this.RevertRowToNormal(i);
      }
    } else {
      if (result[2]) {
        this.items[i].userid = result[2];
      }
    }
    //we already assumed success... so we don't need to update
    //the gui on succeed.
  }

  /*================================================
	Reverts a row that is currently being edited back to normal
	=================================================*/
  RevertRowToNormal(i) {
    var row = this.rowObjects[i];
    if (typeof row != "undefined") {
      $(row[0].cells[0]).text(this.items[i].player);
      $(row[0].cells[1]).text(this.items[i].playerguild);
      $(row[0].cells[2])
        .empty()
        .append(
          $("<img>")
            .attr({
              src:
                Site.SiteRoot +
                "images/classes/small/" +
                this.items[i].playerclass +
                ".gif"
            })
            .addClass("classIcon")
        );
      $(row[0].cells[3]).text(this.items[i].dkp);

      Util.Hide(`save_${i}`);
    }
  }

  /*================================================
	Triggered when a user clicks on the delete button.
	Sends an ajax request to delete the specified user
	=================================================*/
  OnDeleteItem(i) {
    var result = confirm("Delete Player? All player history will be lost.");
    if (!result) return;

    if (i == this.activeRow) this.RevertRowToNormal(i);

    var item = this.items[i];
    var id = item.userid;

    $.ajax(DKP.BaseUrl + "Admin/Manage", {
      method: "post",
      dataType: "json",
      data: { ajax: "DeletePlayer", id: id },
      success: json => this.OnDeleteItemCallback(json, i)
    });
  }

  /*================================================
	Callback after the server has processed our
	delete command. Check for errors. If it all went through
	ok, go ahead and remove the player from the table
	=================================================*/
  OnDeleteItemCallback(result, i) {
    if (!result[0]) {
      alert(result[1]);
    } else {
      this.items[i].deleted = true;

      const toDelete = this.rowObjects[i];
      toDelete.remove();

      if (this.activeRow === i) {
        this.activeRow === -1;
      }
    }
  }

  /*================================================
	Called when the user wants to create a new player.
	Sent request via ajax to the server.
	=================================================*/
  OnAddPlayer() {
    if (this.activeRow != -1) {
      this.SaveActiveChanges(this.activeRow);
      this.activeRow = -1;
    }

    var name = this.nameInput.val();
    var guild = this.guildInput.val();
    var playerclass = this.classInput.val();
    var dkp = this.dkpInput.val();

    if (!name) return;
    if (dkp == "") dkp = 0;

    $.ajax(DKP.BaseUrl + "Admin/Manage", {
      method: "post",
      dataType: "json",
      data: {
        ajax: "AddPlayer",
        name: name,
        playerguild: guild,
        playerclass: playerclass,
        dkp: dkp
      },
      success: data => this.OnAddPlayerCallback(data)
    });

    //clear the entry form and move back to the first input
    //this allows the user to enter many users quickly
    this.dkpInput.val("");
    this.nameInput.val("");
    this.nameInput.focus();
  }

  /*================================================
	Callback when a user has been added to the database.
	If everythign went ok, add the user to the table.
	Display any errors
	=================================================*/
  OnAddPlayerCallback(result) {
    if (!result[0]) {
      alert(result[1]);
    } else {
      var item = result[2];

      this.Add(item);
      var row = this.GetRow(this.items.length - 1);
      this.rowObjects.push(row);

      //add the row to the visible table
      row.insertAfter(this.firstRow);
    }
  }

  /*================================================
	Issued when a user clicks on a save link. Just
	saves changes and reverts edited row to normal
	=================================================*/
  OnSaveItem() {
    this.SaveActiveChanges();
    this.activeRow = -1;
  }

  /*================================================
	used to monitor key pressed when editing a player.
	If enter is pressed, treat it the same as hitting
	the save button.
	=================================================*/
  OnEditKeyPress(event) {
    if (Util.IsEnterEvent(event)) {
      this.SaveActiveChanges();
      this.activeRow = -1;
    }
  }

  /*================================================
	Triggered when a class is selected for a user in edit
	mode. Treated the same as clicking the save button.
	=================================================*/
  OnEditSelect(event) {
    this.SaveActiveChanges();
    this.activeRow = -1;
  }

  /*================================================
	Detects when a user pressed enter while creating
	a new player. Detects if the enter button is pressed.
	=================================================*/
  OnKeyPress(event) {
    if (Util.IsEnterEvent(event)) this.OnAddPlayer();
  }

  OnAltClick(i) {
    var user = this.items[i];
    document.location = DKP.BaseUrl + "Admin/PlayerAlts?player=" + user.userid;
  }

  /*================================================
	Generates a dropdown / selection box of all the
	available classes to select from.
	=================================================*/
  GetClassDropdown() {
    function createOption(clazz) {
      return $("<option>")
        .attr({ value: clazz })
        .text(clazz);
    }

    const select = $("<select>").css("width", "75px");
    select.append(createOption("Death Knight"));
    select.append(createOption("Druid"));
    select.append(createOption("Hunter"));
    select.append(createOption("Mage"));
    select.append(createOption("Paladin"));
    select.append(createOption("Priest"));
    select.append(createOption("Rogue"));
    select.append(createOption("Shaman"));
    select.append(createOption("Warlock"));
    select.append(createOption("Warrior"));
    return select;
  }
}
