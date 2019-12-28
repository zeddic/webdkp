------------------------------------------------------------------------
-- AWARDS	
------------------------------------------------------------------------
-- This file contains methods related to awarding/deducting DKP and 
-- items. It also contains methods for appending this data to the log file. 
------------------------------------------------------------------------


-- ================================
-- Called when user clicks on the 'award item' box. 
-- Gets the first selected player in the list, and the
-- contents of the award item edit boxes. Uses this to 
-- display a short blirb to the screen then recordes 
-- the changes
-- ================================
function WebDKP_AwardItem_Event()
	local name, class, guild;
	local cost = WebDKP_AwardItem_FrameItemCost:GetText();
	if ( cost == nil or cost=="") then
		WebDKP_Print("No value was entered so the DKP defaulted to 0.");
		cost = "0";
	end
	local percentcost = string.find(cost, "%%");
	local percentflag = 0;
	local tableid = WebDKP_GetTableid();
	local item = WebDKP_AwardItem_FrameItemName:GetText();

	if ( item == nil or item=="" ) then
		WebDKP_Print("You must enter an item name.");
		PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
		return;
	end

	if percentcost ~= nil then
		-- This means they are entering a percent so calculate the proper cost
		-- Substitute the % with "" so we are left with just the number as a string
		cost = string.gsub(cost, "%%", "")
		cost = tonumber(cost);
		percentflag = 1;
	end
		
	cost = tonumber(cost);
	cost = WebDKP_ROUND(cost,2);

	local points = cost * -1;
	local player = WebDKP_GetSelectedPlayers(1);
	
	if ( player == nil or player == "") then
		WebDKP_Print("No player was selected to award. Award NOT made.");
		PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
	else
		if percentflag == 1 then
			local actualname = player[0]["name"];
			cost = (cost / 100) * WebDKP_DkpTable[actualname]["dkp_"..tableid] * -1;
			points = WebDKP_ROUND(cost,2);
		end
		
		if WebDKP_Options["EPGPEnabled"] == 1 then
			WebDKP_AddEP(cost, item, "true", player);
			WebDKP_UpdateTableToShow();
			WebDKP_UpdateEPGPTable();

		else
			WebDKP_AddDKP(points, item, "true", player);
			WebDKP_UpdateTableToShow();
			WebDKP_UpdateTable();
		end
		WebDKP_AnnounceAwardItem(points, item, player[0]["name"]);
	end
end

-- ================================
-- Called when user clicks on 'award dkp' on the award 
-- dkp tab. Gets data from the award dkp edit boxes. 
-- Uses this to display a little blirb, then recodes
-- this information for all players currently selected
-- (note, if player is hidden due to filter, they are automattically
-- deselected)
-- ================================
function WebDKP_AwardDKP_Event()
	local name, class, guild;
	local points = WebDKP_AwardDKP_FramePoints:GetText();
	local reason = WebDKP_AwardDKP_FrameReason:GetText();
	if reason == "" or reason == nil then
		reason = "Not Specified";
	end

	points = tonumber(points);
	if ( points == nil or points=="") then
		WebDKP_Print("You must enter points to award.");
		PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
		return;
	end
	
	points = WebDKP_ROUND(points,2);
	local players = WebDKP_GetSelectedPlayers(0);
	
	if ( players == nil ) then
		WebDKP_Print("No players were selected. Award NOT made.");
		PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
	else 
		-- Chcek to see if EPGP is enabled, if so then we need to use the EP function
		if WebDKP_Options["EPGPEnabled"] == 1 then
			WebDKP_AddEP(points, reason, "false", players);
			WebDKP_UpdateTableToShow();
			WebDKP_UpdateEPGPTable();

		else
			WebDKP_AddDKP(points, reason, "false", players);
			WebDKP_UpdateTableToShow();
			WebDKP_UpdateTable();
		end
		WebDKP_AnnounceAward(points,reason);
		-- Update the table so we can see the new dkp status
	end
	WebDKP_UnselectAll();
end

