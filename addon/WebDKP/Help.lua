------------------------------------------------------------------------
-- HELP	
------------------------------------------------------------------------
-- Contains methods related to displaying help and the help frame to the
-- user. 
------------------------------------------------------------------------

-- The actual help content
local WebDKP_Help = {
	[1] = {
		["Name"] =	"Welcome",
		["Text"] =	"|cFFFF0000Welcome to WebDKP|r"..
					"|n|n"..
					"This addon is intended to make the task of managing DKP easier by automating many of the administrative tasks. The addon makes it easy to both view and make changes to your guild�s DKP. The changes made in game can then be synced with the WebDKP.com website by either using the included sync tool or uploading your log file from the website control center. "..
					"|n|n"..
					"This help document is provided to guide you through the addon�s basic and more advanced use. If this is your very first time using the addon, you can find a handy tutorial in the WebDKP zip file.  If you have any additional questions not covered in this document, or have suggestions, please utilize one of the forums at www.webdkp.com or www.dkptracker.com. "..
					"|n|n"..
					"Thanks,|n"..
					"Quartal, Xenosian, Agard, and Zevious"
	},
	[2] = {  
		["Name"] =	"General Use",
		["Text"] =	"|cFFFF0000General Use|r"..
					"|n|n"..
					"Most of the features of the addon can be reached by selecting |cFFFF0000DKP Table|r from the main drop down menu (off of the minimap). "..
					"|n|n"..
					"Clicking on this will show the main DKP list on the left and a window with a set of tabs on the right. If this is your first time using the addon the list on the left will probably be empty. That�s because the list is defaulted to only show people who are in your current group. If you were to join a party or a raid you would see the list start to fill in with people. As the table fills in with people you can sort it by clicking on the table headers."..
					"|n|n"..
					"The tabs on the right are where all the functions of the addon are located. This area has 3 main tabs for you to work with: Filters, Award DKP, and Award Item. "..
					"|n|n"..
					"The |cFFFF0000Filters Tab|r allows you to filter what you want to be shown in your DKP table. You can limit the table to certain classes, making it easier to find people you want. Here you also have the option of showing all the people in your table � including those not in your current group � by unchecking �Only show players in current group�. "..
					"|n|n"..
					"The |cFFFF0000Award DKP Tab|r allows you to award or subtract DKP from people. Note that this is only used for DKP alone, not for recording items that are awarded. To award DKP all you need to do is select the people that you want to award from the left, enter a reason and # of points to award, then click �Award DKP�. To make selecting players from the list easier you�ll find two buttons near the bottom called �Select All� and �Deselect All�"..
					"|n|n"..
					"Finally, the |cFFFF0000Award Item Tab|r allows you to award an item to a single player. To use it, select a receiving player from the left, an item cost (in positive #�s), enter in the name of the item, and then select Award Item. If you enable �Autofill� or �AutoAward� (on by default) this information will be entered and recorded for you automatically. If you have these features disabled you can also fill in the item name by shift+clicking on the name of an item in chat.  "..
					"|n|n"..
					"After you have finished recording information in game you can, the |cFFFF0000Sync Data|r from the WebDKP addon to the WebDKP.com site. Checkout the program WebDKP Sync.exe that came with the addon."..
					"|n|n"..
					"The |cFFFF0000Decay Feature|r allows you to put in a value to multiply peoples DKP by. Putting in a value of .5 would subtract 50% of someones DKP that had positive DKP. Putting in a value of -.5 would ADD 50% of a players DKP ONLY if they have negative DKP."

	},
	[3] = {
		["Name"] =	"Options",
		["Text"] =	"|cFFFF0000Options|r"..
					"|n|n"..
					"WebDKP has a variety of options that allow you to tweak it to your guilds needs. The option window can be found by clicking on the options choice on the mini map drop down.".. 
					"|n|n"..
					"|cFFFF0000AutoFill|r � When enabled WebDKP will monitor your chat window for any announcements of people receiving items (ex: Zedd received [Crown of Destruction]). When it detects them it will automatically select the receiving player in the table and fill in the item name box on the Award Item tab. If you are using loot tables the system will also attempt to look up the cost of the item in the loot table and fill it in item cost box. "..
					"|n|n"..
					"|cFFFF0000Autofill Threshold|r � Sets the level of item that AutoFill will pick up on. For example, should it only autofill information for Blue items or better or should it also include green items?"..
					"|n|n"..
					"|cFFFF0000AutoAward|r � Takes AutoFill one step farther. When enabled WebDKP will display a popup box whenever it detects an item being received. This popup box will allow you to enter in a DKP cost for the item and automatically record this information in your DKP table. Like AutoFill it is tied in with your loot table. "..
					"|n|n"..
					"|cFFFF0000ZeroSum|r � Checking this enables ZeroSum DKP calculations when awarding DKP. ZeroSum DKP is a system where DKP is only awarded when items are handed out. For the DKP that is spent on the item an equal but opposite amount is awarded and distributed across the entire raid. When enabled, this feature will automatically take care of this calculation for you when you award an item. "..
					"|n|n"..
					"|cFFFF0000Announce Bid in Raid Warning|r � Announces when bidding start via a Raid Warning"..
					"|n|n"..
					"|cFFFF0000Confirm Bid Awards|r � When about to award a winning bidder an item a dialog box will be displayed that allows you to customize what the player is paying for the item"..
					"|n|n"..
					"|cFFFF0000Allow Negative Bids|r � Allows players to bid more money than they have"..
					"|n|n"..
					"|cFFFF0000Used Fixed Bidding|r � Enables a fixed bidding system. In this system players do not bid specific amounts, they simply say whether they need an item or not by whispering !need (the person with the most dkp wins the item). Winning players are then charged all their dkp. This keeps the system turn base"..
					"|n|n"..
					"|cFFFF0000Notify Low Bidders|r � Tells bidders when they have bid lower than a person before them"..
					"|n|n"..
					"|cFFFF0000Auto Award for Boss Kills|r � The enable/disable checkbox will enable or disable this feature. The text box allows you to specifiy how much dkp is automatically awarded for every boss kill. The other two checkboxes allow you to enable or disable this feature for Northrend Raids."..
					"|n|n"..
					"|cFFFF0000Turn Base DKP|r The enable/disable checkbox will enable or disable this feature. Turn base DKP is where a player bids all their DKP and loses all their DKP for items. The Fixed Bidding code is utilized for this we just eliminated the loot tables."
					

	},
	[4] = {
		["Name"] =	"Whisper DKP",
		["Text"] =	"|cFFFF0000Whisper DKP|r"..
					"|n|n"..
					"Whisper DKP is a feature that allows people to send you whispers to see their current DKP as well as the DKP table. The nice part about this feature is that the incoming and outgoing whispers related to Whisper DKP are hidden from you, so your chat box isn�t filled up. "..
					"|n|n"..
					"To use the feature, anyone just needs to whisper you one of the following commands:"..
					"|n|n"..
					"|cFFFF0000!help|r - Lists the commands with instructions|n"..
					"|cFFFF0000!dkp|r - Tells them their current DKP|n"..
					"|cFFFF0000!list|r - Lists the DKP of everyone in your current group|n"..
					"|n|n"..
					"!list and !listall can have class names added to the end to filter them. Examples:"..
					"|n|n"..
					"|cFFFF0000!list hunter|r - Lists the DKP of all hunters in the current group|n"..
					"|cFFFF0000!list hunter paladin|r - Lists the DKP of all hunters and paladins in the current group"
	},
	[5] = {
		["Name"] =	"Bidding",
		["Text"] =	"|cFFFF0000Bidding|r"..
					"|n|n"..
					"An automated bidding feature is available for guilds that use bidding to decide who should receive an item. An item is first placed up for bid using the bidding window or using a chat command. Players in your raid can either say in a whisper / raid / party message how much they want to bid and the addon will pick up this information and display in on screen. Once bidding has ended you can select the winning users and click Award. This will automatically record this information in your DKP table."..
					"|n|n"..
					"To start a bid using the bidding form you can select Bidding from the drop down menu. You can then enter the item name to bid on as well as an optional time in seconds for how long the bid should last. (Entering 0 here means there will be no time limit). Click Start the Bidding to being the bidding. You can shift+click on items in chat to autofill in the item name in the item name box. "..
					"|n|n"..
					"Players can place bids by using a chat message or whisper with the command:|n"..
					"|cFFFF0000!bid #|r   (Example: !bid 20)"..
					"|n|n"..
					"To start a bid using chat commands you can say either of the following in chat:|n"..
					"|cFFFF0000!startbid ItemName|r|n"..
					"|cFFFF0000!startbid ItemName, #seconds|r"..
					"|n|n"..
					"The item name can either be typed in or entered via shift+clicking on an item. |n"..
					"A bid can then be stopped using the command|n"..
					"|cFFFF0000!stopbid|r"..
					"|n|n"..
					"If you are using a Fixed Bidding system (see options), players also have the option of whispering |cFFFF0000!need|r and |cFFFF0000!greed|r instead of bidding a specific amount."

	},
	[5] = {
		["Name"] =	"Loot Table Integration",
		["Text"] =	"|cFFFF0000Loot Table Integration|r"..
					"|n|n"..
					"A handy feature of the addon is that it is integrated with loot tables that you create on WebDKP.com . When you create a loot table on the site (either manually or by selecting one of the templates) this information is sent to the addon on your next sync."..
					" The WebDKP addon can then look up this information in different ways to make your life easier. "..
					"|n|n"..
					"A few places where you will see the loot table information used:"..
					"|n|n"..
					"|cFFFF0000Award Item Tab|r|nIf you enter in a name in the Award Item tab the dkp cost will automatically be looked up and filled in for you."..
					"|n|n"..
					"|cFFFF0000Auto Awards|r|nIf you are using the AutoAward feature the popup will automatically fill in the item cost for you in the item cost field."..
					"|n|n"..
					"|cFFFF0000Fixed Bidding|r|nIf you are using the fixed bidding option item costs will be filled in from the loot table when you award a player. (see options for more details about fixed bidding)"
	},
	[6] = {
		["Name"] =	"Slash Commands",
		["Text"] =	"|cFFFF0000Slash Commands|r"..
					"|n|n"..
					"The following are slash commands that can be used in game:"..
					" 1.) /webdkp show table - Displays the DKP Table. "..
					"|n|n"..
					" 2.) /webdkp show bidding - Displays the bidding window."..
					"|n|n"..
					" 3.) /webdkp show synch - Displays the in game synch settings"..
					"|n|n"..
					" 4.) /webdkp show options - Displays the options window"..
					"|n|n"..
					" 5.) /webdkp show timed - Displays the Timed Awards window"..
					"|n|n"..
					" 6.) /webdkp show help - Displays the Help window"..
					"|n|n"..
					" 7.) /webdkp show log - Displays the Award Log window"..
					"|n|n"..
					" 8.) /webdkp show raidlog - Displays the Raid Log window"..
					"|n|n"..
					" 9.) /webdkp show charlog - Displays the Character Raid Log window"..
					"|n|n"..
					"10.) /webdkp start raid - Starts a raid in the Raid Log"..
					"|n|n"..
					"11.) /webdkp end raid - Ends a raid in the Raid Log"..
					"|n|n"..
					"12.) /webdkp ignore list - Lists all of the ignored items"..
					"|n|n"..
					"13.) /webdkp ignore add - Adds an item to the ignore list"..
					"|n|n"..
					"14.) /webdkp ignore del - Deletes an item from the ignore list (It can't delete the default ones)"
	},
	[7] = {
		["Name"] =	"Ignored Items",
		["Text"] =	"|cFFFF0000Ignored Items|r"..
					"|n|n"..
					"WebDKP allows you to add items to the default ignore list. The default ignore list is contained in webdkp.lua and can't be edited from within the game. The following are the commands that allow you to list, add, and delete ignore items from within WoW"..
					"|n|n"..
					" 1.) The following is an example of how to list all items you've added "..
					"     /webdkp ignore list"..
					"|n|n"..
					" 2.) The following is an example of how to add an item to the ignore list"..
					"     /webdkp ignore add itemname, Example: /webdkp ignore add Cloth Boots"..
					"|n|n"..
					" 3.) The following is an example of how to delete an item from the ignore list"..
					"     /webdkp ignore del itemname, Example: /webdkp ignore del Cloth Boots"
	},
}

