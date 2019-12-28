------------------------------------------------------------------------
-- UTILITY
------------------------------------------------------------------------
-- This file contains utility methods used by the rest of the addon
------------------------------------------------------------------------

-- ================================
-- Helper method. Returns the id of the currently
-- selected table (if working with multiple dkp tables)
-- ================================
function WebDKP_GetTableid()
	local tableid = WebDKP_Frame.selectedTableid;
	if (tableid == nil ) then
		tableid = 1;
	end
	return tableid;
end





-- ================================
-- Helper method for should display. Returns true if the specified player
-- is in the current group
-- ================================
function WebDKP_PlayerInGroup(name)
	for key, entry in pairs(WebDKP_PlayersInGroup) do
		if ( type(entry) == "table" ) then
			if ( entry["name"] == name) then
				return true;
			end
		end
	end
	return false;
end


-- ================================
-- Returns the guild name of a specified player. This attempts this
-- in a few ways. First tries to get it via raid data. If not in a raid
-- it attempts to get it via party data. If all these fail, it returns
-- "Unknown" which is a marker for the webdkp.com site to try to get
-- the real guild name sometime in the future. 
-- ================================
function WebDKP_GetGuildName(playerName)
	-- this is a big pain - we can't just query a player for a guild, 
	-- we need to find their slot in the current raid / party and query
	-- that slot...
	
	-- First try running through all the people in the current raid...
	local numberInGroup = GetNumGroupMembers();
	local name, class;
	for i=1, numberInGroup do
		name, _, _, _, _, _, _, _ , _ = GetRaidRosterInfo(i);
		if ( name == playerName) then
			guild, _, _ = GetGuildInfo("raid"..i);
			return guild;
		end
	end
	
	-- No go, now try running through people in the current party --
	for i=1, numberInGroup do
		playerHandle = "party"..i;
		name = UnitName(playerHandle);
		if( name == playerName ) then
			guild, _, _ = GetGuildInfo("raid"..i);
			return guild;
		end
	end
	
	-- no go, try the current player
	if( playerName == UnitName("player") ) then
		guild, _, _ = GetGuildInfo("player");
		return guild;
	end
	
	-- all failed, return unknown
	return "Unknown";

end

-- ================================
-- Helper method for awarding an item. 
-- Returns the name of the first selected player
-- If no one is selected returns 'NONE'
-- ================================
function WebDKP_GetFirstSelectedPlayer()
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			if( v["Selected"] ) then
				name = k; 
				return name;
			end
		end
	end
	return "NONE";
end

-- ================================
-- Helper method. Returns the size
-- of the passed table. Returns 0 if
-- the passed variable is nil.
-- ================================
function WebDKP_GetTableSize(table)
	local count = 0;
	if( table == nil ) then
		return count;
	end
	for key, entry in pairs(table) do
		count = count + 1;
	end
	return count;
end

-- ================================
-- Prints a message to the console. Used for debugging
-- ================================
function WebDKP_Print(toPrint)
	DEFAULT_CHAT_FRAME:AddMessage(toPrint, 1, 1, 0);
end

-- ================================
-- Gets information on the specified item, where item is the item link
-- Returns: color, item name, itemLink
-- ================================
function WebDKP_GetItemInfo(sItem)
	local itemName, itemLink, itemRarity, itemLevel, itemMinLevel, itemType, itemSubType, itemStackCount, itemEquipLoc, invTexture = GetItemInfo(sItem);
	if ( itemRarity and itemName and itemLink ) then
		return itemRarity, itemName, itemLink, itemLevel, itemEquipLoc;
	else
		return 0, sItem, sItem;
	end
end

-- ================================
-- Returns true if the given item name is the name of an item 
-- that should be ignored. Items to ignore are set
-- in WebDKP_IgnoreItems in WebDKP.lua
-- ================================
function WebDKP_ShouldIgnoreItem(itemName) 
	for key, value in pairs(WebDKP_IgnoreItems) do
		if ( itemName == value ) then
			return true;	
		end
	end
	return false;
end


