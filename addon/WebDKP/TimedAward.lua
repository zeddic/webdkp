------------------------------------------------------------------------
-- TimedAward	
------------------------------------------------------------------------
-- Contains methods related to timed awards and the timed awards gui frame. 
-- TimedAwards provide a method to automatically award dkp at certain timed
-- intervals. Players can either set an award to continouously be made or 
-- for a 1 time award to be done after so many minutes.
--
-- Note, values for this module are contained in the WebDKP_Options datastructure. 
-- Important ones: "TimedAwardInProgress" and "TimedAwardTimer"
------------------------------------------------------------------------

-- =====================================================================
-- Performs an automatted award by awarding everyone in the current group the 
-- amount of dkp specified in the timed Boss Award Dkp box
-- Added by Zevious (Bronzebeard)
-- =====================================================================
function WebDKP_BossAward_PerformAward(arg1,arg2,arg7) 
    local GoFlag = 0;
    RaidTotal = GetNumGroupMembers()

    if (arg2 =="UNIT_DIED" or arg2 =="Freya" or arg2=="Hodir" or arg2=="Thorim" or arg2=="Mimiron" or arg2=="Brann Bronzebeard" or arg2 =="Highlord Tirion Fordring" or arg2 == "King Varian Wrynn" or arg2=="Garrosh Hellscream" or arg2=="Muradin Bronzebeard" or arg2=="High Overlord Saurfang" or arg2=="Valithria Dreamwalker" or arg2=="Lord Magmathar" or arg2=="Lord Victor Nefarius" or arg2=="Cho'gall" or arg2=="Al'Akir" or arg2=="Elementium Monstrosity" or arg2=="Omnotron" or arg2=="Nefarian" or arg2=="Shannox" or arg2=="Lord Rhyolith" or arg2=="Baleroc" or arg2=="Alysrazor" or arg2=="Ragnaros") then
        if( WebDKP_Options["AwardBossDKP"] == 1) then
            ZoneName = GetRealZoneText();
            ---------------------------------------------------------------
            -- Added by Zevious to detect Naxxramas Four Horsemen Kill
            -- 5/26/09
            ---------------------------------------------------------------
            if (arg7 == "Thane Korth'azz" and _G["KorFlag"] == 1) then
                _G["RivendareFlag"] = 0;
                _G["LadyFlag"] = 0;
                _G["SirFlag"] = 0;
            end
            if (arg7 == "Baron Rivendare" and _G["RivendareFlag"]) == 1 then
                _G["KorFlag"] = 0;
                _G["LadyFlag"] = 0;
                _G["SirFlag"] = 0;
            end
            if (arg7 == "Lady Blaumeux" and _G["LadyFlag"]) == 1 then
                _G["KorFlag"] = 0;
                _G["RivendareFlag"] = 0;
                _G["SirFlag"] = 0;
            end
            if (arg7 == "Sir Zeliek" and _G["SirFlag"]) == 1 then
                _G["KorFlag"] = 0;
                _G["RivendareFlag"] = 0;
                _G["LadyFlag"] = 0;
            end
            if (arg7 == "Thane Korth'azz") then _G["KorFlag"] = 1 end
            if (arg7 == "Baron Rivendare") then _G["RivendareFlag"] = 1 end
            if (arg7 == "Lady Blaumeux") then _G["LadyFlag"] = 1 end
            if (arg7 == "Sir Zeliek") then _G["SirFlag"] = 1 end
            if (_G["KorFlag"] + _G["RivendareFlag"] + _G["LadyFlag"] +_G["SirFlag"]  == 3) then
                arg7 ="Four Horsemen"
                _G["KorFlag"] = 0;
                _G["RivendareFlag"] = 0;
                _G["LadyFlag"] = 0;
                _G["SirFlag"] = 0;
            end

            -- This if statement checks for a boss dieing to determine if an auto award should be given.
            -- =======================================================================================================================
            if (arg7 =="Magmaw" or
                    arg7 == "Argaloth" or
                    arg7 == "Al'Akira" or
                    arg7 == "Cho'gall" or
                    arg7 == "Chimaeron" or
                    arg7 == "Sinestra" or
                    arg7 == "Emalon the Storm Watcher" or
                    arg7 == "Maloriak" or
                    arg7 == "Halfus Wyrmbreaker" or
                    arg7 == "Atramedes" or
                    arg7 == "Archavon the Stone Watcher" or
                    arg7 == "Koralon the Flame Watcher" or
                    arg7 == "Onyxia" or
                    arg7 == "Anub'Rekhan" or
                    arg7 == "Grand Widow Faerlina" or
                    arg7 == "Maexxna" or
                    arg7 == "Icehowl" or
                    arg7 == "Lord Jaraxxus" or
                    arg7 == "Instructor Razuvious" or
                    arg7 == "Gothik the Harvester" or
                    arg7 == "Four Horsemen" or
                    arg7 == "Patchwerk" or
                    arg7 == "Grobbulus" or
                    arg7 == "Gluth" or
                    arg7 == "Thaddius" or
                    arg7 == "Noth the Plaguebringer" or
                    arg7 == "Heigan the Unclean" or
                    arg7 == "Loatheb" or
                    arg7 == "Sapphiron" or
                    arg7 == "Kel'Thuzad" or
                    arg7 == "Flame Leviathan" or
                    arg7 == "Ignis the Furnace Master" or
                    arg7 == "Razorscale" or
                    arg7 == "XT-002 Deconstructor" or
                    arg7 == "Kologarn" or
                    arg7 == "Iron Council" or
                    arg7 == "Auriaya" or
                    arg7 == "Mimiron" or
                    arg7 == "Freya" or
                    arg7 == "Thorim" or
                    arg7 == "Hodir" or
                    arg7 == "General Vezax" or
                    arg7 == "Sartharion" or
                    arg7 == "Yogg-Saron" or
                    arg7 == "Malygos") or
                    arg7 == "Anub'arak" or
                    arg7 == "Fjola Lightbane" or
                    arg7 == "Lord Marrowgar" or
                    arg7 == "Lady Deathwhisper" or
                    arg7 == "Deathbringer Saurfang" or
                    arg7 == "Festergut" or
                    arg7 == "Rotface" or
                    arg7 == "Professor Putricide" or
                    arg7 == "Blood-Queen Lana'thel" or
                    arg7 == "Sindragosa" or
                    arg7 == "Valiona" or
                    arg7 == "Alysrazor" or
                    arg7 == "Ragnaros" or
                    arg7 == "Beth'tilac" or
                    arg7 == "Majordomo Staghelm" or
                    arg7 == "The Lich King" then
                if arg7 == nil then
                    arg7 = arg2;
                end
                if ZoneName == "Blackwing Descent" and arg7 == "Onyxia" then
                    return;
                end

                if RaidTotal > 15 and WebDKP_Options["AwardBossDKP25"] == 1 then GoFlag = 1 end 			-- Is the group 25 and is 25 enabled
                if RaidTotal < 11 and WebDKP_Options["AwardBossDKP10"] == 1 and RaidTotal > 5 then GoFlag = 1 end	-- Is the group 10 and is 10 enabled

                    if GoFlag == 1 then

                    PlaySound(SOUNDKIT.UI_WORLDQUEST_COMPLETE, "SFX");

                    WebDKP_UpdatePlayersInGroup();
                    local dkp = WebDKP_GeneralOptions_FrameBossDKP:GetText();
                    if(dkp == nil or dkp == "") then
                            dkp = 0;
                    end
                    dkp = tonumber(dkp);
                    WebDKP_AddDKP(dkp, "Auto Award Boss Kill: "..arg7, "false" , WebDKP_PlayersInGroup);
                    WebDKP_AnnounceBossAward(dkp);
                    WebDKP_Refresh()

                end
            end

            -- This if statement checks "Yells" to determine if a boss encounter was won.
            -- =======================================================================================================================
            if (arg2 == "Valithria Dreamwalker" and (string.find(arg1, "I AM RENEWED! Ysera grant me the favor to lay these foul creatures to rest!")==1)) or
            (arg2 == "Lord Victor Nefarius" and (string.find(arg1, "Hmm. A shame to lose that experiment...")==1)) or
            (arg2 == "Lord Victor Nefarius" and (string.find(arg1, "Impressive! You managed to destroy one of my most horrific creations - a task I'd thought impossible until now.")==1)) or
            (arg2 == "Lord Victor Nefarius" and (string.find(arg1, "I should've known better than to rely on something SO stupidly named... to entertain me for long.")==1)) or
            (arg2 == "Muradin Bronzebeard" and (string.find(arg1, "Don't say I didn't warn ya, scoundrels! Onward, brothers and sisters!")==1)) or
            (arg2 == "Muradin Bronzebeard" and (string.find(arg1, "That malfunctioning piece of junk was murder on the repair bills.")==1)) or
            (arg2 == "High Overlord Saurfang" and (string.find(arg1, "The Alliance falter. Onward to the Lich King!")==1)) or
            (arg2 == "Omnotron" and (string.find(arg1, "Defense systems obliterated. Powering down...")==1)) or
            (arg2 == "Brann Bronzebeard" and (string.find(arg1, "You've defeated the Iron Council")==1)) or
            (arg2 == "Thorim" and (string.find(arg1, "Stay your arms! I yield!" )== 1)) or
            (arg2 == "Hodir" and (string.find(arg1, "I... I am released from his grasp... at last." )== 1)) or
            (arg2 == "Freya" and (string.find(arg1, "His hold on me dissipates." )== 1)) or
            (arg2 == "Mimiron" and (string.find(arg1, "It would appear that I've made a slight miscalculation.")== 1)) or
            (arg2 == "Lord Jaraxxus" and (string.find(arg1, "Another will take my place. Your world is doomed.")== 1)) or
            (arg2 == "King Varian Wrynn" and (string.find(arg1, "GLORY TO THE ALLIANCE!")== 1)) or
            (arg2 == "Al'Akir" and (string.find(arg1, "GLORY TO THE ALLIANCE!")== 1)) or
            (arg2 == "Cho'gall" and (string.find(arg1, "Foolish mortals-(Usurper's children!)")== 1)) or
            (arg2 == "Al'Akir" and (string.find(arg1, "The Conclave of Wind has dissipated. Your honorable conduct and determination have earned you")== 1)) or
            (arg2 == "Al'Akir" and (string.find(arg1, "After every storm... comes the calm...")== 1)) or
            (arg2 == "Elementium Monstrosity" and (string.find(arg1, "Impossible...")== 1)) or
            (arg2 == "Nefarian" and (string.find(arg1, "Defeat has never tasted so bitter...")== 1)) or
            (arg2 == "Shannox" and (string.find(arg1, "Ohh... the pain")== 1)) or
            (arg2 == "Lord Rhyolith" and (string.find(arg1, "Broken. Mnngghhh... broken...")== 1)) or
            (arg2 == "Baleroc" and (string.find(arg1, "Mortal filth... the master's keep is forbidden....")== 1)) or
            (arg2 == "Alysrazor" and (string.find(arg1, "The light...")== 1)) or
            (arg2 == "Ragnaros" and (string.find(arg1, "Too soon!")== 1)) or
            (arg2 == "Garrosh Hellscream" and (string.find(arg1, "FOR THE HORDE!")== 1)) then
                if arg2 == "Brann Bronzebeard" and (string.find(arg1, "You've defeated the Iron Council")==1) then
                    arg2 = "Iron Council";
                elseif arg2 == "King Varian Wrynn" and (string.find(arg1, "GLORY TO THE ALLIANCE!")== 1) or
                        arg2 == "Garrosh Hellscream" and (string.find(arg1, "FOR THE HORDE!")== 1) then
                    arg2 = "The Champions - ToC";
                elseif arg2 == "Garrosh Hellscream" and (string.find(arg1, "FOR THE HORDE!")== 1) then
                    arg2 = "The Champions - ToC";
                elseif arg2 == "Muradin Bronzebeard" and (string.find(arg1, "Don't say I didn't warn ya, scoundrels! Onward, brothers and sisters!")==1) or
                        arg2 == "High Overlord Saurfang" and (string.find(arg1, "The Alliance falter. Onward to the Lich King!")==1) then
                    arg2 = "Gunship Battle - ICC";
                end
                if RaidTotal > 15 and WebDKP_Options["AwardBossDKP25"] == 1 then GoFlag = 1 end 			-- Is the group 25 and is 25 enabled
                if RaidTotal < 11 and WebDKP_Options["AwardBossDKP10"] == 1 and RaidTotal > 5 then GoFlag = 1 end	-- Is the group 10 and is 10 enabled

                if GoFlag == 1 then

                    PlaySound(SOUNDKIT.UI_WORLDQUEST_COMPLETE, "SFX");

                    WebDKP_UpdatePlayersInGroup();
                    local dkp = WebDKP_GeneralOptions_FrameBossDKP:GetText();
                    if(dkp == nil or dkp == "") then
                            dkp = 0;
                    end
                    dkp = tonumber(dkp);
                    if arg2 == "Valiona" then
                        arg2 = "BoT Twins";
                    end
                    WebDKP_AddDKP(dkp, "Auto Award Boss Kill: "..arg2, "false" , WebDKP_PlayersInGroup);
                    WebDKP_AnnounceBossAward(dkp);
                    WebDKP_Refresh()

                end
            end
        end
    end
