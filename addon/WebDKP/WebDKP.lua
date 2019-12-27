------------------------------------------------------------------------
-- WEB DKP
------------------------------------------------------------------------
-- An addon to help manage the dkp for a guild. The addon provides a 
-- list of the dkp of all players as well as an interface to add / deduct dkp 
-- points. 
-- The addon generates a log file which can then be uploaded to a companion 
-- website at www.webdkp.com
--
--
-- HOW THIS ADDON IS ORGANIZED:
-- The addon is grouped into a series of files which hold code for certain
-- functions. 
-- 
-- WebDKP			Code to handle start / shutdown / registering events
--					and GUI event handlers. This is the main entry point
--					of the addon and directs events to the functionality
--					in the other files
--
-- GroupFunctions	Methods the handle scanning the current group, updating
--					the dkp table to be show based on filters, sorting, 
--					and updating the gui with the current table
--
-- Announcements	Code handling announcements as they are echoed to the screen
--
-- WhisperDKP		Implementation of the Whisper DKP feature. 
--
-- Utility			Utility and helper methods. For example, methods
--					to find out a users guild or print something to the 
--					screen. 
--
-- AutoFill			Methods related to autofilling in item names when drops
--					Occur		
--
-- Bidding			Implements the automatted bidding feature for WebDKP. 
--					Contains code for the bidding GUI as well as handling
--					incoming bid whispers
--
-- Options			Implements a GUI for updating and changing addon options. 
--					This used to be in WebDKP but was branched to a seperate GUI
--					and file as options grew. 
------------------------------------------------------------------------

---------------------------------------------------
-- MEMBER VARIABLES
---------------------------------------------------
-- Sets the range of dkp that defines tiers.
-- Example, 50 would be:
-- 0-50 = tier 0
-- 51-100 = tier 1, etc
WebDKP_TierInterval = 50;   

-- Specify what filters are turned on and off. 1 = on, 0 = off
WebDKP_Filters = {
	["Death Knight"] = 1,
	["Druid"] = 1,
	["Hunter"] = 1,
	["Mage"] = 1,
	["Rogue"] = 1,
	["Shaman"] = 1,
	["Paladin"] = 1,
	["Priest"] = 1,
	["Warrior"] = 1,
	["Warlock"] = 1,
	["Group"] = 1,
	["Guild"] = 1,
	["GuildOnline"] = 1,
	["LimitAlts"] = 0,
	["LimitAlts2"] = 0,
	["Standby1"] = 0,
	["Standby2"] = 0,
}

-- Specifies what classes compose different filter groups
WebDKP_FilterGroups = {
	["Casters"] = "Paladin Shaman Mage Warlock Priest Druid",
	["Melee"] = "Paladin Shaman Warrior Rogue Druid Hunter Death Knight",
	["Healer"] = "Shaman Paladin Priest Druid",
	["Chain"] = "Shaman Hunter",
	["Cloth"] = "Warlock Mage Priest",
	["Leather"] = "Rogue Druid",
	["Plate"] = "Warrior Paladin Death Knight"
}

-- Items to ignore for auto dkp. If they are picked up, auto dkp will not show
WebDKP_IgnoreItems = {
	"Badge of Justice",
	"Void Crystal",
    	"Emblem of Heroism",
    	"Emblem of Valor",
    	"Emblem of Conquest",
	"Emblem of Triumph",
    	"Abyss Crystal",
    	"Stone Keeper's Shard",
	"Sack of Frosty Treasures",
}


-- The dkp table itself (This is loaded from the saved variables file)
-- Its structure is:
-- ["playerName"] = {
--		["dkp"] = 100,
--		["class"] = "ClassName",
--		["Selected"] = true/ false if they are selected in the gui
-- }
WebDKP_DkpTable = {};


-- Loot Table Init.
WebDKP_Loot = {};


-- The list of users for synching Added by Zevious
-- Its structure is:
-- ["playerName"] = {
-- }
WebDKP_Synch_Users = {};


WebDKP_RaidInfo = {};

-- The list of user defined ignore items Added by Zevious
-- Its structure is:
-- ["Item Name"] = {
-- }
WebDKP_Ignored_Items = {};

-- Holds the list of users tables on the site. This is used for those guilds
-- who have multiple dkp tables for 1 guild. 
-- When there are multiple table names in this list a drop down will appear 
-- in the addon so a user can select which table they want to award dkp to
-- Its structure is: 
-- ["tableName"] = { 
--		["id"] = 1 (this is the tableid of the table on the webdkp site)
-- }
WebDKP_Tables = {};
selectedTableid = 1;


-- The dkp table that will be shown. This is filled programmatically
-- based on running through the big dkp table applying the selected filters
WebDKP_DkpTableToShow = {}; 

-- Keeps track of the current players in the group. This is filled programmatically
-- and is filled with Raid data if the player is in a raid, or party data if the
-- player is in a party. It is used to apply the 'Group' filter
WebDKP_PlayersInGroup = {};

-- Keeps track of the sorting options. 
-- Curr = current columen being sorted
-- Way = asc or desc order. 0 = desc. 1 = asc
WebDKP_LogSort = {
	["curr"] = 3,
	["way"] = 1, -- Desc
	["curr2"] = 2,
	["way2"] = 1,
	["curr3"] = 1,
	["way3"] = 2,
	["curr4"] = 4,
	["way4"] = 1
};

-- Additional user options and information that must be saved across reloads. 
-- Note, that this data is only here for quick reference. Many of these values are initalized
-- to default values in the Options.lua file.

WebDKP_Options = {
	["EPGPEnabled"] = 0,				-- Enables EPGP
	["AutoGive"] = 0,			-- Auto Gives Items when awarded! Caution!
	["AwardBossDKP"] = 0,			-- Award DKP on Boss Kill. 0 = disabled. 1 = enabled
	["AwardBossDKP10"] = 0,			-- If Checked 10 Man Northrend Raid bosses auto award
	["AwardBossDKP25"] = 0,			-- If Checked 25 man Northrend Raid bosses auto award
	["BossDKPValue"] = 0,			-- Amount awarded for Boss Kills
	["ZeroSumStandby"] = 1,			-- Enable Zerosum awards for standby players
	["TimedStandby"] = 1,			-- Enable Zerosum awards for standby players
	["AutofillEnabled"] = 1,		-- auto fill data. 0 = disabled. 1 = enabled. 
	["AutofillThreshold"] = 3,		-- What level of items should be picked up by auto fill. -1 = Gray, 4 = Orange
	["AutoAwardEnabled"] = 1,		-- Whether dkp awards should be recorded automatically if all data can be auto filled (user is still prompted)
	["SelectedTableId"] = 1,		-- The last table that was being looked at
	["MiniMapButtonAngle"] = 1,
	["BidAnnounceRaid"] = 0,		-- Announces when bids start / stop in raid warning
	["BidConfirmPopup"] = 1,		-- Displays a popup when a winning bid is determined so that the user can tweak how much to award
	["BidAllowNegativeBids"] = 0,		-- Whether or not to allow people to bid more dkp than they have
	["BidFixedBidding"] = 0,		-- Whether fixed bidding is enabled. With fixed bidding users say !need instead of bidding a specific amount.DKP is deducted from a loot table
	["BidNotifyLowBids"] = 0,		-- Tells people when they have bid lower than the highest bid so far
	["TimedAwardRepeat"] = 1,		-- Whether timed awards should repeat after they have finished
	["TimedAwardInProgress"] = false,	-- Whether a timed award is in progress (0 = no, 1 = yes)
	["TimedAwardTimer"] = 0,		-- The current timer for a timed award (seconds). If a timed award is in progress and this reaches 0 an award must be given
	["TimedAwardTotalTime"] = 5,		-- How many minutes the timer started at.
	["TimedAwardDkp"] = 0,			-- How much DKP should be awarded for a timed award
	["SynchPassword"] = "",			-- Required password to synchronize.
	["EnableSynch"] = 0,			-- Enable or Disable The Synchronization
	["SynchFrom"] = "",			-- Player to Synchronize From
	["Decay"] = 1,				-- Added for Decay
	["TurnBase"] = 1,			-- Added for DKP Turn base where you bid all and lose all.
	["SilentBidding"] = 1,			-- Added to enable silent bidding so the countdown announcements dont say whos winning or the dkp value thats highest.
	["BidandRoll"] = 0,			-- Added to enable monitoring bids and rolls at the same time.
	["FiftyGreed"] = 0,			-- Added to enable custom !greed percentages
	["NeedAll"] = 0,			-- Added to enable custom !need percentages
	["DisableBid"] = 0,			-- Added to allow the user to disable !bid to force people to use !main or !off
	["TimedAwardMiniTimer"] = 0,		-- 1 = mini timer is shown, 0 = mini timer is hidden
 	["Enabled"] = 1,                	-- 1 = On, 0 = Off added by cather (Bronzebeard)
	["Announcements"] = 0,                	-- 1 = On, 0 = Off added by Zevious (Bronzebeard)
	["EditStartAnnounce"] = "",		-- The custom start bid message - Zev
	["EditDuringAnnounce"] = "",		-- The custom during bid message - Zev
	["EditEndAnnounce"] = "",		-- The custom end bid message - Zev
	["EditSRollAnnounce"] = "",		-- The custom start roll message - Zev
	["EditRollAnnounce"] = "",		-- The custom roll message - Zev
	["EditERollAnnounce"] = "",		-- The custom end roll message - Zev
	["InGroup"] = 1,			-- Only displays raid attendance for chars in the group
	["LimitGuild"] = 1,			-- Display people only in your guild
	["LimitGuildOnline"] = 1,		-- Limit the list to only online guild members
	["LimitAlts"] = 0,			-- Exclude all alternate players
	["LimitAlts2"] = 0,			-- Exclude alt players not in the current group
	["Standby1"] = 0,			-- Includes alts in the listing
	["Standby2"] = 0,			-- Only shows standby players
	["StartBid"] = 0,			-- Value you want all auctions to start at
	["Time"] = 0,				-- How long you want a bid or roll to last
	["AltClick"] = 1,			-- Option to disable the Alt+Click to bring up the bidding window.
	["IgnWhispers"] = 0,			-- Option to ignore whispers from people outside of the raid/party added to prevent spamming.
	["GreedDKP"] = "50",			-- Default Option for !greed is 50%
	["NeedDKP"] = "100",			-- Default Option for !need is 100%
	["dkpCap"] = 0,				-- Enable or disable the DKP Cap
	["dkpCapLimit"] = 0,			-- Default value is blank
	["ItemLevelEquation"] = 0,		-- Enable or Disable the Item Level Multiplier
	["ItemLevelMult"] = ".01",		-- Default Item Level Multiplier
	["SlotLocMult"] = 0,			-- Slot Location Mult Enabled
	["ItemLocMult"] = {			-- Enable Slot Location Multiplier
		["head"] = "1",
		["neck"] = "1",
		["shoulders"] = "1",
		["back"] = "1",
		["chest"] = "1",
		["wrist"] = "1",
		["hands"] = "1",
		["waist"] = "1",
		["legs"] = "1",
		["feet"] = "1",
		["fingers"] = "1",
		["trinkets"] = "1",
		["mainhand"] = "1",
		["shield"] = "1",
		["ranged"] = "1",
		["relic"] = "1",
		["idol"] = "1",
		["twohand"] = "1",
		["onehanders"] = "1",
		["heldoffhand"] = "1",
		["offhandweapon"] = "1",
	},			
}

