------------------------------------------------------------------------
-- EPGP Support Functions
------------------------------------------------------------------------
-- 
------------------------------------------------------------------------

-- ================================
-- Add EP to Player(s)
-- ================================
function WebDKP_AddEP(points, reason, forItem, players)

	local date  = date("%Y-%m-%d %H:%M:%S");
	local location = GetZoneText();
	local tableid = WebDKP_GetTableid();
	local awardedBy = UnitName("player");
	local pointsholder = points;

	--Remarked to work on synching EPGP later
	--if WebDKP_Options["EnableSynch"] == 1 then
	--	WebDKP_Synch_Auto(points, forItem, players, reason, date); 		-- Runs the Synch Code
	--end

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
	
			-- Do a check to see if it's for an item. Items are GP.
			if forItem == "false" then
				WebDKP_AddEPToTable(name, class, points);
			else
				WebDKP_AddGPToTable(name, class, points);	
			end
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

	-- Zerosum does not apply to EPGP
	-- if this is an item award and we are using zero-sum dkp, we need to give automated
	-- zero sum awards too
	--if ( WebDKP_WebOptions["ZeroSumEnabled"]==1 and forItem=="true") then
	--	WebDKP_AwardZeroSum(points, reason, date);
	--end

	WebDKP_UpdateLogTable();
end


-- ================================
-- Add EP to Player(s)
-- ================================
function WebDKP_AddEPToTable(name, class, points, tableidfrom)
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

	if WebDKP_DkpTable[name]["ep_"..tableid] == nil then
		WebDKP_DkpTable[name]["ep_"..tableid] = 0;
	end
	WebDKP_DkpTable[name]["ep_"..tableid] = WebDKP_DkpTable[name]["ep_"..tableid] + points;
	
	-- since this player has points, it can't be auto trimmed any more
	WebDKP_DkpTable[name]["cantrim"] = false;
end

-- ================================
-- Add GP to Player(s)
-- ================================
function WebDKP_AddGPToTable(name, class, points, tableidfrom)
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

	if WebDKP_DkpTable[name]["gp_"..tableid] == nil then
		WebDKP_DkpTable[name]["gp_"..tableid] = 1;
	end
	WebDKP_DkpTable[name]["gp_"..tableid] = WebDKP_DkpTable[name]["gp_"..tableid] + points;
	
	-- since this player has points, it can't be auto trimmed any more
	WebDKP_DkpTable[name]["cantrim"] = false;
end

-- ================================
-- Rerenders the table to the screen.For EPGP
-- ================================
function WebDKP_UpdateEPGPTable()

	-- Copy data to the temporary array
	local entries = { };
	for k, v in pairs(WebDKP_DkpTableToShow) do
		if ( type(v) == "table" ) then
			if( v[1] ~= nil and v[2] ~= nil and v[3] ~=nil and v[4] ~=nil) then
				tinsert(entries,{v[1],v[2],v[3],v[4],v[5],v[6],v[7],v[8]}); -- copies over name, class, dkp, tier,rank,rankindex,EP,GP
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
		local line = getglobal("WebDKP_FrameLineEPGP" .. i);
		local nameText = getglobal("WebDKP_FrameLineEPGP" .. i .. "Name");
		local classText = getglobal("WebDKP_FrameLineEPGP" .. i .. "Class");
		local epText = getglobal("WebDKP_FrameLineEPGP" .. i .. "EP");
		local gpText = getglobal("WebDKP_FrameLineEPGP" .. i .. "GP");
		local lpText = getglobal("WebDKP_FrameLineEPGP" .. i .. "LP");
		local rankText = getglobal("WebDKP_FrameLineEPGP" .. i .. "Rank");
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
			epText:SetText(entries[index][7]);
			gpText:SetText(entries[index][8]);
			local LPRounded = WebDKP_ROUND((entries[index][7])/(entries[index][8]),2);
			lpText:SetText(LPRounded);
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




