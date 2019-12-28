------------------------------------------------------------------------
-- AUTO FILL Tasks
------------------------------------------------------------------------
-- This file contains methods related to auto filling in information in your dkp
-- form when items drop
------------------------------------------------------------------------

-- ========================== 
-- Values for Multiple Loot Award Windows added by Zevious
-- ==========================
Award_Window_Flag1 = 0;
sPlayer1 = "";
sLink1 = "";
Award_Window_Flag2 = 0;
sPlayer2 = "";
sLink2 = "";
Award_Window_Flag3 = 0;
sPlayer3 = "";
SLink3 = "";
Award_Window_Flag4 = 0;
sPlayer4 = "";
SLink4 = "";
sPlayer5 = "";
SLink5 = "";
ItemHolder = "";

-- ================================
-- Helper structure that maps rarity of an item back to its rank
-- ================================
WebDKP_RarityTable = {
	[0] = -1,
	[1] = 0,
	[2] = 1,
	[3] = 2,
	[4] = 3,
	[5] = 4
};

-- ================================
-- An event that is triggered when loot is taken. If auto fill 
-- is enabled, this must check to see:
-- 1 - what item dropped and fill it in the item input
-- 2 - see what player got the item and select them
-- 3 - see if the item is in the loot table, and enter the cost if it is
-- 4 - if auto award is enabled it should award the item
-- ================================
function WebDKP_Loot_Taken(arg1)
if arg1 ~= ItemHolder then
	if ( WebDKP_Options["AutofillEnabled"] == 0 ) then
		return;
	end
	--1 Find out what item was dropped
	local sPlayer, sLink;
	local iStart, iEnd, sPlayerName, sItem = string.find(arg1, "([^%s]+) receives loot: (.+)%.");
	if ( sPlayerName ) then
		sPlayer = sPlayerName;
		sLink = sItem;
	else
		local iStart, iEnd, sItem = string.find(arg1, "You receive loot: (.+)%.");
		if ( sItem ) then
			sPlayer = UnitName("player");
			sLink = sItem;
		end
	end
	if ( sLink and sPlayer ) then
		--Get details about the item
		local sRarity, sName, sItem = WebDKP_GetItemInfo(sLink);
		
		-- if this is in our ignore list, we can skip it
		if ( WebDKP_ShouldIgnoreItem(sName) )  then
			return;
		end
		
		-- if this is the item that was last bid off/awarded, we can skip autofilling it
		if ( sName == WebDKP_lastBidItem or sName == WebDKP_bidItem) then 
			WebDKP_lastBidItem = "";
			return;
		end
		local rarity = WebDKP_RarityTable[sRarity];
		local cost = nil; 
		
		-- if this item isn't past the autofill rarity threshold in the options, skip it
		if( rarity < WebDKP_Options["AutofillThreshold"] ) then
			return;
		end
		
		--display the item name in the form
		WebDKP_AwardItem_FrameItemName:SetText(sName);
		
		-- see if we can determine the cost while we are at it...
		if ( WebDKP_Loot ~= nil ) then
			cost = WebDKP_Loot[sName];
			if ( cost ~= nil ) then 
				WebDKP_AwardItem_FrameItemCost:SetText(cost);
			else
				WebDKP_AwardItem_FrameItemCost:SetText("");
			end
		end
		--select the player
		WebDKP_SelectPlayerOnly(sPlayer);
		
		-- if we are set to auto award items, go ahead and display the popup
		if (WebDKP_Options["AutoAwardEnabled"] == 1) then
			-- If we know the cost, prefill it in the form. 
			-- If not, show an input for them to enter something.
			if ( cost ~= nil ) then
				WebDKP_ShowAwardFrame("Award "..sPlayer.." "..sLink.." for "..cost.." DKP? \r\n (Enter DKP below, positive numbers only, % values work (50%)",cost,sLink,sPlayer);
				WebDKP_AwardFrameCost:SetText(cost);
			else
				WebDKP_ShowAwardFrame("Award "..sPlayer.." "..sLink.."? \r\n (Enter DKP below, positive numbers only, % values work (50%)",nil,sLink,sPlayer);
			end
		end
	end
	ItemHolder = arg1;
end
end