-- User options that are syncronized with the website
WebDKP_WebOptions = {			
	["ZeroSumEnabled"] = 0,			-- Whether or not to use ZeroSum DKP settings
	["CombineAlts"] = 0	,			-- Whether or not alts and mains are combined and share dkp
}

WebDKP_Alts = {};					-- Holds list of alts in the game. Structure is: 
									-- ["AltName"] = "Main Name", 

local WebDKP_Loaded = false;		-- used to flag whether the addon has been loaded already (wow 2.0 seems to load it twice?)

---------------------------------------------------
-- INITILIZATION
---------------------------------------------------
-- ================================
-- On load setup the slash event that will toggle the gui
-- and register for some extra events
-- ================================
function WebDKP_OnLoad(self)
	--WebDKP_Print("OnLoad");
	local this = self;

	if ( WebDKP_Loaded == false ) then
		WebDKP_Loaded = true;
		--register the slash event
		SlashCmdList["WEBDKP"] = WebDKP_ToggleGUI;
		SLASH_WEBDKP1 = "/webdkp";					-- Toggles the WEBDKP DKP Table
		SLASH_SYNCH1 = "/synch";					-- Starts the synching 
		SlashCmdList["SYNCH"] = WebDKP_Start_Synch;
							
			
		--register extra events
		this:RegisterEvent("CHAT_MSG_SYSTEM");				-- So we can monitor for /random rolls
		this:RegisterEvent("COMBAT_LOG_EVENT_UNFILTERED");		-- So we can monitor the combat log
		this:RegisterEvent("CHAT_MSG_MONSTER_YELL");			-- So we can monitor for some Ulduar boss kills.
		this:RegisterEvent("GROUP_ROSTER_UPDATE");			-- so we can handle party changes
		this:RegisterEvent("ITEM_TEXT_READY");
		this:RegisterEvent("ADDON_LOADED");	
		this:RegisterEvent("CHAT_MSG_WHISPER");				-- chat handles so we can look for webdkp commands like !bid			
		this:RegisterEvent("CHAT_MSG_LOOT");
		this:RegisterEvent("CHAT_MSG_PARTY");
		this:RegisterEvent("CHAT_MSG_RAID");
		this:RegisterEvent("CHAT_MSG_RAID_LEADER");
		this:RegisterEvent("CHAT_MSG_RAID_WARNING");
		this:RegisterEvent("ADDON_ACTION_FORBIDDEN");			--debugging - Blizzards new code likes to blame us for things we don't do :(
		-- this:RegisterEvent("UNIT_DIED");
		this:RegisterEvent("CHAT_MSG_ADDON");
		WebDKP_OnEnable();
	end


end

-- ================================
-- Called when the addon is enabled. 
-- Takes care of basic startup tasks: hide certain forms, 
-- get the people currently in the group, etc.
-- ================================
function WebDKP_OnEnable()

	_G["RivendareFlag"] = 0;
	_G["LadyFlag"] = 0;
	_G["SirFlag"] = 0;
	_G["KorFlag"] = 0;
	_G["AwardedReason"] = "";
	_G["AwardedDate"] = "";
	_G["LineLocation"] = "";

	-- WebDKP_Frame:Hide();
	_G["WebDKP_FiltersFrame"]:Show();
	_G["WebDKP_AwardDKP_Frame"]:Hide();
	_G["WebDKP_AwardItem_Frame"]:Hide();
	_G["WebDKP_Standby_Frame"]:Hide();
	
	WebDKP_UpdatePlayersInGroup();
	WebDKP_UpdateTableToShow();
	
	-- place a hook on the chat frame so we can filter out our whispers
	WebDKP_Register_WhisperHook();
	
	-- place a hook on item shift+clicks so we can get item details
	hooksecurefunc("SetItemRef",WebDKP_ItemChatClick);
	hooksecurefunc("ChatFrame_OnHyperlinkShow",WebDKP_ItemChatShiftClick);
	hooksecurefunc("HandleModifiedItemClick",  WebDKP_HandleModifiedItemClick);

		
end

-- ================================
-- Invoked when we recieve one of the requested events. 
-- Directs that event to the appropriate part of the addon
-- ================================
-- Had to break up the event handler so we still do the important stuff like load the addon and not any of the loot handling
-- Cather (Bronzebeard)
function WebDKP_OnEvent(self,event, ...)
	local arg1, arg2, arg3, arg4, arg5, arg6, arg7, arg8, arg9 = ...;
	if(event=="GROUP_ROSTER_CHANGED") then
		WebDKP_GROUP_ROSTER_CHANGED(arg1);
	elseif(event=="ADDON_LOADED") then
		WebDKP_ADDON_LOADED(arg1);
	elseif(event=="CHAT_MSG_SYSTEM") then
		Webdkp_Sys_Msg_Received(arg1);
	elseif(event == "CHAT_MSG_ADDON") then
        --SendChatMessage("WebDKP: ".."Inside addon".." "..arg1.." "..arg2, "WHISPER", nil, "Webdkp")
		if C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKPA Undo") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKPA Undo")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKPA LogAdd") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKPA LogAdd")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKPA LogDel") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKPA LogDel")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKPA Item") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKPA Item")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKP MultStart") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKP Mult Start")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKP MultName") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKP MultName")
		elseif C_ChatInfo.IsAddonMessagePrefixRegistered("!WDKP Mult End") == false then
			C_ChatInfo.RegisterAddonMessagePrefix("!WDKP Mult End")
		end
		WebDKP_AddonChan_Processing(arg1,arg2,arg3,arg4);
   	end
 
	if(WebDKP_Options["Enabled"] == 1) then
        if(event=="CHAT_MSG_WHISPER") then
            WebDKP_CHAT_MSG_WHISPER(arg1,arg2);
        elseif(event=="CHAT_MSG_PARTY" or event=="CHAT_MSG_RAID" or event=="CHAT_MSG_RAID_LEADER" or event=="CHAT_MSG_RAID_WARNING") then
            WebDKP_CHAT_MSG_PARTY_RAID(arg1,arg2);
        elseif(event=="CHAT_MSG_LOOT") then
            WebDKP_Loot_Taken(arg1,arg2);
        elseif(event=="ADDON_ACTION_FORBIDDEN") then
            WebDKP_Print(arg1.."  "..arg2);
        elseif(event=="COMBAT_LOG_EVENT_UNFILTERED") then
            WebDKP_BossAward_PerformAward(arg1,arg2,arg9);
		elseif(event =="CHAT_MSG_MONSTER_YELL") then
			WebDKP_BossAward_PerformAward(arg1,arg2,arg9);
        end
    end
end



-- ================================
-- Invoked when addon finishes loading data from the saved variables file. 
-- Should parse the players options and update the gui.
-- ================================
function WebDKP_ADDON_LOADED(arg1)

  if arg1 == "WebDKP" then
	if( WebDKP_DkpTable == nil) then
		WebDKP_DkpTable = {};
	end

	if( WebDKP_Loot == nil) then
		WebDKP_Loot = {};
	end

	if( WebDKP_Synch_Users == nil) then
		WebDKP_Synch_Users = {};
	end
	if( WebDKP_Ignored_Items == nil) then
		WebDKP_Ignored_Items = {};
	end
	if (WebDKP_RaidInfo == nil) then
		WebDKP_RaidInfo = {};
	end


	-- Reset everyone's standby status to 0
	WebDKP_Standby_Reset();

	
	-- Combine WebDKP_IgnoreItems with WebDKP_Ignored_Items that the user adds Added by Zevious
	-- Loops through checking to see if the new ignore items already exist and if so, don't add it again.
	numEntries1 = getn(WebDKP_IgnoreItems);
	numEntries2 = getn(WebDKP_Ignored_Items);
	for ii=1, numEntries2, 1 do
		itemfoundflag = 0;
		newitemignored = WebDKP_Ignored_Items[ii];

		for i=1, numEntries1, 1 do
			
			ItemIgnoredName = WebDKP_IgnoreItems[i];
			if ItemIgnoredName == newitemignored then
			
				itemfoundflag = 1;
			end
		end
		if itemfoundflag == 0 then
			numEntries1 = numEntries1 + 1;
			tinsert(WebDKP_IgnoreItems,newitemignored);
		end
	end

	-- load up the last loot table that was being viewed
	WebDKP_Frame.selectedTableid = WebDKP_Options["SelectedTableId"];

	WebDKP_Options_Init(); -- load up the options to the options gui
	
	WebDKP_UpdateTableToShow(); --update who is in the table
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	WebDKP_UpdateLogTable();   -- update the log file
	
	-- set the mini map position

	WebDKP_MinimapButton_SetPositionAngle(WebDKP_Options["MiniMapButtonAngle"]);

  end
end





-- ================================
-- Called on shutdown. Does nothing
-- ================================
function WebDKP_OnDisable()
    
