------------------------------------------------------------------------
-- GROUP FUNCTIONS
------------------------------------------------------------------------
-- This file contains methods related to working with the dkp table
-- and the current group. 
-- Contained in here are methods to:
-- *	Scan your group to find out what players are currently in it
-- *	Update the 'table to show' which determines the dkp table to show based on members
--		of your group, the current dkp table, and any filters that are selected
-- *	Update the gui with the table to show
------------------------------------------------------------------------

-- ================================
-- Rerenders the table to the screen. This is called 
-- on a few instances - when the scroll frame throws an 
-- event or when filters are applied or when group
-- memebers change. 
-- General structure:
-- First runs through the table to display and puts the data
-- into a temp array to work with
-- Then uses sorting options to sort the temp array
-- Calculates the offset of the table to determine
-- what information needs to be displayed and in what lines 
-- of the table it should be displayed
-- ================================
function WebDKP_UpdateTable()



	--WebDKP_Print("Scroll method called");
	-- Copy data to the temporary array
	local entries = { };
	for k, v in pairs(WebDKP_DkpTableToShow) do
		if ( type(v) == "table" ) then
			if( v[1] ~= nil and v[2] ~= nil and v[3] ~=nil and v[4] ~=nil) then
				tinsert(entries,{v[1],v[2],v[3],v[4],v[5],v[6]}); -- copies over name, class, dkp, tier,rank,rankindex
			end
		end
	end
	
	-- SORT
	table.sort(
		entries,
		function(a1, a2)
			if ( a1 and a2 ) then
				if ( a1 == nil ) then
					return 1>0;
				elseif (a2 == nil) then
					return 1<0;
				end
				if ( WebDKP_LogSort["way"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr"]] == a2[WebDKP_LogSort["curr"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr"]] > a2[WebDKP_LogSort["curr"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr"]] == a2[WebDKP_LogSort["curr"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr"]] < a2[WebDKP_LogSort["curr"]];
					end
				end
			end
		end
	);
	
	local numEntries = getn(entries);
	local offset = FauxScrollFrame_GetOffset(WebDKP_FrameScrollFrame);
	--WebDKP_Print("Before Update");
	FauxScrollFrame_Update(WebDKP_FrameScrollFrame, numEntries, 25, 20);
	--WebDKP_Print("After Update");
	-- Run through the table lines and put the appropriate information into each line
	for i=1, 25, 1 do
		local line = getglobal("WebDKP_FrameLine" .. i);
		local nameText = getglobal("WebDKP_FrameLine" .. i .. "Name");
		local classText = getglobal("WebDKP_FrameLine" .. i .. "Class");
		local dkpText = getglobal("WebDKP_FrameLine" .. i .. "DKP");
		local rankText = getglobal("WebDKP_FrameLine" .. i .. "Rank");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_FrameScrollFrame); 
		
		if ( index <= numEntries) then
			local playerName = entries[index][1];
			line:Show()

			nameText:SetText(entries[index][1]);
			classText:SetText(entries[index][2]);

			-- Set the text color if their class is valid.
			local classname = entries[index][2];
			classname = string.upper(classname);
			classname = string.gsub(classname, " ", "");
			-- If the class name matches up set the color otherwise leave it as the default color
			if RAID_CLASS_COLORS[classname] ~= nil then
				classText:SetTextColor(RAID_CLASS_COLORS[classname]["r"],RAID_CLASS_COLORS[classname]["g"],RAID_CLASS_COLORS[classname]["b"]);
				nameText:SetTextColor(RAID_CLASS_COLORS[classname]["r"],RAID_CLASS_COLORS[classname]["g"],RAID_CLASS_COLORS[classname]["b"]);
			end

			dkpText:SetText(entries[index][3]);
			rankText:SetText(entries[index][5]);
			-- kill the background of this line if it is not selected
			if( not WebDKP_DkpTable[playerName]["Selected"] ) then
				getglobal("WebDKP_FrameLine" .. i .. "Background"):SetVertexColor(0, 0, 0, 0);
			else
				getglobal("WebDKP_FrameLine" .. i .. "Background"):SetVertexColor(0.1, 0.1, 0.9, 0.8);
			end
		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end
