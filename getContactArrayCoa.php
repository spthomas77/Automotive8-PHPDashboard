<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('shareCoa.php');

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


$_SESSION['emailData'] = $emailData;
$_SESSION['LName'] = $LastName;
$_SESSION['FName'] = $FirstName;
$_SESSION['opr1'] = $opr1;
$_SESSION['opr2'] = $opr2;
$_SESSION['opr3'] = $opr3;
$_SESSION['Product'] = $Product;

if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}

$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
//echo "USERTICKET = $USERTICKET<br>";
$jsonFilters = NULL;
$result = loadContactArrayFilters($email,$USERTICKET,FOLDER,$account,$jsonFilters);



//echo json_encode($rows);

echo json_encode( array(
	'success'   => $result['success'],
	'data'		=> $result['data'],
	'colNames'	=> $result['colNames'],
	'colModel'	=> $result['colModel'] 
));