-- ================================
-- Adds the specified dkp / reason to all selected players
-- If this is an item award, it is only awarded to the first player
-- If it is an item award and zero-sum is used, an automatted
-- zero sum award is also given
-- ================================
function WebDKP_AddDKP(points, reason, forItem, players)
	local date  = date("%Y-%m-%d %H:%M:%S");
	local location = GetZoneText();
	local tableid = WebDKP_GetTableid();
	local awardedBy = UnitName("player");
	local pointsholder = points;


	if WebDKP_Options["EnableSynch"] == 1 then
		WebDKP_Synch_Auto(points, forItem, players, reason, date); 		-- Runs the Synch Code
	end
	if (not WebDKP_Log) then
		WebDKP_Log = {};
	end
	
	local _,itemName,itemLink = WebDKP_GetItemInfo(reason);
	local itemID = WebDKP_GetItemID(itemLink);

	WebDKP_Log["Version"] = 2;
	if forItem == "true" then
		reason = itemName;
		--next, make sure this award exists in the log
		if (not WebDKP_Log[reason.." "..date]) then
			WebDKP_Log[reason.." "..date] = {};
		end

		WebDKP_Log[reason.." "..date]["itemlink"] = itemLink;
		WebDKP_Log[reason.." "..date]["reason"] = reason;
		WebDKP_Log[reason.." "..date]["itemid"] = itemID;
	else
		--next, make sure this award exists in the log
		if (not WebDKP_Log[reason.." "..date]) then
			WebDKP_Log[reason.." "..date] = {};
		end

		WebDKP_Log[reason.." "..date]["itemlink"] = reason;
		WebDKP_Log[reason.." "..date]["reason"] = reason;
	end
	
	WebDKP_Log[reason.." "..date]["date"] = date;
	WebDKP_Log[reason.." "..date]["foritem"] = forItem;
	WebDKP_Log[reason.." "..date]["zone"] = location;
	WebDKP_Log[reason.." "..date]["tableid"] = tableid;
	WebDKP_Log[reason.." "..date]["awardedby"] = awardedBy;
	WebDKP_Log[reason.." "..date]["points"] = points;
	if itemID == nil and forItem =="true" then
		WebDKP_Log[reason.." "..date]["itemid"] = "0";
	end
	
	if (not WebDKP_Log[reason.." "..date]["awarded"]) then
		WebDKP_Log[reason.." "..date]["awarded"] = {};
	end
	
	for k, v in pairs(players) do
		if ( type(v) == "table" ) then
			name = v["name"]; 
			class = v["class"];
			guild = WebDKP_GetGuildName(name);
			points = pointsholder;
			
			-- Check to see if the awardee is an alt
			if WebDKP_Alts[name] ~= nil then
				-- It's an alt get the primary players name
				name = WebDKP_Alts[name];
			end

			local DKPCapVal = tonumber(WebDKP_Options["dkpCapLimit"]);
			if WebDKP_Options["dkpCap"] == 1 and (WebDKP_DkpTable[name]["dkp_"..tableid] + points > DKPCapVal) and WebDKP_Options["EPGPEnabled"] == 0 then

				points = DKPCapVal - WebDKP_DkpTable[name]["dkp_"..tableid];

				-- Create the log entry array
				if (not WebDKP_Log[reason.." CAP"..points.." "..date]) then
					WebDKP_Log[reason.." CAP"..points.." "..date] = {};
					WebDKP_Log[reason.." CAP"..points.." "..date]["itemlink"] = itemLink;
					WebDKP_Log[reason.." CAP"..points.." "..date]["reason"] = reason.." CAP"..points;
					WebDKP_Log[reason.." CAP"..points.." "..date]["date"] = date;
					WebDKP_Log[reason.." CAP"..points.." "..date]["foritem"] = forItem;
					WebDKP_Log[reason.." CAP"..points.." "..date]["zone"] = location;
					WebDKP_Log[reason.." CAP"..points.." "..date]["tableid"] = tableid;
					WebDKP_Log[reason.." CAP"..points.." "..date]["awardedby"] = awardedBy;
					WebDKP_Log[reason.." CAP"..points.." "..date]["points"] = points;
					WebDKP_Log[reason.." CAP"..points.." "..date]["awarded"] = {};
				end
				--add them to the log entry
				WebDKP_Log[reason.." CAP"..points.." "..date]["awarded"][name] = {};
				WebDKP_Log[reason.." CAP"..points.." "..date]["awarded"][name]["name"]=name;
				WebDKP_Log[reason.." CAP"..points.." "..date]["awarded"][name]["guild"]=guild;
				WebDKP_Log[reason.." CAP"..points.." "..date]["awarded"][name]["class"]=class;
			
				WebDKP_AddDKPToTable(name, class, points);

			else
				WebDKP_AddDKPToTable(name, class, points);
				--add them to the log entry
				WebDKP_Log[reason.." "..date]["awarded"][name] = {};
				WebDKP_Log[reason.." "..date]["awarded"][name]["name"]=name;
				WebDKP_Log[reason.." "..date]["awarded"][name]["guild"]=guild;
				WebDKP_Log[reason.." "..date]["awarded"][name]["class"]=class;

				-- If awarding an item, only 1 person should be recorded as having received it
				if ( forItem == "true" ) then
					break;
				end
			end
		end
	end
	
	-- if this is an item award and we are using zero-sum dkp, we need to give automated
	-- zero sum awards too
	if ( WebDKP_WebOptions["ZeroSumEnabled"]==1 and forItem=="true") then
		WebDKP_AwardZeroSum(points, reason, date);
	end
	WebDKP_UpdateLogTable();