-- ================================
-- Initializes the help menu to show the 
-- currently selected topic
-- ================================
function WebDKP_Help_Init()
	WebDKP_Help_DropDown_Init();
	WebDKP_Help_Frame_Text:SetText(WebDKP_Help[WebDKP_HelpFrame.helpChoice]["Text"]);
end

-- ================================
-- Toggles the help frame on and off.
-- ================================
function WebDKP_Help_ToggleGUI()
	if ( WebDKP_HelpFrame:IsShown() ) then
		WebDKP_HelpFrame:Hide();
	else
		WebDKP_Help_Init();
		WebDKP_HelpFrame:Show();
	end
end


-- ================================
-- Invoked when the gui is down. Loads up the contents
-- of the help topic drop down. 
-- ================================
function WebDKP_Help_DropDown_OnLoad()
	UIDropDownMenu_Initialize(WebDKP_Help_DropDown, WebDKP_Help_DropDown_Init);
end

-- ================================
-- Invoked when the help menu drop down list is invoked. 
-- Runs through the help topics data structure and adds
-- a list of items. 
-- ================================
function WebDKP_Help_DropDown_Init()

	if( WebDKP_HelpFrame.helpChoice == nil ) then
		WebDKP_HelpFrame.helpChoice = 1;
	end

	--WebDKP_Print(WebDKP_Help[WebDKP_HelpFrame.helpChoice]["Name"]);
	local numHelpTopics = WebDKP_GetTableSize(WebDKP_Help);
	for i=1, numHelpTopics do
		if ( type(WebDKP_Help[i]) == "table" ) then
			local checked = (WebDKP_Help[i]["Name"] == WebDKP_Help[WebDKP_HelpFrame.helpChoice]["Name"]);
			WebDKP_Add_HelpDropDownItem(self,WebDKP_Help[i]["Name"],i, checked);	
		end
	end
	UIDropDownMenu_SetSelectedName(WebDKP_Help_DropDown, WebDKP_Help[WebDKP_HelpFrame.helpChoice]["Name"] );
	UIDropDownMenu_SetWidth(WebDKP_Help_DropDown, 150, 10);
end

-- ================================
-- Helper method that adds individual entries into the help menu drop down
-- ================================
function WebDKP_Add_HelpDropDownItem(self,text, value, checked)
local this = self;
	local info = { };
	info.text = text;
	info.value = value; 
	info.owner = this;
	info.checked = checked;
	info.func = WebDKP_Help_DropDown_OnClick;
	UIDropDownMenu_AddButton(info);
end

-- ================================
-- Called when the user switches between
-- a different dkp table.
-- ================================
function WebDKP_Help_DropDown_OnClick(self)
local this = self;
	WebDKP_HelpFrame.helpChoice = this.value;
	WebDKP_Help_DropDown_Init();
	WebDKP_Help_Frame_Text:SetText(WebDKP_Help[WebDKP_HelpFrame.helpChoice]["Text"]);
end