function WebDKP_ShowAwardFrame(title, cost,SLink,sPlayer)
	PlaySound(SOUNDKIT.IG_MAIN_MENU_OPEN, "SFX");
	if Award_Window_Flag1 == 0 then
		WebDKP_AwardFrame:Show();
		WebDKP_AwardFrameTitle:SetText(title);
		if(cost ~= nil) then
			WebDKP_AwardFrameCost:SetText(cost);
		else
			WebDKP_AwardFrameCost:SetText("");
		end
		sPlayer1 = sPlayer;
		sLink1 = SLink;
		Award_Window_Flag1 = 1;
		
	elseif Award_Window_Flag1 == 1 and Award_Window_Flag2 == 0 then
		WebDKP_AwardFrame2:Show();
		WebDKP_AwardFrame2Title:SetText(title);
		if(cost ~= nil) then
			WebDKP_AwardFrame2Cost:SetText(cost);
		else
			WebDKP_AwardFrame2Cost:SetText("");
		end
		Award_Window_Flag2 = 1;
		sLink2 = SLink;
		sPlayer2 = sPlayer;

	elseif Award_Window_Flag1 == 1 and Award_Window_Flag2 == 1 and Award_Window_Flag3 == 0 then
		WebDKP_AwardFrame3:Show();
		WebDKP_AwardFrame3Title:SetText(title);
		if(cost ~= nil) then
			WebDKP_AwardFrame3Cost:SetText(cost);
		else
			WebDKP_AwardFrame3Cost:SetText("");
		end
		Award_Window_Flag3 = 1;
		sPlayer3 = sPlayer;
		sLink3 = SLink;

	elseif Award_Window_Flag1 == 1 and Award_Window_Flag2 ==1 and Award_Window_Flag3 == 1 and Award_Window_Flag4 == 0 then
		WebDKP_AwardFrame4:Show();
		WebDKP_AwardFrame4Title:SetText(title);
		if(cost ~= nil) then
			WebDKP_AwardFrame4Cost:SetText(cost);
		else
			WebDKP_AwardFrame4Cost:SetText("");
		end
		Award_Window_Flag4 = 1;
		sPlayer4 = sPlayer;
		sLink4 = SLink;

	elseif Award_Window_Flag1 == 1 and Award_Window_Flag2 ==1 and Award_Window_Flag3 == 1 and Award_Window_Flag4 == 1 then
		WebDKP_AwardFrame5:Show();
		WebDKP_AwardFrame5Title:SetText(title);
		if(cost ~= nil) then
			WebDKP_AwardFrame5Cost:SetText(cost);
		else	
			WebDKP_AwardFrame5Cost:SetText("");
		end
		sPlayer5 = sPlayer;
		sLink5 = SLink;

	end
	
end


-- ================================
-- Callback function from clicking 'yes' on the autoaward dialog box totally changed by Zevious
-- ================================
function WebDKP_AwardItem_Event2(dkp,playervalue)
	local cost = dkp;
	local percentcost = string.find(cost, "%%");
	local percentflag = 0;
	local tableid = WebDKP_GetTableid();
	if playervalue == 1 then
		player = sPlayer1;
		item = sLink1;
		
	elseif playervalue == 2 then
		player = sPlayer2;
		item = sLink2;
	elseif playervalue == 3 then
		player = sPlayer3;
		item = sLink3;
	elseif playervalue ==  4 then
		player = sPlayer4;
		item = sLink4;
	else 
		player = sPlayer5;
		item = sLink5;
	end
	WebDKP_SelectPlayerOnly(player);
	local player2 = WebDKP_GetSelectedPlayers(1);

	if percentcost ~= nil then
		-- This means they are entering a percent so calculate the proper cost
		-- Substitute the % with "" so we are left with just the number as a string
		cost = string.gsub(cost, "%%", "")
		cost = tonumber(cost);
		
		percentflag = 1;

	end

	
	if ( cost == nil or cost=="") then
		WebDKP_Print("No value was entered so the DKP defaulted to 0.");
		cost = 0;
	end

	local points = cost * -1;
	points = WebDKP_ROUND(points,2);

	if percentflag == 1 then
			local actualname = player2[0]["name"];
			cost = (cost / 100) * WebDKP_DkpTable[actualname]["dkp_"..tableid] * -1;
			points = WebDKP_ROUND(cost,2);
	end
	
	if ( player2 == nil or player2 == "") then
		WebDKP_Print("No player was selected to award. Award NOT made.");
		PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
	else
		WebDKP_AddDKP(points, item, "true", player2)
		WebDKP_AnnounceAwardItem(points, item, player2[0]["name"]);

		-- Update the table so we can see the new dkp status
		WebDKP_UpdateTableToShow();
		WebDKP_UpdateTable();
	end
