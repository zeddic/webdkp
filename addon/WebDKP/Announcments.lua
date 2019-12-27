------------------------------------------------------------------------
-- ANNOUNCMENETS	
------------------------------------------------------------------------
-- Contains methods related to the raid announcemenets in game whenever
-- DKP is awarded. 
------------------------------------------------------------------------



-- The following are award strings that the addon uses. If you wish to modify what the addon says for
-- awards you just need to edit these strings. 
-- Do display a new line in your message use \n. 

WebDKP_ItemAward =			"WebDKP: $player awarded $item for: $cost dkp.";

WebDKP_ItemAwardZeroSum =	"WebDKP: $dkp dkp awarded to all players for ZeroSum: $item";

WebDKP_DkpAwardAll =		"WebDKP: $dkp dkp given to all players for: $reason.";

WebDKP_DkpAwardSome =		"WebDKP: $dkp dkp given to selected players for: $reason. \nReceiving players have all been whispered.";

WebDKP_BidStart =			"WebDKP: Bidding has started on $item! $time " ..
							"$startingBid"..
							"$instructions";

WebDKP_BidEnd =				"WebDKP: Bidding has ended for $item The winner was $name who bid $dkp dkp";
WebDKP_BidEndSilent = 			"WebDKP: Bidding has ended for $item";
WebDKP_RollEnd =			"WebDKP: Rolling has ended for $item. $name was the high roller with a $roll";

WebDKP_TimedAward =			"WebDKP: $minutes Minute Timed Award of $dkp dkp Given";
WebDKP_BossAwardNum =			"WebDKP: Great Job! A Boss Award of $dkp Has Been Given";

-- ================================
-- Returns the location where notifications should be sent to. 
-- "Raid" or "Party". If player is in neither a raid or a party, returns
-- "None"
-- ================================
function WebDKP_GetTellLocation()
	
	local inRaid = IsInRaid();
	local inParty = IsInGroup();
	
	if inRaid then
		return "RAID";
	elseif inParty then
		return "PARTY";
	else
		return "NONE";
	end
end

-- ================================
-- Makes an announcement that a user has received an item. 
-- ================================
function WebDKP_AnnounceAwardItem(cost, item, player)

if WebDKP_Options["Announcements"] == 0 then

	local tellLocation = WebDKP_GetTellLocation();
	cost = cost * -1;
	
	local _,_,link = WebDKP_GetItemInfo(item);				-- Convert the item to a link

	local toSay =	string.gsub(WebDKP_ItemAward, "$player", player);
	toSay =	string.gsub(toSay, "$item", link);
	toSay =	string.gsub(toSay, "$cost", cost);
	
	WebDKP_SendAnnouncement(toSay,tellLocation);
	
	
	-- If using Zero Sum announce the zero sum award
	if ( WebDKP_WebOptions["ZeroSumEnabled"]==1) then
		local numPlayers = WebDKP_GetTableSize(WebDKP_PlayersInGroup);
		if ( numPlayers ~= 0 ) then 
			local toAward = (cost) / numPlayers;
			toAward = WebDKP_ROUND(toAward, 2 );
			local toSay =	string.gsub(WebDKP_ItemAwardZeroSum, "$dkp", toAward);
			toSay =	string.gsub(toSay, "$item", link);
			WebDKP_SendAnnouncement(toSay, tellLocation);
		end
	end
end

end

-- ================================
-- Makes an announcement that the raid (or a set of users) has received dkp
-- ================================
function WebDKP_AnnounceAward(dkp, reason)

if WebDKP_Options["Announcements"] == 0 then

	local tellLocation = WebDKP_GetTellLocation();
	local allGroupSelected = WebDKP_AllGroupSelected();

	-- Everyone received the award
	if ( allGroupSelected == true ) then
	
		-- Announce the award
		local toSay =	string.gsub(WebDKP_DkpAwardAll, "$dkp", dkp);
		toSay =	string.gsub(toSay, "$reason", reason);
		WebDKP_SendAnnouncement(toSay,tellLocation);
	
	
	-- Only some people received the award
	else
		
		-- Announce the award
	
		local toSay =	string.gsub(WebDKP_DkpAwardSome, "$dkp", dkp);
		toSay =	string.gsub(toSay, "$reason", reason);
		WebDKP_SendAnnouncement(toSay,tellLocation);
		
		-- now increment through the selected players and announce them
	
		for key_name, v in pairs(WebDKP_DkpTable) do
			if ( type(v) == "table" ) then
				if( v["Selected"] ) then
					--WebDKP_SendAnnouncement(key_name,tellLocation);
					WebDKP_SendWhisper(key_name,"You have been awarded "..dkp.." dkp");
				end
			end
		end
	end
end
end

