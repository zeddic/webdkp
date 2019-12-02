<?php
include_once("lib/stats/wowstats.php");

$name = util::getData("name");

$link = wowstats::GetTextLink($name, 0, true);
//unset($item->tablename);

echo($link);

//echo(util::json($item));
?>