-- End of the Boss Award Function
end



-- ================================
-- Toggles displaying the timed award panel
-- ================================
function WebDKP_TimedAward_ToggleUI()
	if ( WebDKP_TimedAwardFrame:IsShown() ) then
		WebDKP_TimedAwardFrame:Hide();
	else
		WebDKP_TimedAwardFrame:Show();
		local time = WebDKP_TimedAwardFrameTime:GetText();
		if(time == nil or time == "") then
			WebDKP_TimedAwardFrameTime:SetText("5");
		end
		local dkp = WebDKP_TimedAwardFrameDkp:GetText();
		if(dkp == nil or dkp == "") then
			WebDKP_TimedAwardFrameDkp:SetText("0");
		end
	end
end


-- ================================
-- Toggles displaying mini timer
-- ================================
function WebDKP_TimedAward_ToggleMiniTimer()
	if ( WebDKP_TimedAward_MiniFrame:IsShown() ) then
		WebDKP_TimedAward_MiniFrame:Hide();
		WebDKP_Options["TimedAwardMiniTimer"] = 0;
	else
		WebDKP_TimedAward_MiniFrame:Show();
		WebDKP_Options["TimedAwardMiniTimer"] = 1;
	end
end

-- ================================
-- Shows the Bid UI
-- ================================
function WebDKP_TimedAward_ShowUI()
	WebDKP_TimedAwardFrame:Show();
	local time = WebDKP_TimedAwardFrameTime:GetText();
	if(time == nil or time == "") then
		WebDKP_TimedAwardFrameTime:SetText("0");
	end
	local dkp = WebDKP_TimedAwardFrameDkp:GetText();
	if(dkp == nil or dkp == "") then
		WebDKP_TimedAwardFrameTime:SetText("0");
	end
