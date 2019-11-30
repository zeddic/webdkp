/*=====================================================

======================================================*/
DKPAdmin = new (function() {


})();

DKPManage = new (function() {

	this.zerosum = false;
	this.awarditem = false;


	this.Init = function(zerosum) {
		DKPManage.CreatePlayerDropdown();
		DKPManage.zerosum = zerosum;
	}

	this.StartAward = function() {
		Util.Show('AwardContent1');
		Util.Hide('TableContent');
		Util.Hide('AwardButton')
		Util.Hide('AwardContent2Item');
		Util.Hide('AwardContent2');
	}

	this.AwardItem = function() {
		DKPManage.awarditem = true;
		Util.Hide('AwardContent1');
		Util.Show('AwardContent2Item');
		Util.Hide('AwardContent3');

		$("item_name").value = "";
		$("item_cost").value = "";
	}

	this.AwardItemContinue = function() {
		if ( DKPManage.zerosum ) {
			$("selectPlayersContent").innerHTML =
				"You guild is using ZeroSum. You must select a set of players who will recieve positive DKP as a result of this item being awarded.";
			Util.Hide('AwardContent2Item');
			Util.Show('AwardContent3');
		}
		else
		{
			DKPManage.ProcessItemAward();
		}
	}

	this.AwardGeneral = function() {
		DKPManage.awarditem = false;
		Util.Hide('AwardContent1');
		Util.Show('AwardContent2');
		Util.Hide('AwardContent3');

		$("award_reason").value = "";
		$("award_cost").value = "";
	}

	this.SelectPlayersBack = function() {
		if(DKPManage.awarditem  ) {
			DKPManage.AwardGeneral();
		}
		else {
			DKPManage.AwardItem();
		}
	}

	this.SelectPlayersForward = function() {
		if(DKPManage.awarditem ) {
			DKPManage.ProcessItemAward();
		}
		else {
			DKPManage.ProcessAward();
		}
	}

	this.SelectRecipients = function() {
		Util.Hide('AwardContent2');
		Util.Show('AwardContent3');
	}


	this.ProcessItemAward = function() {
		var players = [];
		if( DKPManage.zerosum )
			players = playertable.GetSelectedItems();
		if( players.length == 0 && DKPManage.zerosum ) {
			for ( var i = 0 ; i < playertable.items.length ; i++ )
				players.push(playertable.items[i].userid);
		}

		var playerids = players.join(',');
		var item = $("item_name").value;
		var cost = $("item_cost").value;
		var location = $("item_location").value;
		var awardedby = $("item_awardedby").value;
		var playerid = $("userdropdown").value;

		DKPManage.playerid = playerid;
		DKPManage.players = players;
		DKPManage.dkp = parseFloat(cost);
		if( DKPManage.dkp == NaN )
			DKPManage.dkp = 0;

		new Ajax.Request(DKP.BaseUrl+"Admin/CreateAward/", {
				method:'post',
				parameters: {
					ajax:"CreateItemAward",
					playerid:playerid,
					item:item,
					cost:DKPManage.dkp,
					location:location,
					awardedby:awardedby,
					zerosum: playerids
				},
				onSuccess: DKPManage.CreateItemAwardCallback
			});
	}

	this.CreateItemAwardCallback = function(transport)
	{
		Util.Hide('AwardContent2Item');
		Util.Hide('AwardContent3');
		Util.Show("AwardContentFinished");

		var result;
		try {
		 	result = transport.responseText.evalJSON(true);
		}
		catch(e) {
			$("awardFinishedTitle").innerHTML = "Error!";
			$("awardFinishedBad").innerHTML = "There was an error communicating with the server! <br />"+transport.responseText;
			Util.Hide("awardFinishedOk");
			Util.Show("awardFinishedBad");
			return;
		}

		//on error
		if(!result[0]) {
			$("awardFinishedTitle").innerHTML = "Error!";
			$("awardFinishedBad").innerHTML = result[1];
			Util.Hide("awardFinishedOk");
			Util.Show("awardFinishedBad");
		}
		//on success
		else {
			$("awardFinishedTitle").innerHTML = "Award Created!";
			$("awardFinishedOk").innerHTML = "Award Successfully Created";
			Util.Hide("awardFinishedBad");
			Util.Show("awardFinishedOk");
		}
	}


	this.ProcessAward = function() {
		var players = playertable.GetSelectedItems();

		var playerids = players.join(',');
		var reason = $("award_reason").value;
		var cost = $("award_cost").value;
		var location = $("award_location").value;
		var awardedby = $("award_awardedby").value;

		DKPManage.players = players;
		DKPManage.dkp = parseFloat(cost);
		if( DKPManage.dkp == NaN )
			DKPManage.dkp = 0;

		new Ajax.Request(DKP.BaseUrl+"Admin/CreateAward/", {
				method:'post',
				parameters: {
					ajax:"CreateAward",
					playerids:playerids,
					reason:reason,
					cost:DKPManage.dkp,
					location:location,
					awardedby:awardedby
				},
				onSuccess: DKPManage.CreateAwardCallback
			});
	}

	this.CreateAwardCallback = function(transport)
	{
		Util.Hide('AwardContent3');

		Util.Show("AwardContentFinished");

		var result;
		try {
		 	result = transport.responseText.evalJSON(true);
		}
		catch(e) {
			$("awardFinishedTitle").innerHTML = "Error!";
			$("awardFinishedBad").innerHTML = "There was an error communicating with the server! <br /> "+transport.responseText;
			Util.Hide("awardFinishedOk");
			Util.Show("awardFinishedBad");
			return;
		}

		//on error
		if(!result[0]) {
			$("awardFinishedTitle").innerHTML = "Error!";
			$("awardFinishedBad").innerHTML = result[1];
			Util.Hide("awardFinishedOk");
			Util.Show("awardFinishedBad");
		}
		//on success
		else {
			$("awardFinishedTitle").innerHTML = "Award Created!";
			$("awardFinishedOk").innerHTML = "Award Successfully Created";
			Util.Hide("awardFinishedBad");
			Util.Show("awardFinishedOk");
		}
	}

	this.CreatePlayerDropdown = function()
	{
		for ( var i = 0 ; i < playertable.items.length ; i++ ) {
			var user = playertable.items[i];
			var option = Builder.node('option',{value:user.userid},user.player)
			$("userdropdown").appendChild(option);
		}
		$("userdropdown").selectedIndex = 0;
	}
})();