end

-- This function actually adds the DKP to the DKP Table
function WebDKP_AddDKPToTable(name, class, points, tableidfrom)
	-- if the 'combine alts w/ main' option is enabled, these points need to go to the 
	-- main instead of the alt
	local tableid = WebDKP_GetTableid();
	if ( WebDKP_WebOptions["CombineAlts"] == 1 ) then
		name = WebDKP_GetMain(name);
	end

	local tableid = WebDKP_GetTableid();
	if (tableidfrom ~= nil) then
		tableid = tableidfrom;
	end
	WebDKP_MakeSureInTable(name, tableid, class , 0);
	
	if WebDKP_Options["dkpCap"] == 1 and WebDKP_Options["EPGPEnabled"] == 0 then
		local DKPCapVal = tonumber(WebDKP_Options["dkpCapLimit"]);
		if (WebDKP_DkpTable[name]["dkp_"..tableid] + points > DKPCapVal) then
			points = DKPCapVal - WebDKP_DkpTable[name]["dkp_"..tableid];
		end
	end
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_DkpTable[name]["dkp_"..tableid] = WebDKP_DkpTable[name]["dkp_"..tableid] + points;
	else
		WebDKP_DkpTable[name]["gp_"..tableid] = WebDKP_DkpTable[name]["gp_"..tableid] + points;
	end
	
	-- since this player has points, it can't be auto trimmed any more
	WebDKP_DkpTable[name]["cantrim"] = false;
end

-- ================================
-- Helper method for ZeroSum Award. Called when a player
-- is recieving an item and the guild is using zero sum. 
-- This method must run through everyone in the current
-- party and give them an award equal to, but opposite
-- the cost of the item just given. 
-- ================================
function WebDKP_AwardZeroSum(points, reason, date)

	WebDKP_UpdatePlayersInGroup();
	local allplayers = WebDKP_PlayersInGroup;
	local numPlayers = WebDKP_GetTableSize(WebDKP_PlayersInGroup);
	-- Check to see if standby players should count and if any are in standby
	if WebDKP_Options["ZeroSumStandby"] == 1 then
		for k, v in pairs(WebDKP_DkpTable) do
			if ( type(v) == "table" ) then
				local playerName = k; 
				local playerClass = v["class"];
				local playerStandby = v["standby"];
				if playerStandby ~= nil and playerStandby == 1 then
					numPlayers = numPlayers + 1;
					allplayers[numPlayers]=
					{
						["name"] = playerName,
						["class"] = playerClass,
					};
				end
			end
		end
	end

	local toAward = (points * -1) / numPlayers;
	toAward = WebDKP_ROUND(toAward, 2 );
	reason = "ZeroSum: "..reason;

	-- Use existing code
	WebDKP_AddDKP(toAward, reason, "false", allplayers)
end

