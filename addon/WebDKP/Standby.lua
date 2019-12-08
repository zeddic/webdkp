------------------------------------------------------------------------
-- WebDKP Standby Processing
-- Handles functions related to standby players
------------------------------------------------------------------------
-- Work in progres . . . Zevious
------------------------------------------------------------------------

		
-- ===========================================================================================
-- Toggle someone as standby
-- ===========================================================================================
function WebDKP_Standby_GUIToggle(state,playername)

local tableid = WebDKP_GetTableid();

	-- If the playername was not passed then check the edit box
	if playername == nil or playername == "" then
		playername = WebDKP_Standby_FrameAddStandby:GetText();
	end
	-- If we still don't have a valid name check if someone is selected
	if playername == nil or playername == "" then
		playernametable = WebDKP_GetSelectedPlayers(1);
		if playernametable ~= nil then
			playername = playernametable[0]["name"];
		end
	end
	if playername == nil or playername == "" then
		WebDKP_Print("You must enter a name or select a player to add or remove them to the standby list.");
	
	-- If the name is good then do this
	else
		if state == "add" then
		-- We want to flag this player as being in standby

			-- Check to see if this player is already in the table
			if WebDKP_DkpTable[playername] == nil then
				-- Add this player to the table
				WebDKP_DkpTable[playername] = {
				["dkp_"..tableid] = 0,
				["class"] = "",
				["standby"] = 1,
				["cantrim"] = false,
					
				}
			WebDKP_SendWhisper(playername,"You are now listed as a standby player"); 
			else
				-- Change their standby state appropiately
				WebDKP_DkpTable[playername]["standby"] = 1;
				WebDKP_SendWhisper(playername,"You are now listed as a standby player"); 
			end

		
		end
		if state == "remove" then
			-- We want to remove this player from being listed as standby
			if WebDKP_DkpTable[playername] == nil then
				-- This person doesn't exist
				WebDKP_Print("This person doesn't exist in your table.");
			else
				-- Change their standby state appropiately
				WebDKP_DkpTable[playername]["standby"] = 0;
				WebDKP_SendWhisper(playername,"You have been removed as a standby player"); 
			end
		
		end
		WebDKP_UpdateTableToShow();
		WebDKP_UpdateTable();
	end


end

-- ===========================================================================================
-- Set everyone's standby status to 0
-- ===========================================================================================
function WebDKP_Standby_Reset()

	for k, v in pairs(WebDKP_DkpTable) do
		if ( type(v) == "table" ) then
			v["standby"] = 0;
		end

	end

	WebDKP_UpdateTableToShow();
	WebDKP_UpdateTable();

end


