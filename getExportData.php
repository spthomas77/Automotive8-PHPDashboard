<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
include 'share.php';

$email = $_GET["email"];
$pwd = $_GET["pwd"];
$emailData = $_GET['emailData'];
$LastName = $_GET['LastName'];
$FirstName = $_GET['FirstName'];
$opr1 = $_GET['opr1'];
$opr2 = $_GET['opr2'];
$opr3 = $_GET['opr3'];
$Product = $_GET['Product'];
$account = $_GET["account"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];
$filters = $_GET["filters"];

$filters = urldecode($filters);
//echo "filters = $filters<br>";

$mode = $_GET['report_mode'];
$searchData = $_GET['searchData'];

if($mode){	
}else{
	$mode = ''; 
}
if($searchData){	
}else{
	$searchData = ''; 
}


$jsonFilters = json_decode($filters);
//var_dump($jsonFilters);
//echo "<br>";


$SelectedAccountID = $account;

$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
//echo "USERTICKET = $USERTICKET<br>";

//$result = loadContactArrayFilters($email,$USERTICKET,FOLDER,$account,$jsonFilters);
$result = loadContactArrayFiltersSub($email,$USERTICKET,FOLDER,$account,$jsonFilters,$mode,$searchData);


$data = $result['data'];
$colNames = $result['colNames'];

$output = '';

$FieldCount = sizeof($colNames);
for($i = 0; $i < $FieldCount;$i++) {
	$output = $output.'"'.$colNames[$i].'",';
}
$output = substr_replace($output, "", -1);
$output = $output.PHP_EOL;

$rowCount = sizeof($data);
for($i = 0; $i < $rowCount;$i++) {
	$row = $data[$i];
	$colCount = sizeof($row);
	$line = '';
	for($j = 0; $j < $colCount;$j++) {
//		if(colNames[$j] != "Notes"){ //fung fillter note from export
			$line = $line.'"'.$row[$colNames[$j]].'",';
//		}
	}
	$line = substr_replace($line, "", -1);
	$output = $output.$line.PHP_EOL;
}





header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"exportData.csv\"");

echo $output;