var SimplePlayerSelectTable = Class.create(DKPTable, {
	GetRow: function(i) {
		var row = Builder.node('tr',{},"");

		var name = Builder.node('td',{}, this.items[i].player);
		//Event.observe(name,'click', this.OnRowClick.bindAsEventListener(this, i,1));
		row.appendChild(name);

		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);
		//Event.observe(row,'click', this.OnRowClick);

		Event.observe(row,'click', this.OnRowClick.bindAsEventListener(this, i));

		return row;
	},

	GetExtraPageButtons: function() {
		var selectall = Builder.node('a',{href:"javascript:;"},"Select All");
		Event.observe(selectall,'click', this.OnSelectAll.bindAsEventListener(this));

		var deselect = Builder.node('a',{href:"javascript:;"},"Deselect All");
		Event.observe(deselect,'click', this.OnDeselectAll.bindAsEventListener(this));


		var temp = Builder.node('span',{style:'vertical-align:top'},[" ( ",selectall," | ", deselect," ) "]);

		return temp;

	},

	OnSelectAll: function() {
		for ( var i = 0 ; i < this.items.length ; i++ ) {
			if (this.ShouldShowRow(i)) {
				this.items[i].selected = true;
				this.rowObjects[i].addClassName("selected");
			}
		}
		this.Redraw();
	},

	OnDeselectAll: function() {
		for ( var i = 0 ; i < this.items.length ; i++ ) {
			if (this.ShouldShowRow(i)) {
				this.items[i].selected = false;
				this.rowObjects[i].removeClassName("selected");
			}
		}
		this.Redraw();
	},

	OnRowClick: function(event) {
		//this.addClassName("over");

		var data = $A(arguments);
		data.shift();
		var i = data[0];
		var row = this.rowObjects[i];

		var current = false;
		if (typeof(this.items[i].selected) != "undefined" ) {
			current = this.items[i].selected;
		}

		if( current ) {
			this.items[i].selected = false;
			row.removeClassName("selected");
		}
		else {
			this.items[i].selected = true;
			row.addClassName("selected");
		}

		//this.items[i].selected
		//var el = event.element();
		//row.addClassName("selected");
		//document.location = url;
	},

	//OnRowClick: function() {

//	},

	GetFirstRow: function() {
		this.filter = "";

		var row = Builder.node('tr',{},"");

		var cell = Builder.node('td',{});
		var input = Builder.node('input',{type:"text"});
		this.filterInput = input;
		Event.observe(input,'keyup', this.OnKeyPress.bindAsEventListener(this));
		cell.appendChild(input);

		row.appendChild(cell);

		return row;

	},

	OnKeyPress: function(event) {

		/*var keynum;
		if(window.event) // IE
			keynum = event.keyCode;
		else if(event.which) // Netscape/Firefox/Opera
			keynum = event.which;*/



		//var keychar = String.fromCharCode(keynum);
		this.filter = this.filterInput.value.toLowerCase();// + keychar;
		this.Redraw();
		var input = this.filterInput;
		input.focus();
		//setTimeout(function(){input.focus();},10);

	},

	ShouldShowRow: function(i) {
		if (this.filter == "") {
			return true;
		}

		//alert(this.items[i].player);
		//alert("looking for" + this.filter);
		var index = this.items[i].player.toLowerCase().indexOf(this.filter);

		return ( index != -1);
	},

	GetSelectedItems: function() {
		var items = [];
		for ( var i = 0 ; i < this.items.length ; i++ ) {
			if(this.items[i].selected) {
				items.push(this.items[i].userid);
			}
		}
		return items;
	}
});