end


-- ================================
-- Helper method that determines the table that should be shown. 
-- This runs through the dkp list and checks filters against each entry
-- If an entry passes it is moved to the table to show. If it doesn't pass
-- the test it is ignored. 
-- ================================
function WebDKP_UpdateTableToShow()
	GuildRoster();
	-- first, cleanup anyone who does not belong in the table
	WebDKP_CleanupTable();
	tableid = WebDKP_GetTableid();
	local tableid = WebDKP_GetTableid();
	-- clear the old table
	WebDKP_DkpTableToShow = { };
	-- increment through the dkp table and move data over
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			local playerName = k; 
			local playerClass = v["class"];
			local playerDkp = WebDKP_GetDKP(playerName, tableid);
			local playerEP = v["ep_"..tableid];
			--local playerEP = WebDKP_DkpTable[playerName]["ep_"..tableid];

			if playerEP == nil or playerEP == 0 then
				playerEP = 0;
				WebDKP_DkpTable[playerName]["ep_"..tableid] = 0;
			end
			local playerGP = WebDKP_DkpTable[playerName]["gp_"..tableid];
			if playerGP == nil then
				playerGP = 1;
				WebDKP_DkpTable[playerName]["gp_"..tableid] = 1;
			end
			local playerTier = floor((playerDkp-1)/WebDKP_TierInterval);
			local playerStandby = v["standby"];
			if( playerDkp == 0 ) then
				playerTier = 0;
			end

			-- This loop goes through the guild roster to determine the guild rank, Added by Zevious
			playerRank = "PUG";
			playerIndex = 50;
			for ci = 1, GetNumGuildMembers(true) do
				guildname, guildrank, rankindex, _, _, _, _, _, isonline = GetGuildRosterInfo(ci);
				if guildname == playerName then
					playerRank = guildrank;
					playerIndex = rankindex;
					ci = GetNumGuildMembers(true) + 10;
				end
			end


			-- if it should be displayed (passes filter) add it to the table
			if (WebDKP_ShouldDisplay(playerName, playerClass, playerDkp, playerTier,playerStandby)) then
				tinsert(WebDKP_DkpTableToShow,{playerName,playerClass,playerDkp,playerTier,playerRank,playerIndex,playerEP,playerGP});
			else
				-- if it is not displayed, deselect it automatically for us
				WebDKP_DkpTable[playerName]["Selected"] = false;
			end
		end
	end
	-- now need to run through anyone else who is in our current raid / party
	-- They may not have dkp yet and may not be in our dkp table. Use this oppurtunity 
	-- to add them to the table with 0 points and add them to the to display table if appropriate
	-- table to be displayed

	for key, entry in pairs(WebDKP_PlayersInGroup) do
		if ( type(entry) == "table" ) then
			local playerName = entry["name"];
		-- Fixes some sort of weird glitch where the playerName is nil	
		if playerName ~= nil then
			-- is this a new person we haven't seen before?
			if ( WebDKP_DkpTable[playerName] == nil) then
				-- new person, they need to be added
				local playerClass = entry["class"];
				local playerDkp = 0;
				local playerTier = 0;
				local playerEP = 0;
				local playerGP = 1;
				WebDKP_MakeSureInTable(playerName, tableid, playerClass, playerDkp)

				-- This loop goes through the guild roster to determine the guild rank, Added by Zevious
				playerRank = "PUG";
				playerIndex = 50;			
				for ci = 1, GetNumGuildMembers(true) do
					playerRank = "PUG";
					playerIndex = 50;
					guildname, guildrank = GetGuildRosterInfo(ci);
					if guildname == playerName then
						playerRank = guildrank;
						playerIndex = rankindex;
						ci = GetNumGuildMembers(true) + 10;
					end
				end	
	
				-- do a final check to see if we should display (pass all filters, etc.)
				if (WebDKP_ShouldDisplay(playerName, playerClass, playerDkp, playerTier)) then
					tinsert(WebDKP_DkpTableToShow,{playerName,playerClass,playerDkp,playerTier,playerRank,playerIndex,playerEP,playerGP});
				else
					WebDKP_DkpTable[playerName]["Selected"] = false;
				end
			end
		end
		end
	end