end


---------------------------------------------------
-- EVENT HANDLERS (Party changed / gui toggled / etc.)
---------------------------------------------------

-- ================================
-- Called by slash command. Toggles gui. 
-- ================================
function WebDKP_ToggleGUI(msg, editbox)
	
	local continue_flag = 0;
	
	local webdkp_command = msg;
	if ((string.find(webdkp_command,"ignore")==1) and (string.find(webdkp_command,"list")==8)) then
	 -- List all items
		local toplayer = UnitName("player");
		numEntries = getn(WebDKP_Ignored_Items);
		SendChatMessage("WebDKP: User Defined Ignored Items:", "WHISPER", nil, toplayer);
		for i=1, numEntries, 1 do
			itemignored = WebDKP_Ignored_Items[i];
			SendChatMessage("WebDKP: Item Ignored ="..itemignored, "WHISPER", nil, toplayer);
		end	
				
	end

	-- Add or remove an item from the ignore items Added by Zevious
	if ( (string.find(webdkp_command,"ignore")==1) and (string.find(webdkp_command,"add")==8) ) then

		nameofitem = string.gsub(webdkp_command, "ignore add ", "")

		if nameofitem ~= nil and nameofitem ~= "" then
			tinsert(WebDKP_IgnoreItems,nameofitem);
			tinsert(WebDKP_Ignored_Items,nameofitem);
		end

	end

	-- Delete an item from the ignore items Added by Zevious
	if ((string.find(webdkp_command,"ignore")==1) and (string.find(webdkp_command,"del")==8)) then

		nameofitem = string.gsub(webdkp_command, "ignore del ", "")
		if nameofitem ~= nil and nameofitem ~= "" then
			
			numEntries1 = getn(WebDKP_IgnoreItems);
			for i=1, numEntries1, 1 do
			
				local itemremovename = WebDKP_IgnoreItems[i];
			
				if itemremovename == nameofitem then
					tremove(WebDKP_IgnoreItems, i);
				end
			end
			numEntries2 = getn(WebDKP_Ignored_Items);
			for i=1, numEntries2, 1 do
			
				local itemremovename = WebDKP_Ignored_Items[i];
			
				if itemremovename == nameofitem then
					tremove(WebDKP_Ignored_Items, i);
				end
			end
			
		end
	end

	-- Toggle the DKP Table GUI
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"table")==6) ) then

		if ( WebDKP_Frame:IsShown() ) then
			WebDKP_Frame:Hide();
		else
			
			if WebDKP_Options["EPGPEnabled"] == 1 then
				WebDKP_Frame:Show();
				-- Hide Standard DKP Buttons
				WebDKP_FrameScrollFrame:Hide();
				WebDKP_FrameName:Hide();
				WebDKP_FrameClass:Hide();
				WebDKP_FrameDKP:Hide();
				WebDKP_FrameGuildRank:Hide();
				-- Hide all of the DKP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLine" .. i);
					line:Hide();
				end
				-- Show EPGP Buttons
				WebDKP_FrameScrollFrameEPGP:Show();
				WebDKP_FrameNameEPGP:Show();
				WebDKP_FrameClassEPGP:Show();
				WebDKP_FrameEP:Show();
				WebDKP_FrameGP:Show();
				WebDKP_FrameLP:Show();
				WebDKP_FrameGuildRankEPGP:Show();
				-- Show all of the EPGP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLineEPGP" .. i);
					line:Show();
				end

			else
				WebDKP_Frame:Show();
				-- Show Standard DKP Buttons
				WebDKP_FrameScrollFrameEPGP:Hide();
				WebDKP_FrameScrollFrame:Show();
				WebDKP_FrameName:Show();
				WebDKP_FrameClass:Show();
				WebDKP_FrameDKP:Show();
				WebDKP_FrameGuildRank:Show();
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLine" .. i);
					line:Show();
				end

				-- Hide EPGP Buttons
				WebDKP_FrameNameEPGP:Hide();
				WebDKP_FrameClassEPGP:Hide();
				WebDKP_FrameEP:Hide();
				WebDKP_FrameGP:Hide();
				WebDKP_FrameLP:Hide();
				WebDKP_FrameGuildRankEPGP:Hide();
				-- Hide all of the EPGP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLineEPGP" .. i);
					line:Hide();
				end


			end	
			WebDKP_Tables_DropDown_OnLoad();
		end
	end

	-- Toggle the Bidding GUI
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"bidding")==6) ) then

		if ( WebDKP_BidFrame:IsShown() ) then
			WebDKP_BidFrame:Hide();
		else
			WebDKP_BidFrame:Show();	
		end
	end

	-- Toggle the Synch Settings
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"synch")==6) ) then

		if ( WebDKP_SynchFrame:IsShown() ) then
			WebDKP_SynchFrame:Hide();
		else
			WebDKP_SynchFrame:Show();	
		end
	end

	-- Toggle the Options Settings
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"options")==6) ) then

		if ( WebDKP_OptionsFrame:IsShown() ) then
			WebDKP_OptionsFrame:Hide();
		else
			WebDKP_OptionsFrame:Show();	
		end
	end


	-- Toggle the Timed Award Settings
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"timed")==6) ) then

		if ( WebDKP_TimedAwardFrame:IsShown() ) then
			WebDKP_TimedAwardFrame:Hide();
		else
			WebDKP_TimedAwardFrame:Show();	
		end
	end


	-- Toggle the Help Settings
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"help")==6) ) then

		if ( WebDKP_HelpFrame:IsShown() ) then
			WebDKP_HelpFrame:Hide();
		else
			WebDKP_HelpFrame:Show();	
		end
	end

	
	-- Toggle the Loot and Award Log
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"log")==6) ) then

		if ( WebDKP_LogFrame:IsShown() ) then
			WebDKP_LogFrame:Hide();
		else
			WebDKP_LogFrame:Show();	
		end
	end

	-- Toggle the Raid Log
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"raidlog")==6) ) then

		if ( WebDKP_RaidInfoFrame:IsShown() ) then
			WebDKP_RaidInfoFrame:Hide();
		else
			WebDKP_RaidInfoFrame:Show();	
		end
	end

	-- Toggle the Character Raid Log
	if ( (string.find(webdkp_command,"show")==1) and (string.find(webdkp_command,"charlog")==6) ) then

		if ( WebDKP_CharRaidInfoFrame:IsShown() ) then
			WebDKP_CharRaidInfoFrame:Hide();
		else
			WebDKP_CharRaidInfoFrame:Show();	
		end
	end

	-- Starts a Raid
	if ( (string.find(webdkp_command,"start")==1) and (string.find(webdkp_command,"raid")==7) ) then
		WebDKP_RaidStart();
	end
	

	-- Ends a Raid
	if ( (string.find(webdkp_command,"end")==1) and (string.find(webdkp_command,"raid")==5) ) then
		WebDKP_RaidEnd();
	end


	
end
-- =============================================
-- Called by the addon to display the DKP Table
-- =============================================
function WebDKP_Table_GUI()
	WebDKP_Refresh()
		
		if ( WebDKP_Frame:IsShown() ) then
			WebDKP_Frame:Hide();
		else
			
			if WebDKP_Options["EPGPEnabled"] == 1 then
				WebDKP_Frame:Show();
				-- Hide Standard DKP Buttons
				WebDKP_FrameScrollFrame:Hide();
				WebDKP_FrameName:Hide();
				WebDKP_FrameClass:Hide();
				WebDKP_FrameDKP:Hide();
				WebDKP_FrameGuildRank:Hide();
				-- Hide all of the DKP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLine" .. i);
					line:Hide();
				end
				-- Show EPGP Buttons
				WebDKP_FrameScrollFrameEPGP:Show();
				WebDKP_FrameNameEPGP:Show();
				WebDKP_FrameClassEPGP:Show();
				WebDKP_FrameEP:Show();
				WebDKP_FrameGP:Show();
				WebDKP_FrameLP:Show();
				WebDKP_FrameGuildRankEPGP:Show();
				-- Show all of the EPGP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLineEPGP" .. i);
					line:Show();
				end

			else
				WebDKP_Frame:Show();
				-- Show Standard DKP Buttons
				WebDKP_FrameScrollFrame:Show();
				WebDKP_FrameName:Show();
				WebDKP_FrameClass:Show();
				WebDKP_FrameDKP:Show();
				WebDKP_FrameGuildRank:Show();
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLine" .. i);
					line:Show();
				end
				-- Hide EPGP Buttons
				WebDKP_FrameScrollFrameEPGP:Hide();
				WebDKP_FrameNameEPGP:Hide();
				WebDKP_FrameClassEPGP:Hide();
				WebDKP_FrameEP:Hide();
				WebDKP_FrameGP:Hide();
				WebDKP_FrameLP:Hide();
				WebDKP_FrameGuildRankEPGP:Hide();
				-- Hide all of the EPGP lines
				for i=1, 25, 1 do
					local line = getglobal("WebDKP_FrameLineEPGP" .. i);
					line:Hide();
				end

			end
				
			WebDKP_Tables_DropDown_OnLoad();
		end	

		-- WebDKP_Bid_ToggleUI();
end


-- ================================
-- Handles the master loot list being opened 
-- ================================
function WebDKP_OPEN_MASTER_LOOT_LIST()
    -- we don't do anything here because the addon should be
    -- usable by people who are not the master looter. 
    -- If someone wants to tweak this, however, this would be
    -- the area to start. 
end

-- ================================
-- Called when the party / raid configuration changes. 
-- Causes the list of current group memebers to be refreshed
-- so that filters will be ok
-- ================================
function WebDKP_GROUP_ROSTER_CHANGED()
	WebDKP_UpdatePlayersInGroup();
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

-- ================================
-- Handles an incoming whisper. Directs it to the modules
-- who are interested in it.
-- ================================
function WebDKP_CHAT_MSG_WHISPER(arg1,arg2)
local check_ignored = WebDKP_Options["IgnWhispers"];
local ignoreflag = 0;

	-- Check to see if we are ignoring whispers from those outside of the party/raid
	if check_ignored == 1 then
		local name = arg2;
		if WebDKP_PlayerInGroup(name) == false then
			ignoreflag = 1;
		end
	end
	if ignoreflag == 0 then
		WebDKP_WhisperDKP_Event(arg1,arg2);
		WebDKP_Bid_Event(arg1,arg2);
		WebDKP_Synch_Processing(arg1,arg2);					-- Added by Zevious for Synching
	end