var AltTable = Class.create(DKPTable, {
	GetRow: function(i) {
		var row = Builder.node('tr',{},"");

		var name = Builder.node('td',{}, this.items[i].player);
		row.appendChild(name);

		var actions = Builder.node('td',{className:"center"});

		var deleteImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/delete.png",style:"vertical-align:text-bottom"});
		var deleteLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton"},deleteImg);
		Event.observe(deleteLink,'click', this.OnDeleteAlt.bindAsEventListener(this, i));
		actions.appendChild(deleteLink);

		row.appendChild(actions);


		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);

		Event.observe(row,'click', this.OnRowClick.bindAsEventListener(this, i));

		return row;
	},

	GetFirstRow: function() {
		this.filter = "";

		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{});
		var nameInput = Builder.node('input',{type:"text",style:"width:150px"});
		Event.observe(nameInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		name.appendChild(nameInput);
		row.appendChild(name);
		this.nameInput = nameInput;


		//create the action cell
		var action = Builder.node('td',{className:"center"});
		var actionImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/new.png",style:"vertical-align:text-bottom"});
		var actionLink = Builder.node('a',{href:"javascript:;"},"Add Player");
		Event.observe(actionLink,'click', this.OnAddAlt.bindAsEventListener(this));
		row.appendChild(action);

		return row;

	},

	OnDeleteAlt: function() {

	},

	OnAddAlt: function() {

	}
});



