------------------------------------------------------------------------
-- WebDKP Synchronization
-- Utilizes the Addon channel to distribute DKP / Award Log Changes
------------------------------------------------------------------------
-- Contains methods related to Synchonizing DKP Tables with other users.
------------------------------------------------------------------------
local synch_started = 0;		-- Tells us we've started synching so check messages
local multiawardflag = 0;		-- Tells us if a multiple award message has been found
local glob_dkp = 0;			-- Lets us store the global dkp value for multiple char awards
local glob_reason = "";			-- Lets us store the global reason for multiple char awards
local glob_numawarded = 0;		-- Lets us store the global value for number of people awarded
local glob_charlist = {};		-- Lets us store the list of people to be awarded
local glob_chars = "";			-- One string of all chars to broken down into an array.
local glob_awards = {};			-- Temp array to keep track of multiple awards received in case there's more than one award at once . . prob rare but might as well factor it in
		
-- ===========================================================================================
-- Toggles the Synchronizing Frame.
-- ===========================================================================================
function WebDKP_Synch_ToggleUI()
	if ( WebDKP_SynchFrame:IsShown() ) then
		WebDKP_SynchFrame:Hide();
	else
		WebDKP_SynchFrame:Show();
	end 
end


-- ===========================================================================================
-- This function receives whisper messages and proccesses them.
-- It will only process messages when everything is enabled and synching was initiated.
-- ===========================================================================================
function WebDKP_Synch_Processing(arg1,arg2)

	local name = arg2;
	local cmd = arg1;
	local synch_pass = WebDKP_Options["SynchPassword"];
	local enabled = WebDKP_Options["EnableSynch"];
	local masterorbackup = WebDKP_Options["MasterOrBackup"];
	local synch_master = WebDKP_Options["SynchFrom"];
	local tableid = WebDKP_GetTableid();
	if ((string.find(cmd,"!Synch")==1) and (string.find(cmd,synch_pass)==8)) then
		WebDKP_Synch_Send(name);								-- Calls the Send Synch Function to send the DKP Table							
	end
	if ((string.find(cmd,"!WDKP_Synch_All")==1) ) then
		WebDKP_Synch_SendAll(name);								-- Calls the Send Synch Function to send the DKP Table							
	end
		
	
	-- This will process the DKP Table
	if (string.find(cmd,"!Sending")==1) and (string.find(cmd,synch_pass)==10) and enabled == 1 then
		local pattern = "!Sending (.+) (.+),(.+),(.+)";
		_, _,test, nameofchar, class, dkp = string.find(cmd, pattern);
		dkp = tonumber(dkp);
		if nameofchar ~= nil then
			if WebDKP_DkpTable[nameofchar] ~= nil then
				WebDKP_DkpTable[nameofchar]["dkp_"..tableid] = dkp;
			else -- Add to table
				-- new person, they need to be added
				local playerTier = 0;
				WebDKP_DkpTable[nameofchar] = {
					["dkp_"..tableid] = dkp,
					["class"] = class,
						
					}
			end
		end
	end	

	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	

end

