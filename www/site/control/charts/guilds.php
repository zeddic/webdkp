<?php
include("lib/graph/phpgraphlib.php");
include("lib/graph/phpgraphlib_pie.php");

global $sql;

$NUM_STEPS = 50;

$result = $sql->Query("SELECT registerdate FROM security_users ORDER BY registerdate ASC");
$times = array();
while($row = mysql_fetch_array($result)) {
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

//print_r($data);
//die();

/*$result = $sql->Query("SELECT  count(*) as total, security_users.firstname, security_users.lastname
					   FROM patents, security_users
					   WHERE assignedto = security_users.id
					   GROUP BY assignedto");

$data = array();
while($row = mysql_fetch_array($result)) {

	$name = $row["lastname"].", ".$row["firstname"];
	$assigned = $row["total"];
	$data[$name] = $assigned;
}

$result = $sql->Query("SELECT  count(*) as total, security_users.firstname, security_users.lastname
					   FROM patents, security_users
					   WHERE assignedto = security_users.id AND complete=1
					   GROUP BY assignedto");
$data2 = array();
while($row = mysql_fetch_array($result)) {
	$name = $row["lastname"].", ".$row["firstname"];
	$assigned = $row["total"];
	$data2[$name] = $assigned;
}*/

/*$graph=new PHPGraphLib(950,500);
//$graph->setupXAxis(30);
//$data=array("1"=>.0032,"2"=>.0028,"3"=>.0021,"4"=>.0033,"5"=>.0034,"6"=>.0031,"7"=>.0036,"8"=>.0027,"9"=>.0024,"10"=>.0021,"11"=>.0026,"12"=>.0024,"13"=>.0036,"14"=>.0028,"15"=>.0025);

$graph->addData($data);
$graph->setBars(true);
$graph->setLines(true);
$graph->setDataPoints(true);
$graph->setDataPointColor("maroon");
$graph->setDataValues(true);
$graph->setDataValueColor("maroon");
$graph->setGoalLine(.0025);
$graph->setGoalLineColor("red");
//$graph->setDataValues(true);

//$graph->setDataValueColor("black");
//$graph->setLegend(true);
//$graph->setLegendTitle("# Reviewed","Total Assigned Patents");
//$graph->setGradient("146,203,255", "51,157,254","0,85,164", "0,124,240");
$graph->createGraph();*/


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