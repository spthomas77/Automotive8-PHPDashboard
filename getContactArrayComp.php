<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('shareDT.php');

//hardcode 
$email = 'kdutta@mindfireinc.com'; 
$pwd = 'MindFire2012'; 
$account = 2848; 
$PartnerGuid = 'Ventas Strategies';
$PartnerPassword = '846a4340e6b4e'; 


$emailData = $_GET['emailData'];
$LastName = $_GET['LastName'];
$FirstName = $_GET['FirstName'];
$opr1 = $_GET['opr1'];
$opr2 = $_GET['opr2'];
$opr3 = $_GET['opr3'];
$Product = $_GET['Product'];



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
	$SelectedAccountID = 2848;
}

$mode = 'Sold';  //All/Leads/Incompletes/FollowUp/Appointment/InProgess/Sold/Lost
$searchData = 'fa'; 

$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
//echo "USERTICKET = $USERTICKET<br>";
$jsonFilters = NULL;

//$result = loadContactArrayFilters($email,$USERTICKET,FOLDER,$account,$jsonFilters);
//$result = loadContactArrayFiltersUse($email,$USERTICKET,FOLDER,$account,$jsonFilters);
$result = loadContactArrayFiltersSub($email,$USERTICKET,FOLDER,$SelectedAccountID,$jsonFilters,$mode,$searchData);


 /*
//TEST searchbox 

$result = loadContactReportFilter($USERTICKET,FOLDER,$searchData,$mode); 
*/ 



//echo json_encode($rows);
/*
echo json_encode( array(
	'success'   => $result['success'],
	'data'		=> $result['data'],
	'colNames'	=> $result['colNames'],
	'colModel'	=> $result['colModel']
));
*/ 