-- ===========================================================================================
-- This function processes message in the addon channel.
-- It will only process messages if synching is enabled.
-- ===========================================================================================
function WebDKP_AddonChan_Processing(arg1,arg2,arg3,arg4)
	local senderName = arg4;
	local command = arg1;
	local incmsg = arg2;
	local enabled = WebDKP_Options["EnableSynch"];
	local foundBackup = 0;
	local date = "";
	local tableid = WebDKP_GetTableid();
	if (not WebDKP_Log) then
		WebDKP_Log = {};
	end
	if incmsg == nil then
		incmsg = "NONE";
	end

    if enabled == 1 then
        -- First thing we need to do is compare the sender of the message to our list of backup users. If the user is in the list accept the message.
        if WebDKP_Synch_Users ~= nil then
            numEntries = getn(WebDKP_Synch_Users);
        end
        for i=1, numEntries, 1 do
            if WebDKP_Synch_Users[i] == senderName then
                foundBackup = 1;
            end
        end
        -- Assuming the message was from someone in the backup list then continue
        if foundBackup == 1 then
            -- Look at the prefix and determine if its an item award or multiple award of some sort
            if (string.find(command,"!WDKPA Item")==1) then
                -- Proceed with processing for an item award
                local itempattern = "(.+),(.+),(.+),(.+)";
                _, _, dkp, nameofchar, reason, date, _, test2 = string.find(incmsg, itempattern);
                dkp = tonumber(dkp);

                local _,itemName,itemLink = WebDKP_GetItemInfo(reason);
                reason = itemName;
                if nameofchar ~= nil then
                    if WebDKP_DkpTable[nameofchar] ~= nil then
                        WebDKP_DkpTable[nameofchar]["dkp_"..tableid] = WebDKP_DkpTable[nameofchar]["dkp_"..tableid] + dkp;
                    else -- Add to table
                        -- new person, they need to be added
                        local playerTier = 0;
                        WebDKP_DkpTable[nameofchar] = {
                            ["dkp_"..tableid] = dkp,
                            ["class"] = class,

                            }
                    end
                    if (not WebDKP_Log[reason.." "..date]) then
                        WebDKP_Log[reason.." "..date] = {};
                    end
                    WebDKP_Log[reason.." "..date]["awarded"] = {};
                    WebDKP_Log[reason.." "..date]["awarded"][nameofchar] = {};
                    WebDKP_Log[reason.." "..date]["awarded"][nameofchar]["name"] = nameofchar;
                    WebDKP_Log[reason.." "..date]["itemlink"] = itemLink;
                    WebDKP_Log[reason.." "..date]["reason"] = reason;
                    WebDKP_Log[reason.." "..date]["date"] = date;
                    WebDKP_Log[reason.." "..date]["foritem"] = "true";
                    WebDKP_Log[reason.." "..date]["zone"] = "In Game Synched";
                    WebDKP_Log[reason.." "..date]["tableid"] = tableid;
                    WebDKP_Log[reason.." "..date]["awardedby"] = senderName;
                    WebDKP_Log[reason.." "..date]["points"] = dkp;

                    WebDKP_UpdateTableToShow();
                    WebDKP_UpdateTable();
                    WebDKP_UpdateLogTable();

                end

            elseif (string.find(command,"!WDKP MultStart")==1) then
                -- Proceed with processing for a multiple award
                local itempattern = "(.+),(.+),(.+),(.+)";
                _, _, dkp, reason, numawarded,date, _, test2 = string.find(incmsg, itempattern);
                dkp = tonumber(dkp);
                numawarded = tonumber(numawarded);

                -- If there isn't an award going by this char then set one up
                if glob_awards[senderName] == nil then
                    glob_awards[senderName] = {};
                end

                -- Clear any previous award in global this char may have glitched on
                glob_awards[senderName]["awarded"] = "";

                glob_awards[senderName]["dkp"] = dkp;
                glob_awards[senderName]["reason"] = reason;
                glob_awards[senderName]["numawarded"] = numawarded;
                glob_awards[senderName]["sendername"] = senderName;
                glob_awards[senderName]["date"] = date;


            elseif (string.find(command,"!WDKP MultNames")==1) then
                -- Proceed with appending this string with the global, we will break it down when the end is received.

                if glob_awards[senderName]["awarded"] == nil or glob_awards[senderName]["awarded"] == "" then
                    glob_awards[senderName]["awarded"] = incmsg;
                else
                    glob_awards[senderName]["awarded"] = strjoin(",",glob_awards[senderName]["awarded"], incmsg);
                end

            elseif (string.find(command,"!WDKP Mult End")==1) then
                -- Process the received data and clear it when finished
                reason = glob_awards[senderName]["reason"];
                date = glob_awards[senderName]["date"];

                if (not WebDKP_Log) then
                    WebDKP_Log = {};
                end

                WebDKP_Log["Version"] = 2;


                if (not glob_awards[senderName]["awarded_array"]) then
                    glob_awards[senderName]["awarded_array"] = {};
                end

                -- Convert the string of names into an array.
                for i=1, glob_awards[senderName]["numawarded"], 1 do
                    name1, name2 = strsplit(",", glob_awards[senderName]["awarded"],2);
                    glob_awards[senderName]["awarded"] = name2;
                    glob_awards[senderName]["awarded_array"][i] = name1;
                end
                -- ====================== Process Decay Awards ==========================
                if reason == "Decay" then
                    local reason_counter = 0;
                    decay_value = glob_awards[senderName]["dkp"];
                    for i=1, glob_awards[senderName]["numawarded"], 1 do
                        name = glob_awards[senderName]["awarded_array"][i];
                        class = "";
                        guild = WebDKP_GetGuildName(name);
                        dkp = WebDKP_GetDKP(name); -- how much dkp do they have now
                        if ( dkp >= 2 and decay_value > 0) then
                            points = WebDKP_ROUND(dkp * decay_value, 0) * -1;
                        elseif (dkp < 2 and decay_value < 0) then
                            points = WebDKP_ROUND(dkp * decay_value, 0);
                        else
                            points = 0;
                        end
                        WebDKP_AddDKPToTable(name, class, points);

                        local reason = "Decay_"..reason_counter;
                        if (not WebDKP_Log[reason.." "..date]) then
                            WebDKP_Log[reason.." "..date] = {};
                        end

                        WebDKP_Log[reason.." "..date]["itemlink"] = "Decay Award";
                        WebDKP_Log[reason.." "..date]["reason"] = reason;
                        WebDKP_Log[reason.." "..date]["date"] = date;
                        WebDKP_Log[reason.." "..date]["foritem"] = "false";
                        WebDKP_Log[reason.." "..date]["zone"] = "In game synched";
                        WebDKP_Log[reason.." "..date]["tableid"] = tableid;
                        WebDKP_Log[reason.." "..date]["awardedby"] = senderName;
                        WebDKP_Log[reason.." "..date]["points"] = points;


                        if (not WebDKP_Log[reason.." "..date]["awarded"]) then
                            WebDKP_Log[reason.." "..date]["awarded"] = {};
                        end

                        WebDKP_Log[reason.." "..date]["awarded"][name] = {};
                        WebDKP_Log[reason.." "..date]["awarded"][name]["name"]=name;
                        WebDKP_Log[reason.." "..date]["awarded"][name]["guild"]=guild;
                        WebDKP_Log[reason.." "..date]["awarded"][name]["class"]=class;
                        reason_counter = reason_counter + 1;
                    end
                    WebDKP_Print("Decay has been applied.");
                    WebDKP_UpdateTableToShow();
                    WebDKP_UpdateTable();
                    WebDKP_UpdateLogTable();

                -- ====================== Process Other Multi Person Awards ==========================
                else
                    --next, check to see if this awards already exists
                    if (not WebDKP_Log[reason.." "..date]) then
                        WebDKP_Log[reason.." "..date] = {};
                    end

                    WebDKP_Log[reason.." "..date]["itemlink"] = reason;
                    WebDKP_Log[reason.." "..date]["reason"] = reason;


                    WebDKP_Log[reason.." "..date]["date"] = date;
                    WebDKP_Log[reason.." "..date]["foritem"] = "false";
                    WebDKP_Log[reason.." "..date]["zone"] = "In Game Synched";
                    WebDKP_Log[reason.." "..date]["tableid"] = tableid;
                    WebDKP_Log[reason.." "..date]["awardedby"] = senderName;
                    WebDKP_Log[reason.." "..date]["points"] = glob_awards[senderName]["dkp"];

                    if (not WebDKP_Log[reason.." "..date]["awarded"]) then
                        WebDKP_Log[reason.." "..date]["awarded"] = {};
                    end


                    -- Loop through the list of people awarded and add them to the log along with make the DKP change.
                    for i=1, glob_awards[senderName]["numawarded"], 1 do
                            name = glob_awards[senderName]["awarded_array"][i];
                            class = "";
                            guild = WebDKP_GetGuildName(name);

                            local DKPCapVal = tonumber(WebDKP_Options["dkpCapLimit"]);
                            if WebDKP_Options["dkpCap"] == 1 and (WebDKP_DkpTable[name]["dkp_"..tableid] + glob_awards[senderName]["dkp"] > DKPCapVal) then
                                local points2 = DKPCapVal - WebDKP_DkpTable[name]["dkp_"..tableid];

                                if (not WebDKP_Log[reason.." CAP"..points2.." "..date]) then
                                    WebDKP_Log[reason.." CAP"..points2.." "..date] = {};
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["itemlink"] = reason;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["reason"] = reason.." CAP"..points2;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["date"] = date;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["foritem"] = "false";
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["zone"] = "In Game Synched CAP";
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["tableid"] = tableid;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["awardedby"] = senderName;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["points"] = points2;
                                    WebDKP_Log[reason.." CAP"..points2.." "..date]["awarded"] = {};
                                end
                                --add them to the log entry
                                WebDKP_Log[reason.." CAP"..points2.." "..date]["awarded"][name] = {};
                                WebDKP_Log[reason.." CAP"..points2.." "..date]["awarded"][name]["name"]=name;
                                WebDKP_Log[reason.." CAP"..points2.." "..date]["awarded"][name]["guild"]=guild;
                                WebDKP_Log[reason.." CAP"..points2.." "..date]["awarded"][name]["class"]=class;
                                WebDKP_AddDKPToTable(name, class, points2);
                            else

                                WebDKP_AddDKPToTable(name, class, glob_awards[senderName]["dkp"]);

                                --add them to the log entry
                                WebDKP_Log[reason.." "..date]["awarded"][name] = {};
                                WebDKP_Log[reason.." "..date]["awarded"][name]["name"]=name;
                                WebDKP_Log[reason.." "..date]["awarded"][name]["guild"]=guild;
                                WebDKP_Log[reason.." "..date]["awarded"][name]["class"]=class;
                            end

                    end
                    WebDKP_UpdateTableToShow();
                    WebDKP_UpdateTable();
                    WebDKP_UpdateLogTable();
                end


            elseif (string.find(command,"!WDKPA Undo")==1) then
                -- Proceed with processing an UNDO message.
                local itempattern = "(.+),(.+)";
                _, _, reason, date, _, test2 = string.find(incmsg, itempattern);

                if WebDKP_Log[reason.." "..date] ~= nil then
                _G["AwardedReason"] = reason;
                _G["AwardedDate"] = date;

                    if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil then

                        awardedtoremove = WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"];	-- Assigns the table of people awarded
                        tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
                        local points = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["points"];
                        local reason = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["reason"];
                        points = tonumber(points) * -1;
                        for k, v in pairs(awardedtoremove) do
                            if ( type(v) == "table" ) then
                                name = v["name"];
                                WebDKP_AddDKPToTable(name, _, points,tableidfrom);
                            end
                        end

                        WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]] = nil;
                        _G["LineLocation"] = "";
                        _G["AwardedReason"] = "";
                        _G["AwardedDate"] = "";
                        WebDKP_UpdateLogTable();
                    end

                    for i=1, 25, 1 do

                        local line = getglobal("WebDKP_LogFrameLines" .. i);
                        line:Hide();

                    end
                    local numEntries = 0;
                    FauxScrollFrame_Update(WebDKP_LogFrameScrollAwardedFrame, numEntries, 25, 20);
                    WebDKP_UpdateTableToShow();
                    WebDKP_UpdateTable();
                    WebDKP_UpdateLogTable();
                end
            -- Proceeed with processing adding someone to an award
            elseif (string.find(command,"!WDKPA LogAdd")==1) then
                -- Proceed with processing adding a character to an award
                local itempattern = "(.+),(.+),(.+),(.+)";
                _, _, reason, date, awardedtoadd, points, _, test2 = string.find(incmsg, itempattern);
                points = tonumber(points);
                _G["AwardedReason"] = reason;
                _G["AwardedDate"] = date;
                if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil then
                    if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
                        if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil and awardedtoadd ~= "" and awardedtoadd ~= nil then

                            tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
                            WebDKP_AddDKPToTable(awardedtoadd, _, points,tableidfrom);
                            local class = WebDKP_GetPlayerClass(awardedtoadd)
                            WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd] = {};
                            WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd]["name"] = awardedtoadd;
                            WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd]["class"] = class;
                            WebDKP_UpdateLogTable();
                        end

                        WebDKP_UpdateAwardedTable(_G["AwardedReason"],_G["AwardedDate"])
                        WebDKP_UpdateTableToShow();
                        WebDKP_UpdateTable();
                    end
                end
            -- Proceed with processing deleting a char from the award log
            elseif (string.find(command,"!WDKPA LogDel")==1) then

                -- Proceed with processing deleting a character from an award
                local itempattern = "(.+),(.+),(.+),(.+)";
                _, _, reason, date, awardedtodel, points, _, test2 = string.find(incmsg, itempattern);

                local charflag = 0;
                _G["AwardedReason"] = reason;
                _G["AwardedDate"] = date;

                if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
                    if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil and awardedtodel ~= "" and awardedtodel ~= nil then


                        tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
                        local nameEntries = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"];
                        local numEntries = getn(nameEntries)
                        for k, v in pairs(nameEntries) do
                            if ( type(v) == "table" ) then
                                if( v["name"] ~= nil) then

                                    charname = v["name"];

                                    if charname == awardedtodel then

                                        charflag = 1;
                                    end

                                end
                            end
                        end

                        if charflag == 1 then

                            WebDKP_AddDKPToTable(awardedtodel, _, points,tableidfrom);
                            WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtodel] = nil;

                            -- Check to see if there is no one else under the awarded list, if so delete the award.
                            numEntries = getn(nameEntries);
                            if numEntries == nil then
                                WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]] = nil;
                                local line = getglobal("WebDKP_LogFrameLines" .. 1);
                                line:Hide();
                            --else
                            --	WebDKP_UpdateAwardedTable(_G["AwardedReason"],_G["AwardedDate"])
                            end

                            WebDKP_UpdateLogTable();
                            WebDKP_UpdateAwardedTable(_G["AwardedReason"],_G["AwardedDate"])
                            WebDKP_UpdateTableToShow();
                            WebDKP_UpdateTable();
                        end
                    end
                end
            end
        end
    end