end

-- ================================
-- Hides the Bid UI
-- ================================
function WebDKP_TimedAward_HideUI()
	WebDKP_TimedAwardFrame:Hide();
end

-- ================================
-- Triggers The Timer to Start / Stop
-- ================================
function WebDKP_TimedAward_ToggleTimer()
	if ( WebDKP_Options["TimedAwardInProgress"] == true ) then			--Stop the timer
		WebDKP_Options["TimedAwardInProgress"] = false;
		WebDKP_TimedAwardFrameStartStopButton:SetText("Start");
		WebDKP_TimedAward_UpdateFrame:Hide();
		WebDKP_TimedAward_UpdateText();

	else
		WebDKP_Options["TimedAwardInProgress"] = true;			--Start the timer
		
		if ( WebDKP_Options["TimedAwardTimer"] == 0 ) then
			local time = WebDKP_TimedAwardFrameTime:GetText();
			if(time == nil or time == "") then
				time = 5;
			end
			WebDKP_Options["TimedAwardTimer"] = time * 60;
		end
		
		WebDKP_TimedAwardFrameStartStopButton:SetText("Stop");
		WebDKP_TimedAward_UpdateFrame:Show();
		WebDKP_TimedAward_UpdateText();
	end
end

-- ================================
-- Resets the timer to start counting from scartch again
-- ================================
function WebDKP_TimedAward_ResetTimer()
	local time = WebDKP_TimedAwardFrameTime:GetText();
	if(time == nil or time == "") then
		time = 5;
	end
	WebDKP_Options["TimedAwardTimer"] = time * 60;
	WebDKP_TimedAward_UpdateText();