-- ================================
-- Announces that bidding has started. 
-- Accepts item name and the time (in seconds) that bidding
-- will go for
-- ================================
function WebDKP_AnnounceBidStart(item, time, startingBid) 
	local tellLocation = WebDKP_GetTellLocation();
	startingBidvalue = startingBid;
	if(time == 0 or time == nil or time =="" or time=="0") then
		time = "";
	else
		time = "("..time.."s)";
	end
	
	local instructions; 
	local _,_,link = WebDKP_GetItemInfo(item);
	if ( WebDKP_Options["BidFixedBidding"] == 1 ) then
		instructions =	"To place a bid say !need in a raid/party/whisper. "
	else
		instructions =	"To place a bid say !bid <value> in a raid/party/whisper. "..
						"(ex: !bid 50)";
	end
	
	
	local startingBidText = ""; 
	if ( startingBid > 0 ) then
		startingBidText =	"Bidding starts at "..startingBid.." dkp. ";
	end
	
	local toSay =	string.gsub(WebDKP_BidStart, "$item", item);
	toSay =	string.gsub(toSay, "$time", time);
	toSay =	string.gsub(toSay, "$startingBid", startingBidText);
	toSay =	string.gsub(toSay, "$instructions", instructions);
	local announceText = WebDKP_Options["EditStartAnnounce"];
	if announceText ~= "" and announceText ~= nil then
		
		if time == "" then
			time = "User Defined"
		end
		if startingBidvalue == 0 then
			startingBidvalue = "No Starting Bid"
		end
		announceText = string.gsub(announceText, "$item", link);
		announceText = string.gsub(announceText, "$time", time);
		announceText = string.gsub(announceText, "$bid", startingBidvalue);
		WebDKP_SendAnnouncement(announceText,tellLocation);
	else
		WebDKP_SendAnnouncement(toSay,tellLocation);
	end
end	

-- ================================
-- Announces that Rolling has started. 
-- Accepts item name and the time (in seconds) that the rolling will go for
-- Added by Zevious (Bronzebeard)
-- ================================
function WebDKP_AnnounceRollStart(item, time) 
	local tellLocation = WebDKP_GetTellLocation();
	local TimeFlag = "Time remaining:";
	local toSay = nil;
	if(time == 0 or time == nil or time =="" or time=="0") then
		time = "";
	else
		time = "("..time.."s)";
	end
	
	if time == "" then
		toSay = string.gsub("Rolling has started for $item. Please type /roll to roll.","$item", item);
	else
		toSay =	string.gsub("Rolling has started for $item. Please type /roll to roll. Time Remaining: $time","$item", item);
		toSay = string.gsub(toSay,"$time", time);
	end

	--convert the item to a link
	local _,_,link = WebDKP_GetItemInfo(item);
	local tellLocation = WebDKP_GetTellLocation();

	local announceText = WebDKP_Options["EditSRollAnnounce"];
	if announceText ~= "" and announceText ~= nil then
		announceText = string.gsub(announceText, "$item", link);
		if time == 0 or time == nil or time == "0" then
			time = "Unknown"
		end
		announceText = string.gsub(announceText, "$time", time);
		WebDKP_SendAnnouncement(announceText,tellLocation);
	else
		
		toSay = string.gsub(toSay, "$item", link);
		toSay =	string.gsub(toSay, "$time", time);
		WebDKP_SendAnnouncement(toSay,tellLocation);
	end

end


-- ================================
-- Announces that bidding has finished
-- Accepts itemname, name of highest bidder, bid dkp
-- ================================
function WebDKP_AnnounceBidEnd(item, name, dkp, totalbids)
	

	if(name == nil or name == "") then
		name = "no one";
		dkp = 0;
	end
	--convert the item to a link
	local _,_,link = WebDKP_GetItemInfo(item);
	local tellLocation = WebDKP_GetTellLocation();

	if WebDKP_Options["SilentBidding"] == 0 then
		toSay =	string.gsub(WebDKP_BidEnd, "$item", WebDKP_bidItemLink);
		toSay =	string.gsub(toSay, "$name", name);
		toSay =	string.gsub(toSay, "$dkp", dkp);
	else
		toSay =	string.gsub(WebDKP_BidEndSilent, "$item", WebDKP_bidItemLink);
	end
	local announceText = WebDKP_Options["EditEndAnnounce"];
	if announceText ~= "" and announceText ~= nil then
		announceText = string.gsub(announceText, "$item", link);
		announceText = string.gsub(announceText, "$name", name);
		announceText = string.gsub(announceText, "$dkp", dkp);
		announceText = string.gsub(announceText, "$totbid", totalbids);
		WebDKP_SendAnnouncement(announceText,tellLocation);
	else
		WebDKP_SendAnnouncement(toSay,tellLocation);
	end
	
	--WebDKP_SendAnnouncement(toSay,tellLocation);
end