end


-- ================================
-- Event handler for entering a name in the award item field
-- Will automattically fill in the cost if the cost is available in the players loot table
-- ================================
function WebDKP_AutoFillCost()
	--if ( WebDKP_Options["AutofillEnabled"] == 0 ) then
	--	return;
	--end

	local itemName = WebDKP_AwardItem_FrameItemName:GetText();
	local changedflag = 0;
	local itemRarity, itemName2, itemLink, itemLevel, itemEquipLoc = WebDKP_GetItemInfo(itemName);
	local multiplier1 = WebDKP_Options["ItemLevelMult"];
	
	-- see if we can determine the cost while we are at it...
	--if ( WebDKP_Loot ~= nil and sName ~= nil) then
	--	local cost = WebDKP_Loot[sName];
	--	if ( cost ~= nil ) then 
	--		WebDKP_AwardItem_FrameItemCost:SetText(cost);
	--	end
	--end


	startingBid = WebDKP_GetLootTableCost(itemName);

	-- Check to see if the item level equation flag is enabled
	if startingBid == nil and WebDKP_Options["ItemLevelEquation"] == 1 and itemLevel~= nil then
		itemLevel = tonumber(itemLevel);
		multiplier1 = tonumber(multiplier1);
		if itemLevel > 100 then
			startingBid = itemLevel * multiplier1;
		end
		changedflag = 1;
		
	end	
	-- Check to see if the Item Slot Loc Multiplier is enabled
	if (startingBid == nil or changedflag == 1) and WebDKP_Options["SlotLocMult"] == 1 and itemEquipLoc ~= nil then
		if startingBid == nil then
			startingBid = itemLevel;
		end
		if itemEquipLoc == "INVTYPE_HEAD" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["head"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_NECK" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["neck"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_SHOULDER" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["shoulders"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_CHEST" or itemEquipLoc =="INVTYPE_ROBE"  then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["chest"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_WAIST" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["waist"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_LEGS" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["legs"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_FEET" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["feet"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_WRIST" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["wrist"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_HAND" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["hands"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_FINGER" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["fingers"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_TRINKET" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["trinkets"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_CLOAK" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["back"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_WEAPONMAINHAND"then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["mainhand"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_SHIELD" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["shield"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_RELIC" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["relic"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_RANGED" or itemEquipLoc == "INVTYPE_THROWN" or itemEquipLoc == "INVTYPE_RANGEDRIGHT" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["ranged"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_2HWEAPON" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["twohand"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_WEAPON" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["onehanders"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_WEAPONOFFHAND" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["offhandweapon"])
			startingBid = startingBid * multvalue;
		elseif itemEquipLoc == "INVTYPE_HOLDABLE" then
			local multvalue = tonumber(WebDKP_Options["ItemLocMult"]["heldoffhand"])
			startingBid = startingBid * multvalue;
		else
			startingBid = 0;

		end
		
	end

		if ( startingBid ~= nil ) then
			WebDKP_AwardItem_FrameItemCost:SetText(startingBid);
		else
		-- Nothing at this time
		end

end


-- ================================
-- Event handler for entering a name in the award dkp reason field
-- Will automattically fill in the cost if the cost is available in the players toot table
-- ================================
function WebDKP_AutoFillDKP()
	if ( WebDKP_Options["AutofillEnabled"] == 0 ) then
		return;
	end
	local sName = WebDKP_AwardDKP_FrameReason:GetText();
	
	-- see if we can determine the cost while we are at it...
	if ( WebDKP_Loot ~= nil and sName ~= nil) then
		local cost = WebDKP_Loot[sName];
		if ( cost ~= nil ) then 
			WebDKP_AwardDKP_FramePoints:SetText(cost);
		end
	end
end