end


-- ================================
-- Event handler for all party and raid
-- chat messages. 
-- ================================
function WebDKP_CHAT_MSG_PARTY_RAID(arg1,arg2)
	WebDKP_Bid_Event(arg1,arg2);
end

---------------------------------------------------
-- GUI EVENT HANDLERS
-- (Handle events raised by the gui and direct
--  events to the other parts of the addon)
---------------------------------------------------
-- ================================
-- Called by the refresh button. Refreshes the people displayed 
-- in your party. 
-- ================================
function WebDKP_Refresh()
	WebDKP_UpdatePlayersInGroup();
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

-- ================================
-- Called when a player clicks on different tabs. 
-- Causes certain frames to be hidden and the appropriate
-- frame to be displayed
-- ================================
function WebDKP_Tab_OnClick(self)
	local this = self;
	if ( this:GetID() == 1 ) then
		_G["WebDKP_FiltersFrame"]:Show();
		_G["WebDKP_AwardDKP_Frame"]:Hide();
		_G["WebDKP_AwardItem_Frame"]:Hide();
		_G["WebDKP_Standby_Frame"]:Hide();
	elseif ( this:GetID() == 2 ) then
		_G["WebDKP_FiltersFrame"]:Hide();
		_G["WebDKP_AwardDKP_Frame"]:Show();
		_G["WebDKP_AwardItem_Frame"]:Hide();
		_G["WebDKP_Standby_Frame"]:Hide();
	elseif (this:GetID() == 3 ) then
		_G["WebDKP_FiltersFrame"]:Hide();
		_G["WebDKP_AwardDKP_Frame"]:Hide();
		_G["WebDKP_AwardItem_Frame"]:Show();
		_G["WebDKP_Standby_Frame"]:Hide();
	elseif (this:GetID() == 4 ) then
		_G["WebDKP_FiltersFrame"]:Hide();
		_G["WebDKP_AwardDKP_Frame"]:Hide();
		_G["WebDKP_AwardItem_Frame"]:Hide();
		_G["WebDKP_Standby_Frame"]:Show();
	end 
	PlaySound(SOUNDKIT.IG_CHARACTER_INFO_TAB, "SFX");
end

-- ================================
-- Selects all players in the dkp table and updates 
-- table display
-- ================================
function WebDKP_SelectAll()
	local tableid = WebDKP_GetTableid();
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			local playerName = k; 
			local playerClass = v["class"];
			local playerDkp = v["dkp"..tableid];
			if ( playerDkp == nil ) then 
				v["dkp"..tableid] = 0;
				playerDkp = 0;
			end

			local playerTier = floor((playerDkp-1)/WebDKP_TierInterval);
			if (WebDKP_ShouldDisplay(playerName, playerClass, playerDkp, playerTier)) then
				WebDKP_DkpTable[playerName]["Selected"] = true;
			else
				WebDKP_DkpTable[playerName]["Selected"] = false;
			end
		end
	end

	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

-- ================================
-- Deselect all players and update table display
-- ================================
function WebDKP_UnselectAll()
	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			local playerName = k; 
			WebDKP_DkpTable[playerName]["Selected"] = false;
		end
	end
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

---------------------------------------------------
-- FILTERS
-- The following methods are all related to filters and filter groups as 
-- seen on the filters tab. 
-- A filter is an individual class that affects what people are displayed in the table
-- (Example: Hunter, Mage, Warrior)
-- A filter group is a general category that is composed up of other filters (Example: Caster, Melee, etc)
-- The following methods must handle the user checking or unchecking class filters as well as filter 
-- groups. 
---------------------------------------------------

-- ================================
-- Checks whether any of the filter groups (Caster, Melee, etc.) 
-- should be either checked or unchecked based on the current state of the
-- checked classes. Example - Casters should only be checked if druid, mage, etc are
-- currently on. This method should be called whenever one of the _class_ filters change
-- ================================
function WebDKP_UpdateFilterGroupsCheckedState() 
	-- run through each of the filter groups
	for key, value in pairs(WebDKP_FilterGroups) do
		if ( value ~= nil ) then
			
			local checkbox = _G["WebDKP_FiltersFrameClass"..key];
			if (checkbox ~= nil ) then
				-- if all its filter are on, go ahead and check it, otherwise uncheck it
				local allFiltersOn = WebDKP_AllFiltersOn(value);
				if ( allFiltersOn == true ) then
					checkbox:SetChecked(1);
				else
					checkbox:SetChecked(0);
				end
			end
		end
	end
end

-- ================================
-- Runs through the list of all currently checked off _filter groups_
-- and makes sure all of the ones that are checked have their appropriate
-- classes displayed. This is called whenever a user unchecks on of the other
-- group filters to make sure that it doesn't interfere with other group filters that
-- might be checked
-- ================================
function WebDKP_ReinforceCheckedFilterGroups()
	-- run through each of the filter groups
	for key, value in pairs(WebDKP_FilterGroups) do
		if ( value ~= nil ) then
			local checkbox = _G["WebDKP_FiltersFrameClass"..key];
			if (checkbox ~= nil ) then
				local checked = checkbox:GetChecked();
				if ( checked == 1 ) then
					WebDKP_SetFilterGroupState(value,1);
				end
			end
		end
	end
end

-- ================================
-- Called when the user clicks on a filter checkbox. 
-- Changes the filter setting and updates table
-- ================================
function WebDKP_ToggleFilter(filterName)
	WebDKP_Filters[filterName] = abs(WebDKP_Filters[filterName]-1);
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	WebDKP_UpdateFilterGroupsCheckedState();
end

-- ================================
-- Called when user clicks on 'check all'
-- Sets all filters to on and updates table display
-- ================================
function WebDKP_CheckAllFilters()
	WebDKP_SetFilterState("Druid",1);
	WebDKP_SetFilterState("Hunter",1);
	WebDKP_SetFilterState("Mage",1);
	WebDKP_SetFilterState("Rogue",1);
	WebDKP_SetFilterState("Shaman",1);
	WebDKP_SetFilterState("Paladin",1);
	WebDKP_SetFilterState("Priest",1);
	WebDKP_SetFilterState("Warrior",1);
	WebDKP_SetFilterState("Warlock",1);
	WebDKP_SetFilterState("Death Knight",1);
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	WebDKP_UpdateFilterGroupsCheckedState();
end

-- ================================
-- Called when user clicks on 'uncheck all'
-- Sets all filters to off and updates table display
-- ================================
function WebDKP_UncheckAllFilters()
	WebDKP_SetFilterState("Druid",0);
	WebDKP_SetFilterState("Hunter",0);
	WebDKP_SetFilterState("Mage",0);
	WebDKP_SetFilterState("Rogue",0);
	WebDKP_SetFilterState("Shaman",0);
	WebDKP_SetFilterState("Paladin",0);
	WebDKP_SetFilterState("Priest",0);
	WebDKP_SetFilterState("Warrior",0);
	WebDKP_SetFilterState("Warlock",0);
	WebDKP_SetFilterState("Death Knight",0);
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	WebDKP_UpdateFilterGroupsCheckedState();
end

-- ================================
-- An event handler for clicking on the filter buttons that cover
-- groups of classes instead of one class in particular. (For example, Casters, Healers, etc).
-- When this is called it must toggle the entire group from either being on or off. 
-- GroupCheckboxName = the name of the group checkbox in the gui. (Example: "Casters" "Healers")
-- ================================
function WebDKP_ToggleFilterGroup(groupCheckboxName) 
	local filters = WebDKP_FilterGroups[groupCheckboxName]; -- get what filters this group is tied to
	-- look at its checkbox to determine if we are toggling on or off
	local checkbox = _G["WebDKP_FiltersFrameClass"..groupCheckboxName];
	local checked = checkbox:GetChecked();
	if ( checked == 1 ) then
		WebDKP_SetFilterGroupState(filters, 1);
	else
		WebDKP_SetFilterGroupState(filters, 0);
	end
	--update the table to show the new changes
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
	-- update what filter groups are checked as a result of these changes
	WebDKP_ReinforceCheckedFilterGroups();
	--WebDKP_UpdateFilterGroupsCheckedState();
end

-- ================================
-- A helper method that returns true if all of the passed filters are currently
-- on. Filters parameter takes the form of a string with all the class filter names
-- combined. Example ("Druid Hunter Mage")
-- ================================
function WebDKP_AllFiltersOn(filters) 
	
	-- find out what filters were passed by doing string searches
	local filter = {};  
	filter["Druid"] = string.find(string.lower(filters), "druid");
	filter["Hunter"] = string.find(string.lower(filters), "hunter");
	filter["Mage"]= string.find(string.lower(filters), "mage");
	filter["Rogue"] = string.find(string.lower(filters), "rogue");
	filter["Shaman"] = string.find(string.lower(filters), "shaman");
	filter["Paladin"] = string.find(string.lower(filters), "paladin");
	filter["Priest"] = string.find(string.lower(filters), "priest");
	filter["Warrior"] = string.find(string.lower(filters), "warrior");
	filter["Warlock"] = string.find(string.lower(filters), "warlock");
	filter["Death Knight"] = string.find(string.lower(filters), "death knight");
	-- run through all of these filters and see if they are all turned on
	local allTurnedOn = true; -- assume yes until proven otherwise.
	for key, value in pairs(filter) do
		if ( value ~= nil ) then
			if ( WebDKP_Filters[key] == 0 ) then
				allTurnedOn = false;
			end
		end
	end
	return allTurnedOn;
end