end


-- ================================
-- Event handler for the bidding update frame. The update frame is visible (and calling this method)
-- when a timer value was specified. The addon countdowns until 0 - and when it reaches 0 it stops
-- the current bid
-- ================================
function WebDKP_TimedAward_OnUpdate(self, elapsed)
local this = self;	
	this.TimeSinceLastUpdate = this.TimeSinceLastUpdate + elapsed; 	

	if (this.TimeSinceLastUpdate > 1.0) then
		this.TimeSinceLastUpdate = 0;
		-- decrement the count down
		WebDKP_Options["TimedAwardTimer"] = WebDKP_Options["TimedAwardTimer"] - 1;
		
		WebDKP_TimedAward_UpdateText();
		
		--update the gui
		
		if ( WebDKP_Options["TimedAwardTimer"] <= 0 ) then			-- countdown reached 0
			WebDKP_TimedAward_PerformAward();

			-- if we are set to repeat the awards, go ahead and start the timer again
			if ( WebDKP_Options["TimedAwardRepeat"] == 1 ) then
				
				WebDKP_TimedAward_ResetTimer();
			else
				-- it was a one time award, stop everything so we don't start going into negative numbers
				WebDKP_Options["TimedAwardInProgress"] = false;
				WebDKP_TimedAwardFrameStartStopButton:SetText("Start");
				WebDKP_TimedAward_UpdateFrame:Hide();
			end
		end
	end
