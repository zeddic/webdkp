<?php
if($type == "loot")
	$tablename = "LootTable";
else if($type == "awards")
	$tablename = "AwardsTable";
else
	$tablename = "DKPTable";
?>



<?php foreach($data as $entry) { ?>
WebDKP.<?=$tablename?>.Add(<?=(util::json($entry))?>);
<?php } ?>

WebDKP.<?=$tablename?>.CalculatePagingInfo();
WebDKP.<?=$tablename?>.UpdatePageText();
WebDKP.<?=$tablename?>.DrawOnLoad();