-- ================================
-- Updates the filter state for many filters at once.
-- Filters is a simple string that contains all the classes to set to the new
-- state. (Example:  HunterMageRogue) while newState specifies whether to check 
-- or uncheck that filter (1 = on, 0 = off)
-- ================================
function WebDKP_SetFilterGroupState(filters,newState)
	-- find out what filters were passed by doing string searches
	local filter = {};  
	filter["Druid"] = string.find(string.lower(filters), "druid");
	filter["Hunter"] = string.find(string.lower(filters), "hunter");
	filter["Mage"]= string.find(string.lower(filters), "mage");
	filter["Rogue"] = string.find(string.lower(filters), "rogue");
	filter["Shaman"] = string.find(string.lower(filters), "shaman");
	filter["Paladin"] = string.find(string.lower(filters), "paladin");
	filter["Priest"] = string.find(string.lower(filters), "priest");
	filter["Warrior"] = string.find(string.lower(filters), "warrior");
	filter["Warlock"] = string.find(string.lower(filters), "warlock");
	filter["Death Knight"] = string.find(string.lower(filters), "death knight");
	-- for any of the filters passed, set their new state
	for key, value in pairs(filter) do
		if ( value ~= nil ) then
			WebDKP_SetFilterState(key, newState);
		end
	end
end

-- ================================
-- Small helper method for filters - updates
-- checkbox state and updates filter setting in data structure
-- ================================
function WebDKP_SetFilterState(filter,newState)
	-- make sure there are no spaces in the filter name. This allows us to map 
	-- it to a specific xml element name
	xmlName = string.gsub(filter," ","_");
	
	local checkBox = _G["WebDKP_FiltersFrameClass"..xmlName];
	checkBox:SetChecked(newState);
	WebDKP_Filters[filter] = newState;
end




---------------------------------------------------
-- TABLE GUI EVENTS
-- The following methods are related to GUI events generated by the dkp table. 
-- This includes mouse overs for the rows, selecting players, and sorting columns.
---------------------------------------------------

