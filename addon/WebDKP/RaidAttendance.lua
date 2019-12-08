------------------------------------------------------------------------
-- Raid Attendance Functions - Added by Zevious (Bronzebeard) 10-20-09
------------------------------------------------------------------------
-- Contains methods related to the Raid Attendance Logs.
------------------------------------------------------------------------
global_raid = "";
global_startTime = "";
previous_raid = "";
previous_starttime = "";
previous_name = "";

-- ===========================================================================================
-- Toggles the Raid Log Frame.
-- ===========================================================================================
function WebDKP_RaidLog_ToggleUI()
	if ( WebDKP_RaidInfoFrame:IsShown() ) then
		WebDKP_RaidInfoFrame:Hide();
	else
		WebDKP_RaidInfoFrame:Show();
		WebDKP_UpdateRaidLogTable();
	end 
end

-- ===========================================================================================
-- Toggles the Character Raid Log Frame.
-- ===========================================================================================
function WebDKP_CharRaidLog_ToggleUI()
	if ( WebDKP_CharRaidInfoFrame:IsShown() ) then
		WebDKP_CharRaidInfoFrame:Hide();
	else
		WebDKP_CharRaidInfoFrame:Show();
		WebDKP_UpdateCharRaidLogTable()
	end 
end

-- ================================
-- Called when mouse goes over an attendance line entry. 
-- If that player is not selected causes that row
-- to become 'highlighted'
-- ================================
function WebDKP_HandleMouseOverRaidLog(self)
local this = self;
	if WebDKP_RaidInfo ~= nil then
		local raid = _G[this:GetName().."RaidLocation"]:GetText();
		local raiddate = _G[this:GetName().."date"]:GetText();
		local raidstart = _G[this:GetName().."startTime"]:GetText();
		local joinedstring = strjoin(" ", raiddate, raidstart);
		if( not WebDKP_RaidInfo[raid.." "..joinedstring]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
		end
	end
end


-- ================================
-- Called when mouse goes over an character raid line entry. 
-- If that player is not selected causes that row
-- to become 'highlighted'
-- ================================
function WebDKP_HandleMouseOverCharRaidLog(self)
local this = self;
	if WebDKP_CharRaidInfo ~= nil then
		local charname = _G[this:GetName().."CharName"]:GetText();
		local total_raids = _G[this:GetName().."CharRaidTot"]:GetText();
		local char_attended = _G[this:GetName().."CharRaidAttended"]:GetText();
		local char_percent = _G[this:GetName().."CharRaidPercent"]:GetText();
		if( not WebDKP_CharRaidInfo[charname]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
		end
	end
end


-- ================================
-- Called when a mouse leaves an attendance line entry. 
-- If that player is not selected, causes that row
-- to return to normal (none highlighted)
-- ================================
function WebDKP_HandleMouseLeaveRaidLog(self)
local this = self;
	if WebDKP_RaidInfo ~= nil then
		local raid = _G[this:GetName().."RaidLocation"]:GetText();
		local raiddate = _G[this:GetName().."date"]:GetText();
		local raidstart = _G[this:GetName().."startTime"]:GetText();
		local joinedstring = strjoin(" ", raiddate, raidstart);
		if( not WebDKP_RaidInfo[raid.." "..joinedstring]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0, 0, 0, 0);
		end
	end
end

-- ================================
-- Called when a mouse leaves an char raid line entry. 
-- If that player is not selected, causes that row
-- to return to normal (none highlighted)
-- ================================
function WebDKP_HandleMouseLeaveCharRaidLog(self)
local this = self;
	if WebDKP_CharRaidInfo ~= nil then
		local charname = _G[this:GetName().."CharName"]:GetText();
		local total_raids = _G[this:GetName().."CharRaidTot"]:GetText();
		local char_attended = _G[this:GetName().."CharRaidAttended"]:GetText();
		local char_percent = _G[this:GetName().."CharRaidPercent"]:GetText();
		if( not WebDKP_CharRaidInfo[charname]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0, 0, 0, 0);
		end
	end
end

-- ================================
-- Called when the user clicks on a log entry. Causes 
-- that entry to either become selected or normal
-- and updates the dkp table with the change
-- ================================
function WebDKP_SelectRaidLogToggle(self)
local this = self;
	if WebDKP_RaidInfo ~= nil then
		-- Set the previous selection to not selected
		if previous_raid ~= "" and previous_starttime ~= "" then
			WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["selected"] = false;
		end
		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
		local raid = _G[this:GetName().."RaidLocation"]:GetText();
		local raiddate = _G[this:GetName().."date"]:GetText();
		local raidstart = _G[this:GetName().."startTime"]:GetText();
		local joinedstring = strjoin(" ", raiddate, raidstart);
	
		if( WebDKP_RaidInfo[raid.." "..joinedstring]["selected"] ) then
			WebDKP_RaidInfo[raid.." "..joinedstring]["selected"] = false;
			_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
		else
			WebDKP_RaidInfo[raid.." "..joinedstring]["selected"] = true;
			_G[this:GetName() .. "Background"]:SetVertexColor(0.1, 0.1, 0.9, 0.8);
		end
		previous_raid = raid;
		previous_starttime = joinedstring;
		_G["LineLocation"] = _G[this:GetName() .. "Background"]
		WebDKP_UpdateAttendedTable(raid,joinedstring);
	end
end

-- ================================
-- Called when the user clicks on a character raid entry. Causes 
-- that entry to either become selected or normal
-- and updates the dkp table with the change
-- ================================
function WebDKP_SelectCharRaidLogToggle(self)
local this = self;
	if WebDKP_CharRaidInfo ~= nil then
		-- Set the previous selection to not selected
		if previous_name ~= nil and previous_name ~= "" then
			WebDKP_CharRaidInfo[previous_name]["selected"] = false;
		end
		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
		local charname = _G[this:GetName().."CharName"]:GetText();


		if( WebDKP_CharRaidInfo[charname]["selected"] ) then
			WebDKP_CharRaidInfo[charname]["selected"] = false;
			_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
		else
			WebDKP_CharRaidInfo[charname]["selected"] = true;
			_G[this:GetName() .. "Background"]:SetVertexColor(0.1, 0.1, 0.9, 0.8);
		end

		previous_name = charname;
		_G["LineLocation"] = _G[this:GetName() .. "Background"]
		WebDKP_UpdateCharAttendedTable(charname)
	end
end

-- ================================
-- Called when the user clicks on an Attendee.
-- ================================
function WebDKP_SelectAttendeeLogToggle(self)
local this = self;
	if WebDKP_RaidInfo ~= nil then

		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
		local attended = _G[this:GetName().."Attended"]:GetText();
		if attended ~= nil then
			WebDKP_RaidInfoFrameCharChange:SetText(attended);
		end

	end
end

-- ========================================================
-- Rerenders the Raid table to the screen - Zevious
-- ========================================================
function WebDKP_UpdateRaidLogTable()
	
if WebDKP_RaidInfo ~= nil then
	local entries = { };
	local attended = { };
	for k, v in pairs(WebDKP_RaidInfo) do
		countnames = 0;
		if ( type(v) == "table" ) then
			if( v["raid"] ~= nil and v["date"] ~= nil and v["starttime"] ~= nil and v["endtime"] ~= nil and v["totaltime"] ~= nil and v["completedate"] ~= nil) then
				
				tinsert(entries,{v["raid"],v["date"],v["starttime"],v["endtime"],v["totaltime"],v["completedate"]}); -- copies over Raid, Start Time, End Time, Total Raid Time

				
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
				if ( WebDKP_LogSort["way4"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] > a2[WebDKP_LogSort["curr4"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] < a2[WebDKP_LogSort["curr4"]];
					end
				end
			end
		end
	);


	local numEntries = getn(entries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_RaidInfoFrameScrollRaidLogFrame);
	--WebDKP_Print("Before Update");
	FauxScrollFrame_Update(WebDKP_RaidInfoFrameScrollRaidLogFrame, numEntries, 20, 20);
	--WebDKP_Print("After Update");
	-- Run through the table lines and put the appropriate information into each line

	
	for i=1, 20, 1 do
		local line = getglobal("WebDKP_RaidInfoFrameLine" .. i);
		raidText = getglobal("WebDKP_RaidInfoFrameLine" .. i .. "RaidLocation");
		dateText = getglobal("WebDKP_RaidInfoFrameLine" .. i .. "date");
		local startText = getglobal("WebDKP_RaidInfoFrameLine" .. i .. "startTime");
		local endText = getglobal("WebDKP_RaidInfoFrameLine" .. i .. "endTime");
		local timeText = getglobal("WebDKP_RaidInfoFrameLine" .. i .. "totaltime");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_RaidInfoFrameScrollRaidLogFrame);
		if ( index <= numEntries) then
			line:Show();
			raidText:SetText(entries[index][1]);
			dateText:SetText(entries[index][2]);
			startText:SetText(entries[index][3]);
			endText:SetText(entries[index][4]);
			timeText:SetText(entries[index][5]);
		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end
end
end

-- ========================================================
-- Rerenders the Character Raid table to the screen - Zevious
-- ========================================================
function WebDKP_UpdateCharRaidLogTable()
WebDKP_UpdatePlayersInGroup();

local filterdate = WebDKP_CharRaidInfoFrameFilterDate:GetText();
local isfiltered = WebDKP_CharRaidInfoFrameApplyFilterDate:GetChecked();
if WebDKP_CharRaidInfo ~= nil then
	local entries = { };
	if WebDKP_Options["InGroup"] == 1 then
		
		for k, v in pairs(WebDKP_PlayersInGroup) do
			if ( type(v) == "table" ) then
				if( v["name"] ~= nil) then
					
					if WebDKP_CharRaidInfo[v["name"]] ~= nil then
					
						tinsert(entries,{v["name"],WebDKP_CharRaidInfo[v["name"]]["total_raids"],WebDKP_CharRaidInfo[v["name"]]["raids_attended"],WebDKP_CharRaidInfo[v["name"]]["percent"]});
					end
				end
			end
		end
	else 

		--local entries = { };
		local attended = { };
		for k, v in pairs(WebDKP_CharRaidInfo) do
			countnames = 0;
			if ( type(v) == "table" ) then
				if( v["total_raids"] ~= nil and v["raids_attended"] ~= nil and v["percent"] ~= nil) then
				
					tinsert(entries,{k,v["total_raids"],v["raids_attended"],v["percent"]}); -- copies over the data into a temp array

				end
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
				if ( WebDKP_LogSort["way4"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] > a2[WebDKP_LogSort["curr4"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] < a2[WebDKP_LogSort["curr4"]];
					end
				end
			end
		end
	);


	local numEntries = getn(entries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_CharRaidInfoFrameScrollCharRaidLogFrame);
	FauxScrollFrame_Update(WebDKP_CharRaidInfoFrameScrollCharRaidLogFrame, numEntries, 25, 20);

	if isfiltered then
		local month, day, year = WebDKP_ApplyDateFilter(filterdate);
	end

	-- Run through the table lines and put the appropriate information into each line
	-- This is ran if there is no filter date
	for i=1, 23, 1 do
		local line = getglobal("WebDKP_CharRaidInfoFrameLine" .. i);
		nameText = getglobal("WebDKP_CharRaidInfoFrameLine" .. i .. "CharName");
		local totalraidsText = getglobal("WebDKP_CharRaidInfoFrameLine" .. i .. "CharRaidTot");
		local attendedraidsText = getglobal("WebDKP_CharRaidInfoFrameLine" .. i .. "CharRaidAttended");
		local percentText = getglobal("WebDKP_CharRaidInfoFrameLine" .. i .. "CharRaidPercent");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_CharRaidInfoFrameScrollCharRaidLogFrame);
		if ( index <= numEntries) then
			line:Show();
			local charname = entries[index][1];
			local totraids = entries[index][2];
			local attraids = entries[index][3];
			local percraid = entries[index][4];

			-- process the attended raids if the filter is set and if the user entered what appears to be a valid date.
			if isfiltered and month ~= nil then
				totraids, attraids, percraid = WebDKP_ProcessCharAttendedRaids(month,day,year,charname);
	
			end

			nameText:SetText(charname);
			-- Set the name text color
			if WebDKP_DkpTable[charname] ~= nil and WebDKP_DkpTable[charname]["class"] ~= nil then
				local charclass = WebDKP_DkpTable[charname]["class"];
				charclass = string.upper(charclass);
				charclass = string.gsub(charclass, " ", "");
				if RAID_CLASS_COLORS[charclass] ~= nil then
					nameText:SetTextColor(RAID_CLASS_COLORS[charclass]["r"],RAID_CLASS_COLORS[charclass]["g"],RAID_CLASS_COLORS[charclass]["b"]);
				end
			end
			totalraidsText:SetText(totraids);
			attendedraidsText:SetText(attraids);
			percentText:SetText(percraid);
		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end

end
end

-- =======================================================
-- Rerenders the list of raids a character as attended on the right side
-- ========================================================
function WebDKP_UpdateCharAttendedTable(name)
	local filterdate = WebDKP_CharRaidInfoFrameFilterDate:GetText();
	local isfiltered = WebDKP_CharRaidInfoFrameApplyFilterDate:GetChecked();
	raidsattended = WebDKP_CharRaidInfo[name]["attended"];
	local entries = { };
	-- Break down the one variable into two variables inside an array for sorting
	local numEntry = getn(raidsattended);
	for i=1, numEntry, 1 do
		stringdata = raidsattended[i];
		attendedholder, raidlocation1,raiddate1 = strsplit("&", stringdata);

		if attendedholder == "1" then
			if isfiltered then
				local proceedflag = WebDKP_CrossCheckDate(raiddate1);
				if proceedflag == true then		
					tinsert(entries,{raidlocation1,raiddate1}); -- copies over the data into a temp array
				end
			else
				tinsert(entries,{raidlocation1,raiddate1}); -- copies over the data into a temp array
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
				if ( WebDKP_LogSort["way4"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] > a2[WebDKP_LogSort["curr4"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr4"]] == a2[WebDKP_LogSort["curr4"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr4"]] < a2[WebDKP_LogSort["curr4"]];
					end
				end
			end
		end
	);


	--local numEntries = getn(raidsattended);
	local numEntries = getn(entries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_CharRaidInfoFrameScrollAttendedRaidFrame);
	FauxScrollFrame_Update(WebDKP_CharRaidInfoFrameScrollAttendedRaidFrame, numEntries, 25, 20);
	-- Run through the table lines and put the appropriate information into each line

	
	for i=1, 23, 1 do
		local line = getglobal("WebDKP_CharRaidInfoFrameLines" .. i);
		locationText = getglobal("WebDKP_CharRaidInfoFrameLines" .. i .. "RaidLocation");
		dateText = getglobal("WebDKP_CharRaidInfoFrameLines" .. i .. "RaidDate");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_CharRaidInfoFrameScrollAttendedRaidFrame); 

		if ( index <= numEntries) then
			line:Show();
			local raidlocation = entries[index][1];
			local dateofraid = entries[index][2];

					
			locationText:SetText(raidlocation);
			dateText:SetText(dateofraid);
				
		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end

end



-- =======================================================
-- Rerenders the list of people attending a raid on the right side.
-- ========================================================
function WebDKP_UpdateAttendedTable(raid,starttime)

	nameentries = WebDKP_RaidInfo[raid.." "..starttime]["attended"];

	table.sort(nameentries, function(a,b) return a<b end)


	local numEntries = getn(nameentries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_RaidInfoFrameScrollAttendeeFrame);
	FauxScrollFrame_Update(WebDKP_RaidInfoFrameScrollAttendeeFrame, numEntries, 25, 20);
	-- Run through the table lines and put the appropriate information into each line

	
	for i=1, 25, 1 do
		local line = getglobal("WebDKP_RaidInfoFrameLines" .. i);
		attendedText = getglobal("WebDKP_RaidInfoFrameLines" .. i .. "Attended");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_RaidInfoFrameScrollAttendeeFrame); 

		if ( index <= numEntries) then
			line:Show();
			local charname = nameentries[index];
			attendedText:SetText(charname);
			if WebDKP_DkpTable[charname] ~= nil and WebDKP_DkpTable[charname]["class"] ~= nil then
				local charclass = WebDKP_DkpTable[charname]["class"];
				charclass = string.upper(charclass);
				charclass = string.gsub(charclass, " ", "");
				if RAID_CLASS_COLORS[charclass] ~= nil then
					attendedText:SetTextColor(RAID_CLASS_COLORS[charclass]["r"],RAID_CLASS_COLORS[charclass]["g"],RAID_CLASS_COLORS[charclass]["b"]);
				end
			end

		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end