end

-- ================================
-- Updates the timer gui to show how many minutes / seconds are left
-- ================================
function WebDKP_TimedAward_UpdateText()
	
	local toDisplay = "";
	local minutes = floor(WebDKP_Options["TimedAwardTimer"] / 60);
	local seconds = WebDKP_Options["TimedAwardTimer"] % 60;
	
	if ( minutes > 0 ) then
		toDisplay = toDisplay..minutes..":";
	end
	if ( seconds < 10 ) then
		seconds = "0"..seconds;
	end
	toDisplay = toDisplay..seconds;
	
	WebDKP_TimedAwardFrameTimeLeft:SetText("Time Left: "..toDisplay);
	WebDKP_TimedAward_MiniFrameTimeLeft:SetText(toDisplay);
	
end


-- ================================
-- Performs an automatted award by awarding everyone in the current group the 
-- amount of dkp specified in the timed award gui box. Should be 
-- called when the auto timer finishes
-- ================================
function WebDKP_TimedAward_PerformAward() 

	PlaySound(SOUNDKIT.UI_WORLDQUEST_COMPLETE, "SFX");

	WebDKP_UpdatePlayersInGroup();
	local allplayers = WebDKP_PlayersInGroup;
	local numPlayers = WebDKP_GetTableSize(WebDKP_PlayersInGroup);
	local dkp = WebDKP_TimedAwardFrameDkp:GetText();
	if(dkp == nil or dkp == "") then
		dkp = 0;
	end
	dkp = tonumber(dkp);

	-- Check to see if standby players should count and if any are in standby
	if WebDKP_Options["TimedStandby"] == 1 then
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

	if WebDKP_Options["EPGPEnabled"] == 1 then
		WebDKP_AddEP(dkp, "Timed Auto Award", "false", WebDKP_PlayersInGroup);
		WebDKP_UpdateTableToShow();
		WebDKP_UpdateEPGPTable();

	else
		WebDKP_AddDKP(dkp, "Timed Auto Award", "false" , WebDKP_PlayersInGroup);
		WebDKP_UpdateTableToShow();
		WebDKP_UpdateTable();
	end
	
	WebDKP_AnnounceTimedAward( WebDKP_TimedAwardFrameTime:GetText(), dkp ); 
	
	WebDKP_Refresh()
	
end