var EditLootTable = Class.create(DKPTable, {

	SetDetails: function(loottable, section) {
		this.loottable = loottable;
		this.section = section;

		this.activeRow = -1;
	},

	OnSort: function() {
		this.SaveActiveChanges();
	},

	/*================================================
	Generates a single row for the table
	=================================================*/
	GetRow: function(i) {
		//get the item that we are putting into this row
		var item = this.items[i];

		//generate the row element
		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{}, item.name);
		Event.observe(name,'click', this.OnRowClick.bindAsEventListener(this, i,1));
		row.appendChild(name);

		//create the cost cell
		var cost = Builder.node('td',{className:"center"},item.cost);
		Event.observe(cost,'click', this.OnRowClick.bindAsEventListener(this, i,2));
		row.appendChild(cost);

		//create the actions cell
		var actions = Builder.node('td',{className:"center"});
		var deleteImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/delete.png",style:"vertical-align:text-bottom"});
		var deleteLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton"},deleteImg);
		Event.observe(deleteLink,'click', this.OnDeleteItem.bindAsEventListener(this, i));
		actions.appendChild(deleteLink);
		row.appendChild(actions);

		//add mouse over event handlers so we can highlight rows as the
		//mouse movers
		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);

		//return the generated row
		return row;
	},

	GetFirstRow: function() {


		//generate the row element
		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{});
		var nameInput = Builder.node('input',{type:"text",style:"width:360px"});
		Event.observe(nameInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		name.appendChild(nameInput);
		row.appendChild(name);
		this.nameInput = nameInput;

		//create the cost cell
		var cost = Builder.node('td',{className:"center"});
		var costInput = Builder.node('input',{type:"text",style:"width:100px"});
		Event.observe(costInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		cost.appendChild(costInput);
		row.appendChild(cost);
		this.costInput = costInput;

		//create the action cell
		var action = Builder.node('td',{className:"center"});
		var actionImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/new.png",style:"vertical-align:text-bottom"});
		var actionLink = Builder.node('a',{href:"javascript:;"},"Add Loot");
		Event.observe(actionLink,'click', this.OnAddItem.bindAsEventListener(this));


		action.appendChild(actionImg);
		action.innerHTML+=" ";
		action.appendChild(actionLink);
		row.appendChild(action);

		this.firstrow = row;
		return row;
	},

	OnRowClick: function() {
		var data = $A(arguments);
		data.shift();
		var i = data[0];
		var selected = data[1];

		var row = this.rowObjects[i];
		var item = this.items[i];

		if(this.activeRow == i )
			return;

		if(this.activeRow != -1 && this.activeRow != i ) {
			//undo any current selection
			this.SaveActiveChanges();
		}

		this.activeRow = i;

		var activeNameInput = Builder.node('input',{type:"text",style:"width:360px",value:item.name});
		Event.observe(activeNameInput,'keypress', this.OnEditKeyPress.bindAsEventListener(this));
		this.activeNameInput = activeNameInput;

		row.cells[0].innerHTML = "";
		row.cells[0].appendChild(activeNameInput);

		var activeCostInput = Builder.node('input',{type:"text",style:"width:100px",value:item.cost});
		Event.observe(activeCostInput,'keypress', this.OnEditKeyPress.bindAsEventListener(this));
		this.activeCostInput = activeCostInput;

		row.cells[1].innerHTML = "";
		row.cells[1].appendChild(activeCostInput);

		if(selected == 1 )
			this.activeNameInput.focus();
		else
			this.activeCostInput.focus();


	},

	SaveActiveChanges: function() {
		var i = this.activeRow;
		if(i == -1 )
			return;
		var item = this.items[i];

		var newname = this.activeNameInput.value;
		var newcost = this.activeCostInput.value;

		//check to see if a change occured. If so, we need to process
		//an ajax request
		if(this.items[i].name != newname ||
		   this.items[i].cost != newcost ) {

			new Ajax.Request(DKP.BaseUrl+"Admin/EditLootTable/"+this.loottable, {
				method:'post',
				parameters: {ajax:"EditItem",id:item.id,name:newname,cost:newcost},
				onSuccess: this.SaveActiveChangesCallback.bindAsEventListener(this, i)
			});
		}

		//assume success - on failure it will undo anything and show an error
		this.items[i].name = newname;
		this.items[i].cost = newcost;
		this.RevertRowToNormal(i);

	},

	SaveActiveChangesCallback: function(transport) {
		var data = $A(arguments);
		data.shift();
		var i = data[0];

		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert("Error: "+result[1]);
			var item = result[2];
			this.items[i] = item;
			this.RevertRowToNormal(i);
		}
		//we already assumed success... so we don't need to update
		//the gui on succeed.
	},

	RevertRowToNormal: function(i) {
		var row = this.rowObjects[i];
		if(typeof(row) != "undefined" ) {
			row.cells[0].innerHTML = this.items[i].name;
			row.cells[1].innerHTML = this.items[i].cost;
		}
	},

	OnEditKeyPress: function(event) {
		if(Util.IsEnterEvent(event)) {
			this.SaveActiveChanges();
			this.activeRow = -1;
		}
	},

	OnKeyPress: function(event) {
		if(Util.IsEnterEvent(event))
			this.OnAddItem();
	},
	OnDeleteItem: function() {
		var data = $A(arguments);
		data.shift();
		var i = data[0];

		if( i == this.activeRow )
			this.RevertRowToNormal(i);

		var item = this.items[i];
		var id = item.id;

		new Ajax.Request(DKP.BaseUrl+"Admin/EditLootTable/"+this.loottable, {
			method:'post',
			parameters: {ajax:"DeleteItem",id:id},
			onSuccess: this.OnDeleteItemCallback.bindAsEventListener(this, i)
			});
	},

	OnDeleteItemCallback: function(transport) {

		var data = $A(arguments);
		data.shift();
		var i = data[0];

		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert("Error: "+result[1]);
		}
		else {
			var toDelete = this.rowObjects[i];
			this.tableBody.removeChild(toDelete);
		}
	},

	OnAddItem: function() {

		if(this.activeRow != -1 ) {
			this.SaveActiveChanges(this.activeRow);
			this.activeRow = -1;
		}

		var cost = this.costInput.value;
		var name = this.nameInput.value;

		if( name == "" )
			return;
		if( cost == "" )
			cost = 0;

		new Ajax.Request(DKP.BaseUrl+"Admin/EditLootTable/"+this.loottable, {
			method:'post',
			parameters: {ajax:"AddItem",section:this.section,name:name,cost:cost},
			onSuccess: this.OnAddItemCallback.bindAsEventListener(this)
			});

		this.costInput.value = "";
		this.nameInput.value = "";
		this.nameInput.focus();

	},

	OnAddItemCallback: function(transport) {
		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert("Error: "+result[1]);
		}
		else {
			var item = result[2];

			this.Add(item);
			var row = this.GetRow(this.items.length - 1 );
			this.rowObjects[this.items.length-1] = row;

			//add the row to the visable table
			this.tableBody.insertBefore(row, this.firstrow.nextSibling);
		}
	}
});


