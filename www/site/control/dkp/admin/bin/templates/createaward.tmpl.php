<?=$tabs?>
<?=$sidebar?>
<?=$tableselect?>
<br />
<div class="adminContents">


<div id="AwardContent1" style="display:block">
<table class="dkpForm" >
<tr>
	<td><div class="dkpSubtitle">Create Award - Step 1</div></td>
</tr>
<tr>
	<td>Would you like to award an item or DKP?</td>
</tr>
<tr>
	<td>
	<input type="button" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Manage'" value="Cancel" style="width:100px">
	<input type="button" class="largeButton" onclick="DKPManage.AwardItem()" value="Award Item" style="width:100px">
	<input type="button" class="largeButton" onclick="DKPManage.AwardGeneral()" value="Award DKP" style="width:100px">
	</td>
</tr>
</table>
</div>

<div id="AwardContent2Item" style="display:none">
<table class="dkpForm" >
<tr>
	<td colspan=2>
	<div class="dkpSubtitle">Create Award - Step 2</div>
	</td>
</tr>
<tr>
	<td colspan=2>
	Enter some details about your award.
	</td>
</tr>
<tr>
	<td class="label" style="width:180px">Item Name</td>
	<td><input tabindex=1 name="itemname" id="item_name" type="text" value="" ></td>
</tr>
<tr>
	<td class="label"><a href="javascript:;" class="tooltip" tooltip="How much DKP this item should cost. Positive numbers only.">Cost</a></td>
	<td><input tabindex=2 name="cost" id="item_cost" type="text" value=""></td>
</tr>
<tr>
	<td class="label">Give To</td>
	<td>
	<select tabindex=3 name="playerselect" id="userdropdown">
	</select>
	</td>
</tr>
<tr>
	<td class="label">Location</td>
	<td><input tabindex=4 name="location" type="text" id="item_location" value="WebDKP"></td>
</tr>
<tr>
	<td class="label">Awarded By</td>
	<td><input tabindex=5 name="awardedby" id="item_awardedby" type="text" value="<?=$siteUser->username?>"></td>
</tr>
<tr>
	<td colspan=2>
	<input type="button" class="largeButton" onclick="DKPManage.StartAward()" value="Back" style="width:100px">
	<input tabindex=6 type="button" class="largeButton" onclick="DKPManage.AwardItemContinue()" value="<?=($settings->GetZerosumEnabled()?"Select Zerosum":"Award")?>" style="width:120px">
	</td>
</tr>
</table>
</div>

<div id="AwardContent2" style="display:none">
<table class="dkpForm" >
<tr>
	<td colspan=2>
	<div class="dkpSubtitle">Create Award - Step 2</div>
	</td>
</tr>
<tr>
	<td colspan=2>
	Enter some details about your award.
	</td>
</tr>
<tr>
	<td class="label" style="width:180px">Reason</td>
	<td><input tabindex=7 id="award_reason" type="text" value="" ></td>
</tr>
<tr>
	<td class="label"><a href="javascript:;" class="tooltip" tooltip="Enter a positive number to award DKP. Enter a negative number to deduct DKP">Amount</a></td>
	<td><input tabindex=8 id="award_cost" type="text" value=""></td>
</tr>
<tr>
	<td class="label">Location</td>
	<td><input tabindex=9 id="award_location" type="text" value="WebDKP"></td>
</tr>
<tr>
	<td class="label">Awarded By</td>
	<td><input tabindex=10 id="award_awardedby" type="text" value="<?=$siteUser->username?>"></td>
</tr>
<tr>
	<td colspan=2>
	<input type="button" class="largeButton" onclick="DKPManage.StartAward()" value="Back" style="width:100px">
	<input tabindex=11 type="button" class="largeButton" onclick="DKPManage.SelectRecipients()" value="Select Recipients" style="width:150px">
	</td>
</tr>
</table>
</div>


<div id="AwardContent3" style="display:none">
<table class="dkpForm" >
<tr>
	<td>
	<div class="dkpSubtitle" id="selectPlayersTitle">Create Award - Step 3</div>
	</td>
</tr>
<tr>
	<td id="selectPlayersContent">
	Select the players who will recieve this award by clicking on their row.
	Use the filter to quickly find players with a given name.
	</td>
</tr>
<tr>
	<td colspan=2>
	<input type="button" class="largeButton" onclick="DKPManage.SelectPlayersBack()" value="Back" style="width:100px">
	<input type="button" class="largeButton" onclick="DKPManage.SelectPlayersForward()" value="Award" style="width:100px">
	</td>
</tr>
<tr>
	<td>
	<table class="dkp" id="selecttable" cellpadding=0 cellspacing=0 >
		<thead>
		<tr class="header">
			<th class="link"><a>Players</a></th>
		</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<script type="text/javascript">
	playertable = new SimplePlayerSelectTable("selecttable");
	playertable.EnablePaging(50);
	<?php foreach($data as $entry) { ?>
	playertable.Add(<?=(util::json($entry))?>);
	<?php } ?>
	playertable.Draw();
	DKPManage.Init(<?=($settings->GetZerosumEnabled()?'true':'false')?>);
	</script>
	</td>
</tr>
</table>
</div>

<div id="AwardContentFinished" style="display:none">
<table class="dkpForm" >
<tr>
	<td><div class="dkpSubtitle" id="awardFinishedTitle">Players Awarded</div></td>
</tr>
<tr>
	<td>
		<div class="message" id="awardFinishedOk">test</div>
		<div class="errorMessage" id="awardFinishedBad">test</div>
	</td>
</tr>
<tr>
	<td>
	<input type="button" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Manage'" value="Continue" style="width:100px">
	</td>
</tr>
</table>
</div>

<br />
<br />
<br />

</div>