end


-- ================================
-- Updates the list of players in our current group.
-- First attempts to get raid data. If user isn't in a raid
-- it checks party data. If user is not in a party there 
-- is no information to get
-- ================================
function WebDKP_UpdatePlayersInGroup()
	-- Updates the list of players currently in the group
	-- First attempts to get this data via a query to the raid. 
	-- If that failes it resorts to querying for party data
	local numberInGroup = GetNumGroupMembers();
	local inBattleground = WebDKP_InBattleground();
	
	WebDKP_PlayersInGroup = {};
	-- Is a raid going?
	if ( IsInRaid() and inBattleground == false ) then
		-- Yes! Load raid data...
		local name, class, guild;
		for i=1, numberInGroup do
			name, _, _, _, class, _, _, _ , _ = GetRaidRosterInfo(i);
			WebDKP_PlayersInGroup[i]=
			{
				["name"] = name,
				["class"] = class,
			};
		
		end
	-- Is a party going?
	elseif ( IsInGroup() and inBattleground == false ) then
		-- Yes! Load party data instead...
		local name, class, guild, playerHandle;
		for i=1, numberInGroup do
			playerHandle = "party"..i;
			name = UnitName(playerHandle);
			class = UnitClass(playerHandle);
			WebDKP_PlayersInGroup[i]=
			{
				["name"] = name,
				["class"] = class,
			};
			
		end
		-- this doesn't load the current player, so we need to add them manually
		WebDKP_PlayersInGroup[numberInGroup+1]=
		{
			["name"] = UnitName("player"),
			["class"] = UnitClass("player"),
		};
	else
	-- not in party or raid... go ahead and load yourself
		WebDKP_PlayersInGroup[0]=
		{
			["name"] = UnitName("player"),
			["class"] = UnitClass("player"),
		};
	end
end

-- ================================
-- Returns true if the player is currently inside a battle ground
-- instance. 
-- ================================
function WebDKP_InBattleground() 

	-- temp vars
	local status, mapName, instanceID, minlevel, maxlevel;
	
	-- iterate through all of our battle ground queues
	for i=1, 10 do
		
		-- return true if anyone of them is active
		status, mapName, instanceID, minlevel, maxlevel, teamSize = GetBattlefieldStatus(i);
		if ( status == "active" ) then
			return true;
		end	
	end
	
	return false;
end

-- ================================
-- Returns true if everyone in the current group is selected. 
-- This is a helper method when displaying messages to chat. 
-- If everyone is selected you can just say "awarded points to everyone"
-- versus listing out everyone who was selected invidiually
-- ================================
function WebDKP_AllGroupSelected()
	-- First try running through the raid and see if they are all selected
	local name, class;
	local numberInGroup = GetNumGroupMembers();
	if IsInRaid() then
		for i=1, numberInGroup do
			name, _, _, _, _, _, _, _ , _ = GetRaidRosterInfo(i);
			if ( not WebDKP_DkpTable[name]["Selected"]) then
				return false;
			end
		end
		return true;
	elseif IsInGroup() then
		for i=1, numberInGroup do
			playerHandle = "party"..i;
			name = UnitName(playerHandle);
			if ( not WebDKP_DkpTable[name]["Selected"]) then
				return false;
			end
		end
		--before we return true we also need to check the current player...
		if ( not WebDKP_DkpTable[UnitName("player")]["Selected"]) then
			return false;
		end
		return true;
	end
	-- entire group isn't selected, do things manually
	return false;
end