-- ================================
-- Selects a single specified player in the table. 
-- All other players are unselected. 
-- Causes this player to be shown in the table, even if
-- they do not pass filters
-- ================================
function WebDKP_SelectPlayerOnly(toHighlight)
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			local playerName = k;
			if ( playerName == toHighlight ) then
				WebDKP_DkpTable[playerName]["Selected"] = true;
			else
				WebDKP_DkpTable[playerName]["Selected"] = false;
			end
		end
	end
	-- If the player is not currently shown in the table, add them (otherwise we can't see if they are highlighted)
	if( WebDKP_PlayerIsShown(toHighlight) == 0 ) then
		WebDKP_ShowPlayer(toHighlight);
	end
	WebDKP_UpdateTable();
end

-- ================================
-- Returns true if the specified player is currently 
-- shown in the table to the left
-- ================================
function WebDKP_PlayerIsShown(playerName)
	for k, v in pairs(WebDKP_DkpTableToShow) do
		if ( type(v) == "table" ) then
			local player = v[1];
			if ( player == playerName) then
				-- yes, they are being shown
				return 1;
			end
		end
	end
	--no, they arn't being shown
	return 0;
end

-- ================================
-- Shows a player on the table to the left
-- Used if they are not automattically shown via a filter
-- and to force them to be appended. 
-- ================================
function WebDKP_ShowPlayer(playerName)
	if ( WebDKP_DkpTable[playerName] == nil ) then
		return;
	end
	local tableid = WebDKP_GetTableid();
	local playerClass = WebDKP_DkpTable[playerName]["class"];
	local playerDkp = WebDKP_DkpTable[playerName]["dkp_"..tableid];
	if ( playerDkp == nil ) then 
		playerDkp = 0;
	end
	local playerTier = floor((playerDkp-1)/WebDKP_TierInterval);
	if( playerDkp == 0 ) then
		playerTier = 0;
	end
	tinsert(WebDKP_DkpTableToShow,{playerName,playerClass,playerDkp,playerTier});
end



-- ================================
-- Helper method. Rounds the given number to the specified number
-- of decimal places.
-- Example: Round(22.4242,2) returns 22.42
-- ================================
function WebDKP_ROUND( num, idp )
	return tonumber( string.format("%."..idp.."f", num ) );
end

-- ================================
-- Returns true if the given player has dkp in ANY
-- table. 
-- ================================
function WebDKP_PlayerHasDKP(playerName)
	
	local dkp, tableid;
	
	-- if this account has multiple tables, we will iterate through each. 
	-- Check to see if the player has dkp in any table. If they do, return 
	-- true. 
	if ( WebDKP_Tables ~= nil and next(WebDKP_Tables)~=nil ) then
		for key, entry in pairs(WebDKP_Tables) do
			if ( type(entry) == "table" ) then
				tableid = entry["id"]; 
				-- do they have dkp in this table?
				dkp = WebDKP_GetDKP(playerName, tableid); 
				if ( dkp ~= 0 ) then 
					return true;
				end
			end
		end
	else
		-- if we don't have table data, its likely that theres only a single
		-- default table. Check that
		dkp = WebDKP_GetDKP(playerName);
		if ( dkp ~= 0 ) then 
			return true;
		end
	end
	
	-- if data was found no where, they don't have any dkp at all
	return false;
end

-- ================================
-- Helper method. Gets the dkp of the passed player
-- in the currently active table.
-- If this player is an alt for another player and
-- the combine alts feature is enabled - 
-- this will look up the dkp of the alt instead.
-- Parameters: 
-- PlayerName   the name of the player to get dkp for
-- Tableid 		[optional] the id of the table to get the dkp for. Optional. If passed nil, it will lookup the 
--				tableid from the drop down.
-- addToTable   [optional] If the player isn't present in the table should they be added with 0 dkp? Defaults to no
-- Class		[optional] the class of the person in case they need to be added to the table. If not know, place nil. (will default to druid)
-- ================================
function WebDKP_GetDKP(playerName, tableid, addToTable, class)
	if (playerName == nil ) then
		return 0;
	end
	if ( tableid == nil ) then
		tableid = WebDKP_GetTableid();
	end
	
	-- if this player is an alt mapped to another player 
	-- we'll need to lookup the main (only if option enabled)
	local main;
	local alt;
	if ( WebDKP_Alts[playerName] ~= nil and WebDKP_WebOptions["CombineAlts"] == 1 ) then
		-- they have an alt and the option is enabled, lookup their dkp instead
		alt = playerName;
		main = WebDKP_Alts[playerName];
		if ( addToTable ~= nil ) then
			WebDKP_MakeSureInTable(alt, tableid, class);
			WebDKP_MakeSureInTable(main, tableid);
		end
	else
		main = playerName;
		if ( addToTable ~= nil ) then
			WebDKP_MakeSureInTable(main, tableid, class);
		end
	end

	local toReturn;
	-- if they arn't in our table, return 0
	if ( WebDKP_DkpTable[main] == nil ) then
		toReturn = 0;
	-- if they are in our table, but not with this tableid, return 0
	elseif ( WebDKP_DkpTable[main]["dkp_"..tableid] == nil ) then
		toReturn = 0;
	-- if they are in our table with this table id, return their dkp
	else
		toReturn = WebDKP_DkpTable[main]["dkp_"..tableid];
	end
	return toReturn;
end

-- ================================
-- Makes sure that a given player name exists in the dkp table
-- datasturcture and the given dkp table. 
-- If they are not, they are added as a new entry with 0 dkp
-- playerName	name of the player to add
-- tableid		tableid to add their points to
-- class		their class
-- dkp			[optional] their starting dkp. default to 0.
-- ================================
function WebDKP_MakeSureInTable(playerName, tableid, class , dkp)
	if (playerName == nil ) then
		return;
	end
	if (dkp == nil ) then
		dkp = 0;
	end

	-- make sure the player exists in our table
	if(WebDKP_DkpTable[playerName] == nil ) then
	    if ( class == nil ) then
			class = "Unknown";
	    end
	    
	    -- add the player to the table. The canTrim flag signals 
	    -- that this player was auto added in game and is eligible
	    -- to be trimmed as needed
		WebDKP_DkpTable[playerName] = {
			["dkp_"..tableid] = dkp,
			["class"] = class,
			["cantrim"] = true,
		}
	end
	
	-- check what their dkp is in the current table
	if(WebDKP_DkpTable[playerName]["dkp_"..tableid] == nil ) then
		WebDKP_DkpTable[playerName]["dkp_"..tableid] = 0;
	end
end

-- ================================
-- Returns the 'main' character for the given player
-- name.
-- playerName - name of player to get the main for
-- onlyIfCombineAlts - optional. If passed the function will only return a main if the combine Alts option is equal to 1. If combineAlts = 0, it just returns the same name passed
-- ================================
function WebDKP_GetMain(playerName, onlyIfCombineAlts)
	local main;
	if ( WebDKP_Alts[playerName] ~= nil) then
		main = WebDKP_Alts[playerName];
		-- override, if they asked to check setting and setting was off, reset the value
		-- to return the playerName
		if ( onlyIfCombineAlts ~= nil and WebDKP_WebOptions["CombineAlts"] == 0 ) then
			main = playerName;
		end
	else
		main = playerName;
	end
	return main;
end

-- ================================
-- Helper method. Returns the class name
-- of the given player. Player MUST
-- be in current dkp table
-- ================================
function WebDKP_GetPlayerClass(playerName)
	local playerClass = WebDKP_DkpTable[playerName]["class"];
	if(WebDKP_DkpTable[playerName]==nil) then
		playerClass = "Druid";
	end
	return playerClass;
end

function WebDKP_GetCmd(msg)
 	if msg then
 		local a,b,c=strfind(msg, "(%S+)"); --contiguous string of non-space characters
 		if a then
 			return c, strsub(msg, b+2);
 		else	
 			return "";
 		end
 	end
end

function WebDKP_GetCommaCmd(msg)
 	if msg then
 		local a = strfind(msg, ",");
 		if a then
 			local first = strtrim(strsub(msg,0, a-1));
 			local second = strtrim(strsub(msg,a+1));
 			return first, second;
 		else	
 			return msg;
 		end
 	end
end

-- ================================
-- For whisper event hook - sends a whisper back
-- to the given person with a webdkp header so it 
-- will not be displayed in regular whisper chat
-- ================================
function WebDKP_SendWhisper(toPlayer, message)
	SendChatMessage("WebDKP: "..message, "WHISPER", nil, toPlayer)
end


-- ================================
-- Returns a cost of a given item in the loot table. If the item does not appear
-- in the loot table returns nil. 
-- ================================
function WebDKP_GetLootTableCost(item) 
	local toReturn = nil;

	local quality, itemName, item, itemLevel, itemEquipLoc = WebDKP_GetItemInfo(item);
	local value = WebDKP_GetItemID(item);
	if ( WebDKP_Loot ~= nil ) then
		
		toReturn = WebDKP_Loot[itemName];
		if WebDKP_Loot[itemName] == nil then
			toReturn = WebDKP_Loot[value];
		end
	end
	-- If no match was found at this point, check the item level for a match
	if toReturn == nil or toReturn == "" then
		itemLevel = tostring(itemLevel);
		toReturn = WebDKP_Loot[itemLevel];
	end
	if toReturn == nil or toReturn == "" then
		toReturn = nil;
	end
	return toReturn;
end

-- ==============================================================================
-- Returns an item ID, must be  link
-- This way people can specifity dkp based on the itemid
-- ==============================================================================
function WebDKP_GetItemID(item) 
	local toReturn = nil;
	
	if item ~= nil and item ~= "" then
		local itempattern = "%|(.+)%|(%a+):(%d+):(.+)";
		_, _, hexvalue, itemtype, value,holder, _, test2 = string.find(item, itempattern);
	end
	if value ~= "" and value ~= nil then
		toReturn = value;
	end
	
	return toReturn;

end

-- ================================
-- Gets a value of an option from the option data structure. 
-- Priority as follows:
-- 1 - WebDKP_Options
-- 2 - WebDKP_WebOptions
-- 3 - DefaultValue (results in value being stored in WebDKP_Options)
-- ================================
function WebDKP_GetOptionValue(optionName, defaultValue)
	local value;
	if ( WebDKP_Options[optionName] ~= nil ) then
		value = WebDKP_Options[optionName];
	elseif (WebDKP_WebOptions[optionName]~= nil ) then
		value = WebDKP_WebOptions[optionName];
	else
		value = defaultValue;	
		WebDKP_Options[optionName] = defaultValue;
	end
	return value;
end