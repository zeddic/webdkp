<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>"><?=$eventMessage?></div>
<?php } ?>

<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/calc.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Recalculate DKP</div>
	This repair tasks will examine every player's current DKP and Lifetime DKP and ensure
	that it is correct based on their history. If a players DKP is not correct, it is
	changed so that it matches the sum of all the awards they have recieved. The
	usual sign that DKP needs to be recalculated is when a player's history either
	starts above or below 0. The source of these problems were caused by bugs in
	earlier versions of WebDKP.com <b>This operation cannot be undone</b>.
	<br /><br />
	<input type="button" value="Recalculate DKP" class="largeButton" onclick="document.location='<?=$baseurl?>Admin/Repair?event=recalc'">

</div>

<br />


<br />
<br />
<br />

</div>