end



-- =================================================================
-- Called when the user clicks Start Raid
-- =================================================================
function WebDKP_RaidStart()
	local startTime  = date("%H:%M:%S");
	local startTime2  = date("%Y-%m-%d %H:%M:%S");
	local completed = date("%Y%m%d%H%M%S");
	completed = tonumber(completed);
	local date  = date("%Y-%m-%d");
	local raid = GetZoneText();
	global_startTime = startTime2;
	global_raid = raid;

	-- Makes sure the main raid array exists
	if (not WebDKP_RaidInfo) then
		WebDKP_RaidInfo = {};
	end

	-- Makes sure the char raid array exists
	if (not WebDKP_CharRaidInfo) then
		WebDKP_CharRaidInfo = {};
	end


	-- Makes sure there is an entry for this raid
	if (not WebDKP_RaidInfo[raid.." "..startTime2]) then
		WebDKP_RaidInfo[raid.." "..startTime2] = {};
	end
	
	

	-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	-- Adds the raid info for the raid log
	-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	WebDKP_RaidInfo[raid.." "..startTime2]["raid"] = raid;
	WebDKP_RaidInfo[raid.." "..startTime2]["date"] = date;
	WebDKP_RaidInfo[raid.." "..startTime2]["starttime"] = startTime;
	WebDKP_RaidInfo[raid.." "..startTime2]["endtime"] = "";
	WebDKP_RaidInfo[raid.." "..startTime2]["totaltime"] = "";
	WebDKP_RaidInfo[raid.." "..startTime2]["attended"] = {};
	WebDKP_RaidInfo[raid.." "..startTime2]["completedate"] = completed;

	-- Increment the value of everyone's total raids
	if WebDKP_CharRaidInfo ~= nil then
		local numCharEntries = getn(WebDKP_CharRaidInfo);
		for k, v in pairs(WebDKP_CharRaidInfo) do
			charname = k;
			WebDKP_CharRaidInfo[charname]["total_raids"] = WebDKP_CharRaidInfo[charname]["total_raids"] + 1;
			-- Place a 0 in front of everyone's attended so we know they didn't attend. 1 in front means they were there.
			tinsert(WebDKP_CharRaidInfo[charname]["attended"],"0&"..raid.."&"..startTime2);	
		end
	end


	-- Add all players currently in the raid to the list of attendees
	WebDKP_UpdatePlayersInGroup();
	for k, v in pairs(WebDKP_PlayersInGroup) do
			name = v["name"];
			
			-- Make sure the character is in the array and if they aren't add them.
			if WebDKP_CharRaidInfo[name] == nil then
				WebDKP_CharRaidInfo[name] = {}; 
				WebDKP_CharRaidInfo[name]["total_raids"] = 1;
				WebDKP_CharRaidInfo[name]["raids_attended"] = 1;
				WebDKP_CharRaidInfo[name]["percent"] = 100;
				WebDKP_CharRaidInfo[name]["attended"] = {};
				--WebDKP_CharRaidInfo[name]["attended"]["0&"..raid.."&"..startTime2] = "1&"..raid.."&"..startTime2;
				tinsert(WebDKP_CharRaidInfo[name]["attended"], "1&"..raid.."&"..startTime2);
			else
				WebDKP_CharRaidInfo[name]["raids_attended"] = WebDKP_CharRaidInfo[name]["raids_attended"] + 1;
				local numattended = getn(WebDKP_CharRaidInfo[name]["attended"]);
				for i=1, numattended, 1 do
					if WebDKP_CharRaidInfo[name]["attended"][i] == "0&"..raid.."&"..startTime2 then
						WebDKP_CharRaidInfo[name]["attended"][i] = "1&"..raid.."&"..startTime2;
					end
				end
					--tremove(WebDKP_CharRaidInfo[name]["attended"],"0&"..raid.."&"..startTime2);
					--tinsert(WebDKP_CharRaidInfo[name]["attended"],"1&"..raid.."&"..startTime2);
				--WebDKP_CharRaidInfo[name]["attended"]["0&"..raid.."&"..startTime2] = "1&"..raid.."&"..startTime2;

			end

			--add them to the log entry
			tinsert(WebDKP_RaidInfo[raid.." "..startTime2]["attended"],v["name"]); -- copies over the names
			--WebDKP_RaidInfo[raid.." "..startTime2]["attended"]= WebDKP_PlayersInGroup;
	end
	-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	-- Adds the raid info for the character raid log
	-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	WebDKP_UpdateRaidLogTable();
	
	-- Go through and update each person's raid percent.
	if WebDKP_CharRaidInfo ~= nil then
		for k, v in pairs(WebDKP_CharRaidInfo) do
			charname1 = k;
			WebDKP_CharRaidInfo[charname1]["percent"] = WebDKP_ROUND((WebDKP_CharRaidInfo[charname1]["raids_attended"] / WebDKP_CharRaidInfo[charname1]["total_raids"]) * 100,0);	
		end
	end	
