<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/settings.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Instructions</div>
	Here you can set the various settings for WebDKP. These options control what
	type of information is visible on your site as well as allows you to
	enable various types of DKP methods. For example, here can enable Zerosum DKP.
</div>

<?php if(isset($eventResult)){ ?>
<div style="margin-left:70px;" class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>
<br />
<br />


<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/loot.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Loot Table -
	<?php if($settings->GetLootTableEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span>
	<?php } ?>
	</div>
	A loot table allows you to set standard costs for items dropped in raids.
	Some guilds use this to allow items to be <i>purchased</i> by players using their
	DKP. When this option is enabled a Loot Table tab will appear on your site.
	You will also be able to manage your Loot Table from your Control Panel, either uploading a pre-created
	Loot Table file or manually creating one. The Loot Table is also
	integrated with the in-game addon. When items in your Loot Table drop, their
	costs will automattically be filled in for you.

	<br />
	<br />

	<?php if($settings->GetLootTableEnabled()){ ?>
	<input type="button" value="Disable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setLootTable&state=0'">
	<?php } else { ?>
	<input type="button" value="Enable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setLootTable&state=1'">
	<?php } ?>
</div>

<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/settings/totals.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Lifetime DKP -
	<?php if($settings->GetLifetimeEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span>
	<?php } ?>
	</div>
	This option allows you to toggle visibility of a players <i>Lifetime DKP</i> on the table.
	This is the total DKP that a player has earned over the entire time they have played,
	ignoring any DKP that they have spent. It can be used as an indicator of how long
	a player has been with a guild.
	<br />
	<br />
	<?php if($settings->GetLifetimeEnabled()){ ?>
	<input type="button" value="Disable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setLifetime&state=0'">
	<?php } else { ?>
	<input type="button" value="Enable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setLifetime&state=1'">
	<?php } ?>

</div>
<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/settings/zerosum.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Zerosum DKP -
	<?php if($settings->GetZerosumEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span>
	<?php } ?>
	</div>
	This option toggles the Zerosum method of calculating DKP for both the addon
	and website. Zerosum DKP is an alternative method of doing DKP that helps
	counter the inflation normally seen in DKP Tables.
	<br />
	<br />
	<a href="javascript:Util.Toggle('zerosumHelp')"><img src="<?=$siteRoot?>images/buttons/help.png" style="vertical-align:text-bottom"> Read More >></a>
	<br />
	<div id="zerosumHelp" style="display:none">
	The idea behind Zerosum DKP is that for every amount of DKP deducted an equal
	but opposite amount of DKP should also be awarded, such that the sum of everyones
	DKP in your table is zero.
	<br />
	<br />
	In a Zerosum system the only time DKP is entered into the table is when an item
	is awarded. There are no awards for being on time or for killing certain bosses.
	The way it works is that when a player purchases an item for DKP, everyone in the
	raid is given positive DKP equal to the cost of the item divided among them. If
	you have this feature enabled, these calculations are done for you.
	<br />
	<br />
	For Example:
	<br />
	Belt of Might drops in Molten Core which a warrior purchases for 50 DKP.
	Everyone in the raid is given positive DKP equal to the # of people / the cost of the item:
	<br />50 dkp / 40 people = +1.25 DKP per person.
	<br />
	<br />
	You can read more about Zerosum DKP on the <a href="http://www.wowwiki.com/Zero-sum_DKP">WoWWiki</a>.
	</div>
	<br />
	<?php if($settings->GetZerosumEnabled()){ ?>
	<input type="button" value="Disable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setZerosum&state=0'">
	<?php } else { ?>
	<input type="button" value="Enable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setZerosum&state=1'">
	<?php } ?>
</div>
<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/settings/alt.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Alt & Main DKP Sharing -
	<?php if($settings->GetCombineAltsEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span>
	<?php } ?>
	</div>
	When enabled this option allows <i>Alts</i> and <i>Mains</i> within your table to share DKP.
	Whenver an Alt gains DKP it will immediatly be transferred to that player's Main account.
	When playing as either their Alt or their Main, players will have full access to the
	combined DKP between the players. This works for bidding with the in game addon as well.
	<br />
	<br />
	<?php if($settings->GetCombineAltsEnabled()){ ?>
	<input type="button" value="Disable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setAltMain&state=0'">
	<?php } else { ?>
	<input type="button" value="Enable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setAltMain&state=1'">
	<?php } ?>
</div>
<br />
<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/settings/alt.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Tiers -
	<?php if($settings->GetTiersEnabled()){ ?>
	<span style="color:green">Enabled</span>
	<?php } else { ?>
	<span style="color:red">Disabled</span>
	<?php } ?>
	</div>
	Tiers are a method of grouping players in your tabled based on ranges of DKP. The
	higher a persons DKP is, the higher Tier they belong to. For example, players
	with DKP between 0 and 50 may be in Tier 1 while players with DKP between 51 and 100
	may be in Tier 2. Tiers are sometimes used by guilds to speed up loot distribution
	in game. When deciding who gets to roll on an item, players in higher tiers
	are given priority. When enabled, a players Tier will appear on the main DKP Table.
	<br />
	<br />
	<?php if($settings->GetTiersEnabled()){ ?>
	<b>Tier Size</b><br />
	<form action="<?=$baseurl?>Admin/Settings" method="post">
	<input type="hidden" name="event" value="setTierSize">
	<input type="text" name="size" value="<?=$settings->GetTierSize()?>"> <input type="submit" value="Update">
	</form>
	<br />
	<input type="button" value="Disable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setTiers&state=0'">
	<?php } else { ?>
	<input type="button" value="Enable" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Settings?event=setTiers&state=1'">
	<?php } ?>
</div>
<br />
<br />

</div>