end

-- ===========================================================================================
-- This function sends the DKP Table.
-- ===========================================================================================
function WebDKP_Synch_Send(name)
	
	local synch_pass = WebDKP_Options["SynchPassword"];
	local synch_master = WebDKP_Options["SynchFrom"];
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			local playerName = k; 
			local class = WebDKP_DkpTable[playerName]["class"];
			local playerDkp = WebDKP_GetDKP(playerName, tableid);
			SendChatMessage("!Sending "..synch_pass.." "..playerName..","..class.."," ..playerDkp, "WHISPER", nil, name)
		end
	end


end	-- End of WebDKP_Synch_Send()

-- ===========================================================================================
-- This function sends the log data to the addon channel
-- ===========================================================================================
function WebDKP_Synch_SendAll(name)
	for k, v in pairs(WebDKP_Log) do
		if ( type(v) == "table" ) then
			local awardinfo = k; 
			local foritem = WebDKP_Log[awardinfo]["foritem"];
			local points = WebDKP_Log[awardinfo]["points"];
			local players = WebDKP_Log[awardinfo]["awarded"];
			local reason = WebDKP_Log[awardinfo]["reason"];
			local date = WebDKP_Log[awardinfo]["date"];		

			-- Send data to the existing autogive function
			WebDKP_Synch_Auto(points, foritem, players, reason, date)	
		end
	end

	-- Now send the DKP Values, this should correct any duplicate DKP values
	WebDKP_Synch_Send(name)

