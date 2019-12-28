<div style="font-size:100%;line-height:2em">

The in-game Addon lets you view and award DKP in World of Warcraft.It also allows you to
track raid attendance.It comes with a Sync program that uploads your information to the site with a 
single click. To fully make use of the addon,
you'll need to create a <a href="<?=$siteRoot?>Join">free account</a> on WebDKP.com
<br />
<br />
<input type="button" class="largeButton" value="Download Latest Version - 4" onclick="document.location='<?=$siteRoot?>files/addon/WebDKP4.zip'">
<br />
( <a href="#releasenotes">Release Notes</a> | <a href="<?=$siteRoot?>Screenshots">More Screenshots</a> )
<br />
<br />

<a href="<?=$siteRoot?>images/screenshots/Main_DKP.jpg" rel="lightbox" title="">
<img class="photo" src="<?=$siteRoot?>images/screenshots/Main_DKP_thumb.jpg" /></a>

<a href="<?=$siteRoot?>images/screenshots/General_Options_Frame.jpg" rel="lightbox" title="">
<img class="photo" src="<?=$siteRoot?>images/screenshots/General_Options_Frame_thumb.jpg" /></a>

<a href="<?=$siteRoot?>images/screenshots/Visual_Log_Frame.jpg" rel="lightbox" title="" >
<img class="photo" src="<?=$siteRoot?>images/screenshots/Visual_Log_Frame_thumb.jpg" style="height:116px" /></a>

<br />
<br />

<h2>Addon Features</h2>
<ul>
	<li>Integration with WebDKP.com</li>
	<li>View, Sort, and Filter your DKP table In-Game</li>
	<li>Add Awards and Items in Game</li>
	<li>Visual Award Log</li>
	<li>In Game Synching of Data</li>
	<li>Automated Bidding Support</li>
	<li>Timed Awards</li>
	<li>Zerosum DKP Support</li>
	<li>Attendance Tracking</li>
	<li>Auto Award for Boss Kills</li>
	<li>Customizable Options and Announcements</li>
	<li>Sync your in game table with WebDKP.com to manage your data and share it with others</li>
	<li>Start of EPGP Support</li>
</ul>

<br />
<a name="releasenotes"></a>
<h2>Listing of changes for 4.0</h2>
- Compatibility update for WoW Classic <br>
- Special thanks to <a href="https://github.com/avatarofhope2">github/avatarofhope2</a>
<br>
<br>

<h2>Listing of changes for 3.6</h2>
- LUA error fix<br />
- TOC update
- Autoaward for boss kill fixes
- MAC Java sync file included in the zip
<br>
<br>

<h2>Listing of changes from 3.5.02</h2>
- TOC Fix
<br>
<br>

<h2>Listing of changes from 3.5.01</h2>
- Few bug fixes<br />
- Fixes for Autoaward for boss kills
<br>
<br>

<h2>Listing of changes from 3.5</h2>
- Fixed the Combat_Event issue for Autoawards. (Blizzard changed API)<br />
- Fixed itemid saving for Wowhead heroic vs normal linking<br />
- Fixed some misc errors for nulls during bidding<br />

<h2>Listing of changes from 3.43c</h2>
- Updated for WoW 4.1<br />
- Fixed Standby Players Resetting<br />
- Added a feature to only reset Standby players manually<br />
- EPGP features are integrated but not fully functional yet (You can test and report)<br />
- Some tweaks to start saving item IDs for wowhead linking<br />
- Issue with selecting players in the bidding window may be resolved (Believe this only occurs when players have fractional numbers)<br />

<h2>Listing of changes from 3.43a</h2>
- Fixed a loot table bug.<br />
- Added Cataclysm Auto Awards.<br />
- Fixed an autoaward bug.<br />
<br />

<h2>Listing of changes from 3.43</h2>
- Fixed a bug when you try to award an item with a blank cost<br />
<br />

<h2>Listing of changes from 3.42c</h2>
- Fixed an alt click item for bidding problem<br />
- The Award items frame now checks the multiplier for item level<br />
- Fixes for bidding which was screwed up with the item level mult code.<br />
<br />


<h2>Listing of changes from 3.42</h2>
- Class Colors<br />
- Added some Inv Slot under Bidding options<br />
- Adjustable filter for raid attendanec<br />
- In game syncs the log now not just the DKP values<br />
- Zerosum can be applied to standby players<br />
- Fixed an issue related to Loot Tables<br />
<br />


<h2>Listing of changes from 3.41</h2>
- Ability to add the variable $totalbids to Announcements<br />
- More Bidding Options <br />
- Item Level Multiplier<br />
- Item Equip Location Multiplier<br />
- Ability to add item level in the loot table<br />
- Ability to award percent values, the prompt will display when this is allowed<br />
- Fixed a few small things.<br />
- /main and /greed no longer require a value to be accepted, it just defaults to 0. <br />
<br />

<h2>Listing of changes from 3.3b</h2>
- Compatible with 4.0.1.<br />
- New Standby Features<br />
- New option to enable a set DKP Cap (Not tested with in game synching)<br />
- Added new filters to filter Alt characters and Standby characters<br />
- Ability to award percent values, the prompt will display when this is allowed<br />
- A new button in the bidding window to list the top 3 bidders.<br />
- Fixed several potential bugs and sorting in the award log.<br />
- Option to ignore WebDKP whispers from people outside of the party/raid to minimize spam of DKP info.<br />
<br />

<h2>Listing of changes from 3.3</h2>
- When someone rolls now, it will display their total DKP instead of 0.<br />
- Small changes to the in game help (I need to get to more on this someday)<br />
- New option in the bidding options to specify a !need percent. (Default is 100%)<br />
- Changes to the auto award for boss kills, should show the proper encounter in the award log now.<br />
- An online filter for guild members, Unfortunately this only works for guild members, it will not show online status for non guild members.<br />
- The Guild Rank has been added to the bidding window.<br />
<br />

<h2>Release Notes 3.2</h2>
- There is now an option to disable the Alt+Click feature.<br />
- Fix for auto award for boss kills in 5 mans<br />
- You can now add or delete someone to an award in the award log<br />
- You can now specify a % amount for !greed which is based on the loot table value or their total DKP if the loot table is blank.<br />
- Monitoring rolls during bidding should now be fixed.<br />
- Fixed the in game DKP table synching issue where it doesn't transfer Death Knight info.<br />
- Added support for Item IDs in the loot table to fix the issue of having heroic items vs non heroic<br />
- Believe the auto award for Valithiar Dreamwalker encounter is fixed.<br />
<br />



</div>