-- ================================
-- Announces that rolling has finished
-- Accepts itemname, name of highest bidder, bid dkp
-- Added by Zevious (Bronzebeard)
-- ================================
function WebDKP_AnnounceRollEnd(item, name, dkp, totalrolls)
	highest_roller, high_roll = WebDKP_Bid_GetHighestRoll();
	local _,_,link = WebDKP_GetItemInfo(item);
	if(highest_roller == nil or highest_roller == "") then
		highest_roller = "No one";
		high_roll = 0;
	end
	--convert the item to a link
	local _,_,link = WebDKP_GetItemInfo(item);
	local tellLocation = WebDKP_GetTellLocation();

	local announceText = WebDKP_Options["EditERollAnnounce"];
	if announceText ~= "" and announceText ~= nil then
		announceText = string.gsub(announceText, "$item", link);
		announceText = string.gsub(announceText, "$name", highest_roller);
		announceText = string.gsub(announceText, "$roll", high_roll);
		announceText = string.gsub(announceText, "$totrol", totalrolls);
		WebDKP_SendAnnouncement(announceText,tellLocation);
	else
		local toSay = WebDKP_RollEnd;
		toSay = string.gsub(toSay, "$item", link);
		toSay =	string.gsub(toSay, "$name", highest_roller);
		toSay =	string.gsub(toSay, "$roll", high_roll);
		WebDKP_SendAnnouncement(toSay,tellLocation);
	end
end

-- ================================
-- Announces that an automatted timed award has just been given
-- Minutes = # of minutes that the timer is on
-- Dkp = How much dkp was just given
-- ================================
function WebDKP_AnnounceTimedAward(minutes, dkp) 

if WebDKP_Options["Announcements"] == 0 then

	local tellLocation = WebDKP_GetTellLocation();
	local toSay =	string.gsub(WebDKP_TimedAward, "$minutes", minutes);
	toSay =	string.gsub(toSay, "$dkp", dkp);
	WebDKP_SendAnnouncement(toSay,tellLocation);
end
end


-- ================================
-- Announces that an automatted boss award has just been given
-- Dkp = How much dkp was just given
-- Added by Zevious(Bronzebeard)
-- ================================
function WebDKP_AnnounceBossAward(dkp)
if WebDKP_Options["Announcements"] == 0 then

	local tellLocation = WebDKP_GetTellLocation();
	local toSay =	string.gsub(WebDKP_BossAwardNum, "$dkp", dkp);
	WebDKP_SendAnnouncement(toSay,tellLocation);

end
end



-- ================================
-- Sends out an announcent to the screen. 
-- Possible locations are:
-- "RAID", "PARTY", "GUILD", or "NONE"
-- If "NONE" is selected it will output to the players console.
-- This method will also look for line breaks in 'toSay'. If a \n is seen 
-- in the text, the text will be divided into seperate messages at that point. 
-- Example: Hello \n there!
--			Zedd: Hello
--			Zedd: there!
-- ================================
function WebDKP_SendAnnouncement(toSay, location)
	if ( location == "NONE" ) then
		WebDKP_Print(toSay);
	else
		local newLineLoc = string.find(toSay,"\n");
		local tempToSay;
		local breaker = 0 ; 
		--WebDKP_Print("New line loc: "..newLineLoc);
		while (newLineLoc  ~= nil ) do 
			tempToSay = string.sub(toSay,0,newLineLoc-1);
			SendChatMessage(tempToSay,location);
			--trim to say of what we just said
			toSay = string.sub(toSay,newLineLoc+1,string.len(toSay));
			-- get the start of the next new line
			newLineLoc = string.find(toSay,"\n");
		end
		-- finish saying what is left
		SendChatMessage(toSay,location);
	end
end

-- ================================
-- Sends an announcement to the default location
-- ================================
function WebDKP_SendAnnouncementDefault(toSay)
	local tellLocation = WebDKP_GetTellLocation();
	WebDKP_SendAnnouncement(toSay, tellLocation);
end


-- ================================
-- Announces the top 3 bids
-- ================================
function WebDKP_AnnounceTop3()

	local tellLocation = WebDKP_GetTellLocation();
	local highestBidder, highestBidder2, highestBidder3, highestBid, highestBid2, highestBid3 = WebDKP_Bid_GetTopThree();

	-- If there was only two people who bids, the third would need to be set to 0 so its not a nil value.
	if(highestBidder == nil or highestBidder == "") then
		highestBidder = "NA";
		highestBid = 0;
	end
	if(highestBidder2 == nil or highestBidder2 == "") then
		highestBidder2 = "NA";
		highestBid2 = 0;
	end
	if(highestBidder3 == nil or highestBidder3 == "") then
		highestBidder3 = "NA";
		highestBid3 = 0;
	end

	if WebDKP_Options["SilentBidding"] == 0 then
		toSay =	string.gsub(WebDKP_BidEnd, "$item", WebDKP_bidItemLink);
		toSay =	string.gsub(toSay, "$name", name);
		toSay =	string.gsub(toSay, "$dkp", dkp);
	else
		toSay =	string.gsub(WebDKP_BidEndSilent, "$item", WebDKP_bidItemLink);
	end
	local announceText = "WebDKP: The top three bidders were:";
	WebDKP_SendAnnouncement(announceText,tellLocation);
	WebDKP_SendAnnouncement("WebDKP: 1. "..highestBidder.." bidding "..highestBid ,tellLocation);
	WebDKP_SendAnnouncement("WebDKP: 2. "..highestBidder2.." bidding "..highestBid2 ,tellLocation);
	WebDKP_SendAnnouncement("WebDKP: 3. "..highestBidder3.." bidding "..highestBid3 ,tellLocation);

end