end	-- End of WebDKP_Synch_SendAll()




-- ===========================================================================================
-- This function adds someone to the list of backup users.
-- ===========================================================================================
function WebDKP_Synch_AddName()
	local intableflag = 0;
	local add_user = WebDKP_SynchFrameAddSynchUser:GetText();

	if add_user ~= "" then
	local numEntries = getn(WebDKP_Synch_Users);
		for i=1, numEntries, 1 do
			
			local playerName = WebDKP_Synch_Users[i];
			
			if playerName == add_user then
				intableflag = 1;
			end
		end
		if intableflag == 0 then
			tinsert(WebDKP_Synch_Users,add_user);
		end
	end
	WebDKP_SynchFrameAddSynchUser:SetText("");
	WebDKP_Synch_Update();

end	-- End of WebDKP_Synch_AddName()


-- ===========================================================================================
-- This function deletes someone to the list of backup users.
-- ===========================================================================================
function WebDKP_Synch_DelName()
	local add_user = WebDKP_SynchFrameAddSynchUser:GetText();

	if add_user ~= "" then
	local numEntries = getn(WebDKP_Synch_Users);
		for i=1, numEntries, 1 do
			
			local playerName = WebDKP_Synch_Users[i];
			
			if playerName == add_user then
				tremove(WebDKP_Synch_Users, i);
			end
		end
	end
	WebDKP_SynchFrameAddSynchUser:SetText("");
	WebDKP_Synch_Update();

