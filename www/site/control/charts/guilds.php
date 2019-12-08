<?php
include("lib/graph/phpgraphlib.php");
include("lib/graph/phpgraphlib_pie.php");

global $sql;

$NUM_STEPS = 50;

$result = $sql->Query("SELECT registerdate FROM security_users ORDER BY registerdate ASC");
$times = array();
while($row = mysqli_fetch_array($result)) {
	$times[] = strtotime($row["registerdate"]);
}

$start = $times[0];
$end = $times[sizeof($times)-1];
$duration = $end - $start;

//$days = floor($end - $start)/(60*60*24);

//$stepDays = $days / $NUM_STEPS;
$step = $duration / $NUM_STEPS;

$tracer = $start;
$dateIndex = 0;
$data = array();
$i = 1;
$count = 0;
while($tracer <= $end) {
	while($times[$dateIndex] <= $tracer) {
		$dateIndex++;
		$count++;
	}
	//echo();
	$data[date("M Y",$tracer)]  = $count;
	$i++;
	$tracer += $step;
}

$graph=new PHPGraphLib(500,500);
$graph->addData($data);
$graph->setupXAxis(30);
$graph->setupYAxis(10);
$graph->setTitle("Guilds Using WebDKP");
$graph->setBars(false);
$graph->setLine(true);
$graph->setLineColor("255,5,0");
//$graph->setDataPoints(false);
//$graph->setDataPointColor("maroon");
//$graph->setDataValues(false);
//$graph->setDataValueColor("maroon");
$graph->createGraph();
?>
?>