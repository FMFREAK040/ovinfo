<html>
<head>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
<style>
body {
	background-color: white;
}
th {
	font-family: Arial, Helvetica; 
	color:blue; 
	font-size: 26px;
	text-align: left;
}
.tbheader {
	background-color:blue;
	color: white;
	}
.tbhalte {
	font-size: 16px;
}
	
</style>
</head>
<body>
<table>
	<tr class="tbheader">
		<th class="tbheader">Tijd</th>
		<th class="tbheader">&nbsp&nbspBus</th>
		<th class="tbheader">&nbsp&nbspRichting</th>
		<th class="tbheader">Halte</th>
	</tr>
<?php

///
/// Get data from v0.ovapi.nl and put it in a array.
/// tpc is TimingPoint code. Find it on: https://drgl.nl/
///

date_default_timezone_set('Europe/Amsterdam');

$busstop="65150930,65150940,65151250";
error_reporting(E_ALL);
$edtime=array();
$linenumber=array();
$destinationtimes=array();
$dtime=array();
$alltrips=array();
$c=0;
$count=0;
$maxrows=8;

/// Get data function

function getOVdata($tpc) {
$contents = file_get_contents('http://v0.ovapi.nl/tpc/'.$tpc);
$contents = utf8_encode($contents);
$results = json_decode($contents,true);
$sendOVdata = $results;

///
/// Return all data
///

return $sendOVdata;
};


$OVdata=getOVdata($busstop);

foreach ($OVdata as $data1)
{
	foreach ($data1["Passes"] as $data2)
	{

		$alltrips[$c]['Line']=$data2['LinePublicNumber'];
		$alltrips[$c]['Destination']=$data2['DestinationName50'];
		$alltrips[$c]['ExpectedAT']=date("U",strtotime($data2['ExpectedArrivalTime']));
		$alltrips[$c]['TargetAT']=date("U",strtotime($data2['TargetArrivalTime']));
		$alltrips[$c]['TimingPointName']=$data2['TimingPointName'];
	$c=$c+1;
	};
};

usort($alltrips, function ($a, $b) { return ($a['TargetAT'] - $b['TargetAT']);});

foreach ($alltrips as $row) if ($count<$maxrows)
{
	foreach ($row as $field)
	{
		$expectedarrivaltime = $row['ExpectedAT'];
		$targettime = $row['TargetAT'];
		$delayed = (($expectedarrivaltime-$targettime)/60);
		$targettime=date("H:i",$targettime);
		$destination = $row['Destination'];
		$linenumber = $row['Line'];
		$tpname = $row['TimingPointName'];
		$delayed=round(intval($delayed),0);
		if ($delayed!=0)
		{
			if ($delayed<0)
			{
				$delayed="<b style=\"color: darkgreen;\">".$delayed."</b>";
			}
			else
			{
				$delayed="<b style=\"color: red;\">+".$delayed."</b>";
			}
		}
			else
		{
			$delayed="";
		};
	};
	echo "<div style=\"font-family: Arial, Helvetica; color:blue; font-size: 18px;\"><tr><th><i class=\"far fa-clock\"></i> ".$targettime." ".$delayed."</th><th>&nbsp&nbsp<i class=\"fas fa-bus\"></i> ".$linenumber."</th><th>&nbsp&nbsp".$destination."</th><th class=\"tbhalte\">".$tpname."</tr></div>";
	$count+=1;
};

?>
</table>
</body>
</html>