/*================================================
Contains logic and creation code for the dkp management page
This is the page that allows admins to edit user dkp,
name, guild, and class. The table works by allowing admins
to click on rows they wish to edit. When a row is clicked on,
it replaces static content with form input. Whenever the user
presses enter or clicks a save button, an ajax request is made
save the changes to the database.
=================================================*/
var ManageDKPTable = Class.create(ManualPageTable, {

	GetUrl: function() {
		return DKP.BaseUrl + "Admin/Manage/";
	},

	/*================================================
	A setup call that is used to set the permissions that
	the current user has. This will allow us to determine
	what the user can and can not do.
	=================================================*/
	SetDetails: function(guildName, canDelete, canEditPlayer, canAddPlayer, canAddPoints ) {
		this.guildName = guildName;
		this.canDelete = canDelete;
		this.canEditPlayer = canEditPlayer;
		this.canAddPlayer = canAddPlayer;
		this.canAddPoints = canAddPoints;

		this.activeRow = -1;
	},

	/*================================================
	Callback triggered just before a sort is about to
	take place - allows us to save any changes before
	sorting the active row.
	=================================================*/
	OnSort: function() {
		this.SaveActiveChanges();
	},


	/*================================================
	Generates a single row for the table
	=================================================*/
	GetRow: function(i) {
		//get the item that we are putting into this row
		var item = this.items[i];

		this.items[i].deleted = false;

		//generate the row element
		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{}, item.player);
		if( this.canEditPlayer )
			Event.observe(name,'click', this.OnRowClick.bindAsEventListener(this, i,1));
		row.appendChild(name);

		//create the guild cell
		var guild = Builder.node('td',{className:"center"},item.playerguild);
		if( this.canEditPlayer )
			Event.observe(guild,'click', this.OnRowClick.bindAsEventListener(this, i,2));
		row.appendChild(guild);

		//class cell
		var classImg = Builder.node('img',{src:Site.SiteRoot+"images/classes/small/"+this.items[i].playerclass+".gif",className:'classIcon'});
		var classCell = Builder.node('td',{className:"center",sortkey:this.items[i].playerclass},classImg);
		if( this.canEditPlayer )
			Event.observe(classCell,'click', this.OnRowClick.bindAsEventListener(this, i,3));
		row.appendChild(classCell);

		//create the dkp cell
		var cost = Builder.node('td',{className:"center"},item.dkp);
		if( this.canEditPlayer )
			Event.observe(cost,'click', this.OnRowClick.bindAsEventListener(this, i,4));
		row.appendChild(cost);

		//create the actions cell
		var actions = Builder.node('td',{className:"center"});
		var saveImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/save.png",style:"vertical-align:text-bottom"});
		var saveLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton",style:"display:none",id:"save_"+i},saveImg);
		Event.observe(saveLink,'click', this.OnSaveItem.bindAsEventListener(this, i));
		actions.appendChild(saveLink);

		if( this.canEditPlayer ) {
			var editImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/edit.png",style:"vertical-align:text-bottom"});
			var editLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton",title:"Edit User"},editImg);
			Event.observe(editLink,'click', this.OnRowClick.bindAsEventListener(this, i, 1));
			actions.appendChild(editLink);

			var altImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/alts.png",style:"vertical-align:text-bottom"});
			var altLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton",title:"Edit Alts"},altImg);
			Event.observe(altLink,'click', this.OnAltClick.bindAsEventListener(this, i));
			//Event.observe(altLink,'click', function(){document.location=''});
			actions.appendChild(altLink);
		}

		if(this.canDelete) {
			var deleteImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/delete.png",style:"vertical-align:text-bottom"});
			var deleteLink = Builder.node('a',{href:"javascript:;",className:"dkpbutton", title:"Delete User"},deleteImg);
			Event.observe(deleteLink,'click', this.OnDeleteItem.bindAsEventListener(this, i));
			actions.appendChild(deleteLink);

			var deleteCheckBox = Builder.node('input',{type:"checkbox", checked:"checked",name: "test1"},"");
			//action.appendChild(deleteCheckBox);

		}
		row.appendChild(actions);

		//add mouse over event handlers so we can highlight rows as the
		//mouse movers
		Event.observe(row,'mouseover', this.OnRowOver);
		Event.observe(row,'mouseout', this.OnRowOut);

		//return the generated row
		return row;
	},

	/*================================================
	Generates the very first row for the table. This is
	the row that will always appear at the top and will
	provide a way for new users to be created
	=================================================*/
	GetFirstRow: function() {

		//only show the add player row if the current user has permissions to do so
		if(!this.canAddPlayer )
			return;

		//generate the row element
		var row = Builder.node('tr',{},"");

		//create the name cell
		var name = Builder.node('td',{});
		var nameInput = Builder.node('input',{type:"text",style:"width:150px"});
		Event.observe(nameInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		name.appendChild(nameInput);
		row.appendChild(name);
		this.nameInput = nameInput;

		//create the guild cell
		var guild = Builder.node('td',{className:"center"});
		var guildInput = Builder.node('input',{type:"text",style:"width:150px",value:this.guildName});
		Event.observe(guildInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		guild.appendChild(guildInput);
		row.appendChild(guild);
		this.guildInput = guildInput;

		//creat the class selection cell
		var playerClass = Builder.node('td',{className:"center"});
		var classInput = this.GetClassDropdown();
		playerClass.appendChild(classInput);
		row.appendChild(playerClass);
		this.classInput = classInput;

		//create the dkp cell
		var dkp = Builder.node('td',{className:"center"});
		var dkpInput = Builder.node('input',{type:"text",style:"width:75px"});
		Event.observe(dkpInput,'keypress', this.OnKeyPress.bindAsEventListener(this));
		dkp.appendChild(dkpInput);
		row.appendChild(dkp);
		this.dkpInput = dkpInput;

		//create the action cell
		var action = Builder.node('td',{className:"center"});
		var actionImg = Builder.node('img',{src:Site.SiteRoot+"images/buttons/new.png",style:"vertical-align:text-bottom"});
		var actionLink = Builder.node('a',{href:"javascript:;"},"Add Player");
		Event.observe(actionLink,'click', this.OnAddPlayer.bindAsEventListener(this));


		action.appendChild(actionImg);
		action.innerHTML+=" ";
		action.appendChild(actionLink);
		row.appendChild(action);

		this.firstrow = row;
		return row;
	},

	/*================================================
	Generated when a user clicks on a row to be edited.
	This replaces the static content with a form input
	=================================================*/
	OnRowClick: function() {
		var data = $A(arguments);
		data.shift();
		var i = data[0];
		var selected = data[1];

		var row = this.rowObjects[i];
		var item = this.items[i];

		if(this.activeRow == i )
			return;

		if(this.activeRow != -1 && this.activeRow != i ) {
			//undo any current selection
			this.SaveActiveChanges();
		}

		this.activeRow = i;

		//name input
		var activeNameInput = Builder.node('input',{type:"text",style:"width:150px",value:item.player});
		Event.observe(activeNameInput,'keypress', this.OnEditKeyPress.bindAsEventListener(this));
		this.activeNameInput = activeNameInput;
		row.cells[0].innerHTML = "";
		row.cells[0].appendChild(activeNameInput);

		//guild input
		if(item.playerguild == null )
			item.playerguild = "";
		var activeGuildInput = Builder.node('input',{type:"text",style:"width:150px",value:item.playerguild});
		Event.observe(activeGuildInput,'keypress', this.OnEditKeyPress.bindAsEventListener(this));
		this.activeGuildInput = activeGuildInput;
		row.cells[1].innerHTML = "";
		row.cells[1].appendChild(activeGuildInput);

		//class input
		var activeClassInput = this.GetClassDropdown();
		Event.observe(activeClassInput,'change', this.OnEditSelect.bindAsEventListener(this));
		this.activeClassInput = activeClassInput;
		row.cells[2].innerHTML = "";
		row.cells[2].appendChild(activeClassInput);

		//figure out what the class input should select as a default option
		var selectedIndex = 0;
		for ( var i = 0 ; i < this.activeClassInput.options.length ; i++ ) {
			if( this.activeClassInput.options[i].value == item.playerclass ) {
				selectedIndex  = i;
				break;
			}
		}
		activeClassInput.selectedIndex = selectedIndex;

		//dkp input
		var activeDkpInput = Builder.node('input',{type:"text",style:"width:75px",value:item.dkp});
		Event.observe(activeDkpInput,'keypress', this.OnEditKeyPress.bindAsEventListener(this));
		this.activeDkpInput = activeDkpInput;
		row.cells[3].innerHTML = "";
		row.cells[3].appendChild(activeDkpInput);

		//show the save button
		$("save_"+this.activeRow).style.display = "inline";

		//force focus to the cell that they clicked on
		if(selected == 1 )
			this.activeNameInput.focus();
		else if(selected == 2 )
			this.activeGuildInput.focus();
		else if(selected == 3 )
			this.activeClassInput.focus();
		else if(selected == 4 )
			this.activeDkpInput.focus();

	},

	/*================================================
	Determines the row that is currently being edited
	and saves changes to the database. Afterwards,
	the row is revereted back to normal
	=================================================*/
	SaveActiveChanges: function() {
		var i = this.activeRow;
		if(i == -1 )
			return;
		var item = this.items[i];

		var newname = this.activeNameInput.value;
		var newguild = this.activeGuildInput.value;
		var newclass = this.activeClassInput.options[this.activeClassInput.selectedIndex].value;
		var newdkp = this.activeDkpInput.value;

		//check to see if a change occured. If so, we need to process
		//an ajax request
		if(this.items[i].playername != newname ||
		   this.items[i].playerguild != newguild ||
		   this.items[i].playerclass != newclass ||
		   this.items[i].dkp != newdkp) {
			new Ajax.Request(DKP.BaseUrl+"Admin/Manage/", {
				method:'post',
				parameters: {ajax:"EditPlayer",id:this.items[i].userid,name:newname,guild:newguild,playerclass:newclass,dkp:newdkp},
				onSuccess: this.SaveActiveChangesCallback.bindAsEventListener(this, i)
			});
		}

		//assume success
		this.items[i].player = newname;
		this.items[i].playerguild = newguild;
		this.items[i].playerclass = newclass;
		this.items[i].dkp = newdkp;
		this.RevertRowToNormal(i);

	},

	/*================================================
	Issued after save has been sent and processed by the
	server. Checks for any error
	=================================================*/
	SaveActiveChangesCallback: function(transport) {
		var data = $A(arguments);
		data.shift();
		var i = data[0];

		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert("Error: "+result[1]);
			if( result[2] ) {
				this.items[i].player = result[2].player;
				this.items[i].playerguild = result[2].playerguild;
				this.items[i].playerclass = result[2].playerclass;
				this.items[i].dkp = result[2].dkp;
				this.RevertRowToNormal(i);
			}
		}
		else {
			if( result[2] ) {
				this.items[i].userid = result[2];
			}
		}
		//we already assumed success... so we don't need to update
		//the gui on succeed.
	},

	/*================================================
	Reverts a row that is currently being edited back to normal
	=================================================*/
	RevertRowToNormal: function(i) {
		var row = this.rowObjects[i];
		if(typeof(row) != "undefined" ) {
			row.cells[0].innerHTML = this.items[i].player;
			row.cells[1].innerHTML = this.items[i].playerguild;

			var classImg = Builder.node('img',{src:Site.SiteRoot+"images/classes/small/"+this.items[i].playerclass+".gif",className:'classIcon'});
			row.cells[2].innerHTML = "";
			row.cells[2].appendChild(classImg);

			row.cells[3].innerHTML = this.items[i].dkp;

			$("save_"+i).style.display = "none";
		}
	},
	/*================================================
	Triggered when a user clicks on the delete button.
	Sends an ajax request to delete the specified user
	=================================================*/
	OnDeleteItem: function() {
		var result = confirm("Delete Player? All player history will be lost.");
		if( !result )
			return;

		var data = $A(arguments);
		data.shift();
		var i = data[0];

		if( i == this.activeRow )
			this.RevertRowToNormal(i);

		var item = this.items[i];
		var id = item.userid;

		new Ajax.Request(DKP.BaseUrl+"Admin/Manage", {
			method:'post',
			parameters: {ajax:"DeletePlayer",id:id},
			onSuccess: this.OnDeleteItemCallback.bindAsEventListener(this, i)
			});
	},

	/*================================================
	Ajax callback after the server has processed our
	delete command. Check for errors. If it all went through
	ok, go ahead and remove the player from the table
	=================================================*/
	OnDeleteItemCallback: function(transport) {

		var data = $A(arguments);
		data.shift();
		var i = data[0];

		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert(result[1]);
		}
		else {
			this.items[i].deleted = true;

			var toDelete = this.rowObjects[i];
			this.tableBody.removeChild(toDelete);
		}
	},

	/*================================================
	Called when the user wants to create a new player.
	Sent request via ajax to the server.
	=================================================*/
	OnAddPlayer: function() {

		if(this.activeRow != -1 ) {
			this.SaveActiveChanges(this.activeRow);
			this.activeRow = -1;
		}

		var name = this.nameInput.value;
		var guild = this.guildInput.value;
		var playerclass = this.classInput.value;
		var dkp = this.dkpInput.value

		if( name == "" )
			return;
		if( dkp == "" )
			dkp = 0;

		new Ajax.Request(DKP.BaseUrl+"Admin/Manage", {
			method:'post',
			parameters: {ajax:"AddPlayer",name:name,playerguild:guild,playerclass:playerclass,dkp:dkp},
			onSuccess: this.OnAddPlayerCallback.bindAsEventListener(this)
			});

		//clear the entry form and move back to the first input
		//this allows the user to enter many users quickly
		this.dkpInput.value = "";
		this.nameInput.value = "";
		this.nameInput.focus();

	},

	/*================================================
	Callback when a user has been added to the database.
	If everythign went ok, add the user to the table.
	Display any errors
	=================================================*/
	OnAddPlayerCallback: function(transport) {
		var result = transport.responseText.evalJSON(true);
		if(!result[0]) {
			alert(result[1]);
		}
		else {
			var item = result[2];

			this.Add(item);
			var row = this.GetRow(this.items.length - 1 );
			this.rowObjects[this.items.length-1] = row;

			//add the row to the visable table
			this.tableBody.insertBefore(row, this.firstRow.nextSibling);
		}
	},
	/*================================================
	Issued when a user clicks on a save link. Just
	saves changes and reverts edited row to normal
	=================================================*/
	OnSaveItem: function() {
		this.SaveActiveChanges();
		this.activeRow = -1;
	},

	/*================================================
	used to monitor key pressed when editing a player.
	If enter is pressed, treat it the same as hitting
	the save button.
	=================================================*/
	OnEditKeyPress: function(event) {
		if(Util.IsEnterEvent(event)) {
			this.SaveActiveChanges();
			this.activeRow = -1;
		}
	},

	/*================================================
	Triggered when a class is selected for a user in edit
	mode. Treated the same as clicking the save button.
	=================================================*/
	OnEditSelect: function(event) {
		this.SaveActiveChanges();
		this.activeRow = -1;

		//var el = event.element()
		//alert(el.parent);
	},

	/*================================================
	Detects when a user pressed enter while creating
	a new player. Detects if the enter button is pressed.
	=================================================*/
	OnKeyPress: function(event) {
		if(Util.IsEnterEvent(event))
			this.OnAddPlayer();
	},

	OnAltClick: function(i) {
		var data = $A(arguments);
		data.shift();
		var i = data[0];

		var user = this.items[i];

		document.location = DKP.BaseUrl + "Admin/PlayerAlts?player="+user.userid;
	},

	/*================================================
	Generates a dropdown / selection box of all the
	available classes to select from.
	=================================================*/
	GetClassDropdown: function() {
		var select = Builder.node('select',{style:'width:75px'},"");
		select.appendChild(Builder.node('option',{value:"Death Knight"},"Death Knight"));
		select.appendChild(Builder.node('option',{value:"Druid"},"Druid"));
		select.appendChild(Builder.node('option',{value:"Hunter"},"Hunter"));
		select.appendChild(Builder.node('option',{value:"Mage"},"Mage"));
		select.appendChild(Builder.node('option',{value:"Paladin"},"Paladin"));
		select.appendChild(Builder.node('option',{value:"Priest"},"Priest"));
		select.appendChild(Builder.node('option',{value:"Rogue"},"Rogue"));
		select.appendChild(Builder.node('option',{value:"Shaman"},"Shaman"));
		select.appendChild(Builder.node('option',{value:"Warlock"},"Warlock"));
		select.appendChild(Builder.node('option',{value:"Warrior"},"Warrior"));
		//select.appendChild(Builder.node('option',{},"Death Knight"));
		return select;
	}
});