-- ================================
-- Called when a player clicks on a column header on the table
-- Changes the sorting options / asc&desc. 
-- Causes the table display to be refreshed afterwards
-- so the player instantly sees changes
-- ================================
function WebDPK2_SortBy(id)
	if id == 5 then
		id = 6;
	end
	if ( WebDKP_LogSort["curr"] == id ) then
		WebDKP_LogSort["way"] = abs(WebDKP_LogSort["way"]-1);		-- toggles between 1 and 0
	else
		WebDKP_LogSort["curr"] = id;
		if( id == 1) then
			WebDKP_LogSort["way"] = 0;
		elseif ( id == 2 ) then
			WebDKP_LogSort["way"] = 0;
		elseif ( id == 3 ) then
			WebDKP_LogSort["way"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		else
			WebDKP_LogSort["way"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		end
		
	end
	-- update table so we can see sorting changes
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

-- ================================
-- Called when mouse goes over a dkp line entry. 
-- If that player is not selected causes that row
-- to become 'highlighted'
-- ================================
function WebDKP_HandleMouseOver(self)
	local this = self;
	local playerName = _G[this:GetName().."Name"]:GetText();
	if(playerName ~= nil and not WebDKP_DkpTable[playerName]["Selected"] ) then
		_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
	end
end

-- ================================
-- Called when mouse goes over a log line entry. 
-- If that player is not selected causes that row
-- to become 'highlighted'
-- ================================
function WebDKP_HandleMouseOverLog(self)
	local this = self;
	if WebDKP_Log ~= nil then
		local playerdate = _G[this:GetName().."Date"]:GetText();
		local playerreason = _G[this:GetName().."Reason"]:GetText();
		local _,itemName,itemLink = WebDKP_GetItemInfo(playerreason);
		playerreason = itemName;
		if( not WebDKP_Log[playerreason.." "..playerdate]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
		end
	end
end

-- ================================
-- Called when a mouse leaves a log line entry. 
-- If that player is not selected, causes that row
-- to return to normal (none highlighted)
-- ================================
function WebDKP_HandleMouseLeaveLog(self)
	local this = self;
	if WebDKP_Log ~= nil then
		local playerdate = _G[this:GetName().."Date"]:GetText();
		local playerreason = _G[this:GetName().."Reason"]:GetText();
		local _,itemName,itemLink = WebDKP_GetItemInfo(playerreason);
		playerreason = itemName;
		if( not WebDKP_Log[playerreason.." "..playerdate]["selected"] ) then
			_G[this:GetName() .. "Background"]:SetVertexColor(0, 0, 0, 0);
		end
	end
end

-- ================================
-- Called when a mouse leaves a dkp line entry. 
-- If that player is not selected, causes that row
-- to return to normal (none highlighted)
-- ================================
function WebDKP_HandleMouseLeave(self)
	local this = self;
	local playerName = _G[this:GetName().."Name"]:GetText();
	if(playerName ~= nil and not WebDKP_DkpTable[playerName]["Selected"] ) then
		_G[this:GetName() .. "Background"]:SetVertexColor(0, 0, 0, 0);
	end
end

-- ================================
-- Called when the user clicks on a player entry. Causes 
-- that entry to either become selected or normal
-- and updates the dkp table with the change
-- ================================
function WebDKP_SelectPlayerToggle(self)
	local this = self;
	local playerName = _G[this:GetName().."Name"]:GetText();
   if playerName ~= nil then
	if(WebDKP_DkpTable[playerName]["Selected"] ) then
		WebDKP_DkpTable[playerName]["Selected"] = false;
		_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
	else
		WebDKP_DkpTable[playerName]["Selected"] = true;
		_G[this:GetName() .. "Background"]:SetVertexColor(0.1, 0.1, 0.9, 0.8);
	end
   end
end

-- ================================
-- Called when the user clicks on a log entry. Causes 
-- that entry to either become selected or normal
-- and updates the dkp table with the change
-- ================================
function WebDKP_SelectLogToggle(self)
local this = self;
if WebDKP_Log ~= nil then
	-- Set the previous selection to not selected
	if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
		WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["selected"] = false;
	end
	if _G["LineLocation"] ~= "" then
		_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
	end
	local playerdate = _G[this:GetName().."Date"]:GetText();
	local playerreason = _G[this:GetName().."Reason"]:GetText();
	
	local _,itemName,itemLink = WebDKP_GetItemInfo(playerreason);
	playerreason = itemName;
	if( WebDKP_Log[playerreason.." "..playerdate]["selected"] ) then
		WebDKP_Log[playerreason.." "..playerdate]["selected"] = false;
		_G[this:GetName() .. "Background"]:SetVertexColor(0.2, 0.2, 0.7, 0.5);
	else
		WebDKP_Log[playerreason.." "..playerdate]["selected"] = true;
		_G[this:GetName() .. "Background"]:SetVertexColor(0.1, 0.1, 0.9, 0.8);
	end
	_G["AwardedReason"] = playerreason;
	_G["AwardedDate"] = playerdate;
	_G["LineLocation"] = _G[this:GetName() .. "Background"]
	WebDKP_UpdateAwardedTable(playerreason,playerdate);
end
end

---------------------------------------------------
-- MULTIPLE TABLES DROP DOWN
-- The following methods are related to multiple tables drop down
-- that allows users to select which table they want to work with 
-- (Only exists if the user has created multiple tables on WebDkp.com)
---------------------------------------------------

-- ================================
-- Invoked when the gui loads up the drop down list of 
-- available dkp tables. 
-- ================================
function WebDKP_Tables_DropDown_OnLoad()

	UIDropDownMenu_Initialize(WebDKP_Tables_DropDown, WebDKP_Tables_DropDown_Init);
	
	local numTables = WebDKP_GetTableSize(WebDKP_Tables)
	if ( WebDKP_Tables == nil or numTables==0 or numTables==1) then
		WebDKP_Tables_DropDown:Hide();
	else
		WebDKP_Tables_DropDown:Show();
	end
end
-- ================================
-- Invoked when the drop down list of available tables
-- needs to be redrawn. Populates it with data 
-- from the tables data structure and sets up an 
-- event handler
-- ================================
function WebDKP_Tables_DropDown_Init()
	if( WebDKP_Frame.selectedTableid == nil ) then
		WebDKP_Frame.selectedTableid = 1;
	end
	local info;
	local selected = "";
	
	if ( WebDKP_Tables ~= nil and next(WebDKP_Tables)~=nil ) then
		for key, entry in pairs(WebDKP_Tables) do
			if ( type(entry) == "table" ) then
				info = { };
				info.text = key;
				info.value = entry["id"]; 
				info.func = WebDKP_Tables_DropDown_OnClick;
				if ( entry["id"] == WebDKP_Frame.selectedTableid ) then
					info.checked = ( entry["id"] == WebDKP_Frame.selectedTableid );
					selected = info.text;
				end
				UIDropDownMenu_AddButton(info);
			end
		end
	end
	UIDropDownMenu_SetSelectedName(WebDKP_Tables_DropDown, selected );
	UIDropDownMenu_SetWidth(WebDKP_Tables_DropDown, 200, 10);
	-- UIDropDownMenu_SetButtonWidth(WebDKP_Tables_DropDown, 200);
	
end

-- ================================
-- Called when the user switches between
-- a different dkp table.
-- ================================
function WebDKP_Tables_DropDown_OnClick(self)
	local this = self;
	WebDKP_Frame.selectedTableid = this.value;
	WebDKP_Options["SelectedTableId"] = this.value; 
	WebDKP_Tables_DropDown_Init();
	WebDKP_UpdateTableToShow(); --update who is in the table
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end




---------------------------------------------------
-- MINIMAP SCROLLING CODE
-- The following code handles the minimap icon that can be dragged. 
-- Code is based off of examples from Outfitter and the WoWWiki
---------------------------------------------------

-- ================================
-- Called when the user presses the mouse button down on the
-- mini map button. Remembers that position in case they
-- attempt to start dragging
-- ================================
function WebDKP_MinimapButton_MouseDown(self)
	local this = self;
	-- Remember where the cursor was in case the user drags
	
	local	vCursorX, vCursorY = GetCursorPosition();
	
	vCursorX = vCursorX / this:GetEffectiveScale();
	vCursorY = vCursorY / this:GetEffectiveScale();
	
	WebDKP_MinimapButton.CursorStartX = vCursorX;
	WebDKP_MinimapButton.CursorStartY = vCursorY;
	
	local	vCenterX, vCenterY = WebDKP_MinimapButton:GetCenter();
	local	vMinimapCenterX, vMinimapCenterY = Minimap:GetCenter();
	
	WebDKP_MinimapButton.CenterStartX = vCenterX - vMinimapCenterX;
	WebDKP_MinimapButton.CenterStartY = vCenterY - vMinimapCenterY;
end

-- ================================
-- Called when the user starts to drag. Shows a frame that is registered
-- to recieve on update signals, we can then have its event handler
-- check to see the current mouse position and update the mini map button
-- correctly
-- ================================
function WebDKP_MinimapButton_DragStart(self)
	WebDKP_MinimapButton.IsDragging = true;
	WebDKP_UpdateFrame:Show();
end

-- ================================
-- Users stops dragging. Ends the timer
-- ================================
function WebDKP_MinimapButton_DragEnd(self)
	WebDKP_MinimapButton.IsDragging = false;
	WebDKP_UpdateFrame:Hide();
end

-- ================================
-- Updates the position of the mini map button. Should be called
-- via the on update method of the update frame
-- ================================
function WebDKP_MinimapButton_UpdateDragPosition(self)
	local this = self;
	-- Remember where the cursor was in case the user drags
	local	vCursorX, vCursorY = GetCursorPosition();
	
	vCursorX = vCursorX / this:GetEffectiveScale();
	vCursorY = vCursorY / this:GetEffectiveScale();
	
	local	vCursorDeltaX = vCursorX - WebDKP_MinimapButton.CursorStartX;
	local	vCursorDeltaY = vCursorY - WebDKP_MinimapButton.CursorStartY;
	
	--
	
	local	vCenterX = WebDKP_MinimapButton.CenterStartX + vCursorDeltaX;
	local	vCenterY = WebDKP_MinimapButton.CenterStartY + vCursorDeltaY;
	
	-- Calculate the angle
	
	local	vAngle = math.atan2(vCenterX, vCenterY);
	
	-- Set the new position
	
	WebDKP_MinimapButton_SetPositionAngle(vAngle);
end

-- ================================
-- Helper method. Helps restrict a given angle from occuring within a restricted angle
-- range. Returns where the angle should be pushed to - before or after the resitricted
-- range. Used to block the minimap button from appearing behind/above the default ui buttons
-- ================================
function WebDKP_RestrictAngle(pAngle, pRestrictStart, pRestrictEnd)
	if ( pAngle == nil ) then
		return pRestrictStart;
	end
	if ( pRestrictStart == nil or pRestrictStart == nil) then
		return pAngle;
	end

	if pAngle <= pRestrictStart
	or pAngle >= pRestrictEnd then
		return pAngle;
	end
	
	local	vDistance = (pAngle - pRestrictStart) / (pRestrictEnd - pRestrictStart);
	
	if vDistance > 0.5 then
		return pRestrictEnd;
	else
		return pRestrictStart;
	end
end

-- ================================
-- Sets the position of the mini map button based on the passed angle. 
-- Restricts the button from appear over any of the default ui buttons. 
-- ================================
function WebDKP_MinimapButton_SetPositionAngle(pAngle)
	local	vAngle = pAngle;
	
	-- Restrict the angle from going over the date/time icon or the zoom in/out icons
	
	local	vRestrictedStartAngle = nil;
	local	vRestrictedEndAngle = nil;
	
	if GameTimeFrame:IsVisible() then
		if MinimapZoomIn:IsVisible()
		or MinimapZoomOut:IsVisible() then
			vAngle = WebDKP_RestrictAngle(vAngle, 0.4302272732931596, 2.930420793963121);
		else
			vAngle = WebDKP_RestrictAngle(vAngle, 0.4302272732931596, 1.720531504573905);
		end
		
	elseif MinimapZoomIn:IsVisible()
	or MinimapZoomOut:IsVisible() then
		vAngle = WebDKP_RestrictAngle(vAngle, 1.720531504573905, 2.930420793963121);
	end
	
	-- Restrict it from the tracking icon area
	
	vAngle = WebDKP_RestrictAngle(vAngle, -1.290357134304173, -0.4918423429923585);
	
	--
	
	local	vRadius = 80;
	
	vCenterX = math.sin(vAngle) * vRadius;
	vCenterY = math.cos(vAngle) * vRadius;
	
	WebDKP_MinimapButton:SetPoint("CENTER", "Minimap", "CENTER", vCenterX - 1, vCenterY - 1);
	
	WebDKP_Options["MiniMapButtonAngle"] = vAngle;
	--gOutfitter_Settings.Options.MinimapButtonAngle = vAngle;
end

-- ================================
-- Event handler for the update frame. Updates the minimap button
-- if it is currently being dragged. 
-- ================================
function WebDKP_OnUpdate(self,elapsed)
	if WebDKP_MinimapButton.IsDragging then
		WebDKP_MinimapButton_UpdateDragPosition(self);
	end
end


-- ================================
-- Initializes the minimap drop down
-- ================================
function WebDKP_MinimapDropDown_OnLoad(self)
	local this = self;
	--UIDropDownMenu_SetAnchor(-2, -20, this, "TOPRIGHT", this:GetName(), "TOPLEFT");
	UIDropDownMenu_Initialize(this, WebDKP_MinimapDropDown_Initialize);
end

-- ================================
-- Adds buttons to the minimap drop down
-- ================================
function WebDKP_MinimapDropDown_Initialize()
	WebDKP_Add_MinimapDropDownItem(self,"DKP Table",WebDKP_Table_GUI);
	WebDKP_Add_MinimapDropDownItem(self,"Bidding",WebDKP_Bid_ToggleUI);
	WebDKP_Add_MinimapDropDownItem(self,"Timed Awards",WebDKP_TimedAward_ToggleUI);
	WebDKP_Add_MinimapDropDownItem(self,"Options",WebDKP_Options_ToggleUI);
	WebDKP_Add_MinimapDropDownItem(self,"Help",WebDKP_Help_ToggleGUI);
	WebDKP_Add_MinimapDropDownItem(self,"Synch Settings", WebDKP_Synch_ToggleUI);		--Added by Zevious
	WebDKP_Add_MinimapDropDownItem(self,"View Log", WebDKP_Log_ToggleUI);			--Added by Zevious
	WebDKP_Add_MinimapDropDownItem(self,"Raid Log", WebDKP_RaidLog_ToggleUI);			--Added by Zevious
	WebDKP_Add_MinimapDropDownItem(self,"Char Raid Log", WebDKP_CharRaidLog_ToggleUI);		--Added by Zevious
	
end

-- ================================
-- Toggles the Log - Zevious
-- ================================
function WebDKP_Log_ToggleUI()
	if ( WebDKP_LogFrame:IsShown() ) then
		WebDKP_LogFrame:Hide();
	else
		WebDKP_LogFrame:Show();

	end 
end


-- ================================
-- Helper method that adds individual entries into the minimap drop down
-- menu.
-- ================================
function WebDKP_Add_MinimapDropDownItem(self,text, eventHandler)
	local this = self;
	local info = { };
	info.text = text;
	info.value = text; 
	info.owner = this;
	info.func = eventHandler;
	UIDropDownMenu_AddButton(info);
end


-- ================================
-- Helper method. Called whenever a player clicks on item text. 
-- Should autofill this item name into any appropriate gui edit box. 
-- ================================
function WebDKP_ItemChatClick(link, text, button)
	
	-- do a search for 'player'. If it can be found... this is a player link, not an item link. It can be ignored
	local idx = strfind(text, "player");
	
	if( idx == nil ) then
	
		-- check to see if the bidding frame wants to do anything with the information
		WebDKP_Bid_ItemChatClick(link, text, button);
		--WebDKP_Print("link = " .. link .. ", text= " .. text .. ", button = " .. button);
		-- put the item text into the award editbox as long as the table frame is visible
		if ( IsShiftKeyDown() or IsControlKeyDown()) then
			local _,itemName,itemLink = WebDKP_GetItemInfo(link);
			WebDKP_AwardItem_FrameItemName:SetText(itemLink);
		end
		if IsAltKeyDown() and WebDKP_Options["AltClick"] == 1 then
			local _,itemName,itemLink = WebDKP_GetItemInfo(link);
			WebDKP_BidFrameItem:SetText(itemLink);
			if ( WebDKP_BidFrame:IsShown() == FALSE ) then
				WebDKP_Bid_ToggleUI();
			end
		end
	end
end

-- ================================
-- Helper method. Called whenever a player clicks on item boxs (from loot window, etc)
-- Should autofill this item name into any appropriate gui edit box. 
-- ================================
function WebDKP_HandleModifiedItemClick(item) 
	
	if ( item == nil ) then
		return
	end
	
    --WebDKP_Print("HandleModifiedItemClick");
	
	WebDKP_Bid_ItemChatClick(item, nil, nil);
	
	--WebDKP_Print("Post HandleModifiedItemClick");
	
	if (  IsShiftKeyDown() or IsControlKeyDown()) then
		local itemRarity,itemName,itemLink = WebDKP_GetItemInfo(item); 
        	WebDKP_Bid_ItemChatClick(itemLink, itemLink, nil);
		WebDKP_AwardItem_FrameItemName:SetText(item);
	end
	if IsAltKeyDown() and WebDKP_Options["AltClick"] == 1 then
		local _,itemName,itemLink = WebDKP_GetItemInfo(item);
		WebDKP_BidFrameItem:SetText(itemLink);
			if ( WebDKP_BidFrame:IsShown() == FALSE ) then
				WebDKP_Bid_ToggleUI();
			end
	end

end

-- ================================
-- Called when a player shift clicks an item. Added by Zevious
-- ================================
function WebDKP_ItemChatShiftClick(_,link, text, button)
	
	--WebDKP_Print("ItemChatClick");
	
	-- do a search for 'player'. If it can be found... this is a player link, not an item link. It can be ignored
	local idx = strfind(text, "player");
	
	if( idx == nil ) then
	
		-- check to see if the bidding frame wants to do anything with the information
		WebDKP_Bid_ItemChatClick(link, text, button);
		--WebDKP_Print("link = " .. link .. ", text= " .. text .. ", button = " .. button);
		-- put the item text into the award editbox as long as the table frame is visible
		if ( IsShiftKeyDown() or IsControlKeyDown()) then
		
			local _,itemName,itemLink = WebDKP_GetItemInfo(link);
			WebDKP_AwardItem_FrameItemName:SetText(itemLink);
		end
		if IsAltKeyDown() then
			local _,itemName,itemLink = WebDKP_GetItemInfo(link);
			WebDKP_BidFrameItem:SetText(itemLink);
			if ( WebDKP_BidFrame:IsShown() == FALSE ) then
				WebDKP_Bid_ToggleUI();
			end

		end
	end
end



-- ================================
-- Process rolling
-- Added by Zevious (Bronzebeard)
-- ================================
function Webdkp_Sys_Msg_Received(arg1)
	local msg = arg1;
	local itemString = "";
	local pattern = "(.+) rolls (%d+) %((%d+)%-(%d+)%)";


	-- Check to see if it's a /random roll:
	local player, roll, min_roll, max_roll, report, startIndex, endIndex
	if (string.find(msg, pattern)) then
		_, _, player, roll, min_roll, max_roll = string.find(msg, pattern)

		WebDKP_ProcessRoll(player, roll, min_roll, max_roll)
	
	end
	
end

-- ===========================================================================================
-- Notifies the player with the master table to start synching just the DKP Table.
-- Added by Zevious (Bronzebeard)
-- ===========================================================================================
function WebDKP_Start_Synch()
	local synch_master = WebDKP_Options["SynchFrom"];
	local synch_pass = WebDKP_Options["SynchPassword"];
	-- Add a confirmation box Are you sure you want to synch with ""
	SendChatMessage("!Synch "..synch_pass, "WHISPER", nil, synch_master)		-- Whisper the person with the Master table and tell them to synch.

end


-- ===========================================================================================
-- Notifies the player with the master table to start synching both the DKP table and Log.
-- Added by Zevious (Bronzebeard)
-- ===========================================================================================
function WebDKP_Start_SynchAll()
	local synch_master = WebDKP_Options["SynchFrom"];
	local synch_pass = WebDKP_Options["SynchPassword"];
	-- Add a confirmation box Are you sure you want to synch with ""
	SendChatMessage("!WDKP_Synch_All "..synch_pass, "WHISPER", nil, synch_master)		-- Whisper the person with the Master table and tell them to synch.

end


-- ========================================================
-- Rerenders the log table to the screen - Zevious
-- ========================================================
function WebDKP_UpdateLogTable()

if WebDKP_Log ~= nil then
	local entries = { };
	local awarded = { };
	local countnames = 0;
	local awardedtoname = "";
	for k, v in pairs(WebDKP_Log) do
		awardedtoname = "";
		countnames = 0;
		if ( type(v) == "table" ) then
			if( v["date"] ~= nil and v["points"] ~= nil and v["awarded"] ~=nil and v["reason"] ~=nil and v["foritem"] ~= nil) then
				awarded = v["awarded"];
					
				for k, v in pairs(awarded) do
					if ( type(v) == "table" ) then
						if( v["name"] ~= nil) then
				
							awardedtoname = v["name"];
							countnames = countnames + 1;
				
						end
					end
				end
				if countnames == 1 then
					if v["foritem"] == "false" then
						reasonText = v["reason"];
					else
						reasonText = v["itemlink"];
					end
					tinsert(entries,{v["date"],v["points"],reasonText,awardedtoname,v["foritem"]}); -- copies over amount, reason, date
				end
				if countnames > 1 then
					if v["foritem"] == "false" then
						reasonText = v["reason"];
					else
						reasonText = v["itemlink"];
					end
					tinsert(entries,{v["date"],v["points"],reasonText,"Multiple",v["foritem"]}); -- copies over amount, reason, date
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
				if ( WebDKP_LogSort["way2"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr2"]] == a2[WebDKP_LogSort["curr2"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr2"]] > a2[WebDKP_LogSort["curr2"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr2"]] == a2[WebDKP_LogSort["curr2"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr2"]] < a2[WebDKP_LogSort["curr2"]];
					end
				end
			end
		end
	);


	local numEntries = getn(entries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_LogFrameScrollLogFrame);
	--WebDKP_Print("Before Update");
	FauxScrollFrame_Update(WebDKP_LogFrameScrollLogFrame, numEntries, 23, 20);
	--WebDKP_Print("After Update");
	-- Run through the table lines and put the appropriate information into each line

	
	for i=1, 23, 1 do
		local line = getglobal("WebDKP_LogFrameLine" .. i);
		awardedText = getglobal("WebDKP_LogFrameLine" .. i .. "Awarded");
		local amountText = getglobal("WebDKP_LogFrameLine" .. i .. "Amount");
		local reasonText = getglobal("WebDKP_LogFrameLine" .. i .. "Reason");
		local dateText = getglobal("WebDKP_LogFrameLine" .. i .. "Date");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_LogFrameScrollLogFrame);
		if ( index <= numEntries) then
		

			--local playerName = entries[index][1];
			line:Show();
			local charname = entries[index][4];
			awardedText:SetText(charname);
			if WebDKP_DkpTable[charname] ~= nil and WebDKP_DkpTable[charname]["class"] ~= nil then
				local charclass = WebDKP_DkpTable[charname]["class"];
				charclass = string.upper(charclass);
				charclass = string.gsub(charclass, " ", "");
				if RAID_CLASS_COLORS[charclass] ~= nil then
					awardedText:SetTextColor(RAID_CLASS_COLORS[charclass]["r"],RAID_CLASS_COLORS[charclass]["g"],RAID_CLASS_COLORS[charclass]["b"]);
				end
			end
			amountText:SetText(entries[index][2]);
			reasonText:SetText(entries[index][3]);

			dateText:SetText(entries[index][1]);

		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end
end
end
-- =======================================================
-- Rerenders the log of people awarded on the right side - Zevious
-- ========================================================
function WebDKP_UpdateAwardedTable(reason,date)
	
	local awarded = { };
	nameentries = { };
	if reason ~= nil and date ~= nil then
		awarded = WebDKP_Log[reason.." "..date]["awarded"];			-- Assigns the table of people awarded
		for k, v in pairs(awarded) do
			if ( type(v) == "table" ) then
				if( v["name"] ~= nil) then

					tinsert(nameentries,{v["name"]}); -- copies over the names
				
				end
			end
		end
	
	
	end

	-- SORT
	table.sort(
		nameentries,
		function(a1, a2)
			if ( a1 and a2 ) then
				if ( a1 == nil ) then
					return 1>0;
				elseif (a2 == nil) then
					return 1<0;
				end
				if ( WebDKP_LogSort["way3"] == 1 ) then
					if ( a1[WebDKP_LogSort["curr3"]] == a2[WebDKP_LogSort["curr3"]] ) then
						return a1[1] > a2[1];
					else
						return a1[WebDKP_LogSort["curr3"]] > a2[WebDKP_LogSort["curr3"]];
					end
				else
					if ( a1[WebDKP_LogSort["curr3"]] == a2[WebDKP_LogSort["curr3"]] ) then
						return a1[1] < a2[1];
					else
						return a1[WebDKP_LogSort["curr3"]] < a2[WebDKP_LogSort["curr3"]];
					end
				end
			end
		end
	);


	local numEntries = getn(nameentries);

	local offset = FauxScrollFrame_GetOffset(WebDKP_LogFrameScrollAwardedFrame);
	FauxScrollFrame_Update(WebDKP_LogFrameScrollAwardedFrame, numEntries, 23, 20);
	-- Run through the table lines and put the appropriate information into each line

	
	for i=1, 23, 1 do
		local line = getglobal("WebDKP_LogFrameLines" .. i);
		awardedText = getglobal("WebDKP_LogFrameLines" .. i .. "Awarded");
		local index = i + FauxScrollFrame_GetOffset(WebDKP_LogFrameScrollAwardedFrame); 
		
		if ( index <= numEntries) then

			line:Show();
			local charname = nameentries[index][1]
			awardedText:SetText(charname);
			if WebDKP_DkpTable[charname] ~= nil and WebDKP_DkpTable[charname]["class"] ~= nil then
				local charclass = WebDKP_DkpTable[charname]["class"];
				charclass = string.upper(charclass);
				charclass = string.gsub(charclass, " ", "");
				if RAID_CLASS_COLORS[charclass] ~= nil then
					awardedText:SetTextColor(RAID_CLASS_COLORS[charclass]["r"],RAID_CLASS_COLORS[charclass]["g"],RAID_CLASS_COLORS[charclass]["b"]);
				end
			end

		else
			-- if the line isn't in use, hide it so we dont' have mouse overs
			line:Hide();
		end
	end

end
-- ====================================================================
-- Called when the user scrolls through the log awarded list. Causes 
-- ====================================================================
function WebDKP_ScrollLogAwardedToggle()
	WebDKP_UpdateAwardedTable(_G["AwardedReason"], _G["AwardedDate"]);
end



-- ====================================================================
-- Called when the user clicks Undo Award
-- =====================================================================
function WebDKP_LogUndo()
	if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
		if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil then

			if WebDKP_Options["EnableSynch"] == 1 then						-- Make sure we have synching enabled
				WebDKP_Synch_Auto(points,"UNDO", "", _G["AwardedReason"], _G["AwardedDate"]); 	-- Runs Synch Code
			end
			awardedtoremove = WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"];	-- Assigns the table of people awarded
			tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
			local points = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["points"];
			local reason = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["reason"];
			local forItem = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["foritem"];
			points = tonumber(points) * -1;
			for k, v in pairs(awardedtoremove) do
				if ( type(v) == "table" ) then
					name = v["name"];
					
					if WebDKP_Options["EPGPEnabled"] == 1 then
						if forItem == "false" then
							WebDKP_AddEPToTable(name, _, points, tableidfrom)
							WebDKP_UpdateTableToShow();
							WebDKP_UpdateEPGPTable();

						else
							WebDKP_AddGPToTable(name, _, points, tableidfrom)
							WebDKP_UpdateTableToShow();
							WebDKP_UpdateTable();
						end
					else
						-- Pull the proper points value for this character
						WebDKP_AddDKPToTable(name, _, points,tableidfrom);
						WebDKP_UpdateTableToShow();
						WebDKP_UpdateTable();
					end
					

				end
			end

			WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]] = nil;
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
			_G["LineLocation"] = "";
			_G["AwardedReason"] = "";
			_G["AwardedDate"] = "";
			WebDKP_UpdateLogTable();

		end

		for i=1, 23, 1 do

			local line = getglobal("WebDKP_LogFrameLines" .. i);
			line:Hide();
			
		end
		local numEntries = 0;
		FauxScrollFrame_Update(WebDKP_LogFrameScrollAwardedFrame, numEntries, 23, 20);
	end