end	-- End of WebDKP_Synch_DelName()

-- ===========================================================================================
-- This function updates the listing of backup users
-- ===========================================================================================
function WebDKP_Synch_Update()

	local numEntries = getn(WebDKP_Synch_Users);
	local offset = FauxScrollFrame_GetOffset(WebDKP_SynchFrameBackupScrollFrame);
	FauxScrollFrame_Update(WebDKP_SynchFrameBackupScrollFrame, numEntries, 5, 20);
	-- Run through the table lines and put the appropriate information into each line
	for i=1, 5, 1 do
		local line = getglobal("WebDKP_SynchFrameLine" .. i);
		local nameText = getglobal("WebDKP_SynchFrameLine" .. i .. "Name");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_SynchFrameBackupScrollFrame); 
		
		if ( index <= numEntries) then
			local playerName = WebDKP_Synch_Users[index];
			line:Show();
			nameText:SetText(WebDKP_Synch_Users[index]);

		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end


end	-- End of WebDKP_Synch_Update()


-- ===========================================================================================
-- This function whispers backup users DKP changes on the master automatically
-- ===========================================================================================

function WebDKP_Synch_Auto(points, foritem, players, reason, date)
	local synch_pass = WebDKP_Options["SynchPassword"];
	local startvalue = 0;
	local convertedlist = {};
	local countedlist = 0;
	--if WebDKP_Synch_Users ~= nil then
	--	numEntries = getn(WebDKP_Synch_Users);
	--end

	-- Process Undo Awards - Simply send the reason and date then call the undo function if that user has the same entry
	if foritem == "UNDO" and reason ~= nil and date ~= nil then				-- This processes awards involved with an item
		SendAddonMessage("!WDKPA Undo", reason..","..date, "GUILD");
		
	elseif foritem == "LOGADD" and reason ~=nil and date ~= nil then
		-- Since we are using LogAdd we know players is not an array only a string of one player
		SendAddonMessage("!WDKPA LogAdd", reason..","..date..","..players..","..points, "GUILD");
	elseif foritem == "LOGDEL" and reason ~=nil and date ~=nil then
		-- Since we are using LogDel we know players is not an array only a string of one player
		SendAddonMessage("!WDKPA LogDel", reason..","..date..","..players..","..points, "GUILD");		
	else

		-- Some methods start counting at 0 while others at 1 so basically go through the list and start everything at 0
		for k, v in pairs(players) do
			if ( type(v) == "table" ) then
				playerName = v["name"];
				tinsert(convertedlist,playerName);
			end
		end
		numAwarded = getn(convertedlist);
	end

	-- Process Item Awards
	if foritem == "true" and players ~= nil then				-- This processes awards involved with an item
		SendAddonMessage("!WDKPA Item", points..","..playerName..","..reason..","..date, "GUILD")
	end

	if players ~= nil and foritem == "false" then				-- This processes awards
		SendAddonMessage("!WDKP MultStart", points..","..reason..","..numAwarded..","..date, "GUILD")	-- Announce the start of a multiple award so it monitors for names
		
		local countHolder = 1;
		local CharList1 = convertedlist[1];
		for i=2, numAwarded, 1 do				-- This loop goes through the player list up to 10 to send the list of names
			countHolder = countHolder + 1;

			playerName = convertedlist[i];
			CharList1 = strjoin(",", CharList1, playerName) ;

			if countHolder == 10 then
				SendAddonMessage("!WDKP MultNames", CharList1, "GUILD")		-- Announce the first 10 awardees
				i = i + 1;
				if convertedlist[i] ~= nil then
					playerName = convertedlist[i];
					playerName = convertedlist[i];
					CharList1 = playerName;
					countHolder = 1;
				end
			end

		end
		if countHolder < 10 then
			SendAddonMessage("!WDKP MultNames", CharList1, "GUILD")				-- Announce the first 10 awardees
			
		end


		SendAddonMessage("!WDKP Mult End", "", "GUILD")					-- Announce the end of a multiple award	
	end


end -- End of WebDKP_Synch_Auto()