end
-- =================================================================
-- Called when the user Ends a Raid
-- Wraps up the Raid entry by adding an end time and total raid time
-- =================================================================
function WebDKP_RaidEnd()
if global_startTime ~= "" and global_raid ~= "" then
	local endTime  = date("%H:%M:%S");
	
	local pattern = "(%d%d):(%d%d):(%d%d)";
	_, _, hoursa,minutesa,secondsa = string.find(endTime, pattern);				-- Setup values for end times to calc total
	
	_,_,hoursb,minutesb,secondsb = string.find(global_startTime, pattern);			-- Setup values for start times to calc total
	
	local tothours = hoursa - hoursb;
	if tothours < 0 then
		tothours = 24 + tothours;
	end

	local totmins = (60-minutesb)+minutesa;
	if totmins >= 60 then
		totmins = (totmins - 60);
	end
	if totmins < 10 then
		totmins = strjoin ( "", "0", totmins);
	end

	local joinedstring = strjoin(":", tothours, totmins);
	totaltimeraid = joinedstring;

	-- Makes sure there is an entry for this raid
	if (WebDKP_RaidInfo[global_raid.." "..global_startTime]) then
		WebDKP_RaidInfo[global_raid.." "..global_startTime]["endtime"] = endTime;
		WebDKP_RaidInfo[global_raid.." "..global_startTime]["totaltime"] = totaltimeraid;
	end

	global_startTime = "";
	gloabl_raid = "";
	
	WebDKP_UpdateRaidLogTable();