end
-- ====================================================================
-- Called when the user scrolls through the log list. 
-- ====================================================================
function WebDKP_ScrollLogListToggle()
	WebDKP_UpdateLogTable();
	if WebDKP_Log ~= nil then
		-- Set the previous selection to not selected
		if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
			WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["selected"] = false;
		end
		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
	end

end

-- ================================
-- Called when a player clicks on a column header on the table
-- Changes the sorting options / asc&desc. 
-- Causes the table display to be refreshed afterwards
-- so the player instantly sees changes
-- ================================
function WebDKPLog_SortBy(id)

	if ( WebDKP_LogSort["curr2"] == id ) then
		WebDKP_LogSort["way2"] = abs(WebDKP_LogSort["way2"]-1);		-- toggles between 1 and 0
	else
		WebDKP_LogSort["curr2"] = id;
		if( id == 1) then
			WebDKP_LogSort["way2"] = 0;
		elseif ( id == 2 ) then
			WebDKP_LogSort["way2"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		elseif ( id == 3 ) then
			WebDKP_LogSort["way2"] = 0; 
		else
			WebDKP_LogSort["way2"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		end
		
	end
	-- update table so we can see sorting changes
	WebDKP_UpdateLogTable();
end

-- ================================
-- Char Raid sorter
-- ================================
function WebDKPCharLog_SortBy(id)

	if ( WebDKP_LogSort["curr4"] == id ) then
		WebDKP_LogSort["way4"] = abs(WebDKP_LogSort["way4"]-1);		-- toggles between 1 and 0
	else
		WebDKP_LogSort["curr4"] = id;
		if( id == 1) then
			WebDKP_LogSort["way4"] = 0;
		elseif ( id == 2 ) then
			WebDKP_LogSort["way4"] = 1;
		elseif ( id == 3 ) then
			WebDKP_LogSort["way4"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		else
			WebDKP_LogSort["way4"] = 1; --columns with numbers need to be sorted different first in order to get DESC right
		end
		
	end

end
-- ================================
-- Called when the user clicks on an Awardee in the award log.
-- ================================
function WebDKP_SelectAwardeeLogToggle(self)
	local this = self;
	if WebDKP_Log ~= nil then

		if _G["LineLocation"] ~= "" then
			_G["LineLocation"]:SetVertexColor(0, 0, 0, 0);
		end
		local awardee = _G[this:GetName().."Awarded"]:GetText();
		if awardee ~= nil then
			WebDKP_LogFrameCharChange:SetText(awardee);
		end

	end
end

-- ====================================================================
-- Called when the user clicks Delete Character for the Award Log
-- =====================================================================
function WebDKP_DeleteLogChar()
	local awardedtodel = WebDKP_LogFrameCharChange:GetText();	-- Assigns the person to be removed
	local charflag = 0;

	if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
		if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil and awardedtodel ~= "" and awardedtodel ~= nil then
			
			tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
			local points = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["points"];
			local reason = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["reason"];
			local forItem = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["foritem"];

			points = tonumber(points) * -1;

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



				if WebDKP_Options["EPGPEnabled"] == 1 then
					if forItem == "false" then
						WebDKP_AddEPToTable(awardedtodel, _, points, tableidfrom)
					else
						WebDKP_AddGPToTable(awardedtodel, _, points, tableidfrom)
					end
				else
					-- Pull the proper points value for this character
					WebDKP_AddDKPToTable(awardedtodel, _, points,tableidfrom);
				end


				WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtodel] = nil;

				WebDKP_UpdateLogTable();

				-- Check to see if there is no one else under the awarded list, if so delete the award.
				numEntries = getn(nameEntries);
				if numEntries == nil then
					WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]] = nil;
					local line = getglobal("WebDKP_LogFrameLines" .. 1);
					line:Hide();
				else
					WebDKP_UpdateAwardedTable(_G["AwardedReason"],_G["AwardedDate"])
				end

			end
			-- Check to see if we have synching enabled and if we do then send the synch
			if WebDKP_Options["EnableSynch"] == 1 then					
				WebDKP_Synch_Auto(points, "LOGDEL", awardedtodel, _G["AwardedReason"], _G["AwardedDate"])	-- Runs Synch Code
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