-- ================================
-- Returns a table of all the selected players from the main dkp table.
-- Limit specifiecs the maximum number players that should be returned. 
-- If limit = 0, there is no limit
-- ================================
function WebDKP_GetSelectedPlayers(limit) 
	local toReturn = {}; 
	local count = 0; 
	for key_name, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			if( v["Selected"] ) then
				toReturn[count] = {
					["name"] = key_name,
					["class"] = v["class"],
				}
				count = count + 1; 
				if ( limit~=0 and count >= limit ) then
					return toReturn;
				end
			end		
		end
	end
	if ( count == 0 ) then
		return nil;
	else
		return toReturn;
	end
end

-- ================================
-- WebDKP Decay Function provided by Moonblaze (At least I think)
-- Slight modifications by Zevious to allow the user to define the decay factor.
-- ================================
function WebDKP_Decay()
	local name, class, guild;
	local players = WebDKP_GetSelectedPlayers(0);
	local date = date("%Y-%m-%d %H:%M:%S");
	local location = GetZoneText();
	local tableid = WebDKP_GetTableid();
	local awardedBy = UnitName("player");
	local counter = 0;
	local decay_value = tonumber(WebDKP_Options["Decay"]);
    if decay_value ~= nil and decay_value ~= "" then
        -- Send if Synching is Enabled
        if WebDKP_Options["EnableSynch"] == 1 then
            WebDKP_Synch_Auto(decay_value, "false", players, "Decay", date)
        end

        if (not WebDKP_Log) then
            WebDKP_Log = {};
        end
        if ( players == nil ) then
            WebDKP_Print("No players were selected. Award NOT made.");
            PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
        else
        for k, v in pairs(players) do
            if ( type(v) == "table" ) then
                name = v["name"];
                class = v["class"];
                dkp = WebDKP_GetDKP(name); -- how much dkp do they have now
                if ( dkp >= 2 and decay_value > 0) then
                    points = WebDKP_ROUND(dkp * decay_value, 0) * -1;
                elseif (dkp < 2 and decay_value < 0) then
                    points = WebDKP_ROUND(dkp * decay_value, 0);
                else
                    points = 0;
                end
                guild = WebDKP_GetGuildName(name);
                WebDKP_AddDKPToTable(name, class, points);

                local reason = "Decay_"..counter;
                if (not WebDKP_Log[reason.." "..date]) then
                    WebDKP_Log[reason.." "..date] = {};
                end

                WebDKP_Log["Version"] = 2;
                WebDKP_Log[reason.." "..date]["itemlink"] = "Decay Award";
                WebDKP_Log[reason.." "..date]["reason"] = reason;
                WebDKP_Log[reason.." "..date]["date"] = date;
                WebDKP_Log[reason.." "..date]["foritem"] = "false";
                WebDKP_Log[reason.." "..date]["zone"] = location;
                WebDKP_Log[reason.." "..date]["tableid"] = tableid;
                WebDKP_Log[reason.." "..date]["awardedby"] = awardedBy;
                WebDKP_Log[reason.." "..date]["points"] = points;

                if (not WebDKP_Log[reason.." "..date]["awarded"]) then
                    WebDKP_Log[reason.." "..date]["awarded"] = {};
                end

                WebDKP_Log[reason.." "..date]["awarded"][name] = {};
                WebDKP_Log[reason.." "..date]["awarded"][name]["name"]=name;
                WebDKP_Log[reason.." "..date]["awarded"][name]["guild"]=guild;
                WebDKP_Log[reason.." "..date]["awarded"][name]["class"]=class;
                counter = counter + 1;
            end
        end
        WebDKP_Print("Decay has been applied.");
        WebDKP_UpdateTableToShow();
        WebDKP_UpdateTable();
        WebDKP_UpdateLogTable();
        end
    end
end

-- =========================================================================
-- Adds the specified dkp / reason to all selected players
-- This function is for the Log Undo award to correct DKP
-- No announcements or new log entries are created with this function
-- =========================================================================
function WebDKP_AddLogDKP(points, players)
	local tableid = WebDKP_GetTableid();

	for k, v in pairs(players) do
		if ( type(v) == "table" ) then
			name = v["name"]; 
			WebDKP_AddDKPToTable(name, _, points);

			
			-- If awarding an item, only 1 person should be recorded as having recieved it
			if ( forItem == "true" ) then
				break;
			end
		end
	end
	WebDKP_UpdateDKPTable();
end