end
end

-- ====================================================================
-- Called when the user scrolls through the log list. 
-- ====================================================================
function WebDKP_ScrollRaidLogListToggle()
	WebDKP_UpdateRaidLogTable();
	if WebDKP_RaidInfo ~= nil then
		-- Set the previous selection to not selected
		if global_startTime ~= "" and global_raid ~= "" then
			WebDKP_RaidInfo[global_raid.." "..global_startTime]["selected"] = false;
		end
		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
	end

end


-- ====================================================================
-- Called when the user scrolls through the attendee list. 
-- ====================================================================
function WebDKP_ScrollLogAttendedToggle()
	WebDKP_UpdateAttendedTable(previous_raid,previous_starttime);

end

-- ====================================================================
-- Called when the user scrolls through the character raids list. 
-- ====================================================================
function WebDKP_ScrollCharRaidLogListToggle()
	WebDKP_UpdateCharRaidLogTable();

end

-- ====================================================================
-- Called when the user scrolls through the character attended raids list. 
-- ====================================================================
function WebDKP_ScrollAttendedRaidToggle()
	WebDKP_UpdateCharAttendedTable(previous_name);
end


-- ====================================================================
-- This function removes a raid. 
-- ====================================================================
function WebDKP_DeleteRaid()

	if previous_raid ~= "" and previous_starttime ~= "" then
		if WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"] ~= nil then

			-- Handle removing the proper values from the character raid log
			
			-- Decrease the value of everyone's total raids
			if WebDKP_CharRaidInfo ~= nil then
				for k, v in pairs(WebDKP_CharRaidInfo) do
					charname = k;
					local numCharEntries = getn(WebDKP_CharRaidInfo[charname]["attended"]);
					for i=1, numCharEntries, 1 do
					if WebDKP_CharRaidInfo[charname]["attended"][i] == "1&"..previous_raid.."&"..previous_starttime or WebDKP_CharRaidInfo[charname]["attended"][i] == "0&"..previous_raid.."&"..previous_starttime then
						WebDKP_CharRaidInfo[charname]["total_raids"] = WebDKP_CharRaidInfo[charname]["total_raids"] - 1;
					end
					end
					WebDKP_CharRaidInfo[charname]["percent"] = WebDKP_ROUND((WebDKP_CharRaidInfo[charname]["raids_attended"] / WebDKP_CharRaidInfo[charname]["total_raids"]) * 100,0);	
				end
			end	

			getcharentries = getn(WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"]);
			for i=1, getcharentries, 1 do
				charsname = WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"][i];
				if WebDKP_CharRaidInfo[charsname] ~= nil then
	
					-- Remove the raid from the character raid log entry
					--starttimmod, secondvar = strsplit(" ", previous_starttime)
					getentriesrem = getn(WebDKP_CharRaidInfo[charsname]["attended"]);
					for i=1, getentriesrem, 1 do
						if WebDKP_CharRaidInfo[charsname]["attended"][i] == "1&"..previous_raid.."&"..previous_starttime then
							WebDKP_CharRaidInfo[charsname]["attended"][i] = "0&"..previous_raid.."&"..previous_starttime;
						end
					end
					WebDKP_CharRaidInfo[charsname]["raids_attended"] = WebDKP_CharRaidInfo[charsname]["raids_attended"] - 1;
				end

				WebDKP_CharRaidInfo[charsname]["percent"] = WebDKP_ROUND((WebDKP_CharRaidInfo[charsname]["raids_attended"] / WebDKP_CharRaidInfo[charsname]["total_raids"]) * 100,0);
			end

			
			WebDKP_RaidInfo[previous_raid.." "..previous_starttime] = nil;
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
			_G["LineLocation"] = "";
			previous_raid = "";
			previous_starttime = "";
			WebDKP_UpdateRaidLogTable();
		end

		-- This loop justs resets the Attended list to nothing after a delete.
		for i=1, 25, 1 do
			
			local line = getglobal("WebDKP_RaidInfoFrameLines" .. i);
			line:Hide();
			
		end
	end

end

-- ====================================================================
-- This function modifies the end time and total time if someone forgot to end a raid. 
-- ====================================================================
function WebDKP_EndTime()
	local end_time = WebDKP_RaidInfoFrameEndTime:GetText();
	if end_time ~= nil and end_time ~= "" then
	local testnum = tonumber(end_time);
	if testnum > 0 then
		if previous_raid ~= "" and previous_starttime ~= "" then
			if WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"] ~= nil then
					
				local pattern = "(%d%d):(%d%d):(%d%d)";
				local pattern2 = "(%d+).(%d+)";
				local st,stop = string.find (end_time, "%.")
				if  st == 1  then
					end_time = strjoin("", "0", end_time);
				end
				if st == nil then
					end_time = strjoin(".", end_time, "0");
				end
				
				_, _, hours,minutes = string.find(end_time, pattern2);				-- Setup values for end times to calc total
				
				minutes = strjoin("", ".", minutes);
				minutes = tonumber(minutes);
				minutes = 60*minutes								-- Convert the decimal value to minutes based on 60 minutes = 1 hour.
				_,_,hoursb,minutesb,secondsb = string.find(previous_starttime, pattern);	-- Setup values for start times to calc end time.
				hoursb = hoursb + hours;
				minutesb = minutesb + minutes;
				if minutesb >59 then
					hoursb = hoursb + 1;
					minutesb = minutesb - 60;

				end
				if minutesb < 10 then
					minutesb = strjoin("", "0",minutesb);
				end
				local seconds = "00";
			
				if minutes < 10 then
					minutes = strjoin("", "0", minutes);
				end
				-- Format the total time in hours:minutes
				local joinedstring = strjoin(":", hours, minutes);
				totaltimeraid = joinedstring;
	
				local joinedstringend = strjoin(":", hoursb, minutesb, seconds);
				endraid = joinedstringend;
				
				WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["totaltime"] = totaltimeraid;
				WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["endtime"] = endraid;
				
				_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
				_G["LineLocation"] = "";
				previous_raid = "";
				previous_starttime = "";
				WebDKP_UpdateRaidLogTable();
			end

		end
	end
	end

end

-- ====================================================================
-- This function deletes an attendee. 
-- ====================================================================
function WebDKP_DeleteAttendee()
	local del_name = WebDKP_RaidInfoFrameCharChange:GetText();

	if previous_raid ~= "" and previous_starttime ~= ""  and del_name ~= nil and del_name ~= "" then
		
		name_entries = WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"];
		local numEntries = getn(name_entries);

		for i=1, numEntries, 1 do
			if name_entries[i] == del_name then
				local name_rem = name_entries[i];
				local numattended = getn(WebDKP_CharRaidInfo[name_rem]["attended"]);
				for i=1, numattended, 1 do
					if WebDKP_CharRaidInfo[name_rem]["attended"][i] == "1&"..previous_raid.."&"..previous_starttime then
						-- if we are in here then total raids was already incremented so don't do it again.
						WebDKP_CharRaidInfo[name_rem]["attended"][i] = "0&"..previous_raid.."&"..previous_starttime;
					end
				end
				WebDKP_CharRaidInfo[name_rem]["raids_attended"] = WebDKP_CharRaidInfo[name_rem]["raids_attended"] - 1;
				WebDKP_CharRaidInfo[name_rem]["percent"] = WebDKP_ROUND((WebDKP_CharRaidInfo[name_rem]["raids_attended"] / WebDKP_CharRaidInfo[name_rem]["total_raids"]) * 100,0);
				table.remove(name_entries, i);	
			end
		end
	
		WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"] = name_entries;
		WebDKP_UpdateAttendedTable(previous_raid,previous_starttime)
	end



end

-- ====================================================================
-- This function deletes a character from the char raid log. 
-- ====================================================================
function WebDKP_RemAttendee()

	-- Removes the character from the array.
	WebDKP_CharRaidInfo[previous_name] = nil;
	
	WebDKP_UpdateCharRaidLogTable();
	
	-- Clear the right side of attended raids
		
	for i=1, 25, 1 do
		local line = getglobal("WebDKP_RaidInfoFrameLines" .. i);
		line:Hide();
	end
	previous_name = "";

end

-- ====================================================================
-- This function adds an attendee. 
-- ====================================================================
function WebDKP_AddAttendee()
	local add_name = WebDKP_RaidInfoFrameCharChange:GetText();
	local charflag = 0;
	local foundflag = 0;
	if WebDKP_CharRaidInfo[add_name] ~= nil then
		local charfirsttime = WebDKP_CharRaidInfo[add_name]["attended"][1];
		_, charfirstraiddate = strsplit("&", charfirsttime);
	else 
		charfirstraiddate = 0;
	end
	if previous_raid ~= "" and previous_starttime ~= ""  and add_name ~= nil and add_name ~= "" then
		
		name_entries = WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"];

		-- Make sure this player doesn't already exist in the raid.
		local numEntries = getn(name_entries);

		for k, v in pairs(name_entries) do
			charname = v;
			if charname == add_name then
				charflag = 1;	
			end	
		end
		if charflag == 0 then
			table.insert(name_entries, add_name);
			WebDKP_RaidInfo[previous_raid.." "..previous_starttime]["attended"] = name_entries;
		
		-- Adds the character information into the char raid log.
		--starttimemod, secondvar = strsplit(" ", previous_starttime)
		if WebDKP_CharRaidInfo[add_name] == nil then
			WebDKP_CharRaidInfo[add_name] = {};
			WebDKP_CharRaidInfo[add_name]["raids_attended"] = 1;
			WebDKP_CharRaidInfo[add_name]["total_raids"] = 1;
			WebDKP_CharRaidInfo[add_name]["percent"] = 100;
			WebDKP_CharRaidInfo[add_name]["attended"] = {};
			--starttimemod, secondvar = strsplit(" ", previous_starttime)
			tinsert(WebDKP_CharRaidInfo[add_name]["attended"],"1&"..previous_raid.."&"..previous_starttime);
		else
			WebDKP_CharRaidInfo[add_name]["raids_attended"] = WebDKP_CharRaidInfo[add_name]["raids_attended"] + 1;
			local numattended = getn(WebDKP_CharRaidInfo[add_name]["attended"]);
			for i=1, numattended, 1 do
				if WebDKP_CharRaidInfo[add_name]["attended"][i] == "0&"..previous_raid.."&"..previous_starttime then
					WebDKP_CharRaidInfo[add_name]["attended"][i] = "1&"..previous_raid.."&"..previous_starttime;
					foundflag = 1;
				end
			end
			-- If foundflag is 0 then this player has not been associated with this raid yet so do so.
			if foundflag == 0 then
				WebDKP_CharRaidInfo[add_name]["total_raids"] = WebDKP_CharRaidInfo[add_name]["total_raids"] + 1;
				tinsert(WebDKP_CharRaidInfo[add_name]["attended"],"1&"..previous_raid.."&"..previous_starttime);
			end
	
			WebDKP_CharRaidInfo[add_name]["percent"] = WebDKP_ROUND((WebDKP_CharRaidInfo[add_name]["raids_attended"] / WebDKP_CharRaidInfo[add_name]["total_raids"]) * 100,0);

		end
		
		end
			
		WebDKP_UpdateAttendedTable(previous_raid,previous_starttime)
	end



end

-- ====================================================================
-- This function validates the date entered and returns the date
-- ====================================================================
function WebDKP_ApplyDateFilter(filterdate)
	
	if filterdate == nil or filterdate == "" then
		WebDKP_Print("Please enter an appropiate date to apply the filter. Ex: 11-20-2010")
		return nil;
	else
		local pattern = "(%d+)-(%d+)-(%d+)";
		_, _,month, day, year = string.find(filterdate, pattern);
		if month ~= nil and day ~= nil and year ~= nil then
			return month,day,year;
		else
			WebDKP_Print("Please enter an appropiate date to apply the filter. Ex: 11-20-2010")
			return nil;
		end
			
	end


end

-- ====================================================================
-- This function processes a a person attended raids based on the filter date
-- ====================================================================
function WebDKP_ProcessCharAttendedRaids(month,day,year,charname)
	
	local totalraids = 0;
	local attendedraids = 0;
	local percent = 0;
	month = tonumber(month);
	day = tonumber(day);
	year = tonumber(year);
	-- Set the characters attended raids.
	local raidsattended = WebDKP_CharRaidInfo[charname]["attended"];

	-- Run through the array and remove any raids that are before the filter day
	local numEntries = getn(raidsattended);
	
	for i=1, numEntries, 1 do
		-- Process the data and pull out the raid date info.
		local contflag = 1;
		local raiddata = raidsattended[i];
		local pattern = "(%d+)&(.+)&(.+)";
		local _, _,attendflag, raidlocation, fulldate = string.find(raiddata, pattern);
		local datepattern = "(%d+)-(%d+)-(%d+) (.+)";
		local _, _,raidyear, raidmonth, raidday = string.find(fulldate, datepattern);
		raidyear = tonumber(raidyear);
		raidday = tonumber(raidday);
		raidmonth = tonumber(raidmonth);
		-- Now that the raid date has been broken down we can compare the info with the user defined filter date.
		if raidyear < year then
			contflag = 0;
		end
		if raidyear == year and raidmonth < month then
			contflag = 0;
		end
		if raidyear == year and raidmonth == month and raidday < day then
			contflag = 0;
		end
		-- If the date looks like it counts increment the proper counters
		if contflag == 1 then
			totalraids = totalraids + 1;
			if attendflag == "1" then
				attendedraids = attendedraids + 1;
			end		

		end
		
	
	end
	if totalraids ~= 0 then
		percent = WebDKP_ROUND(attendedraids/totalraids * 100,0);
	end
	return totalraids, attendedraids, percent;

end

-- ====================================================================
-- This function return true if the date is within the filter range
-- ====================================================================
function WebDKP_CrossCheckDate(raiddate)
	
	local contflag = 1;

	-- Process the filter date
	local filterdate = WebDKP_CharRaidInfoFrameFilterDate:GetText();
	local month, day, year = WebDKP_ApplyDateFilter(filterdate);
	month = tonumber(month);
	day = tonumber(day);
	year = tonumber(year);

	-- Process the raid date
	local datepattern = "(%d+)-(%d+)-(%d+) (.+)";
	local _, _,raidyear, raidmonth, raidday = string.find(raiddate, datepattern);
	raidyear = tonumber(raidyear);
	raidday = tonumber(raidday);
	raidmonth = tonumber(raidmonth);

	-- Now that the raid date has been broken down we can compare the info with the user defined filter date.
	if raidyear < year then
		contflag = 0;
	end
	if raidyear == year and raidmonth < month then
		contflag = 0;
	end
	if raidyear == year and raidmonth == month and raidday < day then
		contflag = 0;
	end
	-- If the date looks like it counts increment the proper counters
	if contflag == 1 then
		return true;		
	end
	return false;

end