-- ====================================================================
-- Called when the user clicks Add Character for the Award Log
-- =====================================================================
function WebDKP_AddLogChar()
	local awardedtoadd = WebDKP_LogFrameCharChange:GetText();	-- Assigns the person to be added
	local tableid = WebDKP_GetTableid();

	if _G["AwardedReason"] ~= "" and _G["AwardedDate"] ~= "" then
		if WebDKP_Log[_G["AwardedReason"].." ".._G["AwardedDate"]]["awarded"] ~= nil and awardedtoadd ~= "" and awardedtoadd ~= nil then

			tableidfrom =  WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["tableid"];
			local points = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["points"];
			local reason = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["reason"];
			local forItem = WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["foritem"];
			points = tonumber(points);
			WebDKP_MakeSureInTable(awardedtoadd, tableid, nil , nil);

			local DKPCapVal = tonumber(WebDKP_Options["dkpCapLimit"]);

			if (WebDKP_DkpTable[awardedtoadd]["dkp_"..tableid] + points > DKPCapVal and WebDKP_Options["EPGPEnabled"] == 0) then
				-- Send the user a message saying this player can't be added because the award would put them over the cap.
				WebDKP_Print("Sorry but this player cannot be added to this specific award because it will place them over the DKP CAP.");
				PlaySound(SOUNDKIT.IG_QUEST_FAILED, "SFX");
			else

				if WebDKP_Options["EPGPEnabled"] == 1 then
					if forItem == "false" then
						WebDKP_AddEPToTable(awardedtoadd, _, points, tableidfrom)
					else
						WebDKP_AddGPToTable(awardedtoadd, _, points, tableidfrom)
					end
				else
					-- Pull the proper points value for this character
					WebDKP_AddDKPToTable(awardedtoadd, _, points,tableidfrom);
				end

				WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd] = {};
				WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd]["name"] = awardedtoadd;
				WebDKP_Log[_G["AwardedReason"].." ".. _G["AwardedDate"]]["awarded"][awardedtoadd]["class"] = class;
				WebDKP_UpdateLogTable();
				if WebDKP_Options["EnableSynch"] == 1 then								-- Make sure we have synching enabled	
					WebDKP_Synch_Auto(points, "LOGADD", awardedtoadd, _G["AwardedReason"], _G["AwardedDate"])	-- Runs Synch Code
				end
			end
		end

		WebDKP_UpdateAwardedTable(_G["AwardedReason"],_G["AwardedDate"])
	end
	WebDKP_UpdateTableToShow();
	if WebDKP_Options["EPGPEnabled"] == 0 then
		WebDKP_UpdateTable();       --update the gui
	else
		WebDKP_UpdateEPGPTable();       --update the gui
	end
end

-- ====================================================================
-- Called when the user clicks on an item slot icon in bidding options
-- Brings up a text box which allows the value to be saved
-- =====================================================================
function WebDKP_ProcessItemSlotMulti(itemdesc, itemlocation)

	if WebDKP_Options["ItemLocMult"] == nil then
		WebDKP_Options["ItemLocMult"] = {};
	end
	if WebDKP_Options["ItemLocMult"][itemlocation] == nil then
		WebDKP_Options["ItemLocMult"][itemlocation] = "1";
	end
	
	local itemvalue = WebDKP_Options["ItemLocMult"][itemlocation];
	WebDKP_ItemSlotFrame:Show();
	WebDKP_ItemSlotFrameTitle:SetText(itemdesc);
	WebDKP_ItemSlotFrameCost:SetText(itemvalue); 
end
-- ====================================================================
-- Process the itemslot location mult change
-- =====================================================================
function WebDKP_SetItemSlotMulti(value, itemdesc)
	if itemdesc == "Head Slot" then
		itemlocation = "head";
	elseif itemdesc == "Neck Slot" then
		itemlocation = "neck";
	elseif itemdesc == "Shoulders Slot" then
		itemlocation = "shoulders";
	elseif itemdesc == "Back Slot" then
		itemlocation = "back";
	elseif itemdesc == "Chest Slot" then
		itemlocation = "chest";
	elseif itemdesc == "Wrists Slot" then
		itemlocation = "wrist";
	elseif itemdesc == "Hands Slot" then
		itemlocation = "hands";
	elseif itemdesc == "Waist Slot" then
		itemlocation = "waist";
	elseif itemdesc == "Legs Slot" then
		itemlocation = "legs";
	elseif itemdesc == "Feet Slot" then
		itemlocation = "feet";
	elseif itemdesc == "Fingers Slot" then
		itemlocation = "fingers";
	elseif itemdesc == "Trinkets Slot" then
		itemlocation = "trinkets";
	elseif itemdesc == "Main Hand Slot" then
		itemlocation = "mainhand";
	elseif itemdesc == "Shield Slot" then
		itemlocation = "shield";
	elseif itemdesc == "Ranged Slot" then
		itemlocation = "ranged";
	elseif itemdesc == "Relic Slot" then
		itemlocation = "relic";
	elseif itemdesc == "Two Hand Slot" then
		itemlocation = "twohand";
	elseif itemdesc == "One Hand Slot" then
		itemlocation = "onehanders";
	elseif itemdesc == "Held In Offhand" then
		itemlocation = "heldoffhand";
	elseif itemdesc == "Offhand Weapon" then
		itemlocation = "offhandweapon";
	end


	WebDKP_Options["ItemLocMult"][itemlocation] = value;
 
end