-- ================================
-- Removes anyone from the table who has 0 DKP. This cleans up anyone who was
-- auto added when they were detected in the group, but didn't actually get anything.
-- ================================
function WebDKP_CleanupTable() 
	
	-- iterate through all players in our table
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
		
			-- if we see a player can be trimmed, and is no longer
			-- in our party, go ahead and trim them. The trim flag would
			-- have been set to false if they were given any points. 
			local playerName = k; 
			local cantrim = v["cantrim"];
			local inGroup = WebDKP_PlayerInGroup(playerName);
			
			-- if can trim is not set, assume false ( this would be the 
			-- case of a player being added manually on the site then synced)
			if ( cantrim == nil ) then
				cantrim = false;
			end
			
			-- if the can trim flag is false, do a second check. Here 
			-- we'll see if the player has dkp in any of the tables. If they
			-- don't have dkp anywhere, we can remove them as well
			local standby = WebDKP_DkpTable[playerName]["standby"];
			if ( cantrim == false and standby == 0) then 
				local hasDkp = WebDKP_PlayerHasDKP(playerName);
				if ( hasDkp == false ) then
					cantrim = true;
				end
			end
			
			-- only remove if they are elegible to be trimmed, and 
			-- no longer in the active group
			-- If EPGP is enabled do not remove people with 0 DKP because we are tracking EP and GP
			if ( cantrim == true and inGroup == false and WebDKP_Options["EPGPEnabled"] == 0) then
				WebDKP_DkpTable[playerName] = nil; 
			end
		end
	end

end

-- ================================
-- Helper method. Returns true if the current player should be displayed
-- on the table by checking it against current filters
-- ================================
function WebDKP_ShouldDisplay(name, class, dkp, tier, standby)
	local inguildflag = 0;
	local inguildonlineflag = 0;
	if WebDKP_DkpTable[name]["standby"] == nil then
		WebDKP_DkpTable[name]["standby"] = 0;
	end

	if (name == "Unknown") then
		return false;
	end

	if (WebDKP_Filters[class] == 0) then
		return false;
	end 
	if (WebDKP_Filters["Standby1"] == 1 and standby == 1) then
		return true;
	end

	if (WebDKP_Filters["Group"] == 1 and WebDKP_PlayerInGroup(name) == false) then
		return false;
	end
	if (WebDKP_Filters["Standby2"] == 1 and standby == 0) then
		return false;
	end

	
	-- This loop goes through the guild roster to determine if each player is in the guild.
	for ci = 1, GetNumGuildMembers(true) do
		guildname, _, _, _, _, _, _, _, isonline = GetGuildRosterInfo(ci)
		if name == guildname then
			if isonline ~= nil then
				inguildonlineflag = 1;
			end
			inguildflag = 1;
			ci = GetNumGuildMembers() + 100;
		end
	end

	-- Check to see if this person is in the party and is an alt, if the include alts option is checked
	if WebDKP_Options["LimitAlts2"] == 1 then
		if WebDKP_PlayerInGroup(name) == false and WebDKP_Alts[name] ~= nil then
			return false;
		end
	end
	-- Check to see if the player is an alt and if they are and exlcude alts is checked remove them
	if WebDKP_Options["LimitAlts"] == 1 and WebDKP_Alts[name] ~= nil then
		return false;
	end

	
	-- Compares the player's guild to WebDKP user guild Added by Zevious
	if WebDKP_Options["LimitGuild"] == 1 then
		if inguildflag == 0 then
			return false;
		end
	end
	if WebDKP_Options["LimitGuildOnline"] == 1 then
		if inguildonlineflag == 0 then
			return false;
		end
	end

	classcompare = UnitClass(name);
	-- Added by Zevious to correct the WebySynch Armory issue with classes.
	if class ~= classcompare and classcompare ~= nil then
		WebDKP_DkpTable[name]["class"] = classcompare;
	end

	return true; 
end

-- ==================================================================================
-- Function to determine guild rank if the person is in the users guild
-- ==================================================================================
function WebDKP_GetGuildRank(playerName)

	local playerRank = "PUG";
	local playerIndex = 50;			
	for ci = 1, GetNumGuildMembers(true) do
		guildname, guildrank = GetGuildRosterInfo(ci);
		if guildname == playerName then
			playerRank = guildrank;
			return playerRank;
			--ci = GetNumGuildMembers(true) + 10;
		end
	end
	return playerRank;

end